<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait HasFormalProjections
{
	protected function addFormalProjectionColumns(Blueprint $table, ?int $toleranceDays = 30): void
	{
	    try {
    		$table->datetime(BC::COL_VLD_TO)->default(now()->addDays($toleranceDays))->nullable()->index();
    		$table->boolean(BC::COL_RQ_SIGN)->default(false)->nullable();
    		$table->boolean(BC::COL_IS_SIGN)->default(false)->nullable()->index();
    		$table->datetime(BC::COL_SIGN_AT)->nullable();
    		$table->uuid(BC::COL_SIGN_BY, 128)->nullable();
    		$table->string(BC::COL_SIGN_BY_NAME)->nullable();
    		$table->json('payments')->nullable();
    		$table->datetime(BC::COL_VW_AT)->nullable();
    		$table->foreign(BC::COL_SIGN_BY)
    			->references('id')
    			->on(DC::TABLE_USERS)
    			->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addFormalProjectionColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
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
