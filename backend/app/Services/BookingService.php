<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\StudioClosure;
use App\Models\User;
use Carbon\Carbon;

class BookingService
{
    public function getAllReservations(ReservationRequest $request): array
    {
        $date = $request->input('date');
        $addressId = $request->input('address_id');
        $bookings = Booking::where('address_id', $addressId)
            ->where('date', $date)
            ->orderBy('start_time', 'asc')
            ->get();

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $operatingHours = $this->getOperatingHours($addressId, $dayOfWeek);

        $openTime = Carbon::parse($date . ' ' . $operatingHours->open_time);
        $closeTime = Carbon::parse($date . ' ' . $operatingHours->close_time);


        return $this->calculateAvailableSlots($bookings, $openTime, $closeTime);
    }

    private function calculateAvailableSlots($bookings, $openTime, $closeTime): array
    {
        $availableSlots = [];
        $previousEndTime = $openTime;

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_time);
            $bookingEnd = Carbon::parse($booking->end_time);

            if ($previousEndTime->lt($bookingStart)) {
                $availableSlots[] = [
                    'start_time' => $previousEndTime->format('H:i'),
                    'end_time' => $bookingStart->format('H:i')
                ];
            }
            $previousEndTime = $bookingEnd;
        }

        if ($previousEndTime->lt($closeTime)) {
            $availableSlots[] = [
                'start_time' => $previousEndTime->format('H:i'),
                'end_time' => $closeTime->format('H:i')
            ];
        }

        return $availableSlots;
    }

    public function bookStudio(BookingRequest $request): Booking
    {
        $addressId = $request->input('address_id');
        $bookingDate = Carbon::parse($request->input('date'));
        $startTime = Carbon::parse($request->input('start_time'));
        $endTime = Carbon::parse($request->input('end_time'));
        $userWhoBooks = User::find((int) $request->input('user_id'));

        $this->validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime);

        return Booking::create([
            'address_id' => $addressId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'user_id' => $userWhoBooks->id,
            'total_cost' => 30, // This should be calculated based on business logic
            'date' => $bookingDate->format('Y-m-d'),
        ]);
    }

    private function validateStudioAvailability($addressId, $bookingDate, $startTime, $endTime)
    {
        if (StudioClosure::where('address_id', $addressId)->where('closure_date', $bookingDate->toDateString())->exists()) {
            throw new BookingException('Studio is closed on this date', 422);
        }

        $operatingHours = $this->getOperatingHours($addressId, $bookingDate->dayOfWeek);

        if (!$operatingHours || $operatingHours->is_closed) {
            throw new BookingException('Booking times are outside of business hours.', 422);
        }

        if ($startTime->lt($operatingHours->open_time) || $endTime->gt($operatingHours->close_time)) {
            throw new BookingException("Booking times are outside of business hours. Studio opens at {$operatingHours->open_time} and closes at {$operatingHours->close_time}", 422);
        }

        if ($this->isTimeSlotTaken($addressId, $startTime, $endTime)) {
            throw new BookingException('Studio is already booked for the requested time slot', 400);
        }
    }

    private function isTimeSlotTaken($addressId, $startTime, $endTime): bool
    {
        return Booking::where('address_id', $addressId)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    private function getOperatingHours($addressId, $dayOfWeek)
    {
        return OperatingHour::where('address_id', $addressId)->where('day_of_week', $dayOfWeek)->first();
    }
}
