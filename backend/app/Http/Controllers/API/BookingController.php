<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AvailableEndTimeRequest;
use App\Http\Requests\AvailableStartTimeRequest;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculatePriceRequest;
use App\Http\Requests\FilterBookingHistoryRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    public function __construct(private BookingService $bookingService, private SubscriptionService $subscriptionService)
    {}
    public function getBookingHistory(): JsonResponse
    {
        try {
            $userId = Auth::id(); // Assuming the user is authenticated

            $bookingHistory = $this->bookingService->getBookingHistory($userId);

            if ($bookingHistory->isEmpty()) {
                return $this->sendError('No booking history found.', 404);
            }

            return $this->sendResponse($bookingHistory, 'Booking history retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve booking history.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function filterBookingHistory(FilterBookingHistoryRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id(); // Assuming the user is authenticated

            $status = $request->input('status');
            $date = $request->input('date');
            $time = $request->input('time');
            $search = $request->input('search');

            $bookingHistory = $this->bookingService->filterBookingHistory($userId, $status, $date, $time, $search);

            if ($bookingHistory->isEmpty()) {
                return $this->sendError('No booking history found.', 404);
            }

            return $this->sendResponse($bookingHistory, 'Filtered booking history retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve booking history.', 500, ['error' => $e->getMessage()]);
        }
    }

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
