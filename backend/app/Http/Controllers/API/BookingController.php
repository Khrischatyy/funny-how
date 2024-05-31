<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AvailableEndTimeRequest;
use App\Http\Requests\AvailableStartTimeRequest;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculatePriceRequest;
use App\Http\Requests\FilterBookingHistoryRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\ReservationRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="Booking API",
 *     version="1.0.0"
 * )
 */
class BookingController extends BaseController
{
    public function __construct(private BookingService $bookingService, private PaymentService $paymentService)
    {}

    /**
     * @OA\Get(
     *     path="/api/bookings/{type}",
     *     summary="Get bookings",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Type of bookings to retrieve (e.g. history, upcoming)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No bookings found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve bookings"
     *     )
     * )
     */
    public function getBookings(string $type): JsonResponse
    {
        try {
            $userId = Auth::id();
            $bookings = $this->bookingService->getBookings($userId, $type);

            if ($bookings->isEmpty()) {
                return $this->sendError('No bookings found.', 404);
            }

            return $this->sendResponse($bookings, 'Bookings retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve bookings.', 500, ['error' => $e->getMessage()]);
        }
    }




    /**
     * @OA\Post(
     *     path="/api/bookings/filter/{type}",
     *     summary="Filter bookings",
     *     tags={"Bookings"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Type of bookings to filter (e.g. history, upcoming)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered bookings retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No bookings found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve bookings"
     *     )
     * )
     */
    public function filterBookings(FilterBookingHistoryRequest $request, string $type): JsonResponse
    {
        try {
            $userId = Auth::id();
            $status = $request->input('status');
            $date = $request->input('date');
            $time = $request->input('time');
            $search = $request->input('search');

            $bookings = $this->bookingService->filterBookings($userId, $status, $date, $time, $search, $type);

            if ($bookings->isEmpty()) {
                return $this->sendError('No bookings found.', 404);
            }

            return $this->sendResponse($bookings, 'Filtered bookings retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve bookings.', 500, ['error' => $e->getMessage()]);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/reservation/available-start-time",
     *     summary="Get reservation available start time",
     *     tags={"Reservations"},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="Date for the reservation",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="address_id",
     *         in="query",
     *         required=true,
     *         description="ID of the address",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available start time retrieved successfully",
     *     )
     * )
     */
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


        $session = $this->paymentService->makePayment($totalCost, $booking->id);

        return $this->sendResponse(['booking' => $booking, 'payment_session' => $session], 'Studio booked successfully');
    }

    public function paymentSuccess(PaymentRequest $request): JsonResponse
    {
        try {
            $sessionId = $request->input('session_id');
            $bookingId = $request->input('booking_id');

            $result = $this->paymentService->processPaymentSuccess($sessionId, $bookingId, $this->bookingService);

            if (isset($result['error'])) {
                return $this->sendError($result['error'], 400);
            }

            return $this->sendResponse([], $result['success']);
        } catch (Exception $e) {
            return $this->sendError('Failed to update booking status after payment.', 500, ['error' => $e->getMessage()]);
        }
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
