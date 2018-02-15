<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerWotPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_wot_players', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger( 'server_id' );
	        $table->unsignedInteger( 'sg_id' );
	        $table->timestamps();
	        $table->foreign( 'server_id' )
	              ->references( 'id' )->on( 'servers' )
	              ->onDelete( 'cascade' )
	              ->onUpdate( 'cascade' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_wot_players');
    }
}
