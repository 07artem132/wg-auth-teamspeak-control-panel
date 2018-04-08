<?php

namespace App\Http\Controllers;

use App\Jobs\Wn8UpdateTeamSpeakClientGroupJob;
use App\WgAccount;
use App\Services\WN8;
use App\Services\TeamSpeak;
use Illuminate\Http\Request;
use App\Instanse;
use App\Services\TeamSpeakWgAuth;
use Cache;
use Log;
use App\TsClientWgAccount;

class TeamspeakWn8GroupController extends Controller {
	function UserChengeGroupCron() {
		foreach ( Instanse::with( 'servers.modules.module', 'servers.wn8', 'servers.TsClientWgAccount.wgAccount', 'servers.clans' )->get() as $Instanse ) {
			$this->dispatch( new Wn8UpdateTeamSpeakClientGroupJob( $Instanse->toArray() ) );
		}
	}

	function UserChengeGroupUid( $uid ) {
		try {
			$TeamSpeakWgAuth   = new TeamSpeakWgAuth();
			$tsClientWgAccount = TsClientWgAccount::with( 'wgAccount', 'server.modules.module', 'server.wn8', 'server.TsClientWgAccount.wgAccount', 'server.clans' )->clientUID( $uid )->firstOrFail()->toArray();
			$server            = $tsClientWgAccount['server'];
			unset( $tsClientWgAccount['server'] );
			foreach ( $server['modules'] as $module ) {
				if ( $module['status'] == 'enable' && $module['module']['name'] == 'wn8' ) {
					$TeamSpeak = new TeamSpeak( $server['instanse_id'] );
					$TeamSpeak->ServerUseByUID( $server['uid'] );
					try {
						$playerClanID = $TeamSpeakWgAuth->getAccountInfo( $tsClientWgAccount['wg_account']['account_id'] )->{$tsClientWgAccount['wg_account']['account_id']}->clan_id;
						foreach ( $server['clans'] as $clan ) {
							if ( $clan['clan_id'] == $playerClanID ) {
								$clientGroup = (array) cache::remember( "ts:group:" . $tsClientWgAccount['client_uid'], 5, function () use ( $server, $tsClientWgAccount, $TeamSpeak ) {
									try {
										$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $tsClientWgAccount['client_uid'] );
									} catch ( \Exception $e ) {
										if ( $e->getMessage() != 'empty result set' ) {
											$TeamSpeak->ReturnConnection()->execute( 'quit' );
											throw  new \Exception( 'no client on server' );
										}
									}

									return $clientServerGroupsByUid;
								} );
								$wn8         = Cache::remember( "wn8:" . $tsClientWgAccount['wg_account']['account_id'], 1440, function () use ( $tsClientWgAccount ) {
									$wn8 = (string) new WN8( $tsClientWgAccount['wg_account']['account_id'] );
									Cache::put( "wn8:" . $tsClientWgAccount['wg_account']['account_id'], $wn8, 1440 );

									return $wn8;
								} );

								switch ( true ) {
									case $wn8 >= 0 && $wn8 <= 949:
										if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
											if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
											}
										}
										if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );

										}
										break;
									case $wn8 >= 950 && $wn8 <= 1549:
										if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
											if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );
											}
										}
										if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );

										}
										break;
									case $wn8 >= 1550 && $wn8 <= 2349:
										if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
											if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );
											}
										}
										if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );

										}
										break;
									case $wn8 >= 2350 && $wn8 <= 3129:
										if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
											if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );
											}
										}
										if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );
										}
										break;
									case $wn8 >= 3130 && $wn8 <= 9999 :
										if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
											if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );
											}
										}
										if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
										}
										if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

										}
										if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );

										}
										break;
								}
							} else {
								foreach ( $server['modules'] as $module ) {
									if ( $module['status'] == 'enable' && $module['module']['name'] == 'wot_players' ) {
										$clientGroup = (array) cache::remember( "ts:group:" . $tsClientWgAccount['client_uid'], 5, function () use ( $server, $tsClientWgAccount, $TeamSpeak ) {
											try {
												$clientServerGroupsByUid = $TeamSpeak->clientGetServerGroupsByUid( $tsClientWgAccount['client_uid'] );
											} catch ( \Exception $e ) {
												$TeamSpeak->ReturnConnection()->execute( 'quit' );
												throw  new \Exception( 'no client on server' );
											}

											return $clientServerGroupsByUid;
										} );
										$wn8         = Cache::remember( "wn8:" . $tsClientWgAccount['wg_account']['account_id'], 1440, function () use ( $tsClientWgAccount ) {
											$wn8 = (string) new WN8( $tsClientWgAccount['wg_account']['account_id'] );
											Cache::put( "wn8:" . $tsClientWgAccount['wg_account']['account_id'], $wn8, 1440 );

											return $wn8;
										} );
										switch ( true ) {
											case $wn8 >= 0 && $wn8 <= 949:
												if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													if ( ! array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
													}
												}
												if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												break;
											case $wn8 >= 950 && $wn8 <= 1549:
												if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													if ( ! array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );
													}
												}
												if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												break;
											case $wn8 >= 1550 && $wn8 <= 2349:
												if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													if ( ! array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );
													}
												}
												if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );

												}
												break;
											case $wn8 >= 2350 && $wn8 <= 3129:
												if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													if ( ! array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );
													}
												}
												if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );
												}
												break;
											case $wn8 >= 3130 && $wn8 <= 9999 :
												if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
													if ( ! array_key_exists( $server['wn8']['purple_sg_id'], $clientGroup ) ) {
														$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['purple_sg_id'] );
													}
												}
												if ( array_key_exists( $server['wn8']['red_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['red_sg_id'] );
												}
												if ( array_key_exists( $server['wn8']['yellow_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['yellow_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['turquoise_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['turquoise_sg_id'] );

												}
												if ( array_key_exists( $server['wn8']['green_sg_id'], $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount['client_uid'], $server['wn8']['green_sg_id'] );

												}
												break;
										}
									}
								}
							}
						}
					} catch ( \Exception $e ) {
						if ( $e->getMessage() != 'no client on server' ) {
							#echo $e->getMessage() . PHP_EOL;
							#echo $e->getTraceAsString() . PHP_EOL;
							Log::error( $e->getMessage() );
							Log::error( $e->getTraceAsString() );
						}
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
