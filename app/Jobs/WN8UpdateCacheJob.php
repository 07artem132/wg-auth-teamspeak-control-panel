<?php

namespace App\Jobs;

use App\Services\WN8;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use Illuminate\Support\Facades\Redis;

class WN8UpdateCacheJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	private $accountID;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $accountID ) {
		$this->accountID = $accountID;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$redis       = Redis::connection();
		$cacheKeyWn8 = Cache::getPrefix() . 'wn8:' . $this->accountID;
		$ttl         = $redis->ttl( $cacheKeyWn8 );

		if ( $ttl < 300 ) {
			$wn8 = new WN8( $this->accountID, false );
			$redis->set( $cacheKeyWn8, $wn8->__toInt(), 'EX', env( 'WN8_CACHE_TIME', 1440 ) * 60 );
		}

	}
}
