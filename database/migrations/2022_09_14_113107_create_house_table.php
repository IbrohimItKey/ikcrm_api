<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('house', function (Blueprint $table) {
            $table->id();
            $table->string('house_info')->nullable();
            $table->string('house_number');
            $table->string('corpas')->nullable();
            $table->integer('enterance_count');
            $table->integer('floor_count');
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
        Schema::connection('mysql2')->dropIfExists('house');
    }
}
