<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\PaymentPatternType;
use App\Models\OtherPayment;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OtherPaymentSeeder extends Seeder
{
	use EnsuresSystemUser;

	private const SEED_TAG = 'seed:other_payments';

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			// Limpa apenas o que este seeder gerou anteriormente
			DB::table(DC::TABLE_OT_PYMTS)
				->where('notes', self::SEED_TAG)
				->delete();

			// Coleta colaboradores ativos para associar pagamentos
			$employeeIds = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
			if (empty($employeeIds)) {
				Log::warning('OtherPaymentSeeder: nenhum employee encontrado; nada a semear.');
				return;
			}

			// Catálogo de títulos plausíveis
			$titles = [
				'Bônus pontual',
				'Premiação por desempenho',
				'Reconhecimento trimestral',
				'Diária de viagem',
				'Reembolso extraordinário',
				'Gratificação por projeto',
				'Abono eventual',
				'Incentivo operacional',
				'Complemento de meta',
				'Retribuição por disponibilidade'
			];

			$created = 0;
			$tz = 'America/Sao_Paulo';

			foreach ($employeeIds as $empId) {
				// 0 a 3 lançamentos por colaborador
				$count = random_int(0, 3);
				if ($count === 0) {
					continue;
				}

				// Usamos títulos “sem colisão” para idempotência intra-execução
				$picked = $this->pickUnique($titles, $count);

				foreach ($picked as $title) {
					$type = random_int(0, 1) === 1 ? PaymentPatternType::Percentage : PaymentPatternType::Fixed;

					// Valor compatível com o tipo
					if ($type === PaymentPatternType::Percentage) {
						// 3% a 30%
						$amount = random_int(3, 30);
					} else {
						// R$ 60,00 a R$ 3.000,00 (duas casas)
						$amount = random_int(6000, 300000) / 100;
					}

					// Timestamp aleatório nos últimos 540 dias
					$dt = now($tz)->subDays(random_int(0, 540))
						->setTime(random_int(8, 19), random_int(0, 59), random_int(0, 59));

					OtherPayment::create([
						UC::COL_EMP_ID     => $empId,
						'title'            => $title,
						'amount'           => $amount,
						'type'             => $type, // cast para enum no model
						'notes'            => self::SEED_TAG,
						DC::TABLE_CREATOR  => $systemUserId,
						'created_at'       => $dt->format('Y-m-d H:i:s'),
						'updated_at'       => $dt->format('Y-m-d H:i:s'),
					]);

					$created++;
				}
			}

			Log::info("OtherPaymentSeeder: created={$created}");
		}, 3);
	}

	/**
	 * Retorna N itens únicos de um array base, embaralhando a ordem.
	 * Se N exceder o tamanho do array, realiza rotação sem repetir por iteração.
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

		// Se precisar de mais do que o catálogo, reusa com sufixo incremental
		$out = $pool;
		$needed = $n - count($pool);
		for ($i = 1; $i <= $needed; $i++) {
			$out[] = $pool[$i % count($pool)] . " #{$i}";
		}
		return $out;
	}
}
