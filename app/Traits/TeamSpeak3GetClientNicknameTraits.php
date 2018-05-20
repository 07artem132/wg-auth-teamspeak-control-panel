<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 20.05.2018
 * Time: 18:01
 */

namespace App\Traits;

use Cache;
use App\Services\TeamSpeak;

trait TeamSpeak3GetClientNicknameTraits {
	protected function GetClientNickname( $instanse_id, $server_uid, $client_uid ) {
		return cache::remember( "ts:$instanse_id:$server_uid:client:$client_uid:nickname", env( 'CLIENT_NICKNAME_CACHE_TIME' ), function () use ( $instanse_id, $server_uid, $client_uid ) {
			try {
				$TeamSpeak = new TeamSpeak( $instanse_id );
				$TeamSpeak->ServerUseByUID( $server_uid );

				$ClientInfo = $TeamSpeak->ClientInfo( $client_uid );
				$TeamSpeak->ReturnConnection()->execute( 'quit' );

				return (string)$ClientInfo['client_nickname'];
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