<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ServerWn8PostEfficienciesColumAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('server_wn8_post_efficiencies', function (Blueprint $table) {
		    $table->unsignedInteger('unicum_player_sg_id')->after('great_player_sg_id');
	    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('server_wn8_post_efficiencies', function (Blueprint $table) {
		    $table->dropColumn('unicum_player_sg_id');
	    });

    }
}
