<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait HasNullableAuditColumns
{
	protected function addAuditColumns(Blueprint $table): void
	{
	    try {
    		$table->timestamps();
    		$table->uuid(DC::COL_TABLE_CREATOR)->nullable();
    		$table->uuid(DC::COL_TABLE_UPDATER)->nullable();
    		foreach ([DC::COL_TABLE_CREATOR, DC::COL_TABLE_UPDATER] as $col)
    			$table->foreign($col)
    				->references('id')
    				->on(DC::TABLE_USERS)
    				->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addAuditColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
		protected function addNullableAuditColumns(Blueprint $table): void
	{
		$this->addAuditColumns($table);
	}
	protected function dropAuditColumnForeigns(Blueprint $table, ?string $tableName = null): void
	{
	    try {
    		$tableName = $tableName ?? $table->getTable() ?? '#UNKNOWN_TABLE';
    		foreach (
    			[
    				DC::COL_TABLE_UPDATER,
    				DC::COL_TABLE_CREATOR,
    			] as $column
    		) {
    			try {
    				Schema::hasColumn($tableName, $column)
    					&& $table->dropForeign([$column]);
    			} catch (\Exception $e) {
    				Log::warning(
    					'Failed to drop foreign key for '
    						. $column
    						. ' on table '
    						. $tableName
    						. ': '
    						. $e->getMessage()
    				);
    			}
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::dropAuditColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
