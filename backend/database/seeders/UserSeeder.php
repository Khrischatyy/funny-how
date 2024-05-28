<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Update or create Alex
        $alex = User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Alex',
                'email' => 'khrischatyy@gmail.com',
                'password' => '$2y$10$vvqrdn.O2WcbYOAZUaEUFuwfQcsO7pwvziQo5LRrXT1xftHP76bVS',
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
        $alex->assignRole('studio_owner');

        // Update or create Rus
        $rus = User::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'Rus',
                'email' => 'rushadaev@gmail.com',
                'password' => '$2y$10$x6J2wUd4uLzaB7Og1d.gWe9K38M.ROUDPZa3tJJJuaknMNfFRfKQi',
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
        $rus->assignRole('studio_owner');

        // Reset the sequence for the id column
        DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), coalesce(max(id)+1, 1), false) FROM users");
    }
}
