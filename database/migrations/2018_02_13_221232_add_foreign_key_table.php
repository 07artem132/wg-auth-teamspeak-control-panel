<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
			Schema::table( 'servers', function ( Blueprint $table ) {
				$table->unsignedInteger( 'instanse_id' )->change();

				$table->foreign( 'instanse_id' )
				      ->references( 'id' )->on( 'instanses' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
			} );

			Schema::table( 'server_modules', function ( Blueprint $table ) {
				$table->unsignedInteger( 'server_id' )->change();
				$table->unsignedInteger( 'module_id' )->change();

				$table->foreign( 'server_id' )
				      ->references( 'id' )->on( 'servers' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
				$table->foreign( 'module_id' )
				      ->references( 'id' )->on( 'modules' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
			} );

			Schema::table( 'server_module_options', function ( Blueprint $table ) {
				$table->unsignedInteger( 'server_module_id' )->change();
				$table->unsignedInteger( 'module_option_id' )->change();

				$table->foreign( 'server_module_id' )
				      ->references( 'id' )->on( 'server_modules' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
				$table->foreign( 'module_option_id' )
				      ->references( 'id' )->on( 'module_options' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
			} );

			Schema::table( 'module_options', function ( Blueprint $table ) {
				$table->unsignedInteger( 'module_id' )->change();

				$table->foreign( 'module_id' )
				      ->references( 'id' )->on( 'modules' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
			} );

			Schema::table( 'ts_client_wg_accounts', function ( Blueprint $table ) {
				$table->foreign( 'wg_account_id' )
				      ->references( 'id' )->on( 'wg_accounts' )
				      ->onDelete( 'cascade' )
				      ->onUpdate( 'cascade' );
			} );

			Schema::table( 'server_clan_post_sgids', function ( Blueprint $table ) {
				$table->unsignedInteger( 'server_id' )->change();

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
		Schema::table( 'servers', function ( Blueprint $table ) {
			$table->dropForeign(['instanse_id']);
		} );

		Schema::table( 'server_modules', function ( Blueprint $table ) {
			$table->dropForeign(['server_id']);
			$table->dropForeign(['module_id']);
		} );

		Schema::table( 'server_module_options', function ( Blueprint $table ) {
			$table->dropForeign(['server_module_id']);
			$table->dropForeign(['module_option_id']);
		} );

		Schema::table( 'module_options', function ( Blueprint $table ) {
			$table->dropForeign(['module_id']);
		} );

		Schema::table( 'ts_client_wg_accounts', function ( Blueprint $table ) {
			$table->dropForeign(['wg_account_id']);

		} );

		Schema::table( 'server_clan_post_sgids', function ( Blueprint $table ) {
			$table->dropForeign(['server_id']);
		} );

	}
}
