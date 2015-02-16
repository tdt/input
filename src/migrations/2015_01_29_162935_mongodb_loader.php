<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MongodbLoader extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create the mongo loader table
		Schema::create('input_mongoload', function ($table) {

			$table->increments('id');
			$table->string('model', 255);
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
		// Drop the mongo loader table
		Schema::drop('input_mongoload');
	}

}
