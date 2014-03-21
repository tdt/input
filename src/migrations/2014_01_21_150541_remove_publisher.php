<?php

use Illuminate\Database\Migrations\Migration;

class RemovePublisher extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Remove the publishing table of tdt
		Schema::drop('input_tdtpublish');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Create the datatank publisher table
		Schema::create('input_tdtpublish', function($table){

            $table->increments('id');
            $table->string('uri', 255);
			$table->string('user', 255);
			$table->string('password', 255);

            // created_at | updated_at DATETIME, are default expected by the Eloquent ORM
            $table->timestamps();
        });
	}

}