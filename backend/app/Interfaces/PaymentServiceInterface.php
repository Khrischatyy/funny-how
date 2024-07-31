<?php

namespace App\Interfaces;

use App\Models\Booking;
use App\Models\User;

interface PaymentServiceInterface
{
    public function createPaymentSession(Booking $booking, int $amountOfMoney, User $studioOwner): array;
    public function refundPayment($booking);
    public function verifyPaymentSession($sessionId);
    public function processPaymentSuccess($sessionId, $bookingId);
}