<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Seed the table with initial statuses
        DB::table('booking_statuses')->insert([
            [
                'id' => 1,
                'name' => 'pending'
            ],
            [
                'id' => 2,
                'name' => 'paid'
            ],
            [
                'id' => 3,
                'name' => 'cancelled'
            ],
            [
                'id' => 4,
                'name' => 'expired'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_statuses');
    }
};
