<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ChartsConstants as CHTC,
	DatabaseConstants as DC,
	UsersConstants as UC,
	SettingsConstants as SC
};
use App\Models\{
	ChartOfAccount,
	ChartOfAccountSubType,
	ChartOfAccountType
};
use App\Traits\EnsuresSystemUser;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ChartOfAccountSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = Faker::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			/** @var Collection<string,ChartOfAccountType> $types */
			$types = ChartOfAccountType::query()
				->get()
				->keyBy('id');

			/** @var Collection<string,Collection<int,ChartOfAccountSubType>> $subtypesByType */
			$subtypesByType = ChartOfAccountSubType::query()
				->get()
				->groupBy(CHTC::COL_TP);

			if ($types->isEmpty() || $subtypesByType->isEmpty()) {
				Log::warning('ChartOfAccountSeeder: Nenhum tipo/subtipo de COA encontrado. Execute os seeders de tipos e subtipos antes deste.');
				return;
			}

			$created = 0;
			$updated = 0;

			foreach ($types as $typeId => $type) {
				$subtypes = $subtypesByType->get($typeId) ?? collect();

				foreach ($subtypes as $subIdx => $subtype) {
					// Geramos de 2 a 4 contas por subtipo
					$n = $faker->numberBetween(2, 4);

					for ($i = 0; $i < $n; $i++) {
						// Código determinístico por (type, subtype, i) para idempotência do updateOrCreate
						$codeBase = hexdec(substr(md5($typeId . $subtype->id), 0, 6)) % 900000 + 100000;
						$code     = (int) ($codeBase + $i);

						// Saldos realistas
						$initBalance = $faker->randomFloat(2, 0, 250_000);
						// ±20% de variação sobre o saldo inicial
						$currentBalance = round($initBalance * $faker->randomFloat(2, 0.8, 1.2), 2);
						$expectedNext   = round($currentBalance * $faker->randomFloat(2, 0.95, 1.15), 2);

						// Profundidade aproximada por subtipo (0..2)
						$depth = min(2, (int) floor($subIdx / 3));

						$name = sprintf(
							'%s - %s %d',
							(string) ($type->{CHTC::COL_NM} ?? 'Conta'),
							(string) ($subtype->{CHTC::COL_NM} ?? 'Subtipo'),
							$i + 1
						);

						$payload = [
							CHTC::COL_NM         => $name,
							CHTC::COL_CD         => $code,
							'depth'              => $depth,
							CHTC::CUR_BL         => $currentBalance,
							CHTC::INIT_BL        => $initBalance,
							CHTC::EXP_NXT_MN_BL  => $expectedNext,
							'currency_id'        => SC::DEF_SITE_CURRENCY_ID, // e.g. 'BRL'
							'rules'         => [
								'tags'        => $faker->randomElements(['fixo', 'operacional', 'financeiro', 'impostos', 'cloud', 'folha'], $faker->numberBetween(1, 3)),
								'reconciled'  => $faker->boolean(65),
								'visibility'  => $faker->randomElement(['public', 'internal']),
							],
							'restrictions'       => [
								// regra de segurança padrão; regras específicas podem vir do tipo/subtipo
								'allow_negative_balances' => false,
							],
							UC::COL_RSP_ID       => null, // será herdado de user_id se vazio (ver ::saving)
							UC::COL_PD_UPD       => false,
							UC::COL_IS_SYS       => true,
							CHTC::COL_TP         => $typeId,
							CHTC::COL_SUBTP      => $subtype->id,
							CHTC::COL_ENB        => 1,
							CHTC::COL_DESC       => $faker->sentence(12),
							UC::COL_USER_ID      => $systemUserId,
							DC::TABLE_CREATOR    => $systemUserId,
						];

						/** @var ChartOfAccount $model */
						$model = ChartOfAccount::query()
							->where(CHTC::COL_CD, $code)
							->where(CHTC::COL_SUBTP, $subtype->id)
							->first();

						if ($model) {
							$model->fill($payload)->save();
							$updated++;
						} else {
							ChartOfAccount::create($payload);
							$created++;
						}
					}
				}
			}

			Log::info("ChartOfAccountSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
