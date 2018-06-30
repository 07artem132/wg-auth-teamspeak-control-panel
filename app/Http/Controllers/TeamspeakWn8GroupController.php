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
use App\Traits\TeamSpeak3GetClientGroupTraits;

class TeamspeakWn8GroupController extends Controller {
	use TeamSpeak3GetClientGroupTraits;

	function UserChengeGroupCron() {
		foreach ( Instanse::with( 'servers.modules.module', 'servers.wn8', 'servers.TsClientWgAccount.wgAccount', 'servers.clans' )->get() as $Instanse ) {
			$this->dispatch( new Wn8UpdateTeamSpeakClientGroupJob( $Instanse->toArray() ) );
		}
	}

	function UserChengeGroupUid( $uid ) {
		try {
			$TeamSpeakWgAuth   = new TeamSpeakWgAuth();
			$tsClientWgAccount = TsClientWgAccount::with( 'wgAccount', 'server.modules.module', 'server.wn8', 'server.TsClientWgAccount.wgAccount', 'server.clans' )->clientUID( $uid )->firstOrFail();
			foreach ( $tsClientWgAccount->server->modules as $module ) {
				if ( $module->status == 'enable' && $module->module->name == 'wn8' ) {
					$TeamSpeak = new TeamSpeak( $tsClientWgAccount->server->instanse_id );
					$TeamSpeak->ServerUseByUID( $tsClientWgAccount->server->uid );
					try {
						foreach ( $tsClientWgAccount->server->clans as $clan ) {
							$clanInfo = $TeamSpeakWgAuth->clanInfo( $clan->clan_id );
							if ( array_key_exists( $tsClientWgAccount->wgAccount->account_id, $clanInfo[ $clan->clan_id ]['members'] ) ) {
								$clientGroup           = $this->GetClientGroup( $tsClientWgAccount->server->instanse_id, $tsClientWgAccount->server->uid, $tsClientWgAccount->client_uid );
								$wn8                   = new WN8( $tsClientWgAccount->wgAccount->account_id );
								$ColumClientRank       = $this->wn8RatingToRankColumName( $wn8->__toInt() );
								$ColumClientRankRemove = $this->getAllColumName();

								$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
								unset( $ColumClientRankRemove[ $ColumClientRank ] );
								$ColumClientRankRemove = array_flip( $ColumClientRankRemove );

								if ( ! empty( $tsClientWgAccount->server->wn8->$ColumClientRank ) ) {
									if ( ! array_key_exists( $tsClientWgAccount->server->wn8->$ColumClientRank, $clientGroup ) ) {
										$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount->client_uid, $tsClientWgAccount->server->wn8->$ColumClientRank );
									}
								}

								foreach ( $ColumClientRankRemove as $rank ) {
									if ( ! empty( $tsClientWgAccount->server->wn8->$rank ) ) {
										if ( array_key_exists( $tsClientWgAccount->server->wn8->$rank, $clientGroup ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount->client_uid, $tsClientWgAccount->server->wn8->$rank );
										}
									}
								}
								break 2;
							} else {
								foreach ( $tsClientWgAccount->server->modules as $module ) {
									if ( $module->status == 'enable' && $module->module->name == 'wot_players' ) {
										$clientGroup = $this->GetClientGroup( $tsClientWgAccount->server->instanse_id, $tsClientWgAccount->server->uid, $tsClientWgAccount->client_uid );
										$wn8         = new WN8( $tsClientWgAccount->wgAccount->account_id );
										$wn8         = $wn8->__toInt();
										$ColumClientRank       = $this->wn8RatingToRankColumName( $wn8 );
										$ColumClientRankRemove = $this->getAllColumName();
										$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
										unset( $ColumClientRankRemove[ $ColumClientRank ] );
										$ColumClientRankRemove = array_flip( $ColumClientRankRemove );
										if ( !empty($tsClientWgAccount->server->wn8->$ColumClientRank) ) {
											if ( ! array_key_exists( $tsClientWgAccount->server->wn8->$ColumClientRank, $clientGroup ) ) {
												$TeamSpeak->ClientAddServerGroup( $tsClientWgAccount->client_uid, $tsClientWgAccount->server->wn8->$ColumClientRank );
											}
										}

										foreach ( $ColumClientRankRemove as $item ) {
											if ( array_key_exists( $item, $tsClientWgAccount->server->wn8 ) ) {
												if ( array_key_exists( $tsClientWgAccount->server->wn8->$item, $clientGroup ) ) {
													$TeamSpeak->ClientRemoveServerGroup( $tsClientWgAccount->client_uid, $tsClientWgAccount->server->wn8->$item );
												}
											}
										}
									}
								}
							}
						}
					} catch ( \Exception $e ) {
						if ( $e->getMessage() != 'no client on server' ) {
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
