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
    }
}
