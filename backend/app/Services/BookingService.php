<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Exceptions\OperatingHourException;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\OperatingHour;
use App\Models\StudioClosure;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    private const BOOKING_PAGINATE_COUNT = 15;

    public function getBookings($userId, $type)
    {
        $query = Booking::where('user_id', $userId)->with(['user']);

        if ($type === 'history') {
            $query->where('date', '<', now());
        } else {
            $query->where('date', '>=', now());
        }

        return $query->with(['address.company', 'address.photos', 'address.badges', 'status'])->paginate(self::BOOKING_PAGINATE_COUNT);
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

        return $query->with(['address.company', 'address.badges', 'status'])->paginate(self::BOOKING_PAGINATE_COUNT);
    }

    public function getAvailableStartTime(string $date, int $addressId): array
    {
        $date = Carbon::parse($date);

        $operatingHours = $this->getOperatingHours($addressId, $date);

        $openTime = $date->copy()->setTimeFromTimeString($operatingHours->open_time);
        $closeTime = $date->copy()->setTimeFromTimeString($operatingHours->close_time);

        $bookings = Booking::where('address_id', $addressId)
            ->where('date', $date->toDateString())
            ->orderBy('start_time', 'asc')
            ->get();

        return $this->calculateAvailableStartTimes($bookings, $openTime, $closeTime);
    }

    private function calculateAvailableStartTimes($bookings, $openTime, $closeTime): array
    {
        $availableStartTimes = [];
        $possibleStartTimes = [];
        $current = $openTime->copy();

        while ($current->lt($closeTime)) {
            $possibleStartTimes[] = $current->copy();
            $current->addHour();
        }

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_time);
            $bookingEnd = Carbon::parse($booking->end_time);

            foreach ($possibleStartTimes as $key => $startTime) {
                if ($startTime->gte($bookingStart) && $startTime->lt($bookingEnd)) {
                    unset($possibleStartTimes[$key]);
                }
            }
        }

        foreach ($possibleStartTimes as $startTime) {
            $availableStartTimes[] = $startTime->format('H:i');
        }

        return $availableStartTimes;
    }

    public function getAvailableEndTime(string $date, int $addressId, string $startTime): array
    {
        $date = Carbon::parse($date);
        $startTime = Carbon::parse($startTime);

        $operatingHours = $this->getOperatingHours($addressId, $date);

        // Extract the time portion only
        $openTime = Carbon::createFromFormat('H:i:s', $operatingHours->open_time);
        $closeTime = Carbon::createFromFormat('H:i:s', $operatingHours->close_time);
        $startTimeOnly = Carbon::createFromFormat('H:i:s', $startTime->format('H:i:s'));

        // Compare times without date
        if ($startTimeOnly->lt($openTime) || $startTimeOnly->gte($closeTime)) {
            throw new BookingException('Start time is outside of operating hours', 422);
        }

        $bookings = Booking::where('address_id', $addressId)
            ->where('date', $date->toDateString())
            ->orderBy('start_time', 'asc')
            ->get();

        return $this->calculateAvailableEndTimes($bookings, $startTime, $closeTime);
    }

    private function calculateAvailableEndTimes($bookings, $startTime, $closeTime): array
    {
        $availableEndTimes = [];
        $current = $startTime->copy()->addHour();

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_time);
            $bookingEnd = Carbon::parse($booking->end_time);

            // If the start time is before the booking start
            if ($startTime->lt($bookingStart)) {
                while ($current->lte($bookingStart) && $current->lt($closeTime)) {
                    $availableEndTimes[] = $current->format('H:i');
                    if ($current->eq($bookingStart)) {
                        // Stop adding end times if we reach the booking start time
                        return $availableEndTimes;
                    }
                    $current->addHour();
                }
            }

            // Adjust the current time if the start time falls within an existing booking
            if ($startTime->lt($bookingEnd) && $current->gte($bookingStart) && $current->lt($bookingEnd)) {
                $current = $bookingEnd->copy()->addHour();
            }
        }

        // If no bookings block the remaining time, add the rest of the hours until close time
        while ($current->lt($closeTime)) {
            $availableEndTimes[] = $current->format('H:i');
            $current->addHour();
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

    public function bookAddress(BookingRequest $request): Booking
    {
        $addressId = $request->input('address_id');
        $bookingDate = Carbon::parse($request->input('date'));
        $startTime = Carbon::parse($request->input('start_time'));
        $endTime = Carbon::parse($request->input('end_time'));
        $userWhoBooks = Auth::user();

        $this->validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime);

        return Booking::create([
            'address_id' => $addressId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'user_id' => $userWhoBooks->id,
            'total_cost' => $this->getTotalCost($startTime, $endTime, $addressId),
            'date' => $bookingDate->format('Y-m-d'),
            'status_id' => 1, // studio is on pending after booking
        ]);
    }

    public function updateBookingStatus($bookingId, $statusId)
    {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $booking->status_id = $statusId;
            $booking->save();
        }
    }

    private function validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime)
    {
        // Check if booking date and time are in the past
        $currentDateTime = now();
        $bookingDateTime = $bookingDate->setTimeFrom($startTime);

        if ($bookingDateTime->lt($currentDateTime)) {
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
        //3,4 это моды weekdays и weekends
        //надо будет переделать, че то тут хуйня какая-то

        return match ($firstLineOperatingHours->mode_id) {
            1, 2 => $firstLineOperatingHours,
            3, 4 => $this->regular($operatingHours, $bookingDate->dayOfWeek)->firstOrFail(),
            default => throw new OperatingHourException("Invalid operating hours mode", 400),
        };
    }

    public function cancelBooking(int $bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            $booking->status_id = 3;
            $booking->save();

            $user = Auth::user();

            // Возвращаем все активные бронирования
            return Booking::where('user_id', $user->id)->get();

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
