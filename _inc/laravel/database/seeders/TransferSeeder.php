<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CPC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\{Branch, Department, Employee, Transfer};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\{Seeder};
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\{ConsoleOutput};

final class TransferSeeder extends Seeder
{
	use EnsuresSystemUser;
	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	private const MAX_PICK_ATTEMPTS = 24;
	private const MAX_DATE_SHIFT_ATTEMPTS = 7;

	public function run(): void
	{
		$faker = fake('pt_BR');
		$out = new ConsoleOutput();
		DB::transaction(function () use ($faker, $out): void {
			$clock = microtime(true);
			$systemUserId = $this->ensureSystemUser();

			$employees = Employee::query()
				->select(['id', CPC::COL_BRC_ID, CPC::COL_DEP_ID])
				->get()
				->values();

			if ($employees->isEmpty()) {
				Log::notice('No employees found. Skipping transfer seeding.');
				return;
			}

			$branchIds = Branch::query()
				->pluck('id')
				->filter(fn($v) => is_string($v) && trim($v) !== '')
				->values()
				->all();

			if (!$branchIds) {
				Log::notice('No branches found. Skipping transfer seeding.');
				return;
			}

			$departments = Department::query()
				->select(['id', CPC::COL_BRC_ID])
				->get();

			$deptIdsAll = $departments
				->pluck('id')
				->filter(fn($v) => is_string($v) && trim($v) !== '')
				->values()
				->all();

			if (!$deptIdsAll) {
				Log::notice('No departments found. Skipping transfer seeding.');
				return;
			}

			$deptIdsByBranch = $departments
				->groupBy(CPC::COL_BRC_ID)
				->map(fn($rows) => $rows->pluck('id')->filter(fn($v) => is_string($v) && trim($v) !== '')->values()->all())
				->all();

			$branchAddressMap = Branch::query()
				->pluck('address', 'id')
				->all();

			$nEmployees = $employees->count();
			$countOpt = null;

			try {
				if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
					$raw = $this->command->option('count');
					if (is_numeric($raw) && (int) $raw > 0) $countOpt = (int) $raw;
				}
			} catch (\Throwable) {
			}

			// Regra do projeto (mocking): default >= 64 * n (n = base entities: employees)
			$total = $countOpt ?? max(16 * max(1, $nEmployees), 3);

			$out->writeln("Seeding Transfers: total={$total}, employees={$nEmployees}");

			$employeeArr = $employees->all();
			$seenEmpDate = [];

			$hardCap = 2; /* original: 3200 */
			for ($i = 1; $i <= $total; $i++) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				if (!$hardCap) break;
				$hardCap--;
				try {
					/** @var Employee $emp */
					$emp = $employeeArr[random_int(0, count($employeeArr) - 1)];
					$empId = (string) ($emp->getAttribute('id') ?? '');

					if ($empId === '') {
						Log::warning('TransferSeeder: employee without id encountered, skipping.');
						continue;
					}

					$srcBranchId = (string) ($emp->getAttribute(CPC::COL_BRC_ID) ?? '');
					$srcDeptId   = (string) ($emp->getAttribute(CPC::COL_DEP_ID) ?? '');

					[$destBranchId, $destDeptId] = $this->pickDestination(
						$srcBranchId,
						$srcDeptId,
						$branchIds,
						$deptIdsAll,
						$deptIdsByBranch
					);

					// Data da transferência: últimos ~18 meses (0..540 dias)
					$dt = now('America/Sao_Paulo')->subDays(random_int(0, 540))->toDateString();

					// Evita colisão "employee_id + date" nesta execução (sem query no BD).
					for ($k = 0; $k < self::MAX_DATE_SHIFT_ATTEMPTS; $k++) {
						$key = $empId . '|' . $dt;
						if (!isset($seenEmpDate[$key])) {
							$seenEmpDate[$key] = true;
							break;
						}
						$dt = now('America/Sao_Paulo')->subDays(random_int(0, 540))->addDays($k + 1)->toDateString();
					}

					$t = new Transfer();
					$t->setAttribute(UC::COL_EMP_ID, $empId);
					$t->setAttribute(UC::COL_BRC_ID, $destBranchId);
					$t->setAttribute(UC::COL_DEP_ID, $destDeptId);
					$t->setAttribute(UC::COL_TRF_DT, $dt);
					$t->setAttribute('description', $faker->boolean(60) ? $faker->sentence(8) : null);
					$t->setAttribute('notes', $faker->boolean(35) ? $faker->sentence(10) : null);

					// Auditoria explícita (sem auth()).
					$t->setAttribute(DC::COL_TABLE_CREATOR, $systemUserId);
					$t->setAttribute(DC::COL_TABLE_UPDATER, $systemUserId);

					$t->save();

					// IMPORTANTÍSSIMO:
					// Atualiza APENAS lotação via query builder para não disparar boot/mutators do Employee
					// (evita re-associação indevida de user_id e violação de UNIQUE).
					$payload = [
						CPC::COL_BRC_ID => $destBranchId,
						CPC::COL_DEP_ID => $destDeptId,
						DC::COL_TABLE_UPDATER => $systemUserId,
						DC::COL_U_AT => now('America/Sao_Paulo'),
					];

					$addr = $branchAddressMap[$destBranchId] ?? null;
					if (is_string($addr) && trim($addr) !== '') {
						$payload[CPC::COL_BRC_LC] = $addr;
					}

					DB::table(DC::TABLE_EMPLOYEES)
						->where('id', $empId)
						->update($payload);

					// Mantém o array local coerente para próximas iterações
					$emp->setAttribute(CPC::COL_BRC_ID, $destBranchId);
					$emp->setAttribute(CPC::COL_DEP_ID, $destDeptId);

					// $out->writeln(sprintf(
					// 	'[%d/%d] emp=%s | %s/%s -> %s/%s | %s',
					// 	$i,
					// 	$total,
					// 	$empId,
					// 	$srcBranchId !== '' ? $srcBranchId : '-',
					// 	$srcDeptId !== '' ? $srcDeptId : '-',
					// 	$destBranchId !== '' ? $destBranchId : '-',
					// 	is_string($destDeptId) && $destDeptId !== '' ? $destDeptId : '-',
					// 	$dt
					// ));
				} catch (\Throwable $e) {
					Log::warning('TransferSeeder: failed record', [
						'i' => $i,
						'error' => $e->getMessage(),
					]);
					continue;
				}
			}
		}, 3);
	}

	/**
	 * Regra:
	 * - Branch pode permanecer igual se (e somente se) Dept mudar.
	 * - Dept pode permanecer igual se (e somente se) Branch mudar.
	 * - Não pode ficar "sem mudança" (branch e dept iguais aos do employee).
	 * - Mantém o snippet-base de branch selection (reject current, whenEmpty fallback).
	 */
	private function pickDestination(
		string $srcBranchId,
		string $srcDeptId,
		array $branchIds,
		array $deptIdsAll,
		array $deptIdsByBranch
	): array {
		$branchIds = array_values(array_filter($branchIds, fn($v) => is_string($v) && trim($v) !== ''));
		$deptIdsAll = array_values(array_filter($deptIdsAll, fn($v) => is_string($v) && trim($v) !== ''));

		if (!$branchIds) {
			throw new \RuntimeException('No branches available for transfer destination.');
		}
		if (!$deptIdsAll) {
			throw new \RuntimeException('No departments available for transfer destination.');
		}

		for ($attempt = 0; $attempt < self::MAX_PICK_ATTEMPTS; $attempt++) {
			// === MANTÉM A REGRA (snippet) ===
			$destBranchId = collect($branchIds)
				->reject(fn($b) => $srcBranchId !== '' && $b === $srcBranchId)
				->whenEmpty(fn($c) => collect($branchIds))
				->shuffle()
				->first();

			if (!is_string($destBranchId) || trim($destBranchId) === '') {
				$destBranchId = $srcBranchId !== '' ? $srcBranchId : $branchIds[array_rand($branchIds)];
			}

			// Tenta dept compatível com a filial escolhida; cai para qualquer um se não houver.
			$deptPoolForBranch = $deptIdsByBranch[$destBranchId] ?? [];
			$pool = $deptPoolForBranch ?: $deptIdsAll;

			// Se a branch ficou igual, tenta forçar trocar dept (quando houver dept atual).
			if ($destBranchId === $srcBranchId && $srcDeptId !== '' && count($pool) > 1) {
				$pool = array_values(array_filter($pool, fn($d) => $d !== $srcDeptId));
			}

			$destDeptId = $pool ? $pool[array_rand($pool)] : null;

			$branchDiff = ($srcBranchId === '') ? ($destBranchId !== '') : ($destBranchId !== $srcBranchId);
			$deptDiff   = ($srcDeptId === '') ? (is_string($destDeptId) && $destDeptId !== '') : ((string) $destDeptId !== $srcDeptId);

			if ($destBranchId !== '' && ($branchDiff || $deptDiff)) {
				return [$destBranchId, $destDeptId];
			}

			// fallback estruturado (sem while infinito):
			// 1) tenta trocar dept na mesma branch
			if ($destBranchId === $srcBranchId && $srcDeptId !== '') {
				$altPool = $deptPoolForBranch ?: $deptIdsAll;
				$altPool = array_values(array_filter($altPool, fn($d) => $d !== $srcDeptId));
				if ($altPool) {
					return [$destBranchId, $altPool[array_rand($altPool)]];
				}
			}

			// 2) tenta trocar branch e pegar um dept (qualquer)
			$altBranches = array_values(array_filter($branchIds, fn($b) => $srcBranchId === '' ? true : $b !== $srcBranchId));
			if ($altBranches) {
				$b = $altBranches[array_rand($altBranches)];
				$p = ($deptIdsByBranch[$b] ?? []) ?: $deptIdsAll;
				return [$b, $p ? $p[array_rand($p)] : null];
			}
		}

		throw new \RuntimeException('Cannot pick a valid destination for Transfer after max attempts.');
	}
}
