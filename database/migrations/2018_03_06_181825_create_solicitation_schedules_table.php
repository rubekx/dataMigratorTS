<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSolicitationSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitation_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('solicitation_id')->unsigned();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('solicitant_profile_id')->unsigned();
            $table->foreign('solicitant_profile_id')->references('id')->on('profiles');
            $table->integer('consultant_profile_id')->unsigned();
            $table->foreign('consultant_profile_id')->references('id')->on('profiles');
            $table->date('suggestion');
            $table->boolean('solicitant_acceptance');
            $table->boolean('consultant_acceptance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solicitation_schedules', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['solicitant_profile_id']);
          $table->dropForeign(['consultant_profile_id']);
        });
        Schema::dropIfExists('solicitation_schedules');
    }
}
