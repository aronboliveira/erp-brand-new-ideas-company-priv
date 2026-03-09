<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	EmailsConstants as EC,
	FormsConstants as FC,
	ProjectsConstants as PJC
};
use App\Enums\{AppModuleType, Visibility};
use App\Models\FormBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

class FormBuilderSeeder extends Seeder
{
	// private const HARD_CAP = 8000;
	private const HARD_CAP = 2;

	// private const SECONDS_LIMIT = 3 * 10 ** 2;
	private const SECONDS_LIMIT = 32;

	// Boolean columns to exhaustively cover (2^7 = 128 combos)
	private const BOOL_COLS = [
		AC::COL_IA,
		FC::COL_RQ_LOGIN,
		FC::COL_LMT_ONE_PRSN,
		FC::COL_ACPT_SBM,
		FC::COL_CSRF_CHK_REQ,
		FC::COL_ALW_EDT_AFT_SB,
		FC::COL_CST_RQ,
	];

	public function run(): void
	{
		DB::connection()->disableQueryLog();
		DB::connection()->flushQueryLog();
		$clock = microtime(true);
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$faker = \Faker\Factory::create('pt_BR');

		if (!class_exists(FormBuilder::class)) {
			Log::warning(static::class . ' FormBuilder model missing; skipping.');
			return;
		}

		$formTable = (new FormBuilder())->getTable();
		if (!Schema::hasTable($formTable)) {
			Log::warning(static::class . ' missing table; skipping.', [
				'table' => $formTable,
			]);
			return;
		}

		$userIds = $this->pluckIdsSafe(DC::TABLE_USERS);
		$emailTemplateIds = $this->pluckIdsSafe(DC::TABLE_EMAIL_TEMPLATES);

		$existing = (int) DB::table($formTable)->count();
		if ($existing >= self::HARD_CAP) return;

		$remaining = self::HARD_CAP - $existing;

		$combos = $this->booleanCombos(count(self::BOOL_COLS)); // 128
		$comboIdx = 0;

		$created = 0;

		foreach (AppModuleType::cases() as $module) {
			if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
				Log::warning(self::class . ' seeding time limit reached, stopping early');
				$out->writeln("<comment>[FormBuilderSeeder]</comment> Seeding time limit reached, stopping early.");
				return;
			}
			if ($created >= $remaining) break;

			$perModule = random_int(2, 16);
			for ($i = 0; $i < $perModule && $created < $remaining; $i++) {
				if ((microtime(true) - $clock) >= self::SECONDS_LIMIT) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					$out->writeln("<comment>[FormBuilderSeeder]</comment> Seeding time limit reached, stopping early.");
					return;
				}
				$combo = $combos[$comboIdx % count($combos)];
				$comboIdx++;
				try {
					$now = now();
					$bools = array_combine(self::BOOL_COLS, $combo);
					$isActive = (bool) $bools[AC::COL_IA];
					$acceptSubmissions = (bool) $bools[FC::COL_ACPT_SBM];

					// If we want submissions to be accepted, ensure deployment URL and "active"
					// (model will nullify COL_ACPT_SBM when inactive or not deployed).
					$dplUrl = null;
					$dplBy  = null;
					$dplAt  = null;

					if ($acceptSubmissions || ($isActive && $faker->boolean(65))) {
						$dplUrl = $this->fakeHttpsUrl($faker);
						$dplBy  = $this->pick($userIds);
						$dplAt  = $faker->boolean(70) ? $now->copy()->subDays(random_int(0, 120)) : null;
					}

					// Publishing: frequently mirror deployer when deployed.
					$pubBy = null;
					$pubAt = null;
					if ($dplUrl !== null && $faker->boolean(70)) {
						$pubBy = $dplBy;
						$pubAt = $dplAt ?? $now->copy()->subDays(random_int(0, 120));
					} elseif ($faker->boolean(15)) {
						$pubBy = $this->pick($userIds);
						$pubAt = $faker->boolean(60) ? $now->copy()->subDays(random_int(0, 365)) : null;
					}
					// Visibility: keep plausible states; model may adjust further.
					$visibility = $faker->boolean(25)
						? Visibility::Public->value
						: ($faker->boolean(50) ? Visibility::Private->value : Visibility::Unlisted->value);

					// Expiration: sometimes in the past to exercise rules.
					$expAt = null;
					if ($faker->boolean(20)) {
						$expAt = $faker->boolean(35)
							? $now->copy()->subDays(random_int(1, 120))
							: $now->copy()->addDays(random_int(1, 365));
					}

					// Roles allowlists
					$authDpls = $faker->boolean(60) ? $this->randomRoles($faker) : null;
					$authPubs = $faker->boolean(60) ? $this->randomRoles($faker) : null;

					// Consent docs
					$consentDoc = $bools[FC::COL_CST_RQ]
						? ($faker->boolean(70) ? $this->fakeHttpsUrl($faker) : ('attr://docs/' . Str::uuid()))
						: ($faker->boolean(10) ? $this->fakeHttpsUrl($faker) : null);

					$privacyDoc = $faker->boolean(65)
						? $this->fakeHttpsUrl($faker)
						: null;

					// Client form columns (trait will normalize; keep simple)
					$clientForm = [
						'form_name' => 'frm_' . Str::lower(Str::random(10)),
						'title' => $faker->sentence(3),
						'description' => $faker->boolean(45) ? $faker->sentence(12) : null,
						'action' => $faker->boolean(70) ? $this->fakeHttpsUrl($faker) : null,
						'method' => $faker->randomElement(['get', 'post', 'dialog', null]),
						'enctype' => $faker->randomElement([
							'application/x-www-form-urlencoded',
							'multipart/form-data',
							'text/plain',
							null,
						]),
						'target' => $faker->boolean(20) ? $faker->randomElement(['_self', '_blank', '_parent']) : null,
						'accept_charset' => $faker->boolean(10) ? 'utf-8' : null,
						'autocomplete' => $faker->randomElement(['on', 'off', null]),
						'novalidate' => $faker->boolean(15) ? true : null,
						'referrerpolicy' => $faker->boolean(10) ? $faker->randomElement(['no-referrer', 'strict-origin', 'same-origin']) : null,
						'allowed_methods' => $faker->boolean(25) ? $faker->randomElements(['get', 'post', 'dialog'], random_int(1, 3)) : null,
						'allowed_enctypes' => $faker->boolean(25) ? $faker->randomElements([
							'application/x-www-form-urlencoded',
							'multipart/form-data',
							'text/plain',
						], random_int(1, 3)) : null,
						'submit_label' => $faker->boolean(60) ? $faker->randomElement(['Enviar', 'Salvar', 'Confirmar']) : null,
						'reset_label' => $faker->boolean(35) ? $faker->randomElement(['Limpar', 'Resetar']) : null,
						'show_reset' => $faker->boolean(35) ? $faker->boolean() : null,
						'prevent_double_submit' => $faker->boolean(55) ? $faker->boolean() : null,
						'submit_debounce_ms' => $faker->boolean(35) ? random_int(0, 3000) : null,
					];
					// HTML-linked metadata
					$htmlLinked = [
						'aria' => $faker->boolean(30) ? [
							'aria-label' => $faker->sentence(2),
							'aria-live' => $faker->randomElement(['polite', 'assertive']),
						] : null,
						'dataset' => $faker->boolean(30) ? [
							'data-form' => 'builder',
							'data-module' => $module->value,
						] : null,
						'selectors' => $faker->boolean(25) ? array_values(array_unique([
							'#frm_' . Str::lower(Str::random(6)),
							'.form_' . Str::lower(Str::random(6)),
						])) : null,
						'size' => $faker->boolean(20) ? [
							'width' => $faker->randomElement(['100%', '480px', '720px']),
							'height' => $faker->randomElement(['auto', '640px', '80vh']), // trait will drop invalid; keep mostly valid
						] : null,
						'tags' => $faker->boolean(40) ? $faker->randomElements(['lead', 'deal', 'support', 'public', 'internal'], random_int(1, 3)) : null,
					];
					// Link IDs (nullable)
					$linkIds = [
						PJC::COL_LD_ID => null,
						PJC::COL_DL_ID => null,
						PJC::COL_SUP_ID => null,
						PJC::COL_PJ_ID => null,
						PJC::COL_CTC_ID => null,
					];
					// Cache flags for linked entities; keep consistent-ish with presence of ids.
					$isLd = $faker->boolean(20);
					$isDl = $faker->boolean(20);
					$isSup = $faker->boolean(20);
					$isPj = $faker->boolean(20);
					$isCtc = $faker->boolean(20);

					// Receiver / templates
					$receiver = $faker->boolean(60) ? $faker->safeEmail() : null;

					$rcvTmp = $faker->boolean(30) ? $this->pick($emailTemplateIds) : null;
					$sbmTmp = $faker->boolean(30) ? $this->pick($emailTemplateIds) : null;
					$fldTmp = $faker->boolean(30) ? $this->pick($emailTemplateIds) : null;

					// Basic JSON areas
					$variables = $faker->boolean(25) ? [
						'app_module' => $module->value,
						'seed' => Str::random(8),
					] : null;
					$scripts = $faker->boolean(25) ? [$this->fakeHttpsUrl($faker)] : null;
					$styles  = $faker->boolean(25) ? [$this->fakeHttpsUrl($faker)] : null;

					$webhooks = $faker->boolean(15) ? [
						['url' => $this->fakeHttpsUrl($faker), 'event' => 'submission.created'],
					] : null;
					$notifications = $faker->boolean(15) ? ['email' => true] : null;

					$cookies = $faker->boolean(10) ? ['remember' => true] : null;
					$exports = $faker->boolean(15) ? ['csv' => true] : null;

					$otherUrls = $faker->boolean(15) ? [$this->fakeHttpsUrl($faker)] : null;

					$blockedIps = $faker->boolean(10) ? [
						'192.168.0.0/24',
						'10.0.0.0/8',
					] : null;
					$settings = $faker->boolean(20) ? ['theme' => $faker->randomElement(['light', 'dark'])] : null;

					$form = new FormBuilder();
					$name = $this->safeName($faker);
					$out->writeln("<info>[FormBuilderSeeder]</info> Form name: " . $name);
					// ? overhead
					$formId = (string) Str::uuid();
					$form->setAttribute('id', $formId);
					$form->fill([
						'module' => $module->value,
						'name' => $name,
						'visibility' => $visibility,

						FC::COL_CAPTCHA_PRV => $faker->boolean(25) ? $faker->randomElement(['recaptcha', 'hcaptcha']) : null,
						FC::COL_RT_LMT => $faker->boolean(35) ? random_int(0, 120) : 0,
						FC::COL_RTT_DAYS => $faker->boolean(60) ? random_int(0, 1460) : 730,
						FC::COL_EXP_AT => $expAt,

						AC::COL_IA => $isActive,
						FC::COL_RQ_LOGIN => (bool) $bools[FC::COL_RQ_LOGIN],
						FC::COL_LMT_ONE_PRSN => (bool) $bools[FC::COL_LMT_ONE_PRSN],
						FC::COL_ACPT_SBM => (bool) $bools[FC::COL_ACPT_SBM],
						FC::COL_CSRF_CHK_REQ => (bool) $bools[FC::COL_CSRF_CHK_REQ],
						FC::COL_ALW_EDT_AFT_SB => (bool) $bools[FC::COL_ALW_EDT_AFT_SB],
						FC::COL_CST_RQ => (bool) $bools[FC::COL_CST_RQ],
						FC::COL_CST_DOC => $consentDoc,
						FC::COL_PRV_PL_DOC => $privacyDoc,

						AC::COL_IS_LD_ACT => $isLd,
						AC::COL_IS_DL_ACT => $isDl,
						AC::COL_IS_SUP_ACT => $isSup,
						AC::COL_IS_PRJ_ACT => $isPj,
						AC::COL_IS_CTC_ACT => $isCtc,

						...$linkIds,

						'receiver' => $receiver,
						EC::COL_RCV_TMP => $rcvTmp,
						EC::COL_SBM_TMP => $sbmTmp,
						EC::COL_FLD_TMP => $fldTmp,

						FC::COL_RDR_URL => $faker->boolean(25) ? $this->fakeHttpsUrl($faker) : null,
						'generator' => $faker->boolean(50) ? $faker->randomElement(['builder', 'import', 'plugin']) : null,
						FC::COL_SBM_CNT => $faker->boolean(40) ? random_int(0, 500) : 0,

						FC::COL_DPL_URL => $dplUrl,
						FC::COL_DPL_BY => $dplBy,
						FC::COL_AUTH_DPLS => $authDpls,

						FC::COL_PUB_BY => $pubBy,
						FC::COL_PUB_AT => $pubAt,
						FC::COL_AUTH_PUBS => $authPubs,

						FC::COL_DPL_AT => $dplAt,
						FC::COL_SBM_URL => $faker->boolean(20) ? $this->fakeHttpsUrl($faker) : null,

						...$clientForm,
						...$htmlLinked,

						'variables' => $variables,
						'scripts' => $scripts,
						'styles' => $styles,
						'webhooks' => $webhooks,
						'notifications' => $notifications,
						'cookies' => $cookies,
						'exports' => $exports,
						FC::COL_OTHER_URLS => $otherUrls,
						FC::COL_BLK_IP_RG => $blockedIps,
						'settings' => $settings,
					]);
					$out->writeln("<info>[FormBuilderSeeder]</info> Filled FormBuilder attributes for {$name}.");
					// Ensure 2..N fields. Prefer actual FormField rows if possible.
					$fieldCount = random_int(2, 8);
					$out->writeln("<info>[FormBuilderSeeder]</info> Created FormBuilder name {$name} with {$fieldCount} fields for module {$module->value}.");
					$form->setAttribute('fields', []);
					$form->saveQuietly();

					$fieldCount = random_int(2, 8);
					$fieldIds = $this->createFieldsForForm($formId, $fieldCount, $faker);

					$form->forceFill(['fields' => $fieldIds])->saveQuietly();
					$created++;
				} catch (\Throwable $e) {
					$out->writeln("<error>[FormBuilderSeeder]</error> Failed to create FormBuilder: " . $e->getMessage());
					Log::warning(static::class . ' failed to create FormBuilder', [
						'err'  => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
			}
		}
	}

	private function booleanCombos(int $n): array
	{
		$out = [];
		$total = 1 << $n;
		for ($mask = 0; $mask < $total; $mask++) {
			$row = [];
			for ($i = 0; $i < $n; $i++)
				$row[] = (bool) (($mask >> $i) & 1);
			$out[] = $row;
		}
		return $out;
	}

	private function pluckIdsSafe(string $table): array
	{
		try {
			if (!Schema::hasTable($table)) return [];
			return DB::table($table)->pluck('id')->filter()->values()->all();
		} catch (\Throwable $e) {
			Log::debug(static::class . ' failed to pluck ids', [
				'table' => $table,
				'err'   => $e->getMessage(),
				'file'  => $e->getFile(),
				'line'  => $e->getLine(),
			]);
			return [];
		}
	}

	private function pick(array $ids): ?string
	{
		if (!$ids) return null;
		return $ids[array_rand($ids)];
	}

	private function fakeHttpsUrl($faker): string
	{
		$host = $faker->domainName();
		$path = '/' . Str::lower(Str::random(8));
		return 'https://' . $host . $path;
	}

	private function safeName($faker): string
	{
		$s = trim((string) $faker->words(random_int(2, 4), true));
		return $s !== '' ? Str::title($s) : ('Form ' . Str::upper(Str::random(6)));
	}

	private function randomRoles($faker): array
	{
		$pool = ['admin', 'manager', 'agent', 'sales', 'support', 'dev', 'owner'];
		$k = random_int(1, min(4, count($pool)));
		$out = $faker->randomElements($pool, $k);
		$out = array_values(array_unique(array_map(fn($v) => strtolower(trim((string) $v)), $out)));
		return $out ?: ['admin'];
	}

	/**
	 * Returns a string[] of field identifiers (ids/codes).
	 * If FormField table/model is unavailable, generates UUID tokens as fallback.
	 */

	private function createFieldsForForm(string $formId, int $count, $faker): array
	{
		static $colsCache = [];

		$formFieldClass = '\\App\\Models\\FormField';
		if (!class_exists($formFieldClass)) {
			$out = [];
			for ($i = 0; $i < $count; $i++) $out[] = 'fld_' . Str::lower(Str::uuid()->toString());
			return $out;
		}

		$fieldTable = (string) (new $formFieldClass())->getTable();
		if ($fieldTable === '' || !Schema::hasTable($fieldTable)) {
			$out = [];
			for ($i = 0; $i < $count; $i++) $out[] = 'fld_' . Str::lower(Str::uuid()->toString());
			return $out;
		}

		$colsCache[$fieldTable] ??= array_flip(Schema::getColumnListing($fieldTable));
		$cols = $colsCache[$fieldTable];

		$now = now();

		$rows = [];
		$ids  = [];

		for ($i = 0; $i < $count; $i++) {
			$id = (string) Str::uuid();

			$row = [];
			isset($cols['id']) && $row['id'] = $id;
			isset($cols[FC::COL_FM_ID]) && $row[FC::COL_FM_ID] = $formId;

			isset($cols['name']) && $row['name'] = $faker->words(random_int(1, 3), true);
			isset($cols['type']) && $row['type'] = $faker->randomElement(['text', 'email', 'phone', 'textarea', 'select', 'checkbox', 'radio', 'date']);
			isset($cols['required']) && $row['required'] = $faker->boolean(35);

			isset($cols[DC::COL_C_AT]) && $row[DC::COL_C_AT] = $now;
			isset($cols['created_at']) && $row['created_at'] = $now;
			isset($cols[DC::COL_U_AT]) && $row[DC::COL_U_AT] = $now;
			isset($cols['updated_at']) && $row['updated_at'] = $now;

			$rows[] = $row;
			$ids[] = $id;
		}

		Schema::withoutForeignKeyConstraints(function () use ($fieldTable, $rows): void {
			foreach (array_chunk($rows, 500) as $chunk)
				$this->insertWithRetry(DB::table($fieldTable), $chunk);
		});

		return $ids;
	}

	/**
	 * Retry transient MySQL errors: 1205 (lock wait timeout), 1213 (deadlock).
	 */
	private function insertWithRetry(mixed $qb, array $rows, int $maxAttempts = 6): void
	{
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		for ($i = 0; $i < $maxAttempts; $i++) {
			try {
				$out->writeln("<info>[FormBuilderSeeder]</info> Inserting batch of " . count($rows) . " rows (attempt " . ($i + 1) . ")...");
				$qb->insert($rows);
				return;
			} catch (QueryException $e) {
				$errno = (int) ($e->errorInfo[1] ?? 0);

				if (!in_array($errno, [1205, 1213], true) || $i === $maxAttempts - 1)
					throw $e;
				$out->writeln("<comment>[FormBuilderSeeder]</comment> Transient error (errno {$errno}) on insert, retrying...");
				usleep(150_000 * (1 + $i)); // backoff simples
			}
		}
	}
}
