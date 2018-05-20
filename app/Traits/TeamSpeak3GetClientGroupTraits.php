<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 20.05.2018
 * Time: 15:00
 */

namespace App\Traits;

use Cache;
use App\Services\TeamSpeak;
use TeamSpeak3_Helper_String;

trait TeamSpeak3GetClientGroupTraits {

	protected function GetClientGroup( $instanse_id, $server_uid, $client_uid ) {
		return cache::remember( "ts:$instanse_id:$server_uid:group:$client_uid", env( 'GROUP_CACHE_TIME' ), function () use ( $instanse_id, $server_uid, $client_uid ) {
			try {
				$TeamSpeak = new TeamSpeak( $instanse_id );
				$TeamSpeak->ServerUseByUID( $server_uid );
				$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client_uid );
				$TeamSpeak->ReturnConnection()->execute( 'quit' );

				array_walk( $clientServerGroupsByUid, function ( &$value, &$key ) {
					if ( $value instanceof TeamSpeak3_Helper_String ) {
						$value = (string) $value;
					}
				} );

				return $clientServerGroupsByUid;
			} catch ( \Exception $e ) {
				if ( isset( $TeamSpeak ) ) {
					$TeamSpeak->ReturnConnection()->execute( 'quit' );
				}

				if ( $e->getMessage() != 'database empty result set' ) {
					throw  new \Exception( $e->getMessage() );
				}

				return [];
			}
		} );
	}

}