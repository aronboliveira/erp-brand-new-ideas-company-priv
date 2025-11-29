<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BC,
	ChartsConstants as CHTC,
	DatabaseConstants as DC
};
use App\Models\{
	BankAccount,
	BankTransfer,
	Contract,
	Invoice,
	Loan,
	Payslip,
	ProductServiceUnit,
	User
};
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class BankTransferSeeder extends Seeder
{
	public function run(): void
	{
		DB::transaction(function (): void {
			$faker = Faker::create('pt_BR');

			// ------------------------------------------------------------------
			// 1) Garantir que existam pelo menos 2 contas bancárias
			// ------------------------------------------------------------------
			$accounts = BankAccount::query()->pluck('id')->all();

			if (count($accounts) < 2) {
				Log::warning('BankTransferSeeder: menos de 2 contas encontradas; criando contas mock para testes.');
				$accounts = array_merge(
					$accounts,
					$this->bootstrapMockAccounts($faker, max(0, 2 - count($accounts)))
				);
			}

			if (count($accounts) < 2) {
				Log::error('BankTransferSeeder: não foi possível garantir 2 contas. Abortando.');
				return;
			}

			// ------------------------------------------------------------------
			// 2) Catálogo de rótulos de meios de pagamento compatíveis com a coluna ENUM
			//    (o Model converterá para o enum interno quando cabível)
			// ------------------------------------------------------------------
			$payLabels = ['debit', 'credit', 'pix', 'ted', 'doc', 'other'];

			// ------------------------------------------------------------------
			// 3) Obter IDs auxiliares (quando existentes) para relacionamentos opcionais
			// ------------------------------------------------------------------
			$contracts = Contract::query()->pluck('id')->all();
			$loans     = Loan::query()->pluck('id')->all();
			$invoices  = Invoice::query()->pluck('id')->all();
			$payslips  = Payslip::query()->pluck('id')->all();
			$psUnits   = ProductServiceUnit::query()->pluck('id')->all();
			$users     = User::query()->pluck('id')->all();

			// ------------------------------------------------------------------
			// 4) Gerar transferências
			// ------------------------------------------------------------------
			$total    = 40;
			$created  = 0;
			$updated  = 0;

			for ($i = 0; $i < $total; $i++) {
				// Contas distintas
				$from = $faker->randomElement($accounts);
				do {
					$to = $faker->randomElement($accounts);
				} while ($to === $from);

				// Valores
				$amount  = $faker->randomFloat(2, 10, 50_000);                       // valor principal
				$svcFee  = $faker->boolean(60) ? $faker->randomFloat(2, 0, 25) : 0;  // taxa serviço
				$taxFee  = $faker->boolean(50) ? $faker->randomFloat(2, 0, 15) : 0;  // taxes agregadas

				// Parcelas (mantidas compatíveis com as regras do Model)
				$instTotal   = $faker->boolean(30) ? $faker->numberBetween(1, 12) : 1;
				$instCurrent = $instTotal > 1 ? $faker->numberBetween(1, $instTotal) : 1;

				// Datas (o Model normaliza timestamps no passado para "agora" quando aplicável)
				$isScheduledFuture = $faker->boolean(30);
				$scheduledAt = $isScheduledFuture
					? now()->addDays($faker->numberBetween(1, 15))
					: now()->subDays($faker->numberBetween(0, 5));
				$executedAt  = $faker->boolean(70) ? (clone $scheduledAt)->addMinutes($faker->numberBetween(5, 180)) : null;
				$completedAt = $executedAt && $faker->boolean(80) ? (clone $executedAt)->addMinutes($faker->numberBetween(10, 180)) : null;
				$failedAt    = (!$completedAt && $executedAt && $faker->boolean(10)) ? (clone $executedAt)->addMinutes($faker->numberBetween(5, 60)) : null;
				$cancelledAt = (!$executedAt && $faker->boolean(5)) ? (clone $scheduledAt)->addMinutes($faker->numberBetween(1, 90)) : null;

				// PIX/Cartão/etc
				$payLabel = $faker->randomElement($payLabels);
				$payInt   = in_array($payLabel, ['credit'], true) ? 1 : 0; // 0=debit ou outros; 1=credit, conforme observação da migração

				// Códigos e metadados
				$transferCode = 'TRF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
				$purposeCode  = (string) $faker->randomElement(['300', '310', '120', '540', '999']);
				$transferType = $faker->randomElement(['salary', BC::VL_SPL_PAY, BC::VL_TAX_PAY, BC::VL_LN_PAY, 'investment', 'withdrawal', 'internal', 'rent', 'service', 'purchase', 'refund', 'other']);

				// Regras de reconciliação automática
				$autorcc = $faker->boolean(35);
				$rccRules = $autorcc ? [
					'match_min'         => 0.92,
					'match_window_days' => 7,
					'allow_split'       => $faker->boolean(),
					'notes_required'    => $faker->boolean(30),
				] : [];

				// Impostos “visuais” (não vinculados a Tax records; COL_TXS_LST é livre)
				$taxList = $faker->boolean(50) ? [
					[
						'name'  => 'IOF',
						'rate'  => 0.38,
						'value' => round($amount * 0.0038, 2),
					],
				] : [];

				// Anexos válidos para o enum MimeType do projeto (ex.: pdf, jpg, png)
				$attachments = $faker->boolean(50) ? [
					[
						'path'      => 'docs/transfers/' . Str::uuid() . '.pdf',
						'extension' => 'pdf',
						'label'     => 'Comprovante PDF',
					],
				] : [];

				// Relacionamentos eventuais (para enriquecer o dataset)
				$relContract = $faker->boolean(20) && !empty($contracts) ? $faker->randomElement($contracts) : null;
				$relLoan     = $faker->boolean(10) && !empty($loans)     ? $faker->randomElement($loans)     : null;
				$relInvoice  = $faker->boolean(25) && !empty($invoices)  ? $faker->randomElement($invoices)  : null;
				$relPayslip  = $faker->boolean(10) && !empty($payslips)  ? $faker->randomElement($payslips)  : null;
				$relPSU      = $faker->boolean(15) && !empty($psUnits)   ? $faker->randomElement($psUnits)   : null;

				$creator = !empty($users) ? $faker->randomElement($users) : null;

				$payload = [
					// Identificadores das contas
					BC::COL_ACC_FROM      => $from,
					BC::COL_ACC_TO        => $to,

					// Moeda e valores
					BC::COL_CUR_ID        => 'BRL',
					'amount'              => round($amount, 2),
					BC::COL_SVC_FEE       => round($svcFee, 2),
					BC::COL_TXS_FEE       => round($taxFee, 2),

					// Parcelas
					BC::COL_N_INTR        => $instTotal,
					BC::COL_CURR_N_INTR   => $instCurrent,

					// Método de pagamento
					BC::COL_PAY_MTD       => $payInt,
					BC::COL_PAY_MTD_LB    => $payLabel,

					// Agendamento e execução
					BC::COL_SCHD_TRF_TS   => $scheduledAt,
					BC::COL_EXC_AT        => $executedAt,
					BC::COL_CMP_AT        => $completedAt,
					DC::COL_FL_AT         => $failedAt,
					BC::COL_CNC_AT        => $cancelledAt,
					BC::COL_IS_SCD        => $isScheduledFuture,
					BC::COL_CAN_CHG_BK    => $faker->boolean(5),

					// Propósito/regulatório
					BC::COL_PPS_CD        => $purposeCode,
					BC::COL_TRF_TP        => $transferType,
					BC::COL_PPS_DS        => $faker->boolean(40) ? $faker->sentence(8) : null,

					// Reconciliação
					BC::COL_AUTORCC       => $autorcc,
					BC::COL_RCC_RL        => $rccRules,
					BC::COL_RCC_AT        => null,
					BC::COL_RCC_BY        => null,

					// Descritivo
					'reference'           => $transferCode,
					'description'         => $faker->sentence(12),
					'notes'               => $faker->boolean(50) ? $faker->paragraph() : null,

					// T&C e tributos auxiliares
					BC::COL_TC            => [],          // manter vazio; o Model exige Tax resolvível quando preenchido
					BC::COL_TXS_LST       => $taxList,

					// Relacionamentos opcionais
					'contract'            => $relContract,
					'loan'                => $relLoan,
					'invoice'             => $relInvoice,
					'payslip'             => $relPayslip,
					BC::COL_PRD_SV_UNT    => $relPSU,

					// Anexos e logs
					'attachments'         => $attachments,
					DC::COL_ER_LG         => [],
					DC::COL_RTR_CT        => 0,
					DC::COL_LST_RTR_AT    => null,

					// Auditoria
					DC::TABLE_CREATOR     => $creator,
				];

				// Critério de upsert por referência única
				$unique = ['reference' => $payload['reference']];

				$model = BankTransfer::updateOrCreate($unique, $payload);
				$model->wasRecentlyCreated ? $created++ : $updated++;
			}

			Log::info("BankTransferSeeder: created={$created}, updated={$updated}");
		}, 3);
	}

	/**
	 * Cria rapidamente N contas bancárias mock para permitir o seed de transferências.
	 * Mantém campos mínimos requeridos conforme a migração/model.
	 *
	 * @return string[] IDs das contas criadas
	 */
	private function bootstrapMockAccounts(\Faker\Generator $faker, int $n): array
	{
		$ids = [];
		for ($i = 0; $i < $n; $i++) {
			$acc = BankAccount::create([
				BKC::COL_ACC_N   => $faker->bankAccountNumber() . '-' . $faker->randomDigit(),
				BKC::COL_IS_VRT  => true,

				BKC::COL_HNM     => $faker->name(),
				BKC::COL_CT      => preg_replace('/\D+/', '', $faker->cellphoneNumber()),
				BKC::COL_HD_ADDR => $faker->address(),

				BKC::COL_NM      => $faker->randomElement(['Banco do Brasil', 'Itaú Unibanco', 'Bradesco', 'Santander']),
				BKC::COL_ADR     => $faker->streetAddress() . ', ' . $faker->city(),
				BKC::COL_BANK_IDF => str_pad((string) $faker->numberBetween(1_000_000_000_0000, 9_999_999_999_9999), 14, '0', STR_PAD_LEFT),
				BKC::COL_AG_N    => (string) $faker->numberBetween(1, 9999),
				BKC::COL_AG_DG   => (string) $faker->randomDigit(),

				BKC::COL_OB      => $faker->randomFloat(2, 0, 20_000),
				BC::COL_CUR_ID   => 'BRL',
				CHTC::CUR_BL     => $faker->randomFloat(2, 0, 20_000),
				BKC::COL_AMT_STR => $faker->randomFloat(2, 0, 25_000),
				BKC::COL_AM_LK   => $faker->randomFloat(2, 0, 2_000),

				BKC::COL_PIX_KEYS => [],
				'vaults'         => [],
				'restrictions'   => [],
				'profile'        => [],
				BKC::COL_CRD_CD  => [],
				BKC::COL_DBT_CD  => [],
			]);

			$ids[] = $acc->id;
		}
		return $ids;
	}
}
