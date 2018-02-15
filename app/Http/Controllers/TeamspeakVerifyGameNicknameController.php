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
									$TeamSpeak->ClientAddServerGroup( $tsClient->client_uid, $tsClient->server->NoValidNickname->sg_id );
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
