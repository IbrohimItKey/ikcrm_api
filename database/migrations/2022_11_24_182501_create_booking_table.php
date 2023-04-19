<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('booking', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('patronymic')->nullable();
            $table->string('phone')->nullable();
            $table->integer('prepayment');
            $table->string('house_flat_id')->constrained('house_flat')->onDelete('cascade')->onUpdate('cascade');;
            $table->string('admin_name')->nullable();
            $table->bigInteger('notification_date')->nullable();
            $table->bigInteger('expire_date')->nullable();
            $table->integer('read_before')->nullable();
            $table->integer('notify_before')->nullable();
            $table->integer('notify')->nullable();
            $table->integer('read')->nullable();
            $table->string('admin_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('booking');
    }
}
