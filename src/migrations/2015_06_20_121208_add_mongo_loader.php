<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMongoLoader extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Add mongo variables
		Schema::create('input_mongoload', function($table) {

			$table->increments('id');
			$table->string('database', 255);
			$table->integer('port');
			$table->string('collection', 255);
			$table->string('host', 255);
			$table->string('username', 255)->nullable();
			$table->string('password', 255)->nullable();
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
		// Drop the table
		Schema::drop('input_mongoload');
	}
}
