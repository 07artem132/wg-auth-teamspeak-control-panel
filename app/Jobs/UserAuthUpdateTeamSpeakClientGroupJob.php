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

class UserAuthUpdateTeamSpeakClientGroupJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
								$playerClanID = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] )->{$client['wg_account']['account_id']}->clan_id;
								$clanInfo     = $TeamSpeakWgAuth->clanInfo( $playerClanID );
								if ( array_key_exists( 'clans', $server ) ) {
									$clientGroup = (array) cache::remember( "ts:group:" . $client['client_uid'], 5, function () use ( $server, $client ) {
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
											switch ( true ) {
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'commander':
													if ( ! array_key_exists( $clan['commander'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'executive_officer':
													if ( ! array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'personnel_officer':
													if ( ! array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'combat_officer':
													if ( ! array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'intelligence_officer':
													if ( ! array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'quartermaster':
													if ( ! array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'recruitment_officer':
													if ( ! array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'junior_officer':
													if ( ! array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'private':
													if ( ! array_key_exists( $clan['private'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['private'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'recruit':
													if ( ! array_key_exists( $clan['recruit'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['recruit'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
													}
													break;
												case $clanInfo->$playerClanID->members->{$client['wg_account']['account_id']}->role == 'reservist':
													if ( ! array_key_exists( $clan['reservist'], $clientGroup ) ) {
														if ( is_null( $TeamSpeak ) ) {
															$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
														}
														$TeamSpeak->ServerUseByUID( $server['uid'] );
														$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $clan['reservist'] );
													}

													if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
													}
													if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
													}
													if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
													}
													if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
													}
													if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
													}
													if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
													}
													if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
													}
													if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
													}
													if ( array_key_exists( $clan['private'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
													}
													if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
														$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
													}
													break;
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

									if ( array_key_exists( $clan['commander'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['commander'] );
									}
									if ( array_key_exists( $clan['executive_officer'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['executive_officer'] );
									}
									if ( array_key_exists( $clan['personnel_officer'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['personnel_officer'] );
									}
									if ( array_key_exists( $clan['combat_officer'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['combat_officer'] );
									}
									if ( array_key_exists( $clan['intelligence_officer'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['intelligence_officer'] );
									}
									if ( array_key_exists( $clan['quartermaster'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['quartermaster'] );
									}
									if ( array_key_exists( $clan['recruitment_officer'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruitment_officer'] );
									}
									if ( array_key_exists( $clan['junior_officer'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['junior_officer'] );
									}
									if ( array_key_exists( $clan['private'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['private'] );
									}
									if ( array_key_exists( $clan['recruit'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['recruit'] );
									}
									if ( array_key_exists( $clan['reservist'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['reservist'] );
									}
									if ( ! array_key_exists( $clan['clan_tag'], $clientGroup ) ) {
										$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $clan['clan_tag'] );
									}
								}

							} catch ( \Exception $e ) {
								if ( $e->getMessage() != 'no client on server' ) {
								#	echo $e->getMessage() . PHP_EOL;
								#	echo $e->getTraceAsString() . PHP_EOL;
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
