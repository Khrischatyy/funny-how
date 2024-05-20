<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculatePriceRequest;
use App\Http\Requests\ReservationRequest;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function calculatePrice(CalculatePriceRequest $request)
    {
        $addressId = $request->input('address_id');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        $totalPrice = $this->bookingService->getTotalCost($startTime, $endTime, $addressId);

        return $this->sendResponse($totalPrice, 'Total price received');
    }
}
