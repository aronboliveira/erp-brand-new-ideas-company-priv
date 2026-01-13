<?php

namespace App\Traits;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	BanksConstants as BKC,
	BillsConstants as BC,
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	MessagesConstants as MC,
	ProjectsConstants as PJC,
	SupportsConstants as SPC,
	UsersConstants as UC,
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Log, Schema};

trait DefinesDates
{
	public const DATE_COLUMNS = [
		PJC::COL_S_DT,
		PJC::COL_E_DT,
		PJC::COL_D_DATE,
		AC::COL_PST_DT,
		AC::COL_JNG_DT,
		BKC::COL_TRF_DT,
		BC::COL_BL_DT,
		BC::COL_SD_DT,
		BC::COL_POS_DT,
		BC::COL_ISS_DT,
		BC::COL_RCC_DT,
		BC::COL_BNK_EXT_DT,
		CC::COL_CPT_DT,
		CC::COL_WRN_DATE,
		CC::COL_FD_DT,
		CC::COL_PRC_DT,
		CC::COL_SPT_DT,
		DC::COL_EXP_DT,
		PJC::COL_RVS_DT,
		PJC::COL_APR_DT,
		UC::COL_PED,
		UC::COL_TERMINATION_DT,
		UC::COL_TERMINATION_NDT,
		UC::COL_RESIGNATION_DT,
		UC::COL_PRMT_DT,
		UC::COL_TRF_DT,
		PJC::COL_SBM_AT,
		PJC::COL_REJ_AT,
		PJC::COL_APV_AT,
		PJC::COL_INV_AT,
		MC::COL_SNT_AT,
		MC::COL_RD_AT,
		AC::COL_APL_AT,
		AC::COL_LST_RVW_AT,
		PJC::COL_JND_AT,
		PJC::COL_LFT_AT,
		PJC::COL_EXP_AT,
		SPC::COL_CLSD_AT,
	];

	protected static function bootDefinesDates(): void
	{
		// todo this should be activated in production, but it's too heavy for mocks
		// static::saving(function (Model $model) {
		// 	if (!$model instanceof Model)
		// 		return;

		// 	$tableName = $model->getTable();
		// 	if (empty($tableName) || !is_string($tableName))
		// 		return;

		// 	try {
		// 		$hasStartDate = Schema::hasColumn($tableName, PJC::COL_S_DT);
		// 		$hasEndDate = Schema::hasColumn($tableName, PJC::COL_E_DT);
		// 		$hasDueDate = Schema::hasColumn($tableName, PJC::COL_D_DATE);
		// 		$hasExpDate = Schema::hasColumn($tableName, DC::COL_EXP_DT);
		// 		$hasJngDate = Schema::hasColumn($tableName, AC::COL_JNG_DT);
		// 		$hasFndDate = Schema::hasColumn($tableName, CC::COL_FD_DT);
		// 		$hasBillDate = Schema::hasColumn($tableName, BC::COL_BL_DT);
		// 		$hasSendDate = Schema::hasColumn($tableName, BC::COL_SD_DT);
		// 		$hasIssueDate = Schema::hasColumn($tableName, BC::COL_ISS_DT);
		// 		$hasReconcileDate = Schema::hasColumn($tableName, BC::COL_RCC_DT);
		// 		$hasTerminationDate = Schema::hasColumn($tableName, UC::COL_TERMINATION_DT);
		// 		$hasNoticeDate = Schema::hasColumn($tableName, UC::COL_TERMINATION_NDT);
		// 		$hasResignationDate = Schema::hasColumn($tableName, UC::COL_RESIGNATION_DT);
		// 		$hasPromotionDate = Schema::hasColumn($tableName, UC::COL_PRMT_DT);
		// 		$hasTransferDate = Schema::hasColumn($tableName, UC::COL_TRF_DT);
		// 		$hasSubmittedAt = Schema::hasColumn($tableName, PJC::COL_SBM_AT);
		// 		$hasRejectedAt = Schema::hasColumn($tableName, PJC::COL_REJ_AT);
		// 		$hasApprovedAt = Schema::hasColumn($tableName, PJC::COL_APV_AT);
		// 		$hasInvitedAt = Schema::hasColumn($tableName, PJC::COL_INV_AT);
		// 		$hasSentAt = Schema::hasColumn($tableName, MC::COL_SNT_AT);
		// 		$hasReadAt = Schema::hasColumn($tableName, MC::COL_RD_AT);
		// 		$hasAppliedAt = Schema::hasColumn($tableName, AC::COL_APL_AT);
		// 		$hasLastReviewAt = Schema::hasColumn($tableName, AC::COL_LST_RVW_AT);
		// 		$hasJoinedAt = Schema::hasColumn($tableName, PJC::COL_JND_AT);
		// 		$hasLeftAt = Schema::hasColumn($tableName, PJC::COL_LFT_AT);
		// 		$hasExpiredAt = Schema::hasColumn($tableName, PJC::COL_EXP_AT);
		// 		$hasClosedAt = Schema::hasColumn($tableName, SPC::COL_CLSD_AT);
		// 	} catch (\Exception $e) {
		// 		Log::notice("Failed to check columns for date definitions", [
		// 			'table' => $tableName,
		// 			'error' => $e->getMessage(),
		// 			'file' => $e->getFile(),
		// 			'line' => $e->getLine(),
		// 			'trait' => __TRAIT__,
		// 			'class' => static::class,
		// 		]);
		// 		return;
		// 	}
		// 	$startDate = $hasStartDate ? $model->getAttribute(PJC::COL_S_DT) : null;
		// 	$endDate = $hasEndDate ? $model->getAttribute(PJC::COL_E_DT) : null;
		// 	$dueDate = $hasDueDate ? $model->getAttribute(PJC::COL_D_DATE) : null;
		// 	$jngDate = $hasJngDate ? $model->getAttribute(AC::COL_JNG_DT) : null;
		// 	$fndDate = $hasFndDate ? $model->getAttribute(CC::COL_FD_DT) : null;
		// 	$billDate = $hasBillDate ? $model->getAttribute(BC::COL_BL_DT) : null;
		// 	$sendDate = $hasSendDate ? $model->getAttribute(BC::COL_SD_DT) : null;
		// 	$issueDate = $hasIssueDate ? $model->getAttribute(BC::COL_ISS_DT) : null;
		// 	$reconcileDate = $hasReconcileDate ? $model->getAttribute(BC::COL_RCC_DT) : null;
		// 	$terminationDate = $hasTerminationDate ? $model->getAttribute(UC::COL_TERMINATION_DT) : null;
		// 	$noticeDate = $hasNoticeDate ? $model->getAttribute(UC::COL_TERMINATION_NDT) : null;
		// 	$resignationDate = $hasResignationDate ? $model->getAttribute(UC::COL_RESIGNATION_DT) : null;
		// 	$promotionDate = $hasPromotionDate ? $model->getAttribute(UC::COL_PRMT_DT) : null;
		// 	$transferDate = $hasTransferDate ? $model->getAttribute(UC::COL_TRF_DT) : null;
		// 	$expirationDate = $hasExpDate ? $model->getAttribute(DC::COL_EXP_DT) : null;
		// 	$submittedAt = $hasSubmittedAt ? $model->getAttribute(PJC::COL_SBM_AT) : null;
		// 	$rejectedAt = $hasRejectedAt ? $model->getAttribute(PJC::COL_REJ_AT) : null;
		// 	$approvedAt = $hasApprovedAt ? $model->getAttribute(PJC::COL_APV_AT) : null;
		// 	$invitedAt = $hasInvitedAt ? $model->getAttribute(PJC::COL_INV_AT) : null;
		// 	$sentAt = $hasSentAt ? $model->getAttribute(MC::COL_SNT_AT) : null;
		// 	$readAt = $hasReadAt ? $model->getAttribute(MC::COL_RD_AT) : null;
		// 	$appliedAt = $hasAppliedAt ? $model->getAttribute(AC::COL_APL_AT) : null;
		// 	$lastReviewAt = $hasLastReviewAt ? $model->getAttribute(AC::COL_LST_RVW_AT) : null;
		// 	$joinedAt = $hasJoinedAt ? $model->getAttribute(PJC::COL_JND_AT) : null;
		// 	$leftAt = $hasLeftAt ? $model->getAttribute(PJC::COL_LFT_AT) : null;
		// 	$expiredAt = $hasExpiredAt ? $model->getAttribute(PJC::COL_EXP_AT) : null;
		// 	$closedAt = $hasClosedAt ? $model->getAttribute(SPC::COL_CLSD_AT) : null;

		// 	try {
		// 		$startDateObj = static::normalizeDate($startDate);
		// 		$endDateObj = static::normalizeDate($endDate);
		// 		$dueDateObj = static::normalizeDate($dueDate);
		// 		$jngDateObj = static::normalizeDate($jngDate);
		// 		$fndDateObj = static::normalizeDate($fndDate);
		// 		$billDateObj = static::normalizeDate($billDate);
		// 		$sendDateObj = static::normalizeDate($sendDate);
		// 		$issueDateObj = static::normalizeDate($issueDate);
		// 		$reconcileDateObj = static::normalizeDate($reconcileDate);
		// 		$terminationDateObj = static::normalizeDate($terminationDate);
		// 		$noticeDateObj = static::normalizeDate($noticeDate);
		// 		$resignationDateObj = static::normalizeDate($resignationDate);
		// 		$promotionDateObj = static::normalizeDate($promotionDate);
		// 		$transferDateObj = static::normalizeDate($transferDate);
		// 		$expirationDateObj = static::normalizeDate($expirationDate);
		// 		$submittedAtObj = static::normalizeDate($submittedAt);
		// 		$rejectedAtObj = static::normalizeDate($rejectedAt);
		// 		$approvedAtObj = static::normalizeDate($approvedAt);
		// 		$invitedAtObj = static::normalizeDate($invitedAt);
		// 		$sentAtObj = static::normalizeDate($sentAt);
		// 		$readAtObj = static::normalizeDate($readAt);
		// 		$appliedAtObj = static::normalizeDate($appliedAt);
		// 		$lastReviewAtObj = static::normalizeDate($lastReviewAt);
		// 		$joinedAtObj = static::normalizeDate($joinedAt);
		// 		$leftAtObj = static::normalizeDate($leftAt);
		// 		$expiredAtObj = static::normalizeDate($expiredAt);
		// 		$closedAtObj = static::normalizeDate($closedAt);

		// 		if ($startDateObj && $endDateObj) {
		// 			if ($endDateObj->lt($startDateObj)) {
		// 				$model->setAttribute(PJC::COL_E_DT, static::denormalizeDate($startDateObj, $endDate, $tableName, PJC::COL_E_DT));
		// 				$endDateObj = clone $startDateObj;
		// 			} elseif ($startDateObj->gt($endDateObj)) {
		// 				$model->setAttribute(PJC::COL_S_DT, static::denormalizeDate($endDateObj, $startDate, $tableName, PJC::COL_S_DT));
		// 				$startDateObj = clone $endDateObj;
		// 			}
		// 		}

		// 		if ($startDateObj && $expirationDateObj) {
		// 			if ($expirationDateObj->lt($startDateObj)) {
		// 				$model->setAttribute(DC::COL_EXP_DT, static::denormalizeDate($startDateObj, $expirationDate, $tableName, DC::COL_EXP_DT));
		// 				$expirationDateObj = clone $startDateObj;
		// 			} elseif ($startDateObj->gt($expirationDateObj)) {
		// 				$model->setAttribute(PJC::COL_S_DT, static::denormalizeDate($expirationDateObj, $startDate, $tableName, PJC::COL_S_DT));
		// 				$startDateObj = clone $expirationDateObj;
		// 			}
		// 		}

		// 		if ($endDateObj && $expirationDateObj) {
		// 			if ($expirationDateObj->gt($endDateObj)) {
		// 				$model->setAttribute(DC::COL_EXP_DT, static::denormalizeDate($endDateObj, $expirationDate, $tableName, DC::COL_EXP_DT));
		// 				$expirationDateObj = clone $endDateObj;
		// 			} elseif ($endDateObj->lt($expirationDateObj)) {
		// 				$model->setAttribute(PJC::COL_E_DT, static::denormalizeDate($expirationDateObj, $endDate, $tableName, PJC::COL_E_DT));
		// 				$endDateObj = clone $expirationDateObj;
		// 			}
		// 		}

		// 		if ($dueDateObj && $startDateObj) {
		// 			if ($dueDateObj->lt($startDateObj)) {
		// 				$model->setAttribute(PJC::COL_D_DATE, static::denormalizeDate($startDateObj, $dueDate, $tableName, PJC::COL_D_DATE));
		// 				$dueDateObj = clone $startDateObj;
		// 			} elseif ($startDateObj->gt($dueDateObj)) {
		// 				$model->setAttribute(PJC::COL_S_DT, static::denormalizeDate($dueDateObj, $startDate, $tableName, PJC::COL_S_DT));
		// 				$startDateObj = clone $dueDateObj;
		// 			}
		// 		}

		// 		if ($dueDateObj && $endDateObj) {
		// 			if ($dueDateObj->gt($endDateObj)) {
		// 				$model->setAttribute(PJC::COL_D_DATE, static::denormalizeDate($endDateObj, $dueDate, $tableName, PJC::COL_D_DATE));
		// 				$dueDateObj = clone $endDateObj;
		// 			} elseif ($endDateObj->lt($dueDateObj)) {
		// 				$model->setAttribute(PJC::COL_E_DT, static::denormalizeDate($dueDateObj, $endDate, $tableName, PJC::COL_E_DT));
		// 				$endDateObj = clone $dueDateObj;
		// 			}
		// 		}

		// 		if ($hasReconcileDate && $reconcileDateObj) {
		// 			$earliestBillDate = null;
		// 			if ($billDateObj)
		// 				$earliestBillDate = $earliestBillDate === null ? $billDateObj : ($billDateObj->lt($earliestBillDate) ? $billDateObj : $earliestBillDate);
		// 			if ($sendDateObj)
		// 				$earliestBillDate = $earliestBillDate === null ? $sendDateObj : ($sendDateObj->lt($earliestBillDate) ? $sendDateObj : $earliestBillDate);
		// 			if ($issueDateObj)
		// 				$earliestBillDate = $earliestBillDate === null ? $issueDateObj : ($issueDateObj->lt($earliestBillDate) ? $issueDateObj : $earliestBillDate);
		// 			if ($earliestBillDate !== null && $reconcileDateObj->gt($earliestBillDate)) {
		// 				$model->setAttribute(BC::COL_RCC_DT, static::denormalizeDate($earliestBillDate, $reconcileDate, $tableName, BC::COL_RCC_DT));
		// 				$reconcileDateObj = clone $earliestBillDate;
		// 			}
		// 		}

		// 		$earliestEmploymentDate = null;
		// 		if ($jngDateObj && $fndDateObj)
		// 			$earliestEmploymentDate = $jngDateObj->lt($fndDateObj) ? $jngDateObj : $fndDateObj;
		// 		elseif ($jngDateObj)
		// 			$earliestEmploymentDate = $jngDateObj;
		// 		elseif ($fndDateObj)
		// 			$earliestEmploymentDate = $fndDateObj;

		// 		if ($hasTerminationDate && $terminationDateObj && $earliestEmploymentDate) {
		// 			if ($terminationDateObj->lt($earliestEmploymentDate)) {
		// 				$model->setAttribute(UC::COL_TERMINATION_DT, static::denormalizeDate($earliestEmploymentDate, $terminationDate, $tableName, UC::COL_TERMINATION_DT));
		// 				$terminationDateObj = clone $earliestEmploymentDate;
		// 			}
		// 		}

		// 		if ($hasPromotionDate && $promotionDateObj && $earliestEmploymentDate) {
		// 			if ($promotionDateObj->lt($earliestEmploymentDate)) {
		// 				$model->setAttribute(UC::COL_PRMT_DT, static::denormalizeDate($earliestEmploymentDate, $promotionDate, $tableName, UC::COL_PRMT_DT));
		// 				$promotionDateObj = clone $earliestEmploymentDate;
		// 			}
		// 		}

		// 		if ($hasTransferDate && $transferDateObj && $earliestEmploymentDate) {
		// 			if ($transferDateObj->lt($earliestEmploymentDate)) {
		// 				$model->setAttribute(UC::COL_TRF_DT, static::denormalizeDate($earliestEmploymentDate, $transferDate, $tableName, UC::COL_TRF_DT));
		// 				$transferDateObj = clone $earliestEmploymentDate;
		// 			}
		// 		}

		// 		if ($hasNoticeDate && $noticeDateObj && $terminationDateObj) {
		// 			if ($noticeDateObj->gt($terminationDateObj)) {
		// 				$model->setAttribute(UC::COL_TERMINATION_NDT, static::denormalizeDate($terminationDateObj, $noticeDate, $tableName, UC::COL_TERMINATION_NDT));
		// 				$noticeDateObj = clone $terminationDateObj;
		// 			}
		// 		}

		// 		if ($hasResignationDate && $resignationDateObj && $earliestEmploymentDate) {
		// 			if ($resignationDateObj->lt($earliestEmploymentDate)) {
		// 				$model->setAttribute(UC::COL_RESIGNATION_DT, static::denormalizeDate($earliestEmploymentDate, $resignationDate, $tableName, UC::COL_RESIGNATION_DT));
		// 				$resignationDateObj = clone $earliestEmploymentDate;
		// 			}
		// 		}

		// 		if ($jngDateObj && $fndDateObj) {
		// 			if ($jngDateObj->lt($fndDateObj)) {
		// 				$model->setAttribute(AC::COL_JNG_DT, static::denormalizeDate($fndDateObj, $jngDate, $tableName, AC::COL_JNG_DT));
		// 				$jngDateObj = clone $fndDateObj;
		// 			}
		// 		}

		// 		if ($hasRejectedAt && $rejectedAtObj) {
		// 			if ($submittedAtObj && $rejectedAtObj->lt($submittedAtObj)) {
		// 				$model->setAttribute(PJC::COL_REJ_AT, static::denormalizeDate($submittedAtObj, $rejectedAt, $tableName, PJC::COL_REJ_AT));
		// 				$rejectedAtObj = clone $submittedAtObj;
		// 			}
		// 			if ($appliedAtObj && $rejectedAtObj->lt($appliedAtObj)) {
		// 				$model->setAttribute(PJC::COL_REJ_AT, static::denormalizeDate($appliedAtObj, $rejectedAt, $tableName, PJC::COL_REJ_AT));
		// 				$rejectedAtObj = clone $appliedAtObj;
		// 			}
		// 			if ($lastReviewAtObj && $rejectedAtObj->lt($lastReviewAtObj)) {
		// 				$model->setAttribute(PJC::COL_REJ_AT, static::denormalizeDate($lastReviewAtObj, $rejectedAt, $tableName, PJC::COL_REJ_AT));
		// 				$rejectedAtObj = clone $lastReviewAtObj;
		// 			}
		// 		}

		// 		if ($hasApprovedAt && $approvedAtObj) {
		// 			if ($submittedAtObj && $approvedAtObj->lt($submittedAtObj)) {
		// 				$model->setAttribute(PJC::COL_APV_AT, static::denormalizeDate($submittedAtObj, $approvedAt, $tableName, PJC::COL_APV_AT));
		// 				$approvedAtObj = clone $submittedAtObj;
		// 			}
		// 			if ($appliedAtObj && $approvedAtObj->lt($appliedAtObj)) {
		// 				$model->setAttribute(PJC::COL_APV_AT, static::denormalizeDate($appliedAtObj, $approvedAt, $tableName, PJC::COL_APV_AT));
		// 				$approvedAtObj = clone $appliedAtObj;
		// 			}
		// 			if ($lastReviewAtObj && $approvedAtObj->lt($lastReviewAtObj)) {
		// 				$model->setAttribute(PJC::COL_APV_AT, static::denormalizeDate($lastReviewAtObj, $approvedAt, $tableName, PJC::COL_APV_AT));
		// 				$approvedAtObj = clone $lastReviewAtObj;
		// 			}
		// 		}

		// 		if ($hasInvitedAt && $invitedAtObj) {
		// 			if ($jngDateObj && $invitedAtObj->gt($jngDateObj)) {
		// 				$model->setAttribute(PJC::COL_INV_AT, static::denormalizeDate($jngDateObj, $invitedAt, $tableName, PJC::COL_INV_AT));
		// 				$invitedAtObj = clone $jngDateObj;
		// 			}
		// 			if ($approvedAtObj && $invitedAtObj->gt($approvedAtObj)) {
		// 				$model->setAttribute(PJC::COL_INV_AT, static::denormalizeDate($approvedAtObj, $invitedAt, $tableName, PJC::COL_INV_AT));
		// 				$invitedAtObj = clone $approvedAtObj;
		// 			}
		// 		}

		// 		if ($hasReadAt && $readAtObj) {
		// 			if ($sentAtObj && $readAtObj->lt($sentAtObj)) {
		// 				$model->setAttribute(MC::COL_RD_AT, static::denormalizeDate($sentAtObj, $readAt, $tableName, MC::COL_RD_AT));
		// 				$readAtObj = clone $sentAtObj;
		// 			}
		// 			if ($sendDateObj && $readAtObj->lt($sendDateObj)) {
		// 				$model->setAttribute(MC::COL_RD_AT, static::denormalizeDate($sendDateObj, $readAt, $tableName, MC::COL_RD_AT));
		// 				$readAtObj = clone $sendDateObj;
		// 			}
		// 		}

		// 		if ($hasAppliedAt && $appliedAtObj && $sentAtObj) {
		// 			if ($appliedAtObj->lt($sentAtObj)) {
		// 				$model->setAttribute(AC::COL_APL_AT, static::denormalizeDate($sentAtObj, $appliedAt, $tableName, AC::COL_APL_AT));
		// 				$appliedAtObj = clone $sentAtObj;
		// 			}
		// 		}

		// 		if ($hasLastReviewAt && $lastReviewAtObj) {
		// 			if ($readAtObj && $lastReviewAtObj->lt($readAtObj)) {
		// 				$model->setAttribute(AC::COL_LST_RVW_AT, static::denormalizeDate($readAtObj, $lastReviewAt, $tableName, AC::COL_LST_RVW_AT));
		// 				$lastReviewAtObj = clone $readAtObj;
		// 			}
		// 			if ($sentAtObj && $lastReviewAtObj->lt($sentAtObj)) {
		// 				$model->setAttribute(AC::COL_LST_RVW_AT, static::denormalizeDate($sentAtObj, $lastReviewAt, $tableName, AC::COL_LST_RVW_AT));
		// 				$lastReviewAtObj = clone $sentAtObj;
		// 			}
		// 			if ($appliedAtObj && $lastReviewAtObj->lt($appliedAtObj)) {
		// 				$model->setAttribute(AC::COL_LST_RVW_AT, static::denormalizeDate($appliedAtObj, $lastReviewAt, $tableName, AC::COL_LST_RVW_AT));
		// 				$lastReviewAtObj = clone $appliedAtObj;
		// 			}
		// 		}

		// 		if ($hasJoinedAt && $joinedAtObj) {
		// 			if ($invitedAtObj && $joinedAtObj->lt($invitedAtObj)) {
		// 				$model->setAttribute(PJC::COL_JND_AT, static::denormalizeDate($invitedAtObj, $joinedAt, $tableName, PJC::COL_JND_AT));
		// 				$joinedAtObj = clone $invitedAtObj;
		// 			}
		// 			if ($jngDateObj && $joinedAtObj->lt($invitedAtObj) && $invitedAtObj) {
		// 				$model->setAttribute(AC::COL_JNG_DT, static::denormalizeDate($invitedAtObj, $jngDate, $tableName, AC::COL_JNG_DT));
		// 				$jngDateObj = clone $invitedAtObj;
		// 			}
		// 		}

		// 		if ($hasLeftAt && $leftAtObj) {
		// 			if ($sentAtObj && $leftAtObj->lt($sentAtObj)) {
		// 				$model->setAttribute(PJC::COL_LFT_AT, static::denormalizeDate($sentAtObj, $leftAt, $tableName, PJC::COL_LFT_AT));
		// 				$leftAtObj = clone $sentAtObj;
		// 			}
		// 			if ($invitedAtObj && $leftAtObj->lt($invitedAtObj)) {
		// 				$model->setAttribute(PJC::COL_LFT_AT, static::denormalizeDate($invitedAtObj, $leftAt, $tableName, PJC::COL_LFT_AT));
		// 				$leftAtObj = clone $invitedAtObj;
		// 			}
		// 			if ($joinedAtObj && $leftAtObj->lt($joinedAtObj)) {
		// 				$model->setAttribute(PJC::COL_LFT_AT, static::denormalizeDate($joinedAtObj, $leftAt, $tableName, PJC::COL_LFT_AT));
		// 				$leftAtObj = clone $joinedAtObj;
		// 			}
		// 		}

		// 		if ($hasExpiredAt && $expiredAtObj) {
		// 			if ($sentAtObj && $expiredAtObj->lt($sentAtObj)) {
		// 				$model->setAttribute(PJC::COL_EXP_AT, static::denormalizeDate($sentAtObj, $expiredAt, $tableName, PJC::COL_EXP_AT));
		// 				$expiredAtObj = clone $sentAtObj;
		// 			}
		// 			if ($invitedAtObj && $expiredAtObj->lt($invitedAtObj)) {
		// 				$model->setAttribute(PJC::COL_EXP_AT, static::denormalizeDate($invitedAtObj, $expiredAt, $tableName, PJC::COL_EXP_AT));
		// 				$expiredAtObj = clone $invitedAtObj;
		// 			}
		// 			if ($joinedAtObj && $expiredAtObj->lt($joinedAtObj)) {
		// 				$model->setAttribute(PJC::COL_EXP_AT, static::denormalizeDate($joinedAtObj, $expiredAt, $tableName, PJC::COL_EXP_AT));
		// 				$expiredAtObj = clone $joinedAtObj;
		// 			}
		// 			if ($expirationDateObj && $expiredAtObj->lt($sentAtObj) && $sentAtObj) {
		// 				$model->setAttribute(DC::COL_EXP_DT, static::denormalizeDate($sentAtObj, $expirationDate, $tableName, DC::COL_EXP_DT));
		// 				$expirationDateObj = clone $sentAtObj;
		// 			}
		// 			if ($expirationDateObj && $expiredAtObj->lt($invitedAtObj) && $invitedAtObj) {
		// 				$model->setAttribute(DC::COL_EXP_DT, static::denormalizeDate($invitedAtObj, $expirationDate, $tableName, DC::COL_EXP_DT));
		// 				$expirationDateObj = clone $invitedAtObj;
		// 			}
		// 			if ($expirationDateObj && $expiredAtObj->lt($joinedAtObj) && $joinedAtObj) {
		// 				$model->setAttribute(DC::COL_EXP_DT, static::denormalizeDate($joinedAtObj, $expirationDate, $tableName, DC::COL_EXP_DT));
		// 				$expirationDateObj = clone $joinedAtObj;
		// 			}
		// 		}

		// 		if ($hasClosedAt && $closedAtObj) {
		// 			if ($fndDateObj && $closedAtObj->lt($fndDateObj)) {
		// 				$model->setAttribute(SPC::COL_CLSD_AT, static::denormalizeDate($fndDateObj, $closedAt, $tableName, SPC::COL_CLSD_AT));
		// 				$closedAtObj = clone $fndDateObj;
		// 			}
		// 			if ($startDateObj && $closedAtObj->lt($startDateObj)) {
		// 				$model->setAttribute(SPC::COL_CLSD_AT, static::denormalizeDate($startDateObj, $closedAt, $tableName, SPC::COL_CLSD_AT));
		// 				$closedAtObj = clone $startDateObj;
		// 			}
		// 			if ($expirationDateObj && $closedAtObj->gt($expirationDateObj)) {
		// 				$model->setAttribute(SPC::COL_CLSD_AT, static::denormalizeDate($expirationDateObj, $closedAt, $tableName, SPC::COL_CLSD_AT));
		// 				$closedAtObj = clone $expirationDateObj;
		// 			}
		// 			if ($endDateObj && $closedAtObj->gt($endDateObj)) {
		// 				$model->setAttribute(SPC::COL_CLSD_AT, static::denormalizeDate($endDateObj, $closedAt, $tableName, SPC::COL_CLSD_AT));
		// 				$closedAtObj = clone $endDateObj;
		// 			}
		// 		}
		// 	} catch (\Exception $e) {
		// 		Log::warning("Failed to validate date relationships", [
		// 			'table' => $tableName,
		// 			'error' => $e->getMessage(),
		// 			'file' => $e->getFile(),
		// 			'line' => $e->getLine(),
		// 			'trace' => $e->getTraceAsString(),
		// 			'trait' => __TRAIT__,
		// 			'class' => static::class,
		// 		]);
		// 		return;
		// 	}
		// });
	}

	protected static function normalizeDate($value): ?\Carbon\Carbon
	{
		if ($value === null)
			return null;
		if ($value instanceof \Carbon\Carbon)
			return $value;
		try {
			return \Carbon\Carbon::parse($value);
		} catch (\Exception $e) {
			return null;
		}
	}

	protected static function denormalizeDate(\Carbon\Carbon $carbonDate, $originalValue, string $tableName, string $columnName)
	{
		if ($originalValue === null)
			return null;
		try {
			$columnType = strtolower(Schema::getColumnType($tableName, $columnName));
			if ($columnType === 'date')
				return $carbonDate->format('Y-m-d');
			if (in_array($columnType, ['datetime', 'timestamp']))
				return $carbonDate->format('Y-m-d H:i:s');
			return $carbonDate;
		} catch (\Throwable $e) {
			if (str_contains($e->getMessage(), 'enum'))
				Log::debug("Skipped enum column: {$tableName}.{$columnName}");
			return $carbonDate->format('Y-m-d H:i:s');
		}
	}
	public function getDates(): array
	{
		if (!$this instanceof Model)
			return [];
		$tableName = $this->getTable();
		if (empty($tableName) || !is_string($tableName))
			return [];
		$dates = [];
		if (method_exists($this, 'getDates') && is_callable('parent::getDates')) {
			try {
				$parentDates = parent::getDates();
				is_array($parentDates) && $dates = array_merge($dates, $parentDates);
			} catch (\Throwable $e) {
				Log::notice("Failed to fetch parent dates for model", [
					'table' => $tableName,
					'error' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trait' => __TRAIT__,
					'class' => static::class,
				]);
			}
		}
		try {
			foreach (static::DATE_COLUMNS as $dateColumn) {
				try {
					if (Schema::hasColumn($tableName, $dateColumn) && !in_array($dateColumn, $dates, true))
						$dates[] = $dateColumn;
				} catch (\Throwable $e) {
					if (str_contains($e->getMessage(), 'enum'))
						Log::debug("Skipped enum column: {$tableName}.{$dateColumn}");
					continue;
				}
			}

			try {
				if (Schema::hasColumn($tableName, 'from')) {
					$fromType = strtolower(Schema::getColumnType($tableName, 'from'));
					if (in_array($fromType, ['date', 'timestamp', 'datetime']) && !in_array('from', $dates, true))
						$dates[] = 'from';
				}
			} catch (\Throwable $e) {
				if (str_contains($e->getMessage(), 'enum'))
					Log::debug("Skipped enum column: {$tableName}.from");
			}

			try {
				if (Schema::hasColumn($tableName, 'to')) {
					$toType = strtolower(Schema::getColumnType($tableName, 'to'));
					if (in_array($toType, ['date', 'timestamp', 'datetime']) && !in_array('to', $dates, true))
						$dates[] = 'to';
				}
			} catch (\Throwable $e) {
				if (str_contains($e->getMessage(), 'enum'))
					Log::debug("Skipped enum column: {$tableName}.to");
			}

			$dates = array_values(array_unique($dates));
		} catch (\Throwable $e) {
			Log::warning("Failed to fetch date columns for model", [
				'table' => $tableName,
				'error' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trait' => __TRAIT__,
				'class' => static::class,
			]);
		}
		return $dates;
	}
}
