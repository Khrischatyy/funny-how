<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AdminCompany;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class DatabaseSeederProd extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            BadgeSeeder::class,
            EquipmentTypeSeeder::class,
            OperatingModeSeeder::class,
        ]);
    }
}
