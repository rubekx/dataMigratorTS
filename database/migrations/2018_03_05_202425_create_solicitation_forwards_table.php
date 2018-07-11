<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSolicitationForwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitation_forwards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('solicitation_id')->unsigned();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('consultant_profile_id')->unsigned();
            $table->foreign('consultant_profile_id')->references('id')->on('profiles');
            $table->integer('regulator_profile_id')->unsigned();
            $table->foreign('regulator_profile_id')->references('id')->on('profiles');
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
        Schema::table('solicitation_forwards', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['consultant_profile_id']);
          $table->dropForeign(['regulator_profile_id']);
        });
        Schema::dropIfExists('solicitation_forwards');
    }
}
