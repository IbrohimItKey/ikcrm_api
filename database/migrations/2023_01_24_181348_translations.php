<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Translations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('lang');
            $table->text('lang_key');
            $table->text('lang_value');
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
        Schema::connection('mysql2')->dropIfExists('translations');
        // Schema::connection('mysql2')->table('translations', function (Blueprint $table) {
        //     $table->dropColumn('user_id');
        // });
    }
}
