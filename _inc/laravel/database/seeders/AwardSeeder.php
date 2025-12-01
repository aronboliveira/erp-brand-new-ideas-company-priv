<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{Award, AwardType, Employee};
use App\Traits\EnsuresSystemUser;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

final class AwardSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			// 1) Garante tipos de prêmio mínimos (caso não existam)
			$typeIds = AwardType::query()->pluck('id')->all();
			if (empty($typeIds)) {
				$defaults = [
					'Funcionário do Mês',
					'Pontualidade',
					'Melhor Atendimento',
					'Produtividade',
					'Tempo de Casa',
				];

				$now = now('America/Sao_Paulo');
				$payload = [];
				foreach ($defaults as $name) {
					$payload[] = [
						'id'              => (string) Str::uuid(),
						'name'            => $name,
						DC::COL_TABLE_CREATOR => $systemUserId,
						DC::COL_TABLE_UPDATER => null,
						'created_at'      => $now,
						'updated_at'      => $now,
					];
				}

				DB::table(DC::TABLE_AWD_TPS)->insert($payload);
				$typeIds = AwardType::query()->pluck('id')->all();
			}

			// 2) Coleta empregados existentes
			$employeeIds = Employee::query()->pluck('id')->all();
			if (empty($employeeIds)) {
				Log::notice('Nenhum employee encontrado. Pulando seeding de awards.');
				return;
			}

			// 3) Gera prêmios
			$qty = min(120, max(20, count($employeeIds) * 2));
			$rows = [];
			$now = now('America/Sao_Paulo');

			for ($i = 0; $i < $qty; $i++) {
				/** @var \Carbon\Carbon $date */
				$date = now('America/Sao_Paulo')->subDays(random_int(0, 720))->format('Y-m-d');

				$rows[] = [
					'id'               => (string) Str::uuid(),
					UC::COL_EMP_ID     => $faker->randomElement($employeeIds),
					UC::COL_AWD_TP     => $faker->randomElement($typeIds),
					'date'             => $date,
					'gift'             => $faker->optional(0.5)->randomElement([
						'Voucher R$ 200',
						'Day Off',
						'Placa de Reconhecimento',
						'Kit Brinde',
						null,
					]),
					'description'      => $faker->optional(0.7)->sentence(12),
					DC::COL_TABLE_CREATOR  => $systemUserId,
					DC::COL_TABLE_UPDATER  => null,
					'created_at'       => $now,
					'updated_at'       => $now,
				];
			}

			collect($rows)->chunk(1000)->each(
				fn($chunk) => DB::table(DC::TABLE_AWD)->insert($chunk->all())
			);
		}, 3);
	}
}
