<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Models\{PosPayment, Payment};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

class PosPaymentSeeder extends Seeder
{
	/**
	 * Gera registros em pos_payments, vinculando a POS e, na maioria dos casos,
	 * a um Payment existente. Usa Eloquent::create() para disparar eventos
	 * e permitir que o hook ::saving do modelo sincronize dados no Payment.
	 */
	public function run(): void
	{
		// Coleta IDs existentes para manter integridade referencial
		$posIds       = DB::table(DC::TABLE_POS)->pluck('id')->all();
		$paymentIds   = DB::table(DC::TABLE_PAY)->pluck('id')->all();
		$bankAccountIds = DB::table(DC::TABLE_BANK_ACC)->pluck('id')->all();

		if (empty($posIds)) {
			$this->command?->warn('PosPaymentSeeder: nenhuma POS encontrada — nada a semear.');
			return;
		}

		// Quantidade alvo proporcional ao nº de POS (limites de segurança)
		$target = min(max(count($posIds) * 32, 16), 160);

		DB::transaction(function () use ($posIds, $paymentIds, $bankAccountIds, $target) {
			for ($i = 0; $i < $target; $i++) {
				try {
					$posId = $posIds[array_rand($posIds)];
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Pagamento para Ponto de Venda: {$posId}");
					// 80% dos registros terão um Payment associado (em produção deveria ser 100%)
					$paymentId = (!empty($paymentIds) && random_int(1, 100) <= 80)
						? $paymentIds[array_rand($paymentIds)]
						: null;

					// 70% terão conta bancária (pode ser nula p/ PIX/dinheiro em specs antigas)
					$bankId = (!empty($bankAccountIds) && random_int(1, 100) <= 70)
						? $bankAccountIds[array_rand($bankAccountIds)]
						: null;

					// Datas recentes (últimos 120 dias)
					$date = Carbon::now()->subDays(random_int(0, 120))->toDateString();

					// Valores não negativos e consistentes
					$amount = round(random_int(5_00, 10_000_00) / 100, 2); // R$5,00 a R$10.000,00
					$maxDiscount = (int) floor($amount * 0.25 * 100);     // máx. 25% do valor
					$discountAmt = $maxDiscount > 0
						? round(random_int(0, $maxDiscount) / 100, 2)
						: 0.00;

					// Sincronização com Payment é feita no hook ::saving do modelo
					// (criamos via Eloquent para disparar o evento 'saving').
					try {
						PosPayment::query()->create([
							BC::COL_POS_ID   => $posId,
							'payment'        => $paymentId,
							BC::COL_BACC_ID  => $bankId,
							'date'           => $date,
							'amount'         => $amount,
							'discount'       => $discountAmt,   // mantido como valor absoluto
							BC::COL_DSC_AMT  => $discountAmt,   // espelha 'discount' (compat. legado)
						]);
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("Criando Pagamento de Ponta de Venda relacionado a Pagamento {$paymentId}, Ponto de Venda {$posId} e Conta Bancária {$bankId}");

						// Opcional: se não havia Payment e quiser garantir vínculo mínimo,
						// crie um Payment básico aqui e atualize o PosPayment (omisso por padrão).
					} catch (\Throwable $e) {
						// Em caso de erro isolado, continua o seeding sem abortar toda a transação
						report($e);
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		});
	}
}
