<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AdminCompany;
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
            RoleSeeder::class,
            BadgeSeeder::class,
            EquipmentTypeSeeder::class,
            OperatingModeSeeder::class,

//            CountrySeeder::class,
//            CitySeeder::class,
//            CompanySeeder::class,
//            UserSeeder::class,
//            AddressSeeder::class,
//            CompanyCitySeeder::class,
//            AddressBadgeSeeder::class,
//            EquipmentSeeder::class,
//            AddressEquipmentSeeder::class,
//            AdminCompanySeeder::class,
//            OperatingHoursSeeder::class,
//            AddressPriceSeeder::class,
//            AddressPhotoSeeder::class,
//            BookingSeeder::class,
        ]);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
