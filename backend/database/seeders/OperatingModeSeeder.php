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
                    'description' => 'Weekdays + Weekends - выставляются часы во все сразу будние (5 дней) выходные (2 дня),
                     при выборе regular, 24/7 и everyday - недоступны',
                ],
            );

        DB::table('operating_modes')
            ->updateOrInsert(
                [
                    'id' => 4,
                    'mode' => 'ondays',
                    'description' => 'On Days - выставляются дни отдельно, когда выбран ondays, то regular, 24/7 и everyday - недоступны',
                ],
            );
    }
}
