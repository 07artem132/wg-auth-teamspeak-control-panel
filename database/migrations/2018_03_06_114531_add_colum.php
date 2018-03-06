<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColum extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('server_wn8_post_efficiencies', function (Blueprint $table) {
			$table->smallInteger('terkin_sg_id')->unsigned()->after('purple_sg_id');
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
			$table->dropColumn('terkin_sg_id');
		});
	}}
