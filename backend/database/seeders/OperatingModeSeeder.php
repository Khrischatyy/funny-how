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
                    'mode' => 'holidays',
                    'description' => 'Holidays - (суббота и воскресение) выставляются часы в выходные дни,
                     при выборе holidays и weekdays 24/7 и everyday - недоступны',

                ],
            );

        DB::table('operating_modes')
            ->updateOrInsert(
                [
                    'id' => 4,
                    'mode' => 'weekdays',
                    'description' => 'Weekdays - (пн, вт, ср, чт, пт) выставляются часы в будние дни
                     при выборе holidays и weekdays 24/7 и everyday - недоступны',
                ],
            );

        DB::table('operating_modes')
            ->updateOrInsert(
                [
                    'id' => 5,
                    'mode' => 'closed',
                    'description' => 'День закрыт',
                ],
            );






    }
}
