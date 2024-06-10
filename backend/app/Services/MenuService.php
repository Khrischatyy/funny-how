<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MenuService
{
    public function getMenuItems()
    {
        $user = Auth::user(); // Get the authenticated user

        // Define menu items for each role
        $menus = [
            'studio_owner' => [
                'history' => ['name' => 'History', 'path' => Storage::disk('s3')->url('public/menu/history.svg')],
                'studios_management' => ['name' => 'Studios Management', 'path' => Storage::disk('s3')->url('public/menu/mic.svg')],
                'booking_management' => ['name' => 'Booking Management', 'path' => Storage::disk('s3')->url('public/menu/Booking.svg')],
                'profile' => ['name' => 'Profile', 'path' => Storage::disk('s3')->url('public/menu/profile.svg')],
            ],
            'user' => [
                'history' => ['name' => 'History', 'path' => Storage::disk('s3')->url('public/menu/history.svg')],
                'booking_management' => ['name' => 'Booking Management', 'path' => Storage::disk('s3')->url('public/menu/Booking.svg')],
                'profile' => ['name' => 'Profile', 'path' => Storage::disk('s3')->url('public/menu/profile.svg')],
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

        // Remove duplicates based on 'name' key
//        $menu = array_values(array_intersect_key($menu, array_unique(array_column($menu, 'name'))));

        return $menu;
    }
}
