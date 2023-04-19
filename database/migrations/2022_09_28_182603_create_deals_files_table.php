<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('deals_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('deal_id')->unsigned()->index()->nullable();
            $table->string('name')->nullable();
            $table->string('guid')->nullable();
            $table->string('ext',20)->nullable();
            $table->string('size')->nullable();
            $table->tinyInteger('main_image')->default(0);
            $table->foreign('deal_id')->references('id')->on('deals');
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
        Schema::connection('mysql2')->dropIfExists('deals_files');
    }
}
