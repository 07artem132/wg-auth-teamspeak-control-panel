<?php

namespace App\Jobs;

use Log;
use Cache;
use App\Services\TeamSpeak;
use Illuminate\Bus\Queueable;
use App\Services\TeamSpeakWgAuth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\FastWargamingInfo;
use App\Traits\TeamSpeak3GetClientGroupTraits;

class UserAuthUpdateTeamSpeakClientGroupJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TeamSpeak3GetClientGroupTraits;
	private $instanses;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $Instanse ) {
		$this->instanses = $Instanse;
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
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'wg_auth_bot' ) {
						foreach ( $server['ts_client_wg_account'] as $client ) {
							try {
								if ( array_key_exists( 'clans', $server ) ) {
									$clientGroup = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );
									foreach ( $server['clans'] as $clan ) {
										$clanInfo = $TeamSpeakWgAuth->clanInfo( $clan['clan_id'] );
										if ( array_key_exists( $client['wg_account']['account_id'], $clanInfo[ $clan['clan_id'] ]['members'] ) ) {
											$ColumClientRank       = $clanInfo[ $clan['clan_id'] ]['members'][ $client['wg_account']['account_id'] ]['role'];
											$ColumClientRankRemove = $this->getAllColumName();

											$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
											unset( $ColumClientRankRemove[ $ColumClientRank ] );
											$ColumClientRankRemove = array_flip( $ColumClientRankRemove );

											if ( array_key_exists( $ColumClientRank, $clan ) ) {
												if ( ! array_key_exists( $clan[ $ColumClientRank ], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan[ $ColumClientRank ] );
												}
											}

											foreach ( $ColumClientRankRemove as $item ) {
												if ( array_key_exists( $item, $clan ) ) {
													if ( array_key_exists( $clan[ $item ], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan[ $item ] );
													}
												}
											}


											if ( ! array_key_exists( $clan['clan_tag'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['clan_tag'] );
											}
											continue 2;
										}
									}

									if ( is_null( $TeamSpeak ) ) {
										$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
									}

									$TeamSpeak->ServerUseByUID( $server['uid'] );
									$ColumClientRankRemove = $this->getAllColumName();
									foreach ( $server['clans'] as $clan ) {
										foreach ( $ColumClientRankRemove as $item ) {
											if ( array_key_exists( $item, $clan ) ) {
												if ( array_key_exists( $clan[ $item ], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan[ $item ] );
												}
											}
										}
										if ( array_key_exists( $clan['clan_tag'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['clan_tag'] );
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


	protected function getAllColumName() {
		return [
			'commander',
			'executive_officer',
			'personnel_officer',
			'combat_officer',
			'intelligence_officer',
			'quartermaster',
			'recruitment_officer',
			'junior_officer',
			'private',
			'recruit',
			'reservist',
			//clan_tag
		];
	}

}
