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

class TeamSpeakWgAuth {

	function __construct() {
		WargamingAPI::setApplicationId( env( 'WG_APP_ID' ) );
	}

	function setToken( $token ) {
		WargamingAPI::setToken( $token );
	}

	function getAccountInfo( $account_id, $access_token = null ) {
		return WargamingAPI::wot()->account->info( array( 'account_id' => $account_id ) );
	}

	function genAuthUrl( $redirect_to = '' ) {
		return WargamingAPI::genAuthUrl( $redirect_to );
	}

	function prolongateToken( $token, $expires_at = 604800 ) {
		return WargamingAPI::wot()->auth->prolongate( array( 'access_token' => $token, 'expires_at' => $expires_at ) );
	}

	function clanInfo( $ClanID ) {
		return WargamingAPI::wgn()->clans->info( [ 'clan_id' => $ClanID, 'members_key' => 'id' ] );
	}
}