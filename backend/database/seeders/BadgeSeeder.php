<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('badges')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'name' => 'mixing',
                    'image' => 'public/badges/mixing.svg',
                    'description' => 'This studio provide sound engineering service'
                ],
            );

        DB::table('badges')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'name' => 'record',
                    'image' => 'public/badges/record.svg',
                    'description' => 'They can help to record your stuff'
                ],
            );

        DB::table('badges')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'name' => 'rent',
                    'image' => 'public/badges/rent.svg',
                    'description' => 'You can rent whole thing without any escort'
                ],
            );


        DB::statement("SELECT setval(pg_get_serial_sequence('badges', 'id'), coalesce(max(id)+1, 1), false) FROM badges");
    }
}
