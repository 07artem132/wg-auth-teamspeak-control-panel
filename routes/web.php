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




////////////////////////////////////////////////
Route::get( '/teamspeak/{id}/server/add', [
	'uses' => 'ServerConfigControllers@AddServer',
] );

Route::post( '/teamspeak/{id}/server/add', [
	'uses' => 'ServerConfigControllers@AddServerToDb',
] );

Route::get( '/teamspeak/{id}/{uid}/edit', [
	'uses' => 'ServerConfigControllers@EditServer',
] );