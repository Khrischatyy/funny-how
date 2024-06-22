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
        DB::table('operating_modes')->updateOrInsert(
            ['id' => 1],
            [
                'mode' => '24/7',
                'description_registration' => 'Operates every day, no need to specify working hours.',
                'description_customer' => '24/7 - Operates every day.',
                // 'description' => '24/7 - Работает каждый день, рабочие часы проставлять не нужно, все остальные (everyday, weekdays,holidays) - недоступны',
            ]
        );

        DB::table('operating_modes')->updateOrInsert(
            ['id' => 2],
            [
                'mode' => 'everyday',
                'description_registration' => 'Set working hours for each day of the week.',
                'description_customer' => 'Working hours for whole week.',
                // 'description' => 'Everyday - на все дни недели можно проставить время работы студии, при этом остальные (24/7, holidays, weekdays) опции не доступны',
            ]
        );

        DB::table('operating_modes')->updateOrInsert(
            ['id' => 3],
            [
                'mode' => 'regular',
                'description_registration' => 'Set working hours for all weekdays (5 days) and weekends (2 days).',
                'description_customer' => 'Working hours for weekdays and weekends.',
                // 'description' => 'Weekdays + Weekends - выставляются часы во все сразу будние (5 дней) выходные (2 дня), при выборе regular, 24/7 и everyday - недоступны',
            ]
        );

        DB::table('operating_modes')->updateOrInsert(
            ['id' => 4],
            [
                'mode' => 'ondays',
                'description_registration' => 'Set working hours for specific days.',
                'description_customer' => 'Working hours for each day.',
                // 'description' => 'On Days - выставляются дни отдельно, когда выбран ondays, то regular, 24/7 и everyday - недоступны',
            ]
        );

        DB::statement("SELECT setval(pg_get_serial_sequence('operating_modes', 'id'), coalesce(max(id)+1, 1), false) FROM operating_modes");
    }
}