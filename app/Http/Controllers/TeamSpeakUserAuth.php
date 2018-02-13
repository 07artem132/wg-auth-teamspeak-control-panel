<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\server;
use App\Services\WargamingAPI;
use App\ServerClan;
use App\Services\TeamSpeak;
use App\ServerClanPostSgid;
use App\Services\TeamSpeakWgAuth;
use App\Traits\JsonDecodeAndValidate;
use App\WgAccount;
use App\TsClientWgAccount;

class TeamSpeakUserAuth extends Controller {
	use JsonDecodeAndValidate;

	function RegistrationWgVerify( Request $request, $id ) {
		/**
		 * $TsVerifyInfo->client_uid
		 * $TsVerifyInfo->server_uid
		 */
		$TsVerifyInfo = $this->JsonDecodeAndValidate( Redis::get( 'PendingVerify:' . $id ) );

		$WargamingAPI = new TeamSpeakWgAuth();
		$WgUserInfo   = $WargamingAPI->prolongateToken( $request->input( 'access_token' ) );

		try {
			$WgAccounts = WgAccount::account_id( $WgUserInfo->account_id )->firstOrFail();
		} catch ( ModelNotFoundException $e ) {
			$WgAccounts                   = new WgAccount;
			$WgAccounts->account_id       = $WgUserInfo->account_id;
			$WgAccounts->token            = $WgUserInfo->access_token;
			$WgAccounts->token_expires_at = date( 'Y-m-d H:i:s', $WgUserInfo->expires_at );
			$WgAccounts->saveOrFail();
		}

		$TsClientWgAccount                = new TsClientWgAccount;
		$TsClientWgAccount->wg_account_id = $WgAccounts->id;
		$TsClientWgAccount->client_uid    = $TsVerifyInfo->client_uid;
		$TsClientWgAccount->saveOrFail();

		$TeamSpeakServer = server::uid( $TsVerifyInfo->server_uid )->firstOrFail();

		foreach ( $TeamSpeakServer->clans as $clan ) {
			$ClanInfo = $WargamingAPI->clanInfo( $clan->clan_id );
			if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$WgUserInfo->account_id}->role ) ) {
				$ts3conn = new TeamSpeak( $TeamSpeakServer->instanse->id );
				$ts3conn->ServerUseByUID( $TsVerifyInfo->server_uid );

				$ServerClanPostSgid = ServerClanPostSgid::clanID( $clan->clan_id )->firstOrFail();
				$SGID               = $ServerClanPostSgid->{$ClanInfo->{$clan->clan_id}->members->{$WgUserInfo->account_id}->role};

				if ( ! empty( $SGID ) && ! $ts3conn->ClientMemberOfServerGroupId( $TsVerifyInfo->client_uid, $SGID ) ) {
					$ts3conn->ClientAddServerGroup( $TsVerifyInfo->client_uid, $SGID );
				}

				$SGID = $ServerClanPostSgid->clan_tag;

				if ( ! empty( $SGID ) && ! $ts3conn->ClientMemberOfServerGroupId( $TsVerifyInfo->client_uid, $SGID ) ) {
					$ts3conn->ClientAddServerGroup( $TsVerifyInfo->client_uid, $SGID );
				}
				echo 'авторизация прошла нормально';

				return;
			}
		}
		echo 'к сожалению вы не состоите в нужном калне';

		return;
	}

	function Registration( $id ) {
		/**
		 * $TsVerifyInfo->client_uid
		 * $TsVerifyInfo->server_uid
		 */
		$TsVerifyInfo = $this->JsonDecodeAndValidate( Redis::get( 'PendingVerify:' . $id ) );

		$WargamingAPI = new TeamSpeakWgAuth();

		try {
			$TsClientWgAuth = TsClientWgAccount::clientUID( $TsVerifyInfo->client_uid )->firstOrFail();
		} catch ( ModelNotFoundException $e ) {

			return redirect( $WargamingAPI->genAuthUrl( env( 'APP_URL' ) . 'teamspeak/verify/' . $id . '/wg' ) );
		}

		$TeamSpeakServer = server::uid( $TsVerifyInfo->server_uid )->firstOrFail();

		foreach ( $TeamSpeakServer->clans as $clan ) {
			$ClanInfo = $WargamingAPI->clanInfo( $clan->clan_id );

			if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$TsClientWgAuth->wgAccount->account_id}->role ) ) {
				$ts3conn = new TeamSpeak( $TeamSpeakServer->instanse->id );
				$ts3conn->ServerUseByUID( $TsVerifyInfo->server_uid );

				$ServerClanPostSgid = ServerClanPostSgid::clanID( $clan->clan_id )->firstOrFail();
				$SGID               = $ServerClanPostSgid->{$ClanInfo->{$clan->clan_id}->members->{$TsClientWgAuth->wgAccount->account_id}->role};

				if ( ! empty( $SGID ) && ! $ts3conn->ClientMemberOfServerGroupId( $TsVerifyInfo->client_uid, $SGID ) ) {
					$ts3conn->ClientAddServerGroup( $TsVerifyInfo->client_uid, $SGID );
				}

				$SGID = $ServerClanPostSgid->clan_tag;

				if ( ! empty( $SGID ) && ! $ts3conn->ClientMemberOfServerGroupId( $TsVerifyInfo->client_uid, $SGID ) ) {
					$ts3conn->ClientAddServerGroup( $TsVerifyInfo->client_uid, $SGID );
				}
				echo 'авторизация прошла нормально';

				return;
			}
		}
		echo 'к сожалению вы не состоите в нужном калне';

		return;
	}

	function RegisterPendingVerify( Request $request ) {
		server::UID( $request->input( "server_uid" ) )->firstOrFail();

		$VerifyID = crc32( $request->input( "client_uid" ) . $request->input( "server_uid" ) );

		Redis::set( 'PendingVerify:' . $VerifyID, json_encode( $request->all() ) );
		Redis::expire( 'PendingVerify:' . $VerifyID, 60 * 5 );#default 5

		echo $VerifyID;
	}
}
