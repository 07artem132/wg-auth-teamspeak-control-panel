<?php

namespace App\Jobs;

use Log;
use App\Services\WN8;
use App\Services\TeamSpeak;
use Illuminate\Bus\Queueable;
use App\Services\TeamSpeakWgAuth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Traits\TeamSpeak3GetClientGroupTraits;

class Wn8UpdateTeamSpeakClientGroupJob implements ShouldQueue {
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
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'wn8' ) {
						foreach ( $server['ts_client_wg_account'] as $client ) {
							try {
								foreach ( $server['clans'] as $clan ) {
									$clanInfo = $TeamSpeakWgAuth->clanInfo( $clan['clan_id'] );
									if ( array_key_exists( $client['wg_account']['account_id'], $clanInfo[ $clan['clan_id'] ]['members'] ) ) {
										$clientGroup           = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );
										$wn8                   = new WN8( $client['wg_account']['account_id'] );
										$wn8                   = $wn8->__toInt();
										$ColumClientRank       = $this->wn8RatingToRankColumName( $wn8 );
										$ColumClientRankRemove = $this->getAllColumName();

										$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
										unset( $ColumClientRankRemove[ $ColumClientRank ] );
										$ColumClientRankRemove = array_flip( $ColumClientRankRemove );

										if ( array_key_exists( $ColumClientRank, $server['wn8'] ) ) {
											if ( ! array_key_exists( $server['wn8'][ $ColumClientRank ], $clientGroup ) ) {
												if ( is_null( $TeamSpeak ) ) {
													$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
												}
												$TeamSpeak->ServerUseByUID( $server['uid'] );
												$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8'][ $ColumClientRank ] );
											}
										}

										foreach ( $ColumClientRankRemove as $item ) {
											if ( array_key_exists( $item, $server['wn8'] ) ) {
												if ( array_key_exists( $server['wn8'][ $item ], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8'][ $item ] );
												}
											}
										}
										continue 2;
									}
								}

								foreach ( $server['modules'] as $module ) {
									if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
										$clientGroup           = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );
										$wn8                   = new WN8( $client['wg_account']['account_id'] );
										$wn8                   = $wn8->__toInt();
										$ColumClientRank       = $this->wn8RatingToRankColumName( $wn8 );
										$ColumClientRankRemove = $this->getAllColumName();

										$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
										unset( $ColumClientRankRemove[ $ColumClientRank ] );
										$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
										if ( array_key_exists( $ColumClientRank, $server['wn8'] ) ) {
											if ( ! array_key_exists( $server['wn8'][ $ColumClientRank ], $clientGroup ) ) {
												if ( is_null( $TeamSpeak ) ) {
													$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
												}
												$TeamSpeak->ServerUseByUID( $server['uid'] );
												$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['wn8'][ $ColumClientRank ] );
											}
										}

										foreach ( $ColumClientRankRemove as $item ) {
											if ( array_key_exists( $item, $server['wn8'] ) ) {
												if ( array_key_exists( $server['wn8'][ $item ], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8'][ $item ] );
												}
											}
										}
										continue 2;
									}
								}

								$clientGroup           = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );
								$ColumClientRankRemove = $this->getAllColumName();

								foreach ( $ColumClientRankRemove as $item ) {
									if ( array_key_exists( $item, $server['wn8'] ) ) {
										if ( array_key_exists( $server['wn8'][ $item ], $clientGroup ) ) {
											if ( is_null( $TeamSpeak ) ) {
												$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
											}
											$TeamSpeak->ServerUseByUID( $server['uid'] );
											$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['wn8'][ $item ] );
										}
									}
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


	protected function wn8RatingToRankColumName( $wn8 ) {
		switch ( true ) {
			case $wn8 >= 0 && $wn8 <= 399:
				return 'bad_player_sg_id';
				break;
			case $wn8 >= 400 && $wn8 <= 899:
				return 'player_below_average_sg_id';
				break;
			case $wn8 >= 900 && $wn8 <= 1469:
				return 'good_player_sg_id';
				break;
			case $wn8 >= 1470 && $wn8 <= 2179:
				return 'average_player_sg_id';
				break;
			case $wn8 >= 2180 && $wn8 <= 2879 :
				return 'great_player_sg_id';
				break;
			case $wn8 >= 2880 && $wn8 <= 9999 :
				return 'unicum_player_sg_id';
				break;
			default:
				return 'bad_player_sg_id';
		}
	}

	protected function getAllColumName() {
		return [
			'bad_player_sg_id',
			'player_below_average_sg_id',
			'good_player_sg_id',
			'average_player_sg_id',
			'great_player_sg_id',
			'unicum_player_sg_id',
		];
	}


}
