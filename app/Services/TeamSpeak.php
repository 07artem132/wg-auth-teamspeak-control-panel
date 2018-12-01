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
use Cache;
use TeamSpeak3_Adapter_ServerQuery_Exception;

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
					$nickname = mb_strimwidth( $module->options->toArray()[0]['value'] . ' ' . ( random_int( 0, 9999 ) ), 0, 30, '' );
					#echo $nickname . PHP_EOL;
					try {
						$this->updateNickname( $nickname );
					} catch ( \Exception $e ) {
						$nickname = mb_strimwidth( $module->options->toArray()[0]['value'] . ' ' . ( random_int( 0, 9999 ) ), 0, 30, '' );
						$this->updateNickname( $nickname );
					}
				}
			}
		}
	}

	function ClientMemberOfServerGroupId( $ClientUID, $SGID ) {
		try {
			$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		} catch ( TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
			var_dump( 'Рекомендуется удалить uid клиента из бд: ' . $ClientUID );
			throw new  TeamSpeak3_Adapter_ServerQuery_Exception( $e->getMessage() );

		}

		$ServerGroupClientList = $this->ts3conn->serverGroupClientList( $SGID );

		if ( array_key_exists( $cldbid, $ServerGroupClientList ) ) {
			return true;
		}

		return false;
	}

	function ClientAddServerGroup( $ClientUID, $sgid ) {
		$cache = Cache::get( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID" );
		try {
			$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		} catch ( TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
			var_dump( 'Рекомендуется удалить uid клиента из бд: ' . $ClientUID );
			throw new  TeamSpeak3_Adapter_ServerQuery_Exception( $e->getMessage() );
		}
		try {
			$this->ts3conn->serverGroupClientAdd( $sgid, $cldbid );
		} catch ( TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
			if ( $e->getMessage() === 'duplicate entry' ) {
				$cache[ $sgid ] = [ 'ClientAddedGroupTimestamp' => microtime( true ) ];
				Cache::put( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID", $cache, env( 'GROUP_CACHE_TIME' ) );

				return;
			}
		}
		$cache[ $sgid ] = [ 'ClientAddedGroupTimestamp' => microtime( true ) ];
		Cache::put( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID", $cache, env( 'GROUP_CACHE_TIME' ) );
	}

	function ClientInfo( $ClientUID ) {
		$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];

		return $this->ts3conn->clientInfoDb( $cldbid );
	}

	function ClientRemoveServerGroup( $ClientUID, $sgid ) {
		try {
			$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		} catch ( TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
			var_dump( 'Рекомендуется удалить uid клиента из бд: ' . $ClientUID );
			throw new  TeamSpeak3_Adapter_ServerQuery_Exception( $e->getMessage() );

		}

		try {
			$this->ts3conn->serverGroupClientDel( $sgid, $cldbid );
		} catch ( TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
			if ( $e->getMessage() === 'empty result set' ) {
				$cache = Cache::get( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID" );
				unset( $cache[ $sgid ] );
				Cache::put( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID", $cache, env( 'GROUP_CACHE_TIME' ) );

				return;
			}
		}

		$cache = Cache::get( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID" );
		unset( $cache[ $sgid ] );
		Cache::put( "ts:{$this->InstanceConfig->id}:$this->latestUidSelect:group:$ClientUID", $cache, env( 'GROUP_CACHE_TIME' ) );
	}

	function clientGetServerGroupsByUid( $ClientUID ) {
		try {
			$cldbid = $this->ts3conn->clientFindDb( $ClientUID, true )[0];
		} catch ( TeamSpeak3_Adapter_ServerQuery_Exception $e ) {
			var_dump( 'Рекомендуется удалить uid клиента из бд: ' . $ClientUID );
			throw new  TeamSpeak3_Adapter_ServerQuery_Exception( $e->getMessage() );
		}


		return $this->ts3conn->clientGetServerGroupsByDbid( $cldbid );
	}

	function GetServerList() {
		return $this->ts3conn->serverList();
	}
}