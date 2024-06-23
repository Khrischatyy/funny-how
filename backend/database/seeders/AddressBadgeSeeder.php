<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'address_id' => 1,
                    'badge_id' => 1
                ],
            );

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'address_id' => 1,
                    'badge_id' => 2
                ]);

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'address_id' => 2,
                    'badge_id' => 3
                ]);

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 4,
                    'address_id' => 1,
                    'badge_id' => 1
                ],
            );

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 5,
                    'address_id' => 1,
                    'badge_id' => 2
                ]);

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 6,
                    'address_id' => 1,
                    'badge_id' => 3
                ]);


        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 7,
                    'address_id' => 1,
                    'badge_id' => 1
                ],
            );

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 8,
                    'address_id' => 1,
                    'badge_id' => 2
                ]);

        DB::table('address_badge')
            ->updateOrInsert(
                [
                    'id' => 9,
                    'address_id' => 1,
                    'badge_id' => 3
                ]);

        DB::statement("SELECT setval(pg_get_serial_sequence('address_badge', 'id'), coalesce(max(id)+1, 1), false) FROM address_badge");

    }
}
