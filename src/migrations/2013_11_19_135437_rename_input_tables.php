<?php

use Illuminate\Database\Migrations\Migration;

class RenameInputTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Rename the input tables to prefixed tables.
		Schema::rename('csvextract', 'input_csvextract');
		Schema::rename('xmlextract', 'input_xmlextract');
		Schema::rename('jsonextract', 'input_jsonextract');
		Schema::rename('shpextract', 'input_shpextract');

		Schema::rename('rdfmap', 'input_rdfmap');

		Schema::rename('sparqlload', 'input_sparqlload');

		Schema::rename('tdtpublisher', 'input_tdtpublish');

		Schema::rename('job', 'input_job');

		Schema::rename('graphs', 'input_graph');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Undo the renaming of the tables
		Schema::rename('input_csvextract', 'csvextract');
		Schema::rename('input_xmlextract', 'xmlextract');
		Schema::rename('input_jsonextract', 'jsonextract');
		Schema::rename('input_shpextract', 'shpextract');

		Schema::rename('input_rdfmap', 'rdfmap');

		Schema::rename('input_sparqlload', 'sparqlload');

		Schema::rename('input_tdtpublish', 'tdtpublisher');

		Schema::rename('input_job', 'job');

		Schema::rename('input_graph', 'graphs');
	}

}