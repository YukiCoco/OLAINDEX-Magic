<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnedriveAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onedrive_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->string('account_type')->nullable();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('access_token_expires')->nullable();
            $table->string('account_email')->nullable();
            $table->string('account_state')->nullable();
            $table->string('account_extend')->nullable();
            $table->string('nick_name')->nullable();
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
        Schema::dropIfExists('onedrive_accounts');
    }
}
