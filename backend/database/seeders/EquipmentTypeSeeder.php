<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'name' => 'Microphone',
                    'icon' => '/images/micro.svg',
                ],
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'name' => 'Audio card',
                    'icon' => '/images/micro.svg',
                ]
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'name' => 'Monitors',
                    'icon' => '/images/micro.svg',
                ]
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 4,
                    'name' => 'Other shit',
                    'icon' => '/images/micro.svg',
                ]
            );
    }
}
