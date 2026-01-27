<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, CompaniesConstants as CC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait BranchConnected
{
	protected function addBranchColumns(Blueprint $table, bool $unique = false, bool $nullable = false, ?bool $prefixed = true): void
	{
		$id = $prefixed ? CC::COL_BRC_ID : 'branch';

		$col = $table->uuid($id);
		if ($nullable) $col->nullable();

		$unique ? $col->unique() : $col->index();

		$nullable
			? $table->foreign($id)->references('id')->on(DC::TABLE_BRANCHES)->nullOnDelete()
			: $table->foreign($id)->references('id')->on(DC::TABLE_BRANCHES)->cascadeOnDelete();
	}

	protected function dropBranchColumnForeign(Blueprint $table, string $tableName, ?bool $prefixed = true): void
	{
		try {
			$id = $prefixed ? CC::COL_BRC_ID : 'branch';
			Schema::hasColumn($tableName, $id) && $table->dropForeign([$id]);
		} catch (\Exception $e) {
			Log::warning(
				'Failed to drop foreign key for '
					. CC::COL_BRC_ID
					. ' on table '
					. $tableName
					. ': '
					. $e->getMessage()
			);
		}
	}
}
