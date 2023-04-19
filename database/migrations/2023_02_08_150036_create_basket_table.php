<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('basket_house', function (Blueprint $table) {
            $table->id();
            $table->string('house_info');
            $table->string('house_number');
            $table->string('corpas');
            $table->integer('enterance_count');
            $table->integer('floor_count');
            $table->integer('project_stage');
            $table->integer('total_apartments');
            $table->integer('number_apartment_entrance');
            $table->integer('number_apartment_one_floor');
            $table->timestamps();
        });

        Schema::connection('mysql2')->create('basket_house_flat', function (Blueprint $table) {
            $table->id();
            $table->integer('basket_house_id');
            $table->integer('number_of_flat');
            $table->integer('floor');
            $table->integer('enterance');
            $table->integer('room_count');
            $table->double('total_area');
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
        Schema::dropIfExists('basket_house');
        Schema::dropIfExists('basket_house_flat');
    }
}
