<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LanguageTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('language_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('language_id');
            $table->string('lang');
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
        Schema::connection('mysql2')->dropIfExists('language_translations');
        // Schema::connection('mysql2')->table('language_translations', function (Blueprint $table) {
        //     $table->dropColumn('user_id');
        // });
    }
}
