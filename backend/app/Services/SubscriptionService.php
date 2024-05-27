<?php

namespace App\Services;

use App\Http\Requests\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class SubscriptionService
{
    //create subscribe session
    public function subscribe(): Session
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $user = Auth::user();

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Studio Subscription',
                    ],
                    'unit_amount' => 10,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('checkout.success', [$user => 'user']),
            'cancel_url' => route('checkout.cancel'),
        ]);
    }


    //create payment session
    public function makePayment($totalPrice): Session
    {
        $amountInCents = $totalPrice * 100;

        $user = Auth::user();

        Stripe::setApiKey(env('STRIPE_SECRET'));

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Studio Booking',
                    ],
                    'unit_amount' => $amountInCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel', ['user' => $user]),
        ]);
    }
}
