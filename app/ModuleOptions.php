<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ModuleOptions
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $module_id
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions whereDescription( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions whereModuleId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions whereName( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions whereUpdatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions module( $module_id )
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModuleOptions name( $module_id, $name )
 */
class ModuleOptions extends Model {
	public function scopename( $query, $module_id, $name ) {
		return $query->where( [
			[ 'name', '=', $name ],
			[ 'module_id', '=', $module_id ]
		] );
	}

	public function scopemodule( $query, $module_id ) {
		return $query->where( [ 'module_id', '=', $module_id ] );
	}
}
