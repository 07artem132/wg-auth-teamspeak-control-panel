<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 25.05.2018
 * Time: 21:50
 */

namespace App\Http\Controllers;

use App\Services\TeamSpeak;
use App\Instanse;
use App\TsClientWgAccount;

class ClearOldDataControllers extends Controller {
	function  list() {
		foreach ( Instanse::with( 'servers.TsClientWgAccount' )->get() as $Instance ) {
			try {
				$TeamSpeak = new TeamSpeak( $Instance->id );
				foreach ( $Instance->servers as $server ) {
					try {
						$TeamSpeak->ServerUseByUID( $server->uid );
						foreach ( $server->TsClientWgAccount as $client ) {
							try {
								$TeamSpeak->clientGetServerGroupsByUid( $client->client_uid );
							} catch ( \TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
								echo '<pre>server uid ->' . $server->uid . ' uid->' . $client->client_uid . '  message->' . $e->getMessage() . '</pre>' . PHP_EOL;
								TsClientWgAccount::find( $client->id )->delete();
							}
						}
					} catch ( \Exception $e ) {
						var_dump( $e->getMessage() );
						var_dump( $e->getTraceAsString() );
					}
				}
			} catch ( \Exception $e ) {
				var_dump( $e->getMessage() );
				var_dump( $e->getTraceAsString() );
			}
			$TeamSpeak->ReturnConnection()->execute( 'quit' );
		}
	}
}