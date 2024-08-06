<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Room::upsert([
            [
                'id' => 1,
                'name' => 'Room 1',
                'address_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Room 1',
                'address_id' => 2,
            ],
            [
                'id' => 3,
                'name' => 'Room 1',
                'address_id' => 3,
            ],
        ],
            ['id']);

        DB::statement("SELECT setval(pg_get_serial_sequence('rooms', 'id'), coalesce(max(id)+1, 1), false) FROM rooms");

    }
}
