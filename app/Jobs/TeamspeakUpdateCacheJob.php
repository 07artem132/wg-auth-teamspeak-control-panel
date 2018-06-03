<?php

namespace App\Jobs;

use Log;
use Cache;
use App\Services\TeamSpeak;
use TeamSpeak3_Helper_String;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

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
			$TeamSpeak = new TeamSpeak( $this->instanses->id );
			$redis     = Redis::connection();
			foreach ( $this->instanses->servers as $server ) {
				try {
					$TeamSpeak->ServerUseByUID( $server->uid );
					if ( ! env( 'ALL_CLIENT_CACHE' ) ) {
						foreach ( $TeamSpeak->ReturnConnection()->clientList() as $client ) {
							if ( ! empty( $server->TsClientWgAccount->firstWhere( 'client_uid', '=', $client['client_unique_identifier'] ) ) ) {
								$cacheKeyNick  = Cache::getPrefix() . "ts:{$this->instanses->id}:{$server->uid}:client:{$client['client_unique_identifier']}:nickname";
								$cacheKeyGroup = Cache::getPrefix() . "ts:{$this->instanses->id}:{$server->uid}:group:{$client['client_unique_identifier']}";

								if ( $redis->ttl( $cacheKeyNick ) < 20 ) {
									$redis->set( $cacheKeyNick, serialize( (string) $client['client_nickname'] ), 'EX', env( 'CLIENT_NICKNAME_CACHE_TIME', 1 ) * 60 );
								}

								if ( $redis->ttl( $cacheKeyGroup ) < 20 ) {
									$clientServerGroups = $TeamSpeak->clientGetServerGroupsByUid( $client['client_unique_identifier'] );

									array_walk( $clientServerGroups, function ( &$group, &$group_id ) {
										array_walk( $group, function ( &$value, &$key ) {
											if ( $value instanceof TeamSpeak3_Helper_String ) {
												$value = (string) $value;
											}
										} );
									} );

									$redis->set( $cacheKeyGroup, serialize( $clientServerGroups ), 'EX', env( 'GROUP_CACHE_TIME', 1 ) * 60 );
								}
							}
						}
					} else {
						foreach ( $TeamSpeak->ReturnConnection()->clientListDb( 0, 500000 ) as $client ) {
							if ( ! empty( $server->TsClientWgAccount->firstWhere( 'client_uid', '=', $client['client_unique_identifier'] ) ) ) {
								$cacheKeyNick  = Cache::getPrefix() . "ts:{$this->instanses->id}:{$server->uid}:client:{$client['client_unique_identifier']}:nickname";
								$cacheKeyGroup = Cache::getPrefix() . "ts:{$this->instanses->id}:{$server->uid}:group:{$client['client_unique_identifier']}";

								if ( $redis->ttl( $cacheKeyNick ) < 20 ) {
									$redis->set( $cacheKeyNick, serialize( (string) $client['client_nickname'] ), 'EX', env( 'CLIENT_NICKNAME_CACHE_TIME', 1 ) * 60 );
								}

								if ( $redis->ttl( $cacheKeyGroup ) < 20 ) {
									$clientServerGroups = $TeamSpeak->clientGetServerGroupsByUid( $client['client_unique_identifier'] );

									array_walk( $clientServerGroups, function ( &$group, &$group_id ) {
										array_walk( $group, function ( &$value, &$key ) {
											if ( $value instanceof TeamSpeak3_Helper_String ) {
												$value = (string) $value;
											}
										} );
									} );

									$redis->set( $cacheKeyGroup, serialize( $clientServerGroups ), 'EX', env( 'GROUP_CACHE_TIME', 1 ) * 60 );
								}
							}
						}
					}
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
