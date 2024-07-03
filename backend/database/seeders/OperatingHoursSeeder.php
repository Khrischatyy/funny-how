<?php

namespace Database\Seeders;

use App\Models\OperatingHour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperatingHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inserts = [
            [
                'address_id' => 3,
                'mode_id' => 1,
                'day_of_week' => null,
                'open_time' => Carbon::parse('00:00')->toTimeString(),
                'close_time' => Carbon::parse('23:59:59')->toTimeString() // представляем 24:00 как 23:59:59
            ],
            [
                'address_id' => 2,
                'mode_id' => 1,
                'day_of_week' => null,
                'open_time' => Carbon::parse('00:00')->toTimeString(),
                'close_time' => Carbon::parse('23:59:59')->toTimeString() // представляем 24:00 как 23:59:59
            ],
            [
                'address_id' => 1,
                'mode_id' => 3,
                'day_of_week' => 0,
                'open_time' => Carbon::parse('11:00')->toTimeString(),
                'close_time' => Carbon::parse('17:00')->toTimeString()
            ],
            [
                'address_id' => 1,
                'mode_id' => 3,
                'day_of_week' => 6,
                'open_time' => Carbon::parse('11:00')->toTimeString(),
                'close_time' => Carbon::parse('17:00')->toTimeString()
            ],
        ];

        for ($day = 1; $day <= 5; $day++) {
            $inserts[] = [
                'address_id' => 1,
                'mode_id' => 3,
                'day_of_week' => $day,
                'open_time' => Carbon::parse('10:00')->toTimeString(),
                'close_time' => Carbon::parse('18:00')->toTimeString()
            ];
        }

        OperatingHour::insert($inserts);

        DB::statement("SELECT setval(pg_get_serial_sequence('operating_hours', 'id'), coalesce(max(id)+1, 1), false) FROM operating_hours");
    }
}