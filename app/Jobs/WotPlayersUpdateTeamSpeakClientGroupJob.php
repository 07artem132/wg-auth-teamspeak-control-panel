<?php

namespace App\Jobs;

use Log;
use Illuminate\Bus\Queueable;
use App\Services\TeamSpeakWgAuth;
use App\Services\TeamSpeak;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Traits\TeamSpeak3GetClientGroupTraits;

class WotPlayersUpdateTeamSpeakClientGroupJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TeamSpeak3GetClientGroupTraits;

	private $instanses;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $instanses ) {
		$this->instanses = $instanses;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		try {
			$TeamSpeak       = null;
			$TeamSpeakWgAuth = new TeamSpeakWgAuth();
			foreach ( $this->instanses['servers'] as $server ) {
				foreach ( $server['modules'] as $module ) {
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
						foreach ( $server['ts_client_wg_account'] as $client ) {
							try {
								$clientGroup = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );

								foreach ( $server['clans'] as $clan ) {
									$clanInfo = $TeamSpeakWgAuth->clanInfo( $clan['clan_id'] );
									if ( array_key_exists( $client['wg_account']['account_id'], $clanInfo[ $clan['clan_id'] ]['members'] ) ) {
										if ( array_key_exists( $server['wot_players']['sg_id'], $clientGroup ) ) {
											if ( is_null( $TeamSpeak ) ) {
												$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
											}
											$TeamSpeak->ServerUseByUID( $server['uid'] );
											$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wot_players']['sg_id'] );
											if ( env( 'APP_DEBUG' ) ) {
												echo "client uid->" . $client['client_uid'] . " remove server group id->" . $server['wot_players']['sg_id'].PHP_EOL;
											}

											continue 2;
										}
										continue 2;
									}
								}

								if ( ! array_key_exists( $server['wot_players']['sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wot_players']['sg_id'] );
									if ( env( 'APP_DEBUG' ) ) {
										echo "client uid->" . $client['client_uid'] . " add to server group id->" . $server['wot_players']['sg_id'].PHP_EOL;
									}
									continue;
								}

							} catch ( \Exception $e ) {
								if ( $e->getMessage() != 'no client on server' ) {
									if ( ! is_null( $TeamSpeak ) ) {
										$TeamSpeak->ReturnConnection()->execute( 'quit' );
										$TeamSpeak = null;
									}
									Log::error( '-------------------------' );
									Log::error( 'wotID->' . $client['wg_account']['account_id'] );
									Log::error( 'uid->' . $client['client_uid'] );
									if ( isset( $clientGroup ) ) {
										Log::error( $clientGroup );
									}
									Log::error( $e->getMessage() );
									Log::error( $e->getTraceAsString() );
								}
							}
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			echo $e->getMessage() . PHP_EOL;
			echo $e->getTraceAsString() . PHP_EOL;
			Log::error( $e->getMessage() );
			Log::error( $e->getTraceAsString() );
		}
		if ( ! is_null( $TeamSpeak ) ) {
			$TeamSpeak->ReturnConnection()->execute( 'quit' );
		}
	}
}
