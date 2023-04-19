<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('status')->nullable();
            $table->text('message');
//            $table->text('context');
            $table->text('formatted');
            $table->string('remote_addr')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('level')->index();
            $table->string('level_name');
            $table->string('channel')->index();
            $table->text('extra');
            $table->string('record_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('action_logs');
    }
}
