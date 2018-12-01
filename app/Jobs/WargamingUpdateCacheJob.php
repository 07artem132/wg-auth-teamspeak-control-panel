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

class WargamingUpdateCacheJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	private $accountID;
	public $timeout = 900;

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
		$redis               = Redis::connection();
		$cacheKeyAccountInfo = Cache::getPrefix() . 'account:' . $this->accountID;
		$ttl                 = $redis->ttl( $cacheKeyAccountInfo );

		if ( $ttl < 60 ) {
			$data = WargamingAPI::wot()->account->info( array( 'account_id' => $this->accountID ) );
			$redis->set( $cacheKeyAccountInfo, serialize( $data ), 'EX', env( 'PLAYERINFO_CACHE_TIME' ) * 60 );
		}
	}
}
