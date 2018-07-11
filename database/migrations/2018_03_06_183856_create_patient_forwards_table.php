<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientForwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_forwards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('solicitation_id')->unsigned();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('patient_id')->nullable()->unsigned();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->integer('cbo_id')->nullable()->unsigned();
            $table->foreign('cbo_id')->references('id')->on('cbo');
            $table->boolean('is_requested');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_forwards', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['patient_id']);
          $table->dropForeign(['cbo_id']);
        });
        Schema::dropIfExists('patient_forwards');
    }
}
