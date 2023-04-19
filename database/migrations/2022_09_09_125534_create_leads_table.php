<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('patronymic')->nullable();
            $table->string('phone',25);
            $table->string('additional_phone',25)->nullable();
            $table->string('email',255)->nullable();
            $table->string('source',255)->nullable();
            $table->string('issued_by')->nullable();
            $table->string('series_number')->nullable();
            $table->string('inn')->nullable();
            $table->text('referer')->nullable();
            $table->string('requestid')->nullable();
            $table->foreignId('lead_status_id')->constrained('lead_status')->onUpdate('cascade')->onDelete('cascade');
            $table->date('interview_date')->nullable();
            $table->softDeletes();
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
        Schema::connection('mysql2')->dropIfExists('leads');
    }
}
