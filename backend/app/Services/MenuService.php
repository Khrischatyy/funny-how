<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class MenuService
{
    public function getMenuItems()
    {
        $user = Auth::user(); // Get the authenticated user

        // Define menu items for each role
        $menus = [
            'studio_owner' => [
                'history' => 'History',
                'studios_management' => 'Studios Management',
                'booking_management' => 'Booking Management',
                'profile' => 'Profile',
            ],
            'user' => [
                'history' => 'History',
                'booking_management' => 'Booking Management',
                'profile' => 'Profile',
            ],
        ];

        // Get the user's roles
        $roles = $user->getRoleNames(); // Using spatie/laravel-permission

        // Fetch the menu for the user's roles
        $menu = [];
        foreach ($roles as $role) {
            if (isset($menus[$role])) {
                $menu = array_merge($menu, $menus[$role]);
            }
        }

        // Remove duplicates
        $menu = array_unique($menu);

        return $menu;
    }
}
