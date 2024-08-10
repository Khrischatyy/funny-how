<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        Role::updateOrCreate(
            ['id' => 3],
            [
                'name' => 'studio_engineer',
                'guard_name' => 'web',
            ]
        );

        DB::statement("SELECT setval(pg_get_serial_sequence('roles', 'id'), coalesce(max(id)+1, 1), false) FROM roles");
    }
}
