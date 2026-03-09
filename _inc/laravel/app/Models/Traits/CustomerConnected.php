<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait CustomerConnected
{
	protected function addCustomerColumns(Blueprint $table, bool $unique = false, bool $nullable = false, string $onDelete = 'cascade'): void
	{
	    try {
    		$onDelete = strtolower((string) trim($onDelete));
    		switch (true) {
    			case $nullable && in_array($onDelete, ['cascade', 'restrict']):
    				Log::warning(
    					'Foreign key constraint conflict: onDelete was set to "' . $onDelete . '" ' .
    						'but $nullable is true for customer columns. Forcing onDelete to "set null" to maintain data integrity.'
    				);
    				$onDelete = 'set null';
    				break;
    			case in_array($onDelete, ['cascade', 'restrict', 'set null', 'null on delete']):
    				$onDelete = $onDelete === 'null on delete' ? 'set null' : $onDelete;
    				break;
    			default:
    				$onDelete = 'cascade';
    		}
    		$unique ? ($nullable ? $table->uuid(BC::COL_CST_ID)->nullable()->unique() : $table->uuid(BC::COL_CST_ID)->index()) : ($nullable ? $table->uuid(BC::COL_CST_ID)->nullable()->index() : $table->uuid(BC::COL_CST_ID)->index());
    		$table->foreign(BC::COL_CST_ID)
    			->references('id')
    			->on(DC::TABLE_CUSTOMERS)
    			->onDelete($onDelete);
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addCustomerColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}
	protected function dropCustomerColumnForeigns(Blueprint $table, string $tableName): void
	{
		try {
			Schema::hasColumn($tableName, BC::COL_CST_ID) &&
				$table->dropForeign([BC::COL_CST_ID]);
		} catch (\Exception $e) {
			Log::warning(
				'Failed to drop foreign key for '
					. BC::COL_CST_ID
					. ' on table '
					. $tableName
					. ': '
					. $e->getMessage()
			);
		}
	}
}
