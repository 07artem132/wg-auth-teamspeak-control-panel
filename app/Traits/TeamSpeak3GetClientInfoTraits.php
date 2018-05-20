<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 20.05.2018
 * Time: 17:58
 */

namespace App\Traits;

use Cache;
use App\Services\TeamSpeak;
use TeamSpeak3_Helper_String;

trait TeamSpeak3GetClientInfoTraits {
	protected function GetClientInfo( $instanse_id, $server_uid, $client_uid ) {
		return cache::remember( "ts:$instanse_id:$server_uid:client:$client_uid", env( 'CLIENT_CACHE_TIME' ), function () use ( $instanse_id, $server_uid, $client_uid ) {
			try {
				$TeamSpeak = new TeamSpeak( $instanse_id );
				$TeamSpeak->ServerUseByUID( $server_uid );

				$ClientInfo = $TeamSpeak->ClientInfo( $client_uid );
				$TeamSpeak->ReturnConnection()->execute( 'quit' );

				array_walk( $ClientInfo, function ( &$value, &$key ) {
					if ( $value instanceof TeamSpeak3_Helper_String ) {
						$value = (string) $value;
					}
				} );

				return $ClientInfo;
			} catch ( \Exception $e ) {
				$TeamSpeak->ReturnConnection()->execute( 'quit' );
				if ( $e->getMessage() != 'database empty result set' ) {
					throw  new \Exception( $e->getMessage() );
				}

				return [];
			}
		} );
	}
}