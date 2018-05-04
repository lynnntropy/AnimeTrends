<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnimeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->string('title_english')->after('title')->nullable();
            $table->string('title_japanese')->after('title_english')->nullable();
            $table->string('synonyms')->after('title_japanese')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn('title_english');
            $table->dropColumn('title_japanese');
            $table->dropColumn('synonyms');
        });
    }
}
