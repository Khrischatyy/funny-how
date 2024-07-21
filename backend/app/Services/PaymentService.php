<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Charge;
use App\Models\Payout;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PaymentService
{
    public const MINUTE_TO_PAY = 30;
    public const SERVICE_FEE_PERCENTAGE = 0.5; // 4% сервисный сбор

    public function createPaymentSession(Booking $booking, int $amountOfMoney): array
    {
        try {
            $user = Auth::user();
            $address = Address::findOrFail($booking->address_id);

            if (!$user->stripe_account_id) {
                throw new Exception("Studio owner does not have a Stripe account.");
            }

            $session = $user->checkoutCharge($amountOfMoney * 100, 'Payment for studio reservation', 1, [
                'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
                'cancel_url' => env('APP_URL') . '/cancel-booking',
                'payment_intent_data' => [
                    'application_fee_amount' => $amountOfMoney * 100 * self::SERVICE_FEE_PERCENTAGE,
                    'transfer_data' => ['destination' => $user->stripe_account_id],
                ],
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);

            $this->createCharge($booking, $session->id, $amountOfMoney, 'usd');

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

            // Обновим баланс студии
            $this->updateBalance($booking->address_id, -$charge->amount);

            // Обновим статус бронирования
            $this->updateBookingStatus($booking->id, 3);

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Refund processed successfully and booking status updated.',
            ];

        } catch (Exception $e) {
            Log::error('Refund failed: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => 500,
                'error' => 'Failed to process refund: ' . $e->getMessage(),
            ];
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

    public function processPaymentSuccess($sessionId, $bookingId)
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

        $validationResult = $this->validateSession($session);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        $booking = $this->updateBookingStatus($bookingId, 2);
        $this->updateCharge($session->id, $session->payment_intent);

        // Рассчитаем сумму, идущую студии, и сервисный сбор
        $totalAmount = $session->amount_total; // Сумма в центах
        $serviceFee = $totalAmount * self::SERVICE_FEE_PERCENTAGE;
        $amountToStudio = $totalAmount - $serviceFee;

        // Обновим баланс студии
        $this->updateBalance($booking->address_id, $amountToStudio);

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Payment successful and booking status updated.',
        ];
    }

    private function validateSession(Session $session): array
    {
        if ($session->expires_at < time()) {
            Log::error('Payment session has expired.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment session has expired.',
            ];
        }

        if ($session->payment_status !== 'paid') {
            Log::error('Payment not completed.');
            return [
                'success' => false,
                'code' => 400,
                'error' => 'Payment not completed.',
            ];
        }

        return ['success' => true];
    }

    private function updateBookingStatus(int $bookingId, int $statusId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->status_id = $statusId;
        $booking->save();

        return $booking;
    }

    private function updateCharge(string $sessionId, string $paymentIntent): void
    {
        $charge = Charge::where('stripe_session_id', $sessionId)->firstOrFail();
        $charge->update([
            'stripe_payment_intent' => $paymentIntent,
        ]);
    }

    private function updateBalance(int $addressId, int $amount): void
    {
        $address = Address::findOrFail($addressId);
        $address->available_balance += $amount; // Сохраняем в центах, увеличение или уменьшение баланса
        $address->save();
    }

    public function getAvailableBalance(): array
    {
        $user = Auth::user();
        $company = $user->company;


        if (!$company) {
            return [
                'success' => false,
                'balance' => 0,
            ];
        }

        $addresses = Address::where('company_id', $company->id)->get();
        $totalBalance = $addresses->sum('available_balance');

        return [
            'success' => true,
            'balance' => $totalBalance,
        ];
    }
}
