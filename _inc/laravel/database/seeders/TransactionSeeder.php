<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Enums\TransferType;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
	/**
	 * Parâmetros (ENV e CLI):
	 *
	 *  --count=INT                     Limita o total aproximado de transações.
	 *
	 *  TRX_OPTIONALITY=0..1            Probabilidade média de preencher campos opcionais (default: 0.65).
	 *  TRX_PER_BILL_MIN=INT            Transações mín. por Bill (default: 0)
	 *  TRX_PER_BILL_MAX=INT            Transações máx. por Bill (default: 2)
	 *  TRX_PER_INV_MIN=INT             Transações mín. por Invoice (default: 0)
	 *  TRX_PER_INV_MAX=INT             Transações máx. por Invoice (default: 2)
	 *  TRX_PER_POS_MIN=INT             Transações mín. por POS (default: 0)
	 *  TRX_PER_POS_MAX=INT             Transações máx. por POS (default: 2)
	 *  TRX_OTHER_COUNT=INT             Quantidade extra de transações do tipo "other" (default: 20)
	 *  TRX_MAX_AMT=number              Valor máximo para amount (default: 8000.00)
	 */
	public function run(): void
	{
		// Tabelas essenciais
		if (!Schema::hasTable(DC::TABLE_TRS)) {
			$this->command?->warn('Tabela de transactions ausente. Seeder abortado.');
			return;
		}

		// Coleções base
		$bankAccounts = Schema::hasTable(DC::TABLE_BANK_ACC) ? DB::table(DC::TABLE_BANK_ACC)->pluck('id')->all() : [];
		$users        = Schema::hasTable(DC::TABLE_USERS)    ? DB::table(DC::TABLE_USERS)->pluck('id')->all()      : [];

		$bills    = Schema::hasTable(DC::TABLE_BILLS) ? DB::table(DC::TABLE_BILLS)->pluck('id')->all() : [];
		$invoices = Schema::hasTable(DC::TABLE_INVS)  ? DB::table(DC::TABLE_INVS)->pluck('id')->all()  : [];
		$poses    = Schema::hasTable(DC::TABLE_POS ?? 'pos') && Schema::hasColumn(DC::TABLE_POS ?? 'pos', 'id')
			? DB::table(DC::TABLE_POS ?? 'pos')->pluck('id')->all()
			: []; // fallback de nome de tabela caso a constante não exista

		// Parâmetros
		$opt = (float) env('TRX_OPTIONALITY', 0.65);
		$opt = max(0.0, min(1.0, $opt));

		$perBillMin = (int) env('TRX_PER_BILL_MIN', 0);
		$perBillMax = (int) env('TRX_PER_BILL_MAX', 2);
		$perInvMin  = (int) env('TRX_PER_INV_MIN', 0);
		$perInvMax  = (int) env('TRX_PER_INV_MAX', 2);
		$perPosMin  = (int) env('TRX_PER_POS_MIN', 0);
		$perPosMax  = (int) env('TRX_PER_POS_MAX', 2);

		$otherCount = (int) env('TRX_OTHER_COUNT', 20);
		$maxAmount  = (float) env('TRX_MAX_AMT', 8000.00);
		$target     = (int) ($this->command?->option('count') ?? 0);

		$clampRange = function (int $min, int $max): array {
			if ($min < 0) $min = 0;
			if ($max < $min) $max = $min;
			return [$min, $max];
		};
		[$perBillMin, $perBillMax] = $clampRange($perBillMin, $perBillMax);
		[$perInvMin,  $perInvMax] = $clampRange($perInvMin,  $perInvMax);
		[$perPosMin,  $perPosMax] = $clampRange($perPosMin,  $perPosMax);

		$maybe = fn(callable $fn) => fake()->boolean((int) round($opt * 100)) ? $fn() : null;
		$json  = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$rows = [];
		$inserted = 0;
		$now = Carbon::now();

		// Helper para incluir colunas somente se existirem
		$addIfHas = function (&$row, string $column, $value) {
			if ($value === null) return;
			if (Schema::hasColumn(DC::TABLE_TRS, $column)) {
				$row[$column] = $value;
			}
		};

		// --------- Geradores por origem (Bill / Invoice / POS) ----------
		$genFor = function (array $ids, TransactionType $type, int $min, int $max) use (
			&$rows,
			&$inserted,
			$target,
			$maybe,
			$json,
			$now,
			$bankAccounts,
			$users,
			$maxAmount,
			$addIfHas
		) {
			foreach ($ids as $srcId) {
				if ($target > 0 && $inserted >= $target) break;

				$count = fake()->numberBetween($min, $max);
				for ($i = 0; $i < $count; $i++) {
					if ($target > 0 && $inserted >= $target) break;

					// Valores
					$amount = round(fake()->randomFloat(2, 20.00, $maxAmount), 2);
					$svcFee = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.03), 2));
					$taxFee = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.05), 2));

					// Datas/schedule
					$createdAt = $now->subDays(fake()->numberBetween(0, 120))->subMinutes(fake()->numberBetween(0, 1_440));
					$executed  = $maybe(fn() => $createdAt->addDays(fake()->numberBetween(0, 10))->addMinutes(fake()->numberBetween(0, 1_440)));
					$completed = $maybe(fn() => $executed ? $executed->addMinutes(fake()->numberBetween(5, 600)) : null);
					$canceled  = $completed ? null : $maybe(fn() => $createdAt->addDays(fake()->numberBetween(0, 15)));

					// JSON e campos opcionais
					$taxesList = $maybe(function () {
						$pool = [
							['name' => 'ISS',    'rate' => 2.00],
							['name' => 'PIS',    'rate' => 1.65],
							['name' => 'COFINS', 'rate' => 7.60],
						];
						return Arr::random($pool, fake()->numberBetween(1, 2));
					});

					$attachments = $maybe(fn() => [
						['path' => fake()->lexify('docs/doc-????.pdf'), 'extension' => 'pdf'],
						['path' => fake()->lexify('imgs/proof-????.png'), 'extension' => 'png'],
					]);

					$reconcileRules = $maybe(fn() => [
						'auto_match'   => fake()->boolean(60),
						'amount_delta' => fake()->randomFloat(2, 0.00, 5.00),
						'date_tolerance_days' => fake()->randomElement([0, 1, 2, 3]),
					]);

					// Flags e metadados de pagamento
					$methodCode  = fake()->randomElement([0, 1]);
					$methodLabel = Arr::random(PaymentMethod::values()); // compatível com ENUM da migration
					$ppsCode     = $maybe(fn() => fake()->randomElement(['300', '301', '302']));
					$trfType     = $maybe(fn() => Arr::random(TransferType::values()));
					$ppsDesc     = $maybe(fn() => fake()->sentence());

					// Relacionamentos opcionais
					$accountId = $maybe(fn() => $bankAccounts ? Arr::random($bankAccounts) : null);
					$userId    = $maybe(fn() => $users ? Arr::random($users) : null);
					$rccBy     = $maybe(fn() => $users ? Arr::random($users) : null);

					$uType = Arr::random(['admin', 'employee', 'client', 'system']);

					// Montagem do registro
					$row = [
						'id'                 => (string) Str::uuid(),
						'account'            => $accountId,
						UC::COL_USER_ID      => $userId,
						UC::COL_U_TP         => $uType,
						BC::COL_PAY_TP       => $type->value,
						BC::COL_PAY_ID       => $srcId,
						'category'           => $type->value, // compat.
						BC::COL_CUR_ID       => config('app.currency', 'BRL'),
						'amount'             => $amount,
						BC::COL_SVC_FEE      => $svcFee,
						BC::COL_TXS_FEE      => $taxFee,
						BC::COL_TXS_LST      => $json($taxesList),
						BC::COL_PAY_MTD      => $methodCode,
						BC::COL_PAY_MTD_LB   => $methodLabel,
						BC::COL_N_INTR       => $maybe(fn() => fake()->numberBetween(1, 6)),
						BC::COL_CURR_N_INTR  => $maybe(fn() => fake()->numberBetween(1, 6)),
						BC::COL_IS_SCD       => $maybe(fn() => fake()->boolean(15)),
						BC::COL_CAN_CHG_BK   => $maybe(fn() => fake()->boolean(10)),
						BC::COL_PPS_CD       => $ppsCode,
						BC::COL_TRF_TP       => $trfType,
						BC::COL_PPS_DS       => $ppsDesc,
						BC::COL_TC           => $json($maybe(fn() => ['notes' => fake()->sentence()])),
						BC::COL_AUTORCC      => $maybe(fn() => fake()->boolean(30)),
						BC::COL_RCC_RL       => $json($reconcileRules),
						BC::COL_RCC_AT       => $maybe(fn() => $completed?->addMinutes(fake()->numberBetween(0, 240))?->toDateTimeString()),
						BC::COL_RCC_BY       => $rccBy,
						'reference'          => $maybe(fn() => 'TRX-' . strtoupper(Str::random(8))),
						'description'        => 'Transação gerada pelo seeder',
						'notes'              => $maybe(fn() => fake()->realText(120)),
						'attachments'        => $json($attachments),
						'type'               => $maybe(fn() => Arr::random(['gateway_a', 'gateway_b', 'manual', 'reconciliation'])),
						'date'               => $createdAt->toDateString(),
						'created_at'         => $createdAt->toDateTimeString(),
						'updated_at'         => $createdAt->addMinutes(fake()->numberBetween(0, 10_080))->toDateTimeString(),
					];

					// Campos de schedule (trait AcceptsSchedule)
					$row[BC::COL_SCHD_TRF_TS] = $maybe(fn() => $now->addDays(fake()->numberBetween(0, 20))->toDateTimeString());
					$row[BC::COL_EXC_AT]      = $executed?->toDateTimeString();
					$row[BC::COL_CNC_AT]      = $canceled?->toDateTimeString();
					$row[BC::COL_CMP_AT]      = $completed?->toDateTimeString();
					$row[BC::COL_CNC_RS]      = $canceled ? fake()->sentence() : null;

					// Colunas de auditoria (se existirem)
					$addIfHas($row, DC::COL_TABLE_CREATOR, $maybe(fn() => $users ? Arr::random($users) : null));
					$addIfHas($row, DC::COL_TABLE_UPDATER, $maybe(fn() => $users ? Arr::random($users) : null));

					// Remover apenas nulls; manter 0/false
					$rows[] = array_filter($row, static fn($v) => $v !== null);
					$inserted++;
				}
			}
		};

		// Gerar por fonte
		$genFor($bills,    TransactionType::Bill,    $perBillMin, $perBillMax);
		$genFor($invoices, TransactionType::Invoice, $perInvMin,  $perInvMax);
		$genFor($poses,    TransactionType::Pos,     $perPosMin,  $perPosMax);

		// Extras "other"
		$genOther = function (int $qtd) use (&$rows, &$inserted, $target, $maybe, $json, $now, $bankAccounts, $users, $maxAmount, $addIfHas) {
			for ($i = 0; $i < $qtd; $i++) {
				if ($target > 0 && $inserted >= $target) break;

				$amount   = round(fake()->randomFloat(2, 10.00, $maxAmount), 2);
				$svcFee   = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.02), 2));
				$taxFee   = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.04), 2));
				$createdAt = $now->subDays(fake()->numberBetween(0, 90))->subMinutes(fake()->numberBetween(0, 720));

				$row = [
					'id'               => (string) Str::uuid(),
					'account'          => $maybe(fn() => $bankAccounts ? Arr::random($bankAccounts) : null),
					UC::COL_USER_ID    => $maybe(fn() => $users ? Arr::random($users) : null),
					UC::COL_U_TP       => Arr::random(['system', 'admin', 'employee']),
					BC::COL_PAY_TP     => TransactionType::Other->value,
					BC::COL_PAY_ID     => null,
					'category'         => TransactionType::Other->value,
					BC::COL_CUR_ID     => config('app.currency', 'BRL'),
					'amount'           => $amount,
					BC::COL_SVC_FEE    => $svcFee,
					BC::COL_TXS_FEE    => $taxFee,
					BC::COL_PAY_MTD    => fake()->randomElement([0, 1]),
					BC::COL_PAY_MTD_LB => Arr::random(PaymentMethod::values()),
					'description'      => 'Lançamento avulso (other) gerado pelo seeder',
					'reference'        => $maybe(fn() => 'TRX-' . strtoupper(Str::random(8))),
					'attachments'      => $json($maybe(fn() => [
						['path' => fake()->lexify('other/att-????.pdf'), 'extension' => 'pdf'],
					])),
					'date'             => $createdAt->toDateString(),
					'created_at'       => $createdAt->toDateTimeString(),
					'updated_at'       => $createdAt->addMinutes(fake()->numberBetween(0, 10080))->toDateTimeString(),
				];

				// schedule
				$row[BC::COL_SCHD_TRF_TS] = $maybe(fn() => $now->addDays(fake()->numberBetween(0, 10))->toDateTimeString());

				// auditoria condicional
				$addIfHas($row, DC::COL_TABLE_CREATOR, $maybe(fn() => $users ? Arr::random($users) : null));
				$addIfHas($row, DC::COL_TABLE_UPDATER, $maybe(fn() => $users ? Arr::random($users) : null));

				$rows[] = array_filter($row, static fn($v) => $v !== null);
				$inserted++;
			}
		};

		if ($target > 0) {
			$remaining = max(0, $target - $inserted);
			$genOther(min($remaining, $otherCount));
		} else {
			$genOther($otherCount);
		}

		if (!$rows) {
			$this->command?->info('TransactionSeeder: nada a inserir.');
			return;
		}

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, 1000) as $chunk) {
				DB::table(DC::TABLE_TRS)->insert($chunk);
			}
		});

		$this->command?->info("TransactionSeeder: {$inserted} transações inseridas em " . DC::TABLE_TRS . ".");
	}
}
