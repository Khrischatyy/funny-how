<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\GetClientsRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\UserPhotoUpdateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\SendResetPasswordJob;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Jobs\SendWelcomeEmailOwnerJob;
use App\Models\AdminCompany;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\FailedPasswordResetResponse;
use Laravel\Fortify\Http\Responses\PasswordResetResponse;

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

    /**
     * @OA\Post(
     *     path="/user/forgot-password",
     *     summary="Send a password reset link",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset link sent."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Invalid email"),
     *     @OA\Response(response="500", description="Failed to send password reset link")
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return $this->sendResponse([], __('passwords.sent'), 200);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/reset-password",
     *     summary="Reset the user's password",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="your-reset-token"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="new-password"),
     *             @OA\Property(property="password_confirmation", type="string", example="new-password")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="400", description="Invalid input"),
     *     @OA\Response(response="500", description="Failed to reset password")
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
    
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                Auth::login($user);
            }
        );
    
        if ($status == Password::PASSWORD_RESET) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return $this->sendResponse([
                "message" => "Password reset successfully",
                "role" => $user->getRoleNames()->first(),
                "token" => $token,
                "company_slug" => $user->company->slug ?? null,
                "has_company" => AdminCompany::where('admin_id', $user->id)->exists(),
            ], 'Password reset successfully.');
        }
    
        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/user/send-welcome-email-owner",
     *     summary="Send a welcome email to the owner",
     *     tags={"User"},
     *     security={{ "sanctum":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Welcome email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Welcome email sent successfully✨")
     *         )
     *     ),
     *     @OA\Response(response="400", description="Invalid input"),
     *     @OA\Response(response="500", description="Failed to send welcome email")
     * ),
     * @OA\Server(
     *     url="https://funny-how.com/api/v1",
     *     description="Funny How API Server"
     * )
     */
    public function sendWelcomeEmailOwner(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        SendWelcomeEmailOwnerJob::dispatch($user);

        return $this->sendResponse([], 'Welcome email sent successfully');
    }
}