<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\PaymentPatternType;
use App\Models\Overtime;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OvertimeSeeder extends Seeder
{
	use EnsuresSystemUser;

	private const SEED_TAG = 'seed:overtime';

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			// Limpa apenas o que este seeder gerou anteriormente
			DB::table(DC::TABLE_OVT)
				->where('notes', self::SEED_TAG)
				->delete();

			// Colaboradores
			$employeeIds = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
			if (empty($employeeIds)) {
				Log::warning('OvertimeSeeder: nenhum employee encontrado; nada a semear.');
				return;
			}

			$titles = [
				'Plantão extraordinário',
				'Hora extra de manutenção',
				'Atendimento emergencial',
				'Suporte fora do expediente',
				'Intervenção programada',
				'Cobertura de turno',
				'Virada de release',
				'Backup operacional',
				'Monitoramento noturno',
				'Rotina pós-deploy',
			];

			$tz   = 'America/Sao_Paulo';
			$base = now($tz);
			$created = 0;

			foreach ($employeeIds as $empId) {
				$qtd = random_int(0, 3);
				if ($qtd === 0) {
					continue;
				}

				$picked = $this->pickUnique($titles, $qtd);

				foreach ($picked as $title) {
					$type = random_int(0, 1) === 1 ? PaymentPatternType::Percentage : PaymentPatternType::Fixed;

					// Parâmetros coerentes
					$days  = random_int(0, 5);        // pode ser 0 se compensado em horas
					$hours = max(1, random_int(1, 8)); // 1..8
					if ($type === PaymentPatternType::Percentage) {
						$rate = random_int(3, 30);      // 3%..30% (campo inteiro)
					} else {
						$rate = random_int(10, 200);     // R$ por hora (inteiro)
					}

					// Timestamp (até 360 dias atrás)
					$dt = (clone $base)
						->subDays(random_int(0, 360))
						->setTime(random_int(8, 19), random_int(0, 59), random_int(0, 59));

					Overtime::create([
						UC::COL_EMP_ID    => $empId,
						'title'           => $title,
						UC::COL_NDAYS     => $days,
						'hours'           => $hours,
						'rate'            => $rate,
						'type'            => $type,               // cast enum no model
						'notes'           => self::SEED_TAG,
						DC::TABLE_CREATOR => $systemUserId,
						'created_at'      => $dt->format('Y-m-d H:i:s'),
						'updated_at'      => $dt->format('Y-m-d H:i:s'),
					]);

					$created++;
				}
			}

			Log::info("OvertimeSeeder: created={$created}");
		}, 3);
	}

	/**
	 * Retorna N itens únicos do array base; se exceder, reutiliza com sufixos.
	 *
	 * @param array<int,string> $pool
	 * @return array<int,string>
	 */
	private function pickUnique(array $pool, int $n): array
	{
		$n = max(0, $n);
		if ($n === 0) return [];

		$pool = array_values($pool);
		shuffle($pool);

		if ($n <= count($pool)) {
			return array_slice($pool, 0, $n);
		}

		$out = $pool;
		$needed = $n - count($pool);
		for ($i = 1; $i <= $needed; $i++) {
			$out[] = $pool[$i % count($pool)] . " #{$i}";
		}
		return $out;
	}
}
