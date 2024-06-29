<?php

namespace App\Services;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function updateUser(User $user, UserUpdateRequest $request): User
    {
        $data = $request->validated();

        if (isset($data['firstname'])) {
            $user->firstname = $data['firstname'];
        }
        if (isset($data['lastname'])) {
            $user->lastname = $data['lastname'];
        }
        if (isset($data['username'])) {
            $user->username = $data['username'];
        }
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }
        if (isset($data['date_of_birth'])) {
            $user->date_of_birth = $data['date_of_birth'];
        }

        $user->save();

        return $user;
    }


    public function updateUserPhoto(User $user, $photo): string
    {
        $photoName = uniqid() . '.' . $photo->getClientOriginalExtension();

        $photoPath = $photo->storeAs('photos/profile', $photoName, 's3');

        $photoUrl = Storage::disk('s3')->url($photoPath);

        $user->profile_photo = $photoUrl;
        $user->save();

        return $photoUrl;
    }
}