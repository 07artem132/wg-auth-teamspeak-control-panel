<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 24.03.2018
 * Time: 10:09
 */

namespace App\Http\Controllers;

use App\Jobs\WN8UpdateCacheJob;
use App\WgAccount;

class Wn8UpdateCache extends Controller{
	function Cron() {
		foreach ( WgAccount::all()->toArray() as $account ) {
			$this->dispatch( new WN8UpdateCacheJob( $account['account_id'] ) );
		}
	}

}