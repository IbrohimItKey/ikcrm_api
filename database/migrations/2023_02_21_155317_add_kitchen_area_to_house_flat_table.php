<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKitchenAreaToHouseFlatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('house_flat', function (Blueprint $table) {
            $table->decimal('kitchen_area')->nullable();
        });

        Schema::connection('mysql2')->table('basket_house_flat', function (Blueprint $table) {
            $table->decimal('kitchen_area')->nullable();
            $table->decimal('area')->nullable();
            $table->decimal('terrace')->nullable();
            $table->decimal('balcony')->nullable();
        });

        Schema::connection('mysql2')->create('basket_house_document', function (Blueprint $table) {
            $table->id();
            $table->integer('basket_house_flat_id');
            $table->string('name');
            $table->string('guid');
            $table->string('ext');
            $table->string('size');
            $table->integer('main_image');
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
        Schema::connection('mysql2')->table('house_flat', function (Blueprint $table) {
            $table->dropColumn('kitchen_area');
        });

        Schema::connection('mysql2')->table('basket_house_flat', function (Blueprint $table) {
            $table->dropColumn('kitchen_area');
            $table->dropColumn('area');
            $table->dropColumn('terrace');
            $table->dropColumn('balcony');
        });

        Schema::dropIfExists('basket_house_document');
    }
}
