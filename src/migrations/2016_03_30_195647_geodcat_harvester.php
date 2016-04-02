<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GeodcatHarvester extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_geodcatextract', function ($table) {
            $table->increments('id');
            $table->string('format', 255)->default('ttl');
            $table->string('uri', 255);
            $table->timestamps();
        });

        Schema::create('input_tdtload', function ($table) {
            $table->increments('id');
            $table->string('definition_type', 255);
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
        Schema::drop('input_geodcatextract');
        Schema::drop('input_tdtload');
    }
}
