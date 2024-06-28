<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function pdateUser(User $user, array $data): User
    {
        $user->update(array_filter($data, function ($value) {
            return !is_null($value);
        }));

        return $user;
    }
}