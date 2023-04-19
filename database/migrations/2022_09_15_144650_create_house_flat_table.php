<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseFlatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('house_flat', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('house_id')->unsigned();
            $table->integer('number_of_flat');
            $table->integer('floor');
            $table->integer('enterance');
            $table->integer('room_count');
            $table->decimal('total_area',10,2);
            $table->decimal('area',10,2);
            $table->decimal('basement_area',10,2)->nullable();
            $table->decimal('terrace_area',10,2)->nullable();
            $table->decimal('mansard_area',10,2)->nullable();
            $table->decimal('balcony',10,2)->nullable();
            $table->tinyInteger('status')->default(0);
            // $table->date('date')->nullable();
            $table->string('contract_number')->nullable();
            $table->decimal('price',10,2)->nullable();
            $table->decimal('price_pay_30',10,2)->nullable();
            $table->decimal('price_pay_50',10,2)->nullable();
            $table->decimal('basement',10,2)->nullable();
            $table->decimal('basement_price_pay_30',10,2)->nullable();
            $table->decimal('basement_price_pay_50',10,2)->nullable();
            $table->decimal('terrace',10,2)->nullable();
            $table->decimal('mansard',10,2)->nullable();
            $table->string('currency')->nullable();
            $table->timestamps();
            $table->foreign('house_id')->references('id')->on('house')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('house_flat');
    }
}
