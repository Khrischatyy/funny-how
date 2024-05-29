<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Requests\SetAddressBadgesRequest;
use App\Models\Address;
use App\Models\Badge;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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
        try {
            // Try to find the address
            $address = Address::with('badges')->findOrFail($address_id);

            // Generate the S3 URLs for the badges
            $takenBadges = $address->badges->map(function ($badge) {
                $badge->image_url = Storage::disk('s3')->url($badge->image);
                return $badge;
            });

            $allBadges = Badge::all()->map(function ($badge) {
                $badge->image_url = Storage::disk('s3')->url($badge->image);
                return $badge;
            });

            $badges = [
                'all_badges' => $allBadges,
                'taken_badges' => $takenBadges,
            ];

            return $this->sendResponse($badges, 'Badges retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            // Handle the case where the address was not found
            return $this->sendError('Address not found.', 404);
        } catch (Exception $e) {
            // Handle any other exceptions
            return $this->sendError('Failed to retrieve badges.', 500, ['error' => $e->getMessage()]);
        }
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
                return $this->removeAddressBadge($request, $address_id);
            }

            $address->badges()->attach($badge_id);

            // Reload the relationship to get the updated badges
            $address->load('badges');

            return $this->sendResponse($address->badges, 'Badge added successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to add badge.', 500, ['error' => $e->getMessage()]);
        }
    }
}
