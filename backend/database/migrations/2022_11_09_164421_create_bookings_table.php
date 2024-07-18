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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->time('start_time');
            $table->time('end_time');
            $table->date('date');
            $table->date('end_date')->after('date');
            $table->text('temporary_payment_link')->after('end_date')->nullable();
            $table->timestamp('temporary_payment_link_expires_at')->after('temporary_payment_link')->nullable();

            $table->unsignedBigInteger('address_id');

            $table->foreign('address_id')->references('id')->on('addresses')
                ->onDelete('cascade');

            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
