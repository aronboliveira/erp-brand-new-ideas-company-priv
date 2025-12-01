<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class Client extends Model
{
	use UsesUuids, HasAuditFields;
	protected $table     = DC::TABLE_CLIENTS;
	protected $fillable  = [
		UC::COL_NM,
		UC::COL_EM,
		UC::COL_EM_V_AT,
		UC::COL_LG,
		UC::COL_IA,
		UC::COL_USER_ID,
		UC::COL_TEL,
		UC::COL_ADR,
		UC::COL_IU,
		UC::COL_AV,
		UC::COL_MSG_CL,
		UC::COL_DEL_STT
	];
	protected $guarded = [
		'id',
		DC::COL_TABLE_CREATOR,
	];
	protected $casts = [
		UC::COL_PW => 'hashed',
	];
	protected $hidden = [UC::COL_PW];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, UC::COL_USER_ID, 'id');
	}
}
