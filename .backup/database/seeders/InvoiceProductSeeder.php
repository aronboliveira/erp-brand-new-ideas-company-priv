<?php

namespace Database\Seeders;

use App\Config\Constants\BillsConstants as BC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Models\InvoiceProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable as Carbon;

class InvoiceProductSeeder extends Seeder
{
	// Parâmetros fixos (sem env)
	private const OPTIONALITY   = 0.65; // antes: INV_PRD_OPTIONALITY
	private const PER_INV_MIN   = 1;    // antes: INV_PRD_PER_INV_MIN
	private const PER_INV_MAX   = 5;    // antes: INV_PRD_PER_INV_MAX
	private const MAX_QTY       = 6;    // antes: INV_PRD_MAX_QTY
	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	/**
	 * Opções CLI:
	 *   --count=INT   Limite aproximado de linhas (global).
	 */
	public function run(): void
	{
		$clock = microtime(true);
		if (!Schema::hasTable(DC::TABLE_INV_PRD) || !Schema::hasTable(DC::TABLE_INVS)) {
			$this->command?->warn('Tabelas de faturas/itens de fatura ausentes. Pulando.');
			return;
		}

		$opt      = self::OPTIONALITY;
		$perMin   = self::PER_INV_MIN;
		$perMax   = self::PER_INV_MAX;
		$maxQty   = self::MAX_QTY;
		$target   = (int) ($this->command && $this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count') ? $this->command?->option('count') : 64);

		$maybe = fn(callable $fn) => fake()->boolean((int) round($opt * 100)) ? $fn() : null;
		$json  = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$invoices = DB::table(DC::TABLE_INVS)->get();
		if ($invoices->isEmpty()) {
			$this->command?->warn('Nenhuma fatura encontrada. Pulando.');
			return;
		}

		if (!Schema::hasTable(DC::TABLE_PROD_SERVS)) {
			$this->command?->warn('Tabela de produtos/serviços ausente. Pulando.');
			return;
		}

		$productCols = ['id', BC::COL_SL_PRC, BC::COL_PC_PRC, 'price', 'sale_price', 'unit_price'];
		$existingCols = array_values(array_filter($productCols, fn($c) => Schema::hasColumn(DC::TABLE_PROD_SERVS, $c)));
		$products = DB::table(DC::TABLE_PROD_SERVS)->select($existingCols)->get();
		if ($products->isEmpty()) {
			$this->command?->warn('Nenhum produto/serviço encontrado. Pulando.');
			return;
		}

		$warehouseIds = Schema::hasTable(DC::TABLE_WRH) ? DB::table(DC::TABLE_WRH)->pluck('id')->all() : [];
		$loanIds      = Schema::hasTable(DC::TABLE_LN) ? DB::table(DC::TABLE_LN)->pluck('id')->all() : [];
		$contractIds  = Schema::hasTable(DC::TABLE_CONTRACTS) ? DB::table(DC::TABLE_CONTRACTS)->pluck('id')->all() : [];
		$userIds      = Schema::hasTable(DC::TABLE_USERS) ? DB::table(DC::TABLE_USERS)->pluck('id')->all() : [];

		$now  = Carbon::now();
		$rows = [];
		$totalPlanned = 0;

		foreach ($invoices as $inv) {
			$itemsForInvoice = fake()->numberBetween($perMin, $perMax);
			if ($target > 0 && $totalPlanned + $itemsForInvoice > $target) {
				$itemsForInvoice = max(0, $target - $totalPlanned);
			}
			if ($itemsForInvoice === 0) continue;

			for ($i = 0; $i < $itemsForInvoice; $i++) {
				try {
					if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
						$this->command?->warn('Limite de tempo atingido, interrompendo o seeder de itens de fatura.');
						return;
					}
					if ($target > 0 && $totalPlanned >= $target) break 2;

					$shorteningUniqueProdIds = $products->pluck('id');
					$pickedProdId = $shorteningUniqueProdIds->random();
					$shorteningUniqueProdIds = $shorteningUniqueProdIds->filter(function ($value) use ($pickedProdId) {
						return $value !== $pickedProdId;
					});
					$price = $this->pickPrice(DB::table(DC::TABLE_PROD_SERVS)->where('id', $pickedProdId)->first());
					if ($price <= 0) $price = round(fake()->randomFloat(2, 10, 900), 2);

					$qty = fake()->numberBetween(1, $maxQty);
					$subtotal = round($price * $qty, 2);

					$discount = $maybe(function () use ($subtotal) {
						if ($subtotal <= 0) return 0.0;
						$max = min($subtotal, round($subtotal * fake()->randomFloat(2, 0, 0.25), 2));
						return $max > 0 ? round(fake()->randomFloat(2, 0, $max), 2) : 0.0;
					}) ?? 0.0;

					$svcFee = $maybe(fn() => round($subtotal * fake()->randomFloat(2, 0.00, 0.03), 2));
					$isSec  = $maybe(fn() => fake()->boolean(20));
					$charge = $maybe(fn() => fake()->boolean(10));

					$createdAt = $now->subDays(fake()->numberBetween(0, 60))->subMinutes(fake()->numberBetween(0, 1440));
					$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 2880));

					$taxStr = $maybe(function () {
						$names = ['ISS', 'ICMS', 'IOF', 'PIS', 'COFINS'];
						$n = Arr::random($names);
						$rate = ['0%', '2%', '5%', '7,6%', '12%'];
						return $n . ' ' . Arr::random($rate);
					});

					$taxesList = $maybe(function () {
						$sample = [
							['name' => 'ISS', 'rate' => 2.00],
							['name' => 'PIS', 'rate' => 1.65],
							['name' => 'COFINS', 'rate' => 7.60],
						];
						return Arr::random($sample, fake()->numberBetween(1, 2));
					});

					$attachments = $maybe(function () {
						return [
							['name' => fake()->lexify('spec-????.pdf'), 'url' => fake()->url()],
							['name' => fake()->lexify('img-????.png'), 'url' => fake()->url()],
						];
					});

					$product = DB::table(DC::TABLE_PROD_SERVS)->where('id', $pickedProdId)->first();
					if (!$product) {
						Log::warning(get_class($this) . ' skipped: produto/serviço não encontrado para item de fatura', ['product_id' => $pickedProdId]);
						continue;
					}
					$p = $product;

					$rows[] = [
						BC::COL_INV_ID       => $inv->id,
						BC::COL_PRD_ID       => $pickedProdId,
						'quantity'           => $qty,
						'tax'                => $taxStr,
						'price'              => $price,
						BC::COL_CUR_ID       => $inv->currency_id ?: 'BRL',
						'discount'           => $discount,
						BC::COL_SVC_FEE      => $svcFee,
						BC::COL_IS_SCD       => $isSec,
						BC::COL_CAN_CHG_BK   => $charge,
						'reference'          => $maybe(fn() => 'REF-' . strtoupper(Str::random(6))),
						'description'        => 'Item de fatura para ' . (property_exists($p, 'name') ? $p->name : ('produto ' . substr($p->id, 0, 6))),
						'notes'              => $maybe(fn() => fake()->realText(120)),
						BC::COL_TXS_LST      => $json($taxesList),
						'attachments'        => $json($attachments),
						'contract'           => $maybe(fn() => $contractIds ? Arr::random($contractIds) : null),
						'loan'               => $maybe(fn() => $loanIds ? Arr::random($loanIds) : null),
						BC::COL_WRH_ID       => $maybe(fn() => $warehouseIds ? Arr::random($warehouseIds) : null),

						DC::COL_TABLE_CREATOR    => $maybe(fn() => $userIds ? Arr::random($userIds) : null),
						'created_at'         => $createdAt->toDateTimeString(),
						'updated_at'         => $updatedAt->toDateTimeString(),
					];
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Produto de Fatura relacionado à Fatura {$inv->id} e Produto {$p->id}, no Valor de {$price} x {$qty}");
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

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, 1000) as $chunk) {
				foreach ($chunk as $row) InvoiceProduct::create($row);
			}
		});

		$this->command?->info("InvoiceProductSeeder: {$totalPlanned} itens inseridos.");
	}

	private function pickPrice(object $p): float
	{
		foreach ([BC::COL_SL_PRC, BC::COL_PC_PRC, 'price', 'sale_price', 'unit_price'] as $c) {
			if (property_exists($p, $c) && is_numeric($p->{$c}) && $p->{$c} > 0) {
				return (float) $p->{$c};
			}
		}
		return 0.0;
	}
}
