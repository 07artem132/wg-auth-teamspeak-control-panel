<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 23.02.2018
 * Time: 17:17
 */

namespace App\Http\Controllers;

use App\server;
use App\ServerModule;
use App\ServerModuleOptions;
use Illuminate\Http\Request;
use App\module;
use App\ModuleOptions;
use App\ServerClanPostSgid;
use App\Services\TeamSpeak;
use App\ServerWn8PostEfficiency;
use App\ServerWotPlayer;
use App\ServerNoValidNickname;
use App\ServerWgAuthNotifyAuthSuccessGroup;

class ServerModuleConfigControllers {
	function ListModuleAdd( $id, $uid ) {
		$module = module::all();
		$module = $module->toArray();

		return view( 'ServerModuleAddlist', [ 'Instanses' => $module, 'InstanseID' => $id, 'ServerUID' => $uid ] );
	}

	function ClanEditModuleGroup( Request $request, $id, $uid, $modulesID, $ServerClanPostSgidID ) {
		ServerClanPostSgid::findOrFail( $ServerClanPostSgidID )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/clan/add' );

	}

	function ClanRemoveModuleGroup( Request $request, $id, $uid, $modulesID, $ServerClanPostSgidID ) {
		ServerClanPostSgid::findOrFail( $ServerClanPostSgidID )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/clan/list' );
	}

	function ClanAddModuleGroupSaveDb( Request $request, $id, $uid, $modulesID ) {
		$ServerClanPostSgid                       = new ServerClanPostSgid;
		$ServerClanPostSgid->server_id            = server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$ServerClanPostSgid->clan_id              = $request->input( 'clan_id' );
		$ServerClanPostSgid->commander            = $request->input( 'commander' );
		$ServerClanPostSgid->executive_officer    = $request->input( 'executive_officer' );
		$ServerClanPostSgid->personnel_officer    = $request->input( 'personnel_officer' );
		$ServerClanPostSgid->combat_officer       = $request->input( 'combat_officer' );
		$ServerClanPostSgid->intelligence_officer = $request->input( 'intelligence_officer' );
		$ServerClanPostSgid->quartermaster        = $request->input( 'quartermaster' );
		$ServerClanPostSgid->recruitment_officer  = $request->input( 'recruitment_officer' );
		$ServerClanPostSgid->junior_officer       = $request->input( 'junior_officer' );
		$ServerClanPostSgid->private              = $request->input( 'private' );
		$ServerClanPostSgid->recruit              = $request->input( 'recruit' );
		$ServerClanPostSgid->reservist            = $request->input( 'reservist' );
		$ServerClanPostSgid->clan_tag             = $request->input( 'clan_tag' );
		$ServerClanPostSgid->saveOrFail();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/clan/list' );
	}

	function WN8AddModuleGroup( Request $request, $id, $uid, $modulesID ) {
		server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id;
		$ts3conn = new TeamSpeak( server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id );
		$ts3conn->ServerUseByUID( base64_decode( $uid ) );
		$groupList = $ts3conn->ReturnConnection()->serverGroupList( [ 'type' => 1 ] );
		foreach ( $groupList as &$group ) {
			$group = (string) $group['name'];
		}

		return view( 'WN8AddModuleGroup', [
			'groupList'  => $groupList,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

	function WN8ListModuleGroup( Request $request, $id, $uid, $modulesID ) {
		$wn8 = ServerWn8PostEfficiency::where( 'server_id', '=', server::uid( base64_decode( $uid ) )->firstOrFail()->id )->get()->toArray();

		return view( 'WN8ListModuleGroup', [
			'Instanses'  => $wn8,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

	function WN8AddModuleGroupSaveDb( Request $request, $id, $uid, $modulesID ) {
		$ServerWn8PostEfficiency                  = new ServerWn8PostEfficiency;
		$ServerWn8PostEfficiency->server_id       = server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$ServerWn8PostEfficiency->red_sg_id       = $request->input( 'red_sg_id' );
		$ServerWn8PostEfficiency->yellow_sg_id    = $request->input( 'yellow_sg_id' );
		$ServerWn8PostEfficiency->green_sg_id     = $request->input( 'green_sg_id' );
		$ServerWn8PostEfficiency->turquoise_sg_id = $request->input( 'turquoise_sg_id' );
		$ServerWn8PostEfficiency->purple_sg_id    = $request->input( 'purple_sg_id' );
		$ServerWn8PostEfficiency->saveOrFail();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/wn8/list' );
	}

	function WN8editModuleGroup( Request $request, $id, $uid, $modulesID, $wn8id ) {
		ServerWn8PostEfficiency::findOrFail( $wn8id )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/wn8/add' );
	}

	function ClanAddModuleGroup( Request $request, $id, $uid, $modulesID ) {
		server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id;
		$ts3conn = new TeamSpeak( server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id );
		$ts3conn->ServerUseByUID( base64_decode( $uid ) );
		$groupList = $ts3conn->ReturnConnection()->serverGroupList( [ 'type' => 1 ] );
		foreach ( $groupList as &$group ) {
			$group = (string) $group['name'];
		}

		return view( 'СlanAddModuleGroup', [
			'groupList'  => $groupList,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

	function ClanListModuleGroup( Request $request, $id, $uid, $modulesID ) {
		$ServerModule = ServerModule::where( 'id', '=', $modulesID )->firstOrFail();
		if ( $ServerModule->module->name == 'wg_auth_bot' ) {
			$clans = server::uid( base64_decode( $uid ) )->firstOrFail()->clans->toArray();

			return view( 'СlanListModuleGroup', [
				'Instanses'  => $clans,
				'InstanseID' => $id,
				'ServerUID'  => $uid,
				'modulesID'  => $modulesID
			] );
		}
	}

	function WotPlayersListModuleGroup( Request $request, $id, $uid, $modulesID ) {
		$wn8 = ServerWotPlayer::where( 'server_id', '=', server::uid( base64_decode( $uid ) )->firstOrFail()->id )->get()->toArray();

		return view( 'WotPlayersListModuleGroup', [
			'Instanses'  => $wn8,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );

	}

	function WotPlayersAddModuleGroup( Request $request, $id, $uid, $modulesID ) {
		server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id;
		$ts3conn = new TeamSpeak( server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id );
		$ts3conn->ServerUseByUID( base64_decode( $uid ) );
		$groupList = $ts3conn->ReturnConnection()->serverGroupList( [ 'type' => 1 ] );
		foreach ( $groupList as &$group ) {
			$group = (string) $group['name'];
		}

		return view( 'WotPlayersAddModuleGroup', [
			'groupList'  => $groupList,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );

	}

	function WotPlayersEditModuleGroup( Request $request, $id, $uid, $modulesID, $wotplayersid ) {
		ServerWotPlayer::findOrFail( $wotplayersid )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/wotplayers/add' );
	}

	function WotPlayersAddModuleGroupSaveDB( Request $request, $id, $uid, $modulesID ) {
		$ServerWn8PostEfficiency            = new ServerWotPlayer;
		$ServerWn8PostEfficiency->server_id = server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$ServerWn8PostEfficiency->sg_id     = $request->input( 'sg_id' );
		$ServerWn8PostEfficiency->saveOrFail();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/wotplayers/' . 'list' );

	}

	function VerifyGameNicknameListModuleGroup( Request $request, $id, $uid, $modulesID ) {
		$wn8 = ServerNoValidNickname::where( 'server_id', '=', server::uid( base64_decode( $uid ) )->firstOrFail()->id )->get()->toArray();

		return view( 'VerifyGameNicknameListModuleGroup', [
			'Instanses'  => $wn8,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

	function VerifyGameNicknameAddModuleGroup( Request $request, $id, $uid, $modulesID ) {
		server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id;
		$ts3conn = new TeamSpeak( server::uid( base64_decode( $uid ) )->firstOrFail()->instanse->id );
		$ts3conn->ServerUseByUID( base64_decode( $uid ) );
		$groupList = $ts3conn->ReturnConnection()->serverGroupList( [ 'type' => 1 ] );
		foreach ( $groupList as &$group ) {
			$group = (string) $group['name'];
		}

		return view( 'VerifyGameNicknameAddModuleGroup', [
			'groupList'  => $groupList,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );

	}

	function VerifyGameNicknameAddModuleGroupSaveDb( Request $request, $id, $uid, $modulesID ) {
		$ServerWn8PostEfficiency            = new ServerNoValidNickname;
		$ServerWn8PostEfficiency->server_id = server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$ServerWn8PostEfficiency->sg_id     = $request->input( 'sg_id' );
		$ServerWn8PostEfficiency->saveOrFail();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/verifygamenickname/' . 'list' );
	}

	function VerifyGameNicknameEditModuleGroup( Request $request, $id, $uid, $modulesID, $verifygamenicknameid ) {
		ServerNoValidNickname::find( $verifygamenicknameid )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/verifygamenickname/' . 'add' );
	}

	function ListModuleGroupNonify( Request $request, $id, $uid, $modulesID ) {
		server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$Instanses = ServerWgAuthNotifyAuthSuccessGroup::where( 'server_id', '=', server::uid( base64_decode( $uid ) )->firstOrFail()->id )->get()->toArray();

		return view( 'ListModuleGroupNonify', [
			'Instanses'  => $Instanses,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

	function EditModuleGroupNonify( Request $request, $id, $uid, $modulesID, $groupnotifyid ) {
		ServerWgAuthNotifyAuthSuccessGroup::find( $groupnotifyid )->delete();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/group/notify/' . 'add' );
	}

	function AddtModuleGroupNonify( Request $request, $id, $uid, $modulesID ) {
		return view( 'AddModuleGroupNonify', [
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );

	}
	function AddtModuleGroupNonifySaveDB(Request $request, $id, $uid, $modulesID){
		$ServerWgAuthNotifyAuthSuccessGroup                       = new ServerWgAuthNotifyAuthSuccessGroup;
		$ServerWgAuthNotifyAuthSuccessGroup->server_id            = server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$ServerWgAuthNotifyAuthSuccessGroup->commander            = $request->input( 'commander' );
		$ServerWgAuthNotifyAuthSuccessGroup->executive_officer    = $request->input( 'executive_officer' );
		$ServerWgAuthNotifyAuthSuccessGroup->personnel_officer    = $request->input( 'personnel_officer' );
		$ServerWgAuthNotifyAuthSuccessGroup->combat_officer       = $request->input( 'combat_officer' );
		$ServerWgAuthNotifyAuthSuccessGroup->intelligence_officer = $request->input( 'intelligence_officer' );
		$ServerWgAuthNotifyAuthSuccessGroup->quartermaster        = $request->input( 'quartermaster' );
		$ServerWgAuthNotifyAuthSuccessGroup->recruitment_officer  = $request->input( 'recruitment_officer' );
		$ServerWgAuthNotifyAuthSuccessGroup->junior_officer       = $request->input( 'junior_officer' );
		$ServerWgAuthNotifyAuthSuccessGroup->private              = $request->input( 'private' );
		$ServerWgAuthNotifyAuthSuccessGroup->recruit              = $request->input( 'recruit' );
		$ServerWgAuthNotifyAuthSuccessGroup->reservist            = $request->input( 'reservist' );
		$ServerWgAuthNotifyAuthSuccessGroup->saveOrFail();

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/group/notify/list' );
	}

	function ModuleAddSave( Request $request, $id, $uid, $modulesID ) {
		$param = $request->all();
		unset( $param['submitButton'] );

		$ServerModule            = new ServerModule;
		$ServerModule->server_id = server::uid( base64_decode( $uid ) )->firstOrFail()->id;
		$ServerModule->module_id = $modulesID;
		$ServerModule->status    = 'enable';
		$ServerModule->save();

		foreach ( $param as $key => $value ) {
			$ServerModuleOptions                   = new ServerModuleOptions;
			$ServerModuleOptions->server_module_id = $ServerModule->id;
			$ServerModuleOptions->module_option_id = ModuleOptions::module( $modulesID )->where( 'name', '=', $key )->firstOrFail()->id;
			$ServerModuleOptions->value            = $value;
			$ServerModuleOptions->save();
		}

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/list' );
	}

	function ModuleAdd( $id, $uid, $modulesID ) {
		$module = ModuleOptions::module( $modulesID )->get();
		$module = $module->toArray();

		return view( 'ServerModuleAdd', [
			'Instanses'  => $module,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

	function ListModule( $id, $uid ) {
		$Instanse = server::with( 'modules.module' )->where( 'instanse_id', '=', $id )->uid( base64_decode( $uid ) )->firstOrFail();
		$Instanse = $Instanse->modules->makeHidden( [] )->toArray();

		return view( 'servermodulelist', [ 'Instanses' => $Instanse, 'InstanseID' => $id, 'ServerUID' => $uid ] );
	}

	function DisabledModule( $id, $uid, $modulesID ) {
		ServerModule::where( 'id', '=', $modulesID )->update( [ 'status' => 'disable' ] );

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/list' );
	}

	function EnabledModule( $id, $uid, $modulesID ) {
		ServerModule::where( 'id', '=', $modulesID )->update( [ 'status' => 'enable' ] );

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/list' );
	}

	function EditModuleOption( $id, $uid, $modulesID, $moduleOptionID ) {
		$result = ServerModuleOptions::with( 'option' )->where( 'server_module_id', '=', $modulesID )->where( 'module_option_id', '=', $moduleOptionID )->firstOrFail();
		$result = $result->toArray();

		return view( 'editModuleOptionConfig', [
			'Instanses'  => $result,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );

	}

	function SaveEditModuleOption( Request $request, $id, $uid, $modulesID, $moduleOptionID ) {
		ServerModuleOptions::with( 'option' )->where( 'server_module_id', '=', $modulesID )->where( 'module_option_id', '=', $moduleOptionID )->update( [ 'value' => $request->input( 'value' ) ] );

		return response()->redirectTo( 'teamspeak/' . $id . '/' . $uid . '/module/' . $modulesID . '/edit' );
	}

	function EditModule( $id, $uid, $modulesID ) {
		$result = ServerModuleOptions::with( 'option' )->where( 'server_module_id', '=', $modulesID )->get();
		$result = $result->toArray();

		return view( 'editModuleConfig', [
			'Instanses'  => $result,
			'InstanseID' => $id,
			'ServerUID'  => $uid,
			'modulesID'  => $modulesID
		] );
	}

}