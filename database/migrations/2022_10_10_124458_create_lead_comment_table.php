<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('lead_comment', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            // $table->bigInteger('lead_id')->unsigned();
            $table->string('comment')->nullable();
            $table->foreignId('lead_id')->constrained('leads')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::connection('mysql2')->dropIfExists('lead_comment');
    }
}
