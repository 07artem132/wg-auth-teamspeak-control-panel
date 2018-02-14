<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\server
 *
 * @property int $id
 * @property int $instanse_id
 * @property string $uid
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\server uid( $uid )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\server whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\server whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\server whereInstanseId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\server whereUid( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\server whereUpdatedAt( $value )
 * @mixin \Eloquent
 * @property-read \App\Instanse $instanse
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ServerClan[] $clans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ServerModule[] $modules
 */
class server extends Model {
	public function instanse() {
		return $this->hasOne( 'App\Instanse', 'id', 'instanse_id' );
	}

	public function scopeuid( $query, $uid ) {
		return $query->where( 'uid', '=', $uid );
	}

	function clans() {
		return $this->hasMany( 'App\ServerClanPostSgid', 'server_id', 'id' );
	}

	function modules(){
		return $this->hasMany( 'App\ServerModule', 'server_id', 'id' );
	}
}
