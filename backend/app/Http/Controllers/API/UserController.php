<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminCompany;
use Exception;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/me",
     *     summary="Get authenticated user information",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string", example="User information retrieved successfully"),
     *                 @OA\Property(property="token", type="string", example="TOKEN_HERE"),
     *                 @OA\Property(property="role", type="string", example="admin"),
     *                 @OA\Property(property="has_company", type="boolean", example=true)
     *             ),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated")
     * )
     */
    public function getMe(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('Unauthenticated.', 401);
            }

            return $this->sendResponse([
                "message" => "User information retrieved successfully",
                "role" => $user->getRoleNames()->first(),
                "has_company" => AdminCompany::where('admin_id', $user->id)->exists(),
            ], 'User information retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve user information.', 500, ['error' => $e->getMessage()]);
        }
    }
}