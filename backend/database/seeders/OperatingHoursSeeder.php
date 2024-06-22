<?php

namespace Database\Seeders;

use App\Models\OperatingHour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                'open_time' => '00:00',
                'close_time' => '24:00'
            ],
            [
                'address_id' => 2,
                'mode_id' => 1,
                'day_of_week' => null,
                'open_time' => '00:00',
                'close_time' => '24:00'
            ],
            [
                'address_id' => 1,
                'mode_id' => 3,
                'day_of_week' => 0,
                'open_time' => '11:00',
                'close_time' => '17:00'
            ],
            [
                'address_id' => 1,
                'mode_id' => 4,
                'day_of_week' => 6,
                'open_time' => '11:00',
                'close_time' => '17:00'
            ],
        ];

        for ($day = 1; $day <= 5; $day++) {
            $inserts[] = [
                'address_id' => 1,
                'mode_id' => 3,
                'day_of_week' => $day,
                'open_time' => '10:00',
                'close_time' => '18:00'
            ];
        }

        $isHoursAdded = OperatingHour::insert($inserts);

        DB::statement("SELECT setval(pg_get_serial_sequence('operating_hours', 'id'), coalesce(max(id)+1, 1), false) FROM operating_hours");
    }
}