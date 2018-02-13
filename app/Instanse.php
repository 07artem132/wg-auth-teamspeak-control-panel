<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Instanse
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $ip
 * @property string $port
 * @property string $login
 * @property string $password
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Instanse whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\server[] $servers
 */
class Instanse extends Model
{
    function servers(){
	    return $this->hasMany( 'App\server', 'instanse_id', 'id' );
    }
}
