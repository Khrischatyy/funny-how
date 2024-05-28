<?php

namespace Database\Seeders;

use App\Models\AdminCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminCompany::updateOrCreate(
            ['id' => 1],
            [
                'admin_id' => 2,
                'company_id' => 1,
            ]
        );

        AdminCompany::updateOrCreate(
            ['id' => 2],
            [
                'admin_id' => 1,
                'company_id' => 2,
            ]
        );
        DB::statement("SELECT setval(pg_get_serial_sequence('admin_company', 'id'), coalesce(max(id)+1, 1), false) FROM admin_company");
    }
}
