<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Models\TaskStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskStageSeeder extends Seeder
{
	private const DEFAULT_MULTIPLIER = 2;

	// Break-out safety
	private const MAX_PICK_ATTEMPTS = 64;
	private const MAX_VERBOSE_ROWS  = 80;

	public function run(): void
	{
		$output = new ConsoleOutput();
		$io     = new SymfonyStyle(new ArrayInput([]), $output);

		$projectIds = $this->pluckIdsSafe(DC::TABLE_PROJECTS);
		if (empty($projectIds)) {
			$io->warning('No projects found; skipping TaskStageSeeder.');
			return;
		}

		$allTaskIds = $this->pluckIdsSafe(DC::TABLE_TASKS);
		$userIds    = $this->pluckIdsSafe(DC::TABLE_USERS);

		$target = $this->resolveDesiredCount(count($projectIds));
		$made   = 0;

		$priorityCounts = [];
		$statusCounts   = [];
		$mismatchCounts = 0;

		$io->title('TaskStageSeeder');
		$io->text('Projects: ' . count($projectIds));
		$io->text('Tasks pool: ' . count($allTaskIds));
		$io->text('Users pool: ' . count($userIds));
		$io->text('Target rows: ' . $target);

		$verboseRows = [];

		foreach ($projectIds as $projectId) {
			if ($made >= $target) break;

			$taskIdsForProject = $this->resolveTaskIdsForProject($projectId, $allTaskIds);

			foreach ($taskIdsForProject as $taskId) {
				if ($made >= $target) break;

				foreach (PriorityLevel::cases() as $priority) {
					if ($made >= $target) break;

					foreach (EvaluationStatus::cases() as $status) {
						if ($made >= $target) break;

						try {
							$responsible = $this->pickRandomId($userIds, self::MAX_PICK_ATTEMPTS);

							[$progress, $complete] = $this->progressAndCompleteForStatus($status);

							$stage = new TaskStage();

							$stage->setAttribute(AC::COL_PJ, $projectId);
							$stage->setAttribute('task', $taskId); // nullable ok (migration)

							$name = mb_substr(trim($priority->name . ' / ' . $status->name), 0, 254);
							$stage->setAttribute('name', $name !== '' ? $name : null);

							$stage->setAttribute('description', 'Seeded stage for reporting/testing.');

							$due = Carbon::now()
								->addDays(random_int(-14, 30))
								->setTime(random_int(8, 18), 0, 0);

							$stage->setAttribute(PJC::COL_D_DATE, $due);

							$requestedPriority = (string) $priority->value;
							$requestedStatus   = (string) $status->value;

							$stage->setAttribute('priority', $requestedPriority);
							$stage->setAttribute('status', $requestedStatus);

							$stage->setAttribute('progress', $progress);
							$stage->setAttribute('complete', $complete);

							$stage->setAttribute('color', $this->randomHexColor());
							$stage->setAttribute('order', $made);

							$stage->setAttribute('responsible', $responsible);

							$stage->setAttribute('involved', $this->buildInvolved($responsible, $userIds));

							$stage->setAttribute('metadata', [
								'seed' => true,
								'v'    => 2,
								'ts'   => Carbon::now()->toIso8601String(),
								'requested_status'   => $requestedStatus,
								'requested_priority' => $requestedPriority,
							]);

							$stage->setAttribute('attachments', []);
							$stage->setAttribute('tags', [
								'seed',
								strtolower($requestedPriority),
								strtolower($requestedStatus),
							]);
							$output->writeln('Creating TaskStage for project ' . $projectId . ', task ' . ($taskId ?? 'null') . ', priority ' . $requestedPriority . ', status ' . $requestedStatus);
							$stage->save();
							$made++;

							$priorityCounts[$requestedPriority] = ($priorityCounts[$requestedPriority] ?? 0) + 1;

							// Nota: mutator pode normalizar (ex.: in_progress -> active via enum normalize)
							$persistedStatus = (string) ($stage->getRawOriginal('status') ?? '');
							$statusCounts[$persistedStatus] = ($statusCounts[$persistedStatus] ?? 0) + 1;

							$mismatch = ($persistedStatus !== '' && $persistedStatus !== $requestedStatus);
							if ($mismatch) $mismatchCounts++;

							if (count($verboseRows) < self::MAX_VERBOSE_ROWS) {
								$verboseRows[] = [
									'i' => (string) $made,
									'project_id' => (string) $projectId,
									'task_id' => (string) ($taskId ?? ''),
									'priority(req)' => $requestedPriority,
									'status(req)' => $requestedStatus,
									'status(db)' => $persistedStatus,
									'progress' => (string) $progress,
									'complete' => $complete ? '1' : '0',
									'mismatch' => $mismatch ? 'YES' : '',
								];
							}
						} catch (\Throwable $e) {
							Log::warning(self::class . ' failed creating TaskStage', [
								'project_id' => (string) $projectId,
								'task_id'    => (string) ($taskId ?? ''),
								'error'      => $e->getMessage(),
							]);
						}
					}
				}
			}
		}

		$io->section('Sample variations (requested vs persisted)');
		if (!empty($verboseRows)) {
			$io->table(
				array_keys($verboseRows[0]),
				array_map(fn($r) => array_values($r), $verboseRows)
			);
		} else {
			$io->text('No rows created.');
		}

		$io->section('Summary');
		$io->text('Created: ' . $made);
		$io->text('Status mismatches (req != db): ' . $mismatchCounts);

		if (!empty($priorityCounts)) {
			ksort($priorityCounts);
			$io->table(['priority', 'count'], array_map(
				fn($k) => [$k, (string) $priorityCounts[$k]],
				array_keys($priorityCounts)
			));
		}

		if (!empty($statusCounts)) {
			ksort($statusCounts);
			$io->table(['status(db)', 'count'], array_map(
				fn($k) => [$k, (string) $statusCounts[$k]],
				array_keys($statusCounts)
			));
		}

		Log::info(self::class . ' done', ['created' => $made, 'target' => $target, 'mismatches' => $mismatchCounts]);
	}

	private function resolveDesiredCount(int $projectCount): int
	{
		$opt = null;

		try {
			if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count'))
				$opt = $this->command->option('count');
		} catch (\Throwable) {
		}

		$count = is_numeric($opt) ? (int) $opt : 0;
		if ($count > 0) return $count;

		return self::DEFAULT_MULTIPLIER * max(1, $projectCount);
	}

	private function pluckIdsSafe(string $table): array
	{
		try {
			if (!Schema::hasTable($table))
				return [];

			return DB::table($table)
				->pluck('id')
				->filter(fn($v) => is_string($v) && trim($v) !== '')
				->values()
				->all();
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed plucking ids', ['table' => $table, 'error' => $e->getMessage()]);
			return [];
		}
	}

	private function resolveTaskIdsForProject(string $projectId, array $allTaskIds): array
	{
		// Sem while; sem risco de loop infinito. Fallback controlado.
		try {
			if (Schema::hasTable(DC::TABLE_TASKS) && Schema::hasColumn(DC::TABLE_TASKS, AC::COL_PJ)) {
				$ids = DB::table(DC::TABLE_TASKS)
					->where(AC::COL_PJ, $projectId)
					->pluck('id')
					->filter(fn($v) => is_string($v) && trim($v) !== '')
					->values()
					->all();

				if (!empty($ids)) return $ids;
			}
		} catch (\Throwable $e) {
			Log::debug(self::class . ' task lookup by project failed', ['project_id' => $projectId, 'error' => $e->getMessage()]);
		}

		$n = random_int(2, 16);

		if (empty($allTaskIds))
			return array_fill(0, $n, null);

		$pool = $allTaskIds;
		shuffle($pool);

		return array_slice($pool, 0, min($n, count($pool)));
	}

	private function pickRandomId(array $ids, int $maxAttempts = self::MAX_PICK_ATTEMPTS): ?string
	{
		if (empty($ids)) return null;

		$attempts = 0;

		// While com break-out explícito
		while ($attempts < $maxAttempts) {
			$attempts++;
			$id = $ids[array_rand($ids)] ?? null;
			if (is_string($id) && trim($id) !== '') return $id;
		}

		return null;
	}

	private function buildInvolved(?string $responsible, array $userIds): array
	{
		$list = [];

		if (is_string($responsible) && trim($responsible) !== '')
			$list[] = $responsible;

		if (!empty($userIds)) {
			$pool = $userIds;
			shuffle($pool);

			$extra = array_slice($pool, 0, random_int(0, 3));
			foreach ($extra as $id)
				if (is_string($id) && trim($id) !== '')
					$list[] = $id;
		}

		$list = array_values(array_unique($list));
		return array_slice($list, 0, 64);
	}

	private function progressAndCompleteForStatus(EvaluationStatus $status): array
	{
		// Ajustado para o seu enum.
		return match ($status) {
			EvaluationStatus::Completed => [100, true],

			// “Aceito” pode ser interpretado como concluído (mantém status=accept, mas coerente com progress/complete)
			EvaluationStatus::Accept => [100, true],

			// Em execução
			EvaluationStatus::Active,
			EvaluationStatus::InProgress => [random_int(1, 99), false],

			// Inícios “zerados”
			EvaluationStatus::Draft,
			EvaluationStatus::Pending,
			EvaluationStatus::NotStarted => [0, false],

			// Estados terminais / bloqueados
			EvaluationStatus::Suspended,
			EvaluationStatus::Cancelled,
			EvaluationStatus::Decline,
			EvaluationStatus::Expired,
			EvaluationStatus::Archived,
			EvaluationStatus::Undefined => [random_int(0, 99), false],
		};
	}

	private function randomHexColor(): string
	{
		return sprintf('#%06X', random_int(0, 0xFFFFFF));
	}
}
