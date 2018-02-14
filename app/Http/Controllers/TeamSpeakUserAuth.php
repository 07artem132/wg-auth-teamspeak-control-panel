<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidJSON;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\server;
use App\Services\TeamSpeak;
use App\ServerClanPostSgid;
use App\Services\TeamSpeakWgAuth;
use App\Traits\JsonDecodeAndValidate;
use App\WgAccount;
use App\TsClientWgAccount;

class TeamSpeakUserAuth extends Controller {
	use JsonDecodeAndValidate;

	function RegistrationWgVerify( Request $request, $id ) {
		$TeamSpeakWgAuth = new TeamSpeakWgAuth();

		try {
			$TsVerifyInfo = $this->JsonDecodeAndValidate( $TeamSpeakWgAuth->GetVerifyDataByID( $id ) );
		} catch ( InvalidJSON $e ) {
			return response( 'Вероятно ссылка устарела...', 200 );
		}
		if ( $request->input( 'status' ) != 'ok' ) {
			return response( 'На стороне вг что-то пошло не так, возможно вы отменили авторизацию', 200 );
		}

		$WgUserInfo = $TeamSpeakWgAuth->prolongateToken( $request->input( 'access_token' ) );

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
			$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );

			if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$TsClientWgAccount->wgAccount->account_id}->role ) ) {
				$ts3conn = new TeamSpeak( $TeamSpeakServer->instanse->id );
				$ts3conn->ServerUseByUID( $TsVerifyInfo->server_uid );

				$SGID = $clan->{$ClanInfo->{$clan->clan_id}->members->{$TsClientWgAccount->wgAccount->account_id}->role};
				if ( ! empty( $SGID ) ) {
					if ( ! $ts3conn->ClientMemberOfServerGroupId( $TsVerifyInfo->client_uid, $SGID ) ) {
						$ts3conn->ClientAddServerGroup( $TsVerifyInfo->client_uid, $SGID );
					}
				}

				$SGID = $clan->clan_tag;
				if ( ! empty( $SGID ) ) {
					if ( ! $ts3conn->ClientMemberOfServerGroupId( $TsVerifyInfo->client_uid, $SGID ) ) {
						$ts3conn->ClientAddServerGroup( $TsVerifyInfo->client_uid, $SGID );
					}
				}

				return response( 'авторизация прошла нормально', 200 );
			}
		}

		return response( 'к сожалению вы не состоите в нужном калне', 200 );
	}

	function Registration( $id ) {
		$WargamingAPI = new TeamSpeakWgAuth();

		try {
			$this->JsonDecodeAndValidate( $WargamingAPI->GetVerifyDataByID( $id ) );
		} catch ( InvalidJSON $e ) {
			return response( 'Вероятно ссылка устарела...', 200 );
		}

		$url = $WargamingAPI->genAuthUrl( env( 'APP_URL' ) . 'user/verify/' . $id . '/wg' );

		return redirect( $url );
	}

	function VerifyPrivilege( Request $request ) {
		try {
			$server = server::UID( $request->input( "server_uid" ) )->firstOrFail();
			foreach ( $server->modules as $module ) {
				if ( $module->module->name == 'wg_auth_bot' ) {
					$TeamSpeakWgAuth = new TeamSpeakWgAuth();

					if ( ! $TeamSpeakWgAuth->ClientUidIsRegister( $request->input( "client_uid" ) ) ) {
						$VerifyID = $TeamSpeakWgAuth->GetVerifyID( $request->all() );

						return response()->json( [ 'verify' => 'AuthorizationRequired', 'verify_id' => $VerifyID ] );
					} else {
						$Client = TsClientWgAccount::clientUID( $request->input( "client_uid" ) )->firstOrFail();
						foreach ( $server->clans as $clan ) {
							$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );

							if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$Client->wgAccount->account_id}->role ) ) {
								$ts3conn = new TeamSpeak( $server->instanse->id );
								$ts3conn->ServerUseByUID( $request->input( "server_uid" ) );

								$SGID = $clan->{$ClanInfo->{$clan->clan_id}->members->{$Client->wgAccount->account_id}->role};
								if ( ! empty( $SGID ) ) {
									if ( ! $ts3conn->ClientMemberOfServerGroupId( $request->input( "client_uid" ), $SGID ) ) {
										$ts3conn->ClientAddServerGroup( $request->input( "client_uid" ), $SGID );
									}
								}

								$SGID = $clan->clan_tag;
								if ( ! empty( $SGID ) ) {
									if ( ! $ts3conn->ClientMemberOfServerGroupId( $request->input( "client_uid" ), $SGID ) ) {
										$ts3conn->ClientAddServerGroup( $request->input( "client_uid" ), $SGID );
									}
								}

								return response()->json( [ 'verify' => 'successfully' ] );
							}
						}

						return response()->json( [ 'verify' => 'ClanNotAllowedOrNoClan' ] );
					}
				}
			}

			return response()->json( [ 'verify' => 'ModuleIsDisabled' ] );
		} catch ( ModelNotFoundException $e ) {
			return response()->json( [ 'verify' => 'ServerNotFound' ] );
		}
	}
}
