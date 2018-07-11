<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('solicitation_id')->unsigned();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('status_id')->unsigned();
            $table->foreign('status_id')->references('id')->on('statu');
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
        Schema::table('statuses_history', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['status_id']);
        });
        Schema::dropIfExists('statuses_history');
    }
}
