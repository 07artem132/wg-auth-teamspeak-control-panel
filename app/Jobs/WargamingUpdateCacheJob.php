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
		if ( Redis::ttl( config( 'cache.prefix' ).":account:$this->accountID" ) < 60 || ! Cache::has( "account:$this->accountID" ) ) {
			Cache::put( "account:$this->accountID", WargamingAPI::wot()->account->info( array( 'account_id' => $this->accountID ) ), env('PLAYERINFO_CACHE_TIME') );
		}
	}
}
