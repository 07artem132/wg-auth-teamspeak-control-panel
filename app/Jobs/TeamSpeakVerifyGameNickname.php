<?php

namespace App\Jobs;

use Log;
use App\Services\TeamSpeak;
use Illuminate\Bus\Queueable;
use App\Services\TeamSpeakWgAuth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use TeamSpeak3_Helper_String;
use App\TsClientWgAccount;

class TeamSpeakVerifyGameNickname implements ShouldQueue {
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
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'verify_game_nickname' ) {
						foreach ( $server['ts_client_wg_account'] as $client ) {
							try {
								$clientNickname = (string) cache::remember( "ts:client:" . $client['client_uid'], 5, function () use ( $client, $server ) {
									$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									try {
										$ClientInfo = $TeamSpeak->ClientInfo( $client['client_uid'] );
									} catch ( \Exception $e ) {
										$TeamSpeak->ReturnConnection()->execute( 'quit' );
										throw  new \Exception( 'no client on server' );
									}
									$TeamSpeak->ReturnConnection()->execute( 'quit' );

									return $ClientInfo;
								} )['client_nickname'];
								$clientNickname = (string) $clientNickname;
								$playerNickname = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] )->{$client['wg_account']['account_id']}->nickname;
								$playerClanID   = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] )->{$client['wg_account']['account_id']}->clan_id;

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
												$clientGroup = (array) cache::remember( "ts:group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
													$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client['client_uid'] );
													$TeamSpeak->ReturnConnection()->execute( 'quit' );

													return $clientServerGroupsByUid;
												} );
												if ( ! array_key_exists( $server['no_valid_nickname']['sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
												}
											} else {
												foreach ( $server['modules'] as $module ) {
													if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
														$clientGroup = (array) cache::remember( "ts:group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
															$TeamSpeak               = new TeamSpeak( $this->instanses['id'] );
															$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client['client_uid'] );
															$TeamSpeak->ReturnConnection()->execute( 'quit' );

															return $clientServerGroupsByUid;
														} );
														if ( ! array_key_exists( $server['no_valid_nickname']['sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
														}
													}
												}
											}
										}
									}
								} else {
									if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
										$clientGroup = (array) cache::remember( "ts:group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
											$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
											$TeamSpeak->ServerUseByUID( $server['uid'] );
											$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client['client_uid'] );
											$TeamSpeak->ReturnConnection()->execute( 'quit' );

											return $clientServerGroupsByUid;
										} );
										if ( array_key_exists( $server['no_valid_nickname']['sg_id'], $clientGroup ) ) {
											if ( is_null( $TeamSpeak ) ) {
												$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
											}
											$TeamSpeak->ServerUseByUID( $server['uid'] );
											$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
										}
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
