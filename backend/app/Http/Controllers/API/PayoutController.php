<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PayoutController extends BaseController
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Get(
     *     path="/payouts/available-balance",
     *     summary="Get available balance for payout",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Available balance retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="balance", type="number", format="float", example=1000.00)
     *             ),
     *             @OA\Property(property="message", type="string", example="Available balance retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="500", description="Failed to retrieve available balance")
     * )
     */
    public function getAvailableBalance(): JsonResponse
    {
        try {
            $result = $this->paymentService->getAvailableBalance();
            return $this->sendResponse(['balance' => $result['balance']], 'Available balance retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve available balance.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/payouts/create-payout",
     *     summary="Create a payout",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Payout created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="payout_id", type="string", example="po_1J9FzL2eZvKYlo2C0sJdQ3h9")
     *             ),
     *             @OA\Property(property="message", type="string", example="Payout created successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="500", description="Failed to create payout")
     * )
     */
    public function createPayout(): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->paymentService->createPayout($user);
            return $this->sendResponse(['payout_id' => $result['payout']->id], 'Payout created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create payout.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function createAccountLink(): JsonResponse
    {
        try {
            $user = Auth::user();
            $url = $this->paymentService->createAccountLink($user);
            return $this->sendResponse(['url' => $url], 'Payout created successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to create payout.', 500, ['error' => $e->getMessage()]);
        }
    }
}