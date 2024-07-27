<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Exceptions\OperatingHourException;
use App\Http\Requests\BookingRequest;
use App\Jobs\BookingCancellationJob;
use App\Jobs\BookingPendingJob;
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
        $query = Booking::where('user_id', $userId)
        ->orWhereHas('address.company.adminCompany', function ($query) use ($userId) {
            $query->where('admin_id', $userId);
        })
        ->with(['address.company', 'address.badges', 'status', 'user']);
    
        // Add a join to the addresses table to get the timezone
        $query->join('addresses', 'bookings.address_id', '=', 'addresses.id');
    
        // Filter by type (history or upcoming)
        $query->where(function ($q) use ($type) {
            if ($type === 'history') {
                $q->whereRaw("TO_TIMESTAMP(CONCAT(bookings.date, ' ', bookings.start_time), 'YYYY-MM-DD HH24:MI:SS') < NOW() AT TIME ZONE addresses.timezone");
            } else {
                $q->whereRaw("TO_TIMESTAMP(CONCAT(bookings.date, ' ', bookings.start_time), 'YYYY-MM-DD HH24:MI:SS') >= NOW() AT TIME ZONE addresses.timezone");
            }
        });

        // Execute the query and log the results for debugging
        $results = $query->select('bookings.*')->paginate(self::BOOKING_PAGINATE_COUNT);

        $results->each(function ($booking) {
            Log::info('Booking Details:', [
                'booking_date' => $booking->date,
                'address_timezone' => $booking->address->timezone,
                'converted_current_time' => now()->timezone($booking->address->timezone)->toDateTimeString()
            ]);
        });

        return $results;
    
        // Filter by status
        if ($status) {
            $query->whereHas('status', function ($query) use ($status) {
                $query->where('name', $status);
            });
        }
    
        // Filter by date
        if ($date) {
            $query->whereDate('date', Carbon::parse($date, $timezone));
        }
    
        // Filter by time
        if ($time) {
            $parsedTime = Carbon::parse($time, $timezone);
            $query->whereTime('start_time', '<=', $parsedTime)
                ->whereTime('end_time', '>=', $parsedTime);
        }
    
        // Filter by search term
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

        // Log the SQL query and its bindings
        Log::info('SQL Query:', ['query' => $query->toSql(), 'bindings' => $query->getBindings()]);

        // Execute the query and log the results for debugging
        $results = $query->select('bookings.*')->paginate(self::BOOKING_PAGINATE_COUNT);

        $results->each(function ($booking) {
            Log::info('Booking Details:', [
                'booking_date' => $booking->date,
                'address_timezone' => $booking->address->timezone,
                'converted_current_time' => now()->timezone($booking->address->timezone)->toDateTimeString()
            ]);
        });

        return $results;
    }

    public function getAvailableStartTime(string $date, int $addressId): array
    {
        $address = Address::findOrFail($addressId);
        if (!$address) {
            throw new ModelNotFoundException("Address not found");
        }
        $timezone = $address->timezone;

        $date = Carbon::parse($date, $timezone)->startOfDay();
        Log::info('Date in getAvailableStartTime: ' . $date);
        Log::info('Timezone in getAvailableStartTime: ' . $timezone);
        Log::info('Timezone in date: ' . $date->timezone); 
        $operatingHours = $this->getOperatingHours($addressId, $date);
        Log::info('Operating Hours: ', $operatingHours->toArray());
        $openTime = $date->copy()->setTimezone($timezone)->setTimeFromTimeString($operatingHours->open_time);
        $closeTime = $date->copy()->setTimezone($timezone)->setTimeFromTimeString($operatingHours->close_time);
        // Get the current time with timezone
        $now = Carbon::now($timezone);

        // Only adjust the open time if the date is today
        if ($date->isToday()) {
            // Round up to the nearest hour, 20:05 -> 21:00
            if ($now->minute > 0 || $now->second > 0) {
                $now->addHour()->minute(0)->second(0);
            }

            // If the openTime is before the current time, set openTime to the rounded current time, e.g. 10:00(common) -> 21:00 (current)
            if ($openTime->lessThan($now)) {
                $openTime = $now;
            }
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

        $availableStartTimes = $this->calculateAvailableStartTimes($bookings, $openTime, $closeTime, $timezone);

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

    private function calculateAvailableStartTimes($bookings, $openTime, $closeTime, $timezone): array
    {
        Log::info('Calculating available start times');
        Log::info('Comparin:g ' . $openTime->greaterThanOrEqualTo($closeTime) ? 'true' : 'false');
        //If modified open time is greater or equal than close time, return empty array, e.g. 21:00 - 21:00 means no available times
        if ($openTime->greaterThanOrEqualTo($closeTime)) {
            return [];
        }
        $availableStartTimes = [];
        $current = $openTime->copy();
        Log::info('$current', $current->toArray());
        // Создаем массив занятых временных интервалов
        $occupiedIntervals = [];

        Log::info('Calculating available start times');
        Log::info('Open Time: ' . $openTime);
        Log::info('Close Time: ' . $closeTime);

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->date . ' ' . $booking->start_time, $timezone);
            $bookingEnd = Carbon::parse($booking->date . ' ' . $booking->end_time, $timezone);
            $occupiedIntervals[] = [$bookingStart, $bookingEnd];
            Log::info('Booking Interval: ', ['start' => $bookingStart, 'timeZone' => $bookingStart->getTimezone(), 'end' => $bookingEnd]);
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

        $date = Carbon::parse($date, $timezone)->startOfDay();
        $startTime = Carbon::parse($date->toDateString() . ' ' . $startTime, $timezone);

        $operatingHours = $this->getOperatingHours($addressId, $date);

        // Extract the time portion only
        $openTime = Carbon::parse($operatingHours->open_time, $timezone);
        $closeTime = $operatingHours->close_time === '24:00' ? Carbon::parse('23:59:59', $timezone) : Carbon::parse($operatingHours->close_time, $timezone);

        $startTimeOnly = Carbon::parse($startTime->format('H:i:s'), $timezone);

        Log::info('getAvailableEndTime request: ', [
            'date' => $date,
            'startTime' => $startTime,
            'operatingHours' => $operatingHours,
            'openTime' => $openTime,
            'closeTime' => $closeTime,
            'startTimeOnly' => $startTimeOnly,

            
            'startTimeOnlyTZ' => $startTimeOnly->getTimezone(),
        ]);

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
                // Исключаем статусы cancel и expired, если pending or paid, то оставляем
                $query->whereNotIn('name', ['cancel', 'expired']);
            })
            ->orderBy('start_time', 'asc')
            ->get();

            Log::info('бронирования', $bookings->toArray());

        if ($operatingHours->mode_id == 1) {
            $date->addDays(3); // Добавляем 3 дня для режима 24/7
        }

        return $this->calculateAvailableEndTimes($bookings, $startTime, $closeTime, $date, $operatingHours->mode_id, $timezone);
    }

    private function calculateAvailableEndTimes($bookings, $startTime, $closeTime, $date, $mode, $timezone): array
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
            $bookingStart = Carbon::parse($booking->date . ' ' . $booking->start_time, $timezone);
            $bookingEnd = Carbon::parse($booking->date . ' ' . $booking->end_time, $timezone);

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
        // Parse the new datetime format for start and end times
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        // Calculate the total number of hours between start and end
        $hours = $end->diffInHours($start);

        // Fetch the enabled address prices for the given address_id
        $addressPrices = DB::table('address_prices')
            ->where('address_id', $addressId)
            ->where('is_enabled', true)
            ->orderBy('hours', 'desc')
            ->get();

        if ($addressPrices->isEmpty()) {
            throw new \Exception('Prices were not set');
        }

        // Determine the applicable price tier based on the total hours
        $applicablePrice = $addressPrices->firstWhere('hours', '<=', $hours);

        // If no specific tier is applicable, use the highest tier available
        if (!$applicablePrice) {
            $applicablePrice = $addressPrices->first();
        }

        // Calculate the total price using the applicable price per hour
        $totalPrice = $hours * $applicablePrice->price_per_hour;

        return $totalPrice;
    }

    public function bookAddress(BookingRequest $request): array
    {
        try {
            $addressId = $request->input('address_id');
            $address = Address::findOrFail($addressId);

            $timezone = $address->timezone;

            $bookingDate = Carbon::parse($request->input('date'), $timezone)->format('Y-m-d');
            $startTime = Carbon::parse($request->input('start_time'), $timezone);
            $endTime = Carbon::parse($request->input('end_time'), $timezone);
            $endDate = Carbon::parse($request->input('end_date'), $timezone)->format('Y-m-d');

            // Set the correct date for startTime and endTime
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $bookingDate . ' ' . $startTime->format('H:i:s'), $timezone);
            $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $bookingDate . ' ' . $endTime->format('H:i:s'), $timezone);

            $userWhoBooks = Auth::user();

            $this->validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime, $timezone);

            $amount = $this->getTotalCost($startTime, $endTime, $addressId);

            // Create and save the booking to get an ID
            $booking = Booking::create([
                'address_id' => $addressId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'user_id' => $userWhoBooks->id,
                'date' => $bookingDate,
                'end_date' => $endDate,
                'status_id' => 1, // studio is on pending after booking
            ]);

            // Get the studio owner
            $company = $address->company;
            $studioOwner = $company->adminCompany->user;


            // Generate the payment session
            $paymentSession = $this->paymentService->createPaymentSession($booking, $amount, $studioOwner);
            $paymentUrl = $paymentSession['payment_url'] ?? null;

            // Update the booking with the temporary payment link and expiration time
            $booking->temporary_payment_link = $paymentUrl;
            $booking->temporary_payment_link_expires_at = Carbon::now()->addMinutes( $this->paymentService::MINUTE_TO_PAY); // link expires in 20 minutes
            $booking->save();

            $userEmail = $userWhoBooks->email;
            $amount = $amount ?? null;

            // TODO: create delay 10 minutes for sending email
            dispatch(new BookingPendingJob($booking, $paymentUrl, $userEmail, $amount));
            

            return [
                'booking' => $booking,
                'session_id' => $paymentSession['session_id'],
                'payment_url' => $paymentUrl,
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
        Log::info('beforeConversion', [
            'bookingDateTimezone' => $bookingDate,
            'startTime' => $startTime,
            'endTime' => $endTime
        ]);
        // Set the timezone for the current date and time match that user timezone, assuming that server timezone is UTC
        $currentDateTime = Carbon::now($timezone);
   
        Log::info('startTimeBeforeConversion', [$startTime]);
        // Parse the booking date, start time, and end time correctly with the specified timezone
        $bookingDate = Carbon::createFromFormat('Y-m-d', $bookingDate, $timezone);
        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime, $timezone);
        $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $endTime, $timezone);
    
        // Log information for debugging
        Log::info('Current Date Time: ' . $currentDateTime->toDateTimeString() . ' Timezone: ' . $currentDateTime->getTimezone()->getName());
        Log::info('Booking Start Time: ' . $startTime->toDateTimeString() . ' Timezone: ' . $startTime->getTimezone()->getName());
        Log::info('Booking End Time: ' . $endTime->toDateTimeString() . ' Timezone: ' . $endTime->getTimezone()->getName());
        Log::info('Is booking in the past: ' . ($startTime->lt($currentDateTime) ? 'Yes' : 'No'));
    
        if ($startTime->lt($currentDateTime)) {
            throw new BookingException('Cannot book a time in the past', 422);
        }
    
        if (StudioClosure::where('address_id', $addressId)->where('closure_date', $bookingDate->toDateString())->exists()) {
            throw new BookingException('Studio is closed on this date', 422);
        }
    
        $operatingHours = $this->getOperatingHours($addressId, $bookingDate);
   
        Log::info('operationHours', ['operationHours' => $operatingHours, 'comparing' => !$operatingHours || $operatingHours->is_closed]);
        if (!$operatingHours || $operatingHours->is_closed) {
            throw new BookingException('Booking times are outside of business hours.', 422);
        }
   
        $openTime = $bookingDate->copy()->setTimeFromTimeString($operatingHours->open_time);
        $closeTime = $bookingDate->copy()->setTimeFromTimeString($operatingHours->close_time);
       
        Log::info('operationHours', ['operationHours' => $operatingHours, 'openTime' => $openTime, 'opentimeTZ' => $openTime->getTimezone(), 'closeTime' => $closeTime, 'closeTimeTZ' => $closeTime->getTimezone(), '$startTime->lt($openTime)' => $startTime->lt($openTime), '$endTime->gt($closeTime)'=>$endTime->gt($closeTime)]);
        if ($startTime->lt($openTime) || $endTime->gt($closeTime)) {
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