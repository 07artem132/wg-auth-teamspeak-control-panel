<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 14.02.2018
 * Time: 16:40
 */

namespace App\Services;

use Cache;
use App\Traits\JsonDecodeAndValidate;

class WN8 {
	use JsonDecodeAndValidate;

	protected $expected_tank_values = array();
	protected $wn8;
	protected $account_id;
	public $missing_tanks;

	/**
	 * @param integer $account_id ID аккаунта в танках
	 * @param  bool $cache вернуть кешированный результат ?
	 * @param integer $expected_tank_values_version Версия массива ожидаемых значений танков от wnefficiency.net (vBAddict.net)
	 *
	 * @throws \Exception
	 */
	public function __construct( $account_id, $cache = true, $expected_tank_values_version = 30 ) {
		$this->account_id = $account_id;

		$this->loadExpectedTankValues( $expected_tank_values_version );
		if ( $cache ) {
			$this->wn8 = Cache::remember( "wn8:" . $account_id, env( 'WN8_CACHE_TIME' ), function () {
				return (float) $this->calculateWN8();
			} );
		} else {
			$this->wn8 = $this->calculateWN8();
		}
	}

	protected function loadExpectedTankValues( $version ) {
		$buff = Cache::remember( "expected_tank_values:$version", 60 * 24 * 1, function () use ( $version ) {
			return $this->JsonDecodeAndValidate( file_get_contents( 'https://static.modxvm.com/wn8-data-exp/json/wn8exp.json' ) )->data;
		} );

		foreach ( $buff AS $tank ) {

			// Load tanks values and index them by Tank ID
			$this->expected_tank_values[ $tank->IDNum ] = $tank;

		}
	}

	protected function getUserSummary( $account_id ) {
		return WargamingAPI::wot()->account->info( [
			'fields'     => 'statistics.all.battles,statistics.all.frags,statistics.all.damage_dealt,statistics.all.dropped_capture_points,statistics.all.spotted,statistics.all.wins',
			'account_id' => $account_id
		] )->{$account_id}->statistics->all;

	}

	protected function getUserTanks( $account_id ) {
		return WargamingAPI::wot()->account->tanks( [
			'fields'     => 'tank_id,statistics.battles',
			'account_id' => $account_id
		] )->{$account_id};
	}

	protected function getMissingTanksStats( $account_id, $missing ) {
		return WargamingAPI::wot()->tanks->stats( [
			'tank_id'    => implode( ',', $missing ),
			'fields'     => 'tank_id,all.battles,all.frags,all.damage_dealt,all.dropped_capture_points,all.spotted,all.wins',
			'account_id' => $account_id
		] )->$account_id;

	}

	protected function calculateWN8(): float {

		$summary = $this->getUserSummary( $this->account_id );
		$tanks   = $this->getUserTanks( $this->account_id );

		// If this account has no tanks data skip calculation and return 0
		if ( empty( $tanks ) ) {
			return 0;
		}

		// WN8 expected calculation
		$expDAMAGE = $expFRAGS = $expSPOT = $expDEF = $expWIN = 0;

		// Tanks missing in expected tank values but existing in account
		$missing = array();

		// Calculated account expected values
		foreach ( $tanks AS $tank ) {
			// Tank exists in expected tank values
			if ( isset( $this->expected_tank_values[ $tank->tank_id ] ) ) {

				// Expected values for current tank
				$expected = $this->expected_tank_values[ $tank->tank_id ];

				// Battles on current tank
				$tank_battles = $tank->statistics->battles;

				// Calculate expected values for current tank
				$expDAMAGE += $expected->expDamage * $tank_battles;
				$expSPOT   += $expected->expSpot * $tank_battles;
				$expFRAGS  += $expected->expFrag * $tank_battles;
				$expDEF    += $expected->expDef * $tank_battles;
				$expWIN    += 0.01 * $expected->expWinRate * $tank_battles;

				// Tank missing in expected tank values so add it to the list
			} else {

				$missing [] = $tank->tank_id;

			}
		}

		// User want accurate calculation
		if ( ! empty( $missing ) ) {

			// Get missing tanks stats from API server
			$missing_tanks = $this->getMissingTanksStats( $this->account_id, $missing );

			// Reduce account summary data
			foreach ( $missing_tanks AS $tank ) {
				$summary->damage_dealt           -= $tank->all->damage_dealt;
				$summary->spotted                -= $tank->all->spotted;
				$summary->frags                  -= $tank->all->frags;
				$summary->dropped_capture_points -= $tank->all->dropped_capture_points;
				$summary->wins                   -= $tank->all->wins;
			}
		}

		// Calculate WN8
		$rDAMAGE  = $summary->damage_dealt / $expDAMAGE;
		$rSPOT    = $summary->spotted / $expSPOT;
		$rFRAG    = $summary->frags / $expFRAGS;
		$rDEF     = $summary->dropped_capture_points / $expDEF;
		$rWIN     = $summary->wins / $expWIN;
		$rWINc    = max( 0, ( $rWIN - 0.71 ) / ( 1 - 0.71 ) );
		$rDAMAGEc = max( 0, ( $rDAMAGE - 0.22 ) / ( 1 - 0.22 ) );
		$rFRAGc   = max( 0, min( $rDAMAGEc + 0.2, ( $rFRAG - 0.12 ) / ( 1 - 0.12 ) ) );
		$rSPOTc   = max( 0, min( $rDAMAGEc + 0.1, ( $rSPOT - 0.38 ) / ( 1 - 0.38 ) ) );
		$rDEFc    = max( 0, min( $rDAMAGEc + 0.1, ( $rDEF - 0.10 ) / ( 1 - 0.10 ) ) );
		$wn8      = 980 * $rDAMAGEc + 210 * $rDAMAGEc * $rFRAGc + 155 * $rFRAGc * $rSPOTc + 75 * $rDEFc * $rFRAGc + 145 * MIN( 1.8, $rWINc );

		return round( $wn8, 2 );
	}

	public function __toString(): string {
		return (string) $this->wn8;
	}

	public function __toFloat(): float {
		return (float) $this->wn8;
	}

	public function __toInt(): int {
		return (int) $this->wn8;
	}

}