<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 12.04.2018
 * Time: 19:08
 */

namespace App\Services;


class FastWargamingInfo {

	/**
	 * @param int $clanID
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function Clan( int $clanID ): array {
		$client   = new \GuzzleHttp\Client();
		$res      = $client->request( 'GET', 'http://ru.wargaming.net/clans/wot/' . $clanID . '/api/players/', [
			'headers' => [
				'User-Agent'       => \Campo\UserAgent::random(),
				'Accept'           => 'application/json',
				'X-Requested-With' => 'XMLHttpRequest'
			]
		] );
		$response = $res->getBody();
		$response = json_decode( $response );

		if ( $response->status == 'ok' ) {
			foreach ( $response->items as $item ) {
				$result[ $clanID ]['members'][ $item->id ]['role']      = $item->role->name;
				$result[ $clanID ]['members'][ $item->id ]['role_i18n'] = $item->role->localized_name;
				#$result[$clanID]['members'][$item->id]['joined_at'];
				$result[ $clanID ]['members'][ $item->id ]['account_id']   = $item->id;
				$result[ $clanID ]['members'][ $item->id ]['account_name'] = $item->name;
			}
		}

		return $result;

	}

	public static function Account( int $accountID ): \stdClass {
		$client   = new \GuzzleHttp\Client();
		$res      = $client->request( 'GET', "https://worldoftanks.ru/ru/community/accounts/" . $accountID, [
			'headers' => [
				'User-Agent'       => \Campo\UserAgent::random(),
				'Accept'           => 'application/json',
				'X-Requested-With' => 'XMLHttpRequest'
			]
		] );
		$response = $res->getBody();

		preg_match_all( '/USER_DATA\s=\s({.*?),\s*VEHICLES_PARAMETERS/s', $response, $matches, PREG_SET_ORDER, 0 );
		$response = json_decode( $matches[0][1] );

		return $response;
	}
}