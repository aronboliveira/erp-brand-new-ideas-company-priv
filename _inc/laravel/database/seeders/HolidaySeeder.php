<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use App\Enums\EventObservance;
use App\Enums\HolidayType;
use App\Models\Holiday;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

final class HolidaySeeder extends Seeder
{
	private const MAX_UNIQUE_ATTEMPTS = 25;
	private const MAX_LOOP_GUARD = 800;
	private const SECONDS_LIMIT = 3 * 10 ** 2;

	/**
	 * ISO-3166-1 alpha-2 => Faker locale
	 * (fallback para en_US se o locale não existir no runtime)
	 */
	private const LOCALE_BY_COUNTRY = [
		'BR' => 'pt_BR',
		'PT' => 'pt_PT',
		'US' => 'en_US',
		'CN' => 'zh_CN',

		'AR' => 'es_AR',
		'BO' => 'es_BO',
		'CL' => 'es_CL',
		'EC' => 'es_EC',
		'PY' => 'es_PY',
		'PE' => 'es_PE',
		'UY' => 'es_UY',
		'CO' => 'es_CO',
		'GY' => 'en_GB',
	];

	/**
	 * 16 variações com shape consistente:
	 * array<array{day:string, reduce_hours:int}>
	 */
	private const SHIFT_VARIANTS = [
		[['day' => 'morning', 'reduce_hours' => 2]],
		[['day' => 'afternoon', 'reduce_hours' => 3]],
		[['day' => 'night', 'reduce_hours' => 2]],
		[['day' => 'all', 'reduce_hours' => 1]],
		[['day' => 'morning', 'reduce_hours' => 1], ['day' => 'afternoon', 'reduce_hours' => 2]],
		[['day' => 'morning', 'reduce_hours' => 2], ['day' => 'afternoon', 'reduce_hours' => 2]],
		[['day' => 'morning', 'reduce_hours' => 3], ['day' => 'afternoon', 'reduce_hours' => 3]],
		[['day' => 'morning', 'reduce_hours' => 2], ['day' => 'night', 'reduce_hours' => 2]],
		[['day' => 'all', 'reduce_hours' => 2]],
		[['day' => 'all', 'reduce_hours' => 3]],
		[['day' => 'all', 'reduce_hours' => 4]],
		[['day' => 'morning', 'reduce_hours' => 1], ['day' => 'afternoon', 'reduce_hours' => 1], ['day' => 'night', 'reduce_hours' => 1]],
		[['day' => 'morning', 'reduce_hours' => 2], ['day' => 'afternoon', 'reduce_hours' => 1]],
		[['day' => 'afternoon', 'reduce_hours' => 2], ['day' => 'night', 'reduce_hours' => 1]],
		[['day' => 'morning', 'reduce_hours' => 1], ['day' => 'night', 'reduce_hours' => 2]],
		[], // variação “sem redução”
	];

	/** @var array<string, Faker> */
	private array $fakerCache = [];

	public function run(): void
	{
		$clock = microtime(true);
		$out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$out->writeln('<info>[HolidaySeeder]</info> start');

		if (!Schema::hasTable(DC::TABLE_HLD)) {
			$out->writeln('<comment>[HolidaySeeder]</comment> missing table: ' . DC::TABLE_HLD);
			return;
		}

		// Países com enum de regiões no trait (fonte “funcional” para sua regra)
		$countries = $this->countriesWithRegionEnums();

		// Garante BR presente
		if (!in_array('BR', $countries, true)) {
			$countries[] = 'BR';
		}

		// Pools de IDs (apenas se as tabelas existirem)
		$linkPool = $this->loadLinkPool();

		// Alvo: 64 * N (N = companies) ou --count; sempre arredonda p/ múltiplo de 64
		$minPerCountry = 2 * count($countries);
		$rawTarget = $this->inferTargetCount($minPerCountry);
		$target = $this->roundUp64(max($rawTarget, $minPerCountry));

		$out->writeln(sprintf(
			'<info>[HolidaySeeder]</info> target=%d (minPerCountry=%d rawTarget=%d)',
			$target,
			$minPerCountry,
			$rawTarget
		));

		$types = array_map(static fn(HolidayType $e) => $e->value, HolidayType::cases());
		$observances = array_map(static fn(EventObservance $e) => $e->value, EventObservance::cases());

		$created = 0;
		$guard = 0;

		// Garantias globais: pelo menos 1 vínculo de cada tipo quando houver pool
		$mustLink = [
			'event' => !empty($linkPool['event']),
			'award' => !empty($linkPool['award']),
			'coupon' => !empty($linkPool['coupon']),
			'project' => !empty($linkPool['project']),
			'task' => !empty($linkPool['task']),
			'meeting' => !empty($linkPool['meeting']),
			'goal' => !empty($linkPool['goal']),
		];

		// 1) Pelo menos 2 por país (com locale correto no name)
		foreach ($countries as $cc) {
			for ($i = 0; $i < 2 && $created < $target; $i++) {
				$faker = $this->fakerForCountry($cc);
				$data = $this->makeHolidayData(
					cc: $cc,
					faker: $faker,
					type: $types[array_rand($types)],
					observance: $observances[array_rand($observances)],
					shiftBy: self::SHIFT_VARIANTS[$i % 16],
					countries: [$cc],
					linkPool: $linkPool,
					mustLink: $mustLink
				);
				$this->persistHoliday($data);
				$created++;
				if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
					$out->writeln('<error>[HolidaySeeder]</error> time limit reached, stopping early');
					return;
				}
			}
		}

		// 2) Looping principal (type x observance x 16 shift variants), com BR majoritário
		while ($created < $target) {
			$guard++;
			if ((microtime(true) - $clock) > self::SECONDS_LIMIT) {
				$out->writeln('<error>[HolidaySeeder]</error> time limit reached, stopping early');
				return;
			}
			if ($guard > self::MAX_LOOP_GUARD) {
				$out->writeln('<error>[HolidaySeeder]</error> guard stop (MAX_LOOP_GUARD)');
				return;
			}

			$cc = $this->pickCountryWeighted($countries);
			$faker = $this->fakerForCountry($cc);

			$type = $types[array_rand($types)];
			$observance = $observances[array_rand($observances)];

			foreach (self::SHIFT_VARIANTS as $sv) {
				if ($created >= $target) {
					break;
				}

				// countries/states: ora global (null), ora escopado
				$scope = $this->makeScope($cc);

				$data = $this->makeHolidayData(
					cc: $cc,
					faker: $faker,
					type: $type,
					observance: $observance,
					shiftBy: $sv,
					countries: $scope['countries'],
					states: $scope['states'],
					linkPool: $linkPool,
					mustLink: $mustLink
				);
				$out->writeln("<info>[HolidaySeeder]</info> creating holiday: name=\"{$data['name']}\" cc={$cc}\" type={$type} observance={$observance}");
				$this->persistHoliday($data);
				$created++;

				if (($created % 64) === 0) {
					$out->writeln("<info>[HolidaySeeder]</info> created={$created}/{$target}");
				}
			}
		}

		$out->writeln("<info>[HolidaySeeder]</info> done created={$created}");
	}

	private function output(): ConsoleOutput|\Symfony\Component\Console\Output\OutputInterface
	{
		if ($this->command instanceof \Illuminate\Console\Command && method_exists($this->command, 'getOutput')) {
			return $this->command->getOutput();
		}
		return new ConsoleOutput();
	}

	private function countriesWithRegionEnums(): array
	{
		// Espelha o que seu UsesCountryRegions efetivamente suporta (STATE_ENUMS)
		// Sem refletir o trait diretamente para manter o Seeder autocontido.
		return [
			'BR',
			'PT',
			'US',
			'CN',
			'AR',
			'BO',
			'CL',
			'EC',
			'PY',
			'PE',
			'UY',
			'CO',
			'GY',
		];
	}

	private function fakerForCountry(string $cc): Faker
	{
		$cc = strtoupper(trim($cc));
		$locale = self::LOCALE_BY_COUNTRY[$cc] ?? 'en_US';

		if (isset($this->fakerCache[$locale])) {
			return $this->fakerCache[$locale];
		}

		try {
			$this->fakerCache[$locale] = FakerFactory::create($locale);
		} catch (\Throwable) {
			$this->fakerCache[$locale] = FakerFactory::create('en_US');
		}

		return $this->fakerCache[$locale];
	}

	private function inferTargetCount(int $minPerCountry): int
	{
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				return max($opt, $minPerCountry);
			}
		}

		$n = $this->countCompaniesRaw();
		$n = max(1, $n);

		return 64 * $n;
	}

	private function countCompaniesRaw(): int
	{
		if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasColumn(DC::TABLE_USERS, 'type')) {
			return 1;
		}

		try {
			$row = DB::selectOne('select count(*) as c from ' . DC::TABLE_USERS . ' where type = ?', ['company']);
			return (int) ($row->c ?? 1);
		} catch (\Throwable) {
			return 1;
		}
	}

	private function roundUp64(int $n): int
	{
		$r = $n % 64;
		return $r === 0 ? $n : ($n + (64 - $r));
	}

	private function pickCountryWeighted(array $countries): string
	{
		// 75% BR, 25% demais
		if (in_array('BR', $countries, true) && random_int(1, 100) <= 75) {
			return 'BR';
		}

		$pool = collect($countries)->reject(fn($c) => (string) $c === 'BR')->values()->all();
		return $pool ? $pool[array_rand($pool)] : 'BR';
	}

	/**
	 * Retorna:
	 * - countries: ?array<string>
	 * - states: ?array<string, array<int, string>>  (map por país)
	 */
	private function makeScope(string $cc): array
	{
		$cc = strtoupper(trim($cc));

		// 30% global (null)
		if (random_int(1, 100) <= 30) {
			return ['countries' => null, 'states' => null];
		}

		// 70% escopado (countries)
		$countries = null;

		if ($cc === 'BR') {
			$countries = ['BR'];
		} else {
			$roll = random_int(1, 100);
			if ($roll <= 55) $countries = ['BR'];
			elseif ($roll <= 80) $countries = ['BR', $cc];
			else $countries = [$cc];
		}

		// 35% com state-scope (somente se houver enum no seu trait para esse cc)
		$states = null;
		$hasEnum = in_array($cc, $this->countriesWithRegionEnums(), true);

		if ($hasEnum && random_int(1, 100) <= 35) {
			$states = $this->randomStatesMap($cc);
			// Quando há states, faz sentido garantir que countries contenha o cc
			if (is_array($countries) && !in_array($cc, $countries, true)) {
				$countries = collect($countries)->push($cc)->unique()->values()->all();
			}
		}

		return ['countries' => $countries, 'states' => $states];
	}

	private function randomStatesMap(string $cc): ?array
	{

		$cc = strtoupper(trim($cc));

		try {
			$values = match ($cc) {
				'BR' => array_map(fn($e) => (string) $e->value, \App\Enums\BrazilState::cases()),
				'PT' => array_map(fn($e) => (string) $e->value, \App\Enums\PortugalState::cases()),
				'US' => array_map(fn($e) => (string) $e->value, \App\Enums\UnitedStatesState::cases()),
				'CN' => array_map(fn($e) => (string) $e->value, \App\Enums\ChinaState::cases()),
				'AR' => array_map(fn($e) => (string) $e->value, \App\Enums\ArgentinaProvince::cases()),
				'BO' => array_map(fn($e) => (string) $e->value, \App\Enums\BoliviaDepartment::cases()),
				'CL' => array_map(fn($e) => (string) $e->value, \App\Enums\ChileRegion::cases()),
				'EC' => array_map(fn($e) => (string) $e->value, \App\Enums\EcuadorProvince::cases()),
				'PY' => array_map(fn($e) => (string) $e->value, \App\Enums\ParaguayDepartment::cases()),
				'PE' => array_map(fn($e) => (string) $e->value, \App\Enums\PeruDepartment::cases()),
				'UY' => array_map(fn($e) => (string) $e->value, \App\Enums\UruguayDepartment::cases()),
				'CO' => array_map(fn($e) => (string) $e->value, \App\Enums\ColombiaDepartment::cases()),
				'GY' => array_map(fn($e) => (string) $e->value, \App\Enums\GuyanaRegion::cases()),
				default => [],
			};
		} catch (\Throwable) {
			$values = [];
		}

		if (!$values) return null;

		$take = min(count($values), random_int(1, 5));
		$picked = [];

		for ($i = 0; $i < $take; $i++) {
			$picked[] = $values[array_rand($values)];
		}

		$picked = collect($picked)->unique()->values()->all();

		return [$cc => $picked];
	}

	private function makeHolidayData(
		string $cc,
		Faker $faker,
		string $type,
		string $observance,
		array $shiftBy,
		?array $countries,
		?array $states = null,
		array $linkPool = [],
		?array &$mustLink = null
	): array {
		$cc = strtoupper(trim($cc));

		$year = (int) now()->format('Y');
		$date = Carbon::create($year, random_int(1, 12), random_int(1, 28))->startOfDay();

		// 70% single-day, 30% multi-day (1..4 dias)
		$endDate = (clone $date);
		if (random_int(1, 100) > 70) {
			$endDate = (clone $date)->addDays(random_int(1, 4));
		}

		// name (unique): usa locale do faker + sufixo curto
		$name = $this->uniqueName($cc, $faker, $date, $endDate);

		// code (uuid nullable unique): em 80% dos registros
		$code = null;
		if (random_int(1, 100) <= 80) {
			$code = $this->uniqueUuidCode();
		}

		// occasion: texto localizado
		$occasion = null;
		if (random_int(1, 100) <= 85) {
			$occasion = $this->localizedOccasion($faker);
		}

		// recurring: mantém boolean (às vezes null para testes)
		$recurring = false;
		$rollRec = random_int(1, 100);
		if ($rollRec <= 10) $recurring = true;
		elseif ($rollRec <= 15) $recurring = null;

		// vínculos (garante 1 de cada quando possível)
		$links = [
			'event' => null,
			'award' => null,
			'coupon' => null,
			'project' => null,
			'task' => null,
			'meeting' => null,
			'goal' => null,
		];

		if (is_array($mustLink)) {
			foreach ($links as $k => $_) {
				if (($mustLink[$k] ?? false) === true && !empty($linkPool[$k])) {
					$links[$k] = $linkPool[$k][array_rand($linkPool[$k])];
					$mustLink[$k] = false;
					break;
				}
			}
		} else {
			foreach ($links as $k => $_) {
				if (!empty($linkPool[$k]) && random_int(1, 100) <= 8) {
					$links[$k] = $linkPool[$k][array_rand($linkPool[$k])];
				}
			}
		}

		// listas (deixe null com frequência; quando setar, use IDs reais)
		$designations = $this->maybeRandomIdList(DC::TABLE_DESIGNS ?? 'designations', 2, 10, 10);
		$departments  = $this->maybeRandomIdList(DC::TABLE_DEPARTMENTS, 2, 10, 15);
		$branches     = $this->maybeRandomIdList(DC::TABLE_BRANCHES, 2, 10, 15);
		$companies    = $this->maybeRandomCompanyIdList(2, 10, 15);
		$vendors      = $this->maybeRandomIdList(DC::TABLE_VENDORS, 2, 10, 12);
		$customers    = $this->maybeRandomIdList(DC::TABLE_CUSTOMERS, 2, 10, 12);

		return [
			'code' => $code,
			'name' => $name,
			'date' => $date,
			PJC::COL_E_DT => $endDate,
			'occasion' => $occasion,
			'type' => $type,
			'observance' => $observance,
			'recurring' => $recurring,

			'event' => $links['event'],
			'award' => $links['award'],
			'coupon' => $links['coupon'],
			'project' => $links['project'],
			'task' => $links['task'],
			'meeting' => $links['meeting'],
			'goal' => $links['goal'],

			// JSON fields (casts array)
			PJC::COL_RDC_SHFT_BY => $shiftBy,
			'countries' => $countries,
			'states' => $states,

			'designations' => $designations,
			'departments' => $departments,
			'branches' => $branches,
			'companies' => $companies,
			'vendors' => $vendors,
			'customers' => $customers,
		];
	}

	private function persistHoliday(array $data): void
	{
		$m = new Holiday();
		$m->fill($data);
		$m->save();
	}

	private function localizedOccasion(Faker $faker): string
	{
		$txt = (string) $faker->sentence(random_int(4, 10));
		return rtrim($txt, ". \t\n\r\0\x0B");
	}

	private function uniqueUuidCode(): string
	{
		$attempts = 0;

		do {
			$attempts++;
			$uuid = (string) Str::uuid();

			$exists = false;
			try {
				$row = DB::selectOne('select 1 as x from ' . DC::TABLE_HLD . ' where code = ? limit 1', [$uuid]);
				$exists = ($row !== null);
			} catch (\Throwable) {
				$exists = false;
			}

			if (!$exists) {
				return $uuid;
			}
		} while ($attempts < self::MAX_UNIQUE_ATTEMPTS);

		// fallback: reduz chance de colisão ainda mais
		return (string) Str::uuid();
	}

	private function uniqueName(string $cc, Faker $faker, Carbon $date, Carbon $endDate): string
	{
		$attempts = 0;

		do {
			$attempts++;

			// name <= 254, unique
			// Base localizado + sufixo curto para unicidade
			$base = $this->localizedTitle($faker);
			$suffix = strtoupper(Str::random(6));
			$name = "{$base} ({$cc}) {$date->format('Ymd')}-{$endDate->format('Ymd')}-{$suffix}";

			// hard cap 254
			if (mb_strlen($name) > 254) {
				$name = mb_substr($name, 0, 254);
			}

			$exists = false;
			try {
				$row = DB::selectOne('select 1 as x from ' . DC::TABLE_HLD . ' where name = ? limit 1', [$name]);
				$exists = ($row !== null);
			} catch (\Throwable) {
				$exists = false;
			}

			if (!$exists) {
				return $name;
			}
		} while ($attempts < self::MAX_UNIQUE_ATTEMPTS);

		// fallback final (ainda localizado, mas forçando variação)
		$name = $this->localizedTitle($faker) . ' ' . strtoupper(Str::random(10));
		return mb_strlen($name) > 254 ? mb_substr($name, 0, 254) : $name;
	}

	private function localizedTitle(Faker $faker): string
	{
		// título curto e “natural”
		$words = $faker->words(random_int(2, 5), true);
		return Str::title((string) $words);
	}

	private function maybeRandomCompanyIdList(int $min, int $max, int $chancePct): ?array
	{
		if (random_int(1, 100) > $chancePct) {
			return null;
		}

		if (!Schema::hasTable(DC::TABLE_USERS) || !Schema::hasColumn(DC::TABLE_USERS, 'type')) {
			return null;
		}

		$limit = max($min, min($max, random_int($min, $max)));
		$sql = 'select id from ' . DC::TABLE_USERS . ' where type = ? order by ' . $this->randomOrderSql() . ' limit ' . (int) $limit;

		try {
			$rows = DB::select($sql, ['company']);
		} catch (\Throwable) {
			return null;
		}

		$ids = [];
		foreach ($rows as $r) {
			if (is_scalar($r->id ?? null)) $ids[] = (string) $r->id;
		}

		$ids = collect($ids)->unique()->values()->all();
		return $ids ?: null;
	}

	private function maybeRandomIdList(string $table, int $min, int $max, int $chancePct): ?array
	{
		if (random_int(1, 100) > $chancePct) {
			return null;
		}

		if (!Schema::hasTable($table)) {
			return null;
		}

		$limit = max($min, min($max, random_int($min, $max)));
		$sql = 'select id from ' . $table . ' order by ' . $this->randomOrderSql() . ' limit ' . (int) $limit;

		try {
			$rows = DB::select($sql);
		} catch (\Throwable) {
			return null;
		}

		$ids = [];
		foreach ($rows as $r) {
			if (is_scalar($r->id ?? null)) $ids[] = (string) $r->id;
		}

		$ids = collect($ids)->unique()->values()->all();
		return $ids ?: null;
	}

	private function randomOrderSql(): string
	{
		return match (DB::getDriverName()) {
			'pgsql', 'sqlite' => 'RANDOM()',
			default => 'RAND()',
		};
	}

	private function loadLinkPool(): array
	{
		return [
			'event' => $this->idsFromTable(DC::TABLE_EVENTS),
			'award' => $this->idsFromTable(DC::TABLE_AWD),
			'coupon' => $this->idsFromTable(DC::TABLE_COUPONS),
			'project' => $this->idsFromTable(DC::TABLE_PROJECTS),
			'task' => $this->idsFromTable(DC::TABLE_TASKS),
			'meeting' => $this->idsFromTable(DC::TABLE_MEETINGS),
			'goal' => $this->idsFromTable(DC::TABLE_GL),
		];
	}

	private function idsFromTable(string $table, int $limit = 6): array
	{
		if (!Schema::hasTable($table)) return [];

		$sql = 'select id from ' . $table . ' order by ' . $this->randomOrderSql() . ' limit ' . (int) $limit;

		try {
			$rows = DB::select($sql);
		} catch (\Throwable) {
			return [];
		}

		$ids = [];
		foreach ($rows as $r) {
			if (is_scalar($r->id ?? null)) $ids[] = (string) $r->id;
		}

		return collect($ids)->unique()->values()->all();
	}
}
