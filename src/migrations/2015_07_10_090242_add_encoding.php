<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEncoding extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('input_rdfmap');
        Schema::drop('input_sparqlload');

        Schema::table('input_csvextract', function ($table) {
            $table->string('encoding', 50)->nullable();
        });

        Schema::table('input_xmlextract', function ($table) {
            $table->string('encoding', 50)->nullable();
        });

        Schema::table('input_shpextract', function ($table) {
            $table->string('encoding', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('input_csvextract', function ($table) {
            $table->dropColumn('encoding');
        });

        Schema::table('input_xmlextract', function ($table) {
            $table->dropColumn('encoding');
        });

        Schema::table('input_shpextract', function ($table) {
            $table->dropColumn('encoding');
        });

        Schema::create('input_rdfmap', function ($table) {
            $table->increments('id');
            $table->string('mapfile', 255);

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });

        Schema::create('input_sparqlload', function ($table) {

            $table->increments('id');
            $table->string('endpoint', 255);
            $table->string('user', 255);
            $table->string('password', 255);
            $table->integer('buffer_size');

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });
    }

}
