<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Jobs\SendWelcomeEmailJob;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleController extends BaseController
{
    public function redirectToProvider()
    {
        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();

            // Проверка на наличие полей в ответе от Google
            $firstName = $googleUser->user['given_name'] ?? null;
            $lastName = $googleUser->user['family_name'] ?? null;

            if ($user) {
                // Обновление данных пользователя, если он уже существует
                $user->update([
                    'google_id' => $googleUser->id,
                ]);
            } else {
                // Создание нового пользователя, если он не существует
                $user = User::create([
                    'google_id' => $googleUser->id,
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'email' => $googleUser->email,
                    'password' => Str::random(12),
                    'profile_photo' => $googleUser->avatar,
                    'date_of_birth' => null, // Дата рождения не возвращается Google API, устанавливаем null
                ]);

                // Отправка email при создании нового пользователя
                SendWelcomeEmailJob::dispatch($user);
            }

            Auth::login($user);

            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect(env('AXIOS_BASEURL_CLIENT') . '/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            dd($e, 'stop');
            // Handle the exception or redirect to an error page
            return redirect('/login')->with('error', 'Failed to login with Google.');
        }
    }
}
