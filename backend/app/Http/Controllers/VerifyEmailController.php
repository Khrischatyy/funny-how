<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;
/**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Login",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Success",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="You are successfully logged in"),
 *             @OA\Property(property="token", type="string", example="3|3oAVp3brOZCusG98NXfI0fcu5veK1BcBdcMWqnF4efc0bfbe"),
 *             @OA\Property(property="role", type="string", example="studio_owner")
 *         )
 *     ),
 *     @OA\Response(response="400", description="Bad Request"),
 *     @OA\Response(response="404", description="Not Found"),
 *     @OA\Response(response="500", description="Internal Server Error"),
 * )
 *
 * @OA\Post(
 *     path="/auth/register",
 *     summary="Register a new user",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "role"},
 *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="johndoe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="password_confirmation", type="string", example="password123"),
 *             @OA\Property(property="role", type="string", maxLength=15, example="studio_owner")
 *         )
 *     ),
 *     @OA\Response(
 *         response="201",
 *         description="Created",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User registered successfully."),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *                 @OA\Property(property="role", type="string", example="studio_owner")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="400", description="Bad Request"),
 *     @OA\Response(response="422", description="Unprocessable Entity"),
 *     @OA\Response(response="500", description="Internal Server Error"),
 * )
 */

class VerifyEmailController extends BaseController
{
    public function verify(Request $request, User $user)
    {
        $routeHash = $request->route('hash');
        $user_id = $request->route('id');

        $user = User::find($user_id);

        if (is_null($user) || (! hash_equals(sha1($user->getEmailForVerification()), $routeHash))) {
            return $this->sendError('There is no user with this ID', 404);
        }

        if($user->markEmailAsVerified())
        {
            return $this->sendResponse('', 'Email successfully verified.' );
        }
    }
}
