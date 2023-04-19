<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalToFlatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->table('basket_house_flat', function (Blueprint $table) {
            $table->integer('additional_type')->nullable();
        });

        Schema::connection('mysql2')->table('basket_house', function (Blueprint $table) {
            $table->boolean('has_basement')->nullable();
            $table->boolean('has_attic')->nullable();
        });

        Schema::connection('mysql2')->table('house_flat', function (Blueprint $table) {
            $table->integer('additional_type')->nullable();
        });

        Schema::connection('mysql2')->table('house', function (Blueprint $table) {
            $table->boolean('has_basement')->nullable();
            $table->boolean('has_attic')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('basket_house_flat', function (Blueprint $table) {
            $table->dropColumn('additional_type');
        });

        Schema::connection('mysql2')->table('basket_house', function (Blueprint $table) {
            $table->dropColumn('has_basement');
            $table->dropColumn('has_attic');
        });

        Schema::connection('mysql2')->table('house_flat', function (Blueprint $table) {
            $table->dropColumn('additional_type');
        });

        Schema::connection('mysql2')->table('house', function (Blueprint $table) {
            $table->dropColumn('has_basement');
            $table->dropColumn('has_attic');
        });
    }
}
