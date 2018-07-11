<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->string('ibge', 20);
            $table->integer('fu_id')->unsigned();
            $table->foreign('fu_id')->references('id')->on('fus');
            $table->integer('center_id')->nullable()->unsigned();
            $table->foreign('center_id')->references('id')->on('centers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
          $table->dropForeign(['fu_id']);
          $table->dropForeign(['center_id']);
        });
        Schema::dropIfExists('cities');
    }
}
