<?php

namespace App\Services;

use App\Http\Requests\UserUpdateRequest;
use App\Jobs\SendStaffInvitationJob;
use App\Mail\StaffInvitationMail;
use App\Models\Address;
use App\Models\AdminCompany;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class UserService
{
    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function addMember(Request $request, Address $address): User
    {
        // Extract all necessary input data at the beginning
        $name = $request->input('name');
        $email = $request->input('email');
        $roleName = $request->input('role');
        $ratePerHour = $request->input('rate_per_hour');

        // Fetch the role dynamically based on the provided data
        $role = Role::where('name', $roleName)->firstOrFail();

        // Generate a random password
        $randomPassword = Str::random(12);

        // Create the user with the given data
        $user = User::create([
            'username' => $name,
            'email' => $email,
            'password' => Hash::make($randomPassword),
        ]);

        // Assign the role to the user
        $user->assignRole($role);

        // Save the rate per hour in the engineer_rates table
        $user->engineerRate()->create(['rate_per_hour' => $ratePerHour]);

        // Attach the address to the engineer
        $user->addresses()->attach($address->id);

        // Generate the password reset link
        $resetUrl = $this->generatePasswordResetLink($user);


        // Dispatch the job to send an invitation email with the reset link

        dispatch(new SendStaffInvitationJob($user, $resetUrl, $roleName));

        return $user;
    }

    public function listMembers($company_id)
    {
        // Get all addresses associated with the company
        $addresses = Address::where('company_id', $company_id)->get();

        // Retrieve users associated with these addresses, including roles and engineerRate
        $staff = $addresses->flatMap(function ($address) {
            return $address->users()->with(['roles', 'engineerRate'])->get();
        });

        return $staff;
    }

    public function removeStaff(Address $address, int $staffId)
    {
        $staff = $address->users()->findOrFail($staffId);

        $address->users()->detach($staffId);
    }

    public function generatePasswordResetLink(User $user): string
    {
        $token = Password::createToken($user);
        $email = $user->email;
        $resetUrl = url(env('APP_URL') . '/reset-password?token=' . $token . '&email=' . $email);

        return $resetUrl;
    }

    public function sendResetLink(string $email)
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status == Password::RESET_LINK_SENT) {
            return __('passwords.sent');
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function resetUserPassword(Request $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->input('password')),
                ])->save();

                Auth::login($user);
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                "message" => "Password reset successfully",
                "role" => $user->getRoleNames()->first(),
                "token" => $token,
                "company_slug" => $user->addresses->first()->company->slug ?? null,
                "has_company" => $user->addresses->isNotEmpty(),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
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
            $query->whereHas('room.address', function ($query) use ($company) {
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

    public function checkEmail(string $queryEmail)
    {
        // Implement logic to find emails by letter for users with role 'studio_engineer'
        return User::where('email', 'LIKE', "%$queryEmail%")
            ->role('studio_engineer', 'web')
            ->get(['firstname', 'email']); // Возвращаем имя и email
    }
}