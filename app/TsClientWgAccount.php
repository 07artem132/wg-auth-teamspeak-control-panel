<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TsClientWgAccount
 *
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TsClientWgAccount clientUID( $ClientUID )
 * @property int $id
 * @property int $wg_account_id
 * @property string $client_uid
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\WgAccount $wgAccount
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TsClientWgAccount whereClientUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TsClientWgAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TsClientWgAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TsClientWgAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TsClientWgAccount whereWgAccountId($value)
 */
class TsClientWgAccount extends Model {
	public function scopeclientUID( $query, $ClientUID ) {
		return $query->where( 'client_uid', '=', $ClientUID );
	}
	public function scopeserverID( $query, $ClientUID ) {
		return $query->where( 'server_id', '=', $ClientUID );
	}

	function server(){
		return $this->hasOne( 'App\server', 'id', 'server_id' );
	}

	function wgAccount() {
		return $this->hasOne( 'App\WgAccount', 'id', 'wg_account_id' );
	}
}
