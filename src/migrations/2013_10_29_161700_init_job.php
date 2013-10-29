<?php

use Illuminate\Database\Migrations\Migration;

class InitJob extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create the table for the job model
		Schema::create('job', function($table){

			$table->increments('id');
			$table->string('name', 255);
			$table->string('collection_uri', 255);
			$table->integer('extractor_id');
			$table->string('extractor_type', 255);
			$table->integer('mapper_id')->nullable();
			$table->string('mapper_type', 255)->nullable();
			$table->integer('loader_id');
			$table->string('loader_type', 255)
			$table->integer('publisher_id')->nullable();
			$table->string('publisher_type', 255)->nullable();

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
		// Drop the table for the job model
		Schema::drop('job');
	}

}