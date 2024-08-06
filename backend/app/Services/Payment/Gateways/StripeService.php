<?php

namespace App\Services\Payment\Gateways;

use App\Interfaces\PaymentServiceInterface;
use App\Jobs\BookingConfirmationJob;
use App\Jobs\BookingConfirmationOwnerJob;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Charge;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeService implements PaymentServiceInterface
{
    public const SERVICE_FEE_PERCENTAGE = 0.04; // 4% сервисный сбор

    public function createPaymentSession(Booking $booking, int $amountOfMoney, User $studioOwner): array
    {
        try {
            if (!$studioOwner->stripe_account_id) {
                throw new \Exception("Studio owner does not have a Stripe account.");
            }

            $session = $studioOwner->checkoutCharge($amountOfMoney * 100, 'Payment for studio reservation', 1, [
                'success_url' => env('APP_URL') . '/payment-success?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking->id,
                'cancel_url' => env('APP_URL') . '/cancel-booking',
                'payment_intent_data' => [
                    'application_fee_amount' => $amountOfMoney * 100 * self::SERVICE_FEE_PERCENTAGE,
                    'transfer_data' => ['destination' => $studioOwner->stripe_account_id],
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

    public function refundPayment(Booking $booking, $studioOwner): array
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

    public function verifyPaymentSession($sessionId, $studioOwner): ?Session
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

    public function processPaymentSuccess($sessionId, $bookingId, $studioOwner): array
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

        $userWhoBooksEmail = $booking->user->email; 
        $studioOwner =  $booking->address->company->adminCompany->user;
        dispatch(new BookingConfirmationJob($booking, $userWhoBooksEmail, $totalAmount));
        dispatch(new BookingConfirmationOwnerJob($booking, $studioOwner, $totalAmount));

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Payment successful and booking status updated.',
        ];
    }

    public function retrieveAccount($user)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            if (!$user->stripe_account_id) {
                throw new Exception('Stripe account not found.', 404);
            }

            $account = Account::retrieve($user->stripe_account_id);

            return $account;
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve Stripe account. ' . $e->getMessage(), 500);
        }
    }

    public function createAccount($user): array
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        if (!$user->stripe_account_id) {
            $account = Account::create([
                'type' => 'express',
                'country' => 'US',
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
            ]);

            $user->stripe_account_id = $account->id;
            $user->save();
        }

        $accountLink = $this->createAccountLink($user->stripe_account_id);

        return [
            'url' => $accountLink->url,
        ];
    }

    public function refreshAccountLink($user): array
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        if (!$user->stripe_account_id) {
            throw new Exception('No Stripe account found.', 404);
        }

        $accountLink = $this->createAccountLink($user->stripe_account_id);

        return [
            'url' => $accountLink->url,
        ];
    }

    private function createAccountLink(string $stripeAccountId): AccountLink
    {
        return AccountLink::create([
            'account' => $stripeAccountId,
            'refresh_url' => env('APP_URL') . '/stripe/refresh',
            'return_url' => env('APP_URL') . '/stripe/complete',
            'type' => 'account_onboarding',
        ]);
    }

    public function retrieveBalance($user)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            if (!$user->stripe_account_id) {
                throw new Exception('Stripe account ID not found for the user.', 404);
            }

            $balance = Balance::retrieve([
                'stripe_account' => $user->stripe_account_id,
            ]);

            return $balance;
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve balance. ' . $e->getMessage(), 500);
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
