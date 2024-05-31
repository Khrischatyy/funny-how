<?php

namespace App\Services;

use App\Http\Requests\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PaymentService
{

    private const MINUTE_TO_PAY = 30;

//    //create subscribe session
//    public function subscribe(): Session
//    {
//        Stripe::setApiKey(env('STRIPE_SECRET'));
//
//        $user = Auth::user();
//
//        return Session::create([
//            'payment_method_types' => ['card'],
//            'line_items' => [[
//                'price_data' => [
//                    'currency' => 'usd',
//                    'product_data' => [
//                        'name' => 'Studio Subscription',
//                    ],
//                    'unit_amount' => 10,
//                ],
//                'quantity' => 1,
//            ]],
//            'mode' => 'subscription',
//            'success_url' => route('checkout.success'),
//            'cancel_url' => route('checkout.cancel'),
//        ]);
//    }


    public function makePayment($totalPrice, $bookingId)
    {
        $amountInCents = $totalPrice * 100;

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $expiresAt = now()->addMinutes(self::MINUTE_TO_PAY)->timestamp;
        $successUrl = env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $bookingId;

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Studio Booking',
                    ],
                    'unit_amount' => 50,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => env('APP_URL') . '/cancel-booking',
            'expires_at' => $expiresAt
        ]);
    }

    public function verifyPaymentSession($sessionId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = Session::retrieve($sessionId);
            return $session;
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error: ' . $e->getMessage());
            return null;
        }
    }

    public function processPaymentSuccess($sessionId, $bookingId, BookingService $bookingService)
    {
        // Verify the session
        $session = $this->verifyPaymentSession($sessionId);

        if (!$session) {
            return ['error' => 'Payment verification failed.'];
        }

        // Check if the session has expired
        if ($session->expires_at < time()) {
            return ['error' => 'Payment session has expired.'];
        }

        // Check the payment status
        if ($session->payment_status !== 'paid') {
            return ['error' => 'Payment not completed.'];
        }

        // Update the booking status to paid status
        $bookingService->updateBookingStatus($bookingId, 2);

        return ['success' => 'Payment successful and booking status updated.'];
    }
}
