<?php

namespace Database\Seeders;

use App\Models\RoomPrice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RoomPrice::upsert([
            [
                'room_id' => 1,
                'hours' => 1,
                'is_enabled' => true,
                'total_price' => 50,
                'price_per_hour' => 50,
            ],
            [
                'room_id' => 1,
                'hours' => 4,
                'is_enabled' => true,
                'total_price' => 80,
                'price_per_hour' => 20,
            ],

            [
                'room_id' => 2,
                'hours' => 1,
                'is_enabled' => true,
                'total_price' => 50,
                'price_per_hour' => 50,
            ],
            [
                'room_id' => 2,
                'hours' => 4,
                'is_enabled' => true,
                'total_price' => 80,
                'price_per_hour' => 20,
            ],
            [
                'room_id' => 2,
                'hours' => 8,
                'is_enabled' => true,
                'total_price' => 100,
                'price_per_hour' => 12.5,
            ],

        ],
            ['id'],
        );
        DB::statement("SELECT setval(pg_get_serial_sequence('roles', 'id'), coalesce(max(id)+1, 1), false) FROM roles");
    }
}
