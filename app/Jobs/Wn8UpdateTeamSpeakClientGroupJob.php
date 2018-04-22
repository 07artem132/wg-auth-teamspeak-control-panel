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
use App\Services\WN8;

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
								foreach ( $server['clans'] as $clan ) {
									$clanInfo = $TeamSpeakWgAuth->clanInfo( $clan['clan_id'] );
									if ( array_key_exists( $client['wg_account']['account_id'], $clanInfo[ $clan['clan_id'] ]['members'] ) ) {
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
										$wn8         = new WN8( $client['wg_account']['account_id'] );
										$wn8         = $wn8->toInt();
										switch ( true ) {
											case $wn8 >= 0 && $wn8 <= 399:
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													}
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 400 && $wn8 <= 899:
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 900 && $wn8 <= 1469:
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );

													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 1470 && $wn8 <= 2179:
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 2180 && $wn8 <= 2879 :
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 2880 && $wn8 <= 9999 :
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
												}
												break;
										}
										continue 2;
									}
								}
								foreach ( $server['modules'] as $module ) {
									if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
										$clientGroup = (array) cache::remember( "ts:" . $server['uid'] . ":group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
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
										$wn8         = new WN8( $client['wg_account']['account_id'] );
										$wn8         = $wn8->toInt();
										switch ( true ) {
											case $wn8 >= 0 && $wn8 <= 399:
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
													}
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 400 && $wn8 <= 899:
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 900 && $wn8 <= 1469:
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );

													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 1470 && $wn8 <= 2179:
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 2180 && $wn8 <= 2879 :
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
													}
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );

												}
												break;
											case $wn8 >= 2880 && $wn8 <= 9999 :
												if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && ! array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );
												}
												if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
												}
												if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
												}
												if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
												}
												if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
												}
												break;
										}
										continue 2;
									}
								}
								$clientGroup = (array) cache::remember( "ts:" . $server['uid'] . ":group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
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
								if ( array_key_exists( 'red_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['red_sg_id'] );
								}
								if ( array_key_exists( 'yellow_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['yellow_sg_id'] );
								}
								if ( array_key_exists( 'turquoise_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['turquoise_sg_id'] );
								}
								if ( array_key_exists( 'green_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['green_sg_id'] );
								}
								if ( array_key_exists( 'purple_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['purple_sg_id'] );
								}
								if ( array_key_exists( 'terkin_sg_id', $server['wn8'] ) && array_key_exists( $server['wn8']['terkin_sg_id'], $clientGroup ) ) {
									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}
									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8']['terkin_sg_id'] );
								}

							} catch ( \Exception $e ) {
								if ( $e->getMessage() != 'no client on server' ) {
									if ( ! is_null( $TeamSpeak ) ) {
										$TeamSpeak->ReturnConnection()->execute( 'quit' );
										$TeamSpeak = null;
									}

									print_r( $clientGroup );
									echo 'wotID->' . $client['wg_account']['account_id'] . PHP_EOL;
									echo 'uid->' . $client['client_uid'] . PHP_EOL;
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
