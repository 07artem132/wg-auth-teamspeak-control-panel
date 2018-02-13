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

class TeamSpeak {
	private $ts3conn, $InstanceConfig;

	function __construct( $id ) {
		$this->InstanceConfig = Instanse::findOrFail( $id );
		$this->ts3conn        = TeamSpeak3::factory( "serverquery://{$this->InstanceConfig->login}:{$this->InstanceConfig->password}@{$this->InstanceConfig->ip}:{$this->InstanceConfig->port}/" );
	}

	function ReturnConnection() {
		return $this->ts3conn;
	}

	function ServerUseByUID( $ServerUID ) {
		$this->ts3conn = $this->ts3conn->serverGetByUid( $ServerUID );
	}

	function ClientMemberOfServerGroupId( $ClientUID, $ID ) {
		$client = $this->ts3conn->clientGetByUid( $ClientUID );

		foreach ( $client->memberOf() as $group ) {
			if ( $group instanceof TeamSpeak3_Node_Servergroup && $group->getId() === $ID ) {
				return true;
			}
		}

		return false;
	}

	function ClientAddServerGroup( $ClientUID, $ID ) {
		$client = $this->ts3conn->clientGetByUid( $ClientUID );
		$client->addServerGroup( $ID );
	}

	function GetServerList() {
		return $this->ts3conn->serverList();
	}
}