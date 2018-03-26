<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\TeamSpeak;
use App\Services\TeamSpeakWgAuth;
use Cache;
use Log;

class Wn8UpdateTeamSpeakClientGroupJob implements ShouldQueue {
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
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'wn8' ) {
						foreach ( $server['ts_client_wg_account'] as $client ) {
							try {
								$playerClanID = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] )->{$client['wg_account']['account_id']}->clan_id;
								foreach ( $server['clans'] as $clan ) {
									if ( $clan['clan_id'] == $playerClanID ) {
										$clientGroup = (array) cache::remember( "ts:group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
											$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
											$TeamSpeak->ServerUseByUID( $server['uid'] );
											try {
												$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client['client_uid'] );
											} catch ( \Exception $e ) {
												$TeamSpeak->ReturnConnection()->execute( 'quit' );
												throw  new \Exception( 'no client on server' );
											}
											$TeamSpeak->ReturnConnection()->execute( 'quit' );

											return $clientServerGroupsByUid;
										} );
										$wn8         = Cache::get( "wn8:" . $client['wg_account']['account_id'] );
										switch ( true ) {
											case $wn8 >= 0 && $wn8 <= 949:
												if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
													}
													if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

													}
												}
												break;
											case $wn8 >= 950 && $wn8 <= 1549:
												if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
													if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													}
													if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

													}
												}
												break;
											case $wn8 >= 1550 && $wn8 <= 2349:
												if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
													if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													}
													if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

													}
												}
												break;
											case $wn8 >= 2350 && $wn8 <= 3129:
												if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
													if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													}
													if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

													}
												}
												break;
											case $wn8 >= 3130 && $wn8 <= 9999 :
												if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
													if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													}
													if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

													}
													if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

													}
												}
												break;
										}
									} else {
										foreach ( $server['modules'] as $module ) {
											if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
												$clientGroup = (array) cache::remember( "ts:group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
													$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													try {
														$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $client['client_uid'] );
													} catch ( \Exception $e ) {
														$TeamSpeak->ReturnConnection()->execute( 'quit' );
														throw  new \Exception( 'no client on server' );
													}
													$TeamSpeak->ReturnConnection()->execute( 'quit' );

													return $clientServerGroupsByUid;
												} );
												$wn8         = Cache::get( "wn8:" . $client['wg_account']['account_id'] );
												switch ( true ) {
													case $wn8 >= 0 && $wn8 <= 949:
														if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
															if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
															}
															if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

															}
														}
														break;
													case $wn8 >= 950 && $wn8 <= 1549:
														if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
															if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
															}
															if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

															}
														}
														break;
													case $wn8 >= 1550 && $wn8 <= 2349:
														if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
															if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
															}
															if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

															}
														}
														break;
													case $wn8 >= 2350 && $wn8 <= 3129:
														if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
															if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
															}
															if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

															}
														}
														break;
													case $wn8 >= 3130 && $wn8 <= 9999 :
														if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
															if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
															}
															if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

															}
															if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
																$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

															}
														}
														break;
												}
											}
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
