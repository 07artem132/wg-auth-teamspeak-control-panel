<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ServerClanPostSgid
 *
 * @property int $id
 * @property int|null $clan_id
 * @property int|null $commander
 * @property int|null $executive_officer
 * @property int|null $personnel_officer
 * @property int|null $combat_officer
 * @property int|null $intelligence_officer
 * @property int|null $quartermaster
 * @property int|null $recruitment_officer
 * @property int|null $junior_officer
 * @property int|null $private
 * @property int|null $recruit
 * @property int|null $reservist
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereClanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereCombatOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereCommander($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereExecutiveOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereIntelligenceOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereJuniorOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid wherePersonnelOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereQuartermaster($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereRecruit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereRecruitmentOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereReservist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid clanID($ClanID)
 * @property int|null $clan_tag
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ServerClanPostSgid whereClanTag($value)
 */
class ServerClanPostSgid extends Model
{
	public function scopeclanID($query,$ClanID)
	{
		return $query->where('clan_id', '=', $ClanID);
	}

}
