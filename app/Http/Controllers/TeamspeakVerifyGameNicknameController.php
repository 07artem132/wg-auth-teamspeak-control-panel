<?php

namespace App\Http\Controllers;

use App\Instanse;
use App\Jobs\TeamSpeakVerifyGameNickname;

class TeamspeakVerifyGameNicknameController extends Controller {
	function UserChengeGroupCron() {
		foreach ( Instanse::with( 'servers.modules.module', 'servers.NoValidNickname', 'servers.TsClientWgAccount.wgAccount' )->get() as $Instanse ) {
			$this->dispatch( new TeamSpeakVerifyGameNickname( $Instanse->toArray() ) );
		}
	}
}
