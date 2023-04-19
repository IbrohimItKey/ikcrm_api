<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstallmentPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('installment_plan', function (Blueprint $table) {
            $table->id();
            $table->string('period')->nullable();
            $table->string('percent')->nullable();
            $table->string('an_initial_fee')->nullable();
            $table->date('start_date')->nullable();
            $table->string('month_pay_first')->nullable();
            $table->string('month_pay_second')->nullable();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::connection('mysql2')->dropIfExists('installment_plan');
    }
}
