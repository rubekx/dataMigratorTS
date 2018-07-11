<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSolicitationObservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitation_observations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('solicitation_id')->unsigned();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('profile_id')->unsigned();
            $table->foreign('profile_id')->references('id')->on('profiles');
            $table->integer('current_status_id')->unsigned();
            $table->foreign('current_status_id')->references('id')->on('statu');
            $table->text('description');
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
        Schema::table('solicitation_observations', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['profile_id']);
          $table->dropForeign(['current_status_id']);
        });
        Schema::dropIfExists('solicitation_observations');
    }
}
