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
use App\Traits\TeamSpeak3GetClientGroupTraits;
use App\Traits\TeamSpeak3GetClientNicknameTraits;

class TeamSpeakVerifyGameNickname implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TeamSpeak3GetClientGroupTraits, TeamSpeak3GetClientNicknameTraits;
	private $instanses;
	public $timeout = 900;

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
				if ( env( 'APP_DEBUG' ) ) {
					echo "iteration server->" . $server['uid'] . PHP_EOL;
				}
				foreach ( $server['modules'] as $module ) {
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'verify_game_nickname' ) {
						if ( env( 'APP_DEBUG' ) ) {
							echo "module verify_game_nickname active" . PHP_EOL;
						}
						foreach ( $server['ts_client_wg_account'] as $client ) {
							if ( env( 'APP_DEBUG' ) ) {
								echo "iteration wg_account_id->" . $client['wg_account']['account_id'] . ' client_uid->' . $client['client_uid'] . PHP_EOL;
							}
							try {
								$clientNickname = $this->GetClientNickname( $this->instanses['id'], $server['uid'], $client['client_uid'] );
								$accountInfo    = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] );

								if ( ! isset( $accountInfo->{$client['wg_account']['account_id']} ) || ! is_object( $accountInfo->{$client['wg_account']['account_id']} ) ) {
									continue;
								}

								$playerNickname = $accountInfo->{$client['wg_account']['account_id']}->nickname;

								preg_match_all( '/^(.*?)\s/', $clientNickname, $matches, PREG_SET_ORDER, 0 );

								if ( ! empty( $matches ) ) {
									$clientNicknameFilter = $matches[0][1];
								} else {
									$clientNicknameFilter = $clientNickname;
								}

								if ( $clientNicknameFilter != $playerNickname ) {
									if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
										foreach ( $server['clans'] as $clan ) {
											$clanInfo = $TeamSpeakWgAuth->clanInfo( $clan['clan_id'] );
											if ( array_key_exists( $client['wg_account']['account_id'], $clanInfo[ $clan['clan_id'] ]['members'] ) ) {

												$clientGroup = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );

												if ( ! array_key_exists( $server['no_valid_nickname']['sg_id'], $clientGroup ) ) {
													if ( is_null( $TeamSpeak ) ) {
														$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
													}
													$TeamSpeak->ServerUseByUID( $server['uid'] );
													$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
													if ( env( 'APP_DEBUG' ) ) {
														echo "client uid->" . $client['client_uid'] . " add to server group id->" . $server['no_valid_nickname']['sg_id'] . PHP_EOL;
													}

												}
											} else {
												foreach ( $server['modules'] as $module ) {
													if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
														$clientGroup = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );

														if ( ! array_key_exists( $server['no_valid_nickname']['sg_id'], $clientGroup ) ) {
															if ( is_null( $TeamSpeak ) ) {
																$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
															}
															$TeamSpeak->ServerUseByUID( $server['uid'] );
															$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
															if ( env( 'APP_DEBUG' ) ) {
																echo "client uid->" . $client['client_uid'] . " add to server group id->" . $server['no_valid_nickname']['sg_id'] . PHP_EOL;
															}

														}
													}
												}
											}
										}
									}
								} else {
									if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
										$clientGroup = $this->GetClientGroup( $this->instanses['id'], $server['uid'], $client['client_uid'] );

										if ( array_key_exists( $server['no_valid_nickname']['sg_id'], $clientGroup ) ) {
											if ( is_null( $TeamSpeak ) ) {
												$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
											}
											$TeamSpeak->ServerUseByUID( $server['uid'] );
											$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
											if ( env( 'APP_DEBUG' ) ) {
												echo "client uid->" . $client['client_uid'] . " remove from server group id->" . $server['no_valid_nickname']['sg_id'] . PHP_EOL;
											}

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
									Log::error( $accountInfo );

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
