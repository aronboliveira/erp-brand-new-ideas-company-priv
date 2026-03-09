<?php

namespace App\Traits;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait TaskConnected
{
	protected function addTaskColumns(Blueprint $table, bool $unique = false, bool $nullable = false, bool $cascade = true): void
	{
	    try {
    		$unique ? ($nullable ? $table->uuid(AC::COL_TSK_ID)->nullable()->unique() : $table->uuid(AC::COL_TSK_ID)->index()) : ($nullable ? $table->uuid(AC::COL_TSK_ID)->nullable()->index() : $table->uuid(AC::COL_TSK_ID)->index());
    		$nullable ?
    			$table->foreign(AC::COL_TSK_ID)
    			->references('id')
    			->on(DC::TABLE_TASKS)
    			->nullOnDelete() : ($cascade ?
    				$table->foreign(AC::COL_TSK_ID)
    				->references('id')
    				->on(DC::TABLE_TASKS)
    				->cascadeOnDelete() :
    				$table->foreign(AC::COL_TSK_ID)
    				->references('id')
    				->on(DC::TABLE_TASKS)
    				->restrictOnDelete());
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addTaskColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function dropTaskColumnForeign(Blueprint $table, string $tableName): void
	{
		try {
			Schema::hasColumn($tableName, AC::COL_TSK_ID) &&
				$table->dropForeign([AC::COL_TSK_ID]);
		} catch (\Exception $e) {
			Log::warning(
				'Failed to drop foreign key for '
					. AC::COL_TSK_ID
					. ' on table '
					. $tableName
					. ': '
					. $e->getMessage()
			);
		}
	}
}
