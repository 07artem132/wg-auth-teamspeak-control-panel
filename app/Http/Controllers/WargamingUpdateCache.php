<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 18.03.2018
 * Time: 9:33
 */

namespace App\Http\Controllers;

use App\WgAccount;
use App\ServerClanPostSgid;
use App\Jobs\WargamingUpdateCacheJob;
use App\Jobs\WargamingClanInfoUpdateCacheJob;

class WargamingUpdateCache extends Controller {

	function Cron() {
		foreach ( WgAccount::all()->toArray() as $account ) {
			$this->dispatch( new WargamingUpdateCacheJob( $account['account_id'] ) );
		}
		foreach ( ServerClanPostSgid::all()->toArray() as $item ) {
			$this->dispatch( new WargamingClanInfoUpdateCacheJob( $item['clan_id'] ) );
		}

	}

}