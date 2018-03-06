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
use App\ServerWgAuthNotifyAuthSuccessGroup;
use App\Http\Controllers\TeamspeakWn8GroupController;
use App\Http\Controllers\TeamspeakVerifyGameNicknameController;
use App\Http\Controllers\TeamSpeakWotPlayersController;

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

				foreach ( $TeamSpeakServer->modules as $module ) {
					if ( $module->module->name == 'wg_auth_bot' ) {
						foreach ( $module->options as $option3 ) {
							if ( $option3->option->name == 'chat_notify_group_success' ) {
								if ( $option3->value == 'enable' ) {
									$ServerWgAuthNotifyAuthSuccessGroup = ServerWgAuthNotifyAuthSuccessGroup::where( 'server_id', '=', $TeamSpeakServer->id )->first();
									$ServerWgAuthNotifyAuthSuccessGroup = $ServerWgAuthNotifyAuthSuccessGroup->makeHidden( [
										'server_id',
										'id',
										'created_at',
										'updated_at'
									] )->toArray();
									foreach ( $ServerWgAuthNotifyAuthSuccessGroup as $group => $status ) {
										if ( $status == 1 ) {
											foreach ( $TeamSpeakServer->clans as $ClanNotify ) {
												foreach ( $module->options as $option2 ) {
													if ( $option2->option->name == 'chat_notify_group_success_message' ) {
														$ts3conn->ReturnConnection()->serverGroupGetById( $ClanNotify->$group )->message( $option2->value );
													}
												}

											}
										}
									}
								}

							}
						}
					}
				}
				$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
				$TeamspeakWn8GroupController->UserChengeGroupCron();
				$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
				$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();

				return response( 'авторизация прошла нормально', 200 );
			}
		}
		$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
		$TeamspeakWn8GroupController->UserChengeGroupCron();
		$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
		$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();
		$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
		$TeamSpeakWotPlayersController->UserChengeGroupCron();

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
						$Clients = TsClientWgAccount::clientUID( $request->input( "client_uid" ) )->get();
						foreach ( $Clients as $Client ) {
							if ( $Client->server->uid == $request->input( "server_uid" ) ) {
								$ts3conn = new TeamSpeak( $server->instanse->id );
								$ts3conn->ServerUseByUID( $request->input( "server_uid" ) );

								foreach ( $server->clans as $clan ) {
									$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );

									if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$Client->wgAccount->account_id}->role ) ) {

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
										foreach ( $module->options as $option3 ) {
											if ( $option3->option->name == 'chat_notify_group_success' ) {
												if ( $option3->value == 'enable' ) {
													$ServerWgAuthNotifyAuthSuccessGroup = ServerWgAuthNotifyAuthSuccessGroup::where( 'server_id', '=', $Client->server->id )->first();
													$ServerWgAuthNotifyAuthSuccessGroup = $ServerWgAuthNotifyAuthSuccessGroup->makeHidden( [
														'server_id',
														'id',
														'created_at',
														'updated_at'
													] )->toArray();
													foreach ( $ServerWgAuthNotifyAuthSuccessGroup as $group => $status ) {
														if ( $status == 1 ) {
															foreach ( $server->clans as $ClanNotify ) {
																foreach ( $module->options as $option2 ) {
																	if ( $option2->option->name == 'chat_notify_group_success_message' ) {
																		$ts3conn->ReturnConnection()->serverGroupGetById( $ClanNotify->$group )->message( $option2->value );
																	}
																}

															}
														}
													}
												}

											}
										}
										$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
										$TeamspeakWn8GroupController->UserChengeGroupCron();
										$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
										$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();
										$ts3conn->ReturnConnection()->execute( 'quit' );

										return response()->json( [ 'verify' => 'successfully' ] );
									}
								}
								$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
								$TeamspeakWn8GroupController->UserChengeGroupCron();
								$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
								$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();
								$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
								$TeamSpeakWotPlayersController->UserChengeGroupCron();
								$ts3conn->ReturnConnection()->execute( 'quit' );

								return response()->json( [ 'verify' => 'ClanNotAllowedOrNoClan' ] );

							}
						}

						$TsClientWgAccount                = new TsClientWgAccount;
						$TsClientWgAccount->server_id     = $Client->server->id;
						$TsClientWgAccount->wg_account_id = $Client->wgAccount->id;
						$TsClientWgAccount->client_uid    = $request->input( "client_uid" );
						$TsClientWgAccount->saveOrFail();

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

								foreach ( $module->options as $option3 ) {
									if ( $option3->option->name == 'chat_notify_group_success' ) {
										if ( $option3->value == 'enable' ) {
											$ServerWgAuthNotifyAuthSuccessGroup = ServerWgAuthNotifyAuthSuccessGroup::where( 'server_id', '=', $Client->server->id )->first();
											$ServerWgAuthNotifyAuthSuccessGroup = $ServerWgAuthNotifyAuthSuccessGroup->makeHidden( [
												'server_id',
												'id',
												'created_at',
												'updated_at'
											] )->toArray();
											foreach ( $ServerWgAuthNotifyAuthSuccessGroup as $group => $status ) {
												if ( $status == 1 ) {
													foreach ( $server->clans as $ClanNotify ) {
														foreach ( $module->options as $option2 ) {
															if ( $option2->option->name == 'chat_notify_group_success_message' ) {
																$ts3conn->ReturnConnection()->serverGroupGetById( $ClanNotify->$group )->message( $option2->value );
															}
														}

													}
												}
											}
										}

									}
								}
								$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
								$TeamspeakWn8GroupController->UserChengeGroupCron();
								$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
								$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();

								$ts3conn->ReturnConnection()->execute( 'quit' );

								return response()->json( [ 'verify' => 'successfully' ] );
							}
						}
						$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
						$TeamspeakWn8GroupController->UserChengeGroupCron();
						$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
						$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();
						$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
						$TeamSpeakWotPlayersController->UserChengeGroupCron();

						return response()->json( [ 'verify' => 'ClanNotAllowedOrNoClan' ] );
					}
				}
			}

			return response()->json( [ 'verify' => 'ModuleIsDisabled' ] );
		} catch ( ModelNotFoundException $e ) {
			return response()->json( [ 'verify' => 'ServerNotFound' ] );
		}
	}

	function UserChengeGroupCron() {
		$TeamSpeakWgAuth = new TeamSpeakWgAuth();

		foreach ( WgAccount::all() as $account ) {
			foreach ( $account->tsClient as $tsClient ) {
				$modules = $tsClient->server->modules();
				foreach ( $modules->serverID( $tsClient->server->id )->enable()->get() as $module ) {
					if ( $module->module->name == 'wg_auth_bot' ) {
						$TeamSpeak = new TeamSpeak( $tsClient->server->instanse->id );
						$TeamSpeak->ServerUseByUID( $tsClient->server->uid );
						foreach ( $tsClient->server->clans as $clan ) {
							$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );

							if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role ) ) {
								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'commander' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->commander ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->commander );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'commander' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->commander ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->commander );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}


								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'executive_officer' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->executive_officer ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->executive_officer );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'executive_officer' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->executive_officer ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->executive_officer );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'personnel_officer' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->personnel_officer ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->personnel_officer );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'personnel_officer' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->personnel_officer ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->personnel_officer );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'combat_officer' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->combat_officer ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->combat_officer );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'combat_officer' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->combat_officer ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->combat_officer );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'intelligence_officer' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->intelligence_officer ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->intelligence_officer );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'intelligence_officer' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->intelligence_officer ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->intelligence_officer );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'quartermaster' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->quartermaster ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->quartermaster );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'quartermaster' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->quartermaster ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->quartermaster );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'recruitment_officer' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->recruitment_officer ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->recruitment_officer );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'recruitment_officer' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->recruitment_officer ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->recruitment_officer );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'junior_officer' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->junior_officer ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->junior_officer );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'junior_officer' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->junior_officer ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->junior_officer );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'private' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->private ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->private );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'private' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->private ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->private );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'recruit' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->recruit ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->recruit );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'recruit' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->recruit ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->recruit );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

								if ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role != 'reservist' ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->reservist ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->reservist );
									}
								} elseif ( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role == 'reservist' ) {
									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->reservist ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->reservist );
									}

									if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $clan->clan_tag );
									}
								}

							} else {
								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->commander ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->commander );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->executive_officer ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->executive_officer );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->personnel_officer ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->personnel_officer );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->combat_officer ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->combat_officer );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->intelligence_officer ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->intelligence_officer );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->quartermaster ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->quartermaster );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->recruitment_officer ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->recruitment_officer );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->junior_officer ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->junior_officer );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->private ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->private );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->recruit ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->recruit );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->reservist ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->reservist );
								}

								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $clan->clan_tag ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $clan->clan_tag );
								}
							}
						}
						$TeamSpeak->ReturnConnection()->execute( 'quit' );
					}
				}
			}
		}
	}
}
