<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, SettingsConstants as SC};
use App\Enums\{EvaluationStatus, Frequency};
use App\Models\Contract;
use App\Traits\EnsuresSystemUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class ContractSeeder extends Seeder
{
	use EnsuresSystemUser;

	private const TZ = 'America/Sao_Paulo';

	private const HARD_CAP = 3200;

	/**
	 * Final output must be a power of 2; under HARD_CAP, the maximum power-of-2 is 2048.
	 */
	private const MAX_POWER2 = 2048;

	private const MAX_PER_TYPE = 64;

	/**
	 * Small tolerance to nulls for nullable fields (not for required dates).
	 */
	private const NULL_P = 0.12;

	private const UNIQUE_ATTEMPTS = 40;

	private const SECONDS_LIMIT = 6 * 10 ** 2;

	public function run(): void
	{
		$out = new ConsoleOutput();

		DB::transaction(function () use ($out): void {
			$clock = microtime(true);
			$systemUserId = $this->ensureSystemUser();

			$tz = self::TZ;
			$now = CarbonImmutable::now($tz);

			$typeIds = DB::table(DC::TABLE_CONTRACT_TYPES)->pluck('id')->all();
			$typeIds = array_values(array_filter($typeIds, fn($v) => is_string($v) && trim($v) !== ''));

			if (!$typeIds) {
				$defaults = [
					'Prestação de Serviço',
					'Outsourcing',
					'Contrato de Fornecimento',
					'Acordo de Parceria',
					'Suporte e Manutenção',
					'Consultoria',
					'Licenciamento',
					'Confidencialidade (NDA)',
				];

				$payload = [];
				foreach ($defaults as $name) {
					$id = (string) Str::uuid();
					$out->writeln("<comment>[ContractSeeder] creating ContractType: {$name}</comment>");
					$payload[] = [
						'id' => $id,
						'name' => $name,
						DC::COL_TABLE_CREATOR => $systemUserId,
						DC::COL_TABLE_UPDATER => null,
						'created_at' => $now,
						'updated_at' => $now,
					];
				}

				DB::table(DC::TABLE_CONTRACT_TYPES)->insert($payload);

				$typeIds = DB::table(DC::TABLE_CONTRACT_TYPES)->pluck('id')->all();
				$typeIds = array_values(array_filter($typeIds, fn($v) => is_string($v) && trim($v) !== ''));
			}

			$typeCount = count($typeIds);

			$userIds = DB::table(DC::TABLE_USERS)->limit(2000)->pluck('id')->all();
			$userIds = array_values(array_filter($userIds, fn($v) => is_string($v) && trim($v) !== ''));

			$planScheduleIds = DB::table(DC::TABLE_PLN_SCHD)->limit(2000)->pluck('id')->all();
			$planScheduleIds = array_values(array_filter($planScheduleIds, fn($v) => is_string($v) && trim($v) !== ''));

			$projectIds = DB::table(DC::TABLE_PROJECTS)->limit(2000)->pluck('id')->all();
			$projectIds = array_values(array_filter($projectIds, fn($v) => is_string($v) && trim($v) !== ''));

			$statuses = array_column(EvaluationStatus::cases(), 'value');
			$freqs = array_column(Frequency::cases(), 'value');

			$minRequired = max(
				64,
				$typeCount,
				2 * count($statuses),
				2 * count($freqs)
			);

			$cap = min(self::HARD_CAP, self::MAX_POWER2);
			$target = self::nextPowerOfTwo($minRequired);
			if ($target > $cap) $target = $cap;

			// ---- Per-type iterations: 1..64 each, sum must equal $target
			$iterationsByType = [];
			foreach ($typeIds as $typeId) $iterationsByType[(string) $typeId] = 1;

			$remaining = $target - $typeCount;
			if ($remaining < 0) $remaining = 0;

			$typeIdx = 0;
			$guard = 0;
			while ($remaining > 0 && $guard++ < ($target * 3)) {
				$typeId = (string) $typeIds[$typeIdx % $typeCount];
				$typeIdx++;

				$cur = (int) ($iterationsByType[$typeId] ?? 1);
				if ($cur >= self::MAX_PER_TYPE) continue;

				$iterationsByType[$typeId] = $cur + 1;
				$remaining--;
			}

			$totalPlanned = array_sum($iterationsByType);
			if ($totalPlanned !== $target) {
				// Best-effort correction (should be rare): trim excess (never below 1 per type).
				$delta = $totalPlanned - $target;
				if ($delta > 0) {
					$guard = 0;
					while ($delta > 0 && $guard++ < ($target * 3)) {
						$typeId = (string) $typeIds[$guard % $typeCount];
						$cur = (int) ($iterationsByType[$typeId] ?? 1);
						if ($cur <= 1) continue;
						$iterationsByType[$typeId] = $cur - 1;
						$delta--;
					}
				}
			}

			$totalPlanned = array_sum($iterationsByType);
			$out->writeln("<info>[ContractSeeder] types={$typeCount} target(power2)={$target} planned={$totalPlanned}</info>");

			// ---- Build the "type plan" list
			$typePlan = [];
			foreach ($iterationsByType as $typeId => $qty) {
				for ($i = 0; $i < $qty; $i++) $typePlan[] = (string) $typeId;
			}

			try {
				shuffle($typePlan);
			} catch (\Throwable) {
				// no-op
			}

			// ---- Ensure >=2 rows per status and per frequency
			$statusCount = array_fill_keys($statuses, 0);
			$freqCount = array_fill_keys($freqs, 0);

			// ---- Locales (mostly pt_BR, then es_MX, then en_US)
			$fakers = [
				'pt_BR' => \Faker\Factory::create('pt_BR'),
				'es_MX' => \Faker\Factory::create('es_MX'),
				'en_US' => \Faker\Factory::create('en_US'),
			];

			$created = 0;

			foreach ($typePlan as $i => $typeId) {

				if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
					Log::warning(self::class . ' seeding time limit reached, stopping early');
					return;
				}
				$locale = self::pickLocale($i);
				$faker = $fakers[$locale] ?? $fakers['pt_BR'];

				$status = self::pickNeedOrRandom($statuses, $statusCount, 2, $faker);
				$frequency = self::pickNeedOrRandom($freqs, $freqCount, 2, $faker);

				// Coherent-ish dates
				$start = $now->subDays(self::ri(0, 365))->addDays(self::ri(0, 120));
				$end = self::makeEndDate($start, $frequency, $tz);

				$renewable = (bool) self::rb(0.55);
				$autoRenew = $renewable ? (bool) self::rb(0.55) : false;

				$currency = match ($locale) {
					'en_US' => 'USD',
					'es_MX' => 'MXN',
					default => (string) (SC::DEF_SITE_CURRENCY_ID ?? 'BRL'),
				};
				$currency = strtoupper(substr(trim($currency), 0, 3));
				if ($currency === '') $currency = 'BRL';

				$title = self::makeTitle($faker, $locale, $typeId);
				$subject = self::maybeNull(self::NULL_P, self::makeSubject($faker, $locale));
				$notes = self::maybeNull(self::NULL_P, $faker->optional(0.65)->sentence(18));

				$clientName = self::maybeNull(self::NULL_P, $faker->company);
				$obgName = self::maybeNull(self::NULL_P, ($clientName ?: $faker->company));
				$oblName = self::maybeNull(self::NULL_P, $faker->company);

				$payload = [
					'id' => (string) Str::uuid(),

					// Uniques: we will set them after save if not fillable
					'code' => self::maybeNull(self::NULL_P, null),
					PJC::COL_CN => self::maybeNull(self::NULL_P, null),

					'type' => $typeId,
					'title' => $title,
					'subject' => $subject,
					'value' => self::maybeNull(self::NULL_P, self::pickValueString($faker, $locale)),
					'currency' => self::maybeNull(self::NULL_P, $currency),
					'description' => self::maybeNull(self::NULL_P, $faker->optional(0.85)->paragraphs(self::ri(2, 5), true)),
					'notes' => $notes,

					PJC::COL_S_DT => $start->format('Y-m-d'),
					PJC::COL_E_DT => $end->format('Y-m-d'),
					PJC::COL_CDESC => self::maybeNull(self::NULL_P, $faker->optional(0.8)->paragraphs(self::ri(2, 4), true)),

					'status' => self::maybeNull(self::NULL_P, $status),
					'renewable' => self::maybeNull(self::NULL_P, $renewable),
					PJC::COL_ARNW => self::maybeNull(self::NULL_P, $autoRenew),
					'frequency' => self::maybeNull(self::NULL_P, $frequency),

					'company' => self::maybeNull(self::NULL_P, $userIds ? (string) $faker->randomElement($userIds) : null),
					PJC::COL_CLIENT_ID => self::maybeNull(self::NULL_P, $userIds ? (string) $faker->randomElement($userIds) : null),

					PJC::COL_CLIENT_NAME => $clientName,
					PJC::COL_OBG_NAME => $obgName,
					PJC::COL_OBL_NAME => $oblName,

					PJC::COL_OBG_IDF => self::maybeNull(self::NULL_P, self::fakeIdf($faker, $locale)),
					PJC::COL_OBL_IDF => self::maybeNull(self::NULL_P, self::fakeIdf($faker, $locale)),

					PJC::COL_OBG_ADDR => self::maybeNull(self::NULL_P, $faker->address),
					PJC::COL_OBL_ADDR => self::maybeNull(self::NULL_P, $faker->address),

					PJC::COL_OBG_CTC => self::maybeNull(self::NULL_P, $faker->phoneNumber . ' / ' . $faker->companyEmail),
					PJC::COL_OBL_CTC => self::maybeNull(self::NULL_P, $faker->phoneNumber . ' / ' . $faker->companyEmail),

					PJC::COL_CL_SIG => self::maybeNull(0.85, null),
					PJC::COL_CO_SIG => self::maybeNull(0.85, null),

					PJC::COL_CL_SIGN_AT => self::maybeNull(self::NULL_P, self::signatureDateForStatus($status, $start, $faker)),
					PJC::COL_CO_SIGN_AT => self::maybeNull(self::NULL_P, self::signatureDateForStatus($status, $start->addDays(1), $faker)),

					PJC::COL_APV_BY => self::maybeNull(self::NULL_P, $userIds ? (string) $faker->randomElement($userIds) : null),
					PJC::COL_APV_AT => self::maybeNull(self::NULL_P, self::approvalDateForStatus($status, $start, $faker)),

					PJC::COL_REJ_BY => self::maybeNull(self::NULL_P, $userIds ? (string) $faker->randomElement($userIds) : null),
					PJC::COL_REJ_AT => self::maybeNull(self::NULL_P, self::rejectionDateForStatus($status, $start, $faker)),

					PJC::COL_WT_NM => self::maybeNull(self::NULL_P, $faker->name),
					PJC::COL_WT2_NM => self::maybeNull(self::NULL_P, $faker->name),
					PJC::COL_WT_IDF => self::maybeNull(self::NULL_P, self::fakeIdf($faker, $locale)),
					PJC::COL_WT2_IDF => self::maybeNull(self::NULL_P, self::fakeIdf($faker, $locale)),
					PJC::COL_WT_SIG => self::maybeNull(0.9, null),
					PJC::COL_WT2_SIG => self::maybeNull(0.9, null),
					PJC::COL_WT_SIGN_AT => self::maybeNull(self::NULL_P, $start->format('Y-m-d')),
					PJC::COL_WT2_SIGN_AT => self::maybeNull(self::NULL_P, $start->addDays(1)->format('Y-m-d')),

					PJC::COL_PJ_ID => self::maybeNull(self::NULL_P, $projectIds ? (string) $faker->randomElement($projectIds) : null),
					PJC::COL_PLN_SCHD_ID => self::maybeNull(self::NULL_P, $planScheduleIds ? (string) $faker->randomElement($planScheduleIds) : null),

					PJC::COL_F_PATH => self::maybeNull(0.95, null),
					PJC::COL_ATC_PATHS => self::maybeNull(0.9, null),
					'metadata' => self::jsonOrNull([
						'seed' => 'ContractSeeder',
						'lang' => $locale,
						'status' => $status,
						'frequency' => $frequency,
					]),

					DC::COL_TABLE_CREATOR => $systemUserId,
					DC::COL_TABLE_UPDATER => null,
					'created_at' => $now,
					'updated_at' => $now,
				];

				$out->writeln(
					"<comment>[ContractSeeder] creating: lang={$locale} type={$typeId} status={$status} freq={$frequency} title={$title}</comment>"
				);

				try {
					$m = new Contract();
					$m->forceFill($payload);
					$m->save();

					// Ensure unique code / CN if missing (or if model nullified)
					$needsSave = false;

					$code = trim((string) ($m->getAttribute('code') ?? ''));
					if ($code === '') {
						$m->setAttribute('code', self::makeUniqueCode());
						$needsSave = true;
					}

					$cn = trim((string) ($m->getAttribute(PJC::COL_CN) ?? ''));
					if ($cn === '') {
						$m->setAttribute(PJC::COL_CN, self::makeUniqueCN());
						$needsSave = true;
					}

					if ($needsSave) $m->save();

					$created++;
				} catch (\Throwable $e) {
					Log::warning('[ContractSeeder] failed creating contract: ' . $e->getMessage(), [
						'line' => $e->getLine(),
						'file' => $e->getFile(),
					]);
					continue;
				}
			}

			$out->writeln("<info>[ContractSeeder] created={$created} (target={$target})</info>");
		}, 3);
	}

	private static function nextPowerOfTwo(int $n): int
	{
		$n = max(1, (int) $n);
		$p = 1;
		while ($p < $n) $p <<= 1;
		return $p;
	}

	private static function pickLocale(int $i): string
	{
		// Weighted alternation (mostly pt_BR, then es_MX, then en_US)
		$cycle = ['pt_BR', 'pt_BR', 'pt_BR', 'pt_BR', 'es_MX', 'pt_BR', 'en_US'];
		return $cycle[$i % count($cycle)];
	}

	private static function pickNeedOrRandom(array $options, array &$counts, int $minEach, \Faker\Generator $faker): string
	{
		$need = [];
		foreach ($options as $v) {
			$k = (string) $v;
			$c = (int) ($counts[$k] ?? 0);
			if ($c < $minEach) $need[] = $k;
		}

		$pick = $need ? (string) $faker->randomElement($need) : (string) $faker->randomElement($options);
		$counts[$pick] = ((int) ($counts[$pick] ?? 0)) + 1;
		return $pick;
	}

	private static function makeUniqueCode(): string
	{
		$tries = 0;

		do {
			$tries++;
			$code = 'CTR-' . strtoupper((string) Str::uuid());
			$exists = DB::table(DC::TABLE_CONTRACTS)->where('code', $code)->exists();
		} while ($exists && $tries < self::UNIQUE_ATTEMPTS);

		return $code;
	}

	private static function makeUniqueCN(): string
	{
		$tries = 0;

		do {
			$tries++;
			$cn = 'CTR-' . strtoupper(Str::random(10));
			$exists = DB::table(DC::TABLE_CONTRACTS)->where(PJC::COL_CN, $cn)->exists();
		} while ($exists && $tries < self::UNIQUE_ATTEMPTS);

		return $cn;
	}

	private static function makeEndDate(CarbonImmutable $start, ?string $frequency, string $tz): CarbonImmutable
	{
		$frequency = strtolower(trim((string) $frequency));

		$months = match ($frequency) {
			'annual' => self::ri(12, 24),
			'semestral' => self::ri(6, 12),
			'quaternaly' => self::ri(3, 6),
			'monthly' => self::ri(1, 6),
			'weekly', 'biweekly', 'semimonthly' => self::ri(0, 3),
			'once' => self::ri(0, 2),
			'hourly', 'variable', '' => self::ri(0, 3),
			default => self::ri(1, 12),
		};

		$end = $start->addMonthsNoOverflow(max(0, $months))->timezone($tz);
		if ($end->lessThan($start)) $end = $start;
		return $end;
	}

	private static function makeTitle(\Faker\Generator $faker, string $locale, string $typeId): string
	{
		return match ($locale) {
			'es_MX' => 'Contrato — ' . $faker->sentence(4),
			'en_US' => 'Contract — ' . $faker->sentence(4),
			default => 'Contrato — ' . $faker->sentence(4),
		};
	}

	private static function makeSubject(\Faker\Generator $faker, string $locale): string
	{
		return match ($locale) {
			'es_MX' => $faker->sentence(8),
			'en_US' => $faker->sentence(8),
			default => $faker->sentence(8),
		};
	}

	private static function pickValueString(\Faker\Generator $faker, string $locale): string
	{
		$min = 500.0;
		$max = 350000.0;
		$v = $min + (self::ri(0, 1000000) / 1000000) * ($max - $min);
		$v = round($v, 2);
		return number_format($v, 2, '.', '');
	}

	private static function signatureDateForStatus(string $status, CarbonImmutable $date, \Faker\Generator $faker): ?string
	{
		$status = strtolower(trim($status));
		if (in_array($status, ['active', 'completed', 'accept'], true)) return $date->format('Y-m-d');
		if (self::rb(self::NULL_P)) return null;
		return null;
	}

	private static function approvalDateForStatus(string $status, CarbonImmutable $date, \Faker\Generator $faker): ?string
	{
		$status = strtolower(trim($status));
		if (in_array($status, ['active', 'completed', 'accept'], true)) return $date->addDays(self::ri(0, 7))->format('Y-m-d');
		return self::rb(self::NULL_P) ? null : null;
	}

	private static function rejectionDateForStatus(string $status, CarbonImmutable $date, \Faker\Generator $faker): ?string
	{
		$status = strtolower(trim($status));
		if (in_array($status, ['decline', 'cancelled'], true)) return $date->addDays(self::ri(0, 7))->format('Y-m-d');
		return self::rb(self::NULL_P) ? null : null;
	}

	private static function fakeIdf(\Faker\Generator $faker, string $locale): string
	{
		// Best-effort placeholder identifiers; migration allows nullable anyway.
		if ($locale === 'en_US') return 'SSN-' . self::ri(100, 999) . '-' . self::ri(10, 99) . '-' . self::ri(1000, 9999);
		if ($locale === 'es_MX') return 'RFC-' . strtoupper(Str::random(10));
		return 'DOC-' . strtoupper(Str::random(11));
	}

	private static function jsonOrNull(mixed $v): ?string
	{
		if ($v === null) return null;

		try {
			return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		} catch (\Throwable) {
			return null;
		}
	}

	private static function maybeNull(float $p, mixed $value): mixed
	{
		return self::rb($p) ? null : $value;
	}

	private static function rb(float $p): bool
	{
		$p = max(0.0, min(1.0, $p));
		try {
			return (random_int(0, 1000000) / 1000000) < $p;
		} catch (\Throwable) {
			return (mt_rand(0, 1000000) / 1000000) < $p;
		}
	}

	private static function ri(int $min, int $max): int
	{
		if ($max < $min) [$min, $max] = [$max, $min];
		try {
			return random_int($min, $max);
		} catch (\Throwable) {
			return mt_rand($min, $max);
		}
	}
}
