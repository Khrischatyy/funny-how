<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
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
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'date' => '2024-07-01',
                'address_id' => 1,
                'user_id' => 1,
                'total_cost' => 100.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status_id' => 1,
            ],
            [
                'start_time' => '14:00:00',
                'end_time' => '16:00:00',
                'date' => '2024-07-02',
                'address_id' => 2,
                'user_id' => 2,
                'total_cost' => 150.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status_id' => 1,
            ],
            [
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'date' => '2024-07-03',
                'address_id' => 3,
                'user_id' => 1,
                'total_cost' => 200.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status_id' => 1,
            ],
            [
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'date' => '2024-07-04',
                'address_id' => 1,
                'user_id' => 2,
                'total_cost' => 120.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'status_id' => 1,
            ],
        ];

        DB::table('bookings')->insert($inserts);

        DB::statement("SELECT setval(pg_get_serial_sequence('bookings', 'id'), coalesce(max(id)+1, 1), false) FROM bookings");
    }
}