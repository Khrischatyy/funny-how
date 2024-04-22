<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Requests\VerifyEmailRequest;

class VerifyEmailController extends BaseController
{
    public function verify(Request $request, User $user)
    {
        $routeHash = $request->route('hash');
        $user_id = $request->route('id');

        $user = User::find($user_id);

        if (is_null($user) || (! hash_equals(sha1($user->getEmailForVerification()), $routeHash))) {
            return $this->sendError('There is no user with this ID');
        }

        if($user->markEmailAsVerified())
        {
            return $this->sendResponse('', 'Email successfully verified.' );
        }
    }
}
