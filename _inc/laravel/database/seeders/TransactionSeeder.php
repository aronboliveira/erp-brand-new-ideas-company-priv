<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Enums\TransferType;
use App\Models\Transaction;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
	// Parâmetros fixos (sem env)
	private const OPTIONALITY  = 0.65;
	private const PER_BILL_MIN = 0;
	private const PER_BILL_MAX = 2;
	private const PER_INV_MIN  = 0;
	private const PER_INV_MAX  = 2;
	private const PER_POS_MIN  = 0;
	private const PER_POS_MAX  = 2;

	// Qtd máxima de "others" quando precisar complementar a meta
	private const OTHER_MAX  = 256;
	private const MAX_AMOUNT = 8000.00;

	private const SECONDS_LIMIT = 3 * 10 ** 2; // 5 minutes

	/**
	 * Opção CLI:
	 *  --count=INT   Limita o total aproximado de transações (mas mantendo 64 × n como piso).
	 */
	public function run(): void
	{
		$clock = microtime(true);
		if (!Schema::hasTable(DC::TABLE_TRS)) {
			$this->command?->warn('TransactionSeeder: tabela de transactions ausente. Seeder abortado.');
			return;
		}

		// --------- Coleções base usando *models* sempre que possível ---------

		// BankAccount
		$bankAccounts = $this->pluckModelIds(\App\Models\BankAccount::class);
		if (!$bankAccounts && Schema::hasTable(DC::TABLE_BANK_ACC)) {
			$bankAccounts = DB::table(DC::TABLE_BANK_ACC)->pluck('id')->all();
		}

		// User
		$users = $this->pluckModelIds(\App\Models\User::class);
		if (!$users && Schema::hasTable(DC::TABLE_USERS)) {
			$users = DB::table(DC::TABLE_USERS)->pluck('id')->all();
		}

		// Bills (ainda via tabela; não foi pedido ênfase em Bill aqui)
		$bills = Schema::hasTable(DC::TABLE_BILLS)
			? DB::table(DC::TABLE_BILLS)->pluck('id')->all()
			: [];

		// Invoice via model
		$invoices = $this->pluckModelIds(\App\Models\Invoice::class);
		if (!$invoices && Schema::hasTable(DC::TABLE_INVS)) {
			$invoices = DB::table(DC::TABLE_INVS)->pluck('id')->all();
		}

		// Contracts
		$contracts = $this->pluckModelIds(\App\Models\Contract::class);

		// Loans
		$loans = $this->pluckModelIds(\App\Models\Loan::class);

		// ProductServiceUnit
		$productUnits = $this->pluckModelIds(\App\Models\ProductServiceUnit::class);

		// Payslip
		$payslips = $this->pluckModelIds(\App\Models\Payslip::class);

		// Payment (genérico)
		$payments = $this->pluckModelIds(\App\Models\Payment::class);

		// Tabela de POS pode estar com nome em constantes ou legado simples
		$posTable = \defined(DC::class . '::TABLE_POS') ? DC::TABLE_POS : 'pos';
		$poses    = Schema::hasTable($posTable)
			? DB::table($posTable)->pluck('id')->all()
			: [];

		// Base para piso 64 × n
		$baseCount = \count($bills) + \count($invoices) + \count($poses);
		if ($baseCount <= 0) {
			// Para não ficar sem mocks se não houver relacionamentos ainda
			$baseCount = 1;
		}

		// Regra 64 × n como piso, com override aproximado por --count
		$cliCount = 0;
		if (
			$this->command instanceof \Illuminate\Console\Command
			&& $this->command->hasOption('count')
		) {
			$cliCount = (int) $this->command->option('count');
		}

		$minTarget = 8 * $baseCount;
		$target    = max($minTarget, $cliCount > 0 ? $cliCount : $minTarget);

		$opt       = self::OPTIONALITY;
		$maxAmount = self::MAX_AMOUNT;
		$now       = Carbon::now();

		$maybe = static function (callable $fn) use ($opt) {
			return fake()->boolean((int) round($opt * 100)) ? $fn() : null;
		};

		$json = static function ($v) {
			return $v === null
				? null
				: json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		};

		// Flags para auditoria (evita erro se colunas forem alteradas no futuro)
		$hasCreatorCol = Schema::hasColumn(DC::TABLE_TRS, DC::COL_TABLE_CREATOR);
		$hasUpdaterCol = Schema::hasColumn(DC::TABLE_TRS, DC::COL_TABLE_UPDATER);

		$inserted = 0;
		$stopWarned = false;
		DB::transaction(function () use (
			$bankAccounts,
			$users,
			$bills,
			$invoices,
			$poses,
			$contracts,
			$loans,
			$productUnits,
			$payslips,
			$payments,
			$baseCount,
			$target,
			$maxAmount,
			$now,
			$maybe,
			$json,
			$hasCreatorCol,
			$hasUpdaterCol,
			&$inserted,
			&$clock,
			&$stopWarned
		) {
			// Helper único de criação de uma linha de transação
			$buildRow = function (
				TransactionType $type,
				?string $payId,
				?string $accountId,
				?string $userId,
				string $userType,
				?string $sourceType,
				Carbon $createdAt
			) use (
				$bankAccounts,
				$users,
				$contracts,
				$loans,
				$productUnits,
				$invoices,
				$payslips,
				$payments,
				$maxAmount,
				$now,
				$maybe,
				$json,
				$hasCreatorCol,
				$hasUpdaterCol,
				&$clock,
				&$stopWarned
			): array {
				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? (self::SECONDS_LIMIT * 0.8) : ((6 * 10 ** 2) * 0.8))) {
					if (!$stopWarned) {
						Log::warning(self::class . ' seeding time limit reached, stopping early');
						$stopWarned = true;
					}
					return [];
				}
				// Se não veio payId e houver Payment para transações "other",
				// podemos referenciar um Payment real.
				if ($payId === null && $type === TransactionType::Other && $payments) {
					$payId = Arr::random($payments);
				}


				$amount   = round(fake()->randomFloat(2, 20.0, $maxAmount), 2);
				$discount = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.20), 2)) ?? 0.00;

				// taxas e serviços
				$svcFee = $maybe(fn() => round($amount * fake()->randomFloat(2, 0.00, 0.03), 2));
				$taxFee = $maybe(fn() => round(($amount - $discount) * fake()->randomFloat(2, 0.00, 0.05), 2));

				// datas principais
				$updatedAt  = $createdAt->addMinutes(fake()->numberBetween(0, 60 * 24 * 30));
				$executedAt = $maybe(fn() => $createdAt->addDays(fake()->numberBetween(0, 10))
					->addMinutes(fake()->numberBetween(0, 24 * 60)));
				$completedAt = $maybe(function () use ($executedAt) {
					if (!$executedAt) {
						return null;
					}
					return $executedAt->addMinutes(fake()->numberBetween(5, 24 * 60));
				});
				$cancelledAt = $completedAt
					? null
					: $maybe(fn() => $createdAt->addDays(fake()->numberBetween(0, 15)));

				// schedule
				$scheduledTs = $maybe(fn() => $now->addDays(fake()->numberBetween(0, 20))
					->addMinutes(fake()->numberBetween(0, 24 * 60)));

				// lista de impostos (para BC::COL_TXS_LST)
				$taxesList = $maybe(function () {
					$pool = [
						['name' => 'ISS',    'rate' => 2.00],
						['name' => 'PIS',    'rate' => 1.65],
						['name' => 'COFINS', 'rate' => 7.60],
					];
					return Arr::random($pool, fake()->numberBetween(1, 2));
				});

				// Anexos
				$attachments = $maybe(fn() => [
					['path' => fake()->lexify('docs/doc-????.pdf'), 'extension' => 'pdf'],
					['path' => fake()->lexify('imgs/proof-????.png'), 'extension' => 'png'],
				]);

				// Regras de conciliação
				$reconcileRules = $maybe(fn() => [
					'auto_match'          => fake()->boolean(60),
					'amount_delta'        => fake()->randomFloat(2, 0.00, 5.00),
					'date_tolerance_days' => fake()->randomElement([0, 1, 2, 3]),
				]);

				// Termos / contrato
				$terms = $maybe(fn() => ['notes' => fake()->sentence()]);

				// Ponteiros relacionais opcionais **usando models**
				$contractId = $maybe(fn() => $contracts ? Arr::random($contracts) : null);
				$loanId     = $maybe(fn() => $loans ? Arr::random($loans) : null);
				$psuId      = $maybe(fn() => $productUnits ? Arr::random($productUnits) : null);

				// Pagamento / transferências
				$methodCode   = fake()->randomElement([0, 1]);
				$methodLabel  = Arr::random(PaymentMethod::values());
				$purposeCode  = $maybe(fn() => fake()->randomElement(['300', '301', '302']));
				$transferType = $maybe(fn() => Arr::random(TransferType::values()));
				$purposeDesc  = $maybe(fn() => fake()->sentence(8));

				$numInstallments    = $maybe(fn() => fake()->numberBetween(1, 12)) ?? 1;
				$currentInstallment = $maybe(fn() => fake()->numberBetween(1, (int) $numInstallments));
				$autoReconcile      = $maybe(fn() => fake()->boolean(35));

				// Status de pagamento
				$status = Arr::random(PaymentStatus::values());

				// Reconciliation
				$reconciledAt = $maybe(function () use ($completedAt, $executedAt, $updatedAt) {
					$base = $completedAt ?? $executedAt ?? $updatedAt;
					return $base?->addMinutes(fake()->numberBetween(0, 24 * 60));
				});

				$reconciledBy = $reconciledAt
					? $maybe(fn() => $users ? Arr::random($users) : null)
					: null;

				// invoice/payslip opcionais **usando models**
				$invoiceId = $maybe(fn() => $invoices ? Arr::random($invoices) : null);
				$payslipId = $maybe(fn() => $payslips ? Arr::random($payslips) : null);

				// Cancel reason
				$cancelReason = $cancelledAt ? fake()->sentence() : null;

				// Auditoria (se existir)
				$creator = $maybe(fn() => $users ? Arr::random($users) : null);
				$updater = $maybe(fn() => $users ? Arr::random($users) : null);

				$row = [
					'account'          => $accountId,
					UC::COL_USER_ID    => $userId,
					UC::COL_U_TP       => $userType,
					BC::COL_PAY_TP     => $type->value,
					BC::COL_PAY_ID     => $payId,
					'category'         => $type->value, // compatibilidade legado
					BC::COL_CUR_ID     => config('app.currency', 'BRL'),
					// HasFinancialIssuingColumns
					'amount'           => $amount,
					'discount'         => $discount,
					BC::COL_SVC_FEE    => $svcFee,
					BC::COL_TXS_FEE    => $taxFee,
					'reference'        => $maybe(fn() => 'TRX-' . strtoupper(Str::random(8))),
					'description'      => 'Transação gerada pelo seeder',
					'notes'            => $maybe(fn() => fake()->realText(120)),
					'attachments'      => $json($attachments),
					BC::COL_TC         => $json($terms),
					BC::COL_AUTORCC    => $autoReconcile,
					BC::COL_RCC_RL     => $json($reconcileRules),
					'contract'         => $contractId,
					'loan'             => $loanId,
					BC::COL_PRD_SV_UNT => $psuId,
					// HasPaymentColumns
					BC::COL_IS_SCD      => $maybe(fn() => fake()->boolean(20)),
					BC::COL_CAN_CHG_BK  => $maybe(fn() => fake()->boolean(10)),
					BC::COL_PPS_CD      => $purposeCode,
					BC::COL_TRF_TP      => $transferType,
					BC::COL_PPS_DS      => $purposeDesc,
					BC::COL_TXS_LST     => $json($taxesList),
					BC::COL_PAY_MTD     => $methodCode,
					BC::COL_PAY_MTD_LB  => $methodLabel,
					'status'            => $status,
					BC::COL_N_INTR      => $numInstallments,
					BC::COL_CURR_N_INTR => $currentInstallment,
					BC::COL_RCC_AT      => $reconciledAt?->toDateTimeString(),
					BC::COL_RCC_BY      => $reconciledBy,
					'invoice'           => $invoiceId,
					'payslip'           => $payslipId,
					// AcceptsSchedule
					BC::COL_SCHD_TRF_TS => $scheduledTs?->toDateTimeString(),
					BC::COL_EXC_AT      => $executedAt?->toDateTimeString(),
					BC::COL_CNC_AT      => $cancelledAt?->toDateTimeString(),
					BC::COL_CMP_AT      => $completedAt?->toDateTimeString(),
					BC::COL_CNC_RS      => $cancelReason,
					// Outras colunas da tabela
					'type'              => $sourceType,
					'date'              => $createdAt->toDateString(),
					'created_at'        => $createdAt->toDateTimeString(),
					'updated_at'        => $updatedAt->toDateTimeString(),
				];

				if ($hasCreatorCol) {
					$row[DC::COL_TABLE_CREATOR] = $creator;
				}
				if ($hasUpdaterCol) {
					$row[DC::COL_TABLE_UPDATER] = $updater;
				}
				(new \Symfony\Component\Console\Output\ConsoleOutput
				)->writeln("Criando Transação de conta {$accountId} pelo usuário {$userId} do tipo {$type->value} no valor de {$amount}");
				return $row;
			};

			// Gerador baseado em um conjunto (bills / invoices / pos)
			$genFor = function (
				array $ids,
				TransactionType $type,
				int $min,
				int $max,
				?string $sourceTypeLabel = null
			) use (
				&$inserted,
				$target,
				$bankAccounts,
				$users,
				$now,
				$buildRow,
				&$clock,
				&$stopWarned
			): void {
				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? (self::SECONDS_LIMIT * 0.8) : ((6 * 10 ** 2) * 0.8))) {
					if (!$stopWarned) {
						Log::warning(self::class . ' seeding time limit reached, stopping early');
						$stopWarned = true;
					}
				}
				if (!$ids) {
					return;
				}

				foreach ($ids as $id) {
					if ($target > 0 && $inserted >= $target) {
						return;
					}

					$count = fake()->numberBetween(max(0, $min), max($min, $max));

					for ($i = 0; $i < $count; $i++) {
						try {
							if ($target > 0 && $inserted >= $target) {
								return;
							}

							$createdAt = $now
								->subDays(fake()->numberBetween(0, 120))
								->subMinutes(fake()->numberBetween(0, 24 * 60));

							$accountId = $bankAccounts
								? Arr::random($bankAccounts)
								: null;

							$userId = $users
								? Arr::random($users)
								: null;

							$userType = Arr::random(['admin', 'employee', 'client', 'system']);

							$row = $buildRow(
								$type,
								$id,
								$accountId,
								$userId,
								$userType,
								$sourceTypeLabel,
								$createdAt
							);

							Transaction::query()->create($row);
							$inserted++;
						} catch (\Exception $e) {
							Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
							continue;
						}
					}
				}
			};

			// 1) Gerar transações ligadas às entidades base
			$genFor($bills,    TransactionType::Bill,    self::PER_BILL_MIN, self::PER_BILL_MAX, 'bill_payment');
			$genFor($invoices, TransactionType::Invoice, self::PER_INV_MIN,  self::PER_INV_MAX,  'invoice_payment');
			$genFor($poses,    TransactionType::Pos,     self::PER_POS_MIN,  self::PER_POS_MAX,  'pos');

			// 2) Complementar com transações "other" até atingir a meta (no máximo OTHER_MAX)
			$remaining  = max(0, $target - $inserted);
			$otherCount = min($remaining, self::OTHER_MAX);

			for ($i = 0; $i < $otherCount; $i++) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					if (!$stopWarned) {
						Log::warning(self::class . ' seeding time limit reached, stopping early');
						$stopWarned = true;
					}
					return;
				}
				try {
					if ($target > 0 && $inserted >= $target) {
						break;
					}

					$createdAt = $now
						->subDays(fake()->numberBetween(0, 90))
						->subMinutes(fake()->numberBetween(0, 24 * 60));

					$accountId = $bankAccounts
						? Arr::random($bankAccounts)
						: null;

					$userId = $users
						? Arr::random($users)
						: null;

					$userType = Arr::random(['system', 'admin', 'employee']);

					$row = $buildRow(
						TransactionType::Other,
						null, // payId: para "other" podemos eventualmente usar Payment real
						$accountId,
						$userId,
						$userType,
						Arr::random(['manual', 'gateway_a', 'gateway_b', 'reconciliation', 'import']),
						$createdAt
					);

					// Ajuste de descrição para destacar que é "other"
					$row['description'] = 'Lançamento avulso (other) gerado pelo seeder';

					if (!empty($row) && count($row) > 0) Transaction::query()->create($row);
					$inserted++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		});

		$this->command?->info("TransactionSeeder: {$inserted} transações inseridas em " . DC::TABLE_TRS . ".");
	}

	/**
	 * Tenta buscar todos os IDs de um Model de forma defensiva.
	 * Se o model não existir ou a query falhar, retorna array vazio.
	 */
	private function pluckModelIds(string $fqcn): array
	{
		if (!class_exists($fqcn)) {
			return [];
		}

		try {
			/** @var \Illuminate\Database\Eloquent\Model $fqcn */
			return $fqcn::query()->pluck('id')->all();
		} catch (\Throwable) {
			return [];
		}
	}
}
