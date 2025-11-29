<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, CompaniesConstants as CC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait BranchConnected
{
	protected function addBranchColumns(Blueprint $table, bool $unique = false, bool $nullable = false): void
	{
		$unique ? ($nullable ? $table->uuid(CC::COL_BRC_ID)->unique()->nullable()->index() : $table->uuid(CC::COL_BRC_ID)->index()) : ($nullable ? $table->uuid(CC::COL_BRC_ID)->nullable()->index() : $table->uuid(CC::COL_BRC_ID)->index());
		$nullable ?
			$table->foreign(CC::COL_BRC_ID)
			->references('id')
			->on(DC::TABLE_BRANCHES)
			->nullOnDelete() :
			$table->foreign(CC::COL_BRC_ID)
			->references('id')
			->on(DC::TABLE_BRANCHES)
			->cascadeOnDelete();
	}
	protected function dropBranchForeign(Blueprint $table, string $tableName): void
	{
		try {
			Schema::hasColumn($tableName, CC::COL_BRC_ID) &&
				$table->dropForeign([CC::COL_BRC_ID]);
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
