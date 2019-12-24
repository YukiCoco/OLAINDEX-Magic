<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflineDlFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_dl_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('name')->nullable();
            $table->string('gid')->nullable();
            $table->string('upload_path')->nullable();
            $table->string('client_id')->nullable();
            $table->string('speed')->nullable();
            $table->string('status')->nullable();
            $table->string('progress')->nullable();
            $table->string('error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offline_dl_files');
    }
}
