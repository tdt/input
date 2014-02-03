<?php

use Illuminate\Database\Migrations\Migration;

class BaseUriRdfMapper extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Add column base_uri to the rdf mapper table
		Schema::table('rdfmap', function($table){

			$table->string('base_uri', 255);

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Drop the column base_uri of the rdf mapper table
		Schema::table('rdfmap', function($table){

			$table->dropColumn('base_uri');

		});
	}

}