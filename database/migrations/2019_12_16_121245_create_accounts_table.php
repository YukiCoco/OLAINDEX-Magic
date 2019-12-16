<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('redirect_uri');
            $table->string('account_type');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->string('access_token_expires');
            $table->string('account_email');
            $table->string('account_state');
            $table->string('account_extend');
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
        Schema::dropIfExists('accounts');
    }
}
