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
                'password' => '$2y$10$bA5BVAqpoNbKhOPE./WxM.enbjoJZaItZTACdMeVHNhUyiOCfN8Mq',
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
                'password' => '$2y$10$Gqg3FGE3X0rOZ3q1etepx.qZ6sGsBW7.SwpSCrhKOh1ENWoSpwBya',
                'updated_at' => now(),
                'created_at' => now()
            ]
        );
        $rus->assignRole('studio_owner');

        // Reset the sequence for the id column
        DB::statement("SELECT setval(pg_get_serial_sequence('users', 'id'), coalesce(max(id)+1, 1), false) FROM users");
    }
}
