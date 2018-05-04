<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpisodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('anime_id');
            $table->integer('episode_number');
            $table->string('title')->nullable();
            $table->string('title_romaji')->nullable();
            $table->string('title_japanese')->nullable();
            $table->date('aired_date');
            $table->timestamps();

            $table->foreign('anime_id')->references('id')->on('anime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('episodes');
    }
}
