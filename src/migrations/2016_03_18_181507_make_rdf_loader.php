<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeRdfLoader extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_virtuosoload', function ($table) {
            $table->increments('id');
            $table->string('endpoint', 255);
            $table->integer('port');
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('graph', 255);
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
        Schema::drop('input_virtuosoload');
    }
}
