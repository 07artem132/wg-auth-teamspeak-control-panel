<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumServerIdServerClanPostSgidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('server_clan_post_sgids', function (Blueprint $table) {
		    $table->smallInteger('server_id')->unsigned()->after('id');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('server_clan_post_sgids', function (Blueprint $table) {
		    $table->dropColumn('server_id');
	    });
    }
}
