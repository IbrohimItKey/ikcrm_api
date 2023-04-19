<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('price_apartments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->double('price');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('is_terassa');
            $table->boolean('is_mansard');
            $table->boolean('is_basement');
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
        Schema::dropIfExists('price_apartments');
    }
}
