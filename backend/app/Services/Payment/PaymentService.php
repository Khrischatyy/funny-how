<?php

namespace App\Services\Payment;

use App\Interfaces\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\User;
use App\Services\Payment\Gateways\StripeService;
use App\Services\Payment\Gateways\SquareService;
use Exception;

class PaymentService
{
    protected $stripeService;
    protected $squareService;

    public const MINUTE_TO_PAY = 30;
    public const SERVICE_FEE_PERCENTAGE = 0.04;

    public function __construct(StripeService $stripeService, SquareService $squareService)
    {
        $this->stripeService = $stripeService;
        $this->squareService = $squareService;
    }

    public function createPaymentSession(Booking $booking, int $amountOfMoney, User $studioOwner): array
    {
        $paymentGateway = $studioOwner->payment_gateway;

        switch ($paymentGateway) {
            case 'square':
                return $this->squareService->createPaymentSession($booking, $amountOfMoney, $studioOwner);
            case 'stripe':
                return $this->stripeService->createPaymentSession($booking, $amountOfMoney, $studioOwner);
            default:
                throw new Exception("Unsupported payment gateway: $paymentGateway");
        }
    }

    public function refundPayment($booking, $studioOwner)
    {
        $gateway = $studioOwner->payment_gateway;
        return $this->getService($gateway)->refundPayment($booking);
    }

    public function verifyPaymentSession($sessionId, $gateway, $studioOwner)
    {
        return $this->getService($gateway)->verifyPaymentSession($sessionId, $studioOwner);
    }

    public function processPaymentSuccess($orderId, $bookingId, $gateway, $studioOwner)
    {
        return $this->getService($gateway)->processPaymentSuccess($orderId, $bookingId, $studioOwner);
    }

    public function retrieveAccount(User $user)
    {
        $paymentGateway = $user->payment_gateway;
        return $this->getService($paymentGateway)->retrieveAccount($user);
    }

    public function retrieveBalance(User $user)
    {
        $paymentGateway = $user->payment_gateway;
        return $this->getService($paymentGateway)->retrieveBalance($user);
    }

    protected function getService($gateway): PaymentServiceInterface
    {
        switch ($gateway) {
            case 'stripe':
                return $this->stripeService;
            case 'square':
                return $this->squareService;
            default:
                throw new \Exception("Unsupported payment gateway: $gateway");
        }
    }
}