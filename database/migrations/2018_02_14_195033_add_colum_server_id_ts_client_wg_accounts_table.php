<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumServerIdTsClientWgAccountsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'ts_client_wg_accounts', function ( Blueprint $table ) {
			$table->unsignedInteger( 'server_id' )->after( 'id' );
			$table->foreign( 'server_id' )
			      ->references( 'id' )->on( 'servers' )
			      ->onDelete( 'cascade' )
			      ->onUpdate( 'cascade' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'ts_client_wg_accounts', function ( Blueprint $table ) {
			$table->dropColumn( 'server_id' );
			$table->dropForeign(['server_id']);
		} );

	}
}
