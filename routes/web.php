<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get( '/', function () {
	return view( 'welcome' );
} );

Route::post( '/user/verify', [
	'uses' => 'TeamSpeakUserAuth@VerifyPrivilege',
] );

Route::get( '/user/verify/{id}', [
	'uses' => 'TeamSpeakUserAuth@Registration',
] );

Route::get( '/user/verify/{id}/wg', [
	'uses' => 'TeamSpeakUserAuth@RegistrationWgVerify',
] );

Route::get( '/teamspeak/worker/config', [
	'uses' => 'TeamSpeakWorker@GetConfig',
] );

Route::get( '/user/wn8', [
	'uses' => 'test@wn8',
] );


////////////////////////////////////////////////
Route::get( '/teamspeak/{id}/server/add', [
	'uses' => 'ServerConfigControllers@AddServer',
] );

Route::post( '/teamspeak/{id}/server/add', [
	'uses' => 'ServerConfigControllers@AddServerToDb',
] );

Route::get( '/teamspeak/{id}/server/list', [
	'uses' => 'ServerConfigControllers@ListServer',
] );

Route::get( '/teamspeak/{id}/{uid}/delete', [
	'uses' => 'ServerConfigControllers@DeleteServer',
] );
//////////////////////////////////////
Route::get( '/teamspeak/list', [
	'uses' => 'InstansesConfigControllers@ListServer',
] );

Route::get( '/teamspeak/add', [
	'uses' => 'InstansesConfigControllers@AddServer',
] );

Route::post( '/teamspeak/add', [
	'uses' => 'InstansesConfigControllers@AddServerToDB',
] );

Route::get( '/teamspeak/{id}/delete', [
	'uses' => 'InstansesConfigControllers@DeleteServer',
] );
////////////////////////////////////////////////////////
Route::get( '/teamspeak/{id}/{uid}/module/list', [
	'uses' => 'ServerModuleConfigControllers@ListModule',
] );
Route::get( '/teamspeak/{id}/{uid}/module/add', [
	'uses' => 'ServerModuleConfigControllers@ListModuleAdd',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{moduleID}/add', [
	'uses' => 'ServerModuleConfigControllers@ModuleAdd',
] );
Route::post( '/teamspeak/{id}/{uid}/module/{moduleID}/add', [
	'uses' => 'ServerModuleConfigControllers@ModuleAddSave',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/disabled', [
	'uses' => 'ServerModuleConfigControllers@DisabledModule',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/enabled', [
	'uses' => 'ServerModuleConfigControllers@EnabledModule',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/edit', [
	'uses' => 'ServerModuleConfigControllers@EditModule',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/clan/list', [
	'uses' => 'ServerModuleConfigControllers@ClanListModuleGroup',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/clan/add', [
	'uses' => 'ServerModuleConfigControllers@ClanAddModuleGroup',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/wn8/add', [
	'uses' => 'ServerModuleConfigControllers@WN8AddModuleGroup',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/wn8/list', [
	'uses' => 'ServerModuleConfigControllers@WN8ListModuleGroup',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/wotplayers/list', [
	'uses' => 'ServerModuleConfigControllers@WotPlayersListModuleGroup',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/wotplayers/add', [
	'uses' => 'ServerModuleConfigControllers@WotPlayersAddModuleGroup',
] );
Route::post( '/teamspeak/{id}/{uid}/module/{modulesID}/wotplayers/add', [
	'uses' => 'ServerModuleConfigControllers@WotPlayersAddModuleGroupSaveDB',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/wotplayers/{wotplayersid}/edit', [
	'uses' => 'ServerModuleConfigControllers@WotPlayersEditModuleGroup',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/verifygamenickname/list', [
	'uses' => 'ServerModuleConfigControllers@VerifyGameNicknameListModuleGroup',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/verifygamenickname/add', [
	'uses' => 'ServerModuleConfigControllers@VerifyGameNicknameAddModuleGroup',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/verifygamenickname/{verifygamenicknameid}/edit', [
	'uses' => 'ServerModuleConfigControllers@VerifyGameNicknameEditModuleGroup',
] );
Route::post( '/teamspeak/{id}/{uid}/module/{modulesID}/verifygamenickname/add', [
	'uses' => 'ServerModuleConfigControllers@VerifyGameNicknameAddModuleGroupSaveDB',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/wn8/{wn8id}/edit', [
	'uses' => 'ServerModuleConfigControllers@WN8editModuleGroup',
] );

Route::post( '/teamspeak/{id}/{uid}/module/{modulesID}/wn8/add', [
	'uses' => 'ServerModuleConfigControllers@WN8AddModuleGroupSaveDb',
] );

Route::post( '/teamspeak/{id}/{uid}/module/{modulesID}/clan/add', [
	'uses' => 'ServerModuleConfigControllers@ClanAddModuleGroupSaveDb',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/clan/{clanid}/delete', [
	'uses' => 'ServerModuleConfigControllers@ClanRemoveModuleGroup',
] );
Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/clan/{clanid}/edit', [
	'uses' => 'ServerModuleConfigControllers@ClanEditModuleGroup',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/group/notify/list', [
	'uses' => 'ServerModuleConfigControllers@ListModuleGroupNonify',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/group/notify/{groupnotifyid}/edit', [
	'uses' => 'ServerModuleConfigControllers@EditModuleGroupNonify',
] );

Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/group/notify/add', [
	'uses' => 'ServerModuleConfigControllers@AddtModuleGroupNonify',
] );

Route::post( '/teamspeak/{id}/{uid}/module/{modulesID}/group/notify/add', [
	'uses' => 'ServerModuleConfigControllers@AddtModuleGroupNonifySaveDB',
] );


Route::get( '/teamspeak/{id}/{uid}/module/{modulesID}/{moduleOptionID}/edit', [
	'uses' => 'ServerModuleConfigControllers@EditModuleOption',
] );
Route::post( '/teamspeak/{id}/{uid}/module/{modulesID}/{moduleOptionID}/edit', [
	'uses' => 'ServerModuleConfigControllers@SaveEditModuleOption',
] );
