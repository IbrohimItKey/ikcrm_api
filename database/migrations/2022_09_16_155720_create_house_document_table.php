<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHouseDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('house_document', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('house_flat_id')->unsigned()->index()->nullable();
            $table->string('name')->nullable();
            $table->string('guid')->nullable();
            $table->string('ext',20)->nullable();
            $table->string('size')->nullable();
            $table->tinyInteger('main_image')->default(0);
            $table->timestamps();
            $table->foreign('house_flat_id')->references('id')->on('house_flat')
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
        Schema::connection('mysql2')->dropIfExists('house_document');
    }
}
