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
			$TeamSpeak = new TeamSpeak( $this->instanses->id );
			$redis     = Redis::connection();
			foreach ( $this->instanses->servers as $server ) {
				if ( env( 'APP_DEBUG' ) ) {
					echo "iteration server->" . $server['uid'] . PHP_EOL;
				}
				try {
					$TeamSpeak->ServerUseByUID( $server->uid );
					foreach ( $TeamSpeak->ReturnConnection()->clientListDb( 0, 500000 ) as $client ) {
						if ( env( 'APP_DEBUG' ) ) {
							echo 'iteration client_uid->' . $client['client_unique_identifier'] . PHP_EOL;
						}
						$cacheKeyNick  = Cache::getPrefix() . "ts:{$this->instanses->id}:{$server->uid}:client:{$client['client_unique_identifier']}:nickname";
						$cacheKeyGroup = Cache::getPrefix() . "ts:{$this->instanses->id}:{$server->uid}:group:{$client['client_unique_identifier']}";

						if ( $redis->ttl( $cacheKeyNick ) < 20 ) {
							if ( env( 'APP_DEBUG' ) ) {
								echo 'set cache key->' . $cacheKeyNick . ' value->' . (string) $client['client_nickname'] . ' ' . PHP_EOL;
							}
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

							if ( env( 'APP_DEBUG' ) ) {
								echo 'set cache key->' . $cacheKeyGroup . ' value->' . PHP_EOL;
								print_r( $clientServerGroups );
							}

							$redis->set( $cacheKeyGroup, serialize( $clientServerGroups ), 'EX', env( 'GROUP_CACHE_TIME', 1 ) * 60 );
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
