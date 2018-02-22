<?php

namespace App\Http\Controllers;

use App\WgAccount;
use App\Services\TeamSpeak;
use Illuminate\Http\Request;
use App\Services\TeamSpeakWgAuth;

class TeamspeakVerifyGameNicknameController extends Controller {
	function UserChengeGroupCron() {
		$TeamSpeakWgAuth = new TeamSpeakWgAuth();

		foreach ( WgAccount::all() as $account ) {
			foreach ( $account->tsClient as $tsClient ) {
				$modules = $tsClient->server->modules();
				foreach ( $modules->serverID( $tsClient->server->id )->enable()->get() as $module ) {
					if ( $module->module->name == 'verify_game_nickname' ) {
						$TeamSpeak = new TeamSpeak( $tsClient->server->instanse->id );
						$TeamSpeak->ServerUseByUID( $tsClient->server->uid );
						foreach ( $module->options as $option3 ) {
							if ( $option3->option->name == 'nickname' ) {
								$TeamSpeak->updateNickname( $option3->value );

							}
						}

						$clientNickname = (string) $TeamSpeak->ClientInfo( $tsClient->client_uid )['client_nickname'];
						$playerNickname = $TeamSpeakWgAuth->getAccountInfo( $account->account_id )->{$account->account_id}->nickname;
						preg_match_all( '/^(.*?)\s/', $clientNickname, $matches, PREG_SET_ORDER, 0 );

						if ( ! empty( $matches ) ) {
							$clientNicknameFilter = $matches[0][1];
						} else {
							$clientNicknameFilter = $clientNickname;
						}

						if ( $clientNicknameFilter != $playerNickname ) {
							if ( ! empty( $tsClient->server->NoValidNickname->sg_id ) ) {
								if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->NoValidNickname->sg_id ) ) {
									foreach ( $module->options as $option ) {
										if ( $option->option->name == 'message_type' ) {
											if ( $option->value == 'poke' ) {
												foreach ( $module->options as $option2 ) {
													if ( $option2->option->name == 'message_success' ) {
														foreach ( $module->options as $option3 ) {
															if ( $option3->option->name == 'notify' && $option3->value == 'enable' ) {
																$TeamSpeak->SendPokeClient( $tsClient->client_uid, $option2->value );
															}
														}
														$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $tsClient->server->NoValidNickname->sg_id );
													}
												}

											} elseif ( $option->value == 'message' ) {
												foreach ( $module->options as $option2 ) {
													if ( $option2->option->name == 'message_success' ) {
														foreach ( $module->options as $option3 ) {
															if ( $option3->option->name == 'notify' && $option3->value == 'enable' ) {
																$TeamSpeak->SendMessageClient( $tsClient->client_uid, $option2->value );
															}
														}
														$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $tsClient->server->NoValidNickname->sg_id );
													}
												}
											}
										}
									}
								}
							}
						} else {
							if ( ! empty( $tsClient->server->NoValidNickname->sg_id ) ) {
								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->NoValidNickname->sg_id ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->NoValidNickname->sg_id );
								}
							}
						}
					}
				}
			}
		}
	}

}
