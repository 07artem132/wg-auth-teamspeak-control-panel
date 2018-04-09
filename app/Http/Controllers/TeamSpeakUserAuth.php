<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidJSON;
use App\Jobs\UserAuthUpdateTeamSpeakClientGroupJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\server;
use App\Services\TeamSpeak;
use App\Services\TeamSpeakWgAuth;
use App\Traits\JsonDecodeAndValidate;
use App\WgAccount;
use App\TsClientWgAccount;
use Cache;
use App\Instanse;
use Log;

class TeamSpeakUserAuth extends Controller {
	use JsonDecodeAndValidate;

	function RegistrationWgVerify( Request $request, $id ) {
		$TeamSpeakWgAuth = new TeamSpeakWgAuth();

		try {
			$TsVerifyInfo = $this->JsonDecodeAndValidate( $TeamSpeakWgAuth->GetVerifyDataByID( $id ) );
			cache::delete( "PendingVerify:$id" );
		} catch ( InvalidJSON $e ) {
			return response( 'Вероятно ссылка устарела...', 200 );
		}
		if ( $request->input( 'status' ) != 'ok' ) {
			return response( 'На стороне вг что-то пошло не так, возможно вы отменили авторизацию', 200 );
		}
		$TeamSpeakServer = server::uid( $TsVerifyInfo->server_uid )->firstOrFail();

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
		$TsClientWgAccount->server_id     = $TeamSpeakServer->id;
		$TsClientWgAccount->wg_account_id = $WgAccounts->id;
		$TsClientWgAccount->client_uid    = $TsVerifyInfo->client_uid;
		$TsClientWgAccount->saveOrFail();


		foreach ( $TeamSpeakServer->clans as $clan ) {
			$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );

			if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$TsClientWgAccount->wgAccount->account_id}->role ) ) {
				$this->UserChengeGroupUid( $TsVerifyInfo->client_uid );
				$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
				$TeamspeakWn8GroupController->UserChengeGroupUid( $TsVerifyInfo->client_uid );
				$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
				$TeamspeakVerifyGameNicknameController->UserChengeGroupUid( $TsVerifyInfo->client_uid );
				$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
				$TeamSpeakWotPlayersController->UserChengeGroupUid( $TsVerifyInfo->client_uid );

				return response( '<h1>авторизация прошла нормально</h1>', 200 );
			}
		}
		$this->UserChengeGroupUid( $TsVerifyInfo->client_uid );
		$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
		$TeamspeakWn8GroupController->UserChengeGroupUid( $TsVerifyInfo->client_uid );
		$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
		$TeamspeakVerifyGameNicknameController->UserChengeGroupUid( $TsVerifyInfo->client_uid );
		$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
		$TeamSpeakWotPlayersController->UserChengeGroupUid( $TsVerifyInfo->client_uid );

		return response( '<h1>К сожалению вы не состоите в нужном клане</h1><br/><h1>Ваш ник <span style="color: red;">'.$_GET['nickname'].'</span> если это не Ваш ник, то обратитесь к ☜★ ║Vasek_Terkin (Саня)║ ★☞</h1>', 200 );
	}

	function Registration( $id ) {
		$WargamingAPI = new TeamSpeakWgAuth();

		try {
			$this->JsonDecodeAndValidate( $WargamingAPI->GetVerifyDataByID( $id ) );
		} catch ( InvalidJSON $e ) {
			return response( '<h1>Вероятно ссылка устарела...</h1>', 200 );
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
						$Clients = TsClientWgAccount::clientUID( $request->input( "client_uid" ) )->get();
						foreach ( $Clients as $Client ) {
							if ( $Client->server->uid == $request->input( "server_uid" ) ) {
								$this->UserChengeGroupUid( $request->input( "client_uid" ) );
								$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
								$TeamspeakWn8GroupController->UserChengeGroupUid( $request->input( "client_uid" ) );
								$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
								$TeamspeakVerifyGameNicknameController->UserChengeGroupUid( $request->input( "client_uid" ) );
								$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
								$TeamSpeakWotPlayersController->UserChengeGroupUid( $request->input( "client_uid" ) );

								return response()->json( [ 'verify' => 'successfully' ] );

							}
						}

						$TsClientWgAccount                = new TsClientWgAccount;
						$TsClientWgAccount->server_id     = $server->id;
						$TsClientWgAccount->wg_account_id = $Client->wgAccount->id;
						$TsClientWgAccount->client_uid    = $request->input( "client_uid" );
						$TsClientWgAccount->saveOrFail();

						$this->UserChengeGroupUid( $request->input( "client_uid" ) );
						$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
						$TeamspeakWn8GroupController->UserChengeGroupUid( $request->input( "client_uid" ) );
						$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
						$TeamspeakVerifyGameNicknameController->UserChengeGroupUid( $request->input( "client_uid" ) );
						$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
						$TeamSpeakWotPlayersController->UserChengeGroupUid( $request->input( "client_uid" ) );

						return response()->json( [ 'verify' => 'successfully' ] );
					}
				}
			}

			return response()->json( [ 'verify' => 'ModuleIsDisabled' ] );
		} catch ( ModelNotFoundException $e ) {
			return response()->json( [ 'verify' => 'ServerNotFound' ] );
		}
	}

	function UserChengeGroupCron() {
		foreach ( Instanse::with( 'servers.modules.module', 'servers.TsClientWgAccount.wgAccount', 'servers.clans' )->get() as $Instanse ) {
			$this->dispatch( new UserAuthUpdateTeamSpeakClientGroupJob( $Instanse->toArray() ) );
		}
	}

	function UserChengeGroupUid( $uid ) {
		try {
			$TeamSpeakWgAuth   = new TeamSpeakWgAuth();
			$tsClientWgAccount = TsClientWgAccount::with( 'wgAccount', 'server.modules.module', 'server.TsClientWgAccount.wgAccount', 'server.clans' )->clientUID( $uid )->firstOrFail()->toArray();
			$server            = $tsClientWgAccount['server'];
			unset( $tsClientWgAccount['server'] );
			foreach ( $server['modules'] as $module ) {
				if ( $module['status'] == 'enable' && $module['module']['name'] == 'wg_auth_bot' ) {
					$TeamSpeak = new TeamSpeak( $server['instanse_id'] );
					$TeamSpeak->ServerUseByUID( $server['uid'] );
					try {
						$playerClanID = $TeamSpeakWgAuth->getAccountInfo( $tsClientWgAccount['wg_account']['account_id'] )->{$tsClientWgAccount['wg_account']['account_id']}->clan_id;
						$clanInfo     = $TeamSpeakWgAuth->clanInfo( $playerClanID );
						if ( array_key_exists( 'clans', $server ) ) {
							$clientGroup = (array) cache::remember( "ts:group:" . $tsClientWgAccount['client_uid'], 5, function () use ( $server, $tsClientWgAccount ) {
								$TeamSpeak = new TeamSpeak( $server['instanse_id'] );
								$TeamSpeak->ServerUseByUID( $server['uid'] );
								try {
									$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $tsClientWgAccount['client_uid'] );
								} catch ( \Exception $e ) {
									if ( $e->getMessage() != 'empty result set' ) {
										$TeamSpeak->ReturnConnection()->execute( 'quit' );
										throw  new \Exception( 'no client on server' );
									}
								}
								$TeamSpeak->ReturnConnection()->execute( 'quit' );

								return $clientServerGroupsByUid;
							} );

							foreach ( $server['clans'] as $clan ) {
								if ( $clan['clan_id'] == $playerClanID ) {
									switch ( true ) {
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'commander':
											if ( ! array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'executive_officer':
											if ( ! array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'personnel_officer':
											if ( ! array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'combat_officer':
											if ( ! array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'intelligence_officer':
											if ( ! array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'quartermaster':
											if ( ! array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'recruitment_officer':
											if ( ! array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'junior_officer':
											if ( ! array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'private':
											if ( ! array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'recruit':
											if ( ! array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}
											break;
										case $clanInfo->$playerClanID->members->{$tsClientWgAccount['wg_account']['account_id']}->role == 'reservist':
											if ( ! array_key_exists( $clan['reservist'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
											}

											if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
											}
											if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
											}
											if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
											}
											if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
											}
											if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
											}
											if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
											}
											if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
											}
											if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
											}
											if ( array_key_exists( $clan['private'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
											}
											if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
												$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
											}
											break;
									}
									if ( ! array_key_exists( $clan['clan_tag'], $clientGroup ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $clan['clan_tag'] );
									}
									continue 2;
								}
							}
							if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['commander'] );
							}
							if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['executive_officer'] );
							}
							if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['personnel_officer'] );
							}
							if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['combat_officer'] );
							}
							if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['intelligence_officer'] );
							}
							if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['quartermaster'] );
							}
							if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruitment_officer'] );
							}
							if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['junior_officer'] );
							}
							if ( array_key_exists( $clan['private'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['private'] );
							}
							if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['recruit'] );
							}
							if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['reservist'] );
							}
							if ( ! array_key_exists( $clan['clan_tag'], $clientGroup ) ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $clan['clan_tag'] );
							}
						}

					} catch ( \Exception $e ) {
						if ( $e->getMessage() != 'no client on server' ) {
							#echo $e->getMessage() . PHP_EOL;
							#echo $e->getTraceAsString() . PHP_EOL;
							Log::error( $e->getMessage() );
							Log::error( $e->getTraceAsString() );
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			Log::error( $e->getMessage() );
			Log::error( $e->getTraceAsString() );
		}

		try {
			if ( ! is_null( $TeamSpeak ) ) {
				$TeamSpeak->ReturnConnection()->execute( 'quit' );
			}
		} catch ( \Exception | \Throwable $e ) {
			Log::error( $e->getMessage() );
			Log::error( $e->getTraceAsString() );
		}
	}

}
