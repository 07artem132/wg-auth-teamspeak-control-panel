<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWgAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wg_accounts', function (Blueprint $table) {
            $table->increments('id');
	        $table->Integer('account_id')->unsigned()->unique();
	        $table->string('token');
	        $table->timestamp('token_expires_at')->nullable();
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
        Schema::dropIfExists('ts_client_wg_auths');
    }
}
