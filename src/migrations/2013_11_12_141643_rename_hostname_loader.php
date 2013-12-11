<?php

use Illuminate\Database\Migrations\Migration;

class RenameHostnameLoader extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Rename the column hostname to graph_name
		Schema::table('sparqlload', function($table){
			$table->renameColumn('hostname', 'graph_name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Revert the renaming process
		Schema::table('sparqlload', function($table){
			$table->renameColumn('graph_name', 'hostname');
		});
	}

}