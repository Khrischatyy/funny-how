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
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->after('entrance');
            $table->foreign('city_id')->references('id')->on('cities')
                ->onDelete('cascade');

            $table->time('works_till')->after('entrance');;
            $table->time('works_since')->after('entrance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_city_id_foreign');
            $table->dropColumn('city_id');

            $table->dropColumn('works_since');
            $table->dropColumn('works_till');
        });
    }
};
