<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait HasSalesRepresentantColumns
{
	protected function addSalesRepresentantColumns(Blueprint $table, string $prefix): void
	{
	    try {
    		$table->uuid($prefix . '_id')->nullable()->index();
    		$table->string(BC::COL_TX_N)->nullable();
    		$table->json(BC::COL_OT_TX_ID)->nullable();
    		$table->string('contact')->nullable();
    		$table->boolean(BC::COL_IS_PRM)->default(false)->nullable()->index();
    		foreach (
    			[
    				$prefix . '_id' => DC::TABLE_USERS,
    				BC::COL_TX_N => DC::TABLE_TAXES
    			] as $col => $tab
    		)
    			$table->foreign($col)
    				->references('id')
    				->on($tab)
    				->nullOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addSalesRepresentantColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropSalesRepresentantColumnForeigns(Blueprint $table, string $tableName, string $prefix): void
	{
	    try {
    		foreach (
    			[
    				$prefix . '_id',
    				BC::COL_TX_N
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
	        Log::error(static::class . '::dropSalesRepresentantColumnForeigns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
}
