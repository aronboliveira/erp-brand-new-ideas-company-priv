<?php

namespace App\Enums;

enum SurinameDistrict: string
{
	// Suriname districts (ISO 3166-2:SR codes)
	case BR = 'BR'; // Brokopondo
	case CM = 'CM'; // Commewijne
	case CR = 'CR'; // Coronie
	case MA = 'MA'; // Marowijne
	case NI = 'NI'; // Nickerie
	case PA = 'PA'; // Para
	case PM = 'PM'; // Paramaribo
	case SA = 'SA'; // Saramacca
	case SI = 'SI'; // Sipaliwini
	case WA = 'WA'; // Wanica

	// Suriname area codes (códigos de área) - Suriname uses +597 country code
	// Note: Suriname's telephone system has area codes for districts
	public const AREA_CODES = [
		// Fixed line prefixes by district
		'42',  // Paramaribo and Wanica
		'43',  // Paramaribo
		'44',  // Paramaribo
		'45',  // Paramaribo and Wanica
		'46',  // Wanica
		'47',  // Commewijne
		'48',  // Saramacca
		'49',  // Coronie
		'52',  // Nickerie
		'53',  // Nickerie
		'54',  // Brokopondo
		'55',  // Para
		'56',  // Marowijne
		'57',  // Sipaliwini (interior)
		// Mobile prefixes
		'6',   // Digicel mobile
		'7',   // Telesur mobile
		'8',   // Telesur mobile
		'88',  // Mobile numbers
		'89',  // Mobile numbers
	];

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return null;
		$v = strtoupper(trim($value));
		if ($v === '')
			return null;

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Districts
			'BROKOPONDO'                  => self::BR,
			'COMMEWIJNE'                  => self::CM,
			'CORONIE'                     => self::CR,
			'MAROWIJNE'                   => self::MA,
			'NICKERIE'                    => self::NI,
			'PARA'                        => self::PA,
			'PARAMARIBO'                  => self::PM,
			'SARAMACCA'                   => self::SA,
			'SIPALIWINI'                  => self::SI,
			'WANICA'                      => self::WA,
			// Alternative names and abbreviations
			'PARBO'                       => self::PM, // Common nickname for Paramaribo
			'CAPITAL'                     => self::PM, // Paramaribo is the capital
			'DISTRICT OF PARAMARIBO'      => self::PM,
			'BROKO'                       => self::BR, // Short for Brokopondo
			'COMM'                        => self::CM, // Short for Commewijne
			'SIP'                         => self::SI, // Short for Sipaliwini
			// Dutch spellings (Suriname's official language)
			'BROKOPONDO DISTRICT'         => self::BR,
			'COMMEWIJNE DISTRICT'         => self::CM,
			'CORONIE DISTRICT'            => self::CR,
			'MAROWIJNE DISTRICT'          => self::MA,
			'NICKERIE DISTRICT'           => self::NI,
			'PARA DISTRICT'               => self::PA,
			'PARAMARIBO DISTRICT'         => self::PM,
			'SARAMACCA DISTRICT'          => self::SA,
			'SIPALIWINI DISTRICT'         => self::SI,
			'WANICA DISTRICT'             => self::WA,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::BR => 'Brokopondo',
			self::CM => 'Commewijne',
			self::CR => 'Coronie',
			self::MA => 'Marowijne',
			self::NI => 'Nickerie',
			self::PA => 'Para',
			self::PM => 'Paramaribo',
			self::SA => 'Saramacca',
			self::SI => 'Sipaliwini',
			self::WA => 'Wanica',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::BR => 'Brokopondo',
			self::CM => 'Commewijne',
			self::CR => 'Coronie',
			self::MA => 'Marowijne',
			self::NI => 'Nickerie',
			self::PA => 'Para',
			self::PM => 'Paramaribo',
			self::SA => 'Saramacca',
			self::SI => 'Sipaliwini',
			self::WA => 'Wanica',
		};
	}

	// Get Dutch labels (Suriname's official language)
	public function labelNl(): string
	{
		return match ($this) {
			self::BR => 'Brokopondo',
			self::CM => 'Commewijne',
			self::CR => 'Coronie',
			self::MA => 'Marowijne',
			self::NI => 'Nickerie',
			self::PA => 'Para',
			self::PM => 'Paramaribo',
			self::SA => 'Saramacca',
			self::SI => 'Sipaliwini',
			self::WA => 'Wanica',
		};
	}

	// Get capital/administrative center of each district
	public function capital(): string
	{
		return match ($this) {
			self::BR => 'Brokopondo',
			self::CM => 'Nieuw-Amsterdam',
			self::CR => 'Totness',
			self::MA => 'Albina',
			self::NI => 'Nieuw-Nickerie',
			self::PA => 'Onverwacht',
			self::PM => 'Paramaribo',
			self::SA => 'Groningen',
			self::SI => 'None (district capital varies)', // Sipaliwini has no fixed capital
			self::WA => 'Lelydorp',
		};
	}

	// Get ISO 3166-2:SR code (full code including country prefix)
	public function isoCode(): string
	{
		return "SR-{$this->value}";
	}

	// Get region grouping (Suriname can be divided into regions)
	public function region(): string
	{
		return match ($this) {
			self::PM, self::WA, self::CM, self::SA => 'Coastal',
			self::BR, self::PA => 'Central',
			self::CR, self::NI => 'Western Coastal',
			self::MA => 'Eastern Coastal',
			self::SI => 'Interior',
		};
	}

	// Get area in square kilometers (approximate)
	public function area(): int
	{
		return match ($this) {
			self::BR => 7364,
			self::CM => 2353,
			self::CR => 3902,
			self::MA => 4627,
			self::NI => 5353,
			self::PA => 5393,
			self::PM => 183,
			self::SA => 3636,
			self::SI => 130567, // Largest district, covers most of Suriname's interior
			self::WA => 443,
		};
	}

	// Get population rank (approximate)
	public function populationRank(): int
	{
		return match ($this) {
			self::PM => 1,  // Most populous (capital)
			self::WA => 2,
			self::NI => 3,
			self::MA => 4,
			self::CM => 5,
			self::SA => 6,
			self::BR => 7,
			self::PA => 8,
			self::CR => 9,
			self::SI => 10, // Least populous (but largest area)
		};
	}

	// Get area rank (from largest to smallest)
	public function areaRank(): int
	{
		return match ($this) {
			self::SI => 1,  // Largest by far
			self::BR => 2,
			self::NI => 3,
			self::PA => 4,
			self::MA => 5,
			self::CR => 6,
			self::SA => 7,
			self::CM => 8,
			self::WA => 9,
			self::PM => 10, // Smallest
		};
	}

	// Get whether district is coastal
	public function isCoastal(): bool
	{
		return match ($this) {
			self::PM, self::WA, self::CM, self::SA, self::CR, self::NI, self::MA => true,
			self::BR, self::PA, self::SI => false,
		};
	}

	// Get whether district is in the interior (hinterland)
	public function isInterior(): bool
	{
		return !$this->isCoastal();
	}

	// Get main economic activity
	public function majorEconomicActivity(): string
	{
		return match ($this) {
			self::BR => 'Gold mining, Hydropower (Brokopondo Reservoir)',
			self::CM => 'Agriculture (rice, bananas), Fishing',
			self::CR => 'Agriculture (coconuts, rice), Fishing',
			self::MA => 'Gold mining, Agriculture, Border trade',
			self::NI => 'Agriculture (rice), Fishing, Border trade with Guyana',
			self::PA => 'Gold mining, Bauxite, Agriculture',
			self::PM => 'Government, Services, Commerce, Tourism',
			self::SA => 'Agriculture (rice, bananas), Fishing',
			self::SI => 'Gold mining, Ecotourism, Indigenous communities',
			self::WA => 'Services, Industry, Agriculture',
		};
	}

	// Get border countries
	public function borders(): array
	{
		return match ($this) {
			self::MA => ['French Guiana (France)'],
			self::SI => ['Brazil', 'French Guiana (France)'],
			self::NI => ['Guyana'],
			default => [],
		};
	}

	// Get main area code for the district
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::PM => '42', // Paramaribo
			self::WA => '46', // Wanica
			self::CM => '47', // Commewijne
			self::SA => '48', // Saramacca
			self::CR => '49', // Coronie
			self::NI => '52', // Nickerie
			self::BR => '54', // Brokopondo
			self::PA => '55', // Para
			self::MA => '56', // Marowijne
			self::SI => '57', // Sipaliwini
		};
	}

	// Get district from phone prefix (simplified)
	public static function fromPhonePrefix(string $prefix): ?self
	{
		$map = [
			'42' => self::PM, // Paramaribo
			'43' => self::PM,
			'44' => self::PM,
			'45' => self::PM, // Also Wanica
			'46' => self::WA, // Wanica
			'47' => self::CM, // Commewijne
			'48' => self::SA, // Saramacca
			'49' => self::CR, // Coronie
			'52' => self::NI, // Nickerie
			'53' => self::NI,
			'54' => self::BR, // Brokopondo
			'55' => self::PA, // Para
			'56' => self::MA, // Marowijne
			'57' => self::SI, // Sipaliwini
		];

		return $map[$prefix] ?? null;
	}

	// Get indigenous and Maroon communities presence
	public function indigenousCommunities(): array
	{
		return match ($this) {
			self::BR => ['Maroon communities (Saramacca, Matawai)'],
			self::CM => ['Some Maroon communities'],
			self::MA => ['Maroon communities (Ndyuka), Indigenous (Kaliña, Lokono)'],
			self::PA => ['Maroon communities'],
			self::SI => ['Indigenous (Tiriyó, Wayana, Akurio, Warao), Maroon communities'],
			self::SA => ['Some Indigenous communities'],
			default => [],
		};
	}

	// Get notable landmarks or features
	public function notableFeatures(): array
	{
		return match ($this) {
			self::BR => ['Brokopondo Reservoir (Brokopondomeer)', 'Afobaka Dam', 'Brownsberg Nature Park'],
			self::CM => ['Fort Nieuw-Amsterdam', 'Commewijne River', 'Plantation ruins'],
			self::CR => ['Coronie coast', 'Bigi Pan nature reserve'],
			self::MA => ['Albina border town', 'Marowijne River', 'Galibi Nature Reserve (sea turtles)'],
			self::NI => ['Nieuw-Nickerie (border town)', 'Bigi Pan', 'Rice fields'],
			self::PA => ['Zanderij area', 'Onverwacht', 'Paramam gold fields'],
			self::PM => ['Historic Inner City of Paramaribo (UNESCO)', 'Presidential Palace', 'Fort Zeelandia'],
			self::SA => ['Groningen fort', 'Saramacca River'],
			self::SI => ['Central Suriname Nature Reserve (UNESCO)', 'Voltzberg, Raleighvallen', 'Tafelberg', 'Kasikasima'],
			self::WA => ['Jules Wijdenbosch Bridge', 'Lelydorp', 'Hindu temples'],
		};
	}

	// Get UNESCO World Heritage Sites in the district
	public function unescoSites(): array
	{
		return match ($this) {
			self::PM => ['Historic Inner City of Paramaribo'],
			self::SI => ['Central Suriname Nature Reserve'],
			default => [],
		};
	}

	// Get whether district has significant gold mining activity
	public function hasGoldMining(): bool
	{
		return match ($this) {
			self::BR, self::SI, self::PA, self::MA => true,
			default => false,
		};
	}

	// Get whether district has significant bauxite mining activity
	public function hasBauxiteMining(): bool
	{
		return match ($this) {
			self::BR, self::PA => true, // Brokopondo has bauxite/alumina industry
			default => false,
		};
	}

	// Get whether district is part of the "plantation belt"
	public function isPlantationBelt(): bool
	{
		return match ($this) {
			self::CM, self::SA, self::WA => true, // Former plantation districts
			default => false,
		};
	}
}
