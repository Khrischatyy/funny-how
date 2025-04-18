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
        Schema::create('square_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // или другое поле для ассоциации с пользователем/адресом/компанией
            $table->string('access_token');

            $table->bigInteger('square_location_id')->unsigned()->nullable();
            $table->foreign('square_location_id')->references('id')->on('square_locations')->onDelete('cascade');

            $table->string('refresh_token');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('square_tokens');
    }
};
