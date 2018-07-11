<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSolicitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_id')->unsigned();
            $table->foreign('type_id')->references('id')->on('types');
            $table->integer('profile_id')->unsigned();
            $table->foreign('profile_id')->references('id')->on('profiles');
            $table->text('description');
            $table->integer('status_id')->unsigned();
            $table->foreign('status_id')->references('id')->on('statu');
            $table->integer('cid1_id')->nullable()->unsigned();
            $table->foreign('cid1_id')->references('id')->on('cids');
            $table->integer('cid2_id')->nullable()->unsigned();
            $table->foreign('cid2_id')->references('id')->on('cids');
            $table->integer('ciap1_id')->nullable()->unsigned();
            $table->foreign('ciap1_id')->references('id')->on('ciaps');
            $table->integer('ciap2_id')->nullable()->unsigned();
            $table->foreign('ciap2_id')->references('id')->on('ciaps');
            $table->integer('ciap3_id')->nullable()->unsigned();
            $table->foreign('ciap3_id')->references('id')->on('ciaps');
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
        Schema::table('solicitations', function (Blueprint $table) {
          $table->dropForeign(['type_id']);
          $table->dropForeign(['profile_id']);
          $table->dropForeign(['status_id']);
          $table->dropForeign(['cid1_id']);
          $table->dropForeign(['cid2_id']);
          $table->dropForeign(['ciap1_id']);
          $table->dropForeign(['ciap2_id']);
          $table->dropForeign(['ciap3_id']);
        });
        Schema::dropIfExists('solicitations');
    }
}
