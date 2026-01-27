<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, FormsConstants as FC, ProjectsConstants as PJC, SupportsConstants as SC};
use App\Enums\{EvaluationStatus, UserType};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Carbon\Carbon;

trait PlansByHierarchy
{
	protected static function bootPlansByHierarchy(): void
	{
		// todo this should be activated in production, but it's too heavy for mocks
		// self::saving(function (Model $model): void {
		// 	$tableName = $model->getTable();
		// 	$usersTable = DB::table(DC::TABLE_USERS);
		// 	$alwaysAuthorizedLevels = [UserType::SuperAdmin->value, UserType::Admin->value, UserType::Company->value];
		// 	$authorizedHierarchyLevels = in_array($tableName, [DC::TABLE_CMPT]) ? array_merge([UserType::Hr->value], $alwaysAuthorizedLevels) : (in_array($tableName, [DC::TABLE_EST]) ? array_merge([UserType::Accountant->value], $alwaysAuthorizedLevels) : $alwaysAuthorizedLevels);
		// 	$hasApvAt = Schema::hasColumn($tableName, PJC::COL_APV_AT);
		// 	if (Schema::hasColumn($tableName, PJC::COL_APV_BY)) {
		// 		$approver = $model->getAttribute(PJC::COL_APV_BY);
		// 		if (empty($approver))
		// 			$hasApvAt && $model->setAttribute(PJC::COL_APV_AT, null);
		// 		else {
		// 			try {
		// 				$isAuthorizedApprover = $usersTable
		// 					->where('id', $approver)
		// 					->whereIn('type', $authorizedHierarchyLevels)
		// 					->exists();
		// 				if (!$isAuthorizedApprover && Schema::hasColumn($tableName, SC::COL_ASG_BY)) {
		// 					$assignedBy = $model->getAttribute(SC::COL_ASG_BY);
		// 					$isAuthorizedAssigner = !empty($assignedBy) && $usersTable
		// 						->where('id', $assignedBy)
		// 						->whereIn('type', $authorizedHierarchyLevels)
		// 						->exists();
		// 					$isAuthorizedApprover = $isAuthorizedAssigner;
		// 				}
		// 				if (!$isAuthorizedApprover) {
		// 					$possibleUser = $usersTable->where('id', $model->getAttribute(PJC::COL_APV_BY))
		// 						->first(['name', 'type'])
		// 						?->toArray();
		// 					Log::notice("Unauthorized approver attempted", [
		// 						'table' => $tableName,
		// 						'approver_id' => $approver,
		// 						'approver_name' => $possibleUser['name'] ?? 'unknown',
		// 						'approver_type' => $possibleUser['type'] ?? 'unknown',
		// 						'model_id' => $model->getKey()
		// 					]);
		// 					$model->setAttribute(PJC::COL_APV_BY, null);
		// 					$hasApvAt && $model->setAttribute(PJC::COL_APV_AT, null);
		// 				} else if (empty($model->getAttribute(PJC::COL_APV_AT)))
		// 					$hasApvAt && $model->setAttribute(PJC::COL_APV_AT, now());
		// 			} catch (\Exception $e) {
		// 				Log::warning("Failed to validate approver authorization", [
		// 					'table' => $tableName,
		// 					'approver_id' => $approver,
		// 					'error' => $e->getMessage()
		// 				]);
		// 				$model->setAttribute(PJC::COL_APV_BY, null);
		// 				$hasApvAt && $model->setAttribute(PJC::COL_APV_AT, null);
		// 			}
		// 		}
		// 		if (empty($model->getAttribute(PJC::COL_APV_BY))) {
		// 			try {
		// 				$creator = $model->getAttribute(DC::COL_TABLE_CREATOR);
		// 				$updater = $model->getAttribute(DC::COL_TABLE_UPDATER);
		// 				$authUpdater = !empty($updater) && $usersTable
		// 					->where('id', $updater)
		// 					->whereIn('type', $authorizedHierarchyLevels)
		// 					->exists();
		// 				$authCreator = !empty($creator) && $usersTable
		// 					->where('id', $creator)
		// 					->whereIn('type', $authorizedHierarchyLevels)
		// 					->exists();
		// 				$lastAuthorized = $authUpdater ? $updater : ($authCreator ? $creator : null);
		// 				if ($lastAuthorized) {
		// 					$model->setAttribute(PJC::COL_APV_BY, $lastAuthorized);
		// 					$hasApvAt && $model->setAttribute(PJC::COL_APV_AT, now());
		// 				}
		// 			} catch (\Exception $e) {
		// 				Log::error("Failed to auto-approve for authorized user", [
		// 					'table' => $tableName,
		// 					'model_id' => $model->getKey(),
		// 					'error' => $e->getMessage()
		// 				]);
		// 			}
		// 		}
		// 	}
		// 	if (Schema::hasColumn($tableName, PJC::COL_REJ_BY)) {
		// 		$rejecter = $model->getAttribute(PJC::COL_REJ_BY);
		// 		$hasRejAt = Schema::hasColumn($tableName, PJC::COL_REJ_AT);
		// 		if (empty($rejecter))
		// 			$hasRejAt && $model->setAttribute(PJC::COL_REJ_AT, null);
		// 		else {
		// 			try {
		// 				$isAuthorizedRejecter = $usersTable
		// 					->where('id', $rejecter)
		// 					->whereIn('type', $authorizedHierarchyLevels)
		// 					->exists();
		// 				if (!$isAuthorizedRejecter) {
		// 					$possibleUser = $usersTable->where('id', $model->getAttribute(PJC::COL_REJ_BY))
		// 						->first(['name', 'type'])
		// 						?->toArray();
		// 					Log::notice("Unauthorized rejecter attempted", [
		// 						'table' => $tableName,
		// 						'rejecter_id' => $rejecter,
		// 						'rejecter_name' => $possibleUser['name'] ?? 'unknown',
		// 						'rejecter_type' => $possibleUser['type'] ?? 'unknown',
		// 						'model_id' => $model->getKey()
		// 					]);
		// 					$model->setAttribute(PJC::COL_REJ_BY, null);
		// 					$hasRejAt && $model->setAttribute(PJC::COL_REJ_AT, null);
		// 				} else {
		// 					if (empty($model->getAttribute(PJC::COL_REJ_AT)))
		// 						$hasRejAt && $model->setAttribute(PJC::COL_REJ_AT, now());
		// 				}
		// 			} catch (\Exception $e) {
		// 				Log::error("Failed to validate rejecter authorization", [
		// 					'table' => $tableName,
		// 					'rejecter_id' => $rejecter,
		// 					'error' => $e->getMessage()
		// 				]);
		// 				$model->setAttribute(PJC::COL_REJ_BY, null);
		// 				$hasRejAt && $model->setAttribute(PJC::COL_REJ_AT, null);
		// 			}
		// 		}
		// 	}
		// 	if (Schema::hasColumn($tableName, PJC::COL_SBM_BY)) {
		// 		$submitter = $model->getAttribute(PJC::COL_SBM_BY);
		// 		$hasSbmAt = Schema::hasColumn($tableName, PJC::COL_SBM_AT);
		// 		if (empty($submitter))
		// 			$hasSbmAt && $model->setAttribute(PJC::COL_SBM_AT, null);
		// 		else if (empty($model->getAttribute(PJC::COL_SBM_AT)))
		// 			$hasSbmAt && $model->setAttribute(PJC::COL_SBM_AT, now());
		// 	}
		// 	if (Schema::hasColumn($tableName, 'status')) {
		// 		try {
		// 			$newStatus = self::calculateDynamicStatus($model);
		// 			if ($newStatus !== null)
		// 				$model->setAttribute('status', $newStatus);
		// 		} catch (\Exception $e) {
		// 			Log::error("Failed to calculate dynamic status", [
		// 				'table' => $tableName,
		// 				'model_id' => $model->getKey(),
		// 				'error' => $e->getMessage()
		// 			]);
		// 		}
		// 	}
		// 	if (Schema::hasColumn($tableName, SC::COL_ASG_TO)) {
		// 		$assignedTo = $model->getAttribute(SC::COL_ASG_TO);
		// 		$hasAssignedByColumn = Schema::hasColumn($tableName, PJC::COL_ASG_BY);
		// 		$hasAsgAt = Schema::hasColumn($tableName, PJC::COL_ASG_AT);
		// 		if (empty($assignedTo)) {
		// 			if ($hasAssignedByColumn)
		// 				$model->setAttribute(PJC::COL_ASG_BY, null);
		// 			$hasAsgAt && $model->setAttribute(PJC::COL_ASG_AT, null);
		// 		} else {
		// 			if ($hasAssignedByColumn) {
		// 				$assignedBy = $model->getAttribute(PJC::COL_ASG_BY);
		// 				if (empty($assignedBy)) {
		// 					Log::error("Logic failure: assigned_to set without assigned_by", [
		// 						'table' => $tableName,
		// 						'model_id' => $model->getKey(),
		// 						'assigned_to' => $assignedTo
		// 					]);
		// 					$model->setAttribute(PJC::COL_ASG_BY, null);
		// 					$hasAsgAt && $model->setAttribute(PJC::COL_ASG_AT, null);
		// 				} else if ($hasAsgAt && empty($model->getAttribute(PJC::COL_ASG_AT)))
		// 					$model->setAttribute(PJC::COL_ASG_AT, now());
		// 			} else if ($hasAsgAt && empty($model->getAttribute(PJC::COL_ASG_AT)))
		// 				$model->setAttribute(PJC::COL_ASG_AT, now());
		// 		}
		// 	}
		// 	if (Schema::hasColumn($tableName, SC::COL_CLSD_BY)) {
		// 		$closedBy = $model->getAttribute(SC::COL_CLSD_BY);
		// 		$hasClosedAt = Schema::hasColumn($tableName, SC::COL_CLSD_AT);
		// 		if (empty($closedBy))
		// 			$hasClosedAt && $model->setAttribute(SC::COL_CLSD_AT, null);
		// 		else {
		// 			try {
		// 				$isAuthorizedCloser = $usersTable
		// 					->where('id', $closedBy)
		// 					->whereIn('type', $authorizedHierarchyLevels)
		// 					->exists() ?? $usersTable
		// 					->where('id', $model->getAttribute(PJC::COL_SBM_BY))
		// 					->exists() ?? $usersTable
		// 					->where('id', $model->getAttribute(SC::COL_ASG_BY))
		// 					->exists() ?? $usersTable
		// 					->where('id', $model->getAttribute(SC::COL_ASG_TO))
		// 					->exists();
		// 				if (!$isAuthorizedCloser) {
		// 					$possibleUser = $usersTable->where('id', $model->getAttribute(SC::COL_CLSD_BY))
		// 						->first(['name', 'type'])
		// 						?->toArray();
		// 					Log::notice("Unauthorized closer attempted", [
		// 						'table' => $tableName,
		// 						'closer_id' => $closedBy,
		// 						'closer_name' => $possibleUser['name'] ?? 'unknown',
		// 						'closer_type' => $possibleUser['type'] ?? 'unknown',
		// 						'model_id' => $model->getKey()
		// 					]);
		// 					$model->setAttribute(SC::COL_CLSD_BY, null);
		// 					$hasClosedAt && $model->setAttribute(SC::COL_CLSD_AT, null);
		// 				} else if ($hasClosedAt && empty($model->getAttribute(SC::COL_CLSD_AT)))
		// 					$model->setAttribute(SC::COL_CLSD_AT, now());
		// 			} catch (\Exception $e) {
		// 				Log::error("Failed to validate closer authorization", [
		// 					'table' => $tableName,
		// 					'closer_id' => $closedBy,
		// 					'error' => $e->getMessage()
		// 				]);
		// 				$model->setAttribute(SC::COL_CLSD_BY, null);
		// 				$hasClosedAt && $model->setAttribute(SC::COL_CLSD_AT, null);
		// 			}
		// 		}
		// 	}
		// 	if (Schema::hasColumn($tableName, BC::COL_SIGN_BY)) {
		// 		$signer = $model->getAttribute(BC::COL_SIGN_BY);
		// 		$hasSignAtColumn = Schema::hasColumn($tableName, BC::COL_SIGN_AT);
		// 		$hasSignByName = Schema::hasColumn($tableName, BC::COL_SIGN_BY_NAME);
		// 		$hasIsSign = Schema::hasColumn($tableName, BC::COL_IS_SIGN);
		// 		if ($hasSignByName) {
		// 			$signerName = $model->getAttribute(BC::COL_SIGN_BY_NAME);
		// 			$signerExists = $usersTable
		// 				->where('name', $signerName)
		// 				->exists();
		// 			if ($signerExists && empty($signer)) Log::notice("Signer name found in the Database, but provided without signer id", [
		// 				'table' => $tableName,
		// 				'signer_name' => $signerName,
		// 				'model_id' => $model->getKey()
		// 			]);
		// 		}
		// 		if (empty($signer)) {
		// 			$hasSignAtColumn && $model->setAttribute(BC::COL_SIGN_AT, null);
		// 			$hasIsSign && $model->setAttribute(BC::COL_IS_SIGN, false);
		// 			$hasSignByName && $model->setAttribute(BC::COL_SIGN_BY_NAME, null);
		// 		} else {
		// 			$signerData = $usersTable
		// 				->where('id', $signer)
		// 				->first();
		// 			if ($signerData) {
		// 				if ($hasSignAtColumn && empty($model->getAttribute(BC::COL_SIGN_AT)))
		// 					$model->setAttribute(BC::COL_SIGN_AT, now());
		// 				if ($hasIsSign)
		// 					$model->setAttribute(BC::COL_IS_SIGN, true);
		// 				$name = Schema::hasColumn(DC::TABLE_USERS, 'name') ? $signerData->name : (Schema::hasColumn(DC::TABLE_USERS, 'first_name') && Schema::hasColumn(DC::TABLE_USERS, 'last_name') ? trim($signerData->first_name . ' ' . $signerData->last_name) : null);
		// 				if ($hasSignByName && !empty($name))
		// 					$model->setAttribute(BC::COL_SIGN_BY_NAME, $name);
		// 			} else Log::notice("Listed signer id not found in the Database", [
		// 				'table' => $tableName,
		// 				'signer_id' => $signer,
		// 				'file' => __FILE__,
		// 				'line' => __LINE__,
		// 				'trait' => __TRAIT__,
		// 				'method' => __METHOD__,
		// 				'class' => __CLASS__,
		// 				'model_id' => $model->getKey()
		// 			]);
		// 		}
		// 	}
		// 	if (Schema::hasColumn($tableName, FC::COL_DPL_BY)) {
		// 		$deployer = $model->getAttribute(FC::COL_DPL_BY);
		// 		$hasDplAt = Schema::hasColumn($tableName, FC::COL_DPL_AT);
		// 		if (empty($deployer))
		// 			$hasDplAt && $model->setAttribute(FC::COL_DPL_AT, null);
		// 		else if (empty($model->getAttribute(FC::COL_DPL_AT)))
		// 			$hasDplAt && $model->setAttribute(FC::COL_DPL_AT, now());
		// 	}
		// 	if (Schema::hasColumn($tableName, FC::COL_PUB_BY)) {
		// 		$publisher = $model->getAttribute(FC::COL_PUB_BY);
		// 		$hasPUBAt = Schema::hasColumn($tableName, FC::COL_PUB_AT);
		// 		if (empty($publisher))
		// 			$hasPUBAt && $model->setAttribute(FC::COL_PUB_AT, null);
		// 		else if (empty($model->getAttribute(FC::COL_PUB_AT)))
		// 			$hasPUBAt && $model->setAttribute(FC::COL_PUB_AT, now());
		// 	}
		// if (Schema::hasColumn(BC::COL_REQ_BY)) {
		// 	$requester = $model->getAttribute(BC::COL_REQ_BY);
		// 	$hasReqAt = Schema::hasColumn($tableName, BC::COL_REQ_AT);
		// 	if (empty($requester))
		// 		$hasReqAt && $model->setAttribute(BC::COL_REQ_AT, null);
		// 	else if (empty($model->getAttribute(BC::COL_REQ_AT)))
		// 		$hasReqAt && $model->setAttribute(BC::COL_REQ_AT, now());
		// }
		// });
	}

	/**
	 * Calculate dynamic status based on approval/rejection state and date/time columns
	 * 
	 * Priority order:
	 * 1. Rejected (if rejected_by is set)
	 * 2. Approved (if approved_by is set)
	 * 3. Submitted (if submitted_by is set)
	 * 4. Time-based status (based on date/time vs current datetime)
	 */
	protected static function calculateDynamicStatus(Model $model): ?string
	{
		$tableName = $model->getTable();
		$hasApprovalColumn = Schema::hasColumn($tableName, PJC::COL_APV_BY);
		$hasRejectionColumn = Schema::hasColumn($tableName, PJC::COL_REJ_BY);
		$hasSubmissionColumn = Schema::hasColumn($tableName, PJC::COL_SBM_BY);
		$hasDateColumn = Schema::hasColumn($tableName, 'date');
		$hasTimeColumn = Schema::hasColumn($tableName, 'time');
		$hasStartDateColumn = Schema::hasColumn($tableName, PJC::COL_S_DT);
		$hasEndDateColumn = Schema::hasColumn($tableName, PJC::COL_E_DT);
		if ($hasRejectionColumn && !empty($model->getAttribute(PJC::COL_REJ_BY)))
			return EvaluationStatus::Decline->value;
		if ($hasApprovalColumn && !empty($model->getAttribute(PJC::COL_APV_BY)))
			return EvaluationStatus::Accept->value;
		if ($hasSubmissionColumn && !empty($model->getAttribute(PJC::COL_SBM_BY)))
			return EvaluationStatus::Pending->value;
		try {
			$now = now();
			$dueDateTime = null;
			if ($hasDateColumn && $hasTimeColumn) {
				$date = $model->getAttribute('date');
				$time = $model->getAttribute('time');
				if (!empty($date) && !empty($time)) {
					try {
						if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1)
							$date = Carbon::parse($date)->format('Y-m-d');
						if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time) !== 1)
							$time = Carbon::parse($time)->format('H:i:s');
						$dueDateTime = Carbon::parse($date . ' ' . $time);
					} catch (\Exception $e) {
						Log::warning("Failed to parse date/time for status calculation", [
							'table' => $tableName,
							'date' => $date,
							'time' => $time,
							'error' => $e->getMessage()
						]);
					}
				} elseif (!empty($date)) {
					try {
						$dueDateTime = Carbon::parse($date)->endOfDay();
					} catch (\Exception $e) {
						Log::warning("Failed to parse date for status calculation", [
							'table' => $tableName,
							'date' => $date,
							'error' => $e->getMessage()
						]);
					}
				}
			}
			if (!$dueDateTime && ($hasStartDateColumn || $hasEndDateColumn)) {
				$startDate = $hasStartDateColumn ? $model->getAttribute(PJC::COL_S_DT) : null;
				$endDate = $hasEndDateColumn ? $model->getAttribute(PJC::COL_E_DT) : null;
				$targetDate = !empty($endDate) ? $endDate : $startDate;
				if (!empty($targetDate)) {
					try {
						$dueDateTime = Carbon::parse($targetDate);
					} catch (\Exception $e) {
						Log::warning("Failed to parse start/end date for status calculation", [
							'table' => $tableName,
							'start_date' => $startDate,
							'end_date' => $endDate,
							'error' => $e->getMessage()
						]);
					}
				}
			}
			if ($dueDateTime) {
				if ($now->greaterThan($dueDateTime))
					return EvaluationStatus::Expired->value;
				elseif ($now->isSameDay($dueDateTime) || $now->lessThanOrEqualTo($dueDateTime))
					return EvaluationStatus::InProgress->value;
				else
					return EvaluationStatus::NotStarted->value;
			}
		} catch (\Exception $e) {
			Log::error("Error in time-based status calculation", [
				'table' => $tableName,
				'model_id' => $model->getKey(),
				'error' => $e->getMessage()
			]);
		}
		return null;
	}
}
