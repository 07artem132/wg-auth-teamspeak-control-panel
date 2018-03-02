<?php

use Illuminate\Database\Seeder;

class module extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table( 'modules' )->insert(
			[
				[
					'name' => 'hello_bot',
				],
				[
					'name' => 'wg_auth_bot',
				],
				[
					'name' => 'nickname_change',
				],
				[
					'name' => 'wn8',
				],
				[
					'name' => 'wot_players',
				],
				[
					'name' => 'verify_game_nickname',
				]
			]
		);
	}
}
