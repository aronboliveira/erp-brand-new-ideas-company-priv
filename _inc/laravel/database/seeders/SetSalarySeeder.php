<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\Frequency;
use App\Models\{Employee, PayslipType, SetSalary};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class SetSalarySeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			// Frequências possíveis (use valores do Enum para alinhar com a migration)
			$frequencies = [
				Frequency::Monthly->value,
				Frequency::Semimonthly->value,
				Frequency::Biweekly->value,
				Frequency::Weekly->value,
				Frequency::Annual->value,
				Frequency::Hourly->value,
				Frequency::Once->value,
				Frequency::Variable->value,
				Frequency::Semestral->value, // se existir no Enum Frequency com esse nome
			];

			// Mapeia um dia padrão válido (1..31) conforme a frequência
			$pickPayDay = static function (string $freq): int {
				return match ($freq) {
					'monthly'      => [5, 10, 20, 25, 28][array_rand([5, 10, 20, 25, 28])],
					'semimonthly'  => [5, 20][array_rand([5, 20])],
					'biweekly'     => [5, 19][array_rand([5, 19])],
					'weekly'       => 5,
					'annual'       => 5,
					'hourly'       => 5,
					'once'         => 5,
					'variable'     => 5,
					'semestral'    => 5,
					default        => 5,
				};
			};

			// Tenta obter algum PayslipType válido (opcional)
			$defaultPayslipTypeId = PayslipType::query()->value('id'); // null se não houver

			// Evita recriar para quem já tem set salary
			$already = SetSalary::query()->pluck(UC::COL_EMP_ID)->all();
			$already = array_fill_keys($already, true);

			$employees = Employee::query()
				->select(['id'])
				->orderBy('id')
				->get();

			if ($employees->isEmpty()) {
				Log::info('SetSalarySeeder: nenhum employee encontrado, nada a fazer.');
				return;
			}

			$created = 0;
			$updated = 0;
			$HARD_CAP = 2; // HARD CAP guard

			foreach ($employees as $emp) {
				if ($created >= $HARD_CAP) break; // HARD CAP guard
				try {
					$empId = $emp->id;
					// $ref = $emp instanceof Employee ? ($emp->name ?? $emp->id) : (Employee::query()->where('id', $emp)->value('name') ?? $emp);
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Tipo de Salário para {$ref}");
					// Escolhe uma frequência randômica dentre as válidas
					$freq = $frequencies[array_rand($frequencies)];

					// Define um salário-base realista (>= salário mínimo), com variabilidade
					// DC::MININUM_WAGE_BR foi usado como default na migration
					$base   = (float) (DC::MININUM_WAGE_BR ?? 1412.00); // fallback defensivo
					$factor = [1.0, 1.25, 1.5, 2.0, 2.5, 3.0][array_rand([1.0, 1.25, 1.5, 2.0, 2.5, 3.0])];
					$salary = round($base * $factor + mt_rand(0, 700) /* variação */, 2);

					$payload = [
						UC::COL_EMP_ID      => $empId,
						UC::COL_SLR_TP      => $defaultPayslipTypeId, // pode ser null sem problemas
						'salary'            => $salary,
						'frequency'         => $freq,
						BC::COL_MDAY_LMT    => $pickPayDay($freq),
						DC::COL_TABLE_CREATOR   => $systemUserId,
					];

					// Cria ou atualiza por employee_id (único)
					$model = SetSalary::updateOrCreate(
						[UC::COL_EMP_ID => $empId],
						$payload
					);

					$model->wasRecentlyCreated ? $created++ : $updated++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info("SetSalarySeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
