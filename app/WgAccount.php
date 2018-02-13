<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WgAccount
 *
 * @property int $id
 * @property string $client_uid
 * @property int $account_id
 * @property string $token
 * @property string|null $token_expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount accountId( $AccountID )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount clientUID( $ClientUID )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereAccountId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereClientUid( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereToken( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereTokenExpiresAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WgAccount whereUpdatedAt( $value )
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TsClientWgAccount[] $tsClient
 */
class WgAccount extends Model {


	function tsClient() {
		return $this->hasMany( 'App\TsClientWgAccount', 'wg_account_id', 'id' );
	}

	public function scopeaccount_id( $query, $AccountID ) {
		return $query->where( 'account_id', '=', $AccountID );
	}
}
