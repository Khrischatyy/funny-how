<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\BaseController;
use App\Http\Requests\AddMemberRequest;
use App\Http\Requests\DeleteTeamMemberRequest;
use App\Models\Address;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends BaseController
{
    public function __construct(public UserService $userService)
    {}

    public function addMember(AddMemberRequest $request): JsonResponse
    {
        $address_id = $request->input('address_id');

        $address = Address::findOrFail($address_id);

        $this->authorize('update', $address);

        try {
            $user = $this->userService->addMember($request, $address);

            return $this->sendResponse($user, 'Staff member added successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to add staff member.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function listMembers(): JsonResponse
    {
        $user = Auth::user();
        $company_id = $user->adminCompany->company_id;

        $staff = $this->userService->listMembers($company_id);

        return $this->sendResponse($staff, 'Staff members retrieved successfully.');
    }

    public function removeMember(DeleteTeamMemberRequest $request): JsonResponse
    {
        $address_id = $request->input('address_id');
        $member_id = $request->input('member_id');

        $address = Address::findOrFail($address_id);

        $this->authorize('update', $address);


        $this->userService->removeStaff($address, $member_id);

        return $this->sendResponse([], 'Staff member removed successfully.');
    }

    public function checkMemberEmail(Request $request): JsonResponse
    {
        $queryEmail = $request->query('q'); // Retrieves the 'q' parameter from the URL

        $staffEmails = $this->userService->checkEmail($queryEmail);

        return $this->sendResponse($staffEmails, 'Emails matching the query retrieved successfully.');
    }
}
