<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\BaseController;
use App\Http\Requests\StaffRequest;
use App\Models\Address;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;

class StaffController extends BaseController
{
    public function __construct(public UserService $userService)
    {}

    public function addStaff(StaffRequest $request, $address_id): JsonResponse
    {
        $address = Address::findOrFail($address_id);

        $this->authorize('update', $address);

        try {
            $staff = $this->userService->addStaff($request, $address);

            return $this->sendResponse($staff, 'Staff member added successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to add staff member.', 500, ['error' => $e->getMessage()]);
        }
    }
}
