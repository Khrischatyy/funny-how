<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends BaseController
{
    public function __construct(private MenuService $menuService)
    {}

    /**
     * @OA\Get(
     *     path="/menu",
     *     summary="Get menu items",
     *     tags={"Menu"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="Menu items retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="history", type="string", example="History"),
     *                 @OA\Property(property="studios_management", type="string", example="Studios Management"),
     *                 @OA\Property(property="booking_management", type="string", example="Booking Management"),
     *                 @OA\Property(property="profile", type="string", example="Profile")
     *             ),
     *             @OA\Property(property="message", type="string", example="Menu items retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(response="500", description="Failed to retrieve menu items")
     * )
     */
    public function getMenu(): JsonResponse
    {
        try {
            $menuItems = $this->menuService->getMenuItems();
            return $this->sendResponse($menuItems, 'Menu items retrieved successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve menu items.', 500, ['error' => $e->getMessage()]);
        }
    }
}
