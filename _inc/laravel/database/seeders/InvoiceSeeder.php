<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{BillStatus, PaymentStatus};
use App\Models\Invoice;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
	private const CHUNK_SIZE     = 250; // commit a cada N inserts
	private const PER_CUST_MIN   = 1;   // mínimo de faturas por cliente
	private const PER_CUST_MAX   = 3;   // máximo de faturas por cliente
	private const HARD_CAP = 512;
	private const SECONDS_LIMIT = 6 * 10 ** 2;

	public function run(): void
	{
		$clock = microtime(true);
		// Tabelas essenciais
		foreach ([DC::TABLE_INVS, DC::TABLE_CUSTOMERS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Tabela ausente: {$tbl}. Seeder abortado.");
				return;
			}
		}

		// Coleções base
		$customers = DB::table(DC::TABLE_CUSTOMERS)->select('id')->get();
		if ($customers->isEmpty()) {
			$this->command?->warn('Nenhum customer encontrado. Seeder abortado.');
			return;
		}

		// FKs opcionais (existindo tabela + registros)
		$bills          = Schema::hasTable(DC::TABLE_BILLS)        ? DB::table(DC::TABLE_BILLS)->select('id')->get() : collect();
		$taxRows        = Schema::hasTable(DC::TABLE_TAXES)        ? DB::table(DC::TABLE_TAXES)->select('id', 'name', BC::COL_TAX_RT . ' as rate')->get() : collect();
		$catRows        = Schema::hasTable(DC::TABLE_PROD_SERV_CATS) ? DB::table(DC::TABLE_PROD_SERV_CATS)->select('id')->get() : collect();
		$unitRows       = Schema::hasTable(DC::TABLE_PROD_SERV_UNITS) ? DB::table(DC::TABLE_PROD_SERV_UNITS)->select('id')->get() : collect();
		$contractRows   = Schema::hasTable(DC::TABLE_CONTRACTS)    ? DB::table(DC::TABLE_CONTRACTS)->select('id')->get() : collect();
		$loanRows       = Schema::hasTable(DC::TABLE_LN)           ? DB::table(DC::TABLE_LN)->select('id')->get() : collect();

		// Total a criar
		$baseCount = max(1, $customers->count());
		$target = 8 * $baseCount;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) $target = $opt;
		}

		$created = 0;
		$batch   = 0;
		$now     = Carbon::now();
		$cap = self::HARD_CAP;
		DB::beginTransaction();
		try {
			$targetResult = min($target, $cap);
			foreach ($customers as $cust) {
				if ($cap <= 0 || !$cap) return;
				if ($created >= $target) break;

				$perCustomer = fake()->numberBetween(self::PER_CUST_MIN, self::PER_CUST_MAX);
				if ($created + $perCustomer > $target) {
					$perCustomer = max(0, $target - $created);
				}
				if ($perCustomer === 0) {
					continue;
				}

				for ($i = 0; $i < $perCustomer; $i++) {
					try {
						if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
							$this->command?->warn('Tempo limite atingido, interrompendo a execução do seeder.');
							return;
						}
						if ($cap <= 0 || !$cap) return;
						$cap--;
						if ($created >= $target) break 2;

						// Datas coerentes
						$issue = $now->subDays(fake()->numberBetween(0, 120));
						$due   = $issue->addDays(fake()->numberBetween(7, 45));
						$sent  = fake()->boolean(70) ? $issue->subDays(fake()->numberBetween(0, 3)) : null;

						// Montantes: garante discount <= amount
						$amount   = fake()->randomFloat(2, 50, 5000);
						$discount = fake()->boolean(60) ? fake()->randomFloat(2, 0, $amount * 0.3) : 0.00;

						// Status
						$statusLabel   = Arr::random(method_exists(BillStatus::class, 'values') ? BillStatus::values() : array_map(fn($c) => $c->value, BillStatus::cases()));
						$paymentStatus = Arr::random(method_exists(PaymentStatus::class, 'values') ? PaymentStatus::values() : array_map(fn($c) => $c->value, PaymentStatus::cases()));
						$legacyStatus  = fake()->numberBetween(0, 4); // PJC::COL_STATUS numérico (rótulos em Invoice::$statuses)

						// FKs opcionais (com probabilidade de nulidade)
						$billId  = ($bills->isNotEmpty() && fake()->boolean(40)) ? Arr::random($bills->all())->id : null;
						$taxId   = ($taxRows->isNotEmpty() && fake()->boolean(50)) ? Arr::random($taxRows->all())->id : null;
						$catId   = ($catRows->isNotEmpty() && fake()->boolean(55)) ? Arr::random($catRows->all())->id : null;
						$unitId  = ($unitRows->isNotEmpty() && fake()->boolean(35)) ? Arr::random($unitRows->all())->id : null;
						$contractId = ($contractRows->isNotEmpty() && fake()->boolean(20)) ? Arr::random($contractRows->all())->id : null;
						$loanId     = ($loanRows->isNotEmpty() && fake()->boolean(10)) ? Arr::random($loanRows->all())->id : null;

						// Taxes (JSON): escolhe 0–3 taxas da tabela, quando houver
						$taxes = [];
						if ($taxRows->isNotEmpty()) {
							$pick = fake()->randomElements($taxRows->all(), fake()->numberBetween(0, 3));
							foreach ($pick as $t) {
								$rate = isset($t->rate) ? (float) $t->rate : fake()->randomFloat(2, 1, 25);
								$taxes[] = [
									'id'   => $t->id,
									'name' => $t->name ?? 'Tax',
									'rate' => $rate,
								];
							}
						}

						// Attachments / T&C / Reconcile rules (JSON)
						$attachments = fake()->boolean(30) ? [
							[
								'filename' => 'invoice-' . Str::uuid() . '.pdf',
								'mime'     => 'application/pdf',
								'size'     => fake()->numberBetween(10_000, 800_000),
							],
						] : [];

						$terms = fake()->boolean(60) ? [
							'late_fee_pct' => fake()->randomFloat(2, 0, 5),
							'payment_terms' => Arr::random(['net 7', 'net 15', 'net 30']),
							'notes'        => fake()->boolean(50) ? fake()->sentence(10) : null,
						] : [];

						$reconcileRules = fake()->boolean(40) ? [
							'match_description_contains' => fake()->boolean(70) ? ['invoice', 'payment'] : ['invoice'],
							'min_amount' => fake()->boolean(50) ? round($amount * 0.5, 2) : null,
						] : [];

						// Dados de cobrança/entrega (campos RegistersShipping / Billing)
						$billEmail = fake()->boolean(70) ? fake()->safeEmail() : null;
						$shipEmail = fake()->boolean(30) ? fake()->safeEmail() : null;

						// Criação via Eloquent para aplicar casts (arrays -> JSON)
						do $invId = (string) Str::uuid();
						while (Invoice::where(BC::COL_INV_ID, $invId)->exists());
						$data = [
							// IDs
							BC::COL_INV_ID  => $invId,
							BC::COL_CST_ID  => $cust->id,
							BC::COL_BL_ID   => $billId,
							BC::COL_TAX_ID  => $taxId,

							// Valores / emissão
							BC::COL_CUR_ID  => Arr::random(['BRL', 'USD', 'EUR']),
							'amount'        => $amount,
							'discount'      => $discount,
							BC::COL_SVC_FEE => fake()->randomFloat(2, 0, 50),
							BC::COL_TXS_FEE => fake()->randomFloat(2, 0, 50),
							'reference'     => strtoupper(fake()->bothify('REF-####-??')),
							BC::COL_REF_N   => fake()->boolean(60) ? strtoupper(fake()->bothify('RN-########')) : null,
							'description'   => fake()->sentence(12),
							'notes'         => fake()->boolean(40) ? fake()->paragraph(2) : null,
							'attachments'   => $attachments,
							BC::COL_TC      => $terms,
							BC::COL_AUTORCC => fake()->boolean(20),
							BC::COL_RCC_RL  => $reconcileRules,

							// Rel. adicionais (se existirem)
							'contract'          => $contractId,
							'loan'              => $loanId,
							BC::COL_PRD_SV_UNT  => $unitId,

							// Datas / categoria / status
							BC::COL_SD_DT   => $sent?->toDateString(),
							BC::COL_ISS_DT  => $issue->toDateString(),
							PJC::COL_D_DATE => $due->toDateString(),
							BC::COL_CAT_ID  => $catId,
							PJC::COL_STATUS => $legacyStatus,
							BC::COL_STT_LB  => $statusLabel,
							BC::COL_PAY_STT => $paymentStatus,

							// Frete / desconto / taxes (json)
							BC::COL_SHIP_DSP => fake()->boolean(80) ? 1 : 0,
							BC::COL_DSC_APL  => $discount > 0 ? 1 : 0,
							'taxes'          => $taxes,

							// Endereço de envio
							BC::COL_SHIP_NAME => fake()->boolean(50) ? fake()->name() : null,
							BC::COL_SHIP_EMAIL => $shipEmail,
							BC::COL_SHIP_ADR  => fake()->boolean(50) ? fake()->streetAddress() : null,
							BC::COL_SHIP_TEL  => fake()->boolean(50) ? fake()->numerify('+55###########') : null,
							BC::COL_SHIP_ZIP  => fake()->boolean(50) ? fake()->numerify('########') : null,
							BC::COL_SHIP_CTY  => fake()->boolean(50) ? fake()->city() : null,
							BC::COL_SHIP_ST   => fake()->boolean(50) ? fake()->stateAbbr() : null,
							BC::COL_SHIP_CTR  => fake()->boolean(50) ? Arr::random(['BR', 'US', 'PT', 'ES']) : null,
							BC::COL_SHIP_DTL  => fake()->boolean(30) ? fake()->secondaryAddress() : null,

							// Endereço de cobrança
							BC::COL_BL_NAME => fake()->boolean(60) ? fake()->company() : null,
							BC::COL_BL_EMAIL => $billEmail,
							BC::COL_BL_TEL  => fake()->boolean(60) ? fake()->numerify('+55###########') : null,
							BC::COL_BL_ZIP  => fake()->boolean(60) ? fake()->numerify('########') : null,
							BC::COL_BL_ADR  => fake()->boolean(60) ? fake()->streetAddress() : null,
							BC::COL_BL_ST   => fake()->boolean(60) ? fake()->stateAbbr() : null,
							BC::COL_BL_CTY  => fake()->boolean(60) ? fake()->city() : null,
							BC::COL_BL_CTR  => fake()->boolean(60) ? Arr::random(['BR', 'US', 'PT', 'ES']) : null,
							BC::COL_BL_DTL  => fake()->boolean(30) ? fake()->secondaryAddress() : null,
						];
						$custRef = $cust->name ?? $cust->id;
						(new \Symfony\Component\Console\Output\ConsoleOutput
						)->writeln("({$i}/{$targetResult}) Criando Fatura {$invId} para cliente {$custRef}");
						// Salva (casts cuidam de JSON) — apenas com chaves realmente existentes/permitidas
						Invoice::create($data);

						$created++;
						$batch++;

						if ($batch >= self::CHUNK_SIZE) {
							DB::commit();
							DB::beginTransaction();
							$batch = 0;
						}
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}

			DB::commit();
			$this->command?->info("InvoiceSeeder: {$created} registros inseridos em " . DC::TABLE_INVS . ".");
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->command?->error('Falha ao semear invoices: ' . $e->getMessage());
			throw $e;
		}
	}
}
