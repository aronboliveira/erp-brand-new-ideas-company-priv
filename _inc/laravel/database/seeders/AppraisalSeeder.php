<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\EvaluationStatus;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class AppraisalSeeder extends Seeder
{
	public function run(): void
	{
		$this->seedAppraisals();
	}

	protected function seedAppraisals(): void
	{
		try {
			if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasTable(DC::TABLE_EMPLOYEES)) {
				Log::warning(static::class . ' skipping: users or employees table missing');
				return;
			}
			$output = new \Symfony\Component\Console\Output\ConsoleOutput();
			$users = DB::table(DC::TABLE_USERS)
				->select('id', UC::COL_TP, UC::COL_EMP_ID, UC::COL_NM)
				->get();

			if ($users->isEmpty()) {
				Log::warning(static::class . ' skipping: no users found');
				return;
			}

			$employees = DB::table(DC::TABLE_EMPLOYEES)
				->select('id', 'user_id', 'branch_id', 'department_id', 'name')
				->get();

			if ($employees->isEmpty()) {
				Log::warning(static::class . ' skipping: no employees found');
				return;
			}

			$statuses = EvaluationStatus::cases();
			if (empty($statuses)) {
				Log::warning(static::class . ' skipping: no EvaluationStatus cases found');
				return;
			}

			$employeesByBranch      = [];
			$employeesByBranchDept  = [];
			$employeesArray         = $employees->all();

			foreach ($employees as $emp) {
				$branchId = $emp->branch_id ?? null;
				$deptId   = $emp->department_id ?? null;

				if ($branchId !== null) {
					$employeesByBranch[$branchId] ??= [];
					$employeesByBranch[$branchId][] = $emp;

					if ($deptId !== null) {
						$employeesByBranchDept[$branchId] ??= [];
						$employeesByBranchDept[$branchId][$deptId] ??= [];
						$employeesByBranchDept[$branchId][$deptId][] = $emp;
					}
				}
			}

			$customers = $users->where(UC::COL_TP, 'customer')->values();
			$adminsAll = $users->where(UC::COL_TP, 'admin')->values();
			$hrsAll    = $users->where(UC::COL_TP, 'hr')->values();

			$adminSample = $this->sampleUsersByFactor($adminsAll, 0.10);
			$hrSample    = $this->sampleUsersByFactor($hrsAll, 0.50);

			$appraisers = collect()
				->merge($customers)
				->merge($adminSample)
				->merge($hrSample)
				->unique('id')
				->values();

			if ($appraisers->isEmpty()) {
				Log::warning(static::class . ' skipping: no appraisers found');
				return;
			}

			$companyUsers = $users->where(UC::COL_TP, 'company')->pluck('id')->values()->all();

			$appraiserContexts = [];
			foreach ($appraisers as $appraiser) {
				$appraiserContexts[(string) $appraiser->id] = $this->buildAppraiserContext(
					$appraiser,
					$employees,
					$employeesByBranch,
					$employeesByBranchDept,
					$employeesArray
				);
			}

			$baseN       = max(count($statuses) * $appraisers->count(), 1);
			$targetTotal = $this->resolveTargetTotal($baseN);

			$HARD_CAP = 2; // Hard cap to prevent excessive record creation
			$created = 0;
			$buffer  = [];

			foreach ($statuses as $statusEnum) {
				foreach ($appraisers as $appraiser) {
					if ($created >= $HARD_CAP) break 2; // Hard cap guard
					// if ($created >= $targetTotal) break 2;

					$appraiserId = (string) $appraiser->id;
					$type        = (string) ($appraiser->{UC::COL_TP} ?? '');

					$context = $appraiserContexts[$appraiserId] ?? null;
					if ($context === null || empty($context['employees_pool'])) {
						continue;
					}

					$pool = $context['employees_pool'];
					$poolCount = count($pool);
					if ($poolCount === 0) {
						continue;
					}

					$perCombo = max(1, (int) floor($poolCount / 4));
					$perCombo = min($perCombo, 32, $poolCount);

					$indexes = array_rand($pool, $perCombo);
					if (!is_array($indexes)) {
						$indexes = [$indexes];
					}

					foreach ($indexes as $idx) {
						if ($created >= $HARD_CAP) break 3; // Hard cap guard
						// if ($created >= $targetTotal) break 3;

						/** @var object $emp */
						$emp = $pool[$idx];

						$companyId = null;
						if (!empty($companyUsers)) {
							$companyId = (string) $companyUsers[array_rand($companyUsers)];
						}

						$scores = $this->randomScoreSetForStatus($statusEnum);
						$date   = $this->randomAppraisalDate();

						$acknowledgers = $this->buildAcknowledgersPayload(
							$statusEnum,
							$appraiserId,
							$appraiser,
							$emp
						);

						$metadata = $this->buildMetadataPayload(
							$statusEnum,
							$appraiserId,
							$type,
							$emp
						);

						$buffer[] = [
							'id'                  => (string) Str::uuid(),
							'company'             => $companyId,
							'branch'              => $emp->branch_id ?? null,
							'employee'            => $emp->id ?? null,

							'rating'              => $scores['rating_label'],
							'attendance'          => $scores['attendance'],
							'administration'      => $scores['administration'],
							PJC::COL_CST_EXP      => $scores['customer_experience'],
							'integrity'           => $scores['integrity'],
							'marketing'           => $scores['marketing'],
							'professionalism'     => $scores['professionalism'],

							PJC::COL_APR_DT       => $date,
							'status'              => $statusEnum->value,
							'remark'              => $scores['remark'],
							'appraiser'           => $appraiserId,

							'acknowledgers'       => $acknowledgers === []
								? json_encode([], JSON_UNESCAPED_UNICODE)
								: json_encode($acknowledgers, JSON_UNESCAPED_UNICODE),

							'metadata'            => $metadata === []
								? json_encode([], JSON_UNESCAPED_UNICODE)
								: json_encode($metadata, JSON_UNESCAPED_UNICODE),

							DC::COL_TABLE_CREATOR => $appraiserId,
							DC::COL_TABLE_UPDATER => $appraiserId,
							DC::COL_C_AT          => now(),
							DC::COL_U_AT          => now(),
						];

						// $output->writeln('Prepared appraisal for employee ' . ($emp->name ?? $emp->id ?? 'unknown') . ' by appraiser ' . ($appraiser->{UC::COL_NM} ?? $appraiserId) . ' with status ' . $statusEnum->value);

						$created++;

						if (count($buffer) >= 500) {
							DB::table(DC::TABLE_APR)->insert($buffer);
							$buffer = [];
						}
					}
				}
			}

			if (!empty($buffer)) {
				DB::table(DC::TABLE_APR)->insert($buffer);
			}

			Log::info(static::class . ' seeded appraisals', [
				'created'     => $created,
				'target'      => $targetTotal,
				'appraisers'  => $appraisers->count(),
				'statuses'    => count($statuses),
			]);
		} catch (\Throwable $e) {
			Log::error(static::class . ' failed seeding appraisals', [
				'error' => $e->getMessage(),
			]);
		}
	}

	protected function resolveTargetTotal(int $baseN): int
	{
		$minTotal = max(64 * $baseN, $baseN);

		/** @var Command|null $command */
		$command = $this->command instanceof Command ? $this->command : null;

		if ($command && $command->hasOption('count')) {
			$value = (int) $command->option('count');
			if ($value > 0) {
				return max($value, $baseN);
			}
		}

		return $minTotal;
	}

	protected function sampleUsersByFactor($collection, float $factor)
	{
		$factor = $factor <= 0 ? 0.0 : ($factor > 1 ? 1.0 : $factor);

		$count = $collection->count();
		if ($count === 0 || $factor === 0.0) {
			return collect();
		}

		$take = (int) floor($count * $factor);
		if ($take <= 0) {
			$take = 1;
		}

		if ($take >= $count) {
			return $collection;
		}

		return $collection->random($take);
	}

	protected function buildAppraiserContext(
		object $appraiser,
		$employees,
		array $employeesByBranch,
		array $employeesByBranchDept,
		array $employeesArray
	): array {
		$type        = (string) ($appraiser->{UC::COL_TP} ?? '');
		$appraiserId = (string) $appraiser->id;

		$branchId = null;
		$deptId   = null;
		$empId    = null;

		if (Schema::hasColumn(DC::TABLE_USERS, UC::COL_EMP_ID)) {
			$empId = $appraiser->{UC::COL_EMP_ID} ?? null;
		}

		$empRecord = null;

		if (!empty($empId)) {
			$empRecord = $employees
				->first(fn($e) => (string) $e->id === (string) $empId);
		}

		if ($empRecord === null) {
			$empRecord = $employees
				->first(fn($e) => (string) $e->user_id === $appraiserId);
		}

		if ($empRecord !== null) {
			$branchId = $empRecord->branch_id ?? null;
			$deptId   = $empRecord->department_id ?? null;
		}

		$pool = [];

		if ($type === 'hr' && $branchId !== null && isset($employeesByBranch[$branchId])) {
			$branchEmployees = $employeesByBranch[$branchId];
			$count           = count($branchEmployees);
			$take            = (int) floor($count * 0.25);
			if ($take <= 0) $take = min(1, $count);
			if ($take > 0) {
				$indexes = array_rand($branchEmployees, $take);
				if (!is_array($indexes)) {
					$indexes = [$indexes];
				}
				foreach ($indexes as $idx) {
					$pool[] = $branchEmployees[$idx];
				}
			}
		} elseif ($type === 'admin' && $branchId !== null && isset($employeesByBranch[$branchId])) {
			$sameDept = [];
			$other    = [];

			if ($deptId !== null && isset($employeesByBranchDept[$branchId][$deptId])) {
				$sameDept = $employeesByBranchDept[$branchId][$deptId];
			}

			foreach ($employeesByBranch[$branchId] as $emp) {
				$empDept = $emp->department_id ?? null;
				if ($deptId === null || $empDept === null || $empDept === $deptId) {
					continue;
				}
				$other[] = $emp;
			}

			foreach ($sameDept as $emp) {
				$pool[] = $emp;
			}

			$oc = count($other);
			if ($oc > 0) {
				$take = (int) floor($oc * 0.10);
				if ($take <= 0) $take = 1;
				$take = min($take, $oc);
				$indexes = array_rand($other, $take);
				if (!is_array($indexes)) {
					$indexes = [$indexes];
				}
				foreach ($indexes as $idx) {
					$pool[] = $other[$idx];
				}
			}
		} else {
			$totalEmployees = count($employeesArray);
			if ($totalEmployees > 0) {
				$take = min(16, $totalEmployees);
				$indexes = array_rand($employeesArray, $take);
				if (!is_array($indexes)) {
					$indexes = [$indexes];
				}
				foreach ($indexes as $idx) {
					$pool[] = $employeesArray[$idx];
				}
			}
		}

		$unique = [];
		$seen   = [];

		foreach ($pool as $emp) {
			$eid = (string) ($emp->id ?? '');
			if ($eid === '') {
				continue;
			}
			if (isset($seen[$eid])) {
				continue;
			}
			$seen[$eid] = true;
			$unique[]   = $emp;
		}

		return [
			'type'           => $type,
			'branch_id'      => $branchId,
			'department_id'  => $deptId,
			'employee_id'    => $empId,
			'employees_pool' => $unique,
		];
	}

	protected function randomScoreSetForStatus(EvaluationStatus $status): array
	{
		$base = match ($status) {
			EvaluationStatus::Draft,
			EvaluationStatus::Pending,
			EvaluationStatus::NotStarted,
			EvaluationStatus::Undefined => 5,
			EvaluationStatus::Active,
			EvaluationStatus::InProgress => 7,
			EvaluationStatus::Completed,
			EvaluationStatus::Accept => 8,
			EvaluationStatus::Suspended,
			EvaluationStatus::Cancelled,
			EvaluationStatus::Decline,
			EvaluationStatus::Expired,
			EvaluationStatus::Archived => 6,
		};

		$scores = [];
		foreach (
			[
				'attendance',
				'administration',
				'customer_experience',
				'integrity',
				'marketing',
				'professionalism',
			] as $field
		) {
			$value = $base + random_int(-3, 3);
			if ($value < 0)  $value = 0;
			if ($value > 10) $value = 10;
			$scores[$field] = $value;
		}

		$ratingLabel = match ($status) {
			EvaluationStatus::Draft       => 'Draft appraisal',
			EvaluationStatus::Pending     => 'Pending appraisal',
			EvaluationStatus::Active,
			EvaluationStatus::InProgress  => 'Ongoing performance review',
			EvaluationStatus::Completed,
			EvaluationStatus::Accept      => 'Completed appraisal',
			EvaluationStatus::Decline     => 'Declined appraisal',
			EvaluationStatus::Suspended   => 'Suspended appraisal',
			EvaluationStatus::Cancelled   => 'Cancelled appraisal',
			EvaluationStatus::Expired     => 'Expired appraisal',
			EvaluationStatus::Archived    => 'Archived appraisal',
			EvaluationStatus::NotStarted  => 'Not started appraisal',
			EvaluationStatus::Undefined   => 'Undefined appraisal',
		};

		$remark = match ($status) {
			EvaluationStatus::Completed,
			EvaluationStatus::Accept      => 'Cycle closed with consolidated feedback.',
			EvaluationStatus::Decline     => 'Appraisal declined and requires follow-up.',
			EvaluationStatus::Suspended   => 'Appraisal temporarily on hold.',
			EvaluationStatus::Cancelled   => 'Appraisal cancelled by management.',
			default                       => 'Automatically generated appraisal for testing.',
		};

		return [
			'rating_label'        => $ratingLabel,
			'attendance'          => $scores['attendance'],
			'administration'      => $scores['administration'],
			'customer_experience' => $scores['customer_experience'],
			'integrity'           => $scores['integrity'],
			'marketing'           => $scores['marketing'],
			'professionalism'     => $scores['professionalism'],
			'remark'              => $remark,
		];
	}

	protected function randomAppraisalDate(): string
	{
		$days = random_int(0, 730);

		return now()
			->subDays($days)
			->format('Y-m-d');
	}

	protected function buildAcknowledgersPayload(
		EvaluationStatus $status,
		string $appraiserId,
		object $appraiser,
		object $employee
	): array {
		if (in_array($status, [
			EvaluationStatus::Draft,
			EvaluationStatus::Pending,
			EvaluationStatus::NotStarted,
			EvaluationStatus::Undefined,
		], true)) {
			return [];
		}

		$ack = [];

		$step = match ($status) {
			EvaluationStatus::Completed,
			EvaluationStatus::Accept      => 'final',
			EvaluationStatus::Decline     => 'rejected',
			EvaluationStatus::Suspended   => 'suspended',
			EvaluationStatus::Cancelled   => 'cancelled',
			EvaluationStatus::Expired     => 'expired',
			EvaluationStatus::Archived    => 'archived',
			EvaluationStatus::Active,
			EvaluationStatus::InProgress  => 'in_progress',
			default                       => 'general',
		};

		$ack[] = [
			'user_id'         => $appraiserId,
			'employee_id'     => null,
			'name'            => property_exists($appraiser, UC::COL_NM)
				? (string) $appraiser->{UC::COL_NM}
				: null,
			'acknowledged_at' => now()->format('Y-m-d H:i:s'),
			'step'            => $step,
		];

		$employeeId = (string) ($employee->id ?? '');
		if ($employeeId !== '' && random_int(0, 100) < 60) {
			$ack[] = [
				'user_id'         => null,
				'employee_id'     => $employeeId,
				'name'            => property_exists($employee, 'name')
					? (string) $employee->name
					: null,
				'acknowledged_at' => now()
					->subHours(random_int(1, 72))
					->format('Y-m-d H:i:s'),
				'step'            => 'employee_ack',
			];
		}

		return $ack;
	}

	protected function buildMetadataPayload(
		EvaluationStatus $status,
		string $appraiserId,
		string $appraiserType,
		object $employee
	): array {
		$payload = [
			'appraiser_id'   => $appraiserId,
			'appraiser_type' => $appraiserType,
			'employee_id'    => (string) ($employee->id ?? ''),
			'branch_id'      => $employee->branch_id ?? null,
			'department_id'  => $employee->department_id ?? null,
			'generated_at'   => now()->format('Y-m-d H:i:s'),
		];

		$payload['category'] = match ($status) {
			EvaluationStatus::Completed,
			EvaluationStatus::Accept      => 'completed',
			EvaluationStatus::Decline     => 'rejected',
			EvaluationStatus::Cancelled   => 'cancelled',
			EvaluationStatus::Suspended   => 'suspended',
			EvaluationStatus::Expired     => 'expired',
			EvaluationStatus::Archived    => 'archived',
			EvaluationStatus::Active,
			EvaluationStatus::InProgress  => 'in_progress',
			EvaluationStatus::Draft       => 'draft',
			EvaluationStatus::Pending     => 'pending',
			EvaluationStatus::NotStarted  => 'not_started',
			EvaluationStatus::Undefined   => 'undefined',
		};

		return $payload;
	}
}
