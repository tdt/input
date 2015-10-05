<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ElasticsearchLoader extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_elasticsearchload', function ($table) {
            $table->increments('id', true);
            $table->string('host', 255);
            $table->integer('port');
            $table->string('es_index', 255);
            $table->string('es_type', 255);
            $table->string('username', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->timestamps();
        });

        Schema::drop('input_graph');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('input_elasticsearchload');

        Schema::create('input_graph', function ($table) {
            $table->increments('id');
            $table->string('graph_id', 255);
            $table->string('graph_name', 255);
            $table->string('version', 255);
            $table->timestamps();
        });
    }
}
