<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\BookingRequest;
use App\Models\Address;
use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;

class BookingController extends BaseController
{
    public function __construct(public BookingService $bookingService)
    {}

    public function getBookingByAddressId(BookingRequest $requestBooking)
    {
        $addressId = $requestBooking->input('address_id');
        $day = Carbon::create($requestBooking->input('day'));

        $bookings = $this->bookingService->getFreeSlots($addressId, $day);

        return $this->sendResponse($bookings, 'Free working time received');
    }

    public function book(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'studio_id' => 'required|exists:studios,id',
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s|after:now',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        $studio = Address::find($data['studio_id']);
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        // Проверка часов работы студии
        $openTime = Carbon::parse($studio->open_time);
        $closeTime = Carbon::parse($studio->close_time);
        if ($startTime->lt($openTime) || $endTime->gt($closeTime) || $endTime->lt($startTime)) {
            return response()->json(['message' => 'Время бронирования вне часов работы студии'], 400);
        }

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
}
