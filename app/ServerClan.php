<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ServerClan
 *
 * @property int $id
 * @property int $server_id
 * @property int $clan_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClan serverId($server_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClan whereClanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClan whereServerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServerClan extends Model
{
	public function scopeserverId($query,$server_id)
	{
		return $query->where('server_id', '=', $server_id);
	}

}
