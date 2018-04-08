<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use App\Services\TeamSpeakWgAuth;
use Log;
use App\Services\TeamSpeak;

class WotPlayersUpdateTeamSpeakClientGroupJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
								$playerClanID = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] )->{$client['wg_account']['account_id']}->clan_id;
								if ( array_key_exists( 'wot_players', $server ) && ! empty( $server['wot_players']['sg_id'] ) ) {
									$clientGroup = (array) cache::remember( "ts:" . $server['uid'] . ":group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
										$TeamSpeak->ServerUseByUID( $server['uid'] );
										try {
											$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client['client_uid'] );
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
												if ( is_null( $TeamSpeak ) ) {
													$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
												}
												$TeamSpeak->ServerUseByUID( $server['uid'] );
												$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wot_players']['sg_id'] );
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
									}
								}

							} catch ( \Exception $e ) {
								if ( $e->getMessage() != 'no client on server' ) {
									echo $e->getMessage() . PHP_EOL;
									echo $e->getTraceAsString() . PHP_EOL;
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
