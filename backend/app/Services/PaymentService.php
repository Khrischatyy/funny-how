<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Charge;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class PaymentService
{

    private const MINUTE_TO_PAY = 30;

    public function createPaymentSession(Booking $booking, int $amountOfMoney): array
    {
        try {
            $user = Auth::user();

            $session = $user->checkoutCharge(50, 'Payment for studio reservation', 1, [
                'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
                'cancel_url' => env('APP_URL') . '/cancel-booking',
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);

//            $session = $user->checkoutCharge($amountOfMoney * 100, 'Payment for studio reservation', 1, [
//                'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
//                'cancel_url' => env('APP_URL') . '/cancel-booking',
//                'metadata' => [
//                    'booking_id' => $booking->id,
//                ],
//            ]);

            Log::info('Stripe Session created: ' . json_encode($session));

            // Сохранение начальной информации о платеже
//            $this->createCharge($booking, $session->id, $amountOfMoney, 'usd');
            $this->createCharge($booking, $session->id, 50, 'usd');

            return [
                'session_id' => $session->id,
                'payment_url' => $session->url,
            ];

        } catch (Exception $e) {
            Log::error("Payment failed: " . $e->getMessage());
            throw new Exception("Payment failed: " . $e->getMessage());
        }
    }

    public function saveCharge(Booking $booking, string $paymentIntent, string $sessionId, int $amount, string $currency): void
    {
        Charge::create([
            'booking_id' => $booking->id,
            'stripe_session_id' => $sessionId,
            'stripe_payment_intent' => $paymentIntent,
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }

    public function refundPayment(Booking $booking)
    {
        try {
            $charge = Charge::where('booking_id', $booking->id)->firstOrFail();
            $user = Auth::user();
            $refund = $user->refund($charge->stripe_payment_intent);

            $charge->update([
                'refund_id' => $refund->id,
                'refund_status' => $refund->status,
            ]);

        } catch (Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            throw new Exception("Failed to process refund: " . $e->getMessage());
        }
    }

    public function verifyPaymentSession($sessionId)
    {
        try {
            return \Stripe\Checkout\Session::retrieve($sessionId);
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

        $this->updateCharge($session->id, $session->payment_intent);

        return ['success' => 'Payment successful and booking status updated.'];
    }

    public function updateCharge(string $sessionId, string $paymentIntent): void
    {
        $charge = Charge::where('stripe_session_id', $sessionId)->firstOrFail();
        $charge->update([
            'stripe_payment_intent' => $paymentIntent,
        ]);
    }

    public function createCharge(Booking $booking, string $sessionId, int $amount, string $currency): void
    {
        Charge::create([
            'booking_id' => $booking->id,
            'stripe_session_id' => $sessionId,
            'amount' => $amount,
            'currency' => $currency,
        ]);
    }
}
