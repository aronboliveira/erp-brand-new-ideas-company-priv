<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Traits\BranchConnected;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Log, Schema};

trait HasRatingColumns
{
	use BranchConnected;
	protected function addRatingColumns(Blueprint $table, ?bool $nullableEmployee = true): void
	{
		$table->uuid('company')->nullable()->index(); // ? nullable for tests // ? should be check for $user->type === 'company' in model, else nullified
		$this->addBranchColumns($table, unique: false, nullable: false, prefixed: false);
		$nullableEmployee ? $table->uuid('employee')->nullable()->index() : $table->uuid('employee')->index();
		$table->string('rating', 128)->nullable(); // * for now keeping as a loose string, but should be checked at boot/save with the RatingTitle multilingual enum
		$table->unsignedTinyInteger('attendance')->default(0); // *these are not clear, but should be clamped between 0 to 10 for now
		$table->unsignedTinyInteger('administration')->default(0);
		$table->unsignedTinyInteger(PJC::COL_CST_EXP)->default(0);
		$table->unsignedTinyInteger('integrity')->default(0);
		$table->unsignedTinyInteger('marketing')->default(0);
		$table->unsignedTinyInteger('professionalism')->default(0);
		$table->foreign('employee')
			->references('id')
			->on(DC::TABLE_EMPLOYEES)
			->onDelete($nullableEmployee ? 'set null' : 'cascade');
		$table->foreign('company')
			->references('id')
			->on(DC::TABLE_USERS)
			->nullOnDelete();
	}
	protected function dropRatingColumnForeigns(Blueprint $table, ?string $tableName): void
	{
		$this->dropBranchColumnForeign($table, $tableName, prefixed: false);
		$columns = [
			'company',
			'employee',
		];
		$tableName ??= $table->getTable();
		foreach ($columns as $column) {
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
