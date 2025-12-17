<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\CompaniesConstants as CPC;

use App\Models\{Department as Dpt, Branch as Br, User as Usr};

final class DepartmentSeeder extends Seeder
{
	public const MIN_DEPTS_PER_BRANCH = 4;
	public const MAX_DEPTS_PER_BRANCH = 32;
	public const MIN_DSG_PER_DEPT = 2;
	public const MAX_DSG_PER_DEPT = 16;

	private array $departmentNames = [
		'Financeiro',
		'Recursos Humanos',
		'Tecnologia da Informação',
		'Vendas',
		'Operações',
		'Marketing',
		'Jurídico',
		'Logística',
		'Compras',
		'Suporte',
		'Pesquisa e Desenvolvimento',
		'Atendimento ao Cliente',
		'Produção',
		'Qualidade',
		'Comunicação Corporativa',
		'Relações Públicas',
		'Desenvolvimento de Negócios',
		'Planejamento Estratégico',
		'Segurança da Informação',
		'Gestão de Projetos',
		'Suporte Técnico',
		'Administração',
		'Contabilidade',
		'Desenvolvimento de Produto',
		'Engenharia',
		'Design',
		'Eventos',
		'Treinamento e Desenvolvimento',
		'Saúde e Segurança Ocupacional',
		'Sustentabilidade',
		'Inovação',
		'Riscos e Conformidade'
	];

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$branches = Br::query()->select('id', 'company')->get();

			if ($branches->isEmpty()) {
				Log::warning(get_class($this) . ' aborted: no branches found.');
				return;
			}

			$userIds = Usr::query()->pluck('id')->all();
			$userCount = count($userIds);

			if ($userCount === 0) {
				Log::warning(get_class($this) . ' aborted: no users found.');
				return;
			}

			$absoluteMaxDepts = (int) floor($userCount * 0.5);
			$this->command?->info(get_class($this) . ": absolute maximum departments: {$absoluteMaxDepts} (based on {$userCount} users)");

			$pickUser = fn() => $userIds[array_rand($userIds)];

			$branchDeptCounts = [];
			$totalDeptsToCreate = 0;

			foreach ($branches as $branch) {
				$branchDeptCounts[$branch->id] = self::MIN_DEPTS_PER_BRANCH;
				$totalDeptsToCreate += self::MIN_DEPTS_PER_BRANCH;
			}

			if ($totalDeptsToCreate > $absoluteMaxDepts) {
				$this->command?->warn(get_class($this) . ": minimum departments ({$totalDeptsToCreate}) exceeds absolute max ({$absoluteMaxDepts}). Limiting to absolute max.");
				$totalDeptsToCreate = $absoluteMaxDepts;
			}

			$remainingSlots = $absoluteMaxDepts - $totalDeptsToCreate;

			foreach ($branches as $branch) {
				if ($remainingSlots <= 0)
					break;

				$currentCount = $branchDeptCounts[$branch->id];
				$possibleExtra = min(
					self::MAX_DEPTS_PER_BRANCH - $currentCount,
					$remainingSlots
				);

				if ($possibleExtra > 0) {
					$extraToAdd = random_int(0, $possibleExtra);
					$branchDeptCounts[$branch->id] += $extraToAdd;
					$totalDeptsToCreate += $extraToAdd;
					$remainingSlots -= $extraToAdd;
				}
			}

			$this->command?->info(get_class($this) . ": planning to create {$totalDeptsToCreate} departments across {$branches->count()} branches (limit: {$absoluteMaxDepts})");

			$availableNames = $this->departmentNames;
			shuffle($availableNames);
			$nameIndex = 0;
			$globalCreatedCount = 0;

			foreach ($branches as $branch) {
				if ($globalCreatedCount >= $absoluteMaxDepts) {
					$this->command?->warn("Reached absolute maximum ({$absoluteMaxDepts}). Stopping department creation.");
					break;
				}

				$deptsForThisBranch = $branchDeptCounts[$branch->id];
				$branchCompany = $branch->company;
				$createdForBranch = 0;

				$this->command?->info("Creating {$deptsForThisBranch} departments for branch {$branch->id}");
				$attempts = 0;
				while ($createdForBranch < $deptsForThisBranch && $globalCreatedCount < $absoluteMaxDepts) {
					if ($globalCreatedCount >= $absoluteMaxDepts) {
						$this->command?->warn("Reached absolute maximum ({$absoluteMaxDepts}). Stopping department creation.");
						break;
					}
					$attempts++;
					if ($attempts > $deptsForThisBranch * self::MIN_DEPTS_PER_BRANCH) {
						$this->command?->warn("Too many attempts ({$attempts}) to create departments for branch {$branch->id}. Moving to next branch.");
						break;
					}
					if ($nameIndex >= count($availableNames)) {
						$availableNames = $this->departmentNames;
						shuffle($availableNames);
						$nameIndex = 0;
					}

					$nm = $availableNames[$nameIndex++];

					try {
						$existingDept = Dpt::query()
							->where(CPC::COL_BRC_ID, $branch->id)
							->where(CPC::COL_DEP_NM, $nm)
							->exists();

						if ($existingDept)
							continue;

						$budget = $faker->randomFloat(2, 10_000, 800_000);
						$expenses = $faker->randomFloat(2, 2_000, min($budget, 600_000));

						$d = Dpt::create([
							'id' => Str::uuid()->toString(),
							'company' => $branchCompany,
							CPC::COL_BRC_ID => $branch->id,
							CPC::COL_DEP_NM => $nm,
							'description' => $faker->boolean(50) ? $faker->sentence(10) : null,
							'phone' => $faker->boolean(60) ? $faker->cellphoneNumber() : null,
							'email' => $faker->boolean(60) ? $faker->unique()->safeEmail() : null,
							CPC::COL_MNG => $faker->boolean(70) ? $pickUser() : null,
							'budget' => $budget,
							'expenses' => $expenses,
							'profit' => max(0, $budget - $expenses),
							DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
							DC::COL_TABLE_UPDATER => null,
						]);
						$attemps = 0;
						$createdForBranch++;
						$globalCreatedCount++;
						$this->command?->info("  [{$globalCreatedCount}/{$absoluteMaxDepts}] Created: {$nm} ({$d->id})");
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed to create department: ' . $e->getMessage(), [
							'branch_id' => $branch->id,
							'department_name' => $nm,
						]);
						continue;
					}
				}

				if ($globalCreatedCount >= $absoluteMaxDepts) {
					$this->command?->warn("Reached absolute maximum ({$absoluteMaxDepts}). Stopping.");
					break;
				}
			}

			$this->command?->info(get_class($this) . ": successfully created {$globalCreatedCount} departments (limit was {$absoluteMaxDepts}).");
		}, 3);
	}
}
