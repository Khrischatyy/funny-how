<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Charge;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentService
{

    private const MINUTE_TO_PAY = 30;

    public function createPaymentSession(Booking $booking, int $amountOfMoney): array
    {
        try {
            $paymentIntent = $this->createPaymentIntent($amountOfMoney, 'Payment for studio reservation');

            Log::info('Stripe Session created: ' . json_encode($paymentIntent));

            $session = $this->createStripeSession($booking, $paymentIntent, $amountOfMoney);

            Log::info('Stripe Session created: ' . json_encode($session));

            $this->saveCharge($booking, $paymentIntent, $amountOfMoney);

            return [
                'session_id' => $session->id,
                'payment_url' => $session->url,
            ];

        } catch (Exception $e) {
            Log::error("Payment failed: " . $e->getMessage());
            throw new Exception("Payment failed: " . $e->getMessage());
        }
    }

    private function createPaymentIntent(int $amountOfMoney, string $description): PaymentIntent
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        return PaymentIntent::create([
//            'amount' => $amountOfMoney * 100, // сумма в центах
            'amount' => 50,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'description' => $description,
        ]);
    }

    private function createStripeSession(Booking $booking, PaymentIntent $paymentIntent, int $amountOfMoney): Session
    {
        $expiresAt = now()->addMinutes(self::MINUTE_TO_PAY)->timestamp; // Сессия истекает через 30 минут

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Studio Booking',
                    ],
//                    'unit_amount' => $amountOfMoney * 100,
                    'unit_amount' => 50,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'payment_intent_data' => [
                'description' => 'Payment for studio reservation',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'payment_intent_id' => $paymentIntent->id,
                ],
            ],
            'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
            'cancel_url' => env('APP_URL') . '/cancel-booking',
            'expires_at' => $expiresAt,
        ]);
    }

    private function saveCharge(Booking $booking, PaymentIntent $paymentIntent, int $amountOfMoney): void
    {
        Charge::create([
            'booking_id' => $booking->id,
            'stripe_charge_id' => $paymentIntent->id,
//            'amount' => $amountOfMoney,
            'amount' => 50,
            'currency' => 'usd',
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

    public function refundPayment(Booking $booking)
    {
        try {
            $charge = Charge::where('booking_id', $booking->id)->firstOrFail();

            // Выполняем возврат через метод пользователя
            $user = Auth::user();
            $refund = $user->refund($charge->stripe_charge_id);

            // Обновляем статус возврата и идентификатор возврата в таблице Charge
            $charge->update([
                'refund_id' => $refund->id,
                'refund_status' => $refund->status,
            ]);

        } catch (Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            throw new Exception("Failed to process refund: " . $e->getMessage());
        }
    }
}
