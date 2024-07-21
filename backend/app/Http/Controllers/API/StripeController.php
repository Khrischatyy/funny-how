<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createAccountlink()
    {
        try {
            $user = Auth::user();

            // Проверяем, есть ли у пользователя уже аккаунт в Stripe
            if (!$user->stripe_account_id) {
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Создаем аккаунт в Stripe
                $account = Account::create([
                    'type' => 'express',
                    'country' => 'US', // Измените на необходимую страну
                    'email' => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'tos_acceptance' => [
                        'date' => time(),
                        'ip' => request()->ip(), // IP-адрес пользователя
                    ],
                    'business_type' => 'individual', // или 'company'
                ]);

                // Сохраняем Stripe Account ID в базе данных
                $user->stripe_account_id = $account->id;
                $user->save();
            }

            // Создаем ссылку для подключения аккаунта
            $accountLink = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('stripe.account.refresh'),
                'return_url' => route('stripe.account.complete'),
                'type' => 'account_onboarding',
            ]);

            return response()->json(['url' => $accountLink->url], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function refreshAccountLink()
    {
        try {
            $user = Auth::user();

            $accountLink = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('stripe.account.refresh'),
                'return_url' => route('stripe.account.complete'),
                'type' => 'account_onboarding',
            ]);

            return response()->json(['url' => $accountLink->url], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function completeAccount()
    {
        return response()->json(['message' => 'Account setup complete'], 200);
    }
}
