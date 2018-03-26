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

class WargamingClanInfoUpdateCacheJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	private $clanID;

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
		if ( Redis::ttl( config( 'cache.prefix' ).":clan:$this->clanID" ) < 60 || ! Cache::has( "clan:$this->clanID" ) ) {
			Cache::put( "clan:$this->clanID", WargamingAPI::wgn()->clans->info( [ 'clan_id'     => $this->clanID,
			                                                                      'members_key' => 'id'
			] ), 30 );
		}

	}
}
