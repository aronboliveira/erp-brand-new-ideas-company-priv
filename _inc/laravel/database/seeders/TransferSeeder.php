<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CPC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\{Branch, Department, Employee, Transfer};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class TransferSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$employeeIds = Employee::query()->pluck('id')->all();
			if (!$employeeIds) {
				Log::notice('No employees found. Skipping transfer seeding.');
				return;
			}

			$branchIds = Branch::query()->pluck('id')->all();
			$deptIds   = Department::query()->pluck('id')->all();

			if (!$branchIds || !$deptIds) {
				Log::notice('Branches or Departments not found. Skipping transfer seeding.');
				return;
			}

			// ~35% dos colaboradores receberão 1 transferência simulada (mín. 3).
			$targets = collect($employeeIds)
				->shuffle()
				->take(max(3, (int) floor(count($employeeIds) * 0.35)));

			foreach ($targets as $empId) {
				/** @var Employee $emp */
				$emp = Employee::query()
					->select(['id', CPC::COL_BRC_ID, CPC::COL_DEP_ID])
					->find($empId);

				if (!$emp) {
					continue;
				}

				// Escolhe filial de destino, priorizando troca real (≠ filial atual) se possível.
				$destBranchId = collect($branchIds)->reject(fn($b) => $b === $emp->{CPC::COL_BRC_ID})
					->whenEmpty(fn($c) => collect($branchIds))
					->shuffle()
					->first();

				// Tenta departamento compatível com a filial escolhida; cai para qualquer um se não houver.
				$deptPoolForBranch = Department::query()
					->where(CPC::COL_BRC_ID, $destBranchId)
					->pluck('id')
					->all();

				$destDeptId = collect($deptPoolForBranch ?: $deptIds)
					->shuffle()
					->first();

				// Data da transferência: nos últimos ~18 meses.
				$transferDate = now('America/Sao_Paulo')->subDays(random_int(0, 540))->format('Y-m-d');

				// Evita duplicidade do par (employee_id, transfer_date) por prudência.
				$existsSameDay = Transfer::query()
					->where(UC::COL_EMP_ID, $empId)
					->whereDate(UC::COL_TRF_DT, $transferDate)
					->exists();

				if ($existsSameDay) {
					$transferDate = now('America/Sao_Paulo')->subDays(random_int(0, 540))->addDay()->format('Y-m-d');
				}

				$t = new Transfer();
				$t->{UC::COL_EMP_ID} = $empId;
				$t->{UC::COL_BRC_ID} = $destBranchId;
				$t->{UC::COL_DEP_ID} = $destDeptId;
				$t->{UC::COL_TRF_DT} = $transferDate;
				$t->description      = $faker->boolean(60) ? $faker->sentence(8) : null;
				$t->notes            = $faker->boolean(35) ? $faker->sentence(10) : null;

				// Auditoria explícita (sem auth() no seeding).
				$t->{DC::COL_TABLE_CREATOR} = $systemUserId;

				try {
					$t->save();

					// Atualiza o empregado para refletir a nova lotação (e endereço da filial, se houver).
					$emp->{CPC::COL_BRC_ID} = $destBranchId;
					$emp->{CPC::COL_DEP_ID} = $destDeptId;

					$branchAddress = Branch::query()
						->where('id', $destBranchId)
						->value('address');

					if ($branchAddress) {
						$emp->{CPC::COL_BRC_LC} = $branchAddress;
					}

					$emp->save();
				} catch (\Throwable $e) {
					Log::warning('Failed to seed transfer', [
						'employee_id' => $empId,
						'error'       => $e->getMessage(),
					]);
				}
			}
		}, 3);
	}
}
