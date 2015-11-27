<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJobScheduler extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('input_job', function ($table) {
            $table->string('schedule', 255);
            $table->integer('date_executed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('input_job', function ($table) {
            $table->dropColumn('schedule');
            $table->dropColumn('date_executed');
        });
    }
}
