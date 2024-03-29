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
                'house_number' => '1',
                'entrance' => 1,
                'city_id' => 2,
                'company_id' => 1,
                'works_since' => '10:00:00',
                'works_till' => '18:00:00',
            ],
            [
                'id' => 2,
                'street' => 'Mirijevski Venac 4',
                'latitude' => 20.5320636,
                'company_id' => 1,
                'longitude' => 44.792424,
                'house_number' => '13',
                'entrance' => 13,
                'city_id' => 1,
                'works_since' => '9:00:00',
                'works_till' => '22:00:00',
            ],
            [
                'id' => 3,
                'street' => 'Nikolic',
                'latitude' => 20.386438,
                'longitude' => 44.8018983,
                'house_number' => '13',
                'company_id' => 2,
                'entrance' => 22,
                'city_id' => 3,
                'works_since' => '8:00:00',
                'works_till' => '22:00:00',
            ],
        ],
            ['id']);

    }
}
