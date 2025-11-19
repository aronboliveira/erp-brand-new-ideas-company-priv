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
		$table->uuid(DC::TABLE_CREATOR)->nullable();
		$table->uuid(DC::TABLE_UPDATER)->nullable();
		foreach ([DC::TABLE_CREATOR, DC::TABLE_UPDATER] as $col)
			$table->foreign($col)
				->references('id')
				->on(DC::TABLE_USERS)
				->nullOnDelete();
	}
	protected function dropAuditColumnForeigns(Blueprint $table, string $tableName): void
	{
		foreach (
			[
				DC::TABLE_UPDATER,
				DC::TABLE_CREATOR,
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
