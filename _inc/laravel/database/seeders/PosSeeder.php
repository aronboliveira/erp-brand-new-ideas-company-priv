<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\{PosStatus, PosType};
use App\Models\Pos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

final class PosSeeder extends Seeder
{
	/**
	 * Quantidade de POS a criar.
	 */
	private const TOTAL = 256;

	public function run(): void
	{
		DB::transaction(function () {
			$faker = \Faker\Factory::create('pt_BR');

			// FKs obrigatórias (a tabela/migration exige)
			$customerId = $this->requireAnyId(DC::TABLE_CUSTOMERS, 'customer');
			$categoryId = $this->requireAnyId(DC::TABLE_PROD_SERV_CATS, 'product_service_category');

			// Pools para FKs opcionais (se não houver, segue com null)
			$companyIds      = $this->idPool(DC::TABLE_USERS);
			$branchIds       = $this->idPool(DC::TABLE_BRANCHES);
			$warehouseIds    = $this->idPool(DC::TABLE_WHS);
			$departmentIds   = $this->idPool(DC::TABLE_DEPARTMENTS);
			$vendorIds       = $this->idPool(DC::TABLE_VENDORS);
			$manufacturerIds = $this->idPool(DC::TABLE_USERS);

			$created = 0;
			$failed  = 0;

			for ($i = 0; $i < self::TOTAL; $i++) {
				try {
					// Identificador para vínculo com itens/pagamento (se existirem)
					$posPublicId = 'POS-' . Str::upper(Str::random(10));

					// Datas coerentes
					$createdAt  = Carbon::now()->subDays(random_int(0, 90))->setTime(random_int(8, 20), random_int(0, 59));
					$posDate    = $createdAt->copy()->toDateString();
					$lastTrans  = (clone $createdAt)->addDays(random_int(0, 10))->addMinutes(random_int(0, 600));

					// Tipo / Status (enums do seu modelo)
					$type   = $this->pick(PosType::values());
					$sttLb  = $this->pick(PosStatus::values());
					$status = $this->pick([0, 1, 2, 3]); // inteiro livre legado

					// Capacidades de pagamento
					$acceptCredit = (bool) random_int(0, 1);
					$acceptDebit  = (bool) random_int(0, 1);
					$acceptPix    = (bool) random_int(0, 1);
					$acceptCash   = (bool) random_int(0, 1);

					// Flags aceitas (JSON)
					$allFlags = ['VISA', 'MASTERCARD', 'ELO', 'AMEX', 'HIPER', 'HIPERCARD'];
					shuffle($allFlags);
					$acceptedFlags = array_slice($allFlags, 0, random_int(1, count($allFlags)));

					// QR Pix (JSON) somente se aceitar Pix
					$pixQr = $acceptPix ? [
						'payload'      => '00020126' . random_int(100000, 999999) . Str::upper(Str::random(16)),
						'merchant'     => $faker->company(),
						'city'         => 'RIO DE JANEIRO',
						'amount_hint'  => number_format($this->money(rand(1500, 150000)), 2, '.', ''),
						'txid'         => Str::upper(Str::random(12)),
						'crc'          => strtoupper(substr(hash('crc32', Str::random(16)), 0, 4)),
					] : null;

					// Totais/contadores
					$transactionsCount = random_int(0, 120);
					$accumulatedTotal  = $this->money(random_int(0, 120) * random_int(500, 35000)) / 100; // ~R$ 0,00–42.000
					$serviceFee        = $this->money(random_int(0, 500)) / 100; // 0–5,00

					// Billing (modelo normaliza email/telefone/CEP)
					$blName  = $faker->company();
					$blMail  = $faker->companyEmail();
					$blTel   = '+55 ' . $faker->areaCode . ' ' . $faker->cellphone(false);
					$blZip   = $faker->postcode();
					$blAddr  = $faker->streetAddress();
					$blCity  = $faker->city();
					$blState = $faker->stateAbbr();
					$blCtr   = 'BR';
					$blDtl   = $faker->optional(0.3)->sentence();

					// Payload principal
					$data = [
						BC::COL_POS_ID   => $posPublicId,
						BC::COL_POS_DT   => $posDate,

						BC::COL_DVC_SR   => strtoupper(Str::random(12)),
						BC::COL_MAC_ADR  => sprintf(
							'%02X:%02X:%02X:%02X:%02X:%02X',
							random_int(0, 255),
							random_int(0, 255),
							random_int(0, 255),
							random_int(0, 255),
							random_int(0, 255),
							random_int(0, 255)
						),
						BC::COL_IP_ADR   => $faker->ipv4(),
						BC::COL_DVC_MD   => $this->pick(['Verifone Vx520', 'Ingenico iCT220', 'Datalogic Skorpio', 'Elgin SmartPOS', 'Sunmi P2']),
						BC::COL_OPS_SYS  => $this->pick(['Android 11', 'Android 10', 'Linux 5.4', 'Embedded RTOS']),

						CC::COL_CP_ID    => $this->maybe($companyIds),
						UC::COL_BRC_ID   => $this->maybe($branchIds),
						BC::COL_WRH_ID   => $this->maybe($warehouseIds),
						UC::COL_DEP_ID   => $this->maybe($departmentIds),
						BC::COL_CST_ID   => $customerId,  // obrigatório
						BC::COL_CAT_ID   => $categoryId,  // obrigatório
						CC::COL_MNF_ID   => $this->maybe($manufacturerIds),
						CC::COL_MNF_NM   => $faker->optional(0.6)->company(),
						UC::COL_VD_ID    => $this->maybe($vendorIds),
						CC::COL_VD_NM    => $faker->optional(0.6)->company(),

						'type'           => $type,
						'status'         => $status,
						BC::COL_SHIP_DSP => random_int(0, 1),
						BC::COL_STT_LB   => $sttLb,

						BC::COL_IO       => (bool) random_int(0, 1),
						BC::COL_ACP_CRD  => $acceptCredit,
						BC::COL_ACP_DBT  => $acceptDebit,
						BC::COL_ACP_PIX  => $acceptPix,
						BC::COL_ACP_CSH  => $acceptCash,
						BC::COL_PIX_QR   => $pixQr,
						BC::COL_ACP_FLG  => $acceptedFlags,

						BC::COL_LST_TRS  => $lastTrans,
						DC::COL_LA       => $lastTrans->copy()->addMinutes(random_int(0, 180)),
						BC::COL_TRS_CNT  => $transactionsCount,
						BC::COL_ACC_TTL  => $accumulatedTotal,
						BC::COL_SVC_FEE  => $serviceFee,

						// Billing (normalização no booted())
						BC::COL_BL_NAME  => $blName,
						BC::COL_BL_EMAIL => $blMail,
						BC::COL_BL_TEL   => $blTel,
						BC::COL_BL_ZIP   => $blZip,
						BC::COL_BL_ADR   => $blAddr,
						BC::COL_BL_ST    => $blState,
						BC::COL_BL_CTY   => $blCity,
						BC::COL_BL_CTR   => $blCtr,
						BC::COL_BL_DTL   => $blDtl,

						// Rastreamento de falhas (opcional)
						DC::COL_FL_AT      => null,
						DC::COL_FLD_RS     => null,
						DC::COL_RTR_CT     => random_int(0, 2),
						DC::COL_LST_RTR_AT => null,
						DC::COL_ER_LG      => null,

						// Auditoria mínima (se existir users; não há FK nesses campos por padrão)
						DC::COL_TABLE_CREATOR => $this->maybe($companyIds),
						DC::COL_TABLE_UPDATER => $this->maybe($companyIds),
						DC::COL_C_AT      => $createdAt,
						DC::COL_U_AT      => $createdAt->copy()->addMinutes(random_int(5, 400)),
					];
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Ponto de Venda {$posPublicId} do tipo {$type} para cliente {$customerId}");
					try {
						Pos::create($data);
						$created++;
					} catch (\Throwable $e) {
						$failed++;
						Log::warning(self::class . ' failed to insert POS row', [
							'error'   => $e->getMessage(),
							'pos_id'  => $posPublicId,
							'cust_id' => $customerId,
							'cat_id'  => $categoryId,
						]);
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info(self::class . " finished: created={$created}, failed={$failed}");
		});
	}

	/**
	 * Busca 1 id de uma tabela; se não houver, falha cedo com mensagem clara.
	 */
	private function requireAnyId(string $table, string $humanName): string
	{
		$id = (string) (DB::table($table)->value('id') ?? '');
		if ($id !== '') return $id;

		throw new \RuntimeException("PosSeeder: nenhuma linha encontrada em '{$table}' para '{$humanName}'. Crie ao menos 1 registro antes de semear POS.");
	}

	/**
	 * Retorna pool de ids (pode estar vazio).
	 * @return array<int,string>
	 */
	private function idPool(string $table): array
	{
		return array_values(array_map('strval', DB::table($table)->pluck('id')->all()));
	}

	/**
	 * Escolhe aleatoriamente um item ou retorna null se pool vazio.
	 */
	private function maybe(array $pool): ?string
	{
		if (!$pool) return null;
		return $pool[array_rand($pool)];
	}

	/**
	 * Escolhe um item de um array não-vazio.
	 */
	private function pick(array $options)
	{
		return $options[array_rand($options)];
	}

	/**
	 * Normaliza inteiro de "centavos".
	 */
	private function money(int $cents): int
	{
		return max(0, $cents);
	}
}
