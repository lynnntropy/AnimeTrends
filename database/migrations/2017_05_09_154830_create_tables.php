<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anime', function (Blueprint $table) {
            $table->integer('id');
            $table->primary('id');
            $table->string('title');
            $table->string('image');
            $table->timestamps();
        });

        Schema::create('snapshots', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('anime_id');
            $table->float('rating');
            $table->integer('members');
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
        Schema::dropIfExists('anime');
        Schema::dropIfExists('snapshots');
    }
}
