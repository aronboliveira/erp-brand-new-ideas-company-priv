<?php

namespace App\Traits;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	FormsConstants as FC,
	ProjectsConstants as PJC,
	SupportsConstants as SC
};
use App\Enums\{EvaluationStatus, UserType};
use Carbon\{Carbon};
use Illuminate\Database\Eloquent\{Model};
use Illuminate\Support\Facades\{DB, Log, Schema};

trait PlansByHierarchy
{
	protected static function bootPlansByHierarchy(): void
	{
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																			}

		protected static function calculateDynamicStatus(Model $model): ?string
	{
		    try {
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
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::calculateDynamicStatus — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return '';
		    }
	}
}
