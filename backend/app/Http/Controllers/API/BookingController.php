<?php

namespace App\Http\Controllers\API;

use App\Exceptions\BookingException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Address;
use App\Models\Booking;
use App\Models\OperatingHour;
use App\Models\StudioClosure;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends BaseController
{
    public function __construct(public BookingService $bookingService)
    {}

    public function getAllReservations(ReservationRequest $reservationRequest)
    {
        $date = $reservationRequest->input('date');
        $addressId = $reservationRequest->input('address_id');

        // Get all bookings for the specified date
        $bookings = Booking::where('address_id', $addressId)
            ->where('date', $date)
            ->orderBy('start_time', 'asc')
            ->get();

        // Assume the studio's working hours are from 08:00 to 22:00
        $openTime = Carbon::parse($date . ' 08:00');
        $closeTime = Carbon::parse($date . ' 22:00');

        $availableSlots = [];
        $previousEndTime = $openTime;

        // Find all free slots
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($date . ' ' . $booking->start_time);
            $bookingEnd = Carbon::parse($date . ' ' . $booking->end_time);

            // If there is free time between the end of the previous booking and the start of the new one
            if ($previousEndTime->lt($bookingStart)) {
                $availableSlots[] = [
                    'start_time' => $previousEndTime->format('H:i'),
                    'end_time' => $bookingStart->format('H:i')
                ];
            }

            // Update the end time of the previous booking
            $previousEndTime = $bookingEnd;
        }

        // Add the last free slot at the end of the day, if there is any
        if ($previousEndTime->lt($closeTime)) {
            $availableSlots[] = [
                'start_time' => $previousEndTime->format('H:i'),
                'end_time' => $closeTime->format('H:i')
            ];
        }

        return $this->sendResponse([
            'date' => $date,
            'address_id' => $addressId,
            'available_slots' => $availableSlots
        ], 'Available slots received');
    }

    public function bookStudio(BookingRequest $bookingRequest)
    {
        $addressId = $bookingRequest->input('address_id');
        $bookingDate = Carbon::parse($bookingRequest->input('date'));
        $startTime = Carbon::parse($bookingRequest->input('date') . ' ' . $bookingRequest->input('start_time'));
        $endTime = Carbon::parse($bookingRequest->input('date') . ' ' . $bookingRequest->input('end_time'));
        $userWhoBooks = User::where('id', (int) $bookingRequest->input('user_id'))->first();

        // Проверка, не закрыта ли студия в выбранный день
        if (StudioClosure::where('address_id', $addressId)
            ->where('closure_date', $bookingDate->toDateString())
            ->exists()) {
            throw new BookingException('Studio is closed on this date', 422);
        }

        // Проверка операционных часов студии
        $dayOfWeek = $bookingDate->dayOfWeek;
        $operatingHours = OperatingHour::where('address_id', $addressId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$operatingHours || $operatingHours->is_closed) {
            throw new BookingException('Studio is closed on this date', 422);
        }

        // Проверка, входит ли время бронирования в рабочее время
        $openingTime = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $operatingHours->open_time);
        $closingTime = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $operatingHours->close_time);
        if ($startTime->lt($openingTime) || $endTime->gt($closingTime) || $endTime->lt($startTime)) {
            throw new BookingException('Booking times are outside of business hours.', 422);
        }

        // Проверяем, нет ли пересечения с другими бронированиями
        if (Booking::where('address_id', $addressId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Проверяем, что новое время начала строго раньше времени окончания других бронирований
                    // и что новое время окончания строго до начала других бронирований
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })->exists()) {
            throw new BookingException('Studio is already booked for the requested time slot', 400);
        }

        // Создание бронирования
        $booking = Booking::create([
            'address_id' => $addressId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'user_id' => $userWhoBooks->id,
            'total_cost' => 30,
            'date' => $bookingDate->format('Y-m-d'),
        ]);
        $booking->save();

        return $this->sendResponse($booking, 'Studio booked successfully');
    }
}
