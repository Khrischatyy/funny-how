<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends BaseController
{
    public function __construct(private MenuService $menuService)
    {}

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
