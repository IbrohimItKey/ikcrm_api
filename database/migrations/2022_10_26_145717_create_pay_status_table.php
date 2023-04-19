<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('pay_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_plan_id')->constrained('installment_plan')->onDelete('cascade')->onUpdate('cascade');
            $table->date('pay_date')->nullable();
            $table->string('status')->nullable();
            $table->decimal('sum', 10, 2)->nullable();
            $table->date('pay_start_date')->nullable();
            $table->date('pay_end_date')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('pay_status');
    }
}
