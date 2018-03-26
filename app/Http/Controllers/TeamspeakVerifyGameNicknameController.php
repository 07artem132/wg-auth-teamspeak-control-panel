<?php

namespace App\Http\Controllers;

use Log;
use Cache;
use App\Services\TeamSpeak;
use App\Instanse;
use App\TsClientWgAccount;
use App\Jobs\TeamSpeakVerifyGameNickname;
use App\Services\TeamSpeakWgAuth;

class TeamspeakVerifyGameNicknameController extends Controller {
	function UserChengeGroupCron() {
		foreach ( Instanse::with( 'servers.modules.module', 'servers.NoValidNickname', 'servers.TsClientWgAccount.wgAccount', 'servers.clans' )->get() as $Instanse ) {
			$this->dispatch( new TeamSpeakVerifyGameNickname( $Instanse->toArray() ) );
		}
	}

	function UserChengeGroupUid( $uid ) {
		try {
			$TeamSpeakWgAuth   = new TeamSpeakWgAuth();
			$tsClientWgAccount = TsClientWgAccount::with( 'wgAccount', 'server.modules.module', 'server.NoValidNickname', 'server.TsClientWgAccount.wgAccount', 'server.clans' )->clientUID( $uid )->firstOrFail()->toArray();
			$server            = $tsClientWgAccount['server'];
			unset( $tsClientWgAccount['server'] );
			foreach ( $server['modules'] as $module ) {
				if ( $module['status'] == 'enable' && $module['module']['name'] == 'verify_game_nickname' ) {
					$TeamSpeak = new TeamSpeak( $server['instanse_id'] );
					$TeamSpeak->ServerUseByUID( $server['uid'] );
					try {
						$clientNickname = (string) $TeamSpeak->ClientInfo( $tsClientWgAccount['client_uid'] )['client_nickname'];
						$playerNickname = $TeamSpeakWgAuth->getAccountInfo( $tsClientWgAccount['wg_account']['account_id'] )->{$tsClientWgAccount['wg_account']['account_id']}->nickname;
						$playerClanID   = $TeamSpeakWgAuth->getAccountInfo( $tsClientWgAccount['wg_account']['account_id'] )->{$tsClientWgAccount['wg_account']['account_id']}->clan_id;
						preg_match_all( '/^(.*?)\s/', $clientNickname, $matches, PREG_SET_ORDER, 0 );

						if ( ! empty( $matches ) ) {
							$clientNicknameFilter = $matches[0][1];
						} else {
							$clientNicknameFilter = $clientNickname;
						}

						if ( $clientNicknameFilter != $playerNickname ) {
							if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
								foreach ( $server['clans'] as $clan ) {
									if ( $clan['clan_id'] == $playerClanID ) {
										if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClientWgAccount['client_uid'], $server['no_valid_nickname']['sg_id'] ) ) {
											$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['no_valid_nickname']['sg_id'] );
										}
									} else {
										foreach ( $server['modules'] as $module ) {
											if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
												if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $tsClientWgAccount['client_uid'], $server['no_valid_nickname']['sg_id'] ) ) {
													$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['no_valid_nickname']['sg_id'] );
												}
											}
										}
									}
								}
							}
						} else {
							if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
								if ( $TeamSpeak->ClientMemberOfServerGroupId( $tsClientWgAccount['client_uid'], $server['no_valid_nickname']['sg_id'] ) ) {
									$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['no_valid_nickname']['sg_id'] );
								}
							}
						}
					} catch ( \Exception $e ) {
						Log::error( $e->getMessage() );
						Log::error( $e->getTraceAsString() );
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
