<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Exceptions\OperatingHourException;
use App\Http\Requests\BookingRequest;
use App\Jobs\BookingCancellationJob;
use App\Jobs\BookingConfirmationJob;
use App\Jobs\SendEmailJob;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\StudioClosure;
use App\Models\Address;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    private const BOOKING_PAGINATE_COUNT = 15;
    private const BOOKING_DAY_SLOT_FORWARD = 3;

    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function getBookings($userId, $type)
    {
        $query = Booking::where('user_id', $userId)->with(['user', 'address.company', 'address.photos', 'address.badges', 'status']);

        if ($type === 'history') {
            $query->where('date', '<', now());
        } else {
            $query->where('date', '>=', now());
        }

        $bookings = $query->paginate(self::BOOKING_PAGINATE_COUNT);

        return $bookings;
    }

    public function filterBookings($userId, $status, $date, $time, $search, $type)
    {
        $query = Booking::where('user_id', $userId);

        if ($type === 'history') {
            $query->where('date', '<', now());
        } else {
            $query->where('date', '>=', now());
        }

        if ($status) {
            $query->whereHas('status', function ($query) use ($status) {
                $query->where('name', $status);
            });
        }

        if ($date) {
            $query->whereDate('date', Carbon::parse($date));
        }

        if ($time) {
            $query->whereTime('start_time', '<=', Carbon::parse($time))
                ->whereTime('end_time', '>=', Carbon::parse($time));
        }

        if ($search) {
            $lowerSearch = strtolower($search);
            $query->where(function ($q) use ($lowerSearch) {
                $q->whereHas('address.company', function ($q2) use ($lowerSearch) {
                    $q2->whereRaw('LOWER(name) LIKE ?', ["%$lowerSearch%"]);
                })
                    ->orWhereHas('address', function ($q3) use ($lowerSearch) {
                        $q3->whereRaw('LOWER(street) LIKE ?', ["%$lowerSearch%"]);
                    });
            });
        }

        return $query->with(['address.company', 'address.badges', 'status', 'user'])->paginate(self::BOOKING_PAGINATE_COUNT);
    }

    public function getAvailableStartTime(string $date, int $addressId): array
    {
        $address = Address::findOrFail($addressId);
        if (!$address) {
            throw new ModelNotFoundException("Address not found");
        }
        $timezone = $address->timezone;

        $date = Carbon::parse($date);
        Log::info('Date in getAvailableStartTime: ' . $date);
        $operatingHours = $this->getOperatingHours($addressId, $date);

        $openTime = $date->copy()->setTimezone($timezone)->setTimeFromTimeString($operatingHours->open_time);
        $closeTime = $date->copy()->setTimezone($timezone)->setTimeFromTimeString($operatingHours->close_time);
        // Get the current time with timezone
        $now = Carbon::now($timezone);

        // Round up to the nearest hour, 20:05 -> 21:00
        if ($now->minute > 0 || $now->second > 0) {
            $now->addHour()->minute(0)->second(0);
        }

        // If the openTime is before the current time, set openTime to the rounded current time, e.g. 10:00(common) -> 21:00 (current)
        if ($openTime->lessThan($now)) {
            $openTime = $now;
        }

        Log::info('Open Time: ' . $openTime);
        Log::info('Close Time: ' . $closeTime);
        Log::info('Now: ' . $now);
       
        // Исключаем бронирования со статусами cancel, pending и expired
        $bookings = Booking::where('address_id', $addressId)
            ->where('date', $date->toDateString())
            ->whereHas('status', function ($query) {
                // Исключаем статусы cancel и expired, если pending or paid, то оставляем
                $query->whereNotIn('name', ['cancel', 'expired']);
            })
            ->orderBy('start_time', 'asc')
            ->get();

        Log::info('Bookings: ', $bookings->toArray());

        $availableStartTimes = $this->calculateAvailableStartTimes($bookings, $openTime, $closeTime);

        // Форматируем доступные временные слоты
        $formattedTimes = array_map(function ($time) use ($date) {
            $timeInstance = $date->copy()->setTimeFromTimeString($time);
            return [
                'time' => $time,
                'date' => $timeInstance->format('h:i A j M'),
                'iso_string' => $timeInstance->toIso8601String()
            ];
        }, $availableStartTimes);

        return $formattedTimes;
    }

    private function calculateAvailableStartTimes($bookings, $openTime, $closeTime): array
    {
        Log::info('Calculating available start times');
        Log::info('Comparin:g ' . $openTime->greaterThanOrEqualTo($closeTime) ? 'true' : 'false');
        //If modified open time is greater or equal than close time, return empty array, e.g. 21:00 - 21:00 means no available times
        if ($openTime->greaterThanOrEqualTo($closeTime)) {
            return [];
        }
        $availableStartTimes = [];
        $current = $openTime->copy();

        // Создаем массив занятых временных интервалов
        $occupiedIntervals = [];

        Log::info('Calculating available start times');
        Log::info('Open Time: ' . $openTime);
        Log::info('Close Time: ' . $closeTime);

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->date . ' ' . $booking->start_time);
            $bookingEnd = Carbon::parse($booking->date . ' ' . $booking->end_time);
            $occupiedIntervals[] = [$bookingStart, $bookingEnd];
            Log::info('Booking Interval: ', ['start' => $bookingStart, 'end' => $bookingEnd]);
        }

        Log::info('Occupied Intervals: ', $occupiedIntervals);

        // Проходимся по всем возможным временным слотам в течение рабочего времени
        while ($current->lt($closeTime)) {
            $isAvailable = true;
            foreach ($occupiedIntervals as [$start, $end]) {
                // Проверяем, не попадает ли текущий слот в занятый интервал
                if ($current->between($start, $end) || $current->eq($start)) {
                    $isAvailable = false;
                    break;
                }
            }
            if ($isAvailable) {
                $availableStartTimes[] = $current->format('H:i');
            }
            $current->addHour();
        }

        return $availableStartTimes;
    }

    public function getAvailableEndTime(string $date, int $addressId, string $startTime): array
    {
        $address = Address::findOrFail($addressId);
        if (!$address) {
            throw new ModelNotFoundException("Address not found");
        }
        $timezone = $address->timezone;

        $date = Carbon::parse($date)->startOfDay();
        $startTime = Carbon::parse($date->toDateString() . ' ' . $startTime);

        $operatingHours = $this->getOperatingHours($addressId, $date);

        // Extract the time portion only
        $openTime = Carbon::parse($operatingHours->open_time);
        $closeTime = $operatingHours->close_time === '24:00' ? Carbon::parse('23:59:59') : Carbon::parse($operatingHours->close_time);
        
        

        $startTimeOnly = Carbon::parse($startTime->format('H:i:s'));

        // Compare times without date
        if ($startTimeOnly->lt($openTime) || $startTimeOnly->gte($closeTime)) {
            throw new BookingException('Start time is outside of operating hours', 422);
        }

        $bookings = Booking::where('address_id', $addressId)
            ->where(function ($query) use ($date, $startTime) {
                $query->whereDate('date', '>=', $date->toDateString())
                    ->whereTime('start_time', '>=', $startTime->toTimeString());
            })
            ->whereHas('status', function ($query) {
                $query->whereNotIn('name', ['cancel', 'expired']);
            })
            ->orderBy('start_time', 'asc')
            ->get();

        if ($operatingHours->mode_id == 1) {
            $date->addDays(3); // Добавляем 3 дня для режима 24/7
        }

        return $this->calculateAvailableEndTimes($bookings, $startTime, $closeTime, $date, $operatingHours->mode_id);
    }

    private function calculateAvailableEndTimes($bookings, $startTime, $closeTime, $date, $mode): array
    {
        $availableEndTimes = [];
        $current = $startTime->copy()->addHour();
        $closeDateTime = $date->copy()->setTimeFrom($closeTime);

        if ($mode == 1) { // 24/7 mode
            $maxHours = self::BOOKING_DAY_SLOT_FORWARD * 24; // 72 часа вперед
        } else {
            $maxHours = $closeDateTime->diffInHours($startTime);
        }

        // Добавляем 3 дня к дате
        $endDate = $date->copy()->addDays(3);

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->date . ' ' . $booking->start_time);
            $bookingEnd = Carbon::parse($booking->date . ' ' . $booking->end_time);

            // If the start time is before the booking start
            if ($startTime->lt($bookingStart)) {
                while ($current->lte($bookingStart) && $maxHours > 0) {
                    $availableEndTimes[] = [
                        'time' => $current->format('H:i'),
                        'date' => $current->format('h:i A j M'),
                        'iso_string' => $current->toIso8601String()
                    ];
                    if ($current->eq($bookingStart)) {
                        // Stop adding end times if we reach the booking start time
                        return $availableEndTimes;
                    }
                    $current->addHour();
                    $maxHours--;
                }
            }

            // Adjust the current time if the start time falls within an existing booking
            if ($startTime->lt($bookingEnd) && $current->gte($bookingStart) && $current->lt($bookingEnd)) {
                $current = $bookingEnd->copy()->addHour();
            }
        }

        // Обрабатываем максимальное время до конечной даты
        while ($maxHours > 0 && $current->lte($endDate)) {
            $availableEndTimes[] = [
                'time' => $current->format('H:i'),
                'date' => $current->format('h:i A j M'),
                'iso_string' => $current->toIso8601String()
            ];
            $current->addHour();
            $maxHours--;
        }

        return $availableEndTimes;
    }

    public function getTotalCost(string $startTime, string $endTime, int $addressId)
    {

        // Расчет количества часов между start_time и end_time

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $hours = $end->diffInHours($start);


        $addressPrices = DB::table('address_prices')
            ->where('address_id', $addressId)
            ->where('is_enabled', true)
            ->orderBy('hours', 'asc')
            ->get();

        if ($addressPrices->isEmpty()) {
            throw new \Exception('Prices were not set');
        }

        // Поиск подходящего пакета часов и расчет цены
        $totalPrice = 0;
        foreach ($addressPrices as $price) {
            if ($hours >= $price->hours) {
                $totalPrice = $hours * $price->price_per_hour;
            }
        }

        return $totalPrice;
    }

    public function bookAddress(BookingRequest $request): array
    {
        try {
            $addressId = $request->input('address_id');
            $bookingDate = Carbon::parse($request->input('date'))->format('Y-m-d');
            $startTime = Carbon::parse($request->input('start_time'));
            $endTime = Carbon::parse($request->input('end_time'));
            $endDate = Carbon::parse($request->input('end_date'))->format('Y-m-d');

            $address = Address::findOrFail($addressId);
            if (!$address) {
                throw new ModelNotFoundException("Address not found");
            }
            // Get the address timezone
            $timezone = $address->timezone;
            
            $userWhoBooks = Auth::user();

            Log::info('Booking request: ', [
                'address_id' => $addressId,
                'booking_date' => $bookingDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'end_date' => $endDate,
                'user_id' => $userWhoBooks->id,
            ]);
            $this->validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime, $timezone);

            $booking = Booking::create([
                'address_id' => $addressId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'user_id' => $userWhoBooks->id,
                'date' => $bookingDate,
                'end_date' => $endDate,
                'status_id' => 1, // studio is on pending after booking
            ]);

            $amount = $this->getTotalCost($startTime, $endTime, $addressId);

            $paymentSession = $this->paymentService->createPaymentSession($booking, $amount);

            //Preparing data for email
            $paymentUrl = $paymentSession['payment_url'] ?? null;
            $userEmail = $userWhoBooks->email;
            $amount = $amount ?? null;

            dispatch(new BookingConfirmationJob($booking, $paymentUrl, $userEmail, $amount));

            return [
                'booking' => $booking,
                'session_id' => $paymentSession['session_id'],
                'payment_url' => $paymentSession['payment_url'],
            ];
        } catch (Exception $e) {
            Log::error("Booking failed: " . $e->getMessage());
            throw new Exception("Failed to book address: " . $e->getMessage());
        }
    }

    public function updateBookingStatus($bookingId, $statusId)
    {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $booking->status_id = $statusId;
            $booking->save();
        }

        return $booking;
    }

    private function validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime, $timezone): void
    {
        // Set the timezone for the current date and time match that user timezone, assuming that server timezone is UTC
        $currentDateTime = Carbon::now($timezone);
    
        // Parse the booking date, start time, and end time correctly with the specified timezone
        $bookingDate = Carbon::createFromFormat('Y-m-d', $bookingDate, $timezone);
        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime, $timezone);
        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTime, $timezone);
    
        // Log information for debugging
        Log::info('Current Date Time: ' . $currentDateTime->toDateTimeString() . ' Timezone: ' . $currentDateTime->getTimezone()->getName());
        Log::info('Booking Start Time: ' . $startTime->toDateTimeString() . ' Timezone: ' . $startTime->getTimezone()->getName());
        Log::info('Is booking in the past: ' . ($startTime->lt($currentDateTime) ? 'Yes' : 'No'));
    
        if ($startTime->lt($currentDateTime)) {
            throw new BookingException('Cannot book a time in the past', 422);
        }
    
        if (StudioClosure::where('address_id', $addressId)->where('closure_date', $bookingDate->toDateString())->exists()) {
            throw new BookingException('Studio is closed on this date', 422);
        }
    
        $operatingHours = $this->getOperatingHours($addressId, $bookingDate);
    
        if (!$operatingHours || $operatingHours->is_closed) {
            throw new BookingException('Booking times are outside of business hours.', 422);
        }
    
        if ($startTime->lt($operatingHours->open_time) || $endTime->gt($operatingHours->close_time)) {
            throw new BookingException("Booking times are outside of business hours. Studio opens at {$operatingHours->open_time} and closes at {$operatingHours->close_time}", 422);
        }
    
        if ($this->isTimeSlotTaken($addressId, $startTime, $endTime, $bookingDate->format('Y-m-d'))) {
            throw new BookingException('Studio is already booked for the requested time slot', 400);
        }
    }

    private function isTimeSlotTaken($addressId, $startTime, $endTime, $date): bool
    {
        return Booking::where('address_id', $addressId)
            ->whereDate('date', '=', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function getOperatingHours(int $addressId, Carbon $bookingDate): OperatingHour
    {
        $operatingHours = OperatingHour::where('address_id', $addressId)->get();

        if ($operatingHours->isEmpty()) {
            throw new OperatingHourException("You didn't set hours", 400);
        }

        $firstLineOperatingHours = $operatingHours->first();

        //1,2 mode - имеют только одну запись в базе об operating hours
        //3, each_days - 7 записей в базе на каждый день недели

        return match ($firstLineOperatingHours->mode_id) {
            1, 2 => $firstLineOperatingHours,
            3 => $this->regular($operatingHours, $bookingDate->dayOfWeek)->firstOrFail(),
            default => throw new OperatingHourException("Invalid operating hours mode", 400),
        };
    }

    public function cancelBooking(int $bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            $startTime = Carbon::parse($booking->date . ' ' . $booking->start_time);

            if ($startTime->diffInHours(Carbon::now()) < 6) {
                throw new BookingException("Booking cannot be cancelled less than 6 hours before the start time.");
            }

            if ($booking->status_id == 2) { // Check if the booking is paid
                 $this->paymentService->refundPayment($booking); // Call refundPayment method

                $booking->status_id = 3; // Status "cancelled"
                $booking->save();

                $user = Auth::user();

                dispatch(new BookingCancellationJob($user, $booking));

                return Booking::where('user_id', $user->id)->get();
            } else {
                throw new Exception("Booking cannot be cancelled.");
            }
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException("Booking not found.");
        } catch (Exception $e) {
            throw new Exception("Failed to cancel booking: " . $e->getMessage());
        }
    }

    private function regular($operatingHours, $dayOfWeek)
    {
        return $operatingHours->where('day_of_week', $dayOfWeek);
    }
}
