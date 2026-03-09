<?php

namespace Database\Seeders;

use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\PermissionsConstants as PMC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\AttendanceStatus;
use App\Models\{Employee, EmployeeAttendance};
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeeAttendanceSeeder extends Seeder
{
	// Parâmetros fixos (sem env)
	private const OPTIONALITY   = 0.65;
	private const BACK_DAYS     = 31;
	private const PER_EMP_MIN   = 8;
	private const PER_EMP_MAX   = 18;

	/**
	 * Opção CLI:
	 *  --count=INT   Teto aproximado de registros a inserir (opcional).
	 */
	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_EATD)) {
			$this->command?->warn('Tabela de atendimentos ausente. Abortado.');
			return;
		}

		// Coleções base
		$employees = Schema::hasTable(DC::TABLE_EMPLOYEES ?? 'employees')
			? DB::table(DC::TABLE_EMPLOYEES ?? 'employees')->pluck('id')->all()
			: [];
		$editors = DB::table(DC::TABLE_USERS ?? 'users')->whereIn(UC::COL_TP, [PMC::HR, PMC::ADM, PMC::SA, PMC::CPN])->pluck('id')->all();

		if (!$employees) {
			$this->command?->warn('Nenhum empregado encontrado. Abortado.');
			return;
		}

		$overtimes = Schema::hasTable(DC::TABLE_OVT ?? 'overtimes')
			? DB::table(DC::TABLE_OVT ?? 'overtimes')->pluck('id')->all()
			: [];

		// Parâmetros (fixos)
		$opt         = self::OPTIONALITY;
		$backDays    = self::BACK_DAYS;
		$perEmpMin   = self::PER_EMP_MIN;
		$perEmpMax   = self::PER_EMP_MAX;
		if ($perEmpMax < $perEmpMin) $perEmpMax = $perEmpMin;

		$target   = (int) ($this->command && $this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count') ? $this->command?->option('count') : min(2048, count($employees)));
		$inserted = 0;

		$maybe = fn(callable $fn) => fake()->boolean((int) round($opt * 100)) ? $fn() : null;

		// Helpers de tempo
		$t = fn(int $h, int $m = 0, int $s = 0) => sprintf('%02d:%02d:%02d', max(0, $h), max(0, $m), max(0, $s));
		$addMin = function (string $hhmmss, int $delta) use ($t): string {
			[$h, $m, $s] = array_map('intval', explode(':', $hhmmss));
			$total = $h * 60 + $m + $delta;
			if ($total < 0) $total = 0;
			$h = intdiv($total, 60);
			$m = $total % 60;
			return $t($h, $m, $s);
		};

		$zero = '00:00:00';
		$today = Carbon::today();

		DB::transaction(function () use (
			$employees,
			$editors,
			$overtimes,
			$maybe,
			$opt,
			$t,
			$addMin,
			$zero,
			$today,
			$perEmpMin,
			$perEmpMax,
			$backDays,
			$target,
			&$inserted
		) {
			foreach ($employees as $empId) {
				if ($target > 0 && $inserted >= $target) break;

				// $daysToSeed = fake()->numberBetween($perEmpMin, $perEmpMax); // ORIGINAL — unbounded
				$daysToSeed = min(2, fake()->numberBetween($perEmpMin, $perEmpMax)); // HARD CAP
				// Sorteia datas nos últimos $backDays dias
				$dates = [];
				for ($i = 0; $i < $daysToSeed; $i++) {
					$d = $today->subDays(fake()->numberBetween(1, max(2, $backDays)));
					$dates[] = $d->toDateString();
				}
				$dates = array_values(array_unique($dates));
				// $ref = $empId instanceof Employee ? ($empId->name ?? $empId->id) : (Employee::query()->where('id', $empId)->value('name') ?? $empId);
				// (new \Symfony\Component\Console\Output\ConsoleOutput
				// )->writeln("Criando Atendimento para funcionário: {$ref}");
				foreach ($dates as $date) {
					try {
						if ($target > 0 && $inserted >= $target) break;

						// Status distribuído
						$status = Arr::random([
							AttendanceStatus::Present->value,
							AttendanceStatus::Present->value,
							AttendanceStatus::Present->value,
							AttendanceStatus::Remote->value,
							AttendanceStatus::Leave->value,
							AttendanceStatus::Absent->value,
						]);

						// Jornada base (9h–18h)
						$clkInBase  = $t(9, 0);
						$clkOutBase = $t(18, 0);

						// Variações
						$clkIn  = $addMin($clkInBase, fake()->numberBetween(-20, 45));
						$clkOut = $addMin($clkOutBase, fake()->numberBetween(-60, 150));

						if (in_array($status, [AttendanceStatus::Absent->value, AttendanceStatus::Leave->value], true)) {
							$clkIn  = $zero;
							$clkOut = $zero;
						}

						$erlArr = $maybe(function () use ($clkIn, $addMin, $zero) {
							if ($clkIn === $zero) return null;
							$min = fake()->numberBetween(5, 30);
							return $addMin($clkIn, -$min);
						});

						$late = in_array($status, [AttendanceStatus::Present->value, AttendanceStatus::Remote->value], true)
							? (fake()->boolean(35) && $clkIn !== '00:00:00'
								? $addMin($clkIn, fake()->numberBetween(3, 30))
								: $zero)
							: $zero;

						$erlLeave = $maybe(function () use ($clkOut, $addMin, $zero) {
							if ($clkOut === $zero) return null;
							return fake()->boolean(25) ? $addMin($clkOut, -fake()->numberBetween(5, 45)) : $zero;
						});

						$overtime = ($clkOut !== $zero && fake()->boolean(30))
							? $addMin($clkOut, fake()->numberBetween(15, 120))
							: $zero;

						$erlArrCount = $maybe(fn() => $erlArr && $erlArr !== $zero ? fake()->numberBetween(1, 3) : 0);
						$lateCount   = $maybe(fn() => $late !== $zero ? fake()->numberBetween(1, 4) : 0);
						$erlLvCount  = $maybe(fn() => $erlLeave && $erlLeave !== $zero ? fake()->numberBetween(1, 2) : 0);
						$ovtCount    = $maybe(fn() => $overtime !== $zero ? fake()->numberBetween(1, 3) : 0);

						$ttRest = $maybe(function () use ($t) {
							$mins = Arr::random([0, 15, 30, 45, 60, 90]);
							return $t(intdiv($mins, 60), $mins % 60, 0);
						});

						$ttWork = fake()->boolean(40) ? null : null;

						$ovtId = $maybe(fn() => $overtimes ? Arr::random($overtimes) : null);

						$row = new EmployeeAttendance([
							'id'                   => (string) Str::uuid(),
							UC::COL_EMP_ID         => $empId,
							'date'                 => $date,
							'status'               => $status,
							AC::COL_CLK_IN         => $clkIn,
							AC::COL_CLK_OUT        => $clkOut,

							AC::COL_ERL_ARV        => $erlArr ?? null,
							AC::COL_ERL_AV_CT      => $erlArrCount,
							'late'                 => $late,
							AC::COL_LT_CT          => $lateCount,
							AC::COL_ERL_LV         => $erlLeave ?? $zero,
							AC::COL_ERL_LV_CT      => $erlLvCount,
							'overtime'             => $overtime,
							AC::COL_OVT_CT         => $ovtCount,
							AC::COL_OVT_ID         => $ovtId,

							AC::COL_TT_RST         => $ttRest,
							AC::COL_TT_WRK         => $ttWork,
						]);

						if (Schema::hasColumn(DC::TABLE_EATD, DC::COL_TABLE_CREATOR)) {
							$row->{DC::COL_TABLE_CREATOR} = $maybe(fn() => Arr::random($editors));
						}
						if (Schema::hasColumn(DC::TABLE_EATD, DC::COL_TABLE_UPDATER)) {
							$row->{DC::COL_TABLE_UPDATER} = $maybe(fn() => Arr::random($editors));
						}

						$row->save();
						$inserted++;

						if ($target > 0 && $inserted >= $target) break;
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		});

		$this->command?->info("EmployeeAttendanceSeeder: inseridos {$inserted} registros em " . DC::TABLE_EATD . ".");
	}
}
