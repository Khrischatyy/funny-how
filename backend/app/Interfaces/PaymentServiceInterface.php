<?php

namespace App\Interfaces;

use App\Models\Booking;
use App\Models\User;

interface PaymentServiceInterface
{
    public function createPaymentSession(Booking $booking, int $amountOfMoney, User $studioOwner): array;
    public function refundPayment(Booking $booking, $studioOwner);
    public function verifyPaymentSession($sessionId, $studioOwner);
    public function processPaymentSuccess($sessionId, $bookingId, $studioOwner);
    public function retrieveAccount(User $user);
}