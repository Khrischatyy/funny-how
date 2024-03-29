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
        Schema::create('document_field', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');

            $table->foreign('document_id')->references('id')->on('documents')
                ->onDelete('cascade');

            $table->unsignedBigInteger('field_id');

            $table->foreign('field_id')->references('id')->on('fields')
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
        Schema::dropIfExists('document_field');
    }
};
