<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasFormalProjections
{
	protected function addFormalProjectionColumns(Blueprint $table, ?int $toleranceDays = 30): void
	{
		$table->datetime(BC::COL_VLD_TO)->default(now()->addDays($toleranceDays))->nullable()->index(); // ? nullable for testing purposes
		$table->boolean(BC::COL_RQ_SIGN)->default(false)->nullable(); // ? nullable for testing purposes
		$table->boolean(BC::COL_IS_SIGN)->default(false)->nullable()->index(); // ? nullable for testing purposes
		$table->datetime(BC::COL_SIGN_AT)->nullable(); // ? nullable for testing purposes
		$table->uuid(BC::COL_SIGN_BY, 128)->nullable(); // * a signer is not necessary a user
		$table->string(BC::COL_SIGN_BY_NAME)->nullable(); // ? nullable for testing purposes
		$table->json('payments')->nullable(); // * at boot/saving, should filter only for uuids that represent Payment queryable instances
		$table->datetime(BC::COL_VW_AT)->nullable(); // ? nullable for testing purposes
		$table->foreign(BC::COL_SIGN_BY)
			->references('id')
			->on(DC::TABLE_USERS)
			->nullOnDelete();
	}
	protected function dropFormalProjectionColumnForeigns(Blueprint $table, string $tableName): void
	{
		try {
			Schema::hasColumn($tableName, BC::COL_SIGN_BY) &&
				$table->dropForeign([BC::COL_SIGN_BY]);
		} catch (\Exception $e) {
			Log::warning(
				'Failed to drop foreign key for '
					. BC::COL_SIGN_BY
					. ' on table '
					. $tableName
					. ': '
					. $e->getMessage()
			);
		}
	}
}
