<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     *                 @OA\Property(property="role", type="string", example="studio_owner"),
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
                "company_slug" => $user->company->slug ?? null,
                "has_company" => AdminCompany::where('admin_id', $user->id)->exists(),
            ], 'User information retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve user information.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/set-role",
     *     summary="Set the role for the authenticated user",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", example="studio_owner")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="role", type="string", example="studio_owner")
     *             ),
     *             @OA\Property(property="message", type="string", example="Role updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Invalid role"),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function setRole(RoleRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized.', 401);
        }

//        if ($user->roles->isNotEmpty()) {
//            return $this->sendError('User already has a role.', 409);
//        }

        $role = $request->input('role');

        try {
            $user->syncRoles([$role]);
        } catch (Exception $e) {
            return $this->sendError('Failed to update role.', 500, ['error' => $e->getMessage()]);
        }

        return $this->sendResponse($user->roleName(), 'Role updated successfully.');
    }
}