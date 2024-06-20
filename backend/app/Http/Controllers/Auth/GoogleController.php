<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;

class GoogleController extends BaseController
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
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Update the Google ID and other fields if needed
                $user->update([
                    'google_id' => $googleUser->id,
                    'name' => $googleUser->name,
                    'password' => $user->password ?? Str::random(12), // Keep existing password if it exists
                ]);
            } else {
                // Create a new user if one doesn't exist
                $user = User::create([
                    'google_id' => $googleUser->id,
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Str::random(12),
                ]);
            }

            Auth::login($user);

            // Create a Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect back to the frontend with the token
            return redirect(env('AXIOS_BASEURL_CLIENT') . '/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            dd($e, 'stop');
            // Handle the exception or redirect to an error page
            return redirect('/login')->with('error', 'Failed to login with Google.');
        }
    }
}
