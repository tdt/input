<?php

use Illuminate\Database\Migrations\Migration;

class AddMysqlExtractor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('input_mysqlextract', function ($table) {
            $table->increments('id');
            $table->string('host', 255);
            $table->integer('port')->default(3306);
            $table->string('database', 255)->nullable();
            $table->string('username', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->text('query');
            $table->string('collation', 255)->default('utf8_unicode_ci');
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
        Schema::drop('input_mysqlextract');
    }
}
