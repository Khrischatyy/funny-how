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
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;

class BookingController extends BaseController
{
    public function __construct(public BookingService $bookingService)
    {}

//    public function getAllReservations(ReservationRequest $reservationRequest): JsonResponse
//    {
//        $addressId = $reservationRequest->input('address_id');
//        $day = Carbon::create($reservationRequest->input('date'));
//
//        $bookings = $this->bookingService->getFreeSlots($addressId, $day);
//
//        return $this->sendResponse($bookings, 'Free working time received');
//    }

    public function book(Request $request)
    {

        $studio = Address::find($data['studio_id']);
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);



        // Проверка на пересечение с существующими бронированиями
        if (Booking::where('studio_id', $data['studio_id'])
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime]);
            })->exists()) {
            return response()->json(['message' => 'Студия уже забронирована на выбранное время'], 400);
        }

        // Создание бронирования
        $booking = Booking::create($data);

        return response()->json($booking, 201);
    }

    public function bookStudio(BookingRequest $bookingRequest)
    {
        $addressId = $bookingRequest->input('address_id');
        $bookingDate = Carbon::parse($bookingRequest->input('date'));  // e.g., '2023-08-23'
        $startTime = Carbon::parse($bookingRequest->input('start_time'));  // e.g., '11:00'
        $endTime = Carbon::parse($bookingRequest->input('end_time'));  // e.g., '13:00'


        // Проверка, не закрыта ли студия в выбранный день
        if (StudioClosure::where('address_id', $addressId)->where('closure_date', $startTime->toDateString())->exists()) {
            throw new BookingException('Studio is closed at this date', 422);
        }

        // Проверка операционных часов студии
        $dayOfWeek = $startTime->dayOfWeek;

        $operatingHours = OperatingHour::where('address_id', $addressId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$operatingHours || $operatingHours->is_closed) {
            throw new BookingException('Studio is closed on this date', 422);
        }

        // Проверка, входит ли время бронирования в рабочее время
        $openingTime = Carbon::parse($operatingHours->open_time);
        $closingTime = Carbon::parse($operatingHours->close_time);
        if ($startTime->lt($openingTime) || $endTime->gt($closingTime) || $endTime->lt($startTime)) {
            throw new BookingException('Booking times are outside of business hours.', 422);
        }

        $booking = new Booking([
            'address_id' => $addressId,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        $booking->save();

        dd('ok');

        // Проверяем, нет ли пересечения с другими бронированиями
        if (Booking::where('studio_id', $studioId)
            ->whereDate('start_time', '=', $bookingDate->format('Y-m-d'))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime]);
            })->exists()) {
            throw new BookingException('Studio is already booked for the requested time slot', 400);
        }

        // Создание бронирования
        $booking = new Booking([
            'studio_id' => $studioId,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        $booking->save();


        return response()->json(['message' => 'Бронирование успешно создано.'], 200);
    }
}
