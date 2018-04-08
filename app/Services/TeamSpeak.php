<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 10.02.2018
 * Time: 1:00
 */

namespace App\Services;

use TeamSpeak3;
use App\Instanse;
use TeamSpeak3_Node_Servergroup;
use App\server;

class TeamSpeak {
	private $ts3conn, $InstanceConfig, $latestUidSelect = null;

	function __construct( $id ) {
		$this->InstanceConfig = Instanse::findOrFail( $id );
		$this->ts3conn        = TeamSpeak3::factory( "serverquery://{$this->InstanceConfig->login}:{$this->InstanceConfig->password}@{$this->InstanceConfig->ip}:{$this->InstanceConfig->port}/#use_offline_as_virtual" );
	}

	function ReturnConnection() {
		return $this->ts3conn;
	}

	function SendMessageClient( $ClientUID, $message ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		$this->ts3conn->clientGetByDbid( $cldbid )->message( $message );
	}

	function SendPokeClient( $ClientUID, $message ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		$this->ts3conn->clientGetByDbid( $cldbid )->poke( $message );
	}

	function updateNickname( $nickname ) {
		$this->ts3conn->execute( 'clientupdate', [ 'client_nickname' => $nickname ] );
	}

	function ServerUseByUID( $ServerUID ) {
		if ( $this->latestUidSelect != $ServerUID ) {
			$this->ts3conn         = $this->ts3conn->serverGetByUid( $ServerUID );
			$this->latestUidSelect = $ServerUID;
			foreach ( server::uid( $ServerUID )->firstOrFail()->modules()->get() as $module ) {
				if ( $module->module->toArray()['name'] == 'nickname_change' ) {
					$this->updateNickname( $module->options->toArray()[0]['value'] . ' ' . rand( 0, 999 )%100*10-5 );
				}
			}
		}
	}

	function ClientMemberOfServerGroupId( $ClientUID, $SGID ) {
		$cldbid                = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		$ServerGroupClientList = $this->ts3conn->serverGroupClientList( $SGID );

		if ( array_key_exists( $cldbid, $ServerGroupClientList ) ) {
			return true;
		}

		return false;
	}

	function ClientAddServerGroup( $ClientUID, $sgid ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		$this->ts3conn->serverGroupClientAdd( $sgid, $cldbid );
	}

	function ClientInfo( $ClientUID ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];

		return $this->ts3conn->clientInfoDb( $cldbid );
	}

	function ClientRemoveServerGroup( $ClientUID, $sgid ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		$this->ts3conn->serverGroupClientDel( $sgid, $cldbid );
	}

	function clientGetServerGroupsByUid( $ClientUID ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];

		return $this->ts3conn->clientGetServerGroupsByDbid( $cldbid );
	}

	function GetServerList() {
		return $this->ts3conn->serverList();
	}
}