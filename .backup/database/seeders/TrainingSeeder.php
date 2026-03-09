<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\Training;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrainingSeeder extends Seeder
{
	private const BRANCH_PROBABILITY_PCT = 20;
	private const CERTIFICATE_PROBABILITY_PCT = 20;
	private const REQUIRED_CERTS_PROBABILITY_PCT = 30;
	private \Symfony\Component\Console\Output\ConsoleOutput $output;
	public function run(): void
	{
		DB::disableQueryLog();

		$employees = $this->pluckIds(DC::TABLE_EMPLOYEES);
		$types     = $this->pluckIds(DC::TABLE_TRAINING_TYPES);
		$trainers  = $this->pluckIds(DC::TABLE_TRAINERS);
		$companies = DB::table(DC::TABLE_USERS)
			->where(UC::COL_TP, 'company')
			->pluck('id')
			->filter()
			->values()
			->all();

		$branches = DB::table(DC::TABLE_BRANCHES)
			->select('id', 'company')
			->get()
			->map(function ($r) {
				return [
					'id' => (string) ($r->id ?? ''),
					'company' => (string) ($r->company ?? ''),
				];
			})
			->filter(fn($x) => $x['id'] !== '')
			->values()
			->all();

		$docs = $this->pluckIds(DC::TABLE_DOCS);

		if (empty($employees) || empty($types) || empty($trainers) || empty($companies)) {
			$this->warnMaybe('TrainingSeeder skipped: missing employees, training_types, trainers or company-users.');
			return;
		}

		$countOpt = $this->getCountOption();

		if ($countOpt !== null) {
			$this->seedRandom($countOpt, $employees, $types, $trainers, $companies, $branches, $docs);
			return;
		}
		$this->output ??= new \Symfony\Component\Console\Output\ConsoleOutput();
		$cap = 1024;
		foreach ($employees as $empId) {
			if ($cap <= 0 || !$cap) break;
			$perEmployee = random_int(1, 8);
			for ($i = 0; $i < $perEmployee; $i++) {
				if ($cap <= 0 || !$cap) break;
				$cap--;
				$this->output->writeln("Seeding training for employee $empId (" . ($i + 1) . "/$perEmployee)");
				Training::query()->create(
					$this->makeRow((string) $empId, $types, $trainers, $companies, $branches, $docs)
				);
			}
		}
	}

	private function seedRandom(
		int $total,
		array $employees,
		array $types,
		array $trainers,
		array $companies,
		array $branches,
		array $docs
	): void {
		for ($i = 0; $i < $total; $i++) {
			$empId = $this->pick($employees);
			Training::query()->create(
				$this->makeRow((string) $empId, $types, $trainers, $companies, $branches, $docs)
			);
		}
	}

	private function makeRow(
		string $employeeId,
		array $types,
		array $trainers,
		array $companies,
		array $branches,
		array $docs
	): array {
		$typeId = (string) $this->pick($types);
		$trainerId = (string) $this->pick($trainers);

		$companyId = (string) $this->pick($companies);
		$branchId = null;

		if (!empty($branches) && random_int(1, 100) <= self::BRANCH_PROBABILITY_PCT) {
			$b = $this->pick($branches);
			if (!empty($b['company']) && in_array($b['company'], $companies, true)) {
				$companyId = (string) $b['company'];
				$branchId = (string) $b['id'];
			}
		}

		$start = Carbon::now()->subDays(random_int(0, 365))->toDateString();
		$end = Carbon::parse($start)->addDays(random_int(0, 5))->toDateString();

		$expectedDuration = null;
		if (random_int(1, 100) <= 50) {
			$expectedDuration = $this->minutesToTime(random_int(30, 8 * 60));
		}

		$certificate = null;
		if (!empty($docs) && random_int(1, 100) <= self::CERTIFICATE_PROBABILITY_PCT) {
			$certificate = (string) $this->pick($docs);
		}

		$requiredCerts = [];
		if (!empty($docs) && random_int(1, 100) <= self::REQUIRED_CERTS_PROBABILITY_PCT) {
			$requiredCerts = $this->pickMany($docs, random_int(1, min(3, count($docs))));
		}
		$this->output ??= new \Symfony\Component\Console\Output\ConsoleOutput();
		$this->output->writeln("Seeding training for employee $employeeId");
		return [
			'name' => null,

			'company' => $companyId,
			'branch' => $branchId,
			UC::COL_EMP_ID => $employeeId,

			'trainer' => $trainerId,
			CC::COL_TRAINER_OPT => random_int(0, 2),

			CC::COL_TRN_TP => $typeId,
			CC::COL_TRN_CST => (float) random_int(0, 3000),

			PJC::COL_MIN_DR => null,
			PJC::COL_MAX_DR => null,
			PJC::COL_EXP_DR => $expectedDuration,

			PJC::COL_S_DT => $start,
			PJC::COL_E_DT => $end,

			'required' => (bool) random_int(0, 1),
			'description' => $this->maybeText(60, 'Treinamento interno com foco em processo e boas práticas.'),
			'certificate' => $certificate,

			'performance' => random_int(0, 4),
			'status' => random_int(0, 3),

			'remarks' => $this->maybeText(40, 'Observações registradas durante a execução do treinamento.'),
			'attachments' => $this->maybeArray(35, ['notes' => 'Anexos informativos.']),
			CC::COL_RQ_CERT => $requiredCerts,
			'tags' => $this->maybeArray(60, ['training', 'hrm', 'compliance']),
			'metadata' => $this->maybeArray(60, ['seed' => true, 'v' => 1]),
		];
	}

	private function pluckIds(string $table): array
	{
		return DB::table($table)->pluck('id')->filter()->values()->all();
	}

	private function getCountOption(): ?int
	{
		if (!($this->command instanceof \Illuminate\Console\Command)) {
			return null;
		}

		if (!$this->command->hasOption('count')) {
			return null;
		}

		$raw = $this->command->option('count');
		if (!is_numeric($raw)) {
			return null;
		}

		$n = (int) $raw;
		return $n >= 1 ? $n : null;
	}

	private function pick(array $items): mixed
	{
		return $items[array_rand($items)];
	}

	private function pickMany(array $items, int $n): array
	{
		$items = array_values(array_unique(array_filter($items, fn($v) => is_string($v) && trim($v) !== '')));
		if ($n <= 0 || empty($items)) return [];

		shuffle($items);
		return array_slice($items, 0, min($n, count($items)));
	}

	private function minutesToTime(int $minutes): string
	{
		$m = max(0, $minutes);
		$h = intdiv($m, 60);
		$mm = $m % 60;
		return sprintf('%02d:%02d:00', $h, $mm);
	}

	private function maybeText(int $probPct, string $text): ?string
	{
		return random_int(1, 100) <= $probPct ? $text : null;
	}

	private function maybeArray(int $probPct, array $value): ?array
	{
		return random_int(1, 100) <= $probPct ? $value : null;
	}

	private function warnMaybe(string $msg): void
	{
		try {
			if ($this->command instanceof \Illuminate\Console\Command) {
				$this->command->warn($msg);
				return;
			}
		} catch (\Throwable) {
		}

		Log::warning($msg);
	}
}
