<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 10.02.2018
 * Time: 1:00
 */

namespace App\Services;

use App\Services\TeamSpeak;
use App\Services\WargamingAPI;
use Cache;
use App\TsClientWgAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeamSpeakWgAuth {

	function __construct() {
		WargamingAPI::setApplicationId( env( 'WG_APP_ID' ) );
	}

	function setToken( $token ) {
		WargamingAPI::setToken( $token );
	}

	function getAccountInfo( $account_id, $access_token = null ) {
		$data = Cache::remember( "account:$account_id", 30, function () use ( $account_id ) {
			return WargamingAPI::wot()->account->info( array( 'account_id' => $account_id ) );
		} );

		return $data;

	}

	function genAuthUrl( $redirect_to = '' ) {
		return WargamingAPI::genAuthUrl( $redirect_to );
	}

	function prolongateToken( $token, $expires_at = 604800 ) {
		return WargamingAPI::wot()->auth->prolongate( array( 'access_token' => $token, 'expires_at' => $expires_at ) );
	}

	function clanInfo( $ClanID ) {
		$data = Cache::remember( "clan:$ClanID", 30, function () use ( $ClanID ) {
			return WargamingAPI::wgn()->clans->info( [ 'clan_id' => $ClanID, 'members_key' => 'id' ] );
		} );

		return $data;
	}

	function GetVerifyID( $VerifyInfo ) {
		$VerifyID = crc32( $VerifyInfo["client_uid"] . $VerifyInfo["server_uid"] );

		Cache::put( "PendingVerify:$VerifyID", json_encode( $VerifyInfo ), 10 );

		return $VerifyID;
	}

	function GetVerifyDataByID( $VerifyID ) {
		return Cache::get( "PendingVerify:$VerifyID" );
	}

	function ClientUidIsRegister( $uid ) {
		try {
			TsClientWgAccount::clientUID( $uid )->firstOrFail();

			return true;
		} catch ( ModelNotFoundException $e ) {
			return false;
		}
	}
}