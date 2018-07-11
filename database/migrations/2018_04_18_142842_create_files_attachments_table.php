<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->text('file_name');
            $table->text('path');
            $table->integer('solicitation_id')->unsigned()->nullable();
            $table->foreign('solicitation_id')->references('id')->on('solicitations');
            $table->integer('answer_id')->unsigned()->nullable();
            $table->foreign('answer_id')->references('id')->on('answers');
            $table->integer('solicitation_forward_id')->unsigned()->nullable();
            $table->foreign('solicitation_forward_id')->references('id')->on('solicitation_forwards');
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
        Schema::table('file_attachments', function (Blueprint $table) {
          $table->dropForeign(['solicitation_id']);
          $table->dropForeign(['answer_id']);
          $table->dropForeign(['solicitation_forward_id']);
        });
        Schema::dropIfExists('file_attachments');
    }
}
