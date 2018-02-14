<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\server;

class TeamSpeakWorker extends Controller {
	function GetConfig() {
		$Config  = [];
		$Servers = server::with( 'instanse' )->get();
		for ( $i = 0; $i < count( $Servers ); $i ++ ) {
			$Config[ $Servers[ $i ]->uid ]['ip']       = $Servers[ $i ]->instanse->ip;
			$Config[ $Servers[ $i ]->uid ]['port']     = $Servers[ $i ]->instanse->port;
			$Config[ $Servers[ $i ]->uid ]['login']    = $Servers[ $i ]->instanse->login;
			$Config[ $Servers[ $i ]->uid ]['password'] = $Servers[ $i ]->instanse->password;
			$Config[ $Servers[ $i ]->uid ]['uid']      = $Servers[ $i ]->uid;
			foreach ( $Servers[ $i ]->modules as $module ) {
				$Config[ $Servers[ $i ]->uid ]['module'][ $module->module->name ]['status'] = $module->status;
				foreach ( $module->options as $option ) {
					$Config[ $Servers[ $i ]->uid ]['module'][ $module->module->name ][ $option->option->name ] = $option->value;
				}
			}
		}

		return response()->json( $Config );
	}
}
