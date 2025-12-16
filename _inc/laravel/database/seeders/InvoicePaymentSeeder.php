<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\TransferType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable as Carbon;

class InvoicePaymentSeeder extends Seeder
{
	public function run(): void
	{
		// Verificações de existência
		if (!Schema::hasTable(DC::TABLE_INV_PAY) || !Schema::hasTable(DC::TABLE_INVS)) {
			$this->command?->warn('Tabelas necessárias (invoice_payments/invoices) ausentes. Pulando.');
			return;
		}
		if (!Schema::hasTable(DC::TABLE_BANK_ACC)) {
			$this->command?->warn('Tabela de contas bancárias ausente (bank_accounts). Pulando para evitar violação de NOT NULL em account_id.');
			return;
		}

		// Parâmetros fixos para mocks
		$optionality = 0.65;
		$perInvMin = 1;
		$perInvMax = 3;
		$forceFull = false;
		$targetCount = 0; // 0 = sem limite

		$maybe = fn(callable $fn) => fake()->boolean((int) round($optionality * 100)) ? $fn() : null;
		$json  = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		// Bases
		$invoices = DB::table(DC::TABLE_INVS)->select('id', BC::COL_CUR_ID . ' as currency_id', 'amount')->get();
		if ($invoices->isEmpty()) {
			$this->command?->warn('Nenhuma fatura encontrada. Pulando.');
			return;
		}

		$bankAccIds  = DB::table(DC::TABLE_BANK_ACC)->pluck('id')->all();
		if (!$bankAccIds) {
			$this->command?->warn('Nenhuma conta bancária. Pulando.');
			return;
		}

		$userIds     = Schema::hasTable(DC::TABLE_USERS) ? DB::table(DC::TABLE_USERS)->pluck('id')->all() : [];
		$catIds      = Schema::hasTable(DC::TABLE_PROD_SERV_CATS) ? DB::table(DC::TABLE_PROD_SERV_CATS)->pluck('id')->all() : [];
		$orderIds    = Schema::hasTable(DC::TABLE_ORDERS) ? DB::table(DC::TABLE_ORDERS)->pluck('id')->all() : [];
		$taxIds      = Schema::hasTable(DC::TABLE_TAXES) ? DB::table(DC::TABLE_TAXES)->pluck('id')->all() : [];
		$psUnitIds   = Schema::hasTable(DC::TABLE_PROD_SERV_UNITS) ? DB::table(DC::TABLE_PROD_SERV_UNITS)->pluck('id')->all() : [];
		$loanIds     = Schema::hasTable(DC::TABLE_LN) ? DB::table(DC::TABLE_LN)->pluck('id')->all() : [];
		$contractIds = Schema::hasTable(DC::TABLE_CONTRACTS) ? DB::table(DC::TABLE_CONTRACTS)->pluck('id')->all() : [];
		$payslipIds  = Schema::hasTable(DC::TABLE_PAY_SLP) ? DB::table(DC::TABLE_PAY_SLP)->pluck('id')->all() : [];

		// Saco ponderado de status
		$statusBag = [
			PaymentStatus::Pending->value,
			PaymentStatus::Processing->value,
			PaymentStatus::Authorized->value,
			PaymentStatus::Completed->value,
			PaymentStatus::Completed->value,
			PaymentStatus::Failed->value,
			PaymentStatus::Refunded->value,
			PaymentStatus::PartiallyRefunded->value,
			PaymentStatus::Cancelled->value,
			PaymentStatus::Declined->value,
			PaymentStatus::Expired->value,
			PaymentStatus::Disputed->value,
		];

		// Saco de tipos e métodos
		$typeBag = array_map(fn($v) => $v->value, PaymentType::cases());
		$pmLabelBag = array_map(fn($v) => $v->value, PaymentMethod::cases());

		$now  = Carbon::now();
		$rows = [];
		$codes = [];
		$totalPlanned = 0;

		foreach ($invoices as $inv) {
			// Quantos pagamentos para esta fatura?
			$desired = fake()->numberBetween($perInvMin, $perInvMax);
			if ($targetCount > 0 && $totalPlanned + $desired > $targetCount) {
				$desired = max(0, $targetCount - $totalPlanned);
			}
			if ($desired === 0) continue;

			// Estratégia de valores
			$invAmount = (float) ($inv->amount ?? 0.0);
			$payVals   = [];

			if ($invAmount > 0 && ($forceFull || fake()->boolean(50))) {
				// Divide aleatoriamente para somar ~ ao valor da fatura
				$parts = max(1, $desired);
				$weights = [];
				for ($i = 0; $i < $parts; $i++) {
					$weights[] = max(1, fake()->numberBetween(1, 100));
				}
				$sumW = array_sum($weights);
				for ($i = 0; $i < $parts; $i++) {
					$v = round($invAmount * ($weights[$i] / $sumW), 2);
					$payVals[] = $v;
				}
				// Ajuste final para casar centavos
				$diff = round($invAmount - array_sum($payVals), 2);
				if (abs($diff) >= 0.01) {
					$payVals[array_key_last($payVals)] = round($payVals[array_key_last($payVals)] + $diff, 2);
				}
			} else {
				// Pagamentos independentes (parciais)
				for ($i = 0; $i < $desired; $i++) {
					$payVals[] = round(fake()->randomFloat(2, 30, 1200), 2);
				}
			}

			// Seleciona conta bancária obrigatória
			$bacc = Arr::random($bankAccIds);

			for ($i = 0; $i < $desired; $i++) {
				try {
					// Encerra se já atingiu alvo global
					if ($targetCount > 0 && $totalPlanned >= $targetCount) {
						break 2;
					}

					// Gera código único
					$code = $this->makeCode($codes);
					$codes[$code] = true;

					// Datas coerentes
					$createdAt = $now->subDays(fake()->numberBetween(0, 40))->subMinutes(fake()->numberBetween(0, 1440));
					$date      = $createdAt->addMinutes(fake()->numberBetween(0, 1440))->toDateString();
					$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 2880));

					// Status / reconciliação coerentes
					$status = Arr::random($statusBag);
					$isCompletedLike = in_array($status, [
						PaymentStatus::Completed->value,
						PaymentStatus::Refunded->value,
						PaymentStatus::PartiallyRefunded->value,
					], true);

					$reconciledAt = $maybe(function () use ($isCompletedLike, $updatedAt) {
						return $isCompletedLike ? $updatedAt->toDateTimeString() : null;
					});
					$reconciledBy = $reconciledAt && $userIds ? $maybe(fn() => Arr::random($userIds)) : null;

					// Tipo / método de pagamento
					$payType  = Arr::random($typeBag);
					$pmInt    = fake()->numberBetween(0, 1); // compat
					$pmLabel  = Arr::random($pmLabelBag);
					$trfType  = $maybe(fn() => Arr::random(array_map(fn($v) => $v->value, TransferType::cases())));

					// IDs opcionais / FKs
					$catId    = $maybe(fn() => $catIds ? Arr::random($catIds) : null);
					$orderId  = $maybe(fn() => $orderIds ? Arr::random($orderIds) : null);
					$taxId    = $maybe(fn() => $taxIds ? Arr::random($taxIds) : null);
					$psUnit   = $maybe(fn() => $psUnitIds ? Arr::random($psUnitIds) : null);
					$loanId   = $maybe(fn() => $loanIds ? Arr::random($loanIds) : null);
					$ctrId    = $maybe(fn() => $contractIds ? Arr::random($contractIds) : null);
					$payslip  = $maybe(fn() => $payslipIds ? Arr::random($payslipIds) : null);

					// Anexos / metadados
					$attachments = $maybe(function () {
						return [
							['name' => fake()->lexify('recibo-????.pdf'), 'url' => fake()->url()],
							['name' => fake()->lexify('comprovante-????.png'), 'url' => fake()->url()],
						];
					});
					$rcpMeta = $maybe(function () {
						return [
							'hash' => Str::lower(Str::random(16)),
							'issuer' => fake()->company(),
							'channel' => Arr::random(['pix', 'ted', 'boleto', 'cash', 'card']),
						];
					});
					$reconcileRules = $maybe(function () {
						return [
							'window_days' => fake()->numberBetween(1, 10),
							'match' => Arr::random(['amount+date', 'amount+ref', 'strict']),
						];
					});
					$taxesList = $maybe(function () {
						$items = [
							['name' => 'ISS', 'rate' => 2.00],
							['name' => 'IOF', 'rate' => 0.38],
							['name' => 'IRRF', 'rate' => 1.50],
						];
						return Arr::random($items, fake()->numberBetween(1, 2));
					});

					// Coerência de moeda com a fatura
					$currencyId = $inv->currency_id ?: 'BRL';
					$legacyCurrency = $maybe(fn() => $currencyId); // mantém compatibilidade

					// Valores
					$amount   = isset($payVals[$i]) ? (float) $payVals[$i] : round(fake()->randomFloat(2, 30, 1200), 2);
					$svcFee   = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.03), 2));
					$taxFee   = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.02), 2));

					// Flags / campos diversos
					$isScd    = $maybe(fn() => fake()->boolean(25));
					$canCgbk  = $maybe(fn() => fake()->boolean(10));
					$purpose  = $maybe(fn() => fake()->numerify('3##'));
					$purposeD = $maybe(fn() => fake()->sentence(6));
					$notes    = $maybe(fn() => fake()->realText(120));
					$desc     = 'Pagamento da fatura ' . $inv->id;
					$ref      = $maybe(fn() => 'REF-' . strtoupper(Str::random(6)));
					$addRec   = $maybe(fn() => Arr::random(['yes', 'no']));
					$nInst    = $maybe(fn() => fake()->numberBetween(1, 6));
					$currInst = $nInst ? min($nInst, fake()->numberBetween(1, (int) $nInst)) : $maybe(fn() => 1);

					$rows[] = [
						'id'              => (string) Str::uuid(),
						'code'            => $code,

						// FK obrigatória (HasPaymentColumns com nullableInvoice=false usa invoice_id)
						BC::COL_INV_ID    => $inv->id,

						// Emissão financeira
						BC::COL_CUR_ID    => $currencyId,
						'amount'          => $amount,
						BC::COL_SVC_FEE   => $svcFee,
						BC::COL_TXS_FEE   => $taxFee,
						'reference'       => $ref,
						'description'     => $desc,
						'notes'           => $notes,
						'attachments'     => $json($attachments),
						BC::COL_TC        => $json($maybe(fn() => ['no_refund' => true, 'due_days' => fake()->numberBetween(5, 30)])),
						BC::COL_AUTORCC   => $maybe(fn() => fake()->boolean(20)),
						BC::COL_RCC_RL    => $json($reconcileRules),

						// Pagamento em si
						'date'            => $date,
						BC::COL_IS_SCD    => $isScd,
						BC::COL_CAN_CHG_BK => $canCgbk,
						BC::COL_PPS_CD    => $purpose,
						BC::COL_TRF_TP    => $trfType,
						BC::COL_PPS_DS    => $purposeD,
						BC::COL_TXS_LST   => $json($taxesList),
						BC::COL_PAY_MTD   => $pmInt,
						BC::COL_PAY_MTD_LB => $pmLabel,
						'status'          => $status,
						BC::COL_N_INTR    => $nInst,
						BC::COL_CURR_N_INTR => $currInst,
						BC::COL_RCC_AT    => $reconciledAt,
						BC::COL_RCC_BY    => $reconciledBy,

						// Conclusão (account_id é NOT NULL pelo seu uso: nullableAcc=false)
						BC::COL_BACC_ID   => $bacc,
						BC::COL_CAT_ID    => $catId,
						BC::COL_ADD_RCP   => $addRec,
						BC::COL_RCP_MD    => $json($rcpMeta),

						// Outras relações opcionais
						'contract'        => $ctrId,
						'loan'            => $loanId,
						'payslip'         => $payslip,
						BC::COL_PRD_SV_UNT => $psUnit,
						BC::COL_OD_ID     => $orderId,
						BC::COL_TAX_ID    => $taxId,

						// Legado / compat
						'currency'        => $legacyCurrency,
						'receipt'         => $maybe(fn() => strtoupper(Str::random(10))),

						// Tipo de pagamento (enum label)
						BC::COL_PAY_TP    => $payType,

						// Audit
						DC::COL_TABLE_CREATOR => $maybe(fn() => $userIds ? Arr::random($userIds) : null),
						DC::COL_TABLE_UPDATER => $maybe(fn() => $userIds ? Arr::random($userIds) : null),
						'created_at'      => $createdAt->toDateTimeString(),
						'updated_at'      => $updatedAt->toDateTimeString(),
					];

					$totalPlanned++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}

		if (!$rows) {
			$this->command?->info('Nenhum registro a inserir.');
			return;
		}

		// Insert em chunks
		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, 500) as $chunk) {
				DB::table(DC::TABLE_INV_PAY)->insert($chunk);
			}
		});

		$this->command?->info("InvoicePaymentSeeder: {$totalPlanned} registros inseridos.");
	}

	private function makeCode(array $existing): string
	{
		do {
			$code = 'IPAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));
		} while (isset($existing[$code]) || DB::table(DC::TABLE_INV_PAY)->where('code', $code)->exists());
		return $code;
	}
}
