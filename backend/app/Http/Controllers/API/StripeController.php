<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\Payment\Gateways\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StripeController extends BaseController
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function createAccount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $accountData = $this->stripeService->createAccount($user);
            return $this->sendResponse($accountData, 'Account link created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create account link.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function refreshAccountLink(): JsonResponse
    {
        try {
            $user = Auth::user();
            $accountData = $this->stripeService->refreshAccountLink($user);
            return $this->sendResponse($accountData, 'Account link refreshed successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to refresh account link.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function retrieveBalance(): JsonResponse
    {
        try {
            $user = Auth::user();
            $balance = $this->stripeService->retrieveBalance($user);
            return $this->sendResponse($balance, 'Balance retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve balance.', 500, ['error' => $e->getMessage()]);
        }
    }
}