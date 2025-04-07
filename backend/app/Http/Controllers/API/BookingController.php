<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\AvailableEndTimeRequest;
use App\Http\Requests\AvailableStartTimeRequest;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculatePriceRequest;
use App\Http\Requests\CancelBookingRequest;
use App\Http\Requests\FilterBookingHistoryRequest;
use App\Http\Requests\PaymentRequest;
use App\Services\BookingService;
use App\Services\Payment\Gateways\StripeService;
use App\Services\Payment\PaymentService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class BookingController extends BaseController
{
    public function __construct(private BookingService $bookingService, private PaymentService $paymentService)
    {}

    /**
     * @OA\Get(
     *     path="/booking-management",
     *     summary="Get future bookings",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="start_time", type="string", example="10:00:00"),
     *                         @OA\Property(property="end_time", type="string", example="12:00:00"),
     *                         @OA\Property(property="date", type="string", format="date", example="2024-04-10"),
     *                         @OA\Property(property="address_id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="updated_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="status_id", type="integer", example=1),
     *                         @OA\Property(property="address", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="latitude", type="string", example="37.609337"),
     *                             @OA\Property(property="is_favorite", type="boolean", example="true"),
     *                             @OA\Property(property="longitude", type="string", example="55.758972"),
     *                             @OA\Property(property="street", type="string", example="Газетный переулок"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="city_id", type="integer", example=2),
     *                             @OA\Property(property="company_id", type="integer", example=1),
     *                             @OA\Property(property="company", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Section"),
     *                                 @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                                 @OA\Property(property="slug", type="string", example="section"),
     *                                 @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                                 @OA\Property(property="rating", type="string", example="9.7"),
     *                                 @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                             ),
     *                             @OA\Property(property="badges", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="mixing"),
     *                                     @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                                     @OA\Property(property="pivot", type="object",
     *                                         @OA\Property(property="address_id", type="integer", example=1),
     *                                         @OA\Property(property="badge_id", type="integer", example=1)
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(property="status", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="pending"),
     *                             @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                             @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1/api/v1/history?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1/api/v1/history?page=1"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1/api/v1/history"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             ),
     *             @OA\Property(property="message", type="string", example="Bookings retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="No bookings found"),
     *     @OA\Response(response="500", description="Failed to retrieve bookings")
     * )
     *
     *
     *
     * @OA\Get(
     *     path="/history",
     *     summary="Get booking history",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="start_time", type="string", example="10:00:00"),
     *                         @OA\Property(property="end_time", type="string", example="12:00:00"),
     *                         @OA\Property(property="date", type="string", format="date", example="2024-04-10"),
     *                         @OA\Property(property="address_id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="updated_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="status_id", type="integer", example=1),
     *                         @OA\Property(property="address", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="latitude", type="string", example="37.609337"),
     *                             @OA\Property(property="is_favorite", type="boolean", example="true"),
     *                             @OA\Property(property="longitude", type="string", example="55.758972"),
     *                             @OA\Property(property="street", type="string", example="Газетный переулок"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="city_id", type="integer", example=2),
     *                             @OA\Property(property="company_id", type="integer", example=1),
     *                             @OA\Property(property="company", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Section"),
     *                                 @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                                 @OA\Property(property="slug", type="string", example="section"),
     *                                 @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                                 @OA\Property(property="rating", type="string", example="9.7"),
     *                                 @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                             ),
     *                             @OA\Property(property="badges", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="mixing"),
     *                                     @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                                     @OA\Property(property="pivot", type="object",
     *                                         @OA\Property(property="address_id", type="integer", example=1),
     *                                         @OA\Property(property="badge_id", type="integer", example=1)
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(property="status", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="pending"),
     *                             @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                             @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1/api/v1/history?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1/api/v1/history?page=1"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1/api/v1/history"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             ),
     *             @OA\Property(property="message", type="string", example="Bookings retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="No bookings found"),
     *     @OA\Response(response="500", description="Failed to retrieve bookings")
     * )
     */
    public function getBookings(string $type): JsonResponse
    {
        try {
            $userId = Auth::id();
            $bookings = $this->bookingService->getBookings($userId, $type);

            return $this->sendResponse($bookings, 'Bookings retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve bookings.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/history/filter",
     *     summary="Filter booking history",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-06-10"),
     *             @OA\Property(property="time", type="string", format="time", example="10:00"),
     *             @OA\Property(property="search", type="string", example="Studio Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Filtered bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="start_time", type="string", example="10:00:00"),
     *                         @OA\Property(property="end_time", type="string", example="12:00:00"),
     *                         @OA\Property(property="date", type="string", format="date", example="2024-04-10"),
     *                         @OA\Property(property="address_id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="updated_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="status_id", type="integer", example=1),
     *                         @OA\Property(property="address", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="latitude", type="string", example="37.609337"),
     *                             @OA\Property(property="longitude", type="string", example="55.758972"),
     *                             @OA\Property(property="street", type="string", example="Газетный переулок"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="city_id", type="integer", example=2),
     *                             @OA\Property(property="company_id", type="integer", example=1),
     *                             @OA\Property(property="company", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Section"),
     *                                 @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                                 @OA\Property(property="slug", type="string", example="section"),
     *                                 @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                                 @OA\Property(property="rating", type="string", example="9.7"),
     *                                 @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                             ),
     *                             @OA\Property(property="badges", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="mixing"),
     *                                     @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                                     @OA\Property(property="pivot", type="object",
     *                                         @OA\Property(property="address_id", type="integer", example=1),
     *                                         @OA\Property(property="badge_id", type="integer", example=1)
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(property="status", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="pending"),
     *                             @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                             @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1/api/v1/history/filter?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1/api/v1/history/filter?page=1"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1/api/v1/history/filter"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             ),
     *             @OA\Property(property="message", type="string", example="Filtered bookings retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="No bookings found"),
     *     @OA\Response(response="500", description="Failed to retrieve bookings")
     * )
     *
     *
     * @OA\Post(
     *     path="/booking-management/filter",
     *     summary="Filter future bookings",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="date", type="string", format="date", example="2024-06-10"),
     *             @OA\Property(property="time", type="string", format="time", example="10:00"),
     *             @OA\Property(property="search", type="string", example="Studio Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Filtered bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="start_time", type="string", example="10:00:00"),
     *                         @OA\Property(property="end_time", type="string", example="12:00:00"),
     *                         @OA\Property(property="date", type="string", format="date", example="2024-04-10"),
     *                         @OA\Property(property="address_id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="updated_at", type="string", nullable=true, example=null),
     *                         @OA\Property(property="status_id", type="integer", example=1),
     *                         @OA\Property(property="address", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="latitude", type="string", example="37.609337"),
     *                             @OA\Property(property="longitude", type="string", example="55.758972"),
     *                             @OA\Property(property="street", type="string", example="Газетный переулок"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                             @OA\Property(property="city_id", type="integer", example=2),
     *                             @OA\Property(property="company_id", type="integer", example=1),
     *                             @OA\Property(property="company", type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Section"),
     *                                 @OA\Property(property="logo", type="string", example="https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg"),
     *                                 @OA\Property(property="slug", type="string", example="section"),
     *                                 @OA\Property(property="founding_date", type="string", format="date", example="2020-12-10"),
     *                                 @OA\Property(property="rating", type="string", example="9.7"),
     *                                 @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                             ),
     *                             @OA\Property(property="badges", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="name", type="string", example="mixing"),
     *                                     @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                                     @OA\Property(property="pivot", type="object",
     *                                         @OA\Property(property="address_id", type="integer", example=1),
     *                                         @OA\Property(property="badge_id", type="integer", example=1)
     *                                     )
     *                                 )
     *                             )
     *                         ),
     *                         @OA\Property(property="status", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="pending"),
     *                             @OA\Property(property="created_at", type="string", nullable=true, example=null),
     *                             @OA\Property(property="updated_at", type="string", nullable=true, example=null)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://127.0.0.1/api/v1/history/filter?page=1"),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="last_page_url", type="string", example="http://127.0.0.1/api/v1/history/filter?page=1"),
     *                 @OA\Property(property="links", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="url", type="string", nullable=true, example=null),
     *                         @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                         @OA\Property(property="active", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="path", type="string", example="http://127.0.0.1/api/v1/history/filter"),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="to", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=1)
     *             ),
     *             @OA\Property(property="message", type="string", example="Filtered bookings retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="No bookings found"),
     *     @OA\Response(response="500", description="Failed to retrieve bookings")
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

            return $this->sendResponse($bookings, 'Filtered bookings retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve bookings.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/address/reservation/start-time",
     *     summary="Get available start time for reservation",
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="The date for the reservation"
     *     ),
     *     @OA\Parameter(
     *         name="address_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Available start time retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="13:00"),
     *                 @OA\Items(type="string", example="14:00"),
     *                 @OA\Items(type="string", example="15:00"),
     *                 @OA\Items(type="string", example="16:00"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Available start time retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function getReservationAvailableStartTime(AvailableStartTimeRequest $request): JsonResponse
    {
        $date = $request->query('date');
        $roomId = $request->query('room_id');
        $availableStartTimes = $this->bookingService->getAvailableStartTime($date, $roomId);

        return $this->sendResponse($availableStartTimes, 'Available start time retrieved successfully.');
    }

    /**
     * @OA\Get(
     *     path="/address/reservation/end-time",
     *     summary="Get available end time for reservation",
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="The date for the reservation"
     *     ),
     *     @OA\Parameter(
     *         name="address_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\Parameter(
     *         name="start_time",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="time"),
     *         description="The start time for the reservation"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Available end time retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="13:00"),
     *                 @OA\Items(type="string", example="14:00"),
     *                 @OA\Items(type="string", example="15:00"),
     *                 @OA\Items(type="string", example="16:00"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Available end time retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function getReservationAvailableEndTime(AvailableEndTimeRequest $request): JsonResponse
    {
        $date = $request->query('date');
        $roomId = $request->query('room_id');
        $startTime = $request->query('start_time');

        $availableEndTime = $this->bookingService->getAvailableEndTime($date, $roomId, $startTime);

        return $this->sendResponse($availableEndTime, 'Available end time retrieved successfully.');
    }

    /**
     * @OA\Post(
     *     path="/address/reservation",
     *     summary="Book an address",
     *     tags={"Booking"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "date", "start_time", "end_time"},
     *             @OA\Property(property="address_id", type="integer", example=1),
     *             @OA\Property(property="date", type="string", format="date", example="2024-06-10"),
     *             @OA\Property(property="start_time", type="string", format="time", example="10:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="12:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Studio booked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="booking", type="object"),
     *                 @OA\Property(property="payment_url", type="string", example="https://checkout.stripe.com/pay/cs_live_a1Tm4kDG5a4cbU3lfeVOpfGoMbFYCzMYnVKBCJneJWOrzw5KkwAwEfN0mw")
     *             ),
     *             @OA\Property(property="message", type="string", example="Studio booked successfully"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function bookAddress(BookingRequest $bookingRequest): JsonResponse
    {
        try {
            $bookingData = $this->bookingService->bookAddress($bookingRequest);

            return $this->sendResponse($bookingData, 'Studio booked successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to book address.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/address/payment-success",
     *     summary="Handle payment success",
     *     tags={"Payment"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"session_id", "booking_id"},
     *             @OA\Property(property="session_id", type="string", example="session_id_example"),
     *             @OA\Property(property="booking_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Payment processed successfully"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function paymentSuccess(Request $request): JsonResponse
    {
        try {
            $bookingId = $request->input('booking_id');

            $booking = $this->bookingService->getBookingById($bookingId);
            $studioOwner = $booking->room->address->company->adminCompany->user;
            $paymentGateway = $studioOwner->payment_gateway;
            $charge = $booking->charge;

            if (!$charge || !$charge->stripe_session_id) {
                return $this->sendError('Missing Stripe session ID for booking.', 400);
            }

            $sessionId = $charge->stripe_session_id;

            $result = $this->paymentService->processPaymentSuccess($sessionId, $bookingId, $paymentGateway, $studioOwner);


            if (isset($result['error'])) {
                return $this->sendError($result['error'], 400);
            }

            return $this->sendResponse([], $result['message']);
        } catch (Exception $e) {
            return $this->sendError('Failed to update booking status after payment.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/address/calculate-price",
     *     summary="Calculate the total price for booking",
     *     tags={"Booking"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address_id", "start_time", "end_time"},
     *             @OA\Property(property="address_id", type="integer", example=1),
     *             @OA\Property(property="start_time", type="string", format="time", example="10:00"),
     *             @OA\Property(property="end_time", type="string", format="time", example="12:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Total price received",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="number", format="float", example=100.00),
     *             @OA\Property(property="message", type="string", example="Total price received"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="500", description="Internal Server Error")
     * )
     */
    public function calculatePrice(CalculatePriceRequest $request): JsonResponse
    {
        $roomId = $request->input('room_id');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $engineerId = $request->input('engineer_id'); // Retrieve the engineer_id from the request

        $totalPrice = $this->bookingService->getTotalCost($startTime, $endTime, $roomId, $engineerId);

        return $this->sendResponse($totalPrice, 'Total price received');
    }

    /**
     * @OA\Post(
     *     path="/cancel-booking",
     *     summary="Cancel a booking",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id"},
     *             @OA\Property(property="booking_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Booking cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking cancelled successfully.")
     *         )
     *     ),
     *     @OA\Response(response="404", description="Booking not found"),
     *     @OA\Response(response="500", description="Failed to cancel booking")
     * )
     */
    public function cancelBooking(CancelBookingRequest $request): JsonResponse
    {
        try {
            $cancellationData = $this->bookingService->cancelBooking($request->input('booking_id'));

            return $this->sendResponse($cancellationData, 'Booking cancelled successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Booking not found.', 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to cancel booking.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function handleWebhook(Request $request)
    {
        // Log the webhook payload for testing
        \Log::info('Webhook received:', $request->all());

        // Process the webhook here
        // ...

        return response()->json(['status' => 'success'], 200);
    }
}
