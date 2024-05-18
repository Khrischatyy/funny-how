<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('address_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->integer('hours');
            $table->decimal('total_price', 8, 2);
            $table->decimal('price_per_hour', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_prices');
    }
};
