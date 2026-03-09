<?php

namespace App\Traits;

use App\Config\Constants\{CompaniesConstants as CPC, DatabaseConstants as DC};
use Illuminate\Database\Schema\{Blueprint};
use Illuminate\Support\Facades\{Log, Schema};

trait BranchConnected
{
	protected function addBranchColumns(Blueprint $table, bool $unique = false, bool $nullable = false, ?bool $prefixed = true): void
	{
	    try {
    		$id = $prefixed ? CPC::COL_BRC_ID : 'branch';

    		$col = $table->uuid($id);
    		if ($nullable) $col->nullable();

    		$unique ? $col->unique() : $col->index();

    		$nullable
    			? $table->foreign($id)->references('id')->on(DC::TABLE_BRANCHES)->nullOnDelete()
    			: $table->foreign($id)->references('id')->on(DC::TABLE_BRANCHES)->cascadeOnDelete();
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::addBranchColumns — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	    }
	}

	protected function dropBranchColumnForeign(Blueprint $table, string $tableName, ?bool $prefixed = true): void
	{
		try {
			$id = $prefixed ? CPC::COL_BRC_ID : 'branch';
			Schema::hasColumn($tableName, $id) && $table->dropForeign([$id]);
		} catch (\Exception $e) {
			Log::warning(
				'Failed to drop foreign key for '
					. CPC::COL_BRC_ID
					. ' on table '
					. $tableName
					. ': '
					. $e->getMessage()
			);
		}
	}
}
