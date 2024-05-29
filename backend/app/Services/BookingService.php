<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Exceptions\OperatingHourException;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\StudioClosure;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
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

        $openTime = $date->copy()->setTimeFromTimeString($operatingHours->open_time);
        $closeTime = $date->copy()->setTimeFromTimeString($operatingHours->close_time);

        if ($startTime->lt($openTime) || $startTime->gte($closeTime)) {
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
        ]);
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

    private function getOperatingHours(int $addressId, Carbon $bookingDate): OperatingHour
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

    private function regular($operatingHours, $dayOfWeek)
    {
        return $operatingHours->where('day_of_week', $dayOfWeek);
    }
}
