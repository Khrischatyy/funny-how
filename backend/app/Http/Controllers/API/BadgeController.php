<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SetAddressBadgesRequest;
use App\Models\Address;
use Exception;
use Illuminate\Http\JsonResponse;

class BadgeController extends BaseController
{
    /**
     * Get badges for a specific address.
     *
     * @param int $address_id
     * @return JsonResponse
     */
    public function getAddressBadges($address_id): JsonResponse
    {
        $address = Address::with('badges')->findOrFail($address_id);

        return $this->sendResponse($address->badges, 'Badges retrieved successfully.');
    }


    /**
     * Set badges for a specific address.
     *
     * @param SetAddressBadgesRequest $request
     * @param integer $address_id
     * @return JsonResponse
     */
    public function setAddressBadge(SetAddressBadgesRequest $request, int $address_id): JsonResponse
    {
        $badge_id = $request->input('badge_id');

        try {
            $address = Address::with('badges')->findOrFail($address_id);

            if ($address->badges->contains($badge_id)) {
                return $this->sendError('Badge is already set for this address.', 400);
            }

            $address->badges()->attach($badge_id);

            // Reload the relationship to get the updated badges
            $address->load('badges');

            return $this->sendResponse($address->badges, 'Badge added successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to add badge.', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove a specific badge from an address.
     *
     * @param SetAddressBadgesRequest $request
     * @param int $address_id
     * @return JsonResponse
     */
    public function removeAddressBadge(SetAddressBadgesRequest $request, int $address_id): JsonResponse
    {
        $badge_id = $request->input('badge_id');

        try {
            $address = Address::with('badges')->findOrFail($address_id);

            if (!$address->badges->contains($badge_id)) {
                return $this->sendError('Badge not found for this address.', 404);
            }

            $address->badges()->detach($badge_id);

            $address->load('badges');

            return $this->sendResponse($address->badges, 'Badge removed successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to remove badge.', 500, ['error' => $e->getMessage()]);
        }
    }
}
