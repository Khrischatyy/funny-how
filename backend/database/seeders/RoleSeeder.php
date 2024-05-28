<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'user',
                'guard_name' => 'web',
            ]
        );

        Role::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'studio_owner',
                'guard_name' => 'web',
            ]
        );
    }
}
