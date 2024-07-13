<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Charge;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PaymentService
{
    private const MINUTE_TO_PAY = 30;

    public function createPaymentSession(Booking $booking, int $amountOfMoney): array
    {
        try {
            $user = Auth::user();

//            $session = $user->checkoutCharge($amountOfMoney * 100, 'Payment for studio reservation', 1, [
                $session = $user->checkoutCharge(50, 'Payment for studio reservation', 1, [
                'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
                'cancel_url' => env('APP_URL') . '/cancel-booking',
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);


            // Создание начальной записи о платеже
//            $this->createCharge($booking, $session->id, $amountOfMoney, 'usd');
            $this->createCharge($booking, $session->id, 50, 'usd');

            Log::info('Charge created in DB with session ID: ' . $session->id);

            return [
                'session_id' => $session->id,
                'payment_url' => $session->url,
            ];

        } catch (Exception $e) {
            Log::error("Payment failed: " . $e->getMessage());
            throw new Exception("Payment failed: " . $e->getMessage());
        }
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
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = Session::retrieve($sessionId);
            Log::info('Stripe Session retrieved: ' . json_encode($session));
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
            Log::error('Payment verification failed: Session is null.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment verification failed.',
            ];
        }

        // Check if the session has expired
        if ($session->expires_at < time()) {
            Log::error('Payment session has expired.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment session has expired.',
            ];
        }

        // Check the payment status
        if ($session->payment_status !== 'paid') {
            Log::error('Payment not completed.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment not completed.',
            ];
        }

        // Update the booking status to paid status
        $bookingService->updateBookingStatus($bookingId, 2);

        // Update the charge information
        $this->updateCharge($session->id, $session->payment_intent);

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Payment successful and booking status updated.',
        ];
    }

    public function updateCharge(string $sessionId, string $paymentIntent): void
    {
        $charge = Charge::where('stripe_session_id', $sessionId)->firstOrFail();
        $charge->update([
            'stripe_payment_intent' => $paymentIntent,
        ]);
    }
}
