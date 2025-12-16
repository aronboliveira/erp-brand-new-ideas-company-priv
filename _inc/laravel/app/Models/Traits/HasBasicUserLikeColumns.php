<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use Illuminate\Database\Schema\Blueprint;

trait HasBasicUserLikeColumns
{
	protected function addUserLikeColumns(Blueprint $table, ?string $defaultAvatar = null, bool $hasPassword = false, bool $nullableName = false): void
	{
		$defaultAvatar ??= config('chatify.user_avatar.default');
		$table->uuid('id')->primary();
		$nullableName ? $table->string(UC::COL_NM)->nullable()->index() : $table->string(UC::COL_NM)->index();
		$table->string(UC::COL_EM)->unique()->nullable();
		$table->timestamp(UC::COL_EM_V_AT)->nullable();
		$table->string(UC::COL_AV)->default($defaultAvatar)->nullable();
		$table->integer(UC::COL_IA)->default(1);
		$table->string(UC::COL_LG)->default(DC::DEFAULT_LANG);
		$table->rememberToken();
		$table->json('preferences')->nullable();
		$hasPassword ? $table->string(UC::COL_PW)->nullable() : null;
	}
}
