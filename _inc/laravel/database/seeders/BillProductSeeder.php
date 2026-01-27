<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BC,
	DatabaseConstants as DC
};
use App\Models\BillProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class BillProductSeeder extends Seeder
{
	private const HARD_CAP = 2048;
	private const SECONDS_LIMIT = 300; // 5 minutos
	public function run(): void
	{
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$clock = microtime(true);
		// Coleta de dependências
		$billIds    = DB::table(DC::TABLE_BILLS)->pluck('id');          // obrigatório
		$prodIds    = DB::table(DC::TABLE_PROD_SERVS)->pluck('id');     // opcional
		$coaIds     = DB::table(DC::TABLE_COAS)->pluck('id');           // opcional
		$taxIds     = DB::table(DC::TABLE_TAXES)->pluck('id');          // opcional

		if ($billIds->isEmpty()) {
			$this->command?->warn('[BillProductSeeder] Nenhuma Bill encontrada. Rode o BillSeeder (ou crie Bills) antes deste seeder.');
			return;
		}
		$cap = min(self::HARD_CAP, $billIds->count());
		// Para cada Bill, cria entre 1 e 3 itens
		$availableProdIds = $prodIds->shuffle()->values();
		$prodIndex = 0;
		foreach ($billIds as $billId) {
			$itemsPerBill = random_int(1, 3);

			// Lê os "taxes" declarados na própria Bill; se houver, poderemos gerar BC::COL_OT_TX compatível
			$billRow = DB::table(DC::TABLE_BILLS)->select('taxes')->where('id', $billId)->first();
			$billTaxes = [];
			if ($billRow && !empty($billRow->taxes)) {
				$decoded = json_decode($billRow->taxes, true);
				if (is_array($decoded)) {
					// Normaliza para um mapa de IDs válidos
					foreach ($decoded as $t) {
						if (!is_array($t)) continue;
						$taxKey = $t['id'] ?? $t['tax_id'] ?? $t['key'] ?? null;
						if (is_string($taxKey)) {
							$taxKey = trim($taxKey);
							if ($taxKey !== '') $billTaxes[$taxKey] = true;
						}
					}
				}
			}

			for ($i = 0; $i < $itemsPerBill; $i++) {
				if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
					$out->writeln('[BillProductSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
					$this->command?->warn('[BillProductSeeder] Tempo limite atingido, interrompendo a execução do seeder.');
					return;
				}
				if (--$cap < 0) {
					$out->writeln('[BillProductSeeder] Limite máximo de itens atingido, interrompendo a execução do seeder.');
					$this->command?->warn('[BillProductSeeder] Limite máximo de itens atingido, interrompendo a execução do seeder.');
					return;
				}
				if ($prodIds->isNotEmpty()) {
					if ($prodIndex >= $availableProdIds->count()) {
						$out->writeln('[BillProductSeeder] Lista de produtos esgotada, interrompendo.');
						$this->command?->warn('[BillProductSeeder] Lista de produtos esgotada.');
						return;
					}
					$pickedProdId = $availableProdIds[$prodIndex];
					$prodIndex++;
				} else {
					$pickedProdId = null;
				}
				try {

					// Quantidade e preços “seguros”
					$qty         = random_int(1, 8);
					$unitCents   = random_int(2_000, 40_000); // 20.00 ~ 400.00
					$unit        = round($unitCents / 100, 2);
					$subtotal    = $unit * $qty;

					$maxDisc     = (int) floor($subtotal * 0.25 * 100); // até 25% do subtotal
					$discount    = round(($maxDisc > 0 ? random_int(0, $maxDisc) : 0) / 100, 2);
					$lineTotal   = round(max(0, $subtotal - $discount), 4);

					// “tax” (string) meramente ilustrativo/compatível; BC::COL_TAX_ID é opcional
					$taxLabel    = random_int(0, 1) ? ('TAX-' . Str::upper(Str::random(3))) : null;
					$taxId       = (!$taxIds->isEmpty() && random_int(0, 1) === 1) ? $taxIds->random() : null;

					// Outros impostos (BC::COL_OT_TX) só terão efeito se estiverem também na Bill->taxes
					$otherTaxes = [];
					if (!empty($billTaxes) && random_int(0, 1) === 1) {
						// escolhe até 2 chaves existentes na própria bill
						$allowedKeys = array_keys($billTaxes);
						shuffle($allowedKeys);
						$pick = array_slice($allowedKeys, 0, random_int(1, min(2, count($allowedKeys))));
						foreach ($pick as $tk) {
							$otherTaxes[] = ['id' => $tk]; // estrutura mínima aceita pelo filtro do modelo
						}
					}
					BillProduct::create([
						BC::COL_BL_ID   => $billId,
						BC::COL_PRD_ID  => $pickedProdId,
						BKC::COL_COA    => $coaIds->isNotEmpty()  ? $coaIds->random()  : null,
						'quantity'      => $qty,
						'discount'      => $discount,
						'total'         => $lineTotal,
						'tax'           => $taxLabel,
						BC::COL_TAX_ID  => $taxId,
						BC::COL_OT_TX   => $otherTaxes, // o boot() do modelo manterá apenas os presentes na Bill
						'description'   => 'Item ' . Str::upper(Str::random(5)) . " ({$qty} x {$unit})",
						'attachments'   => [],
						'metadata'      => [
							'unit_price' => $unit,
							'currency'   => 'BRL',
							'seed_src'   => 'BillProductSeeder',
						],
					]);
					$out->writeln("[BillProductSeeder] {$i} - Item de fatura criado para Bill ID {$billId}, quantity {$qty}, unit price {$unit}, total {$lineTotal}.");
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}

		$this->command?->info('[BillProductSeeder] Itens de fatura gerados com sucesso.');
	}
}
