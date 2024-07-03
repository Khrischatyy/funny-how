<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\GetClientsRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\UserPhotoUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminCompany;
use Exception;

class UserController extends BaseController
{
    public function __construct(protected UserService $userService)
    {}

    /**
     * @OA\Get(
     *     path="/user/me",
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
                "user" => $user,
                "company_slug" => $user->company->slug ?? null,
                "has_company" => AdminCompany::where('admin_id', $user->id)->exists(),
            ], 'User information retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve user information.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/user/set-role",
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

        if ($user->roles->isNotEmpty()) {
            return $this->sendError('User already has a role.', 409);
        }

        $role = $request->input('role');

        try {
            $user->syncRoles([$role]);
        } catch (Exception $e) {
            return $this->sendError('Failed to update role.', 500, ['error' => $e->getMessage()]);
        }

        return $this->sendResponse($user->roleName(), 'Role updated successfully.');
    }

    /**
     * @OA\Put(
     *     path="/user",
     *     summary="Update user details",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="firstname", type="string", example="John"),
     *             @OA\Property(property="lastname", type="string", example="Doe"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="firstname", type="string", example="John"),
     *                 @OA\Property(property="lastname", type="string", example="Doe"),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="User updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Failed to update user")
     * )
     */
    public function updateUser(UserUpdateRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('Unauthorized.', 401);
            }

            $updatedUser = $this->userService->updateUser($user, $request);

            return $this->sendResponse($updatedUser, 'User updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to update user.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/user/update-photo",
     *     summary="Update user photo",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="photo", type="string", format="binary", description="User photo")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Photo updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="photo_url", type="string", example="https://example.com/photo.jpg"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-03T09:39:52.000000Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Photo updated successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Failed to update photo")
     * )
     */
    public function updatePhoto(UserPhotoUpdateRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->sendError('Unauthorized.', 401);
            }

            $photoUrl = $this->userService->updateUserPhoto($user, $request->file('photo'));

            return $this->sendResponse(['photo_url' => $photoUrl], 'Photo updated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to update photo.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @OA\Post(
     *     path="/clients",
     *     summary="Get list of clients by company slug with their booking count",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_slug"},
     *             @OA\Property(property="company_slug", type="string", example="your-company-slug")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Clients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="firstname", type="string", example="John"),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="booking_count", type="integer", example=5)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Clients retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated")
     * )
     */
    public function getClients(GetClientsRequest $request): JsonResponse
    {
        try {
            $companySlug = $request->input('company_slug');
            $clients = $this->userService->getClientsByCompanySlug($companySlug);

            return $this->sendResponse($clients, 'Clients retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}