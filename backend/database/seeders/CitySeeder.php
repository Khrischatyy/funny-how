<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        City::upsert([
            [
                'id' => 1,
                'name' => 'belgrade',
                'country_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Moscow',
                'country_id' => 2,
            ],
            [
                'id' => 3,
                'name' => 'Novi Sad',
                'country_id' => 1,
            ],
            ], ['id']
        );
        DB::statement("SELECT setval(pg_get_serial_sequence('cities', 'id'), coalesce(max(id)+1, 1), false) FROM cities");
    }
}
