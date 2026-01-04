<?php

namespace App\Traits;

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC};
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log, Schema};

trait DescribesCompanyBranch
{
	protected static function bootDescribesCompanyBranch(): void
	{
		static::saving(function (Model $model): void {
			try {
				$tableName = $model->getTable();
				$branchColumn = Schema::hasColumn($tableName, 'branch')
					? 'branch'
					: (Schema::hasColumn($tableName, CC::COL_BRC_ID) ? CC::COL_BRC_ID : null);
				$companyColumn = Schema::hasColumn($tableName, 'company')
					? 'company'
					: (Schema::hasColumn($tableName, CC::COL_CP_ID) ? CC::COL_CP_ID : null);
				if (empty($branchColumn) || empty($companyColumn))
					return;
				if (!Schema::hasTable(DC::TABLE_BRANCHES)) {
					Log::warning('DescribesCompanyBranch: branches table does not exist', [
						'trait' => __TRAIT__,
						'class' => get_class($model),
						'table' => $tableName,
					]);
					return;
				}
				$companyColumnForBranch = Schema::hasColumn(DC::TABLE_BRANCHES, 'company')
					? 'company'
					: (Schema::hasColumn(DC::TABLE_BRANCHES, CC::COL_CP_ID) ? CC::COL_CP_ID : null);
				if (empty($companyColumnForBranch)) {
					Log::warning('DescribesCompanyBranch: company column not found in branches table', [
						'trait' => __TRAIT__,
						'class' => get_class($model),
						'table' => $tableName,
						'branches_table' => DC::TABLE_BRANCHES,
					]);
					return;
				}
				$branchId = $model->getAttribute($branchColumn);
				$currentCompanyId = $model->getAttribute($companyColumn);
				if (empty($branchId))
					return;
				try {
					$branch = DB::table(DC::TABLE_BRANCHES)
						->where('id', $branchId)
						->first([$companyColumnForBranch]);
					if (!$branch) {
						Log::debug('DescribesCompanyBranch: branch not found', [
							'trait' => __TRAIT__,
							'class' => get_class($model),
							'table' => $tableName,
							'branch_id' => $branchId,
							'branch_column' => $branchColumn,
						]);
						return;
					}
					$branchCompanyId = $branch->{$companyColumnForBranch} ?? null;
					if (empty($branchCompanyId)) {
						Log::debug('DescribesCompanyBranch: company not found in branch', [
							'trait' => __TRAIT__,
							'class' => get_class($model),
							'table' => $tableName,
							'branch_id' => $branchId,
							'company_column' => $companyColumnForBranch,
						]);
						return;
					}
					try {
						$isValidCompanyType = DB::table(DC::TABLE_USERS)
							->where('id', $branchCompanyId)
							->whereIn('type', [UserType::Company->value, UserType::Vendor->value])
							->exists();
						if (!$isValidCompanyType) {
							Log::debug('DescribesCompanyBranch: branch company is not a valid Company/Vendor user type', [
								'trait' => __TRAIT__,
								'class' => get_class($model),
								'table' => $tableName,
								'branch_id' => $branchId,
								'branch_company_id' => $branchCompanyId,
							]);
							return;
						}
						if ((string) $currentCompanyId !== (string) $branchCompanyId) {
							$model->setAttribute($companyColumn, $branchCompanyId);
							Log::debug('DescribesCompanyBranch: company enforced from branch', [
								'trait' => __TRAIT__,
								'class' => get_class($model),
								'table' => $tableName,
								'branch_id' => $branchId,
								'old_company_id' => $currentCompanyId,
								'branch_company_id' => $branchCompanyId,
							]);
						}
					} catch (\Throwable $e) {
						Log::error('DescribesCompanyBranch: failed to verify branch company user type', [
							'trait' => __TRAIT__,
							'class' => get_class($model),
							'table' => $tableName,
							'method' => 'DB::table(users)->where()->whereIn()->exists()',
							'file' => $e->getFile(),
							'line' => $e->getLine(),
							'branch_company_id' => $branchCompanyId,
							'error' => $e->getMessage(),
						]);
					}
				} catch (\Throwable $e) {
					Log::error('DescribesCompanyBranch: failed to fetch branch', [
						'trait' => __TRAIT__,
						'class' => get_class($model),
						'table' => $tableName,
						'method' => 'DB::table(branches)->where()->first()',
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'branch_id' => $branchId,
						'branch_column' => $branchColumn,
						'company_column' => $companyColumnForBranch,
						'error' => $e->getMessage(),
					]);
				}
			} catch (\Throwable $e) {
				Log::error('DescribesCompanyBranch: unexpected error in trait', [
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
}
