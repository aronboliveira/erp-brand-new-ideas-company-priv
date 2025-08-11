<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants, UsersConstants};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};

class Client extends Model
{
	use UsesUuids;
	protected $table     = DatabaseConstants::TABLE_CLIENTS;
	protected $fillable  = [
		UsersConstants::COL_NM,
		UsersConstants::COL_EM,
		UsersConstants::COL_EM_V_AT,
		UsersConstants::COL_PW,
		UsersConstants::COL_LG,
		UsersConstants::COL_IA,
		UsersConstants::COL_USER_ID,
		UsersConstants::COL_TEL,
		UsersConstants::COL_ADR,
		UsersConstants::COL_IU,
		UsersConstants::COL_AV,
		UsersConstants::COL_MSG_CL,
		UsersConstants::COL_DEL_STT
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(
			User::class,
			'id',
			'id'
		);
	}
}
