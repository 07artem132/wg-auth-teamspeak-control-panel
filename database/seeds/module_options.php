<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class module_options extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		DB::table( 'module_options' )->insert(
			[
				[ 'module_id' => 2, 'name' => '	message_success' ],
				[ 'module_id' => 2, 'name' => '	url' ],
				[ 'module_id' => 2, 'name' => '	cid' ],
				[ 'module_id' => 3, 'name' => '	nickname' ],
				[ 'module_id' => 1, 'name' => '	message_type' ],
				[ 'module_id' => 2, 'name' => '	message_type' ],
				[ 'module_id' => 4, 'name' => '	message_type' ],
				[ 'module_id' => 5, 'name' => '	message_type' ],
				[ 'module_id' => 6, 'name' => '	message_type' ],
				[ 'module_id' => 4, 'name' => '	message_error' ],
				[ 'module_id' => 5, 'name' => '	message_error' ],
				[ 'module_id' => 6, 'name' => '	message_error' ],
				[ 'module_id' => 4, 'name' => '	message_success' ],
				[ 'module_id' => 5, 'name' => '	message_success' ],
				[ 'module_id' => 6, 'name' => '	message_success' ],
				[ 'module_id' => 2, 'name' => '	message_error_clan_not_allowed_or_no_clan' ],
				[ 'module_id' => 2, 'name' => '	message_error_module_is_disabled' ],
				[ 'module_id' => 2, 'name' => '	message_error_server_not_found' ],
				[ 'module_id' => 4, 'name' => '	nickname' ],
				[ 'module_id' => 5, 'name' => '	nickname' ],
				[ 'module_id' => 6, 'name' => '	nickname' ],
				[ 'module_id' => 2, 'name' => '	message_authorization_required' ],
				[ 'module_id' => 6, 'name' => '	notify' ],
				[ 'module_id' => 5, 'name' => '	notify' ],
				[ 'module_id' => 4, 'name' => '	notify' ],
				[ 'module_id' => 2, 'name' => '	move_to_default_channel' ],
				[ 'module_id' => 2, 'name' => '	chat_notify_group_success' ],
				[ 'module_id' => 2, 'name' => '	chat_notify_grpup_error' ],
				[ 'module_id' => 2, 'name' => '	chat_notify_group_success_message' ],
				[ 'module_id' => 2, 'name' => '	chat_notify_group_success_error' ]
			]
		);
	}
}
