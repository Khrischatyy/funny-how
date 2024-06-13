<?php

namespace Database\Seeders;

use App\Models\AddressPhoto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AddressPhoto::truncate();

        $inserts = [
            [
                'address_id' => 1,
                'path' => 'public/studio-photos/1.jpg',
                'index' => 0,
            ],
            [
                'address_id' => 1,
                'path' => 'public/studio-photos/2.jpeg',
                'index' => 1,
            ],
            [
                'address_id' => 1,
                'path' => 'public/studio-photos/3.jpeg',
                'index' => 2,
            ],
            [
                'address_id' => 1,
                'path' => 'public/studio-photos/4.png',
                'index' => 3,
            ],
            [
                'address_id' => 1,
                'path' => 'public/studio-photos/5.jpeg',
                'index' => 4,
            ],
            [
                'address_id' => 2,
                'path' => 'public/studio-photos/7.jpg',
                'index' => 1,
            ],
            [
                'address_id' => 2,
                'path' => 'public/studio-photos/8.png',
                'index' => 2,
            ],
            [
                'address_id' => 2,
                'path' => 'public/studio-photos/9.jpg',
                'index' => 3,
            ],
            [
                'address_id' => 2,
                'path' => 'public/studio-photos/11.png',
                'index' => 4,
            ],
        ];


        AddressPhoto::insert($inserts);

        DB::statement("SELECT setval(pg_get_serial_sequence('address_photos', 'id'), coalesce(max(id)+1, 1), false) FROM address_photos");
    }
}
