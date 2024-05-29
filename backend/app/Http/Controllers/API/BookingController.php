<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AvailableEndTimeRequest;
use App\Http\Requests\AvailableStartTimeRequest;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculatePriceRequest;
use App\Http\Requests\ReservationRequest;
use App\Services\BookingService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    public function __construct(private BookingService $bookingService, private SubscriptionService $subscriptionService)
    {}

    public function getReservationAvailableStartTime(AvailableStartTimeRequest $request): JsonResponse
    {
        $date = $request->query('date');
        $addressId = $request->query('address_id');
        $availableStartTime = $this->bookingService->getAvailableStartTime($date, $addressId);

        return $this->sendResponse($availableStartTime, 'Available start time retrieved successfully.');
    }

    public function getReservationAvailableEndTime(AvailableEndTimeRequest $request): JsonResponse
    {
        $date = $request->query('date');
        $addressId = $request->query('address_id');
        $startTime = $request->query('start_time');

        $availableEndTime = $this->bookingService->getAvailableEndTime($date, $addressId, $startTime);

        return $this->sendResponse($availableEndTime, 'Available end time retrieved successfully.');
    }

    public function bookAddress(BookingRequest $bookingRequest): JsonResponse
    {
        $booking = $this->bookingService->bookAddress($bookingRequest);
        $totalCost = $this->bookingService->getTotalCost(
            $bookingRequest->input('start_time'),
            $bookingRequest->input('end_time'),
            $bookingRequest->input('address_id')
        );


        $session = $this->subscriptionService->makePayment($totalCost);

        return $this->sendResponse(['booking' => $booking, 'payment_session' => $session], 'Studio booked successfully');
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
