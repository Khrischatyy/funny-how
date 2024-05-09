<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        DB::table('operating_modes')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'mode' => '24/7',
                    'description' => '24/7 - Работает каждый день, рабочие часы проставлять не нужно,
                     все остальные (everyday, weekdays,holidays) - недоступны',
                ],
            );

        DB::table('operating_modes')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'mode' => 'everyday',
                    'description' => 'Everyday - на все дни недели можно проставить время работы студии, 
                    при этом остальные (24/7, holidays, weekdays) опции не доступны',

                ],
            );

        DB::table('operating_modes')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'mode' => 'regular',
                    'description' => 'Weekdays + Weekends - (пн, вт, ср, чт, пт, сб, вс) выставляются часы во все дни,
                     при выборе regular, 24/7 и everyday - недоступны',
                ],
            );
    }
}
