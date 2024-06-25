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
                    'name' => 'Amplifier',
                    'icon' => '/images/micro.svg',
                ],
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'name' => 'Audio card',
                    'icon' => '/images/micro.svg',
                ]
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 4,
                    'name' => 'Monitors',
                    'icon' => '/images/micro.svg',
                ]
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 5,
                    'name' => 'Computer',
                    'icon' => '/images/micro.svg',
                ]
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 6,
                    'name' => 'DAW Software',
                    'icon' => '/images/micro.svg',
                ]
            );


        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 7,
                    'name' => 'Plug-ins',
                    'icon' => '/images/micro.svg',
                ]
            );

        DB::table('equipment_type')
            ->updateOrInsert(
                [
                    'id' => 8,
                    'name' => 'Other shit',
                    'icon' => '/images/micro.svg',
                ]
            );
        DB::statement("SELECT setval(pg_get_serial_sequence('equipment_type', 'id'), coalesce(max(id)+1, 1), false) FROM equipment_type");
    }
}
