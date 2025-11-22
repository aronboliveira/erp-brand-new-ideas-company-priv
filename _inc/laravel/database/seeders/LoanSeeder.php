<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\{Employee, Loan, LoanOption};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class LoanSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$employees = Employee::query()->select(['id'])->get();
			if ($employees->isEmpty()) {
				Log::info('LoanSeeder: nenhum employee encontrado.');
				return;
			}

			$tz    = 'America/Sao_Paulo';
			$today = now($tz);

			$titles = [
				'Empréstimo Consignado',
				'Adiantamento Salarial',
				'Auxílio Excepcional',
				'Crédito Emergencial',
				'Linha Interna de Crédito',
			];

			$created = 0;
			$updated = 0;

			foreach ($employees as $emp) {
				// 0..2 empréstimos por empregado
				$qty = random_int(0, 2);
				if ($qty === 0) {
					continue;
				}

				$picked = collect($titles)->shuffle()->take($qty);

				foreach ($picked as $title) {
					// Alterna entre tipos aceitos pelo enum LoanType
					$type = (random_int(0, 1) === 1) ? 'percentage' : 'fixed';

					// Datas sempre com format('Y-m-d')
					$start = (clone $today)->subDays(random_int(0, 720))->format('Y-m-d');
					$end   = (random_int(0, 3) === 0)
						? null
						: (clone $today)->addDays(random_int(30, 540))->format('Y-m-d');

					// Monta valor coerente com o tipo
					if ($type === 'percentage') {
						// 1%..30% (modelo garantirá sanidade extra)
						$amount = (float) random_int(1, 30);
					} else {
						// R$ 500..R$ 15.000 (valor absoluto)
						$amount = (float) random_int(500, 15000);
					}

					// Parcelas (nulo para casos “não consignados”)
					$installments = (random_int(0, 1) === 1) ? random_int(6, 48) : null;

					// DeductionType orientado a desconto de empréstimo
					$deductionType = 'loan';

					// Opcionalmente atrela uma LoanOption existente
					$loanOptionId = null;
					if (random_int(0, 1) === 1) {
						$opt = LoanOption::query()->inRandomOrder()->select('id')->first();
						if ($opt) {
							$loanOptionId = $opt->id;
						}
					}

					// Idempotência por funcionário + título
					$model = Loan::updateOrCreate(
						[UC::COL_EMP_ID => $emp->id, 'title' => $title],
						[
							UC::COL_EMP_ID    => $emp->id,
							BC::COL_LN_OPT    => $loanOptionId,
							'title'           => $title,
							'type'            => $type,            // LoanType aceita 'fixed'|'percentage'
							'amount'          => $amount,
							PJC::COL_S_DT     => $start,
							PJC::COL_E_DT     => $end,
							'reason'          => 'Necessidade financeira pontual / política interna.',
							BC::COL_DD_TYPE   => $deductionType,   // DeductionType::loan
							'installments'    => $installments,
							DC::TABLE_CREATOR => $systemUserId,
						]
					);

					$model->wasRecentlyCreated ? $created++ : $updated++;
				}
			}

			Log::info("LoanSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
