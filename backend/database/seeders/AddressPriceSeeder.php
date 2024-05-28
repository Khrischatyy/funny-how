<?php

namespace Database\Seeders;

use App\Models\AddressPrice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AddressPrice::upsert([
            [
                'address_id' => 1,
                'hours' => 1,
                'is_enabled' => true,
                'total_price' => 50,
                'price_per_hour' => 50,
            ],
            [
                'address_id' => 1,
                'hours' => 4,
                'is_enabled' => true,
                'total_price' => 80,
                'price_per_hour' => 20,
            ],

            [
                'address_id' => 2,
                'hours' => 1,
                'is_enabled' => true,
                'total_price' => 50,
                'price_per_hour' => 50,
            ],
            [
                'address_id' => 2,
                'hours' => 4,
                'is_enabled' => true,
                'total_price' => 80,
                'price_per_hour' => 20,
            ],
            [
                'address_id' => 2,
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
