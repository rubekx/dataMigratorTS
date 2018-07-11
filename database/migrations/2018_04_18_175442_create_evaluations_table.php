<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvaluationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('solicitation_id')->unsigned();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('satisfaction_status_id')->unsigned();
            $table->foreign('satisfaction_status_id')->references('id')->on('statu');
            $table->integer('attendance_status_id')->unsigned();
            $table->foreign('attendance_status_id')->references('id')->on('statu');
            $table->text('description')->nullable();
            $table->boolean("avoided_forwarding")->nullable();
            $table->boolean("induced_forwarding")->nullable();
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
        Schema::table('evaluations', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['satisfaction_status_id']);
          $table->dropForeign(['attendance_status_id']);
        });
        Schema::dropIfExists('evaluations');
    }
}
