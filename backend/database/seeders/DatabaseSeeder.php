<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CountrySeeder::class,
            CitySeeder::class,
            CompanySeeder::class,
            UserSeeder::class,
            AddressSeeder::class,
            BookingSeeder::class,
            CompanyCitySeeder::class,
            BadgeSeeder::class,
            AddressBadgeSeeder::class,
            EquipmentTypeSeeder::class,
            EquipmentSeeder::class,
            AddressEquipmentSeeder::class,
        ]);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
