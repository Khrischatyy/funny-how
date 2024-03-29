<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('address_equipment')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'address_id' => 1,
                    'equipment_id' => 1,
                ]
            );

        DB::table('address_equipment')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'address_id' => 1,
                    'equipment_id' => 2,
                ]
            );

        DB::table('address_equipment')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'address_id' => 1,
                    'equipment_id' => 3,
                ]
            );
    }
}
