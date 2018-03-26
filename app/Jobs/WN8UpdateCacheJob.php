<?php

namespace App\Jobs;

use App\Services\WN8;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use Redis;

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
		if ( Redis::ttl( config( 'cache.prefix' ).":wn8:$this->accountID" ) < 600 || ! Cache::has( "wn8:$this->accountID") ) {
			Cache::put( "wn8:$this->accountID", (string) new WN8( $this->accountID ), 1440 );
		}

	}
}
