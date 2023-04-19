<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectStageToHouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('house', function (Blueprint $table) {
            $table->integer('project_stage');
            $table->integer('total_apartments');
            $table->integer('number_apartment_entrance');
            $table->integer('number_apartment_one_floor');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('house', function (Blueprint $table) {
            $table->dropColumn('project_stage');
            $table->dropColumn('total_apartments');
            $table->dropColumn('number_apartment_entrance');
            $table->dropColumn('number_apartment_one_floor');
        });
    }
}