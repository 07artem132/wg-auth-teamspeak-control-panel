<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TeamSpeak;
use App\Instanse;
use App\server;
use App\module;
use App\ModuleOptions;

class ServerConfigControllers extends Controller {
	function AddServer( $id ) {
		$instanses = Instanse::findOrFail( $id );

		$TeamSpeak = new TeamSpeak( $instanses->id );
		foreach ( $TeamSpeak->GetServerList() as $server ) {
			$ServerInstanseList[ (string) $server['virtualserver_unique_identifier'] ] = (string) $server['virtualserver_name'];
		}

		return view( 'AddServer', [ 'Instanses' => $ServerInstanseList ] );
	}

	function AddServerToDb( Request $Request, $id ) {
		$server              = new server;
		$server->uid         = $Request->input( 'virtualServer' );
		$server->instanse_id = $id;
		$server->saveOrFail();

	}

	function EditServer( Request $Request, $id, $uid ) {
		$uid               = base64_decode( $uid );
		$ModuleList        = module::all();
		$ModuleOptionsList = ModuleOptions::all();

		return view( 'EditServer' );

	}
}
