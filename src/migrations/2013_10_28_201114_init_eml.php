<?php

use Illuminate\Database\Migrations\Migration;

class InitEml extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create the csv extract table
		 Schema::create('csvextract', function($table){

            $table->increments('id');
            $table->string('uri', 255);
            $table->string('delimiter', 255);
            $table->string('has_header_row', 255);

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });

		// Create the rdf mapper table
		Schema::create('rdfmap', function($table){

            $table->increments('id');
            $table->string('mapfile', 255);

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });

		// Create the sparql loader table
		Schema::create('sparqlload', function($table){

            $table->increments('id');
            $table->string('endpoint', 255);
			$table->string('user', 255);
			$table->string('password', 255);
			$table->integer('buffer_size');

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });

		// Create the datatank publisher table
		Schema::create('tdtpublisher', function($table){

            $table->increments('id');
            $table->string('uri', 255);
			$table->string('user', 255);
			$table->string('password', 255);

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
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
		// Drop the csv extract table
		Schema::drop('csvextract');

		// Drop the rdf mapper table
		Schema::drop('rdfmap');

		// Drop the sparql loader table
		Schema::drop('sparqlload');

		// Drop the datatank publisher table
		Schema::drop('tdtpublisher');
	}
}