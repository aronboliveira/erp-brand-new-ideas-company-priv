<?php

namespace Database\Seeders;

use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\AttendanceStatus;
use App\Models\EmployeeAttendance;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeeAttendanceSeeder extends Seeder
{
	/**
	 * Parâmetros (ENV/CLI):
	 *  EATD_OPTIONALITY=0..1    Probabilidade média de preencher campos opcionais (default 0.65)
	 *  EATD_BACK_DAYS=INT       Quantos dias no passado semear (default 30)
	 *  EATD_PER_EMP_MIN=INT     Registros mínimos por empregado (default 8)
	 *  EATD_PER_EMP_MAX=INT     Registros máximos por empregado (default 18)
	 *  --count=INT              Teto aproximado de registros a inserir (opcional)
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

		if (!$employees) {
			$this->command?->warn('Nenhum empregado encontrado. Abortado.');
			return;
		}

		$overtimes = Schema::hasTable(DC::TABLE_OVT ?? 'overtimes')
			? DB::table(DC::TABLE_OVT ?? 'overtimes')->pluck('id')->all()
			: [];

		$opt         = max(0.0, min(1.0, (float) env('EATD_OPTIONALITY', 0.65)));
		$backDays    = (int) env('EATD_BACK_DAYS', 30);
		$perEmpMin   = (int) env('EATD_PER_EMP_MIN', 8);
		$perEmpMax   = (int) env('EATD_PER_EMP_MAX', 18);
		if ($perEmpMax < $perEmpMin) $perEmpMax = $perEmpMin;

		$target = (int) ($this->command?->option('count') ?? 0);
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
			$overtimes,
			$maybe,
			$opt,
			$t,
			$addMin,
			$zero,
			$today,
			$perEmpMin,
			$perEmpMax,
			$target,
			&$inserted
		) {
			foreach ($employees as $empId) {
				if ($target > 0 && $inserted >= $target) break;

				$daysToSeed = fake()->numberBetween($perEmpMin, $perEmpMax);
				// Sorteia datas nos últimos $backDays dias (evita fins de semana parcialmente)
				$dates = [];
				for ($i = 0; $i < $daysToSeed; $i++) {
					$d = $today->subDays(fake()->numberBetween(1, max(2, (int) env('EATD_BACK_DAYS', 30))));
					$dates[] = $d->toDateString();
				}
				$dates = array_values(array_unique($dates));

				foreach ($dates as $date) {
					if ($target > 0 && $inserted >= $target) break;

					// Status distribuído: Present/Remote predominam
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

					// Variações de chegada/saída
					$clkIn  = $addMin($clkInBase, fake()->numberBetween(-20, 45));  // pode adiantar ou atrasar
					$clkOut = $addMin($clkOutBase, fake()->numberBetween(-60, 150)); // pode sair cedo ou fazer hora extra

					// Para Absent/Leave, zeramos clocks
					if (in_array($status, [AttendanceStatus::Absent->value, AttendanceStatus::Leave->value], true)) {
						$clkIn  = $zero;
						$clkOut = $zero;
					}

					// Early arrival (antes do clock-in) — opcional
					$erlArr = $maybe(function () use ($clkIn, $addMin, $zero) {
						if ($clkIn === $zero) return null;
						$min = fake()->numberBetween(5, 30);
						return $addMin($clkIn, -$min);
					});

					// Late (após clock-in) — sempre presente como coluna; 00:00:00 se nenhum atraso
					$late = in_array($status, [AttendanceStatus::Present->value, AttendanceStatus::Remote->value], true)
						? (fake()->boolean(35) && $clkIn !== '00:00:00'
							? $addMin($clkIn, fake()->numberBetween(3, 30))
							: $zero)
						: $zero;

					// Early leave (antes do clock-out) — opcional
					$erlLeave = $maybe(function () use ($clkOut, $addMin, $zero) {
						if ($clkOut === $zero) return null;
						return fake()->boolean(25) ? $addMin($clkOut, -fake()->numberBetween(5, 45)) : $zero;
					});

					// Overtime (após clock-out) — sempre presente como coluna; 00:00:00 quando não houver
					$overtime = ($clkOut !== $zero && fake()->boolean(30))
						? $addMin($clkOut, fake()->numberBetween(15, 120))
						: $zero;

					// Contadores opcionais (deixe null às vezes para o boot normalizar para 0)
					$erlArrCount = $maybe(fn() => $erlArr && $erlArr !== $zero ? fake()->numberBetween(1, 3) : 0);
					$lateCount   = $maybe(fn() => $late !== $zero ? fake()->numberBetween(1, 4) : 0);
					$erlLvCount  = $maybe(fn() => $erlLeave && $erlLeave !== $zero ? fake()->numberBetween(1, 2) : 0);
					$ovtCount    = $maybe(fn() => $overtime !== $zero ? fake()->numberBetween(1, 3) : 0);

					// Descanso total (opcional; se null, Model setará/normalizará)
					$ttRest = $maybe(function () use ($t) {
						$mins = Arr::random([0, 15, 30, 45, 60, 90]);
						return $t(intdiv($mins, 60), $mins % 60, 0);
					});

					// TT_WRK: às vezes deixa null para ser calculado por calculateTotalWork()
					$ttWork = fake()->boolean(40) ? null : null; // força mais variação

					// Vínculo com overtime_id às vezes
					$ovtId = $maybe(fn() => $overtimes ? Arr::random($overtimes) : null);

					// Monta e cria via Model para acionar normalizações
					$row = new EmployeeAttendance([
						'id'                   => (string) Str::uuid(),
						UC::COL_EMP_ID         => $empId,
						'date'                 => $date,
						'status'               => $status,
						AC::COL_CLK_IN         => $clkIn,
						AC::COL_CLK_OUT        => $clkOut,

						AC::COL_ERL_ARV        => $erlArr ?? null,
						AC::COL_ERL_AV_CT      => $erlArrCount,        // pode ser null
						'late'                 => $late,
						AC::COL_LT_CT          => $lateCount,          // pode ser null
						AC::COL_ERL_LV         => $erlLeave ?? $zero,  // coluna não-nullable; usa 00:00:00 se ausente
						AC::COL_ERL_LV_CT      => $erlLvCount,         // pode ser null
						'overtime'             => $overtime,           // coluna não-nullable
						AC::COL_OVT_CT         => $ovtCount,           // pode ser null
						AC::COL_OVT_ID         => $ovtId,

						AC::COL_TT_RST         => $ttRest,             // pode ser null
						AC::COL_TT_WRK         => $ttWork,             // pode ser null
					]);

					// Marcas de auditoria (se existirem como fillable/trait)
					if (Schema::hasColumn(DC::TABLE_EATD, DC::COL_TABLE_CREATOR)) {
						$row->{DC::COL_TABLE_CREATOR} = $maybe(fn() => Arr::random($employees));
					}
					if (Schema::hasColumn(DC::TABLE_EATD, DC::COL_TABLE_UPDATER)) {
						$row->{DC::COL_TABLE_UPDATER} = $maybe(fn() => Arr::random($employees));
					}

					$row->save();
					$inserted++;

					if ($target > 0 && $inserted >= $target) break;
				}
			}
		});

		$this->command?->info("EmployeeAttendanceSeeder: inseridos {$inserted} registros em " . DC::TABLE_EATD . ".");
	}
}
