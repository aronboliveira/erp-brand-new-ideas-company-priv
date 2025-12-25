<?php

namespace App\Enums;

enum ChileRegion: string
{
	// Regions of Chile (from north to south)
	case AP = 'AP';  // Arica y Parinacota
	case TA = 'TA';  // Tarapacá
	case AN = 'AN';  // Antofagasta
	case AT = 'AT';  // Atacama
	case CO = 'CO';  // Coquimbo
	case VS = 'VS';  // Valparaíso
	case RM = 'RM';  // Región Metropolitana de Santiago
	case LI = 'LI';  // Libertador General Bernardo O'Higgins
	case ML = 'ML';  // Maule
	case NB = 'NB';  // Ñuble
	case BI = 'BI';  // Biobío
	case AR = 'AR';  // La Araucanía
	case LR = 'LR';  // Los Ríos
	case LL = 'LL';  // Los Lagos
	case AI = 'AI';  // Aysén del General Carlos Ibáñez del Campo
	case MA = 'MA';  // Magallanes y de la Antártica Chilena

	// Chile area codes (códigos de área) by region
	public const AREA_CODES = [
		2,           // Santiago (Metropolitan Region)
		32,
		33,      // Valparaíso
		34,
		35,      // Valparaíso (Viña del Mar, Quillota)
		41,
		42,      // Concepción (Biobío)
		43,
		44,      // Biobío, Araucanía
		45,
		46,      // Temuco (Araucanía)
		51,
		52,      // Coquimbo, Atacama
		53,
		54,      // Coquimbo, Antofagasta
		55,
		56,      // Antofagasta, Tarapacá
		57,
		58,      // Tarapacá, Arica
		61,
		62,      // Magallanes, Aysén
		63,
		64,      // Los Lagos, Los Ríos
		65,
		66,      // Los Lagos, Los Ríos
		67,
		68,      // Aysén, Magallanes
		71,
		72,      // Maule, O'Higgins
		73,
		74,      // Maule, O'Higgins
		75,
		76,      // Ñuble, Biobío
		77,
		78,      // Ñuble, Biobío
		79,
		81,      // Mobile numbers
		82,
		83,      // Mobile numbers
		84,
		85,      // Mobile numbers
		86,
		87,      // Mobile numbers
		88,
		89,      // Mobile numbers
		91,
		92,      // Mobile numbers
		93,
		94,      // Mobile numbers
		95,
		96,      // Mobile numbers
		97,
		98,      // Mobile numbers
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
			// Regions
			'ARICA Y PARINACOTA'         => self::AP,
			'ARICA Y PARINACOTA REGION'  => self::AP,
			'TARAPACA'                   => self::TA,
			'TARAPACÁ'                   => self::TA,
			'ANTOFAGASTA'                => self::AN,
			'ATACAMA'                    => self::AT,
			'COQUIMBO'                   => self::CO,
			'VALPARAISO'                 => self::VS,
			'VALPARAÍSO'                 => self::VS,
			'V REGION'                   => self::VS,
			'REGION METROPOLITANA'       => self::RM,
			'REGION METROPOLITANA DE SANTIAGO' => self::RM,
			'METROPOLITANA'              => self::RM,
			'SANTIAGO'                   => self::RM,
			'LIBERTADOR GENERAL BERNARDO O HIGGINS' => self::LI,
			'O HIGGINS'                  => self::LI,
			'OHIGGINS'                   => self::LI,
			'MAULE'                      => self::ML,
			'NUBLE'                      => self::NB,
			'ÑUBLE'                      => self::NB,
			'BIOBIO'                     => self::BI,
			'BIÓBÍO'                     => self::BI,
			'BÍO-BÍO'                    => self::BI,
			'LA ARAUCANIA'               => self::AR,
			'LA ARAUCANÍA'               => self::AR,
			'ARAUCANIA'                  => self::AR,
			'ARAUCANÍA'                  => self::AR,
			'LOS RIOS'                   => self::LR,
			'LOS RÍOS'                   => self::LR,
			'LOS LAGOS'                  => self::LL,
			'AYSEN'                      => self::AI,
			'AYSÉN'                      => self::AI,
			'AYSEN DEL GENERAL CARLOS IBANEZ DEL CAMPO' => self::AI,
			'AYSÉN DEL GENERAL CARLOS IBÁÑEZ DEL CAMPO' => self::AI,
			'MAGALLANES'                 => self::MA,
			'MAGALLANES Y DE LA ANTARTICA CHILENA' => self::MA,
			'MAGALLANES Y DE LA ANTÁRTICA CHILENA' => self::MA,
			'MAGALLANES Y ANTARTICA'     => self::MA,
			'MAGALLANES Y ANTÁRTICA'     => self::MA,
			// Roman numerals (historical)
			'XV'                         => self::AP, // Arica y Parinacota
			'I'                          => self::TA, // Tarapacá
			'II'                         => self::AN, // Antofagasta
			'III'                        => self::AT, // Atacama
			'IV'                         => self::CO, // Coquimbo
			'V'                          => self::VS, // Valparaíso
			'RM'                         => self::RM, // Metropolitana
			'VI'                         => self::LI, // O'Higgins
			'VII'                        => self::ML, // Maule
			'XVI'                        => self::NB, // Ñuble
			'VIII'                       => self::BI, // Biobío
			'IX'                         => self::AR, // La Araucanía
			'XIV'                        => self::LR, // Los Ríos
			'X'                          => self::LL, // Los Lagos
			'XI'                         => self::AI, // Aysén
			'XII'                        => self::MA, // Magallanes
			'XIII'                       => self::RM, // Sometimes used for Metropolitan
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::AP => 'Arica y Parinacota',
			self::TA => 'Tarapacá',
			self::AN => 'Antofagasta',
			self::AT => 'Atacama',
			self::CO => 'Coquimbo',
			self::VS => 'Valparaíso',
			self::RM => 'Región Metropolitana de Santiago',
			self::LI => 'Libertador General Bernardo O\'Higgins',
			self::ML => 'Maule',
			self::NB => 'Ñuble',
			self::BI => 'Biobío',
			self::AR => 'La Araucanía',
			self::LR => 'Los Ríos',
			self::LL => 'Los Lagos',
			self::AI => 'Aysén del General Carlos Ibáñez del Campo',
			self::MA => 'Magallanes y de la Antártica Chilena',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::AP => 'Arica and Parinacota',
			self::TA => 'Tarapacá',
			self::AN => 'Antofagasta',
			self::AT => 'Atacama',
			self::CO => 'Coquimbo',
			self::VS => 'Valparaíso',
			self::RM => 'Santiago Metropolitan Region',
			self::LI => 'O\'Higgins',
			self::ML => 'Maule',
			self::NB => 'Ñuble',
			self::BI => 'Biobío',
			self::AR => 'La Araucanía',
			self::LR => 'Los Ríos',
			self::LL => 'Los Lagos',
			self::AI => 'Aysén',
			self::MA => 'Magallanes and Chilean Antarctica',
		};
	}

	// Get capital cities of each region
	public function capital(): string
	{
		return match ($this) {
			self::AP => 'Arica',
			self::TA => 'Iquique',
			self::AN => 'Antofagasta',
			self::AT => 'Copiapó',
			self::CO => 'La Serena',
			self::VS => 'Valparaíso',
			self::RM => 'Santiago',
			self::LI => 'Rancagua',
			self::ML => 'Talca',
			self::NB => 'Chillán',
			self::BI => 'Concepción',
			self::AR => 'Temuco',
			self::LR => 'Valdivia',
			self::LL => 'Puerto Montt',
			self::AI => 'Coyhaique',
			self::MA => 'Punta Arenas',
		};
	}

	// Get ISO 3166-2:CL code (full code including country prefix)
	public function isoCode(): string
	{
		return "CL-{$this->value}";
	}

	// Get region number (Roman numeral as string)
	public function romanNumeral(): string
	{
		return match ($this) {
			self::AP => 'XV',
			self::TA => 'I',
			self::AN => 'II',
			self::AT => 'III',
			self::CO => 'IV',
			self::VS => 'V',
			self::RM => 'RM', // No Roman numeral for Metropolitan Region
			self::LI => 'VI',
			self::ML => 'VII',
			self::NB => 'XVI',
			self::BI => 'VIII',
			self::AR => 'IX',
			self::LR => 'XIV',
			self::LL => 'X',
			self::AI => 'XI',
			self::MA => 'XII',
		};
	}

	// Get region number (as integer for sorting)
	public function regionNumber(): int
	{
		return match ($this) {
			self::AP => 15,
			self::TA => 1,
			self::AN => 2,
			self::AT => 3,
			self::CO => 4,
			self::VS => 5,
			self::RM => 13,
			self::LI => 6,
			self::ML => 7,
			self::NB => 16,
			self::BI => 8,
			self::AR => 9,
			self::LR => 14,
			self::LL => 10,
			self::AI => 11,
			self::MA => 12,
		};
	}

	// Get zone (Chile is divided into 5 zones)
	public function zone(): string
	{
		return match ($this) {
			self::AP, self::TA => 'Norte Grande',
			self::AN, self::AT, self::CO => 'Norte Chico',
			self::VS, self::RM, self::LI, self::ML, self::NB, self::BI => 'Zona Central',
			self::AR, self::LR, self::LL => 'Zona Sur',
			self::AI, self::MA => 'Zona Austral',
		};
	}

	// Get main area code for the region
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::AP => '58',
			self::TA => '57',
			self::AN => '55',
			self::AT => '52',
			self::CO => '51',
			self::VS => '32',
			self::RM => '2',
			self::LI => '72',
			self::ML => '71',
			self::NB => '42',
			self::BI => '41',
			self::AR => '45',
			self::LR => '63',
			self::LL => '64',
			self::AI => '67',
			self::MA => '61',
		};
	}

	// Get region by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		$map = [
			2 => self::RM,   // Santiago
			32 => self::VS,  // Valparaíso
			33 => self::VS,
			34 => self::VS,
			35 => self::VS,
			41 => self::BI,  // Concepción
			42 => self::BI,  // Also used for Ñuble
			43 => self::BI,
			44 => self::BI,
			45 => self::AR,  // Temuco
			46 => self::AR,
			51 => self::CO,  // La Serena
			52 => self::AT,  // Copiapó
			53 => self::CO,
			54 => self::AT,
			55 => self::AN,  // Antofagasta
			56 => self::AN,
			57 => self::TA,  // Iquique
			58 => self::AP,  // Arica
			61 => self::MA,  // Punta Arenas
			62 => self::MA,
			63 => self::LR,  // Valdivia
			64 => self::LL,  // Puerto Montt
			65 => self::LL,
			66 => self::LL,
			67 => self::AI,  // Coyhaique
			68 => self::AI,
			71 => self::ML,  // Talca
			72 => self::LI,  // Rancagua
			73 => self::ML,
			74 => self::ML,
			75 => self::NB,  // Chillán
			76 => self::NB,
			77 => self::BI,
			78 => self::BI,
		];

		return $map[$code] ?? null;
	}

	// Get simplified name (common usage)
	public function shortName(): string
	{
		return match ($this) {
			self::AP => 'Arica',
			self::TA => 'Tarapacá',
			self::AN => 'Antofagasta',
			self::AT => 'Atacama',
			self::CO => 'Coquimbo',
			self::VS => 'Valparaíso',
			self::RM => 'Santiago',
			self::LI => 'O\'Higgins',
			self::ML => 'Maule',
			self::NB => 'Ñuble',
			self::BI => 'Biobío',
			self::AR => 'Araucanía',
			self::LR => 'Los Ríos',
			self::LL => 'Los Lagos',
			self::AI => 'Aysén',
			self::MA => 'Magallanes',
		};
	}
}
