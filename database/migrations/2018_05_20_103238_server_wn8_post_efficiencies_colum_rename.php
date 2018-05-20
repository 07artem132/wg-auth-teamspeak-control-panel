<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ServerWn8PostEfficienciesColumRename extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table( 'server_wn8_post_efficiencies', function ( Blueprint $table ) {
			$table->renameColumn( 'red_sg_id', 'bad_player_sg_id' );
			$table->renameColumn( 'yellow_sg_id', 'player_below_average_sg_id' );
			$table->renameColumn( 'green_sg_id', 'good_player_sg_id' );
			$table->renameColumn( 'turquoise_sg_id', 'average_player_sg_id' );
			$table->renameColumn( 'purple_sg_id', 'great_player_sg_id' );
		} );

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table( 'server_wn8_post_efficiencies', function ( Blueprint $table ) {
			$table->renameColumn( 'bad_player_sg_id', 'red_sg_id' );
			$table->renameColumn( 'player_below_average_sg_id', 'yellow_sg_id' );
			$table->renameColumn( 'good_player_sg_id', 'green_sg_id' );
			$table->renameColumn( 'average_player_sg_id', 'turquoise_sg_id' );
			$table->renameColumn( 'great_player_sg_id', 'purple_sg_id' );
		} );

	}
}
