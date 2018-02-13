<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerClanPostSgidsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'server_clan_post_sgids', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->Integer( 'clan_id' )->unsigned()->nullable();
			$table->Integer( 'commander' )->unsigned()->nullable();
			$table->Integer( 'executive_officer' )->unsigned()->nullable();
			$table->Integer( 'personnel_officer' )->unsigned()->nullable();
			$table->Integer( 'combat_officer' )->unsigned()->nullable();
			$table->Integer( 'intelligence_officer' )->unsigned()->nullable();
			$table->Integer( 'quartermaster' )->unsigned()->nullable();
			$table->Integer( 'recruitment_officer' )->unsigned()->nullable();
			$table->Integer( 'junior_officer' )->unsigned()->nullable();
			$table->Integer( 'private' )->unsigned()->nullable();
			$table->Integer( 'recruit' )->unsigned()->nullable();
			$table->Integer( 'reservist' )->unsigned()->nullable();
			$table->Integer( 'clan_tag' )->unsigned()->nullable();
			$table->timestamps();
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'server_clan_post_sgids' );
	}
}
