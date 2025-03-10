<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'name' => 'Serbia'
                ],
            );

        DB::table('countries')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'name' => 'Russia'
                ],
            );
        DB::statement("SELECT setval(pg_get_serial_sequence('countries', 'id'), coalesce(max(id)+1, 1), false) FROM countries");
    }
}
