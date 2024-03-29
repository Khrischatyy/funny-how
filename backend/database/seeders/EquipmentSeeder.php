<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Equipment::upsert([
            [
                'id' => 1,
                'name' => 'soyuz 017 serial',
                'description' => 'Awesome as fuck micro. Fantastic for hip-hop',
                'shop_path' => 'amazon.com/micro/soyuz017',
                'type_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'marshal 017 serial',
                'description' => 'Awesome as fuck micro. Fantastic for hip-hop',
                'shop_path' => 'amazon.com/micro/marshal',
                'type_id' => 3,
            ],
            [
                'id' => 3,
                'name' => 'gibson 017 serial',
                'description' => 'Awesome as fuck micro. Fantastic for hip-hop',
                'shop_path' => 'amazon.com/micro/gibson',
                'type_id' => 4,
            ],
            ],
            ['id'],
        );
    }
}
