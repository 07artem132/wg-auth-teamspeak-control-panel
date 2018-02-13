<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ServerModuleOptions
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $server_id
 * @property int $module_id
 * @property int $module_option_id
 * @property string $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereModuleOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereValue($value)
 * @property int $server_module_id
 * @property-read \App\ModuleOptions $option
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerModuleOptions whereServerModuleId($value)
 */
class ServerModuleOptions extends Model
{
	function option() {
		return $this->hasOne( 'App\ModuleOptions', 'id', 'module_option_id' );
	}

}
