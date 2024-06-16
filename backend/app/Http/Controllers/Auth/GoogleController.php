<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;

class GoogleController extends Controller
{
    public function redirectToProvider()
    {
        //  return Socialite::driver($driver)->stateless()->redirect();
        return Socialite::driver('google')->setScopes(['openid', 'email'])->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::firstOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'password' => bcrypt(Str::random(24)),
            ]);

            Auth::login($user);

            $token = $user->createToken($user->email)->plainTextToken;

            return response()->json([
                'message' => 'Successfully logged in',
                'token' => $token,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authenticate with Google.', 'message' => $e->getMessage()], 500);
        }
    }
}
