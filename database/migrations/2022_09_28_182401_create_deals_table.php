<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('deals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->integer('house_flat_id')->unsigned();
            $table->string('house_flat_number')->nullable();
            $table->decimal('price_bought', 10, 2)->nullable();
            $table->string('additional_phone')->nullable();
            $table->string('phone')->nullable();
            $table->string('series_number')->nullable();
            $table->string('email')->nullable();
            $table->date('dateDl');
            $table->string('description')->nullable();
            $table->integer('gender', 2)->nullable();
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
        Schema::connection('mysql2')->dropIfExists('deals');
    }
}
