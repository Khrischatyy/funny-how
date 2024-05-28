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
                    'logo' => 'https://funny-how-s3-bucket.s3.amazonaws.com/public/images/9zS6zSlP3k1CvojwujRUyKOLnjOBp5jWbV6nhZI9.jpg',
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
                    'logo' => 'https://funny-how-s3-bucket.s3.amazonaws.com/public/images/25MCafRIqzD4NUa7XBJgIV7PXgDq1DjbjAEX8BE4.jpg',
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
                    'logo' => 'https://funny-how-s3-bucket.s3.amazonaws.com/public/images/25MCafRIqzD4NUa7XBJgIV7PXgDq1DjbjAEX8BE4.jpg',
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
                    'logo' => 'https://funny-how-s3-bucket.s3.amazonaws.com/public/images/25MCafRIqzD4NUa7XBJgIV7PXgDq1DjbjAEX8BE4.jpg',
                    'founding_date' => '2023-12-10 13:25:26',
                    'rating' => 8.7,
                ],
            );
        // Reset the sequence for the id column
        DB::statement("SELECT setval(pg_get_serial_sequence('companies', 'id'), coalesce(max(id)+1, 1), false) FROM companies");
    }
}
