<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, SettingsConstants as SC};
use App\Enums\UserType;
use Illuminate\Database\{Eloquent\Model, Schema\Blueprint};
use Illuminate\Support\Facades\{DB, Log, Schema};

trait HasFinancialIssuingColumns
{
	protected static function bootHasFinancialIssuingColumns(): void
	{
		static::saving(function (Model $model): void {
			try {
				$tableName = $model->getTable();
				if (Schema::hasColumn($tableName, BC::COL_SBM_BY) && Schema::hasColumn($tableName, BC::COL_SBM_AT)) {
					$submittedBy = $model->getAttribute(BC::COL_SBM_BY);
					empty($submittedBy) && $model->setAttribute(BC::COL_SBM_AT, null);
				}
				$hasApvBy = Schema::hasColumn($tableName, BC::COL_APV_BY);
				if (!$hasApvBy)
					return;
				if (!Schema::hasTable(DC::TABLE_USERS)) {
					Log::warning('HasFinancialIssuingColumns: users table does not exist', [
						'trait' => __TRAIT__,
						'class' => get_class($model),
						'table' => $tableName,
					]);
					return;
				}
				$approver = $model->getAttribute(BC::COL_APV_BY);
				if (!empty($approver)) {
					$model->setAttribute(BC::COL_APV_AT, null);
					return;
				}
				$authorizedTypes = [
					UserType::SuperAdmin->value,
					UserType::Admin->value,
					UserType::Company->value,
					UserType::Accountant->value,
				];
				$authorizedApprover = DB::table(DC::TABLE_USERS)
					->where('id', $approver)
					->whereIn('type', $authorizedTypes)
					->exists();
				if (!$authorizedApprover) {
					Log::debug('HasFinancialIssuingColumns: approver not authorized or not found', [
						'trait' => __TRAIT__,
						'class' => get_class($model),
						'table' => $tableName,
						'approver_id' => $approver,
					]);
					$model->setAttribute(BC::COL_APV_BY, null);
					$model->setAttribute(BC::COL_APV_AT, null);
					return;
				}
				$creator = Schema::hasColumn($tableName, DC::COL_TABLE_CREATOR)
					? $model->getAttribute(DC::COL_TABLE_CREATOR)
					: null;
				$updater = Schema::hasColumn($tableName, DC::COL_TABLE_UPDATER)
					? $model->getAttribute(DC::COL_TABLE_UPDATER)
					: null;
				if (empty($creator) && empty($updater))
					return;
				$lastAuthorizedEditor = null;
				try {
					if (!empty($updater)) {
						$authorizedUpdater = DB::table(DC::TABLE_USERS)
							->where('id', $updater)
							->whereIn('type', $authorizedTypes)
							->exists();
						if ($authorizedUpdater)
							$lastAuthorizedEditor = $updater;
					}
					if (empty($lastAuthorizedEditor) && !empty($creator)) {
						$authorizedCreator = DB::table(DC::TABLE_USERS)
							->where('id', $creator)
							->whereIn('type', $authorizedTypes)
							->exists();
						if ($authorizedCreator)
							$lastAuthorizedEditor = $creator;
					}
					if (!empty($lastAuthorizedEditor)) {
						$model->setAttribute(BC::COL_APV_BY, $lastAuthorizedEditor);
						$model->setAttribute(BC::COL_APV_AT, now());
						Log::debug('HasFinancialIssuingColumns: auto-assigned approver', [
							'trait' => __TRAIT__,
							'class' => get_class($model),
							'table' => $tableName,
							'approver_id' => $lastAuthorizedEditor,
							'source' => $lastAuthorizedEditor === $updater ? 'updater' : 'creator',
						]);
					}
					empty($approver) && $model->setAttribute(BC::COL_APV_AT, null);
				} catch (\Throwable $e) {
					Log::error('HasFinancialIssuingColumns: failed to verify authorized users', [
						'trait' => __TRAIT__,
						'class' => get_class($model),
						'table' => $tableName,
						'method' => 'DB::table(users)->where()->whereIn()->exists()',
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'creator' => $creator,
						'updater' => $updater,
						'error' => $e->getMessage(),
					]);
				}
			} catch (\Throwable $e) {
				Log::error('HasFinancialIssuingColumns: unexpected error in trait', [
					'trait' => __TRAIT__,
					'class' => get_class($model),
					'table' => $model->getTable(),
					'method' => 'static::saving',
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
				]);
			}
		});
	}
	protected function addBasicFinancialIssuingColumns(Blueprint $table): void
	{
		$table->unsignedDecimal('amount', 16, 2)->default(0.00); // * it is not clear yet if this is the gross or net amount, so keeping it as is from the old implementation
		$table->string(BC::COL_CUR_ID, 3)->default(SC::DEF_SITE_CURRENCY_ID)->nullable(); // ? nullable for testing purposes
		$table->float('discount')->default(0.00)->nullable();
		$table->string('reference')->nullable();
		$table->text('description')->nullable();
		$table->text('notes')->nullable();
		$table->uuid(BC::COL_SBM_BY)->nullable()->index();
		$table->timestamp(BC::COL_SBM_AT)->nullable();
		$table->uuid(BC::COL_APV_BY)->nullable()->index();
		$table->timestamp(BC::COL_APV_AT)->nullable();
		$table->uuid('contract')->nullable()->index();
		foreach (
			[
				BC::COL_SBM_BY => DC::TABLE_USERS,
				BC::COL_APV_BY => DC::TABLE_USERS,
				'contract' => DC::TABLE_CONTRACTS,
			] as $column => $referencedTable
		)
			$table->foreign($column)
				->references('id')
				->on($referencedTable)
				->nullOnDelete();
		$table->json(BC::COL_TC)->nullable();
	}
	protected function addFinancialIssuingColumns(Blueprint $table, bool $nullableReconcile = true): void
	{
		$this->addBasicFinancialIssuingColumns($table);
		$table->unsignedDecimal(BC::COL_SVC_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes
		$table->unsignedDecimal(BC::COL_TXS_FEE, 16, 2)->default(0.00)->nullable(); // ? nullable for testing purposes
		$table->json('attachments')->nullable();  // * additional attachments, stored as scalar strings for file_paths (filtered by file_exists on the disk) || valid URL strings (with known URL domains, using https + domain in env('APP_URL') or common cloud storage resources [google drive, aws, dropbox, notion, ERPs, CRMs, CMs, etc.]) || uuids referencing valid ids (a uuid) for the Document model; if the schema has the 'attachment' column, then it is automatically appended here for legacy compatibility
		$nullableReconcile ? $table->boolean(BC::COL_AUTORCC)->default(false)->nullable() : $table->boolean(BC::COL_AUTORCC)->default(false);
		$nullableReconcile ? $table->json(BC::COL_RCC_RL)->nullable() : $table->json(BC::COL_RCC_RL);
		$table->uuid('loan')->nullable()->index();
		$table->uuid(BC::COL_PRD_SV_UNT)->nullable()->index();
		foreach (
			[
				'loan' => DC::TABLE_LN,
				BC::COL_PRD_SV_UNT => DC::TABLE_PROD_SERV_UNITS,
			] as $column => $referencedTable
		)
			$table->foreign($column)
				->references('id')
				->on($referencedTable)
				->nullOnDelete();
	}
	protected function dropBasicFinancialIssuingColumnForeigns(Blueprint $table, ?string $tableName = null): void
	{
		foreach (
			['contract', BC::COL_APV_BY, BC::COL_SBM_BY] as $column
		)
			try {
				Schema::hasColumn($tableName ?? $table->getTable(), $column)
					&&
					$table->dropForeign([$column]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for contract: '
						. $e->getMessage()
				);
			}
	}
	protected function dropFinancialIssuingColumnForeigns(Blueprint $table, ?string $tableName = null): void
	{
		$this->dropBasicFinancialIssuingColumnForeigns($table, $tableName);
		foreach (
			[
				'loan',
				BC::COL_PRD_SV_UNT,
			] as $column
		) {
			try {
				Schema::hasColumn($tableName ?? $table->getTable(), $column)
					&&
					$table->dropForeign([$column]);
			} catch (\Exception $e) {
				Log::warning(
					'Failed to drop foreign key for '
						. $column
						. ': '
						. $e->getMessage()
				);
			}
		}
	}
}
