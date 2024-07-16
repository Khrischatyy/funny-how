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
                'street' => 'Gazetniy Av.',
                'latitude' => 55.7578375,
                'longitude' => 37.6064895,
                'city_id' => 2,
                'slug' => 'section-moscow',
                'company_id' => 1,
                'timezone' => 'Europe/Moscow',
            ],
            [
                'id' => 2,
                'street' => 'Mirijevski Venac 4',
                'latitude' => 44.792424,
                'company_id' => 1,
                'slug' => 'test-moscow',
                'longitude' => 20.5320636,
                'city_id' => 1,
                'timezone' => 'Europe/Belgrade',
            ],
            [
                'id' => 3,
                'street' => '435 East 30th Street',
                'latitude' => 34.0199776,
                'longitude' => -118.2688639,
                'slug' => 'Funny How Sweet Co Co Can Be',
                'company_id' => 2,
                'city_id' => 3,
                'timezone' => 'Europe/Belgrade',
            ],
        ],
            ['id']);

        DB::statement("SELECT setval(pg_get_serial_sequence('addresses', 'id'), coalesce(max(id)+1, 1), false) FROM addresses");

    }
}
