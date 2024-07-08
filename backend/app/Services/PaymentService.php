<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Charge;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Charge as StripeCharge;

class PaymentService
{

    private const MINUTE_TO_PAY = 30;

    public function createPaymentSession(Booking $booking)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Создаем PaymentIntent с описанием
            $paymentIntent = PaymentIntent::create([
                'amount' => $booking->total_cost * 100, // сумма в центах
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Оплата бронирования студии', // Описание
            ]);

            $expiresAt = now()->addMinutes(30)->timestamp; // Сессия истекает через 30 минут

            $session = Session::create([
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
                'payment_intent_data' => [
                    'metadata' => [
                        'booking_id' => $booking->id,
                    ],
                ],
                'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
                'cancel_url' => env('APP_URL') . '/cancel-booking',
                'expires_at' => $expiresAt,
            ]);

            // Логирование информации о сессии
            Log::info('Stripe Session created: ' . json_encode($session));

            // Сохранение информации о платеже в таблицу charges
            Charge::create([
                'booking_id' => $booking->id,
                'stripe_charge_id' => $paymentIntent->id,
                'amount' => $booking->total_cost,
                'currency' => 'usd',
                'status' => 'pending',
            ]);

            return [
                'session_id' => $session->id,
                'payment_url' => $session->url,
            ];

        } catch (Exception $e) {
            Log::error("Payment failed: " . $e->getMessage());
            throw new Exception("Payment failed: " . $e->getMessage());
        }
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
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $charge = Charge::where('booking_id', $booking->id)->firstOrFail();

            $refund = Refund::create([
                'payment_intent' => $charge->stripe_charge_id,
                'amount' => $booking->total_cost * 100, // Сумма возврата в центах
            ]);

            // Обновление информации о возврате в таблице charges
            $charge->update([
                'refund_id' => $refund->id,
                'refund_status' => $refund->status,
                'status' => 'refunded',
            ]);

        } catch (Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            throw new Exception("Failed to process refund: " . $e->getMessage());
        }
    }
}
