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
     * @OA\Get(
     *     path="/address/{address_id}/badges",
     *     summary="Get all badges for an address",
     *     tags={"Badges"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Badges retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="mixing"),
     *                     @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                     @OA\Property(property="pivot", type="object",
     *                         @OA\Property(property="address_id", type="integer", example=1),
     *                         @OA\Property(property="badge_id", type="integer", example=1)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Badges retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to retrieve badges")
     * )
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
     * @OA\Post(
     *     path="/address/{address_id}/badge",
     *     summary="Set a badge for an address",
     *     tags={"Badges"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the address"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="badge_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Badges retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="mixing"),
     *                     @OA\Property(property="image", type="string", example="public/badges/mixing.svg"),
     *                     @OA\Property(property="pivot", type="object",
     *                         @OA\Property(property="address_id", type="integer", example=1),
     *                         @OA\Property(property="badge_id", type="integer", example=1)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Badges retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="404", description="Address not found"),
     *     @OA\Response(response="500", description="Failed to add badge")
     * )
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
