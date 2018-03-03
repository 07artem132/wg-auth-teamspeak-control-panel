<?php

namespace App\Http\Controllers;

use App\WgAccount;
use App\Services\WN8;
use App\Services\TeamSpeak;
use Illuminate\Http\Request;

class TeamspeakWn8GroupController extends Controller {
	function UserChengeGroupCron() {
		foreach ( WgAccount::all() as $account ) {
			$wn8 = (string) new WN8( $account->account_id );
			foreach ( $account->tsClient as $tsClient ) {
				$modules = $tsClient->server->modules();
				foreach ( $modules->serverID( $tsClient->server->id )->enable()->get() as $module ) {
					if ( $module->module->name == 'wn8' ) {
						$TeamSpeak = new TeamSpeak( $tsClient->server->instanse->id );
						$TeamSpeak->ServerUseByUID( $tsClient->server->uid );
						foreach ( $module->options as $option3 ) {
							if ( $option3->option->name == 'nickname' ) {
								$TeamSpeak->updateNickname( $option3->value );

							}
						}

						if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wn8->red_sg_id ) ) {
							if ( $wn8 > 949 ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wn8->red_sg_id );
							}
						}

						if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wn8->yellow_sg_id ) ) {
							if ( $wn8 < 950 || $wn8 > 1549 ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wn8->yellow_sg_id );
							}
						}

						if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wn8->green_sg_id ) ) {
							if ( $wn8 < 1550 || $wn8 > 2349 ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wn8->green_sg_id );
							}
						}

						if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wn8->turquoise_sg_id ) ) {
							if ( $wn8 < 2350 || $wn8 > 3129 ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wn8->turquoise_sg_id );
							}
						}

						if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wn8->purple_sg_id ) ) {
							if ( $wn8 < 3130 ) {
								$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wn8->purple_sg_id );
							}
						}

						switch ( true ) {
							case $wn8 > 0 && $wn8 <= 949:
								$sgid = $tsClient->server->wn8->red_sg_id;
								break;
							case $wn8 > 950 && $wn8 <= 1549:
								$sgid = $tsClient->server->wn8->yellow_sg_id;
								break;
							case $wn8 > 1550 && $wn8 <= 2349:
								$sgid = $tsClient->server->wn8->green_sg_id;
								break;
							case $wn8 > 2350 && $wn8 <= 3129:
								$sgid = $tsClient->server->wn8->turquoise_sg_id;
								break;
							case $wn8 > 3130 && $wn8 <= 9999 :
								$sgid = $tsClient->server->wn8->purple_sg_id;
								break;
						}

						if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $sgid ) ) {
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
												$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $sgid );
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
												$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $sgid );
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
	}
}