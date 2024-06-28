<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperatingModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('operating_modes')->updateOrInsert(
            ['id' => 1 ],
            [
                'mode' => '24/7',
                'label' => 'Always Open',
                'description_registration' => 'Operates every day, no need to specify working hours.',
                'description_customer' => 'Open 24/7 - Operates every day.',
            ]
        );

        DB::table('operating_modes')->updateOrInsert(
            ['id' => 2 ],
            [
                'mode' => 'everyday',
                'label' => 'Daily Fixed Hours',
                'description_registration' => 'Set same working hours for each day of the week. One input for all 7 days.',
                'description_customer' => 'Same working hours every day.',
            ]
        );

        DB::table('operating_modes')->updateOrInsert(
            ['id' => 3 ],
            [
                'mode' => 'each_day',
                'label' => 'Custom Hours For Each Day',
                'description_registration' => 'Set different working hours for each day. Separate input for each day.',
                'description_customer' => 'Different working hours for each day.',
            ]
        );

        DB::statement("SELECT setval(pg_get_serial_sequence('operating_modes', 'id'), coalesce(max(id)+1, 1), false) FROM operating_modes");
    }
}