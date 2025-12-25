<?php

namespace App\Enums;

enum ParaguayDepartment: string
{
	// Capital district
	case AS = 'AS';  // Asunción (Capital District)
		// Departments
	case AG = 'AG';  // Alto Paraguay
	case AA = 'AA';  // Alto Paraná
	case AM = 'AM';  // Amambay
	case BQ = 'BQ';  // Boquerón
	case CG = 'CG';  // Caaguazú
	case CZ = 'CZ';  // Caazapá
	case CN = 'CN';  // Canindeyú
	case CE = 'CE';  // Central
	case CC = 'CC';  // Concepción
	case CD = 'CD';  // Cordillera
	case GU = 'GU';  // Guairá
	case IT = 'IT';  // Itapúa
	case MI = 'MI';  // Misiones
	case NE = 'NE';  // Ñeembucú
	case PG = 'PG';  // Paraguarí
	case PH = 'PH';  // Presidente Hayes
	case SP = 'SP';  // San Pedro

	// Paraguay area codes (códigos de área) by department
	public const AREA_CODES = [
		21,          // Asunción and Central Department
		28,
		29,      // Central Department
		31,
		32,      // Concepción, San Pedro
		33,
		34,      // Concepción, Amambay
		35,
		36,      // San Pedro, Caaguazú
		37,
		38,      // Caaguazú, Canindeyú
		39,
		41,      // Canindeyú, Cordillera
		42,
		43,      // Cordillera, Guairá
		44,
		45,      // Guairá, Caazapá
		46,
		47,      // Caazapá, Itapúa
		48,
		49,      // Itapúa, Misiones
		51,
		52,      // Paraguarí, Alto Paraná
		53,
		54,      // Alto Paraná
		55,
		56,      // Alto Paraná, Central
		61,
		62,      // Alto Paraná
		63,
		64,      // Alto Paraná, Canindeyú
		65,
		66,      // Canindeyú, Amambay
		67,
		68,      // Amambay, Boquerón
		69,
		71,      // Boquerón, Presidente Hayes
		72,
		73,      // Presidente Hayes, Alto Paraguay
		74,
		75,      // Alto Paraguay, Ñeembucú
		76,
		77,      // Ñeembucú
		78,
		79,      // Misiones, Ñeembucú
		81,
		82,      // Mobile numbers
		83,
		84,      // Mobile numbers
		85,
		86,      // Mobile numbers
		87,
		88,      // Mobile numbers
		89,
		91,      // Mobile numbers
		92,
		93,      // Mobile numbers
		94,
		95,      // Mobile numbers
		96,
		97,      // Mobile numbers
		98,
		99,      // Mobile numbers
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
			// Capital District
			'ASUNCION'                     => self::AS,
			'ASUNCIÓN'                     => self::AS,
			'CAPITAL'                      => self::AS,
			'CAPITAL DISTRICT'             => self::AS,
			'DISTRITO CAPITAL'             => self::AS,
			// Departments
			'ALTO PARAGUAY'                => self::AG,
			'ALTO PARANA'                  => self::AA,
			'ALTO PARANÁ'                  => self::AA,
			'AMAMBAY'                      => self::AM,
			'BOQUERON'                     => self::BQ,
			'BOQUERÓN'                     => self::BQ,
			'CAAGUAZU'                     => self::CG,
			'CAAGUAZÚ'                     => self::CG,
			'CAAZAPA'                      => self::CZ,
			'CAAZAPÁ'                      => self::CZ,
			'CANINDEYU'                    => self::CN,
			'CANINDEYÚ'                    => self::CN,
			'CENTRAL'                      => self::CE,
			'CONCEPCION'                   => self::CC,
			'CONCEPCIÓN'                   => self::CC,
			'CORDILLERA'                   => self::CD,
			'GUAIRA'                       => self::GU,
			'GUAIRÁ'                       => self::GU,
			'ITAPUA'                       => self::IT,
			'ITAPÚA'                       => self::IT,
			'MISIONES'                     => self::MI,
			'NEEMBUCU'                     => self::NE,
			'ÑEEMBUCÚ'                     => self::NE,
			'NEEMBUCÚ'                     => self::NE,
			'ÑEEMBUCU'                     => self::NE,
			'PARAGUARI'                    => self::PG,
			'PARAGUARÍ'                    => self::PG,
			'PRESIDENTE HAYES'             => self::PH,
			'SAN PEDRO'                    => self::SP,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::AS => 'Asunción',
			self::AG => 'Alto Paraguay',
			self::AA => 'Alto Paraná',
			self::AM => 'Amambay',
			self::BQ => 'Boquerón',
			self::CG => 'Caaguazú',
			self::CZ => 'Caazapá',
			self::CN => 'Canindeyú',
			self::CE => 'Central',
			self::CC => 'Concepción',
			self::CD => 'Cordillera',
			self::GU => 'Guairá',
			self::IT => 'Itapúa',
			self::MI => 'Misiones',
			self::NE => 'Ñeembucú',
			self::PG => 'Paraguarí',
			self::PH => 'Presidencia Hayes',
			self::SP => 'San Pedro',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::AS => 'Asunción',
			self::AG => 'Alto Paraguay',
			self::AA => 'Alto Paraná',
			self::AM => 'Amambay',
			self::BQ => 'Boquerón',
			self::CG => 'Caaguazú',
			self::CZ => 'Caazapá',
			self::CN => 'Canindeyú',
			self::CE => 'Central',
			self::CC => 'Concepción',
			self::CD => 'Cordillera',
			self::GU => 'Guairá',
			self::IT => 'Itapúa',
			self::MI => 'Misiones',
			self::NE => 'Ñeembucú',
			self::PG => 'Paraguarí',
			self::PH => 'Presidente Hayes',
			self::SP => 'San Pedro',
		};
	}

	// Get capital cities of each department
	public function capital(): string
	{
		return match ($this) {
			self::AS => 'Asunción',
			self::AG => 'Fuerte Olimpo',
			self::AA => 'Ciudad del Este',
			self::AM => 'Pedro Juan Caballero',
			self::BQ => 'Filadelfia',
			self::CG => 'Coronel Oviedo',
			self::CZ => 'Caazapá',
			self::CN => 'Salto del Guairá',
			self::CE => 'Areguá',
			self::CC => 'Concepción',
			self::CD => 'Caacupé',
			self::GU => 'Villarrica',
			self::IT => 'Encarnación',
			self::MI => 'San Juan Bautista',
			self::NE => 'Pilar',
			self::PG => 'Paraguarí',
			self::PH => 'Villa Hayes',
			self::SP => 'San Pedro de Ycuamandiyú',
		};
	}

	// Get ISO 3166-2:PY code (full code including country prefix)
	public function isoCode(): string
	{
		return "PY-{$this->value}";
	}

	// Get region (Paraguay is divided into two main regions)
	public function region(): string
	{
		return match ($this) {
			self::AS, self::CE, self::CD, self::GU, self::CZ, self::PG, self::NE, self::MI, self::IT, self::SP, self::CG, self::CN, self::AA => 'Región Oriental',
			self::AG, self::BQ, self::PH, self::AM, self::CC => 'Región Occidental',
		};
	}

	// Get department by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		$map = [
			// Asunción and Central
			21 => self::AS,
			28 => self::CE,
			29 => self::CE,
			// Concepción and Amambay
			31 => self::CC,
			32 => self::CC,
			33 => self::AM,
			34 => self::AM,
			// San Pedro and Caaguazú
			35 => self::SP,
			36 => self::SP,
			37 => self::CG,
			38 => self::CG,
			// Canindeyú and Cordillera
			39 => self::CN,
			41 => self::CD,
			42 => self::CD,
			// Guairá and Caazapá
			43 => self::GU,
			44 => self::GU,
			45 => self::CZ,
			46 => self::CZ,
			// Itapúa and Misiones
			47 => self::IT,
			48 => self::IT,
			49 => self::MI,
			51 => self::MI,
			// Paraguarí and Alto Paraná
			52 => self::PG,
			53 => self::PG,
			54 => self::AA,
			55 => self::AA,
			56 => self::AA,
			61 => self::AA,
			62 => self::AA,
			// More Alto Paraná and Canindeyú
			63 => self::AA,
			64 => self::CN,
			65 => self::CN,
			// Amambay and Boquerón
			66 => self::AM,
			67 => self::BQ,
			68 => self::BQ,
			// Boquerón and Presidente Hayes
			69 => self::BQ,
			71 => self::PH,
			72 => self::PH,
			// Presidente Hayes and Alto Paraguay
			73 => self::PH,
			74 => self::AG,
			75 => self::AG,
			// Ñeembucú
			76 => self::NE,
			77 => self::NE,
			78 => self::NE,
			79 => self::NE,
		];

		return $map[$code] ?? null;
	}

	// Get main area code for the department
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::AS => '21',
			self::CE => '21',
			self::CC => '31',
			self::AM => '33',
			self::SP => '35',
			self::CG => '37',
			self::CN => '39',
			self::CD => '41',
			self::GU => '43',
			self::CZ => '45',
			self::IT => '47',
			self::MI => '49',
			self::PG => '52',
			self::AA => '54',
			self::BQ => '67',
			self::PH => '71',
			self::AG => '74',
			self::NE => '76',
		};
	}

	// Get department code without accents (for systems that don't support them)
	public function codeWithoutAccent(): string
	{
		return match ($this) {
			self::AS => 'AS',
			self::AG => 'AG',
			self::AA => 'AA',
			self::AM => 'AM',
			self::BQ => 'BQ',
			self::CG => 'CG',
			self::CZ => 'CZ',
			self::CN => 'CN',
			self::CE => 'CE',
			self::CC => 'CC',
			self::CD => 'CD',
			self::GU => 'GU',
			self::IT => 'IT',
			self::MI => 'MI',
			self::NE => 'NE',
			self::PG => 'PG',
			self::PH => 'PH',
			self::SP => 'SP',
		};
	}
}
