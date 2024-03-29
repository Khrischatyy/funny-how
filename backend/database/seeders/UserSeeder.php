<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'name' => 'Alex',
                    'email' => 'khrischatyy@gmail.com',
                    'password' => '$2a$12$MDKEa1peOTemkouukzFnkOPRvLCHhKzezrX1OaZxSEMWGKeWBwpqa',
                ],
            );

        DB::table('users')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'name' => 'Rus',
                    'email' => 'rus@gmail.com',
                    'password' => '$2a$12$MDKEa1peOTemkouukzFnkOPRvLCHhKzezrX1OaZxSEMWGKeWBwpqa',
                ],
            );
    }
}
