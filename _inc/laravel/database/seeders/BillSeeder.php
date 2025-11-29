<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Enums\{
	BillStatus,
	ConsumableType,
	PaymentStatus,
	TransactionType,
	UserType
};
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillSeeder extends Seeder
{
	public function run(): void
	{
		// Dependências mínimas para respeitar FKs
		$vendorIds = DB::table(DC::TABLE_VENDORS)->pluck('id');
		$catIds    = DB::table(DC::TABLE_PROD_SERV_CATS)->pluck('id');
		$orderIds  = DB::table(DC::TABLE_ORDERS)->pluck('id');

		if ($vendorIds->isEmpty() || $catIds->isEmpty() || $orderIds->isEmpty()) {
			$this->command?->warn(
				'[BillSeeder] Dependências ausentes: ' .
					($vendorIds->isEmpty() ? 'vendors ' : '') .
					($catIds->isEmpty()    ? 'categorias ' : '') .
					($orderIds->isEmpty()  ? 'orders ' : '') .
					'— rode os seeders correspondentes antes do BillSeeder.'
			);
			return;
		}

		$count = 15; // ajuste conforme necessário
		$today = Carbon::today();

		for ($i = 0; $i < $count; $i++) {
			$vendorId = $vendorIds->random();
			$catId    = $catIds->random();
			$orderId  = $orderIds->random();

			// Datas coerentes com as regras do modelo (booted/saving fará os clamps)
			$sendDate = $today->copy()->addDays(random_int(0, 1));
			$billDate = $today->copy()->addDays(random_int(0, 2));
			$dueDate  = $today->copy()->addDays(random_int(3, 15));

			// Valores monetários
			$amount          = round(random_int(5_000, 200_000) / 100, 2); // 50.00 ~ 2000.00
			$discountApply   = (int) random_int(0, 1);
			$discountMax     = $discountApply ? (int) floor($amount * 0.2 * 100) : 0; // até 20%
			$discount        = round(($discountMax > 0 ? random_int(0, $discountMax) : 0) / 100, 2);

			// Status (deixa o modelo normalizar se algo vier inválido)
			$billStatusCases = BillStatus::cases();
			$payStatusCases  = PaymentStatus::cases();

			$billStatus = $billStatusCases[array_rand($billStatusCases)];
			$payStatus  = $payStatusCases[array_rand($payStatusCases)];

			// Itens simples (sem refs de tax para evitar FKs inexistentes)
			$items = [
				[
					'name'     => 'Serviço ' . Str::upper(Str::random(4)),
					'price'    => round(random_int(1_000, 10_000) / 100, 2),
					'quantity' => random_int(1, 5),
					'discount' => round(random_int(0, 500) / 100, 2),
					'tax'      => [], // o modelo filtrará impostos inválidos
				],
			];

			// Criação via Eloquent para acionar normalizações do modelo
			Bill::create([
				// Identificadores e relacionamentos
				BC::COL_BL_ID   => Str::uuid()->toString(),
				UC::COL_VD_ID   => $vendorId,
				BC::COL_CAT_ID  => $catId,
				BC::COL_OD_ID   => $orderId,

				// Datas
				BC::COL_SD_DT   => $sendDate->toDateString(),
				BC::COL_BL_DT   => $billDate->toDateString(),
				PJC::COL_D_DATE => $dueDate->toDateString(),

				// Status numérico legado (0..3, por exemplo)
				PJC::COL_STATUS => random_int(0, 3),

				// Enums
				BC::COL_STT_LB  => $billStatus,
				BC::COL_PAY_STT => $payStatus,
				'type'          => TransactionType::Bill->value,
				UC::COL_U_TP    => UserType::Customer,

				// Preços e flags
				BC::COL_PRC_CUR => strtoupper(SC::DEF_SITE_CURRENCY_ID),
				BC::COL_SHIP_DSP => random_int(0, 1),
				'amount'        => $amount,
				BC::COL_DSC_APL => $discountApply,
				'discount'      => $discount,

				// Payloads variados
				'taxes'       => [],        // deixe vazio; o modelo valida
				'items'       => $items,
				'attachments' => [],
				'notes'       => 'Conta gerada por seeder para testes.',

				// Billing (opcional)
				BC::COL_BL_NAME => 'Cliente Teste ' . ($i + 1),
				BC::COL_BL_EMAIL => 'cliente' . ($i + 1) . '@example.com',
				BC::COL_BL_TEL => '+55 11 9' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
				BC::COL_BL_ZIP => '01001-000',
				BC::COL_BL_ADR => 'Av. Paulista, 1000',
				BC::COL_BL_CTY => 'São Paulo',
				BC::COL_BL_ST  => 'SP',
				BC::COL_BL_CTR => 'BR',

				// Shipping (opcional)
				BC::COL_SHIP_NAME => 'Logística ' . ($i + 1),
				BC::COL_SHIP_TEL  => '+55 11 3' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
				BC::COL_SHIP_ZIP  => '01310-100',
				BC::COL_SHIP_ADR  => 'Rua Exemplo, 200',
				BC::COL_SHIP_CTY  => 'São Paulo',
				BC::COL_SHIP_ST   => 'SP',
				BC::COL_SHIP_CTR  => 'BR',
			]);
		}

		$this->command?->info('[BillSeeder] ' . $count . ' contas lançadas com sucesso.');
	}
}
