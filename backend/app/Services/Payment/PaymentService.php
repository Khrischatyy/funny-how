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

    public function refundPayment(Booking $booking, $gateway)
    {
        return $this->getService($gateway)->refundPayment($booking);
    }

    public function verifyPaymentSession($sessionId, $gateway)
    {
        return $this->getService($gateway)->verifyPaymentSession($sessionId);
    }

    public function processPaymentSuccess($orderId, $bookingId, $gateway)
    {
        return $this->getService($gateway)->processPaymentSuccess($orderId, $bookingId);
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