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
			$TeamSpeak       = new TeamSpeak( $this->instanses['id'] );
			$TeamSpeakWgAuth = new TeamSpeakWgAuth();
			foreach ( $this->instanses['servers'] as $server ) {
				foreach ( $server['modules'] as $module ) {
					if ( $module['status'] == 'enable' && $module['module']['name'] == 'verify_game_nickname' ) {
						foreach ( $server['ts_client_wg_account'] as $client ) {
							try {
								$TeamSpeak->ServerUseByUID( $server['uid'] );

								$clientNickname = (string) $TeamSpeak->ClientInfo( $client['client_uid'] )['client_nickname'];
								$playerNickname = $TeamSpeakWgAuth->getAccountInfo( $client['wg_account']['account_id'] )->{$client['wg_account']['account_id']}->nickname;

								preg_match_all( '/^(.*?)\s/', $clientNickname, $matches, PREG_SET_ORDER, 0 );

								if ( ! empty( $matches ) ) {
									$clientNicknameFilter = $matches[0][1];
								} else {
									$clientNicknameFilter = $clientNickname;
								}

								if ( $clientNicknameFilter != $playerNickname ) {
									if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
										if ( ! $TeamSpeak->ClientMemberOfServerGroupId( $client['client_uid'], $server['no_valid_nickname']['sg_id'] ) ) {
											$TeamSpeak->ClientAddServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
										}
									}
								} else {
									if ( array_key_exists( 'no_valid_nickname', $server ) && ! empty( $server['no_valid_nickname']['sg_id'] ) ) {
										if ( $TeamSpeak->ClientMemberOfServerGroupId( $client['client_uid'], $server['no_valid_nickname']['sg_id'] ) ) {
											$TeamSpeak->ClientRemoveServerGroup( $client['client_uid'], $server['no_valid_nickname']['sg_id'] );
										}
									}
								}
							} catch ( \Exception $e ) {
								Log::error( $e->getMessage() );
								Log::error( $e->getTraceAsString() );
							}
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			Log::error( $e->getMessage() );
			Log::error( $e->getTraceAsString() );
		}
		$TeamSpeak->ReturnConnection()->execute( 'quit' );
	}
}
