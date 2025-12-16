<?php

namespace App\Traits;

use App\Config\Constants\{CompaniesConstants as CC, ProjectsConstants as PJC, DatabaseConstants as DC};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait StoresPlanning
{
	protected function addPlanningColumns(Blueprint $table, bool $nullableTitle = false, bool $nullableFixedDate = true, bool $nullableFixedTime = true, bool $nullableDept = true, string $onDeleteDept = 'set null'): void
	{
		$nullableTitle ? $table->string('title')->nullable()->index() : $table->string('title')->index();
		$nullableFixedDate ? $table->uuid('date')->default(now()->format('Y-m-d'))->nullable()->index() : $table->uuid('date')->default(now()->format('Y-m-d'))->index();
		$onDeleteDept = strtolower((string) trim($onDeleteDept));
		$nullableFixedTime ? $table->time('time')->default(now()->addHours(24)->format('H:i:s'))->nullable()->index() : $table->time('time')->default(now()->addHours(24)->format('H:i:s'))->index();
		switch (true) {
			case $nullableDept && in_array($onDeleteDept, ['cascade', 'restrict']):
				Log::warning(
					'Foreign key constraint conflict: onDeleteDept was set to "' . $onDeleteDept . '" ' .
						'but $nullableDept is true. Forcing onDeleteDept to "set null" to maintain data integrity.'
				);
				$onDeleteDept = 'set null';
				break;
			case in_array($onDeleteDept, ['cascade', 'restrict', 'set null', 'null on delete']):
				$onDeleteDept = $onDeleteDept === 'null on delete' ? 'set null' : $onDeleteDept;
				break;
			default:
				$onDeleteDept = 'set null';
		}
		$nullableDept ? $table->uuid(CC::COL_DEP_ID)->nullable()->index() : $table->uuid(CC::COL_DEP_ID)->index();
		$table->unsignedTinyInteger(PJC::COL_MIN_DR)->default(15)->nullable(); // * duration in minutes
		$table->unsignedTinyInteger(PJC::COL_EXP_DR)->default(30)->nullable(); // * duration in minutes
		$table->unsignedTinyInteger(PJC::COL_MAX_DR)->default(60)->nullable(); // * duration in minutes
		$table->string('url', 256)->nullable();
		$table->string('location', 256)->nullable();
		$table->text('note')->nullable();
		$table->boolean(CC::COL_IS_INT)->default(true)->nullable();
		$table->json('attachments')->nullable();
		$table->json('invited')->nullable();
		$table->json('conditions')->nullable();
		$table->json('reminders')->nullable();
		$table->json('tags')->nullable();
		$table->foreign(CC::COL_DEP_ID)
			->references('id')
			->on(DC::TABLE_DEPARTMENTS)
			->onDelete($onDeleteDept);
	}

	protected function dropPlanningColumnForeigns(Blueprint $table, string $tableName): void
	{
		foreach (
			[
				DC::TABLE_DEPARTMENTS,
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
