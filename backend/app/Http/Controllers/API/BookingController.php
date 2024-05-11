<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ReservationRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends BaseController
{
    public function __construct(private BookingService $bookingService)
    {}

    public function getAllReservations(ReservationRequest $reservationRequest): JsonResponse
    {
        $data = $this->bookingService->getAllReservations($reservationRequest);

        return $this->sendResponse($data, 'Available slots received');
    }

    public function bookStudio(BookingRequest $bookingRequest): JsonResponse
    {
        $booking = $this->bookingService->bookStudio($bookingRequest);

        return $this->sendResponse($booking, 'Studio booked successfully');
    }
}
