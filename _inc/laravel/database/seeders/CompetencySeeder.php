<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\{AppModuleType, Visibility};
use App\Models\Competency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class CompetencySeeder extends Seeder
{
	private ConsoleOutput $out;

	public function run(): void
	{
		$this->out = new ConsoleOutput();

		try {
			$faker = fake('en_US');

			$companyIds = $this->fetchIdsRaw(DC::TABLE_USERS, "type in ('company','vendor')");
			$branchIds  = $this->fetchIdsRaw(DC::TABLE_BRANCHES);
			$jobIds     = $this->fetchIdsRaw(DC::TABLE_JOBS);

			$moduleCases = AppModuleType::cases();
			$perModule = [];
			$rawTotal = 0;

			foreach ($moduleCases as $case) {
				$n = random_int(2, 32);
				$perModule[$case->value] = $n;
				$rawTotal += $n;
			}

			$pad = $rawTotal % 64 === 0 ? 0 : (64 - ($rawTotal % 64));
			if ($pad > 0) {
				$idx = 0;
				while ($pad > 0) {
					$k = $moduleCases[$idx % count($moduleCases)]->value;
					$perModule[$k]++;
					$pad--;
					$idx++;
				}
			}

			$total = array_sum($perModule);
			$this->out->writeln("CompetencySeeder: modules=" . count($moduleCases) . " raw_total={$rawTotal} final_total={$total} (multiple of 64)");

			$created = [];
			$visibilityPool = [
				Visibility::Public,
				Visibility::Internal,
				Visibility::Private,
				Visibility::Draft,
				Visibility::Restricted,
				Visibility::Unlisted,
				Visibility::Protected,
				Visibility::Archived,
			];

			$levelsPool = ['None', 'Beginner', 'Intermediate', 'Advanced', 'Expert'];

			foreach ($moduleCases as $module) {
				$count = (int) ($perModule[$module->value] ?? 0);
				for ($i = 0; $i < $count; $i++) {
					$name = $this->uniqueNameForModule($module, $faker);

					$category = $faker->boolean(70) ? $faker->words(random_int(1, 2), true) : null;
					$family   = $faker->boolean(55) ? $faker->words(random_int(1, 2), true) : null;

					$desc  = $faker->boolean(80) ? $faker->paragraphs(random_int(1, 2), true) : null;
					$notes = $faker->boolean(50) ? $faker->sentence(random_int(6, 14)) : null;

					$visibility = $visibilityPool[array_rand($visibilityPool)];

					$levels = null;
					if ($faker->boolean(70)) {
						shuffle($levelsPool);
						$levels = array_values(array_slice($levelsPool, 0, random_int(1, 5)));
					}

					$tags = null;
					if ($faker->boolean(75)) {
						$tags = array_values(array_unique(array_map(
							fn() => Str::lower($faker->word()),
							range(1, random_int(2, 6))
						)));
					}

					$skills = null;
					if ($faker->boolean(60)) {
						$skills = array_values(array_unique(array_map(
							fn() => $faker->words(random_int(1, 3), true),
							range(1, random_int(1, 4))
						)));
					}

					$certifications = null;
					if ($faker->boolean(40)) {
						$certifications = array_values(array_unique(array_map(
							fn() => strtoupper($faker->bothify('CERT-###')) . ' ' . $faker->words(2, true),
							range(1, random_int(1, 3))
						)));
					}

					$requirements = null;
					if ($faker->boolean(55)) {
						$requirements = array_values(array_unique(array_map(
							fn() => $faker->sentence(random_int(5, 12)),
							range(1, random_int(1, 4))
						)));
					}

					$behaviors = null;
					if ($faker->boolean(50)) {
						$behaviors = array_values(array_unique(array_map(
							fn() => $faker->words(random_int(1, 2), true),
							range(1, random_int(1, 5))
						)));
					}

					$code = $this->uniqueCompetencyCode();

					$payload = [
						'code'        => $code,
						'name'        => $name,
						'module'      => $module->value,
						'category'    => $category,
						'family'      => $family,
						'description' => $desc,
						'notes'       => $notes,
						'type'        => $faker->boolean(10) ? (string) Str::uuid() : null,
						'visibility'  => $visibility->value,

						// hierarchy fields (nullable; PlansByHierarchy can auto-adjust)
						// leaving nulls explicitly (do not array_filter them away)
						'jobs'           => null,
						'projects'       => null,
						'companies'      => null,
						'branches'       => null,
						'departments'    => null,
						'levels'         => $levels,
						'skills'         => $skills,
						'certifications' => $certifications,
						'requirements'   => $requirements,
						'behaviors'      => $behaviors,
						'tags'           => $tags,
					];

					$this->out->writeln("CMPT create: module={$module->value} visibility={$payload['visibility']} name=\"{$name}\" code={$code}");

					$m = new Competency();
					foreach ($payload as $k => $v)
						$m->setAttribute($k, $v);

					$m->save();
					$created[] = $m->getAttribute('id');
				}
			}

			$this->out->writeln('CompetencySeeder: created=' . count($created));

			$this->applyCoverageRules($created, $companyIds, $branchIds, $jobIds);
		} catch (\Throwable $e) {
			Log::error(self::class . ' seeding failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
		}
	}

	private function applyCoverageRules(array $competencyIds, array $companyIds, array $branchIds, array $jobIds): void
	{
		try {
			$faker = fake('en_US');

			$competencies = Competency::query()
				->whereIn('id', $competencyIds)
				->get();

			if ($competencies->isEmpty()) {
				$this->out->writeln('CompetencySeeder: no competencies found for post-processing');
				return;
			}

			$compCount = $competencies->count();

			// --- RULE: at least 0.25 of companies are looking for at least 2 competencies
			$targetCompanies = max(0, (int) ceil(count($companyIds) * 0.25));
			$companySlice = $targetCompanies > 0 ? array_values(array_slice($companyIds, 0, $targetCompanies)) : [];

			// --- RULE: at least 0.1 of branches are looking for at least 2 competencies
			$targetBranches = max(0, (int) ceil(count($branchIds) * 0.1));
			$branchSlice = $targetBranches > 0 ? array_values(array_slice($branchIds, 0, $targetBranches)) : [];

			// --- RULE: at least 0.5 of jobs appear in competencies.jobs
			$targetJobs = max(0, (int) ceil(count($jobIds) * 0.5));
			$jobSlice = $targetJobs > 0 ? array_values(array_slice($jobIds, 0, $targetJobs)) : [];

			$this->out->writeln("Coverage targets: companies={$targetCompanies} branches={$targetBranches} jobs={$targetJobs}");

			// distribute companies across at least 2 competencies each
			$ci = 0;
			foreach ($companySlice as $companyId) {
				$a = $competencies[$ci % $compCount];
				$b = $competencies[($ci + 1) % $compCount];
				$ci++;

				$this->attachIdToJsonArray($a, 'companies', $companyId);
				$this->attachIdToJsonArray($b, 'companies', $companyId);

				if ($faker->boolean(35)) {
					$c = $competencies[($ci + 2) % $compCount];
					$this->attachIdToJsonArray($c, 'companies', $companyId);
				}
			}

			// distribute branches across at least 2 competencies each
			$bi = 0;
			foreach ($branchSlice as $branchId) {
				$a = $competencies[$bi % $compCount];
				$b = $competencies[($bi + 3) % $compCount];
				$bi++;

				$this->attachIdToJsonArray($a, 'branches', $branchId);
				$this->attachIdToJsonArray($b, 'branches', $branchId);

				if ($faker->boolean(25)) {
					$c = $competencies[($bi + 5) % $compCount];
					$this->attachIdToJsonArray($c, 'branches', $branchId);
				}
			}

			// ensure at least half of jobs appear at least once
			$ji = 0;
			foreach ($jobSlice as $jobId) {
				$m = $competencies[$ji % $compCount];
				$ji++;
				$this->attachIdToJsonArray($m, 'jobs', $jobId);
			}

			// add some random extra “jobs”/“projects”/“departments” spread for realism
			$projectsIds = $this->fetchIdsRaw(DC::TABLE_PROJECTS);
			$deptIds     = $this->fetchIdsRaw(DC::TABLE_DEPARTMENTS);

			foreach ($competencies as $m) {
				if ($faker->boolean(55) && !empty($jobIds))
					$this->attachManyIdsToJsonArray($m, 'jobs', $this->pickMany($jobIds, random_int(0, 6)));

				if ($faker->boolean(35) && !empty($projectsIds))
					$this->attachManyIdsToJsonArray($m, 'projects', $this->pickMany($projectsIds, random_int(0, 4)));

				if ($faker->boolean(35) && !empty($deptIds))
					$this->attachManyIdsToJsonArray($m, 'departments', $this->pickMany($deptIds, random_int(0, 4)));

				if ($faker->boolean(25) && !empty($branchIds))
					$this->attachManyIdsToJsonArray($m, 'branches', $this->pickMany($branchIds, random_int(0, 3)));

				if ($faker->boolean(25) && !empty($companyIds))
					$this->attachManyIdsToJsonArray($m, 'companies', $this->pickMany($companyIds, random_int(0, 3)));
			}

			$this->out->writeln('CompetencySeeder: post-processing completed');
		} catch (\Throwable $e) {
			Log::error(self::class . ' post-processing failed', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
		}
	}

	private function attachIdToJsonArray(Competency $m, string $field, string $id): void
	{
		try {
			$cur = $m->getAttribute($field);
			$arr = is_array($cur) ? $cur : (is_string($cur) ? (json_decode($cur, true) ?: []) : []);
			$arr[] = $id;
			$arr = array_values(array_unique(array_filter($arr, fn($v) => is_scalar($v) && trim((string) $v) !== '')));

			$this->out->writeln("CMPT update: id={$m->getAttribute('id')} {$field}+=1");
			$m->setAttribute($field, $arr);
			$m->save();
		} catch (\Throwable $e) {
			Log::error(self::class . " failed attaching {$field} id", [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'model_id' => $m->getAttribute('id') ?? null,
				'field' => $field,
				'attach_id' => $id,
			]);
		}
	}

	private function attachManyIdsToJsonArray(Competency $m, string $field, array $ids): void
	{
		try {
			$cur = $m->getAttribute($field);
			$arr = is_array($cur) ? $cur : (is_string($cur) ? (json_decode($cur, true) ?: []) : []);
			foreach ($ids as $id)
				$arr[] = $id;

			$arr = array_values(array_unique(array_filter($arr, fn($v) => is_scalar($v) && trim((string) $v) !== '')));

			$this->out->writeln("CMPT update: id={$m->getAttribute('id')} {$field}+=" . count($ids));
			$m->setAttribute($field, $arr ?: null);
			$m->save();
		} catch (\Throwable $e) {
			Log::error(self::class . " failed attaching many {$field} ids", [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'model_id' => $m->getAttribute('id') ?? null,
				'field' => $field,
				'count' => count($ids),
			]);
		}
	}

	private function pickMany(array $pool, int $n): array
	{
		if ($n <= 0 || empty($pool))
			return [];

		$max = min($n, count($pool));
		$keys = array_rand($pool, $max);
		if (!is_array($keys))
			$keys = [$keys];

		$out = [];
		foreach ($keys as $k)
			$out[] = (string) $pool[$k];

		return array_values(array_unique($out));
	}

	private function uniqueCompetencyCode(): string
	{
		$attempts = 0;
		do {
			$attempts++;
			$candidate = 'CMPT-' . (string) Str::uuid();

			try {
				$exists = DB::table(DC::TABLE_CMPT)->where('code', $candidate)->exists();
				if (!$exists)
					return $candidate;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' code existence check failed', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'candidate' => $candidate,
				]);
				return $candidate;
			}
		} while ($attempts < 25);

		$this->out->writeln('CompetencySeeder: code generation attempt limit hit; returning last candidate');
		return 'CMPT-' . (string) Str::uuid();
	}

	private function uniqueNameForModule(AppModuleType $module, $faker): string
	{
		$attempts = 0;
		do {
			$attempts++;

			$prefix = match ($module) {
				AppModuleType::Financial => 'Accounting',
				AppModuleType::Sales => 'Sales',
				AppModuleType::CRM => 'CRM',
				AppModuleType::HRM => 'PeopleOps',
				AppModuleType::Projects => 'Delivery',
				AppModuleType::Management => 'Governance',
				AppModuleType::Inventory => 'Inventory',
				AppModuleType::Support => 'Support',
				AppModuleType::Database => 'Data',
				AppModuleType::Infrastructure => 'Infra',
				AppModuleType::Marketing => 'Marketing',
				AppModuleType::Custom => 'Custom',
				AppModuleType::LandingPage => 'Landing',
				AppModuleType::User => 'Identity',
				AppModuleType::Customer => 'Customer',
				AppModuleType::Vendor => 'Vendor',
				AppModuleType::Product => 'Product',
				AppModuleType::Proposal => 'Proposal',
				AppModuleType::Invoice => 'Invoicing',
				AppModuleType::Bill => 'Billing',
				AppModuleType::Account => 'Accounts',
				default => 'General',
			};

			$candidate = trim($prefix . ' ' . Str::title($faker->words(random_int(2, 4), true)));
			if ($candidate === '')
				$candidate = $prefix . ' ' . Str::upper($faker->bothify('Skill-###'));

			try {
				$exists = DB::table(DC::TABLE_CMPT)
					->where('module', $module->value)
					->where('name', $candidate)
					->exists();

				if (!$exists)
					return $candidate;
			} catch (\Throwable $e) {
				Log::warning(self::class . ' name existence check failed', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'module' => $module->value,
					'candidate' => $candidate,
				]);
				return $candidate;
			}
		} while ($attempts < 20);

		return $module->value . ' ' . Str::upper($faker->bothify('COMP-###'));
	}

	private function fetchIdsRaw(string $table, string $whereSql = ''): array
	{
		try {
			$sql = 'select id from ' . $table;
			if (trim($whereSql) !== '')
				$sql .= ' where ' . $whereSql;

			$rows = DB::select($sql);
			$out = [];
			foreach ($rows as $r) {
				$id = is_object($r) && isset($r->id) ? (string) $r->id : null;
				if ($id !== null && trim($id) !== '')
					$out[] = $id;
			}
			return array_values(array_unique($out));
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed fetching ids', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'table' => $table,
				'where' => $whereSql,
			]);
			return [];
		}
	}
}
