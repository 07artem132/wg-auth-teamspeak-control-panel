<?php

namespace App\Http\Controllers;

use App\WgAccount;
use App\Services\TeamSpeak;
use Illuminate\Http\Request;
use App\Services\TeamSpeakWgAuth;

class TeamSpeakWotPlayersController extends Controller {
	function UserChengeGroupCron() {
		$TeamSpeakWgAuth = new TeamSpeakWgAuth();

		foreach ( WgAccount::all() as $account ) {
			foreach ( $account->tsClient as $tsClient ) {
				try {
					$modules = $tsClient->server->modules();
					foreach ( $modules->serverID( $tsClient->server->id )->enable()->get() as $module ) {
						if ( $module->module->name == 'wot_players' ) {
							$TeamSpeak = new TeamSpeak( $tsClient->server->instanse->id );
							$TeamSpeak->ServerUseByUID( $tsClient->server->uid );
							foreach ( $module->options as $option3 ) {
								if ( $option3->option->name == 'nickname' ) {
									$TeamSpeak->updateNickname( $option3->value );

								}
							}

							foreach ( $tsClient->server->clans as $clan ) {
								$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );
								if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role ) ) {
									if ( ! empty( $tsClient->server->wotPlayers->sg_id ) ) {
										if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id );
											$TeamSpeak->ReturnConnection()->execute( 'quit' );
											break 2;
										}
									}
									$TeamSpeak->ReturnConnection()->execute( 'quit' );
									break 2;
								}
							}

							if ( ! empty( $tsClient->server->wotPlayers->sg_id ) ) {
								foreach ( $module->options as $option ) {
									if ( $option->option->name == 'message_type' ) {
										if ( $option->value == 'poke' ) {
											foreach ( $module->options as $option2 ) {
												if ( $option2->option->name == 'message_success' ) {
													if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id );
														foreach ( $module->options as $option3 ) {
															if ( $option3->option->name == 'notify' && $option3->value == 'enable' ) {
																$TeamSpeak->SendPokeClient( $tsClient->client_uid, $option2->value );
															}
														}
														$TeamSpeak->ReturnConnection()->execute( 'quit' );
														continue;
													}
												}
											}

										} elseif ( $option->value == 'message' ) {
											foreach ( $module->options as $option2 ) {
												if ( $option2->option->name == 'message_success' ) {
													if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id );
														foreach ( $module->options as $option3 ) {
															if ( $option3->option->name == 'notify' && $option3->value == 'enable' ) {
																$TeamSpeak->SendMessageClient( $tsClient->client_uid, $option2->value );
															}
														}
														$TeamSpeak->ReturnConnection()->execute( 'quit' );
														continue;
													}

												}
											}
										}
									}
								}
							}
							$TeamSpeak->ReturnConnection()->execute( 'quit' );
						}
					}
				} catch ( \Exception $e ) {
					echo 'error->' . $account->account_id . PHP_EOL;
					echo $e->getMessage() . PHP_EOL;
					echo '------' . PHP_EOL;
					$TeamSpeak->ReturnConnection()->execute( 'quit' );
				}
			}
		}
	}
}
