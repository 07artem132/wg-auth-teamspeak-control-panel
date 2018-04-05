<?php

namespace App\Http\Controllers;

use App\WgAccount;
use App\Services\TeamSpeak;
use Illuminate\Http\Request;
use App\Services\TeamSpeakWgAuth;
use App\Jobs\WotPlayersUpdateTeamSpeakClientGroupJob;
use App\Instanse;
use App\TsClientWgAccount;
use Cache;
use Log;

class TeamSpeakWotPlayersController extends Controller {
	function UserChengeGroupCron() {
		foreach ( Instanse::with( 'servers.modules.module', 'servers.wotPlayers', 'servers.TsClientWgAccount.wgAccount', 'servers.clans' )->get() as $Instanse ) {
			$this->dispatch( new WotPlayersUpdateTeamSpeakClientGroupJob( $Instanse->toArray() ) );
		}
	}

	function UserChengeGroupUid( $uid ) {
		try {
			$TeamSpeakWgAuth   = new TeamSpeakWgAuth();
			$tsClientWgAccount = TsClientWgAccount::with( 'wgAccount', 'server.modules.module', 'server.wotPlayers', 'server.TsClientWgAccount.wgAccount', 'server.clans' )->clientUID( $uid )->firstOrFail()->toArray();
			$server            = $tsClientWgAccount['server'];
			unset( $tsClientWgAccount['server'] );
			foreach ( $server['modules'] as $module ) {
				if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
					$TeamSpeak = new TeamSpeak( $server['instanse_id'] );
					$TeamSpeak->ServerUseByUID( $server['uid'] );
					try {
						$playerClanID = $TeamSpeakWgAuth->getAccountInfo( $tsClientWgAccount['wg_account']['account_id'] )->{$tsClientWgAccount['wg_account']['account_id']}->clan_id;
						if ( array_key_exists( 'wot_players', $server ) && ! empty( $server['wot_players']['sg_id'] ) ) {
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
									if ( array_key_exists( $server['wot_players']['sg_id'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wot_players']['sg_id'] );
										break 2;
									}
									continue 2;
								}
							}
							if ( ! array_key_exists( $server['wot_players']['sg_id'], $clientGroup ) ) {
								$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wot_players']['sg_id'] );
							}
						}

					} catch ( \Exception $e ) {
						echo $e->getMessage() . PHP_EOL;
						echo $e->getTraceAsString() . PHP_EOL;
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
