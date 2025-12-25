<?php

namespace App\Traits;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{
	BrazilState,
	ChinaState,
	CountryName,
	PortugalState,
	UnitedStatesState,
	ArgentinaProvince,
	BoliviaDepartment,
	ChileRegion,
	EcuadorProvince,
	ParaguayDepartment,
	PeruDepartment,
	UruguayDepartment,
	ColombiaDepartment,
	GuyanaRegion
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Log, Schema};

trait UsesCountryRegions
{
	use DetectsRows, NormalizesArrays;
	/**
	 * Map ISO-3166-1 alpha-2 country code => enum class for subnational units.
	 * @var array<string, class-string>
	 */
	protected const STATE_ENUMS = [
		'BR' => BrazilState::class,
		'PT' => PortugalState::class,
		'US' => UnitedStatesState::class,
		'CN' => ChinaState::class,

		'AR' => ArgentinaProvince::class,
		'BO' => BoliviaDepartment::class,
		'CL' => ChileRegion::class,
		'EC' => EcuadorProvince::class,
		'PY' => ParaguayDepartment::class,
		'PE' => PeruDepartment::class,
		'UY' => UruguayDepartment::class,
		'CO' => ColombiaDepartment::class,
		'GY' => GuyanaRegion::class,
	];

	protected function stateEnumClassForCountry(string $countryCode): ?string
	{
		$cc = strtoupper(trim($countryCode));
		return static::STATE_ENUMS[$cc] ?? null;
	}

	protected function normalizeCountryToCode(?string $value): ?string
	{
		$v = trim((string) $value);
		if ($v === '') return null;

		$vv = strtolower($v);
		$enum = CountryName::normalize($vv);
		if ($enum instanceof CountryName) {
			return match ($enum) {
				CountryName::Brazil        => 'BR',
				CountryName::UnitedStates  => 'US',
				CountryName::Canada        => 'CA',
				CountryName::UnitedKingdom => 'GB',
				CountryName::Germany       => 'DE',
				CountryName::France        => 'FR',
				CountryName::Spain         => 'ES',
				CountryName::Portugal      => 'PT',
				CountryName::Italy         => 'IT',
				CountryName::Argentina     => 'AR',
				CountryName::Chile         => 'CL',
				CountryName::Mexico        => 'MX',
				CountryName::Japan         => 'JP',
				CountryName::China         => 'CN',
				CountryName::India         => 'IN',
				CountryName::Australia     => 'AU',
				CountryName::SouthAfrica   => 'ZA',
				CountryName::Denmark       => 'DK',
				CountryName::Netherlands   => 'NL',
				CountryName::Poland        => 'PL',
				CountryName::SaudiArabia   => 'SA',
				CountryName::Turkey        => 'TR',
				CountryName::Israel        => 'IL',
				CountryName::Russia        => 'RU',
				CountryName::Switzerland   => 'CH',
				CountryName::Belgium       => 'BE',
				CountryName::Austria       => 'AT',
				CountryName::Taiwan        => 'TW',
				CountryName::Colombia      => 'CO',
				CountryName::Peru          => 'PE',
				CountryName::Venezuela     => 'VE',
				CountryName::Norway        => 'NO',
				CountryName::Sweden        => 'SE',
				CountryName::Finland       => 'FI',
				CountryName::Greece        => 'GR',
				CountryName::CzechRepublic => 'CZ',
				CountryName::Hungary       => 'HU',
				CountryName::Romania       => 'RO',
				CountryName::Bolivia       => 'BO',
				CountryName::Ecuador       => 'EC',
				CountryName::Guyana        => 'GY',
				CountryName::Paraguay      => 'PY',
				CountryName::Suriname      => 'SR',
				CountryName::Uruguay       => 'UY',
				CountryName::FrenchGuiana  => 'GF',
			};
		}

		$ascii = strtoupper(Str::ascii($v));
		if (preg_match('/^[A-Z]{2,3}$/', $ascii)) {
			return match ($ascii) {
				'BRA', 'BR' => 'BR',
				'USA', 'US' => 'US',
				'CAN', 'CA' => 'CA',
				'GBR', 'GB', 'UK' => 'GB',
				'DEU', 'DE' => 'DE',
				'FRA', 'FR' => 'FR',
				'ESP', 'ES' => 'ES',
				'PRT', 'PT' => 'PT',
				'ITA', 'IT' => 'IT',
				'ARG', 'AR' => 'AR',
				'CHL', 'CL' => 'CL',
				'MEX', 'MX' => 'MX',
				'JPN', 'JP' => 'JP',
				'CHN', 'CN' => 'CN',
				'IND', 'IN' => 'IN',
				'AUS', 'AU' => 'AU',
				'ZAF', 'ZA' => 'ZA',
				'DNK', 'DK' => 'DK',
				'NLD', 'NL' => 'NL',
				'POL', 'PL' => 'PL',
				'SAU', 'SA' => 'SA',
				'TUR', 'TR' => 'TR',
				'ISR', 'IL' => 'IL',
				'RUS', 'RU' => 'RU',
				'CHE', 'CH' => 'CH',
				'BEL', 'BE' => 'BE',
				'AUT', 'AT' => 'AT',
				'TWN', 'TW' => 'TW',
				'COL', 'CO' => 'CO',
				'PER', 'PE' => 'PE',
				'VEN', 'VE' => 'VE',
				'NOR', 'NO' => 'NO',
				'SWE', 'SE' => 'SE',
				'FIN', 'FI' => 'FI',
				'GRC', 'GR' => 'GR',
				'CZE', 'CZ' => 'CZ',
				'HUN', 'HU' => 'HU',
				'ROU', 'RO' => 'RO',
				'BOL', 'BO' => 'BO',
				'ECU', 'EC' => 'EC',
				'GUY', 'GY' => 'GY',
				'PRY', 'PY' => 'PY',
				'SUR', 'SR' => 'SR',
				'URY', 'UY' => 'UY',
				'GUF', 'GF' => 'GF',
				default => strlen($ascii) === 2 ? $ascii : null,
			};
		}

		$tokens = strtoupper(Str::ascii($v));
		return match ($tokens) {
			'BRASIL', 'BRAZIL' => 'BR',
			'UNITED STATES', 'UNITED STATES OF AMERICA', 'ESTADOS UNIDOS' => 'US',
			'UNITED KINGDOM', 'GREAT BRITAIN', 'REINO UNIDO' => 'GB',
			'ARGENTINA' => 'AR',
			'BOLIVIA' => 'BO',
			'CHILE' => 'CL',
			'ECUADOR', 'EQUADOR' => 'EC',
			'PARAGUAY' => 'PY',
			'PERU', 'PERÚ' => 'PE',
			'URUGUAY' => 'UY',
			'COLOMBIA' => 'CO',
			'GUYANA' => 'GY',
			default => null,
		};
	}

	protected function normalizeStateForCountry(?string $value, string $countryCode, bool $preferRawIfNoEnum): ?string
	{
		$v = trim((string) $value);
		if ($v === '') return null;

		$cc = strtoupper(trim($countryCode));
		$cls = $this->stateEnumClassForCountry($cc);

		if (is_string($cls) && $cls !== '' && method_exists($cls, 'normalize')) {
			try {
				$e = $cls::normalize($v);
				if ($e instanceof \BackedEnum) {
					return (string) $e->value;
				}
			} catch (\Throwable $t) {
				// Falha silenciosa: cai no fallback abaixo (defensivo)
			}
		}

		if ($preferRawIfNoEnum) {
			$raw = strtoupper(Str::ascii($v));
			return $raw !== '' ? $raw : null;
		}

		return null;
	}

	protected function detectStateFromAddress(string $address, string $countryCode, array $allowedStates): ?string
	{
		$addr = trim($address);
		if ($addr === '') return null;

		$cc = strtoupper(trim($countryCode));
		$hayAscii = strtoupper(Str::ascii($addr));
		$hayRaw = mb_strtoupper($addr);

		$cls = $this->stateEnumClassForCountry($cc);

		foreach ($allowedStates as $st) {
			$st = strtoupper(trim((string) $st));
			if ($st === '') continue;

			$tokens = [];

			// Evita falso-positivo com códigos de 1 caractere (AR/BO/EC etc.)
			// Endereços reais quase sempre vêm com o nome da província/departamento, não a letra.
			if (strlen($st) >= 2) {
				$tokens[] = $st;
			}

			$e = null;
			if ($cls && enum_exists($cls) && method_exists($cls, 'tryFrom')) {
				$e = $cls::tryFrom($st);
			}

			if ($e && method_exists($e, 'label')) {
				$tokens[] = strtoupper(Str::ascii((string) $e->label()));
			}

			// aliases úteis por país (sem “explodir” ambiguidade)
			if ($cc === 'AR' && $e instanceof \BackedEnum) {
				// CABA (evita usar "BUENOS AIRES" aqui, para não conflitar com província B)
				if ($e->value === 'C') {
					$tokens[] = 'CABA';
					$tokens[] = 'CAPITAL FEDERAL';
					$tokens[] = 'CIUDAD AUTONOMA DE BUENOS AIRES';
					$tokens[] = 'CIUDAD AUTÓNOMA DE BUENOS AIRES';
				}
				if ($e->value === 'B') {
					$tokens[] = 'BUENOS AIRES';
					$tokens[] = 'PROVINCIA DE BUENOS AIRES';
					$tokens[] = 'BS AS';
					$tokens[] = 'BS.AS.';
				}
			}

			if ($cc === 'CL' && $e && method_exists($e, 'romanNumeral')) {
				$rn = strtoupper(Str::ascii((string) $e->romanNumeral()));
				if ($rn !== '') {
					$tokens[] = $rn;
					$tokens[] = "REGION {$rn}";
					$tokens[] = "REGIÓN {$rn}";
					$tokens[] = "{$rn} REGION";
					$tokens[] = "{$rn} REGIÓN";
				}
			}

			if ($cc === 'CL' && $e && method_exists($e, 'shortName')) {
				$tokens[] = strtoupper(Str::ascii((string) $e->shortName()));
			}

			if ($cc === 'GY' && $e && method_exists($e, 'regionNumber')) {
				$n = (string) $e->regionNumber();
				if ($n !== '') {
					$tokens[] = "REGION {$n}";
					$tokens[] = "REGIÓN {$n}";
					$tokens[] = $n;
				}
			}

			if ($cc === 'CO' && $st === 'DC') {
				$tokens[] = 'BOGOTA';
				$tokens[] = 'BOGOTÁ';
				$tokens[] = 'BOGOTA D.C.';
				$tokens[] = 'BOGOTÁ D.C.';
				$tokens[] = 'DISTRITO CAPITAL';
			}

			// Match ASCII tokens
			if ($this->addressHasAnyStateToken($hayAscii, $tokens)) return $st;

			// Match China native tokens (quando houver)
			if ($cc === 'CN' && $e) {
				$zh = [];
				if (method_exists($e, 'labelZh')) $zh[] = mb_strtoupper((string) $e->labelZh());
				if (method_exists($e, 'labelFullZh')) $zh[] = mb_strtoupper((string) $e->labelFullZh());
				foreach ($zh as $t) {
					if ($t !== '' && mb_strpos($hayRaw, $t) !== false) return $st;
				}
			}
		}

		// fallback genérico (somente para países sem enum)
		foreach ($allowedStates as $st) {
			$t = strtoupper(Str::ascii(trim((string) $st)));
			if ($t === '' || strlen($t) < 2) continue;
			if (mb_strpos($hayAscii, $t) !== false) return $t;
		}

		return null;
	}

	public function getCountriesConstraintAttribute(): ?array
	{
		$c = $this->normalizeStringList($this->getAttribute('countries'));
		$codes = $this->countryCodesFromMixedList($c);
		return $codes ?: null;
	}

	public function getStatesConstraintAttribute(): ?array
	{
		$raw = $this->normalizeStatesMap($this->getAttribute('states'));
		if ($raw === null) return null;

		$countries = $this->getCountriesConstraintAttribute();
		$hasCountries = $countries !== null;

		$out = $this->normalizeAllowedStatesByCountry($raw, $countries ?? [], $hasCountries);
		return $out ?: null;
	}

	protected function normalizeStatesMap(mixed $value): ?array
	{
		if ($value === null) return null;
		$arr = is_array($value) ? $value : (self::looksLikeJson((string) $value) ? (json_decode((string) $value, true) ?: []) : []);
		if (!is_array($arr)) return null;

		$out = [];
		foreach ($arr as $k => $v) {
			if (!is_scalar($k)) continue;
			$ck = $this->normalizeCountryToCode((string) $k);
			if ($ck === null) continue;

			$list = is_array($v) ? $v : [];
			$states = [];
			foreach ($list as $sv) {
				if (!is_scalar($sv)) continue;
				$states[] = (string) $sv;
			}
			$states = array_values(array_unique(array_filter(array_map('trim', $states), fn($x) => $x !== '')));
			if ($states) $out[$ck] = $states;
		}

		return $out ?: null;
	}

	protected function countryCodesFromMixedList(?array $countries): array
	{
		if ($countries === null) return [];
		$out = [];
		foreach ($countries as $c) {
			$code = $this->normalizeCountryToCode((string) $c);
			if ($code !== null) $out[] = $code;
		}
		return array_values(array_unique($out));
	}

	protected function normalizeAllowedStatesByCountry(array $statesRaw, array $allowedCountries, bool $hasCountries): array
	{
		$out = [];

		foreach ($statesRaw as $countryCode => $list) {
			$cc = $this->normalizeCountryToCode((string) $countryCode);
			if ($cc === null) continue;
			if ($hasCountries && !in_array($cc, $allowedCountries, true)) continue;

			$normStates = [];
			foreach ((array) $list as $sv) {
				if (!is_scalar($sv)) continue;
				$state = $this->normalizeStateForCountry((string) $sv, $cc, false);
				if ($state === null) continue;
				$normStates[] = $state;
			}

			$normStates = array_values(array_unique(array_filter($normStates, fn($x) => is_string($x) && $x !== '')));
			if ($normStates) $out[$cc] = $normStates;
		}

		return $out;
	}

	protected function detectCountryFromAddress(string $address, array $allowedCountryCodes, bool $mustDetect): ?string
	{
		$addr = trim($address);
		if ($addr === '') return $mustDetect ? null : null;
		if (!$allowedCountryCodes) return null;

		$hayAscii = strtoupper(Str::ascii($addr));
		$hayRaw = mb_strtoupper($addr);

		foreach ($allowedCountryCodes as $cc) {
			$tokens = $this->countryTokensForCode($cc);
			foreach ($tokens as $t) {
				if ($t === '') continue;

				if (preg_match('/[^\x00-\x7F]/', $t)) {
					if (mb_strpos($hayRaw, mb_strtoupper($t)) !== false) return $cc;
					continue;
				}

				$tt = strtoupper(Str::ascii($t));
				if (strlen($tt) <= 3) {
					$re = '/(^|[^A-Z0-9])' . preg_quote($tt, '/') . '([^A-Z0-9]|$)/';
					if (preg_match($re, $hayAscii)) return $cc;
					continue;
				}

				if (mb_strpos($hayAscii, $tt) !== false) return $cc;
			}
		}

		return null;
	}

	protected function countryTokensForCode(string $cc): array
	{
		$cc = strtoupper(trim($cc));
		return match ($cc) {
			'BR' => ['BR', 'BRA', 'BRAZIL', 'BRASIL'],
			'PT' => ['PT', 'PRT', 'PORTUGAL'],
			'US' => ['US', 'USA', 'UNITED STATES', 'UNITED STATES OF AMERICA', 'ESTADOS UNIDOS'],
			'CN' => ['CN', 'CHN', 'CHINA', '中国', '中國'],

			'AR' => ['AR', 'ARG', 'ARGENTINA'],
			'BO' => ['BO', 'BOL', 'BOLIVIA'],
			'CL' => ['CL', 'CHL', 'CHILE'],
			'EC' => ['EC', 'ECU', 'ECUADOR', 'EQUADOR'],
			'PY' => ['PY', 'PRY', 'PARAGUAY'],
			'PE' => ['PE', 'PER', 'PERU', 'PERÚ'],
			'UY' => ['UY', 'URY', 'URUGUAY'],
			'CO' => ['CO', 'COL', 'COLOMBIA'],
			'GY' => ['GY', 'GUY', 'GUYANA'],

			default => [$cc],
		};
	}

	protected function addressHasAnyStateToken(string $hayAscii, array $tokens): bool
	{
		foreach ($tokens as $t) {
			$tt = strtoupper(Str::ascii(trim((string) $t)));
			if ($tt === '') continue;

			// Evita match de 1 caractere (muito “ruidoso” em endereços)
			if (strlen($tt) === 1) continue;

			if (strlen($tt) <= 3) {
				$re = '/(^|[^A-Z0-9])' . preg_quote($tt, '/') . '([^A-Z0-9]|$)/';
				if (preg_match($re, $hayAscii)) return true;
				continue;
			}

			if (mb_strpos($hayAscii, $tt) !== false) return true;
		}

		return false;
	}


	protected function stateInAllowed(string $state, array $allowedStates): bool
	{
		$s = strtoupper(Str::ascii(trim($state)));
		foreach ($allowedStates as $a) {
			$aa = strtoupper(Str::ascii(trim((string) $a)));
			if ($aa !== '' && $aa === $s) return true;
		}
		return false;
	}

	protected function computeEffectiveScope(): array
	{
		return $this->cacheOnce('effective_scope', function (): array {
			$countriesRaw = $this->normalizeStringList($this->getAttribute('countries'));
			$statesRaw = $this->normalizeStatesMap($this->getAttribute('states'));

			$hasCountries = $countriesRaw !== null;
			$hasStates = $statesRaw !== null;

			$allowedCountries = $hasCountries ? $this->countryCodesFromMixedList($countriesRaw) : [];
			$allowedStates = $hasStates ? $this->normalizeAllowedStatesByCountry($statesRaw, $allowedCountries, $hasCountries) : [];

			if (!$hasCountries && $hasStates) $allowedCountries = array_values(array_unique(array_keys($allowedStates)));

			$effective = [];
			if (Schema::hasColumn($this->getTable(), 'companies'))
				$effective['companies'] = $this->filterByAddressList('companies', DC::TABLE_USERS, 'address', $allowedCountries, $allowedStates, $hasCountries, $hasStates, true);
			if (Schema::hasColumn($this->getTable(), 'branches'))
				$effective['branches'] = $this->filterByAddressList('branches', DC::TABLE_BRANCHES, 'address', $allowedCountries, $allowedStates, $hasCountries, $hasStates);
			if (Schema::hasColumn($this->getTable(), 'departments'))
				$effective['departments'] = $this->filterByAddressList('departments', DC::TABLE_DEPARTMENTS, 'address', $allowedCountries, $allowedStates, $hasCountries, $hasStates);
			if (Schema::hasColumn($this->getTable(), 'vendors'))
				$effective['vendors'] = $this->filterByBillingList('vendors', DC::TABLE_VENDORS, $allowedCountries, $allowedStates, $hasCountries, $hasStates);
			if (Schema::hasColumn($this->getTable(), 'customers'))
				$effective['customers'] = $this->filterByBillingList('customers', DC::TABLE_CUSTOMERS, $allowedCountries, $allowedStates, $hasCountries, $hasStates);

			return [
				'has_countries' => $hasCountries,
				'has_states' => $hasStates,
				'allowed_countries' => $allowedCountries,
				'allowed_states' => $allowedStates,
				'effective' => $effective,
			];
		});
	}

	protected function filterByAddressList(
		string $field,
		string $table,
		string $addressColumn,
		array $allowedCountries,
		array $allowedStates,
		bool $hasCountries,
		bool $hasStates,
		bool $companyOnly = false
	): ?array {
		$list = $this->normalizeStringList($this->getAttribute($field));
		if ($list === null) return null;
		if (!$hasCountries && !$hasStates) return $list;

		if (!Schema::hasTable($table)) {
			Log::warning("Holiday scope: missing table {$table} for {$field}");
			return null;
		}

		$nameCol = $this->detectNameColumn($table);
		$select = ['id'];
		if ($nameCol !== null) $select[] = $nameCol;
		if (Schema::hasColumn($table, $addressColumn)) $select[] = $addressColumn;

		$rows = $this->resolveRowsByIdOrNameCached($table, $list, $select, $nameCol, $companyOnly ? ['type' => 'company'] : []);
		if (!$rows) return null;

		$out = [];
		foreach ($list as $token) {
			$row = $this->rowByToken($rows, $token, $nameCol);
			if (!$row) continue;

			$address = Schema::hasColumn($table, $addressColumn) && is_scalar($row->{$addressColumn} ?? null)
				? (string) $row->{$addressColumn}
				: '';

			$country = $this->detectCountryFromAddress($address, $allowedCountries, $hasCountries || $hasStates);
			if ($hasCountries && $country === null) continue;

			if ($hasStates) {
				if ($country === null) continue;
				$allowedForCountry = $allowedStates[$country] ?? null;
				if (!$allowedForCountry) continue;
				$state = $this->detectStateFromAddress($address, $country, $allowedForCountry);
				if ($state === null) continue;
				if (!$this->stateInAllowed($state, $allowedForCountry)) continue;
			}

			$out[] = $token;
		}

		$out = array_values(array_unique(array_filter($out, fn($v) => is_string($v) && trim($v) !== '')));
		return $out ?: null;
	}

	protected function filterByBillingList(
		string $field,
		string $table,
		array $allowedCountries,
		array $allowedStates,
		bool $hasCountries,
		bool $hasStates
	): ?array {
		$list = $this->normalizeStringList($this->getAttribute($field));
		if ($list === null) return null;
		if (!$hasCountries && !$hasStates) return $list;

		if (!Schema::hasTable($table)) {
			Log::warning("Holiday scope: missing table {$table} for {$field}");
			return null;
		}

		$nameCol = $this->detectNameColumn($table);
		$select = ['id'];
		if ($nameCol !== null) $select[] = $nameCol;
		foreach ([BC::COL_BL_CTR, BC::COL_BL_ST, BC::COL_BL_ADR] as $col)
			if (Schema::hasColumn($table, $col)) $select[] = $col;

		$rows = $this->resolveRowsByIdOrNameCached($table, $list, $select, $nameCol, []);
		if (!$rows) return null;

		$out = [];
		foreach ($list as $token) {
			$row = $this->rowByToken($rows, $token, $nameCol);
			if (!$row) continue;

			$billingCountry = Schema::hasColumn($table, BC::COL_BL_CTR) && is_scalar($row->{BC::COL_BL_CTR} ?? null)
				? (string) $row->{BC::COL_BL_CTR}
				: null;

			$billingState = Schema::hasColumn($table, BC::COL_BL_ST) && is_scalar($row->{BC::COL_BL_ST} ?? null)
				? (string) $row->{BC::COL_BL_ST}
				: null;

			$billingAddress = Schema::hasColumn($table, BC::COL_BL_ADR) && is_scalar($row->{BC::COL_BL_ADR} ?? null)
				? (string) $row->{BC::COL_BL_ADR}
				: '';

			$country = $this->normalizeCountryToCode($billingCountry);
			if ($country === null) $country = $this->detectCountryFromAddress($billingAddress, $allowedCountries, $hasCountries || $hasStates);
			if ($hasCountries && $country === null) continue;

			if ($hasStates) {
				if ($country === null) continue;
				$allowedForCountry = $allowedStates[$country] ?? null;
				if (!$allowedForCountry) continue;

				$state = $this->normalizeStateForCountry($billingState, $country, true);
				if ($state === null) $state = $this->detectStateFromAddress($billingAddress, $country, $allowedForCountry);
				if ($state === null) continue;
				if (!$this->stateInAllowed($state, $allowedForCountry)) continue;
			}

			$out[] = $token;
		}

		$out = array_values(array_unique(array_filter($out, fn($v) => is_string($v) && trim($v) !== '')));
		return $out ?: null;
	}

	protected function enforceGeoScopeOnPersistedLists(): void
	{
		$effective = $this->computeEffectiveScope();
		foreach (['companies', 'branches', 'departments', 'vendors', 'customers'] as $field) {
			if (!Schema::hasColumn($this->getTable(), $field)) continue;
			$current = $this->normalizeStringList($this->getAttribute($field));
			if ($current === null) continue;
			$this->setAttribute($field, $effective['effective'][$field] ?? null);
		}
		$countries = $this->normalizeStringList($this->getAttribute('countries'));
		$states = $this->normalizeStatesMap($this->getAttribute('states'));
		if ($countries === null) $this->setAttribute('countries', null);
		if ($states === null) $this->setAttribute('states', null);
	}
}
