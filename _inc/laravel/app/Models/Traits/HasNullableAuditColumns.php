<?php

namespace App\Traits;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasNullableAuditColumns
{
	protected function addAuditColumns(Blueprint $table): void
	{
		$table->timestamps();
		$table->uuid(DC::COL_TABLE_CREATOR)->nullable();
		$table->uuid(DC::COL_TABLE_UPDATER)->nullable();
		foreach ([DC::COL_TABLE_CREATOR, DC::COL_TABLE_UPDATER] as $col)
			$table->foreign($col)
				->references('id')
				->on(DC::TABLE_USERS)
				->nullOnDelete();
	}
	protected function dropAuditColumnForeigns(Blueprint $table, string $tableName): void
	{
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
	}
}
