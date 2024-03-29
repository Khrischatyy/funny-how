<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')
            ->updateOrInsert(
                [
                    'id' => 1,
                    'name' => 'Section',
                    'logo' => '/images/logos/section.svg',
                    'slug' => 'section',
                    'founding_date' => '2020-12-10 13:25:26',
                    'rating' => 9.7,
                ],
            );

        DB::table('companies')
            ->updateOrInsert(
                [
                    'id' => 2,
                    'name' => 'Abbey Road Studios',
                    'logo' => '/images/logos/abbey.png',
                    'slug' => 'section-near',
                    'founding_date' => '2021-12-10 13:25:26',
                    'rating' => 9.7,
                ],
            );

        DB::table('companies')
            ->updateOrInsert(
                [
                    'id' => 3,
                    'name' => 'TVT',
                    'slug' => 'shit-company',
                    'logo' => '/images/logos/abbey.png',
                    'founding_date' => '2022-12-10 13:25:26',
                    'rating' => 5.7,
                ],
            );


        DB::table('companies')
            ->updateOrInsert(
                [
                    'id' => 4,
                    'name' => 'Release',
                    'slug' => 'good.company',
                    'logo' => '/images/logos/abbey.png',
                    'founding_date' => '2023-12-10 13:25:26',
                    'rating' => 8.7,
                ],
            );
    }
}
