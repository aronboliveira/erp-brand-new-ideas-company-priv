<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	MessagesConstants as MC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Enums\{AvailableLang, EvaluationStatus};
use App\Models\CompanyPolicy;
use App\Traits\{EnsuresSystemUser, EvaluatesMemory};
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class CompanyPolicySeeder extends Seeder
{
	use EvaluatesMemory, EnsuresSystemUser;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;
	private const HARD_CAP = 4;
	public function run(): void
	{
		$clock = microtime(true);
		$systemUserId = $this->ensureSystemUser();
		$this->checkMemoryUsage();

		$table = DC::TABLE_CPN_POL;

		if (!Schema::hasTable($table)) {
			Log::warning(static::class . ' table missing; skipping seeder', ['table' => $table]);
			return;
		}

		$hasCreatedBy = Schema::hasColumn($table, DC::COL_TABLE_CREATOR);
		$hasUpdatedBy = Schema::hasColumn($table, DC::COL_TABLE_UPDATER);

		$companyIds = $this->fetchCompanyUserIds();
		$n = max(1, count($companyIds));
		$target = $this->resolveCountMultipleOf64($n);
		$target = min($target, 256);

		$branchIds = $this->fetchBranchIdsMaybe(); // pode retornar []
		$signerMap = $this->fetchSignerMap();      // [id => name]
		$ackIds    = $this->fetchAcknowledgerUserIds($signerMap);

		$existing = $this->fetchExistingUniqueKeys($table);
		$usedCodes = $existing['codes'];
		$usedUrls  = $existing['urls'];
		$usedCompanies = $existing['companies'];
		$usedCompanyBranch = $existing['company_branch'];

		$langs = AvailableLang::values();
		$faker = fake('pt_BR');

		$availableCompanies = [];
		foreach ($companyIds as $cid) {
			if (!isset($usedCompanies[$cid]))
				$availableCompanies[] = $cid;
		}
		shuffle($availableCompanies);

		DB::beginTransaction();
		try {
			$output = new \Symfony\Component\Console\Output\ConsoleOutput();
			for ($i = 0; $i < $target; $i++) {
				if ($i >= self::HARD_CAP) break; /* HARD_CAP guard */
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					$output->writeln("Reached time limit of " . self::SECONDS_LIMIT . " seconds; stopping seeder.");
					break;
				}
				if (($i % 8) === 0) $this->checkMemoryUsage();

				$title = $faker->sentence(mt_rand(3, 7));
				$code  = $this->uniqueCode($table, $usedCodes);

				$url = null;
				if (mt_rand(1, 100) <= 65) {
					$url = $this->uniqueUrl($table, $usedUrls, Str::slug($title));
				}

				$company = null;
				$branch  = null;

				// company é UNIQUE => no máximo 1 policy por company (o resto será company-wide)
				if (!empty($availableCompanies) && mt_rand(1, 100) <= 20) {
					$company = array_pop($availableCompanies);
					$usedCompanies[$company] = true;

					if (!empty($branchIds) && mt_rand(1, 100) <= 40)
						$branch = $branchIds[array_rand($branchIds)];

					$pairKey = ($company ?? 'null') . '|' . ($branch ?? 'null');
					if (isset($usedCompanyBranch[$pairKey])) {
						$branch = null;
						$pairKey = ($company ?? 'null') . '|' . ($branch ?? 'null');
					}
					$usedCompanyBranch[$pairKey] = true;
				}

				$defaultLang    = $this->weightedLangPick($langs);
				$availableLangs = $this->pickAvailableLangs($langs, $defaultLang);

				$attachments = $this->fakeAttachments();
				$urls = $this->fakeUrls($faker, $url);

				[$signedBy, $signedByName] = $this->pickSigner($signerMap);

				$policy = new CompanyPolicy();

				$policy->setAttribute('code', $code);
				$policy->setAttribute('title', $title);
				$policy->setAttribute('url', $url);

				$policy->setAttribute('email', (mt_rand(1, 100) <= 75) ? $faker->companyEmail() : null);
				$policy->setAttribute('phone', (mt_rand(1, 100) <= 75) ? $this->fakeBrazilPhone($faker) : null);

				$policy->setAttribute('summary', (mt_rand(1, 100) <= 80) ? $faker->text(220) : null);
				$policy->setAttribute('version', $faker->randomFloat(2, 1, 5));

				$policy->setAttribute('company', $company);
				$policy->setAttribute('branch', $branch);

				$policy->setAttribute('description', $faker->paragraphs(mt_rand(2, 5), true));
				$policy->setAttribute(SC::DEF_LNG, $defaultLang);
				$policy->setAttribute('status', $this->weightedStatusPick());

				// Exercita syncLegacyAttachmentsToJson() do Model
				$policy->setAttribute('attachment', (mt_rand(1, 100) <= 20 && !empty($attachments)) ? $attachments[0] : null);
				$policy->setAttribute('file', (mt_rand(1, 100) <= 15 && count($attachments) > 1) ? $attachments[1] : null);
				$policy->setAttribute('attachments', $attachments);

				$policy->setAttribute(MC::COL_AV_LG, $availableLangs);
				$policy->setAttribute('acknowledgers', $this->pickUuidList($ackIds, mt_rand(0, 5)));
				$policy->setAttribute(DC::COL_LEGAL_REP, $this->fakeLegalReps($faker));
				$policy->setAttribute('urls', $urls);

				$policy->setAttribute(BC::COL_SIGN_BY_NAME, $signedByName);
				$policy->setAttribute(BC::COL_SIGN_BY, $signedBy);
				$policy->setAttribute(BC::COL_SIGN_AT, $signedBy ? $faker->dateTimeBetween('-18 months', 'now') : null);

				if ($hasCreatedBy) $policy->setAttribute(DC::COL_TABLE_CREATOR, $systemUserId);
				if ($hasUpdatedBy) $policy->setAttribute(DC::COL_TABLE_UPDATER, $systemUserId);
				// $output->writeln("Seeding company {$company} policy: {$title} ({$code})");
				$policy->save();

				unset($policy);
			}

			DB::commit();
		} catch (\Throwable $e) {
			DB::rollBack();
			Log::error(static::class . ' seeding failed', [
				'table' => $table,
				'error' => $e->getMessage(),
			]);
			throw $e;
		}
	}

	private function resolveCountMultipleOf64(int $n): int
	{
		$requested = 0;

		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			$opt = $this->command->option('count');
			$requested = is_numeric($opt) ? (int) $opt : 0;
		}

		$base = 2 * max(1, $n);
		$count = $requested > 0 ? $requested : $base;

		// Ajusta SEMPRE para múltiplo de 2 (arredondando para cima)
		$mult = (int) ceil($count / 2);
		$out = max(64, $mult * 2);

		return $out;
	}

	private function isSafeIdent(string $v): bool
	{
		return (bool) preg_match('/^[A-Za-z0-9_]+$/', $v);
	}

	private function fetchCompanyUserIds(): array
	{
		$table = DC::TABLE_USERS;
		$typeCol = UC::COL_TP;

		if (!$this->isSafeIdent($table) || !$this->isSafeIdent($typeCol))
			return [];

		try {
			$rows = DB::select("SELECT id FROM {$table} WHERE LOWER({$typeCol}) = ? AND id IS NOT NULL", ['company']);
			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (is_string($id) && trim($id) !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' could not fetch company users', ['error' => $e->getMessage()]);
			return [];
		}
	}

	private function fetchBranchIdsMaybe(): array
	{
		// Não “assume” nome da tabela/constante; tenta detectar via DC se existir.
		try {
			$const = DC::class . '::TABLE_BRANCHES';
			if (!defined($const)) return [];
			$table = constant($const);

			if (!is_string($table) || !$this->isSafeIdent($table)) return [];
			if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) return [];

			$rows = DB::select("SELECT id FROM {$table} WHERE id IS NOT NULL");
			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				if (is_string($id) && trim($id) !== '')
					$out[] = $id;
			}
			return $out;
		} catch (\Throwable) {
			return [];
		}
	}

	private function fetchSignerMap(): array
	{
		$table = DC::TABLE_USERS;
		$nameCol = UC::COL_NM;

		if (!$this->isSafeIdent($table) || !$this->isSafeIdent($nameCol))
			return [];

		try {
			$rows = DB::select("SELECT id, {$nameCol} AS nm FROM {$table} WHERE id IS NOT NULL");
			$out = [];
			foreach ($rows as $r) {
				$id = $r->id ?? null;
				$nm = $r->nm ?? null;
				if (is_string($id) && trim($id) !== '' && is_string($nm))
					$out[$id] = $nm;
			}
			return $out;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' could not fetch signers', ['error' => $e->getMessage()]);
			return [];
		}
	}

	private function fetchAcknowledgerUserIds(array $signerMap): array
	{
		// Tenta pegar de employees.user_id se existir; senão, usa ids de users
		try {
			$empConst = DC::class . '::TABLE_EMPLOYEES';
			if (defined($empConst)) {
				$empTable = constant($empConst);
				$userIdCol = UC::COL_USER_ID;

				if (
					is_string($empTable) && $this->isSafeIdent($empTable) && $this->isSafeIdent($userIdCol)
					&& Schema::hasTable($empTable) && Schema::hasColumn($empTable, $userIdCol)
				) {

					$rows = DB::select("SELECT {$userIdCol} AS uid FROM {$empTable} WHERE {$userIdCol} IS NOT NULL");
					$uniq = [];
					foreach ($rows as $r) {
						$uid = $r->uid ?? null;
						if (is_string($uid) && trim($uid) !== '')
							$uniq[$uid] = true;
					}
					$out = array_values(array_keys($uniq));
					if (!empty($out)) return $out;
				}
			}
		} catch (\Throwable) {
		}

		return array_values(array_keys($signerMap));
	}

	private function fetchExistingUniqueKeys(string $table): array
	{
		$codes = [];
		$urls  = [];
		$companies = [];
		$pairs = [];

		if (!$this->isSafeIdent($table))
			return ['codes' => [], 'urls' => [], 'companies' => [], 'company_branch' => []];

		try {
			$rows = DB::select("SELECT code, url, company, branch FROM {$table}");
			foreach ($rows as $r) {
				$code = $r->code ?? null;
				$url  = $r->url ?? null;
				$company = $r->company ?? null;
				$branch  = $r->branch ?? null;

				if (is_string($code) && trim($code) !== '') $codes[$code] = true;
				if (is_string($url)  && trim($url)  !== '') $urls[$url]  = true;
				if (is_string($company) && trim($company) !== '') $companies[$company] = true;

				$pair = (is_string($company) ? $company : 'null') . '|' . (is_string($branch) ? $branch : 'null');
				$pairs[$pair] = true;
			}
		} catch (\Throwable $e) {
			Log::debug(static::class . ' could not fetch existing keys', [
				'table' => $table,
				'error' => $e->getMessage(),
			]);
		}

		return [
			'codes' => $codes,
			'urls'  => $urls,
			'companies' => $companies,
			'company_branch' => $pairs,
		];
	}

	private function uniqueCode(string $table, array &$usedCodes): string
	{
		$tries = 0;
		do {
			$tries++;
			$code = (string) Str::uuid();
		} while ((isset($usedCodes[$code]) || $this->existsByColumn($table, 'code', $code)) && $tries < 25);

		if (isset($usedCodes[$code]) || $this->existsByColumn($table, 'code', $code))
			$code = (string) Str::uuid();

		$usedCodes[$code] = true;
		return $code;
	}

	private function uniqueUrl(string $table, array &$usedUrls, string $slug): string
	{
		$tries = 0;
		do {
			$tries++;
			$suffix = Str::lower(Str::random(10));
			$url = "https://intranet.example/policies/{$slug}-{$suffix}";
		} while ((isset($usedUrls[$url]) || $this->existsByColumn($table, 'url', $url)) && $tries < 25);

		if (isset($usedUrls[$url]) || $this->existsByColumn($table, 'url', $url))
			$url = "https://intranet.example/policies/" . (string) Str::uuid();

		$usedUrls[$url] = true;
		return $url;
	}

	private function existsByColumn(string $table, string $col, string $value): bool
	{
		if (!$this->isSafeIdent($table) || !$this->isSafeIdent($col))
			return false;

		try {
			return DB::selectOne("SELECT 1 AS x FROM {$table} WHERE {$col} = ? LIMIT 1", [$value]) !== null;
		} catch (\Throwable) {
			return false;
		}
	}

	private function weightedLangPick(array $langs): string
	{
		$pool = array_merge(
			array_fill(0, 6, 'pt-br'),
			array_fill(0, 6, 'en'),
			$langs
		);
		return $pool[array_rand($pool)];
	}

	private function pickAvailableLangs(array $langs, string $default): array
	{
		$count = mt_rand(1, min(4, max(1, count($langs))));
		$picked = [$default];

		for ($i = 1; $i < $count; $i++)
			$picked[] = $langs[array_rand($langs)];

		$uniq = [];
		foreach ($picked as $v) {
			if (is_string($v) && trim($v) !== '')
				$uniq[$v] = true;
		}
		return array_values(array_keys($uniq));
	}

	private function weightedStatusPick(): string
	{
		$pool = [
			EvaluationStatus::Draft->value,
			EvaluationStatus::Draft->value,
			EvaluationStatus::Pending->value,
			EvaluationStatus::Active->value,
			EvaluationStatus::Completed->value,
			EvaluationStatus::Archived->value,
		];
		return $pool[array_rand($pool)];
	}

	private function fakeBrazilPhone($faker): string
	{
		return '+55 (' . $faker->numberBetween(11, 99) . ') '
			. $faker->numberBetween(90000, 99999) . '-'
			. $faker->numberBetween(1000, 9999);
	}

	private function fakeAttachments(): array
	{
		$out = [];
		$n = mt_rand(0, 3);
		for ($i = 0; $i < $n; $i++)
			$out[] = 'uploads/policies/' . (string) Str::uuid() . '.pdf';
		return $out;
	}

	private function fakeUrls($faker, ?string $primary): array
	{
		$uniq = [];

		if (is_string($primary) && trim($primary) !== '')
			$uniq[$primary] = true;

		$n = mt_rand(0, 2);
		for ($i = 0; $i < $n; $i++) {
			$u = (string) $faker->url();
			if (trim($u) !== '')
				$uniq[$u] = true;
		}

		return array_values(array_keys($uniq));
	}

	private function pickSigner(array $map): array
	{
		if (empty($map) || mt_rand(1, 100) <= 35)
			return [null, null];

		$id = array_rand($map);
		$name = $map[$id] ?? null;

		return [
			is_string($id) ? $id : null,
			is_string($name) ? $name : null
		];
	}

	private function pickUuidList(array $ids, int $max): array
	{
		if (empty($ids) || $max <= 0)
			return [];

		$n = mt_rand(0, $max);
		if ($n === 0)
			return [];

		shuffle($ids);
		return array_slice($ids, 0, $n);
	}

	private function fakeLegalReps($faker): array
	{
		$out = [];
		$n = mt_rand(0, 2);

		for ($i = 0; $i < $n; $i++) {
			$out[] = [
				'name' => $faker->name(),
				'position' => $faker->jobTitle(),
				'id' => (mt_rand(1, 100) <= 50) ? (string) Str::uuid() : null,
			];
		}

		return $out;
	}
}
