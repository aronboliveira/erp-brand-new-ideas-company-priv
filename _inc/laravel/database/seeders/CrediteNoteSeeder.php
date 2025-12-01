<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\MonthName;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransferType;
use App\Models\CreditNote;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreditNoteSeeder extends Seeder
{
	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_CR_NOTES)) {
			$this->command?->warn('Tabela de credit notes ausente. Abortando.');
			return;
		}

		// Coleta de FKs necessárias
		$customers  = $this->pluckIds(DC::TABLE_CUSTOMERS ?? 'customers');
		$invoices   = $this->pluckIds(DC::TABLE_INVS ?? 'invoices');
		$bills      = $this->pluckIds(DC::TABLE_BILLS ?? 'bills');
		$accounts   = $this->pluckIds(DC::TABLE_BANK_ACC ?? 'bank_accounts');
		$categories = $this->pluckIds(DC::TABLE_PROD_SERV_CATS ?? 'product_service_categories');
		$users      = $this->pluckIds(DC::TABLE_USERS ?? 'users');
		$contracts  = $this->pluckIds(DC::TABLE_CONTRACTS ?? 'contracts');
		$loans      = $this->pluckIds(DC::TABLE_LN ?? 'loans');
		$units      = $this->pluckIds(DC::TABLE_PROD_SERV_UNITS ?? 'product_service_units');
		$payslips   = $this->pluckIds(DC::TABLE_PAY_SLP ?? 'payslips');

		if (!$customers) {
			$this->command?->warn('Sem customers. Abortando CreditNoteSeeder.');
			return;
		}
		if (!$invoices && !$bills) {
			$this->command?->warn('Sem invoices e sem bills para vínculo obrigatório. Abortando CreditNoteSeeder.');
			return;
		}

		// Parâmetros
		$count        = (int) env('CRN_COUNT', 120);
		$optFill      = max(0.0, min(1.0, (float) env('CRN_OPTIONALITY', 0.92)));
		$invoiceRatio = max(0.0, min(1.0, (float) env('CRN_INVOICE_RATIO', 0.75))); // crédito normalmente ligado a invoice
		$cardRatio    = max(0.0, min(1.0, (float) env('CRN_CARD_RATIO', 0.97)));
		$attachRatio  = max(0.0, min(1.0, (float) env('CRN_ATTACH_RATIO', 0.80)));
		$reconRatio   = max(0.0, min(1.0, (float) env('CRN_RECONCILE_RATIO', 0.40)));

		$statusVals = array_map(fn($c) => $c->value, PaymentStatus::cases());
		$methodVals = array_map(fn($c) => $c->value, PaymentMethod::cases());
		$trfVals    = array_map(fn($c) => $c->value, TransferType::cases());
		$monthVals  = array_map(fn($c) => $c->value, MonthName::cases());

		// Janela temporal
		$today   = Carbon::today();
		$fromDay = $today->subDays(150);

		// Conjuntos para unicidade
		$seenRef     = [];
		$seenFiles   = [];
		$seenPan     = [];
		$seenLast4   = [];

		DB::transaction(function () use (
			$count,
			$optFill,
			$invoiceRatio,
			$cardRatio,
			$attachRatio,
			$reconRatio,
			$customers,
			$invoices,
			$bills,
			$accounts,
			$categories,
			$users,
			$contracts,
			$loans,
			$units,
			$payslips,
			$statusVals,
			$methodVals,
			$trfVals,
			$monthVals,
			$fromDay,
			$today,
			&$seenRef,
			&$seenFiles,
			&$seenPan,
			&$seenLast4
		) {
			for ($i = 1; $i <= $count; $i++) {
				$customerId = Arr::random($customers);

				// Vínculo obrigatório: invoice OU bill
				$preferInvoice = $invoices && (!$bills || fake()->boolean((int) round($invoiceRatio * 100)));
				$invoiceId     = $preferInvoice ? Arr::random($invoices) : null;
				$billId        = !$preferInvoice && $bills ? Arr::random($bills) : null;

				$date = fake()->dateTimeBetween($fromDay, $today)->format('Y-m-d');
				$ref  = $this->uniqueRef("CRN-{$date}-", $seenRef);

				$amount   = (float) fake()->randomFloat(2, 50, 7000);
				$discount = (float) fake()->randomFloat(2, 0, round($amount * 0.25, 2));
				$svcFee   = (float) fake()->randomFloat(2, 0, round($amount * 0.02, 2));
				$taxFee   = (float) fake()->randomFloat(2, 0, round($amount * 0.12, 2));
				$currency = config('app.currency', 'BRL');

				$status = Arr::random($statusVals);
				$pmLbl  = Arr::random($methodVals);
				$pmInt  = fake()->boolean() ? 1 : 0; // 0/1 para compatibilidade

				$trfType = Arr::random($trfVals);
				$ppsCode = Arr::random(['300', '101', '202', '710', '905']);

				$taxList = fake()->boolean((int) round($optFill * 100)) ? $this->makeTaxList($ref) : null;
				$attachments = fake()->boolean((int) round($attachRatio * 100)) ? $this->makeAttachments($ref, $seenFiles) : null;
				$terms = fake()->boolean((int) round($optFill * 100))
					? [
						'Scope: credit note adjustment/refund.',
						'Subject to reconciliation and settlement windows.',
						'Non-transferable.',
					]
					: null;

				$autoRcc  = fake()->boolean((int) round($optFill * 100));
				$rccAt    = $autoRcc && fake()->boolean((int) round($reconRatio * 100))
					? now()->subMinutes(fake()->numberBetween(10, 1000))
					: null;
				$rccBy    = $rccAt && $users ? Arr::random($users) : null;
				$rccRules = $autoRcc ? ['match' => 'amount+last4', 'window_days' => 3] : null;

				$accountId  = $accounts ? Arr::random($accounts) : null;
				$categoryId = $categories ? Arr::random($categories) : null;
				$contract   = $contracts ? Arr::random($contracts) : null;
				$loan       = $loans     ? Arr::random($loans)     : null;
				$unit       = $units     ? Arr::random($units)     : null;
				$payslip    = $payslips  ? Arr::random($payslips)  : null;

				$nInst  = fake()->numberBetween(1, 12);
				$curInst = fake()->numberBetween(1, $nInst);

				// Cartão (quase sempre)
				$card = null;
				if (fake()->boolean((int) round($cardRatio * 100))) {
					$flag   = Arr::random(['visa', 'mastercard', 'amex', 'elo', 'hipercard']);
					$holder = strtoupper(fake()->firstName() . ' ' . fake()->lastName());
					$year   = (int) now()->format('Y') + fake()->numberBetween(0, 5);
					$month  = Arr::random($monthVals);

					$pan   = $this->uniquePan(fake()->numerify('4###############'), $seenPan);
					$last4 = substr(preg_replace('/\D+/', '', $pan), -4) ?: str_pad((string) fake()->randomNumber(4), 4, '0', STR_PAD_LEFT);
					$this->ensureUnique($last4, $seenLast4);

					$card = compact('flag', 'holder', 'year', 'month', 'pan', 'last4');
				}

				$note = new CreditNote();

				$payload = [
					BC::COL_CST_ID       => $customerId,
					'invoice'            => $invoiceId,
					BC::COL_BL_ID        => $billId,

					'date'               => $date,
					'amount'             => $amount,
					'discount'           => $discount,
					BC::COL_CUR_ID       => $currency,
					'reference'          => $ref,
					'description'        => "Credit note {$ref}",
					'notes'              => fake()->boolean((int) round($optFill * 100)) ? fake()->sentence(12) : null,
					'attachments'        => $attachments,
					BC::COL_TC           => $terms,

					'status'             => $status,
					BC::COL_N_INTR       => $nInst,
					BC::COL_CURR_N_INTR  => $curInst,

					BC::COL_BACC_ID      => $accountId,
					BC::COL_CAT_ID       => $categoryId,
					BC::COL_ADD_RCP      => 'receipt_attached',

					BC::COL_SVC_FEE      => $svcFee,
					BC::COL_TXS_FEE      => $taxFee,
					BC::COL_TXS_LST      => $taxList,

					BC::COL_PAY_MTD      => $pmInt,
					BC::COL_PAY_MTD_LB   => $pmLbl,
					BC::COL_TRF_TP       => $trfType,
					BC::COL_PPS_CD       => $ppsCode,
					BC::COL_PPS_DS       => fake()->boolean((int) round($optFill * 100)) ? 'refund/adjustment' : null,

					BC::COL_IS_SCD       => fake()->boolean(8),
					BC::COL_CAN_CHG_BK   => fake()->boolean(4),
					BC::COL_AUTORCC      => $autoRcc,
					BC::COL_RCC_RL       => $rccRules,
					BC::COL_RCC_AT       => $rccAt,
					BC::COL_RCC_BY       => $rccBy,

					'contract'           => $contract,
					'loan'               => $loan,
					BC::COL_PRD_SV_UNT   => $unit,
					'payslip'            => $payslip,
				];

				foreach ($payload as $k => $v) {
					if ($v !== null) {
						$note->{$k} = $v;
					}
				}

				if ($card) {
					$note->{BC::COL_CD_FLG} = $card['flag'];
					$note->{BC::COL_CD_HNM} = $card['holder'];
					$note->{BC::COL_CD_EX_Y} = (string) $card['year'];
					$note->{BC::COL_CD_EX_M} = $card['month'];
					$note->{BC::COL_CD_NB}   = $card['pan'];
					$note->{BC::COL_CD_DG}   = $card['last4'];
				}

				// Garantia final do vínculo (modelo exige um dos dois)
				if (!$note->{'invoice'} && !$note->{BC::COL_BL_ID}) {
					if ($invoices) {
						$note->{'invoice'} = Arr::random($invoices);
					} else {
						$note->{BC::COL_BL_ID} = Arr::random($bills);
					}
				}

				// Criador/Atualizador (se existirem colunas)
				if (Schema::hasColumn(DC::TABLE_CR_NOTES, DC::COL_TABLE_CREATOR) && $users) {
					$note->{DC::COL_TABLE_CREATOR} = Arr::random($users);
				}
				if (Schema::hasColumn(DC::TABLE_CR_NOTES, DC::COL_TABLE_UPDATER) && $users) {
					$note->{DC::COL_TABLE_UPDATER} = Arr::random($users);
				}

				$note->save();
			}
		});

		$this->command?->info("CreditNoteSeeder: {$count} registros inseridos.");
	}

	/** Utils */

	private function pluckIds(string $table): array
	{
		if (!Schema::hasTable($table)) return [];
		return DB::table($table)->limit(5000)->pluck('id')->filter()->values()->all();
	}

	private function uniqueRef(string $prefix, array &$seen): string
	{
		do {
			$ref = $prefix . substr(Str::uuid()->toString(), 0, 10);
		} while (isset($seen[$ref]));
		$seen[$ref] = true;
		return $ref;
	}

	private function makeTaxList(string $seed): array
	{
		$items = [];
		$n = fake()->numberBetween(1, 3);
		for ($i = 0; $i < $n; $i++) {
			$label = 'TAX-' . substr(sha1($seed . $i . Str::uuid()->toString()), 0, 6);
			$items[] = ['name' => $label, 'rate' => fake()->randomFloat(2, 0.5, 7.5)];
		}
		return $items;
	}

	private function makeAttachments(string $seed, array &$seenFiles): array
	{
		$out = [];
		$n = fake()->numberBetween(1, 3);
		for ($i = 0; $i < $n; $i++) {
			do {
				$name = 'rcpt-' . $i . '-' . substr(sha1($seed . $i . Str::uuid()->toString()), 0, 10) . '.pdf';
			} while (isset($seenFiles[$name]));
			$seenFiles[$name] = true;

			$out[] = [
				'path'      => "/docs/credit-notes/{$name}",
				'extension' => 'pdf',
				'label'     => 'Receipt PDF',
			];
		}
		return $out;
	}

	private function uniquePan(string $pattern, array &$seenPan): string
	{
		do {
			$candidate = preg_replace('/\D+/', '', fake()->numerify($pattern)) ?: fake()->numerify('4###############');
		} while (isset($seenPan[$candidate]));
		$seenPan[$candidate] = true;
		return $candidate;
	}

	private function ensureUnique(string $key, array &$set): void
	{
		if (!isset($set[$key])) {
			$set[$key] = true;
			return;
		}
		$c = 1;
		while (isset($set[$key . $c])) $c++;
		$set[$key . $c] = true;
	}
}
