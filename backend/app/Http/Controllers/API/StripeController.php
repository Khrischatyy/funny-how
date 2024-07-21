<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;

class StripeController extends BaseController
{
    public function createAccount(): JsonResponse
    {
        try {
            $user = Auth::user();
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create Stripe account if it doesn't exist
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

                // Save the Stripe Account ID in the database
                $user->stripe_account_id = $account->id;
                $user->save();
            }

            return $this->sendResponse(['account_id' => $user->stripe_account_id], 'Stripe account created successfully.');
        } catch (ApiErrorException $e) {
            return $this->sendError('Failed to create Stripe account.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function createAccountLink(): JsonResponse
    {
        try {
            $user = Auth::user();
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Create Stripe account if it doesn't exist
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

                // Save the Stripe Account ID in the database
                $user->stripe_account_id = $account->id;
                $user->save();
            }

            $accountLink = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => env('APP_URL') . '/stripe/refresh', // URL фронтенда для обновления
                'return_url' => env('APP_URL') . '/stripe/complete', // URL фронтенда для завершения
                'type' => 'account_onboarding',
            ]);

            return $this->sendResponse(['url' => $accountLink->url], 'Account link created successfully.');
        } catch (ApiErrorException $e) {
            return $this->sendError('Failed to create account link.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function refreshAccountLink(): JsonResponse
    {
        return $this->createAccountLink();
    }

    public function completeAccount(): JsonResponse
    {
        // Handle post-onboarding logic
        return $this->sendResponse([], 'Account onboarding complete.');
    }

    public function retrieveAccount(): JsonResponse
    {
        try {
            $user = Auth::user();
            Stripe::setApiKey(env('STRIPE_SECRET'));

            if (!$user->stripe_account_id) {
                return $this->sendError('Stripe account not found.', 404);
            }

            $account = Account::retrieve($user->stripe_account_id);

            return $this->sendResponse($account, 'Stripe account retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve Stripe account.', 500, ['error' => $e->getMessage()]);
        }
    }
}