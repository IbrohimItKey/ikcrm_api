<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSelfIdToFlatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('basket_house_flat', function (Blueprint $table) {
            $table->integer('basket_house_flat_id')->nullable();
        });

        Schema::connection('mysql2')->table('house_flat', function (Blueprint $table) {
            $table->integer('house_flat_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flats', function (Blueprint $table) {
        });
    }
}
