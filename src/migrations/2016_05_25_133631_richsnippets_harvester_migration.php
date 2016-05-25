<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RichsnippetsHarvesterMigration extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_richsnippet_extract', function ($table) {
            $table->increments('id');
            $table->string('uri', 255);
            $table->text('follow_properties')->nullable();
            $table->string('base_uri', 255);
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
        Schema::drop('input_richsnippet_extract');
    }
}
