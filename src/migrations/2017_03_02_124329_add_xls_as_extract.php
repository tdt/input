<?php

use Illuminate\Database\Migrations\Migration;

class AddXlsAsExtract extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the csv extract table
        Schema::create('input_xlsextract', function ($table) {
            $table->increments('id');
            $table->string('uri', 255);
            $table->string('sheet', 255);
            $table->string('has_header_row', 255);
            $table->integer('start_row');

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
        Schema::drop('xlsextract');
    }
}
