<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropServerClansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::dropIfExists('server_clans');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::create('server_clans', function (Blueprint $table) {
		    $table->increments('id');
		    $table->smallInteger('server_id')->unsigned();
		    $table->integer('clan_id')->unsigned();
		    $table->timestamps();
	    });
    }
}
