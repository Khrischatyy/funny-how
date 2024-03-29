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
                    'image' => '/images/badges/mixing.svg'
                ],
            );

        DB::table('badges')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'name' => 'record',
                    'image' => '/images/badges/record.svg'
                ],
            );

        DB::table('badges')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'name' => 'rent',
                    'image' => '/images/badges/rent.svg'
                ],
            );
    }
}
