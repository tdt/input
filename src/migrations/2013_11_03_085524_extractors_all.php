<?php

use Illuminate\Database\Migrations\Migration;

class ExtractorsAll extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Adjust the Sparql loader with hostname and current graph id
        Schema::table('sparqlload', function($table){

            $table->string('hostname');
        });

        // Add the graph table to keep track of the graphs created by our Sparql loader
        Schema::create('graphs', function($table){

            $table->increments('id');
            $table->string('graph_id', 255);
            $table->string('graph_name', 255);
            $table->string('version', 255);

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });


        // Add the json, shp xml and ical extractor
        Schema::create('jsonextract', function($table){

            $table->increments('id');
            $table->string('uri', 255);
            $table->timestamps();
        });

        Schema::create('xmlextract', function($table){

            $table->increments('id');
            $table->string('uri', 255);
            $table->integer('arraylevel');
            $table->timestamps();
        });

        Schema::create('shpextract', function($table){

            $table->increments('id');
            $table->string('uri', 255);
            $table->string('epsg', 32);
            $table->timestamps();
        });

        Schema::create('icalextract', function($table){

            $table->increments('id');
            $table->string('uri', 255);
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
        // Drop tables shpextract, xmlextract, icalextract, jsonextract, graphs, and remove hostname from sparqlload table
        Schema::drop('icalextract');
        Schema::drop('shpextract');
        Schema::drop('jsonextract');
        Schema::drop('graphs');
        Schema::drop('xmlextract');

        Schema::table('sparqlload', function($table){
            $table->dropColumn('hostname');
        });

    }

}
