<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SparqlExtractor extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create the sparql extractor
		Schema::create('input_sparqlextract', function ($table) {

			$table->increments('id');
			$table->text('query');
			$table->string('endpoint', 255);
			$table->string('user', 255);
			$table->string('password', 255);
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
		// Drop the sparql extractor
		Schema::drop('input_sparqlextract');
	}
}
