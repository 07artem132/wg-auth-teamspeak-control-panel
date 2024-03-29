<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\WargamingAPI;
use Cache;
use Redis;
use App\Services\FastWargamingInfo;

class WargamingClanInfoUpdateCacheJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	private $clanID;
	public $timeout = 900;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $clanID ) {
		$this->clanID = $clanID;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$redis       = Redis::connection();
		$cacheKeyClanInfo = Cache::getPrefix() . 'clan:' . $this->clanID;
		$ttl         = $redis->ttl( $cacheKeyClanInfo );

		if ( $ttl < 60  ) {
			$data = FastWargamingInfo::Clan( $this->clanID );
			$redis->set($cacheKeyClanInfo,serialize($data),'EX',env('CLAN_INFO_CACHE_TIME')*60);
		}
	}
}
