<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;use Stripe\Checkout\Session;
use Stripe\Stripe;

class SubscriptionController extends BaseController
{
    public function __construct(private SubscriptionService $subscriptionService)
    {}

    public function subscribe(Request $request): JsonResponse
    {
        $sessionUrl  = $this->subscriptionService->subscribe();

       return $this->sendResponse($sessionUrl, 200);
    }
}
