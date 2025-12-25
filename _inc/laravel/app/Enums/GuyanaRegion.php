<?php

namespace App\Enums;

enum GuyanaRegion: string
{
	// Guyana regions (ISO 3166-2:GY codes)
	case BA = 'BA'; // Barima-Waini
	case CU = 'CU'; // Cuyuni-Mazaruni
	case DE = 'DE'; // Demerara-Mahaica
	case EB = 'EB'; // East Berbice-Corentyne
	case ES = 'ES'; // Essequibo Islands-West Demerara
	case MA = 'MA'; // Mahaica-Berbice
	case PM = 'PM'; // Pomeroon-Supenaam
	case PT = 'PT'; // Potaro-Siparuni
	case UD = 'UD'; // Upper Demerara-Berbice
	case UT = 'UT'; // Upper Takutu-Upper Essequibo

	// Guyana area codes (códigos de área) - Guyana uses +592 country code
	// Note: Guyana's telephone system doesn't have area codes in the same way as other countries
	// The country code is +592, and mobile/landline numbers don't have regional prefixes
	public const AREA_CODES = [
		// Mobile prefixes (Guyana)
		'6',   // Digicel mobile
		'7',   // Guyana Telephone and Telegraph (GT&T) mobile
		// Landline numbers (mostly in Georgetown)
		'22',  // Georgetown and surrounding areas
		'23',  // Georgetown
		'24',  // Georgetown
		'25',  // Georgetown
		'26',  // Georgetown and East Coast
		'27',  // East Bank Demerara
		'28',  // West Coast Demerara
		'29',  // West Demerara
		'33',  // Berbice
		'34',  // Berbice
		'35',  // Berbice
		'36',  // Linden
		'37',  // Linden
		'38',  // Essequibo Coast
		'39',  // Essequibo Coast
		'44',  // New Amsterdam
		'45',  // New Amsterdam
		'46',  // Corriverton
		'47',  // Corriverton
		'55',  // Lethem
		'77',  // Mobile (GT&T)
		'6',   // Mobile (Digicel) - repeated for clarity
		'7',   // Mobile (GT&T) - repeated for clarity
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
			// Regions
			'BARIMA-WAINI'                  => self::BA,
			'BARIMA WAINI'                  => self::BA,
			'REGION 1'                      => self::BA,
			'1'                             => self::BA,
			'CUYUNI-MAZARUNI'               => self::CU,
			'CUYUNI MAZARUNI'               => self::CU,
			'REGION 7'                      => self::CU,
			'7'                             => self::CU,
			'DEMERARA-MAHAICA'              => self::DE,
			'DEMERARA MAHAICA'              => self::DE,
			'REGION 4'                      => self::DE,
			'4'                             => self::DE,
			'EAST BERBICE-CORENTYNE'        => self::EB,
			'EAST BERBICE CORENTYNE'        => self::EB,
			'REGION 6'                      => self::EB,
			'6'                             => self::EB,
			'ESSEQUIBO ISLANDS-WEST DEMERARA' => self::ES,
			'ESSEQUIBO ISLANDS WEST DEMERARA' => self::ES,
			'REGION 3'                      => self::ES,
			'3'                             => self::ES,
			'MAHAICA-BERBICE'               => self::MA,
			'MAHAICA BERBICE'               => self::MA,
			'REGION 5'                      => self::MA,
			'5'                             => self::MA,
			'POMEROON-SUPENAAM'             => self::PM,
			'POMEROON SUPENAAM'             => self::PM,
			'REGION 2'                      => self::PM,
			'2'                             => self::PM,
			'POTARO-SIPARUNI'               => self::PT,
			'POTARO SIPARUNI'               => self::PT,
			'REGION 8'                      => self::PT,
			'8'                             => self::PT,
			'UPPER DEMERARA-BERBICE'        => self::UD,
			'UPPER DEMERARA BERBICE'        => self::UD,
			'REGION 10'                     => self::UD,
			'10'                            => self::UD,
			'UPPER TAKUTU-UPPER ESSEQUIBO'  => self::UT,
			'UPPER TAKUTU UPPER ESSEQUIBO'  => self::UT,
			'REGION 9'                      => self::UT,
			'9'                             => self::UT,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::BA => 'Barima-Waini',
			self::CU => 'Cuyuni-Mazaruni',
			self::DE => 'Demerara-Mahaica',
			self::EB => 'East Berbice-Corentyne',
			self::ES => 'Essequibo Islands-West Demerara',
			self::MA => 'Mahaica-Berbice',
			self::PM => 'Pomeroon-Supenaam',
			self::PT => 'Potaro-Siparuni',
			self::UD => 'Upper Demerara-Berbice',
			self::UT => 'Upper Takutu-Upper Essequibo',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::BA => 'Barima-Waini',
			self::CU => 'Cuyuni-Mazaruni',
			self::DE => 'Demerara-Mahaica',
			self::EB => 'East Berbice-Corentyne',
			self::ES => 'Essequibo Islands-West Demerara',
			self::MA => 'Mahaica-Berbice',
			self::PM => 'Pomeroon-Supenaam',
			self::PT => 'Potaro-Siparuni',
			self::UD => 'Upper Demerara-Berbice',
			self::UT => 'Upper Takutu-Upper Essequibo',
		};
	}

	// Get capital/administrative center of each region
	public function capital(): string
	{
		return match ($this) {
			self::BA => 'Mabaruma',
			self::CU => 'Bartica',
			self::DE => 'Georgetown', // Note: Georgetown is also national capital
			self::EB => 'New Amsterdam',
			self::ES => 'Vreed-en-Hoop',
			self::MA => 'Fort Wellington',
			self::PM => 'Anna Regina',
			self::PT => 'Mahdia',
			self::UD => 'Linden',
			self::UT => 'Lethem',
		};
	}

	// Get ISO 3166-2:GY code (full code including country prefix)
	public function isoCode(): string
	{
		return "GY-{$this->value}";
	}

	// Get region number (Guyana regions are numbered 1-10)
	public function regionNumber(): int
	{
		return match ($this) {
			self::BA => 1,
			self::PM => 2,
			self::ES => 3,
			self::DE => 4,
			self::MA => 5,
			self::EB => 6,
			self::CU => 7,
			self::PT => 8,
			self::UT => 9,
			self::UD => 10,
		};
	}

	// Get geographic zone
	public function zone(): string
	{
		return match ($this) {
			self::BA, self::PM, self::ES, self::DE => 'Coastal',
			self::MA, self::EB => 'Coastal/Berbice',
			self::CU, self::PT, self::UT => 'Interior',
			self::UD => 'Interior/Bauxite',
		};
	}

	// Get approximate area in square kilometers
	public function area(): int
	{
		return match ($this) {
			self::BA => 20339,
			self::CU => 47213,
			self::DE => 2233,
			self::EB => 36255,
			self::ES => 3755,
			self::MA => 4170,
			self::PM => 6195,
			self::PT => 20051,
			self::UD => 17481,
			self::UT => 57590,
		};
	}

	// Get population rank (approximate)
	public function populationRank(): int
	{
		return match ($this) {
			self::DE => 1,  // Most populous (includes Georgetown)
			self::ES => 2,
			self::EB => 3,
			self::UD => 4,
			self::MA => 5,
			self::PM => 6,
			self::CU => 7,
			self::BA => 8,
			self::UT => 9,
			self::PT => 10, // Least populous
		};
	}

	// Get area rank (from largest to smallest)
	public function areaRank(): int
	{
		return match ($this) {
			self::UT => 1,  // Largest
			self::CU => 2,
			self::EB => 3,
			self::PT => 4,
			self::BA => 5,
			self::UD => 6,
			self::PM => 7,
			self::MA => 8,
			self::ES => 9,
			self::DE => 10, // Smallest
		};
	}

	// Get whether region is coastal
	public function isCoastal(): bool
	{
		return match ($this) {
			self::BA, self::PM, self::ES, self::DE, self::MA, self::EB => true,
			self::CU, self::PT, self::UD, self::UT => false,
		};
	}

	// Get whether region is in the interior (hinterland)
	public function isInterior(): bool
	{
		return !$this->isCoastal();
	}

	// Get main economic activity
	public function majorEconomicActivity(): string
	{
		return match ($this) {
			self::BA => 'Mining (gold, diamonds), Logging, Agriculture',
			self::CU => 'Mining (gold, diamonds), Forestry',
			self::DE => 'Government, Services, Commerce, Industry',
			self::EB => 'Agriculture (rice, sugar), Fishing',
			self::ES => 'Agriculture, Fishing, Services',
			self::MA => 'Agriculture (rice), Fishing',
			self::PM => 'Agriculture (rice), Fishing, Coconut',
			self::PT => 'Mining (gold, diamonds), Tourism (Kaieteur Falls)',
			self::UD => 'Mining (bauxite), Forestry',
			self::UT => 'Cattle Ranching, Mining, Agriculture',
		};
	}

	// Get border countries (Guyana borders Venezuela, Brazil, and Suriname)
	public function borders(): array
	{
		return match ($this) {
			self::BA => ['Venezuela'],
			self::CU => ['Venezuela', 'Brazil'],
			self::EB => ['Suriname'],
			self::UT => ['Brazil'],
			self::PT => ['Brazil'],
			default => [],
		};
	}

	// Get whether region is disputed (Venezuela claims most of Guyana west of the Essequibo River)
	public function isDisputed(): bool
	{
		return match ($this) {
			self::BA, self::CU, self::PT, self::UT => true, // These regions are in the Essequibo area claimed by Venezuela
			default => false,
		};
	}

	// Get notable landmarks or features
	public function notableFeatures(): array
	{
		return match ($this) {
			self::BA => ['Mabaruma Settlement', 'Barima River'],
			self::CU => ['Bartica (gateway to the interior)', 'Mazaruni River'],
			self::DE => ['Georgetown (capital)', 'Demerara River', 'Botanical Gardens'],
			self::EB => ['New Amsterdam', 'Corentyne River', 'Berbice River'],
			self::ES => ['Essequibo River (largest river)', 'Leguan Island', 'Wakenaam Island'],
			self::MA => ['Mahaicony River', 'Abary River'],
			self::PM => ['Pomeroon River', 'Supenaam River', 'Anna Regina'],
			self::PT => ['Kaieteur Falls', 'Potaro River', 'Iwokrama Forest'],
			self::UD => ['Linden (mining town)', 'Demerara River', 'Bauxite mines'],
			self::UT => ['Lethem (border town)', 'Rupununi Savannah', 'Takutu River'],
		};
	}

	// Get indigenous territories or presence
	public function indigenousGroups(): array
	{
		return match ($this) {
			self::BA => ['Arawak', 'Carib', 'Warao'],
			self::CU => ['Akawaio', 'Patamona', 'Makushi'],
			self::DE => [], // Mostly urban
			self::EB => ['East Indian (Indo-Guyanese) majority'],
			self::ES => ['Mixed population'],
			self::MA => ['Mixed population'],
			self::PM => ['Mixed population with indigenous communities'],
			self::PT => ['Patamona', 'Makushi'],
			self::UD => ['Mixed with indigenous communities'],
			self::UT => ['Wapishana', 'Makushi', 'Wai-Wai'],
		};
	}

	// Get region from phone number prefix (simplified)
	public static function fromPhonePrefix(string $prefix): ?self
	{
		// Note: This is approximate as Guyana's phone system doesn't strictly map to regions
		$map = [
			'22' => self::DE, // Georgetown
			'23' => self::DE,
			'24' => self::DE,
			'25' => self::DE,
			'26' => self::ES, // East Coast
			'27' => self::ES, // East Bank
			'28' => self::ES, // West Coast
			'29' => self::ES, // West Demerara
			'33' => self::EB, // Berbice
			'34' => self::EB,
			'35' => self::EB,
			'36' => self::UD, // Linden
			'37' => self::UD,
			'38' => self::PM, // Essequibo Coast
			'39' => self::PM,
			'44' => self::EB, // New Amsterdam
			'45' => self::EB,
			'46' => self::EB, // Corriverton
			'47' => self::EB,
			'55' => self::UT, // Lethem
		];

		return $map[$prefix] ?? null;
	}
}
