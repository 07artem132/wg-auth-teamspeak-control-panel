<?php

namespace App\Jobs;

use Log;
use Cache;
use App\Services\TeamSpeak;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TeamspeakUpdateCacheJob implements ShouldQueue {
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
			$TeamSpeak = new TeamSpeak( $this->instanses['id'] );
			foreach ( $this->instanses['servers'] as $server ) {
				try {
					$TeamSpeak->ServerUseByUID( $server['uid'] );
#					if ( ! true ) {
#						foreach ( $TeamSpeak->ReturnConnection()->clientList() as $client ) {
#							foreach ( $server['ts_client_wg_account'] as $allowClient ) {
#								if ( $allowClient['client_uid'] == (string) $client['client_unique_identifier'] ) {
#							$clientJoinGroup = $TeamSpeak->ReturnConnection()->clientGetServerGroupsByDbid( $client['cldbid'] );
#							Cache::delete( "ts:" . $server['uid'] . ":group:" . (string) $client['client_unique_identifier'] );
#							Cache::add( "ts:" . $server['uid'] . ":group:" . (string) $client['client_unique_identifier'], $clientJoinGroup, 5 );
#							Cache::delete( "ts:" . $server['uid'] . ":client:" . (string) $client['client_unique_identifier'] );
#							Cache::add( "ts:" . $server['uid'] . ":client:" . (string) $client['client_unique_identifier'], $client, 5 );
# 		 					}
#							}
#						}
#					} else {
					foreach ( $TeamSpeak->ReturnConnection()->clientListDb( 0, 500000 ) as $client ) {
						#				foreach ( $server['ts_client_wg_account'] as $allowClient ) {
						#					if ( $allowClient['client_uid'] == (string) $client['client_unique_identifier'] ) {
						$clientJoinGroup = $TeamSpeak->ReturnConnection()->clientGetServerGroupsByDbid( $client['cldbid'] );
						Cache::delete( "ts:" . $server['uid'] . ":group:" . (string) $client['client_unique_identifier'] );
						Cache::add( "ts:" . $server['uid'] . ":group:" . (string) $client['client_unique_identifier'], $clientJoinGroup, 5 );
						Cache::delete( "ts:" . $server['uid'] . ":client:" . (string) $client['client_unique_identifier'] );
						Cache::add( "ts:" . $server['uid'] . ":client:" . (string) $client['client_unique_identifier'], $client, 5 );
						#					}
						#				}
					}
#					}
				} catch ( \Exception $e ) {
					Log::error( $e->getMessage() );
					Log::error( $e->getTraceAsString() );
				}
			}
		} catch ( \Exception $e ) {
			Log::error( $e->getMessage() );
			Log::error( $e->getTraceAsString() );
		}
		$TeamSpeak->ReturnConnection()->execute( 'quit' );
	}
}
