<?php

namespace App\Enums;

enum BoliviaDepartment: string
{
	// Bolivia departments (ISO 3166-2:BO codes)
	case H = 'H';   // Chuquisaca
	case C = 'C';   // Cochabamba
	case B = 'B';   // Beni
	case L = 'L';   // La Paz
	case O = 'O';   // Oruro
	case N = 'N';   // Pando
	case P = 'P';   // Potosí
	case S = 'S';   // Santa Cruz
	case T = 'T';   // Tarija

	// Bolivia area codes (códigos de área) by department
	public const AREA_CODES = [
		2,           // La Paz
		3,           // Cochabamba
		4,           // Santa Cruz
		46,
		47,      // Santa Cruz (additional)
		5,           // Oruro
		6,           // Potosí
		7,           // Tarija
		8,           // Chuquisaca (Sucre)
		52,
		53,      // Chuquisaca (additional)
		9,           // Mobile numbers
		22,          // Mobile numbers (Santa Cruz)
		23,          // Mobile numbers (Cochabamba)
		24,          // Mobile numbers (La Paz)
		25,          // Mobile numbers (Oruro)
		26,          // Mobile numbers (Potosí)
		27,          // Mobile numbers (Tarija)
		28,          // Mobile numbers (Chuquisaca)
		29,          // Mobile numbers (Beni, Pando)
		32,          // Mobile numbers
		33,          // Mobile numbers
		34,          // Mobile numbers
		35,          // Mobile numbers
		36,          // Mobile numbers
		37,          // Mobile numbers
		38,          // Mobile numbers
		39,          // Mobile numbers
		42,          // Mobile numbers
		43,          // Mobile numbers
		44,          // Mobile numbers
		45,          // Mobile numbers
		48,          // Mobile numbers
		49,          // Mobile numbers
		62,          // Mobile numbers
		63,          // Mobile numbers
		64,          // Mobile numbers
		65,          // Mobile numbers
		66,          // Mobile numbers
		67,          // Mobile numbers
		68,          // Mobile numbers
		69,          // Mobile numbers
		72,          // Mobile numbers
		73,          // Mobile numbers
		74,          // Mobile numbers
		75,          // Mobile numbers
		76,          // Mobile numbers
		77,          // Mobile numbers
		78,          // Mobile numbers
		79,          // Mobile numbers
		82,          // Mobile numbers
		83,          // Mobile numbers
		84,          // Mobile numbers
		85,          // Mobile numbers
		86,          // Mobile numbers
		87,          // Mobile numbers
		88,          // Mobile numbers
		89,          // Mobile numbers
		91,          // Mobile numbers
		92,          // Mobile numbers
		93,          // Mobile numbers
		94,          // Mobile numbers
		95,          // Mobile numbers
		96,          // Mobile numbers
		97,          // Mobile numbers
		98,          // Mobile numbers
		99,          // Mobile numbers
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
			// Departments
			'CHUQUISACA'                => self::H,
			'COCHABAMBA'                => self::C,
			'BENI'                      => self::B,
			'LA PAZ'                    => self::L,
			'ORURO'                     => self::O,
			'PANDO'                     => self::N,
			'POTOSI'                    => self::P,
			'POTOSÍ'                    => self::P,
			'SANTA CRUZ'                => self::S,
			'SANTA CRUZ DE LA SIERRA'   => self::S,
			'TARIJA'                    => self::T,
			// Alternative names
			'CHARCAS'                   => self::H, // Historical name for Chuquisaca
			'SUCRE'                     => self::H, // Capital city name used for department
			'VALLE'                     => self::C, // Cochabamba is in a valley
			'LLANOS'                    => self::B, // Beni is in the plains
			'ALTIPLANO'                 => self::L, // La Paz is on the altiplano
			'URU'                       => self::O, // Reference to Uru people
			'AMAZONIA'                  => self::N, // Pando is in the Amazon
			'PLATA'                     => self::P, // Potosí was famous for silver
			'CRUCEÑO'                   => self::S, // Demonym
			'CHACO'                     => self::T, // Tarija is in the Chaco region
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::H => 'Chuquisaca',
			self::C => 'Cochabamba',
			self::B => 'Beni',
			self::L => 'La Paz',
			self::O => 'Oruro',
			self::N => 'Pando',
			self::P => 'Potosí',
			self::S => 'Santa Cruz',
			self::T => 'Tarija',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::H => 'Chuquisaca',
			self::C => 'Cochabamba',
			self::B => 'Beni',
			self::L => 'La Paz',
			self::O => 'Oruro',
			self::N => 'Pando',
			self::P => 'Potosi',
			self::S => 'Santa Cruz',
			self::T => 'Tarija',
		};
	}

	// Get capital cities of each department
	public function capital(): string
	{
		return match ($this) {
			self::H => 'Sucre',
			self::C => 'Cochabamba',
			self::B => 'Trinidad',
			self::L => 'La Paz',
			self::O => 'Oruro',
			self::N => 'Cobija',
			self::P => 'Potosí',
			self::S => 'Santa Cruz de la Sierra',
			self::T => 'Tarija',
		};
	}

	// Get whether this department contains the constitutional capital (Sucre) or administrative capital (La Paz)
	public function isCapitalDepartment(): bool
	{
		return match ($this) {
			self::H => true, // Sucre (constitutional capital)
			self::L => true, // La Paz (administrative capital)
			default => false,
		};
	}

	// Get the type of capital
	public function capitalType(): ?string
	{
		return match ($this) {
			self::H => 'constitutional',
			self::L => 'administrative',
			default => null,
		};
	}

	// Get ISO 3166-2:BO code (full code including country prefix)
	public function isoCode(): string
	{
		return "BO-{$this->value}";
	}

	// Get region (Bolivia is divided into two main geographical regions)
	public function region(): string
	{
		return match ($this) {
			self::L, self::O, self::P, self::C, self::H => 'Andean Region',
			self::S, self::B, self::N, self::T => 'Lowlands',
		};
	}

	// Get sub-region (more specific geographical classification)
	public function subRegion(): string
	{
		return match ($this) {
			self::L => 'Altiplano',
			self::O => 'Altiplano',
			self::P => 'Altiplano',
			self::C => 'Valleys',
			self::H => 'Valleys',
			self::S => 'Eastern Lowlands',
			self::B => 'Llanos (Plains)',
			self::N => 'Amazon',
			self::T => 'Chaco',
		};
	}

	// Get main area code for the department
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::H => '4',   // Sucre
			self::C => '4',   // Cochabamba
			self::B => '3',   // Trinidad
			self::L => '2',   // La Paz
			self::O => '2',   // Oruro
			self::N => '3',   // Cobija
			self::P => '2',   // Potosí
			self::S => '3',   // Santa Cruz
			self::T => '4',   // Tarija
		};
	}

	// Get department by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		$map = [
			2 => self::L,   // La Paz
			3 => self::S,   // Santa Cruz (also Beni, Pando)
			4 => self::C,   // Cochabamba (also Chuquisaca, Tarija)
			5 => self::O,   // Oruro
			6 => self::P,   // Potosí
			7 => self::T,   // Tarija
			8 => self::H,   // Chuquisaca
			46 => self::S,  // Santa Cruz
			47 => self::S,  // Santa Cruz
			52 => self::H,  // Chuquisaca
			53 => self::H,  // Chuquisaca
		];

		// Handle 2-digit codes first, then 1-digit
		if (isset($map[$code])) {
			return $map[$code];
		}

		// For single-digit codes that might conflict
		$singleDigitMap = [
			2 => self::L,
			3 => self::S,
			4 => self::C,
			5 => self::O,
			6 => self::P,
			7 => self::T,
			8 => self::H,
		];

		return $singleDigitMap[$code] ?? null;
	}

	// Get the largest city (sometimes different from capital)
	public function largestCity(): string
	{
		return match ($this) {
			self::H => 'Sucre',           // Capital is largest
			self::C => 'Cochabamba',      // Capital is largest
			self::B => 'Trinidad',        // Capital is largest
			self::L => 'El Alto',         // El Alto is larger than La Paz city
			self::O => 'Oruro',           // Capital is largest
			self::N => 'Cobija',          // Capital is largest
			self::P => 'Potosí',          // Capital is largest
			self::S => 'Santa Cruz de la Sierra', // Capital is largest
			self::T => 'Tarija',          // Capital is largest
		};
	}

	// Get population rank (approximate)
	public function populationRank(): int
	{
		return match ($this) {
			self::S => 1,  // Santa Cruz - most populous
			self::L => 2,  // La Paz
			self::C => 3,  // Cochabamba
			self::P => 4,  // Potosí
			self::O => 5,  // Oruro
			self::T => 6,  // Tarija
			self::H => 7,  // Chuquisaca
			self::B => 8,  // Beni
			self::N => 9,  // Pando - least populous
		};
	}

	// Get area rank (approximate, in square km)
	public function areaRank(): int
	{
		return match ($this) {
			self::S => 1,  // Santa Cruz - largest
			self::B => 2,  // Beni
			self::L => 3,  // La Paz
			self::P => 4,  // Potosí
			self::C => 5,  // Cochabamba
			self::T => 6,  // Tarija
			self::O => 7,  // Oruro
			self::H => 8,  // Chuquisaca
			self::N => 9,  // Pando - smallest
		};
	}

	// Get elevation category
	public function elevationCategory(): string
	{
		return match ($this) {
			self::L, self::O, self::P => 'High Altitude (Altiplano)',
			self::C, self::H => 'Medium Altitude (Valleys)',
			self::S, self::T, self::B, self::N => 'Low Altitude (Lowlands)',
		};
	}

	// Get whether the department is landlocked (all are, but this is for consistency)
	public function isLandlocked(): bool
	{
		return true; // Bolivia is a landlocked country
	}

	// Get major economic activity
	public function majorEconomicActivity(): string
	{
		return match ($this) {
			self::S => 'Agriculture, Industry',
			self::L => 'Government, Services',
			self::C => 'Agriculture, Commerce',
			self::P => 'Mining',
			self::O => 'Mining',
			self::T => 'Agriculture, Gas',
			self::H => 'Government, Tourism',
			self::B => 'Cattle, Agriculture',
			self::N => 'Forestry, Brazil Nuts',
		};
	}

	// Get UNESCO World Heritage Sites in the department
	public function unescoSites(): array
	{
		return match ($this) {
			self::L => ['City of Potosí', 'Jesuit Missions of the Chiquitos'],
			self::P => ['City of Potosí'],
			self::H => ['Historic City of Sucre'],
			self::C => ['Jesuit Missions of the Chiquitos'],
			self::S => ['Jesuit Missions of the Chiquitos', 'Noel Kempff Mercado National Park'],
			self::O => ['Carnaval de Oruro'],
			default => [],
		};
	}
}
