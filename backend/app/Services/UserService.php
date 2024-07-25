<?php

namespace App\Services;

use App\Http\Requests\UserUpdateRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserService
{
    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }


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
        $photoName = Str::uuid() . '.jpg';
        $photoPath = 'profile/photos/' . $photoName;

        // Compress, resize, and optimize image in memory
        $compressedImage = $this->imageService->toJpeg($photo);
        $optimizedImage = $this->imageService->optimizeImageInMemory($compressedImage);

        // Save to S3 and get URL
        $photoUrl = $this->imageService->saveImageToStorage($optimizedImage, $photoPath);

        $user->profile_photo = $photoUrl;
        $user->save();

        return $photoUrl;
    }

    public function getClientsByCompanySlug(string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->first();

        if (!$company) {
            throw new \Exception('Company not found.', 404);
        }

        // Authorize the action
        if (!Auth::user()->can('update', $company)) {
            throw new \Exception('Unauthorized.', 403);
        }

        $clients = User::whereHas('bookings', function ($query) use ($company) {
            $query->whereHas('address', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            });
        })
            ->withCount('bookings')
            ->get(['id', 'firstname', 'username', 'phone', 'email', 'bookings_count']);
        // $clients = User::whereHas('adminCompany', function ($query) use ($company) {
        //     $query->where('company_id', $company->id);
        // })
        //     ->withCount('bookings')
        //     ->get(['id', 'firstname', 'username', 'phone', 'email', 'bookings_count']);

        return $clients->map(function($client) {
            return [
                'id' => $client->id,
                'firstname' => $client->firstname,
                'username' => $client->username,
                'phone' => $client->phone,
                'email' => $client->email,
                'booking_count' => $client->bookings_count,
            ];
        });
    }
}