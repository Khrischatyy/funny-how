<?php

namespace Database\Seeders;

use App\Models\RoomPhoto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RoomPhoto::truncate();

        $inserts = [
            [
                'room_id' => 1,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec2ee184.jpg',
                'index' => 0,
            ],
            [
                'room_id' => 1,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 1,
            ],
            [
                'room_id' => 1,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec5974f9.jpg',
                'index' => 2,
            ],
            [
                'room_id' => 1,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 3,
            ],
            [
                'room_id' => 1,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 4,
            ],
            [
                'room_id' => 2,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 1,
            ],
            [
                'room_id' => 2,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 2,
            ],
            [
                'room_id' => 2,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 3,
            ],
            [
                'room_id' => 2,
                'path' => 'https://funny-how-s3-bucket.s3.amazonaws.com/studio/photos/66a27ec4d6a66.jpg',
                'index' => 4,
            ],
        ];


        RoomPhoto::insert($inserts);

        DB::statement("SELECT setval(pg_get_serial_sequence('room_photos', 'id'), coalesce(max(id)+1, 1), false) FROM room_photos");
    }
}
