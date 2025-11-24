<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{
	Allowance,
	Commission,
	Employee,
	Loan,
	Overtime,
	OtherPayment,
	Payslip,
	SaturationDeduction
};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PayslipSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$systemUserId = $this->ensureSystemUser();

			$tz  = 'America/Sao_Paulo';
			$now = now($tz);

			// Cache de IDs existentes para relações opcionais
			$idPool = [
				'allowance'  => Allowance::query()->pluck('id')->all(),
				'commission' => Commission::query()->pluck('id')->all(),
				'loan'       => Loan::query()->pluck('id')->all(),
				'st_ded'     => SaturationDeduction::query()->pluck('id')->all(),
				'ot_pay'     => OtherPayment::query()->pluck('id')->all(),
				'overtime'   => Overtime::query()->pluck('id')->all(),
			];

			// Probabilidades de vincular cada relação (em %)
			$attachProb = [
				'allowance'  => 35,
				'commission' => 25,
				'loan'       => 15,
				'st_ded'     => 20,
				'ot_pay'     => 25,
				'overtime'   => 30,
			];

			$created = 0;
			$updated = 0;

			Employee::query()
				->select(['id', UC::COL_EMP_ID])
				->orderBy('id')
				->chunkById(200, function ($employees) use ($now, $tz, $idPool, $attachProb, $systemUserId, &$created, &$updated) {
					foreach ($employees as $emp) {
						// Gera de 2 a 6 folhas (meses recentes)
						$monthsBack = random_int(2, 6);

						for ($i = 0; $i < $monthsBack; $i++) {
							// Referência de mês (sem usar copy/parse/createFromDate)
							$ref = now($tz)->subMonths($i);

							// Mês de competência (string)
							$salaryMonth = $ref->format('Y-m');

							// Faixa de salário bruto (R$ 2.500,00 a R$ 15.000,00)
							$gross = $this->money(mt_rand(250000, 1500000) / 100);

							// Deduções e acréscimos aproximados
							$deductionsPct = mt_rand(5, 25) / 100;   // 5% a 25%
							$additionsPct  = mt_rand(0, 12) / 100;  // 0% a 12%

							$net = $gross - ($gross * $deductionsPct) + ($gross * $additionsPct);
							if ($net < 0) {
								$net = 0.00;
							}

							$gross = $this->money($gross);
							$net   = $this->money($net);

							// Valor líquido pagável (inteiro, nunca maior que o líquido)
							$netPayable = (int) floor($net);

							// Define um dia de pagamento entre 25 e 28 do mês de referência
							$payDayDay   = mt_rand(25, 28);
							$payDay      = sprintf('%04d-%02d-%02d', (int)$ref->format('Y'), (int)$ref->format('m'), $payDayDay);
							$todayString = $now->format('Y-m-d');

							// Status simples: 0=pending, 1=processed, 2=paid
							$status = (strcmp($todayString, $payDay) >= 0) ? mt_rand(1, 2) : 0;

							// Relações opcionais (com probabilidade)
							$rels = [
								'allowance'     => $this->maybePickId($idPool['allowance'],  $attachProb['allowance']),
								'commission'    => $this->maybePickId($idPool['commission'], $attachProb['commission']),
								'loan'          => $this->maybePickId($idPool['loan'],       $attachProb['loan']),
								BC::COL_ST_DD   => $this->maybePickId($idPool['st_ded'],     $attachProb['st_ded']),
								BC::COL_OT_PAY  => $this->maybePickId($idPool['ot_pay'],     $attachProb['ot_pay']),
								'overtime'      => $this->maybePickId($idPool['overtime'],   $attachProb['overtime']),
							];

							// Payload consolidado
							$payload = array_merge([
								UC::COL_EMP_ID      => $emp->id,
								BC::COL_SLR_M       => $salaryMonth,
								BC::COL_P_DAY       => $payDay,
								BC::COL_G_SLR       => $gross,
								BC::COL_N_SLR       => $net,
								BC::COL_NET_PAYABLE => $netPayable,
								'status'            => $status,
							], $rels);

							// Evita duplicar por funcionário + competência
							$existing = Payslip::query()
								->where(UC::COL_EMP_ID, $emp->id)
								->where(BC::COL_SLR_M, $salaryMonth)
								->first();

							if ($existing) {
								$existing->fill($payload);
								if (empty($existing->{DC::TABLE_CREATOR})) {
									$existing->{DC::TABLE_CREATOR} = $systemUserId;
								}
								$existing->save();
								$updated++;
							} else {
								$m = new Payslip($payload);
								$m->{DC::TABLE_CREATOR} = $systemUserId;
								$m->save();
								$created++;
							}
						}
					}
				});

			Log::info("PayslipSeeder concluído: created={$created}, updated={$updated}");
		});
	}

	private function money(float $v): float
	{
		return (float) number_format($v, 2, '.', '');
	}

	/**
	 * Retorna um ID aleatório do pool respeitando a probabilidade.
	 *
	 * @param  array<string> $pool
	 * @param  int           $probability 0–100
	 */
	private function maybePickId(array $pool, int $probability): ?string
	{
		if (empty($pool)) {
			return null;
		}
		if (mt_rand(1, 100) > $probability) {
			return null;
		}
		return (string) $pool[array_rand($pool)];
	}
}
