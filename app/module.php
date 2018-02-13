<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\module
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\module whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\module whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\module whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\module whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\module whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\module name($name)
 */
class module extends Model
{
	public function scopename($query,$name)
	{
		return $query->where('name', '=', $name);
	}

}
