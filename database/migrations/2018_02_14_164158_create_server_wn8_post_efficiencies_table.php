<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerWn8PostEfficienciesTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'server_wn8_post_efficiencies', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->unsignedInteger( 'server_id' );
			$table->unsignedInteger( 'red_sg_id' );
			$table->unsignedInteger( 'yellow_sg_id' );
			$table->unsignedInteger( 'green_sg_id' );
			$table->unsignedInteger( 'turquoise_sg_id' );
			$table->unsignedInteger( 'purple_sg_id' );
			$table->timestamps();
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
		Schema::dropIfExists( 'server_wn8_post_efficiencies' );
	}
}
