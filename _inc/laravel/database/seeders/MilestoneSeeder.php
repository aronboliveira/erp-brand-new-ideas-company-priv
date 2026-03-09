<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{EvaluationStatus, PriorityLevel};
use App\Models\Milestone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class MilestoneSeeder extends Seeder
{
	// private const MAX_TOTAL_CREATED = 512; // break-out de segurança (não é multiplicador)
	private const MAX_TOTAL_CREATED = 2; // break-out de segurança (não é multiplicador)

	private const MAX_VERBOSE_ROWS = 80;

	public function run(): void
	{
		$output = new ConsoleOutput();
		$io     = new SymfonyStyle(new ArrayInput([]), $output);

		$projectIds = $this->pluckIdsSafe(DC::TABLE_PROJECTS);
		if (empty($projectIds)) {
			$io->warning('No projects found; skipping MilestoneSeeder.');
			return;
		}

		$userIds = $this->pluckIdsSafe(DC::TABLE_USERS);
		$empIds  = $this->pluckIdsSafe(DC::TABLE_EMPLOYEES);

		$made = 0;

		$priorityCounts = [];
		$statusCounts   = [];
		$mismatchCounts = 0;

		$rows = [];

		$io->title('MilestoneSeeder');
		$io->text('Projects: ' . count($projectIds));
		$io->text('Users: ' . count($userIds));
		$io->text('Employees: ' . count($empIds));
		foreach ($projectIds as $projectId) {
			$n = 1;
			for ($i = 0; $i < $n; $i++) {
				$pickedPriorities = $this->pickCases(PriorityLevel::cases(), 2);
				$pickedStatuses   = $this->pickCases(EvaluationStatus::cases(), 2);

				foreach ($pickedPriorities as $priority) {
					foreach ($pickedStatuses as $status) {
						if ($made >= self::MAX_TOTAL_CREATED) {
							$io->warning('Break-out: MAX_TOTAL_CREATED reached. Stopping early.');
							break 4;
						}

						try {
							[$startDate, $dueDate] = $this->makeDatePair();

							$requestedPriority = (string) $priority->value;
							$requestedStatus   = (string) $status->value;

							[$progress, $cost] = $this->progressAndCostForStatus($status);

							$m = new Milestone();

							$m->setAttribute(PJC::COL_PJ_ID, $projectId);
							$m->setAttribute('title', $this->makeTitle($projectId, $startDate, $dueDate, $requestedPriority, $requestedStatus));
							$m->setAttribute('description', 'Seeded milestone for reporting/testing.');

							$m->setAttribute('priority', $requestedPriority);
							$m->setAttribute('status', $requestedStatus);

							$m->setAttribute('progress', $progress);
							$m->setAttribute('cost', $cost);

							$m->setAttribute(PJC::COL_S_DT, $startDate->toDateString());
							$m->setAttribute(PJC::COL_D_DATE, $dueDate->toDateString());

							$involved = $this->buildInvolved($userIds, $empIds);
							$m->setAttribute('involved', $involved);

							$m->setAttribute('tags', [
								'seed',
								strtolower($requestedPriority),
								strtolower($requestedStatus),
							]);

							$m->setAttribute('metadata', [
								'seed' => true,
								'v'    => 1,
								'ts'   => Carbon::now()->toIso8601String(),
								'requested_priority' => $requestedPriority,
								'requested_status'   => $requestedStatus,
								'involved_count'     => count($involved),
							]);
							// $output->writeln('Creating Milestone for project ' . $projectId . ' with priority ' . $requestedPriority . ' and status ' . $requestedStatus);
							$m->save();
							$made++;

							$priorityCounts[$requestedPriority] = ($priorityCounts[$requestedPriority] ?? 0) + 1;

							// Pode haver normalização pelo normalize() do enum (ex.: in_progress -> active)
							$persistedStatus = (string) ($m->getRawOriginal('status') ?? '');
							$statusCounts[$persistedStatus] = ($statusCounts[$persistedStatus] ?? 0) + 1;

							$mismatch = ($persistedStatus !== '' && $persistedStatus !== $requestedStatus);
							if ($mismatch) $mismatchCounts++;

							if (count($rows) < self::MAX_VERBOSE_ROWS) {
								$rows[] = [
									'i' => (string) $made,
									'project_id' => (string) $projectId,
									'title' => (string) ($m->getAttribute('title') ?? ''),
									'priority(req)' => $requestedPriority,
									'status(req)' => $requestedStatus,
									'status(db)' => $persistedStatus,
									'start' => (string) ($m->getAttribute(PJC::COL_S_DT) ?? ''),
									'due' => (string) ($m->getAttribute(PJC::COL_D_DATE) ?? ''),
									'progress' => (string) ($m->getAttribute('progress') ?? ''),
									'cost' => (string) ($m->getAttribute('cost') ?? ''),
									'mismatch' => $mismatch ? 'YES' : '',
								];
							}
						} catch (\Throwable $e) {
							Log::warning(self::class . ' failed creating Milestone', [
								'project_id' => (string) $projectId,
								'error'      => $e->getMessage(),
							]);
						}
					}
				}
			}
		}
		$cap = self::MAX_TOTAL_CREATED;
		foreach ($projectIds as $projectId) {
			if (!$cap || 0 >= $cap) break;
			$cap--;
			$n = random_int(1, 4);
			for ($i = 0; $i < $n; $i++) {
				$pickedPriorities = $this->pickCases(PriorityLevel::cases(), 2);
				$pickedStatuses   = $this->pickCases(EvaluationStatus::cases(), 2);

				foreach ($pickedPriorities as $priority) {
					foreach ($pickedStatuses as $status) {
						if ($made >= self::MAX_TOTAL_CREATED) {
							$io->warning('Break-out: MAX_TOTAL_CREATED reached. Stopping early.');
							break 4;
						}

						try {
							[$startDate, $dueDate] = $this->makeDatePair();

							$requestedPriority = (string) $priority->value;
							$requestedStatus   = (string) $status->value;

							[$progress, $cost] = $this->progressAndCostForStatus($status);

							$m = new Milestone();

							$m->setAttribute(PJC::COL_PJ_ID, $projectId);
							$m->setAttribute('title', $this->makeTitle($projectId, $startDate, $dueDate, $requestedPriority, $requestedStatus));
							$m->setAttribute('description', 'Seeded milestone for reporting/testing.');

							$m->setAttribute('priority', $requestedPriority);
							$m->setAttribute('status', $requestedStatus);

							$m->setAttribute('progress', $progress);
							$m->setAttribute('cost', $cost);

							$m->setAttribute(PJC::COL_S_DT, $startDate->toDateString());
							$m->setAttribute(PJC::COL_D_DATE, $dueDate->toDateString());

							$involved = $this->buildInvolved($userIds, $empIds);
							$m->setAttribute('involved', $involved);

							$m->setAttribute('tags', [
								'seed',
								strtolower($requestedPriority),
								strtolower($requestedStatus),
							]);

							$m->setAttribute('metadata', [
								'seed' => true,
								'v'    => 1,
								'ts'   => Carbon::now()->toIso8601String(),
								'requested_priority' => $requestedPriority,
								'requested_status'   => $requestedStatus,
								'involved_count'     => count($involved),
							]);
							// $output->writeln('<info>[MilestoneSeeder]</info> creating milestone for project_id=' . (string) $projectId . ' priority=' . $requestedPriority . ' status=' . $requestedStatus . ' start=' . $startDate->toDateString() . ' due=' . $dueDate->toDateString());
							$m->save();
							$made++;

							$priorityCounts[$requestedPriority] = ($priorityCounts[$requestedPriority] ?? 0) + 1;

							// Pode haver normalização pelo normalize() do enum (ex.: in_progress -> active)
							$persistedStatus = (string) ($m->getRawOriginal('status') ?? '');
							$statusCounts[$persistedStatus] = ($statusCounts[$persistedStatus] ?? 0) + 1;

							$mismatch = ($persistedStatus !== '' && $persistedStatus !== $requestedStatus);
							if ($mismatch) $mismatchCounts++;

							if (count($rows) < self::MAX_VERBOSE_ROWS) {
								$rows[] = [
									'i' => (string) $made,
									'project_id' => (string) $projectId,
									'title' => (string) ($m->getAttribute('title') ?? ''),
									'priority(req)' => $requestedPriority,
									'status(req)' => $requestedStatus,
									'status(db)' => $persistedStatus,
									'start' => (string) ($m->getAttribute(PJC::COL_S_DT) ?? ''),
									'due' => (string) ($m->getAttribute(PJC::COL_D_DATE) ?? ''),
									'progress' => (string) ($m->getAttribute('progress') ?? ''),
									'cost' => (string) ($m->getAttribute('cost') ?? ''),
									'mismatch' => $mismatch ? 'YES' : '',
								];
							}
						} catch (\Throwable $e) {
							Log::warning(self::class . ' failed creating Milestone', [
								'project_id' => (string) $projectId,
								'error'      => $e->getMessage(),
							]);
						}
					}
				}
			}
		}

		$io->section('Sample variations (requested vs persisted)');
		if (!empty($rows)) {
			$io->table(array_keys($rows[0]), array_map(fn($r) => array_values($r), $rows));
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

		Log::info(self::class . ' done', [
			'created' => $made,
			'mismatches' => $mismatchCounts,
		]);
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
			Log::warning(self::class . ' failed plucking ids', [
				'table' => $table,
				'error' => $e->getMessage(),
			]);
			return [];
		}
	}

	/**
	 * Seleciona N cases (sem while), sempre com fallback seguro.
	 */
	private function pickCases(array $cases, int $n): array
	{
		$cases = array_values($cases);
		if (empty($cases)) return [];

		shuffle($cases);
		return array_slice($cases, 0, min($n, count($cases)));
	}

	/**
	 * Garante coerência: due_date >= start_date.
	 */
	private function makeDatePair(): array
	{
		$start = Carbon::now()->addDays(random_int(-30, 30))->startOfDay();
		$due   = (clone $start)->addDays(random_int(0, 90))->startOfDay();
		return [$start, $due];
	}

	private function makeTitle(string $projectId, Carbon $start, Carbon $due, string $priority, string $status): string
	{
		$pj = $projectId !== '' ? mb_substr($projectId, 0, 8) : 'project';
		$t  = "Milestone — {$pj} — {$start->toDateString()} → {$due->toDateString()} — {$priority}/{$status}";
		return mb_substr($t, 0, 255);
	}

	private function buildInvolved(array $userIds, array $empIds): array
	{
		$out = [];

		if (!empty($userIds)) {
			$u = $userIds;
			shuffle($u);
			foreach (array_slice($u, 0, random_int(0, 3)) as $id)
				if (is_string($id) && trim($id) !== '') $out[] = trim($id);
		}

		if (!empty($empIds)) {
			$e = $empIds;
			shuffle($e);
			foreach (array_slice($e, 0, random_int(0, 2)) as $id)
				if (is_string($id) && trim($id) !== '') $out[] = trim($id);
		}

		// adiciona alguns “nomes” para validar comportamento legado (sem bater em DB)
		foreach (['Ana', 'Bruno', 'Carla', 'Diego'] as $name)
			if (random_int(0, 10) >= 9) $out[] = $name;

		$out = array_values(array_unique($out));
		return array_slice($out, 0, 128);
	}

	private function progressAndCostForStatus(EvaluationStatus $status): array
	{
		// Mantém coerência “semântica” (model ainda clamp/corrige)
		return match ($status) {
			EvaluationStatus::Completed,
			EvaluationStatus::Accept => [100.00, (float) random_int(1000, 20000)],

			EvaluationStatus::Active,
			EvaluationStatus::InProgress => [(float) random_int(1, 99), (float) random_int(0, 30000)],

			EvaluationStatus::Draft,
			EvaluationStatus::Pending,
			EvaluationStatus::NotStarted => [0.00, (float) random_int(0, 10000)],

			default => [(float) random_int(0, 99), (float) random_int(0, 15000)],
		};
	}
}
