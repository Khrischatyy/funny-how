<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Address::upsert([
            [
                'id' => 1,
                'street' => 'Газетный переулок',
                'latitude' => 37.609337,
                'longitude' => 55.758972,
                'city_id' => 2,
                'company_id' => 1,
            ],
            [
                'id' => 2,
                'street' => 'Mirijevski Venac 4',
                'latitude' => 20.5320636,
                'company_id' => 1,
                'longitude' => 44.792424,
                'city_id' => 1,
            ],
            [
                'id' => 3,
                'street' => 'Nikolic',
                'latitude' => 20.386438,
                'longitude' => 44.8018983,
                'company_id' => 2,
                'city_id' => 3,
            ],
        ],
            ['id']);

        DB::statement("SELECT setval(pg_get_serial_sequence('addresses', 'id'), coalesce(max(id)+1, 1), false) FROM addresses");

    }
}
