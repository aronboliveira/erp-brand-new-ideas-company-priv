<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC};
use App\Enums\{Confirmation, Frequency, Weekday, WorkShift};
use App\Models\JobOnBoard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class JobOnBoardSeeder extends Seeder
{
	private ConsoleOutput $out;

	// Hard cap defensivo (não especificado no enunciado, mas mantém o seed controlado).
	private const HARD_CAP = 16000;

	// Tentativas máximas para loops que podem “patinar”.
	private const MAX_ATTEMPTS = 256;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		// ====== READ-ONLY: otimizar com SQL cru ======
		$apps = DB::table(DC::TABLE_JOB_APPS)
			->select(['id', 'job', AC::COL_APL_AT, AC::COL_DEI_CTG, AC::COL_WRK_AUTH_APV, AC::COL_TRMS_ACPT, AC::COL_NTC_PRD])
			->orderBy('id')
			->get();

		$appsCount = $apps->count();
		if ($appsCount <= 0) {
			$this->out->writeln('<comment>[JobOnBoardSeeder]</comment> No job applications found. Skipping.');
			return;
		}

		$rawTarget = (int) floor($appsCount * 0.2);

		// Ajuste para múltiplo de 64 (sempre “para cima”, quando necessário).
		$target = $rawTarget;
		$mod = $target % 64;
		if ($mod !== 0) $target += (64 - $mod);

		// Limites defensivos: não exceder total de aplicações, nem hard cap.
		$target = min($target, $appsCount, self::HARD_CAP);

		// Se por algum motivo cair em 0 (ex.: appsCount pequeno), cria ao menos 64 (se possível),
		// mantendo a regra do múltiplo de 64.
		if ($target <= 0) {
			$target = min(64, $appsCount);
			$mod = $target % 64;
			if ($mod !== 0) $target += (64 - $mod);
			$target = min($target, $appsCount);
		}

		// Pré-carrega referências opcionais (READ-ONLY e leve).
		$contractIds = $this->pluckUuidSafe(DC::TABLE_CONTRACTS, 'id');
		$trainerIds  = $this->pluckUuidSafe(DC::TABLE_TRAINERS, 'id');

		// Treinamentos: ids e nomes (se existirem).
		$trainingRows = $this->selectIdNameSafe(DC::TABLE_TRAINING, 'id', 'name');
		$trainingIds = array_values(array_filter(array_map(
			fn($r) => is_scalar($r->id ?? null) ? (string) $r->id : null,
			$trainingRows
		), fn($v) => is_string($v) && $v !== ''));
		$trainingNames = array_values(array_filter(array_map(
			fn($r) => is_scalar($r->name ?? null) ? trim((string) $r->name) : null,
			$trainingRows
		), fn($v) => is_string($v) && $v !== ''));

		// Amostra sem reposição (evita while de unicidade).
		$pickedApps = $apps->shuffle()->take($target)->values();

		$created = 0;
		$attempts = 0;

		$this->out->writeln(
			"<info>[JobOnBoardSeeder]</info> apps={$appsCount} rawTarget={$rawTarget} target={$target} (multiple of 64)"
		);

		foreach ($pickedApps as $row) {
			if (++$attempts > ($target + self::MAX_ATTEMPTS)) {
				$this->out->writeln('<comment>[JobOnBoardSeeder]</comment> Attempt limit reached. Breaking early.');
				break;
			}

			$appId = is_scalar($row->id ?? null) ? (string) $row->id : '';
			if ($appId === '') continue;

			// Evita duplicidade lógica (não há unique constraint, mas é “contextualmente importante”).
			$exists = DB::table(DC::TABLE_JB_BRD)->where(AC::COL_APLN_ID, $appId)->exists();
			if ($exists) continue;

			$appliedAt = $this->safeDateTimeString($row->{AC::COL_APL_AT} ?? null);

			$jobShift = WorkShift::cases()[array_rand(WorkShift::cases())]->value;

			$daysPerWeek = random_int(1, 7);
			$workDays = $this->randomWeekdays($daysPerWeek);

			// salary_type: mistura Frequency/Confirmation (model normaliza ambos).
			$salaryType = $this->randomSalaryType();

			// salary: produção “nunca null”, mas seed pode gerar alguns nulls.
			$salary = $this->randomSalaryAmount();

			// joining date: após aplicação (quando houver), senão dentro de janela recente.
			$joiningDate = $this->buildJoiningDate($appliedAt);

			// status: manter “string” e compatível com legados/enum.
			$status = $this->randomStatusValue();

			// contract/trainer: opcionais
			$contract = $this->pickOptionalUuid($contractIds, 0.35); // 35% chance de setar
			$trainer  = $this->pickOptionalUuid($trainerIds, 0.55);  // 55% chance de setar

			// trainings: ids ou nomes (model valida existência). Mantém pequeno.
			$trainings = $this->randomTrainings($trainingIds, $trainingNames);

			// Flags: booleans nullable (não remover nulls; deixar o model tratar).
			// A regra de DEI: se false -> null (booted). Então usamos true/null/false e deixamos o model normalizar.
			$deiApv = $this->maybeBoolNullable(0.55, 0.35); // 55% true/false, 35% null (rest false)
			// Ajuste coerente: se aplicação não tem dei category, tende a null.
			$appDeiCtg = is_scalar($row->{AC::COL_DEI_CTG} ?? null) ? trim((string) $row->{AC::COL_DEI_CTG}) : '';
			if ($appDeiCtg === '') $deiApv = null;

			// work auth / terms / notice compliance: pode herdar da application, mas este é source of truth
			$wrkAuthFromApp = $this->scalarBoolOrNull($row->{AC::COL_WRK_AUTH_APV} ?? null);
			$trmsFromApp    = $this->scalarBoolOrNull($row->{AC::COL_TRMS_ACPT} ?? null);
			$ntcFromApp     = is_numeric($row->{AC::COL_NTC_PRD} ?? null) ? (int) $row->{AC::COL_NTC_PRD} : null;

			$wrkAuthApv = $this->coerceTruthyBias($wrkAuthFromApp, 0.70);
			$trmsAcpt   = $this->coerceTruthyBias($trmsFromApp, 0.65);
			$ntcCmp     = $this->noticeCompliant($ntcFromApp);

			// conversão pseudoboolean: o booted força 0/1 (par/ímpar)
			$cnvToEmp = random_int(0, 1) === 1 ? 1 : 0;

			$payload = [
				AC::COL_APLN_ID => $appId,

				AC::COL_WRK_AUTH_APV => $wrkAuthApv,
				AC::COL_RLC_CMP      => $this->maybeBoolNullable(0.55, 0.25),
				AC::COL_TRMS_ACPT    => $trmsAcpt,
				AC::COL_NTC_PRD_CMP  => $ntcCmp,
				AC::COL_RFR_APV      => $this->maybeBoolNullable(0.50, 0.30),
				AC::COL_FM_APV       => $this->maybeBoolNullable(0.60, 0.20),
				AC::COL_RCT_APV      => $this->maybeBoolNullable(0.70, 0.20),
				AC::COL_MNG_APV      => $this->maybeBoolNullable(0.55, 0.25),
				AC::COL_EXP_APV      => $this->maybeBoolNullable(0.60, 0.25),
				AC::COL_SKL_APV      => $this->maybeBoolNullable(0.65, 0.20),
				AC::COL_LCT_APV      => $this->maybeBoolNullable(0.60, 0.25),
				AC::COL_ITV_APRV     => $this->maybeBoolNullable(0.55, 0.25),
				AC::COL_TEST_APV     => $this->maybeBoolNullable(0.45, 0.35),
				AC::COL_BG_CK_APV    => $this->maybeBoolNullable(0.40, 0.40),
				AC::COL_DEI_APV      => $deiApv,

				AC::COL_JNG_DT   => $joiningDate,
				'status'         => $status,
				AC::COL_CNV_TO_EMP => $cnvToEmp,

				AC::COL_JB_TP    => $jobShift,
				AC::COL_DYS_WK   => $daysPerWeek,
				AC::COL_WRK_DYS  => $workDays,

				'salary'         => $salary,
				AC::COL_SLR_TP   => $salaryType,
				AC::COL_SLR_DUR  => $this->randomSalaryDurationString(),

				'contract'       => $contract,
				'trainer'        => $trainer,
				'trainings'      => $trainings,
			];

			$this->out->writeln(
				sprintf(
					'<info>[JobOnBoardSeeder]</info> creating: app=%s status=%s shift=%s days=%d salary=%s trainings=%s',
					$appId,
					(string) ($status ?? 'null'),
					(string) $jobShift,
					(int) $daysPerWeek,
					$salary === null ? 'null' : number_format((float) $salary, 2, '.', ''),
					$trainings === null ? 'null' : (is_array($trainings) ? (string) count($trainings) : '1')
				)
			);

			// IMPORTANTE: usar Model::create para respeitar $casts e ::booted (não usar insert()).
			JobOnBoard::query()->create($payload);
			$created++;

			if ($created >= $target) break;
		}

		$this->out->writeln("<info>[JobOnBoardSeeder]</info> created={$created} target={$target}");
	}

	private function pluckUuidSafe(string $table, string $col): array
	{
		try {
			if (!\Illuminate\Support\Facades\Schema::hasTable($table)) return [];
			if (!\Illuminate\Support\Facades\Schema::hasColumn($table, $col)) return [];
			$rows = DB::table($table)->select([$col])->limit(50000)->get();
			$out = [];
			foreach ($rows as $r) {
				$v = $r->{$col} ?? null;
				if (is_scalar($v) && trim((string) $v) !== '') $out[] = (string) $v;
			}
			return array_values(array_unique($out));
		} catch (\Throwable) {
			return [];
		}
	}

	private function selectIdNameSafe(string $table, string $idCol, string $nameCol): array
	{
		try {
			if (!\Illuminate\Support\Facades\Schema::hasTable($table)) return [];
			if (!\Illuminate\Support\Facades\Schema::hasColumn($table, $idCol)) return [];
			$cols = [$idCol];
			if (\Illuminate\Support\Facades\Schema::hasColumn($table, $nameCol)) $cols[] = $nameCol;
			return DB::table($table)->select($cols)->limit(50000)->get()->all();
		} catch (\Throwable) {
			return [];
		}
	}

	private function maybeBoolNullable(float $truthyProb, float $nullProb): ?bool
	{
		$r = random_int(1, 1000) / 1000;
		if ($r <= $nullProb) return null;
		return $r <= ($nullProb + $truthyProb);
	}

	private function scalarBoolOrNull(mixed $v): ?bool
	{
		if ($v === null) return null;
		if (is_bool($v)) return $v;
		if (is_numeric($v)) return ((int) $v) !== 0;
		if (is_string($v)) {
			$s = strtolower(trim($v));
			if ($s === '') return null;
			if (in_array($s, ['1', 'true', 'yes', 'y', 'sim'], true)) return true;
			if (in_array($s, ['0', 'false', 'no', 'n', 'nao', 'não'], true)) return false;
		}
		return null;
	}

	private function coerceTruthyBias(?bool $fromApp, float $biasIfNull = 0.6): ?bool
	{
		// Se app já tem, tende a manter.
		if ($fromApp !== null) {
			// 85% mantém, 15% pode ficar null (para simular falhas de validação/pendências).
			$r = random_int(1, 100);
			if ($r <= 85) return $fromApp;
			return null;
		}

		// Se não há no app, gera com viés.
		$r = random_int(1, 1000) / 1000;
		if ($r <= 0.25) return null;
		return $r <= (0.25 + $biasIfNull);
	}

	private function noticeCompliant(?int $noticeDays): ?bool
	{
		// Compliant: <= 30 (padrão do app), mas pode ser null/false.
		if ($noticeDays === null) return $this->maybeBoolNullable(0.60, 0.30);
		if ($noticeDays < 0) $noticeDays = 0;
		if ($noticeDays <= 30) return $this->maybeBoolNullable(0.80, 0.10);
		return $this->maybeBoolNullable(0.25, 0.35);
	}

	private function randomWeekdays(int $daysPerWeek): ?array
	{
		$daysPerWeek = max(1, min(7, (int) $daysPerWeek));

		$cases = Weekday::cases();
		$values = array_map(fn($e) => $e->value, $cases);

		// Se daysPerWeek == 7, retorna todos.
		if ($daysPerWeek >= 7) return $values;

		$picked = [];
		$attempt = 0;

		while (count($picked) < $daysPerWeek && $attempt++ < self::MAX_ATTEMPTS) {
			$v = $values[array_rand($values)];
			$picked[$v] = true;
		}

		$out = array_keys($picked);
		return $out ?: null;
	}

	private function randomSalaryType(): ?string
	{
		$pool = [];

		foreach (Confirmation::cases() as $c) $pool[] = $c->value;
		foreach (Frequency::cases() as $f) $pool[] = $f->value;

		// Alguns legados comuns (model aceita via normalize).
		foreach (['monthly', 'weekly', 'annual', 'confirm', 'cancel', 'expired'] as $v) $pool[] = $v;

		$pool = array_values(array_unique($pool));
		if (!$pool) return null;

		// 10% null (coluna nullable).
		if (random_int(1, 100) <= 10) return null;

		return $pool[array_rand($pool)];
	}

	private function randomSalaryAmount(): ?string
	{
		// 12% null (seed), apesar da nota “em produção nunca null”.
		if (random_int(1, 100) <= 12) return null;

		// Escala simples: 1.500 a 35.000
		$min = 1500_00;
		$max = 35000_00;
		$cents = random_int($min, $max);

		return number_format($cents / 100, 2, '.', '');
	}

	private function buildJoiningDate(?string $appliedAt): ?string
	{
		try {
			// 20% null
			if (random_int(1, 100) <= 20) return null;

			$base = $appliedAt ? \Carbon\CarbonImmutable::parse($appliedAt) : \Carbon\CarbonImmutable::now();

			// joining em 7..75 dias após base, limitado a hoje+30 para evitar muito futuro.
			$j = $base->addDays(random_int(7, 75))->startOfDay();
			$maxFuture = \Carbon\CarbonImmutable::now()->addDays(30)->startOfDay();
			if ($j->greaterThan($maxFuture)) $j = $maxFuture;

			return $j->toDateString();
		} catch (\Throwable) {
			return null;
		}
	}

	private function randomStatusValue(): ?string
	{
		// Preferir Confirmation (semântica de “after-eval”), mas aceitar legados.
		$pool = array_map(fn($e) => $e->value, Confirmation::cases());

		foreach (['pending', 'confirmed', 'declined', 'cancelled', 'expired', 'failed', 'on_hold', 'rescheduled', 'tentative'] as $v) {
			$pool[] = $v;
		}

		$pool = array_values(array_unique($pool));

		// 18% null (coluna nullable)
		if (random_int(1, 100) <= 18) return null;

		return $pool[array_rand($pool)];
	}

	private function randomSalaryDurationString(): ?string
	{
		// Coluna livre (texto). 25% null.
		if (random_int(1, 100) <= 25) return null;

		$n = random_int(1, 24);
		$unit = ['days', 'weeks', 'months', 'years'][array_rand(['days', 'weeks', 'months', 'years'])];

		// pequena variação de formato (sem tentar “padronizar demais”).
		$variant = random_int(1, 3);
		return match ($variant) {
			1 => "{$n} {$unit}",
			2 => "{$n}-{$unit}",
			default => "{$n} " . rtrim($unit, 's'),
		};
	}

	private function pickOptionalUuid(array $ids, float $probSet): ?string
	{
		if (!$ids) return null;
		$r = random_int(1, 1000) / 1000;
		if ($r > $probSet) return null;
		return $ids[array_rand($ids)];
	}

	private function randomTrainings(array $trainingIds, array $trainingNames): ?array
	{
		// 40% null
		if (random_int(1, 100) <= 40) return null;

		// Prefer ids se existirem; senão nomes.
		$pool = $trainingIds ?: $trainingNames;
		if (!$pool) return null;

		$k = random_int(1, min(4, count($pool)));
		$picked = [];

		$attempt = 0;
		while (count($picked) < $k && $attempt++ < self::MAX_ATTEMPTS) {
			$v = $pool[array_rand($pool)];
			if (!is_string($v) || trim($v) === '') continue;
			$picked[trim($v)] = true;
		}

		$out = array_keys($picked);
		return $out ?: null;
	}

	private function safeDateTimeString(mixed $v): ?string
	{
		if ($v === null || $v === '') return null;
		try {
			return \Carbon\CarbonImmutable::parse($v)->toDateTimeString();
		} catch (\Throwable) {
			return null;
		}
	}
}
