<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ServerModule
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $server_id
 * @property int $module_id
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModule whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModule whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModule whereModuleId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModule whereServerId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModule whereStatus( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModule whereUpdatedAt( $value )
 * @property-read \App\module $module
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ServerModuleOptions[] $options
 */
class ServerModule extends Model {
	function module() {
		return $this->hasOne( 'App\module', 'id', 'module_id' );
	}

	function options() {
		return $this->hasMany( 'App\ServerModuleOptions', 'server_module_id', 'id' );
	}
}
