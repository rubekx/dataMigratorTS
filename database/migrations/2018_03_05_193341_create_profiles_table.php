<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('cbo_id')->unsigned();
            $table->foreign('cbo_id')->references('id')->on('cbo');
            $table->integer('status_id')->unsigned();
            $table->foreign('status_id')->references('id')->on('statu');
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
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
        Schema::table('profiles', function (Blueprint $table) {
          $table->dropForeign(['user_id']);
          $table->dropForeign(['cbo_id']);
          $table->dropForeign(['status_id']);
          $table->dropForeign(['role_id']);
        });
        Schema::dropIfExists('profiles');
    }
}
