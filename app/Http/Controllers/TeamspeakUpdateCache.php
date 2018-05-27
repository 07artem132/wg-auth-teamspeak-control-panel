<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 14.03.2018
 * Time: 15:42
 */

namespace App\Http\Controllers;

use App\Jobs\TeamspeakUpdateCacheJob;
use App\Instanse;

class TeamspeakUpdateCache extends Controller {

	function Cron() {
		foreach ( Instanse::with( 'servers.TsClientWgAccount' )->get() as $Instanse ) {
			$this->dispatch( new TeamspeakUpdateCacheJob( $Instanse ) );
		}
	}
}