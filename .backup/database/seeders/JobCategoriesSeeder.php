<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{JobLevel, WorkContractType, WorkPresence, WorkShift};
use App\Models\Utility;
use Carbon\CarbonImmutable;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class JobCategoriesSeeder extends Seeder
{
	public function run(): void
	{
		$output = new \Symfony\Component\Console\Output\ConsoleOutput();
		$faker = FakerFactory::create();

		$table = DC::TABLE_JOB_CATS;

		$levels = JobLevel::cases();
		$presence = WorkPresence::cases();
		$contracts = WorkContractType::cases();
		$shifts = WorkShift::cases();

		$minRows = (int) env('SEED_JOB_CATS_MIN', 32);
		if ($minRows < 1) $minRows = 1;

		$hardCap = (int) env('SEED_JOB_CATS_MAX', 512);
		if ($hardCap < $minRows) $hardCap = $minRows;

		$chunkSize = (int) env('SEED_JOB_CATS_CHUNK', 1000);
		if ($chunkSize < 100) $chunkSize = 100;

		$fieldsPool = [
			'IT',
			'HR',
			'Finance',
			'Sales',
			'Marketing',
			'Support',
			'Operations',
			'Legal',
			'Product',
			'Design',
			'Engineering',
			'Data',
			'Security',
		];

		$rows = [];
		$total = 0;
		try {
			foreach ($levels as $i => $level) {
				if ($total >= $hardCap) break;

				$now = CarbonImmutable::now();
				$j = random_int(0, max(count($presence) - 1, 0));
				$k = random_int(0, max(count($contracts) - 1, 0));
				$l = random_int(0, max(count($shifts) - 1, 0));

				$levelVal = $level->value;
				$prsVal = $presence[$j]?->value;
				$ctcVal = $contracts[$k]?->value;
				$shfVal = $shifts[$l]?->value;

				$title = $this->buildTitle($faker, $levelVal, $prsVal, $ctcVal, $shfVal);
				$slug = $this->buildUniqueSlug($title, $now, $total);
				$code = $this->buildUniqueCode($now);

				$rows[] = [
					'id' => $this->uuid(),
					'title' => $this->safeStr($title, 254) ?? ('Job Category ' . ($total + 1)),
					'slug' => $this->safeStr($slug, 254),
					'code' => $this->safeStr($code, 128),
					'field' => $this->safeStr($fieldsPool[array_rand($fieldsPool)], 254),
					'description' => $faker->boolean(90) ? $faker->paragraphs(random_int(1, 3), true) : null,
					'requirements' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'skills' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'responsibilities' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'benefits' => $faker->boolean(70) ? $faker->paragraphs(1, true) : null,
					'incentives' => $faker->boolean(50) ? $faker->paragraphs(1, true) : null,
					'notes' => $faker->boolean(30) ? $faker->sentence(16) : null,
					AC::COL_IA => $faker->boolean(85),

					PJC::COL_ACP_LVLS => $this->safeJson($this->levelsPayload($levels, $i), $table . '.' . PJC::COL_ACP_LVLS),
					PJC::COL_ACP_PRS => $this->safeJson($this->presencePayload($presence, $j), $table . '.' . PJC::COL_ACP_PRS),
					PJC::COL_ACP_CTC_TP => $this->safeJson($this->contractsPayload($contracts, $k), $table . '.' . PJC::COL_ACP_CTC_TP),
					PJC::COL_ACP_SHFT => $this->safeJson($this->shiftsPayload($shifts, $l), $table . '.' . PJC::COL_ACP_SHFT),

					'certifications' => $this->safeJson($faker->boolean(25) ? $this->stringList($faker, 0, 3) : null, $table . '.certifications'),
					'attachments' => $this->safeJson($faker->boolean(20) ? $this->attachmentsList($faker) : null, $table . '.attachments'),
					'metadata' => $this->safeJson($faker->boolean(60) ? $this->metadataPayload($faker) : null, $table . '.metadata'),
					'companies' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.companies'),
					'branches' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.branches'),

					DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
					DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
					DC::COL_C_AT => $now,
					DC::COL_U_AT => $now,
				];
				$output->writeln("Ensured level representation: {$title}, slug {$slug}, code {$code}");
				$total++;

				if (count($rows) >= $chunkSize) {
					$this->flushChunk($table, $rows);
					$rows = [];
				}
			}
			foreach ($presence as $j => $prs) {
				if ($total >= $hardCap) break;

				$now = CarbonImmutable::now();
				$i = random_int(0, max(count($levels) - 1, 0));
				$k = random_int(0, max(count($contracts) - 1, 0));
				$l = random_int(0, max(count($shifts) - 1, 0));

				$levelVal = $levels[$i]?->value;
				$prsVal = $prs->value;
				$ctcVal = $contracts[$k]?->value;
				$shfVal = $shifts[$l]?->value;

				$title = $this->buildTitle($faker, $levelVal, $prsVal, $ctcVal, $shfVal);
				$slug = $this->buildUniqueSlug($title, $now, $total);
				$code = $this->buildUniqueCode($now);

				$rows[] = [
					'id' => $this->uuid(),
					'title' => $this->safeStr($title, 254) ?? ('Job Category ' . ($total + 1)),
					'slug' => $this->safeStr($slug, 254),
					'code' => $this->safeStr($code, 128),
					'field' => $this->safeStr($fieldsPool[array_rand($fieldsPool)], 254),
					'description' => $faker->boolean(90) ? $faker->paragraphs(random_int(1, 3), true) : null,
					'requirements' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'skills' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'responsibilities' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'benefits' => $faker->boolean(70) ? $faker->paragraphs(1, true) : null,
					'incentives' => $faker->boolean(50) ? $faker->paragraphs(1, true) : null,
					'notes' => $faker->boolean(30) ? $faker->sentence(16) : null,
					AC::COL_IA => $faker->boolean(85),

					PJC::COL_ACP_LVLS => $this->safeJson($this->levelsPayload($levels, $i), $table . '.' . PJC::COL_ACP_LVLS),
					PJC::COL_ACP_PRS => $this->safeJson($this->presencePayload($presence, $j), $table . '.' . PJC::COL_ACP_PRS),
					PJC::COL_ACP_CTC_TP => $this->safeJson($this->contractsPayload($contracts, $k), $table . '.' . PJC::COL_ACP_CTC_TP),
					PJC::COL_ACP_SHFT => $this->safeJson($this->shiftsPayload($shifts, $l), $table . '.' . PJC::COL_ACP_SHFT),

					'certifications' => $this->safeJson($faker->boolean(25) ? $this->stringList($faker, 0, 3) : null, $table . '.certifications'),
					'attachments' => $this->safeJson($faker->boolean(20) ? $this->attachmentsList($faker) : null, $table . '.attachments'),
					'metadata' => $this->safeJson($faker->boolean(60) ? $this->metadataPayload($faker) : null, $table . '.metadata'),
					'companies' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.companies'),
					'branches' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.branches'),

					DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
					DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
					DC::COL_C_AT => $now,
					DC::COL_U_AT => $now,
				];
				$output->writeln("Ensured presence representation: {$title}, slug {$slug}, code {$code}");
				$total++;

				if (count($rows) >= $chunkSize) {
					$this->flushChunk($table, $rows);
					$rows = [];
				}
			}
			foreach ($contracts as $k => $ctc) {
				if ($total >= $hardCap) break;

				$now = CarbonImmutable::now();
				$i = random_int(0, max(count($levels) - 1, 0));
				$j = random_int(0, max(count($presence) - 1, 0));
				$l = random_int(0, max(count($shifts) - 1, 0));

				$levelVal = $levels[$i]?->value;
				$prsVal = $presence[$j]?->value;
				$ctcVal = $ctc->value;
				$shfVal = $shifts[$l]?->value;

				$title = $this->buildTitle($faker, $levelVal, $prsVal, $ctcVal, $shfVal);
				$slug = $this->buildUniqueSlug($title, $now, $total);
				$code = $this->buildUniqueCode($now);

				$rows[] = [
					'id' => $this->uuid(),
					'title' => $this->safeStr($title, 254) ?? ('Job Category ' . ($total + 1)),
					'slug' => $this->safeStr($slug, 254),
					'code' => $this->safeStr($code, 128),
					'field' => $this->safeStr($fieldsPool[array_rand($fieldsPool)], 254),
					'description' => $faker->boolean(90) ? $faker->paragraphs(random_int(1, 3), true) : null,
					'requirements' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'skills' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'responsibilities' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'benefits' => $faker->boolean(70) ? $faker->paragraphs(1, true) : null,
					'incentives' => $faker->boolean(50) ? $faker->paragraphs(1, true) : null,
					'notes' => $faker->boolean(30) ? $faker->sentence(16) : null,
					AC::COL_IA => $faker->boolean(85),

					PJC::COL_ACP_LVLS => $this->safeJson($this->levelsPayload($levels, $i), $table . '.' . PJC::COL_ACP_LVLS),
					PJC::COL_ACP_PRS => $this->safeJson($this->presencePayload($presence, $j), $table . '.' . PJC::COL_ACP_PRS),
					PJC::COL_ACP_CTC_TP => $this->safeJson($this->contractsPayload($contracts, $k), $table . '.' . PJC::COL_ACP_CTC_TP),
					PJC::COL_ACP_SHFT => $this->safeJson($this->shiftsPayload($shifts, $l), $table . '.' . PJC::COL_ACP_SHFT),

					'certifications' => $this->safeJson($faker->boolean(25) ? $this->stringList($faker, 0, 3) : null, $table . '.certifications'),
					'attachments' => $this->safeJson($faker->boolean(20) ? $this->attachmentsList($faker) : null, $table . '.attachments'),
					'metadata' => $this->safeJson($faker->boolean(60) ? $this->metadataPayload($faker) : null, $table . '.metadata'),
					'companies' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.companies'),
					'branches' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.branches'),

					DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
					DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
					DC::COL_C_AT => $now,
					DC::COL_U_AT => $now,
				];
				$output->writeln("Ensured contract representation: {$title}, slug {$slug}, code {$code}");
				$total++;

				if (count($rows) >= $chunkSize) {
					$this->flushChunk($table, $rows);
					$rows = [];
				}
			}
			foreach ($shifts as $l => $shf) {
				if ($total >= $hardCap) break;

				$now = CarbonImmutable::now();
				$i = random_int(0, max(count($levels) - 1, 0));
				$j = random_int(0, max(count($presence) - 1, 0));
				$k = random_int(0, max(count($contracts) - 1, 0));

				$levelVal = $levels[$i]?->value;
				$prsVal = $presence[$j]?->value;
				$ctcVal = $contracts[$k]?->value;
				$shfVal = $shf->value;

				$title = $this->buildTitle($faker, $levelVal, $prsVal, $ctcVal, $shfVal);
				$slug = $this->buildUniqueSlug($title, $now, $total);
				$code = $this->buildUniqueCode($now);

				$rows[] = [
					'id' => $this->uuid(),
					'title' => $this->safeStr($title, 254) ?? ('Job Category ' . ($total + 1)),
					'slug' => $this->safeStr($slug, 254),
					'code' => $this->safeStr($code, 128),
					'field' => $this->safeStr($fieldsPool[array_rand($fieldsPool)], 254),
					'description' => $faker->boolean(90) ? $faker->paragraphs(random_int(1, 3), true) : null,
					'requirements' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'skills' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'responsibilities' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'benefits' => $faker->boolean(70) ? $faker->paragraphs(1, true) : null,
					'incentives' => $faker->boolean(50) ? $faker->paragraphs(1, true) : null,
					'notes' => $faker->boolean(30) ? $faker->sentence(16) : null,
					AC::COL_IA => $faker->boolean(85),

					PJC::COL_ACP_LVLS => $this->safeJson($this->levelsPayload($levels, $i), $table . '.' . PJC::COL_ACP_LVLS),
					PJC::COL_ACP_PRS => $this->safeJson($this->presencePayload($presence, $j), $table . '.' . PJC::COL_ACP_PRS),
					PJC::COL_ACP_CTC_TP => $this->safeJson($this->contractsPayload($contracts, $k), $table . '.' . PJC::COL_ACP_CTC_TP),
					PJC::COL_ACP_SHFT => $this->safeJson($this->shiftsPayload($shifts, $l), $table . '.' . PJC::COL_ACP_SHFT),

					'certifications' => $this->safeJson($faker->boolean(25) ? $this->stringList($faker, 0, 3) : null, $table . '.certifications'),
					'attachments' => $this->safeJson($faker->boolean(20) ? $this->attachmentsList($faker) : null, $table . '.attachments'),
					'metadata' => $this->safeJson($faker->boolean(60) ? $this->metadataPayload($faker) : null, $table . '.metadata'),
					'companies' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.companies'),
					'branches' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.branches'),

					DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
					DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
					DC::COL_C_AT => $now,
					DC::COL_U_AT => $now,
				];
				$output->writeln("Ensured shift representation: {$title}, slug {$slug}, code {$code}");
				$total++;

				if (count($rows) >= $chunkSize) {
					$this->flushChunk($table, $rows);
					$rows = [];
				}
			}
		} catch (\Throwable $e) {
			Log::warning("JobCategoriesSeeder first pass error: " . $e->getMessage
				. ', file ' . $e->getFile()
				. ', line ' . $e->getLine()
				. ', trace ' . $e->getTraceAsString());
		}
		try {
			for ($i = 0; $i < count($levels); $i++) {
				for ($j = 0; $j < count($presence); $j++) {
					for ($k = 0; $k < count($contracts); $k++) {
						for ($l = 0; $l < count($shifts); $l++) {
							if ($total >= $hardCap) break 4;

							$now = CarbonImmutable::now();

							$level = $levels[$i] ?? null;
							$prs = $presence[$j] ?? null;
							$ctc = $contracts[$k] ?? null;
							$shf = $shifts[$l] ?? null;

							$levelVal = $level?->value;
							$prsVal = $prs?->value;
							$ctcVal = $ctc?->value;
							$shfVal = $shf?->value;

							$title = $this->buildTitle($faker, $levelVal, $prsVal, $ctcVal, $shfVal);
							$slug = $this->buildUniqueSlug($title, $now, $total);
							$code = $this->buildUniqueCode($now);

							$rows[] = [
								'id' => $this->uuid(),
								'title' => $this->safeStr($title, 254) ?? ('Job Category ' . ($total + 1)),
								'slug' => $this->safeStr($slug, 254),
								'code' => $this->safeStr($code, 128),
								'field' => $this->safeStr($fieldsPool[array_rand($fieldsPool)], 254),
								'description' => $faker->boolean(90) ? $faker->paragraphs(random_int(1, 3), true) : null,
								'requirements' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
								'skills' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
								'responsibilities' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
								'benefits' => $faker->boolean(70) ? $faker->paragraphs(1, true) : null,
								'incentives' => $faker->boolean(50) ? $faker->paragraphs(1, true) : null,
								'notes' => $faker->boolean(30) ? $faker->sentence(16) : null,
								AC::COL_IA => $faker->boolean(85),

								PJC::COL_ACP_LVLS => $this->safeJson($this->levelsPayload($levels, $i), $table . '.' . PJC::COL_ACP_LVLS),
								PJC::COL_ACP_PRS => $this->safeJson($this->presencePayload($presence, $j), $table . '.' . PJC::COL_ACP_PRS),
								PJC::COL_ACP_CTC_TP => $this->safeJson($this->contractsPayload($contracts, $k), $table . '.' . PJC::COL_ACP_CTC_TP),
								PJC::COL_ACP_SHFT => $this->safeJson($this->shiftsPayload($shifts, $l), $table . '.' . PJC::COL_ACP_SHFT),

								'certifications' => $this->safeJson($faker->boolean(25) ? $this->stringList($faker, 0, 3) : null, $table . '.certifications'),
								'attachments' => $this->safeJson($faker->boolean(20) ? $this->attachmentsList($faker) : null, $table . '.attachments'),
								'metadata' => $this->safeJson($faker->boolean(60) ? $this->metadataPayload($faker) : null, $table . '.metadata'),
								'companies' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.companies'),
								'branches' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.branches'),

								DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
								DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
								DC::COL_C_AT => $now,
								DC::COL_U_AT => $now,
							];
							$output->writeln("Prepared job category {$title}, slug {$slug}, code {$code}");
							$total++;

							if (count($rows) >= $chunkSize) {
								$this->flushChunk($table, $rows);
								$rows = [];
							}
						}
					}
				}
			}

			while ($total < $minRows && $total < $hardCap) {
				$now = CarbonImmutable::now();

				$idxLevel = random_int(0, max(count($levels) - 1, 0));
				$idxPrs = random_int(0, max(count($presence) - 1, 0));
				$idxCtc = random_int(0, max(count($contracts) - 1, 0));
				$idxShf = random_int(0, max(count($shifts) - 1, 0));

				$levelVal = $levels[$idxLevel]?->value ?? null;
				$prsVal = $presence[$idxPrs]?->value ?? null;
				$ctcVal = $contracts[$idxCtc]?->value ?? null;
				$shfVal = $shifts[$idxShf]?->value ?? null;

				$title = $this->buildTitle($faker, $levelVal, $prsVal, $ctcVal, $shfVal);
				$slug = $this->buildUniqueSlug($title, $now, $total);
				$code = $this->buildUniqueCode($now);

				$rows[] = [
					'id' => $this->uuid(),
					'title' => $this->safeStr($title, 254) ?? ('Job Category ' . ($total + 1)),
					'slug' => $this->safeStr($slug, 254),
					'code' => $this->safeStr($code, 128),
					'field' => $this->safeStr($fieldsPool[array_rand($fieldsPool)], 254),
					'description' => $faker->paragraphs(random_int(1, 3), true),
					'requirements' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'skills' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'responsibilities' => $faker->boolean(85) ? $faker->paragraphs(random_int(1, 2), true) : null,
					'benefits' => $faker->boolean(70) ? $faker->paragraphs(1, true) : null,
					'incentives' => $faker->boolean(50) ? $faker->paragraphs(1, true) : null,
					'notes' => $faker->boolean(30) ? $faker->sentence(16) : null,
					AC::COL_IA => $faker->boolean(85),

					PJC::COL_ACP_LVLS => $this->safeJson($this->levelsPayload($levels, $idxLevel), $table . '.' . PJC::COL_ACP_LVLS),
					PJC::COL_ACP_PRS => $this->safeJson($this->presencePayload($presence, $idxPrs), $table . '.' . PJC::COL_ACP_PRS),
					PJC::COL_ACP_CTC_TP => $this->safeJson($this->contractsPayload($contracts, $idxCtc), $table . '.' . PJC::COL_ACP_CTC_TP),
					PJC::COL_ACP_SHFT => $this->safeJson($this->shiftsPayload($shifts, $idxShf), $table . '.' . PJC::COL_ACP_SHFT),

					'certifications' => $this->safeJson($faker->boolean(25) ? $this->stringList($faker, 0, 3) : null, $table . '.certifications'),
					'attachments' => $this->safeJson($faker->boolean(20) ? $this->attachmentsList($faker) : null, $table . '.attachments'),
					'metadata' => $this->safeJson($faker->boolean(60) ? $this->metadataPayload($faker) : null, $table . '.metadata'),
					'companies' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.companies'),
					'branches' => $this->safeJson($faker->boolean(35) ? $this->stringList($faker, 1, 4) : null, $table . '.branches'),

					DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
					DC::COL_TABLE_UPDATER => DC::DEFAULT_UUID,
					DC::COL_C_AT => $now,
					DC::COL_U_AT => $now,
				];

				$total++;

				if (count($rows) >= $chunkSize) {
					$this->flushChunk($table, $rows);
					$rows = [];
				}
			}

			if ($rows !== [])
				$this->flushChunk($table, $rows);

			Log::info(self::class . ' seeded job_categories', [
				'table' => $table,
				'total' => $total,
				'min' => $minRows,
				'hard_cap' => $hardCap,
				'chunk' => $chunkSize,
			]);
		} catch (\Throwable $e) {
			Log::error(self::class . ' failed seeding job_categories', [
				'table' => $table,
				'inserted_so_far' => $total,
				'error' => $e->getMessage(),
			]);
			throw $e;
		}
	}

	private function flushChunk(string $table, array $rows): void
	{
		try {
			DB::table($table)->insert($rows);
		} catch (\Throwable $e) {
			Log::error(self::class . ' chunk insert failed', [
				'table' => $table,
				'rows' => count($rows),
				'error' => $e->getMessage(),
			]);
			throw $e;
		}
	}

	private function uuid(): string
	{
		try {
			if (class_exists(Utility::class) && method_exists(Utility::class, 'generateUuid')) {
				$u = Utility::generateUuid();
				if (is_string($u) && $u !== '') return $u;
			}
		} catch (\Throwable $e) {
			Log::debug(self::class . ' Utility::generateUuid failed; falling back', ['error' => $e->getMessage()]);
		}

		return (string) Str::uuid();
	}

	private function safeStr(mixed $value, int $maxLen): ?string
	{
		if (!is_scalar($value)) return null;
		$s = trim((string) $value);
		if ($s === '') return null;
		if (mb_strlen($s) > $maxLen) $s = mb_substr($s, 0, $maxLen);
		return $s;
	}

	private function safeJson(mixed $value, string $context): ?string
	{
		if ($value === null) return null;

		try {
			return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (\Throwable $e) {
			Log::warning(self::class . ' json_encode failed; storing null', [
				'context' => $context,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	private function buildTitle($faker, ?string $level, ?string $presence, ?string $contract, ?string $shift): string
	{
		$role = $faker->randomElement([
			'Software Engineer',
			'Data Analyst',
			'Product Manager',
			'UX Designer',
			'HR Specialist',
			'Accountant',
			'Sales Executive',
			'Support Agent',
			'Security Analyst',
			'DevOps Engineer',
			'QA Engineer',
		]);

		$bits = array_filter([
			$level ? Str::of($level)->replace('_', ' ')->title()->toString() : null,
			$role,
			$presence ? Str::of($presence)->replace('_', ' ')->title()->toString() : null,
			$contract ? Str::of($contract)->replace('_', ' ')->upper()->toString() : null,
			$shift ? Str::of($shift)->replace('_', ' ')->title()->toString() : null,
		], fn($v) => is_string($v) && trim($v) !== '');

		return implode(' • ', $bits) ?: $role;
	}

	private function buildUniqueSlug(string $title, CarbonImmutable $now, int $seedIndex): string
	{
		$base = Str::snake(Str::lower(Str::ascii($title)));
		$base = preg_replace('/[^a-z0-9_]/', '', (string) $base);
		$base = trim((string) $base, '_');

		if ($base === '')
			$base = 'job_cat';

		$suffix = $now->format('Ymd_His') . '_' . $seedIndex;
		$out = $base . '_' . $suffix;

		return mb_strlen($out) <= 254 ? $out : mb_substr($out, 0, 254);
	}

	private function buildUniqueCode(CarbonImmutable $now): string
	{
		$uuid = $this->uuid();
		$out = 'JB-CAT-' . $uuid . '-' . $now->format('YmdHis');

		return mb_strlen($out) <= 128 ? $out : mb_substr($out, 0, 128);
	}

	private function levelsPayload(array $cases, int $idx): array
	{
		$out = [];
		$primary = $cases[$idx]?->value ?? null;
		if (is_string($primary) && $primary !== '') $out[] = $primary;

		if ($idx > 0 && random_int(0, 100) < 35) {
			$prev = $cases[$idx - 1]?->value ?? null;
			if (is_string($prev) && $prev !== '') $out[] = $prev;
		}

		if (isset($cases[$idx + 1]) && random_int(0, 100) < 35) {
			$next = $cases[$idx + 1]?->value ?? null;
			if (is_string($next) && $next !== '') $out[] = $next;
		}

		$out = array_values(array_unique($out));
		return $out ?: [];
	}

	private function presencePayload(array $cases, int $idx): array
	{
		$out = [];
		$primary = $cases[$idx]?->value ?? null;
		if (is_string($primary) && $primary !== '') $out[] = $primary;

		if (random_int(0, 100) < 25 && count($cases) > 1) {
			$other = $cases[random_int(0, count($cases) - 1)]?->value ?? null;
			if (is_string($other) && $other !== '') $out[] = $other;
		}

		$out = array_values(array_unique($out));
		return $out ?: [];
	}

	private function contractsPayload(array $cases, int $idx): array
	{
		$out = [];
		$primary = $cases[$idx]?->value ?? null;
		if (is_string($primary) && $primary !== '') $out[] = $primary;

		if (random_int(0, 100) < 25 && count($cases) > 1) {
			$other = $cases[random_int(0, count($cases) - 1)]?->value ?? null;
			if (is_string($other) && $other !== '') $out[] = $other;
		}

		$out = array_values(array_unique($out));
		return $out ?: [];
	}

	private function shiftsPayload(array $cases, int $idx): array
	{
		$out = [];
		$primary = $cases[$idx]?->value ?? null;
		if (is_string($primary) && $primary !== '') $out[] = $primary;

		if (random_int(0, 100) < 25 && count($cases) > 1) {
			$other = $cases[random_int(0, count($cases) - 1)]?->value ?? null;
			if (is_string($other) && $other !== '') $out[] = $other;
		}

		$out = array_values(array_unique($out));
		return $out ?: [];
	}

	private function stringList($faker, int $min, int $max): array
	{
		$n = $max <= 0 ? 0 : random_int($min, $max);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$s = trim((string) $faker->words(random_int(1, 3), true));
			if ($s !== '') $out[] = $s;
		}
		return array_values(array_unique($out));
	}

	private function attachmentsList($faker): array
	{
		$n = random_int(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$payload = [];

			if ($faker->boolean(70)) $payload['id'] = $this->uuid();
			if ($faker->boolean(60)) $payload['name'] = $faker->words(random_int(2, 5), true);
			if ($faker->boolean(40)) $payload['file_path'] = 'docs/' . Str::slug($faker->words(3, true)) . '.pdf';

			if ($payload !== []) $out[] = $payload;
		}
		return $out;
	}

	private function metadataPayload($faker): array
	{
		return [
			'source' => $faker->randomElement(['seed', 'import', 'legacy', 'admin']),
			'tags' => $this->stringList($faker, 0, 4),
			'priority' => $faker->randomElement(['low', 'medium', 'high']),
			'region' => $faker->randomElement(['br', 'latam', 'na', 'eu', 'apac']),
		];
	}
}
