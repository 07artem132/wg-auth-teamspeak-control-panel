<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TeamSpeak;
use App\Instanse;
use App\server;
use App\module;
use App\ModuleOptions;

class ServerConfigControllers extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
	}

	function AddServer( $id ) {
		$instanses = Instanse::findOrFail( $id );

		$TeamSpeak = new TeamSpeak( $instanses->id );
		foreach ( $TeamSpeak->GetServerList() as $server ) {
			$ServerInstanseList[ (string) $server['virtualserver_unique_identifier'] ] = (string) $server['virtualserver_name'];
		}

		return view( 'AddServer', [ 'Instanses' => $ServerInstanseList ] );
	}

	function DeleteServer( $id, $uid ) {
		server::uid( base64_decode( $uid ) )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/server/list' );
	}

	function AddServerToDb( Request $Request, $id ) {
		$server              = new server;
		$server->uid         = $Request->input( 'virtualServer' );
		$server->name        = $Request->input( 'name' );
		$server->instanse_id = $id;
		$server->saveOrFail();

		return response()->redirectTo( 'teamspeak/' . $id . '/server/list' );

	}

	function ListServer( $id ) {
		$Instanse = server::where( 'instanse_id', '=', $id )->get();
		$Instanse = $Instanse->makeHidden( [  ] )->toArray();
		return view( 'serverList', [ 'Instanses' => $Instanse,'InstanseID'=>$id ] );
	}

}
