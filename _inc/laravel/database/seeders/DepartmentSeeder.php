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
	// public const MIN_DEPTS_PER_BRANCH = 4;
	public const MIN_DEPTS_PER_BRANCH = 2;
	// public const MAX_DEPTS_PER_BRANCH = 32;
	public const MAX_DEPTS_PER_BRANCH = 2;
	// public const MIN_DSG_PER_DEPT = 2;
	public const MIN_DSG_PER_DEPT = 2;
	// public const MAX_DSG_PER_DEPT = 16;
	public const MAX_DSG_PER_DEPT = 2;

	private array $departmentNames = [
		'Financeiro',
		'Recursos Humanos',
		'Tecnologia da Informação',
		'Vendas',
	];

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$clock = microtime(true);
			// private const HARD_CAP = 128;
			$HARD_CAP = 4;
			// private const SECONDS_LIMIT = 300;
			$SECONDS_LIMIT = 32;

			$branches = Br::query()->select('id', 'company')->get();
			if ($branches->isEmpty()) {
				Log::warning(get_class($this) . ' aborted: no branches found.');
				return;
			}

			$userIds = Usr::query()->pluck('id')->all();
			if (!$userIds) {
				Log::warning(get_class($this) . ' aborted: no users found.');
				return;
			}
			$pickUser = fn() => $userIds[array_rand($userIds)];

			$availableNames = $this->departmentNames;
			shuffle($availableNames);
			$nameIndex = 0;
			$created = 0;

			foreach ($branches as $branch) {
				if ($created >= $HARD_CAP) break;
				if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;

				$deptsForBranch = min(self::MAX_DEPTS_PER_BRANCH, $HARD_CAP - $created);
				for ($i = 0; $i < $deptsForBranch; $i++) {
					if ($created >= $HARD_CAP) break;
					if ((microtime(true) - $clock) > $SECONDS_LIMIT) break;
					if ($nameIndex >= count($availableNames)) {
						$availableNames = $this->departmentNames;
						shuffle($availableNames);
						$nameIndex = 0;
					}
					$nm = $availableNames[$nameIndex++];
					try {
						if (Dpt::where(CPC::COL_BRC_ID, $branch->id)->where(CPC::COL_DEP_NM, $nm)->exists())
							continue;
						$budget = $faker->randomFloat(2, 10_000, 800_000);
						$expenses = $faker->randomFloat(2, 2_000, min($budget, 600_000));
						Dpt::create([
							'id' => Str::uuid()->toString(),
							'company' => $branch->company,
							CPC::COL_BRC_ID => $branch->id,
							CPC::COL_DEP_NM => $nm,
							'address' => $faker->address(),
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
						$created++;
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
			$elapsed = round(microtime(true) - $clock, 2);
			(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("[DepartmentSeeder] Done. Created: {$created} in {$elapsed}s");
		}, 3);
	}
}
