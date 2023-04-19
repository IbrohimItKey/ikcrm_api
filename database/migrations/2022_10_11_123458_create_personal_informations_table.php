<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonalInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('personal_informations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('series_number');
            $table->string('given_date');
            $table->string('live_address');
            $table->string('inn');
            $table->foreignId('deal_id')->constrained('deals')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::connection('mysql2')->dropIfExists('personal_informations');
    }
}
