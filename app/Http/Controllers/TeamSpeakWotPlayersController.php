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
				$modules = $tsClient->server->modules();
				foreach ( $modules->serverID( $tsClient->server->id )->enable()->get() as $module ) {
					if ( $module->module->name == 'wot_players' ) {
						$TeamSpeak = new TeamSpeak( $tsClient->server->instanse->id );
						$TeamSpeak->ServerUseByUID( $tsClient->server->uid );

						foreach ( $tsClient->server->clans as $clan ) {
							$ClanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );
							if ( ! empty( $ClanInfo->{$clan->clan_id}->members->{$account->account_id}->role ) ) {
								if ( ! empty( $tsClient->server->wotPlayers->sg_id ) ) {
									if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id );

										break 2;
									}
								}

								break 2;
							}
						}

						if ( ! empty( $tsClient->server->wotPlayers->sg_id ) ) {
							if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id ) ) {
								$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $tsClient->server->wotPlayers->sg_id );

								continue;
							}
						}

					}
				}
			}
		}
	}
}
