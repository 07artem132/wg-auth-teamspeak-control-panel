<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 23.02.2018
 * Time: 15:56
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Instanse;

class InstansesConfigControllers {

	function AddServer() {
		return view( 'InstansesAdd');
	}

	function DeleteServer($id) {
		Instanse::findOrFail($id)->delete();
		return response()->redirectTo('/teamspeak/list');
	}

	function AddServerToDB(Request $Request) {
		$Instanse = new Instanse;
		$Instanse->ip = $Request->input('ip');
		$Instanse->port = $Request->input('port');
		$Instanse->login = $Request->input('login');
		$Instanse->password = $Request->input('password');
		$Instanse->saveOrFail();
		return response()->redirectTo('/teamspeak/list');
	}

	function ListServer() {
		$Instanse = Instanse::all();
		$Instanse = $Instanse->makeHidden( [ ] )->toArray();

		return view( 'InstansesList', [ 'Instanses' => $Instanse ] );
	}
}