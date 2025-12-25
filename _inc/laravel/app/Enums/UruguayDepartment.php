<?php

namespace App\Enums;

enum UruguayDepartment: string
{
	case AR = 'AR';  // Artigas
	case CA = 'CA';  // Canelones
	case CL = 'CL';  // Cerro Largo
	case CO = 'CO';  // Colonia
	case DU = 'DU';  // Durazno
	case FS = 'FS';  // Flores
	case FD = 'FD';  // Florida
	case LA = 'LA';  // Lavalleja
	case MA = 'MA';  // Maldonado
	case MO = 'MO';  // Montevideo
	case PA = 'PA';  // Paysandú
	case RN = 'RN';  // Río Negro
	case RV = 'RV';  // Rivera
	case RO = 'RO';  // Rocha
	case SA = 'SA';  // Salto
	case SJ = 'SJ';  // San José
	case SO = 'SO';  // Soriano
	case TA = 'TA';  // Tacuarembó
	case TT = 'TT';  // Treinta y Tres

	// Uruguay area codes (códigos de área) by department
	public const AREA_CODES = [
		2,           // Montevideo and metropolitan area
		32,
		33,      // Canelones
		34,
		35,      // Maldonado, Rocha
		36,
		37,      // Lavalleja, Florida
		38,
		39,      // Durazno, Flores
		42,
		43,      // Colonia, Soriano
		44,
		45,      // Río Negro, Paysandú
		46,
		47,      // Salto, Artigas
		48,
		49,      // Rivera, Tacuarembó
		52,
		53,      // Cerro Largo, Treinta y Tres
		55,
		56,      // San José, Flores (some areas)
		58,
		59,      // Various departments
		62,
		63,      // Colonia, Soriano
		64,
		65,      // Río Negro, Paysandú
		66,
		67,      // Salto, Artigas
		68,
		69,      // Rivera, Tacuarembó
		72,
		73,      // Cerro Largo, Treinta y Tres
		74,
		75,      // San José, Flores (some areas)
		76,
		77,      // Various departments
		78,
		79,      // Various departments
		92,
		93,      // Montevideo mobile
		94,
		95,      // Montevideo mobile
		96,
		97,      // Interior mobile
		98,
		99,      // Interior mobile
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
			'ARTIGAS'                     => self::AR,
			'CANELONES'                   => self::CA,
			'CERRO LARGO'                 => self::CL,
			'COLONIA'                     => self::CO,
			'DURAZNO'                     => self::DU,
			'FLORES'                      => self::FS,
			'FLORIDA'                     => self::FD,
			'LAVALLEJA'                   => self::LA,
			'JOSE PEDRO VARELA'           => self::LA, // Capital of Lavalleja
			'MALDONADO'                   => self::MA,
			'MONTEVIDEO'                  => self::MO,
			'PAYSANDU'                    => self::PA,
			'PAYSANDÚ'                    => self::PA,
			'RIO NEGRO'                   => self::RN,
			'RÍO NEGRO'                   => self::RN,
			'RIVERA'                      => self::RV,
			'ROCHA'                       => self::RO,
			'SALTO'                       => self::SA,
			'SAN JOSE'                    => self::SJ,
			'SAN JOSÉ'                    => self::SJ,
			'SORIANO'                     => self::SO,
			'TACUAREMBO'                  => self::TA,
			'TACUAREMBÓ'                  => self::TA,
			'TREINTA Y TRES'              => self::TT,
			'33'                          => self::TT, // Alternative name
			'TREINTA Y TRES DEPARTAMENTO' => self::TT,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::AR => 'Artigas',
			self::CA => 'Canelones',
			self::CL => 'Cerro Largo',
			self::CO => 'Colonia',
			self::DU => 'Durazno',
			self::FS => 'Flores',
			self::FD => 'Florida',
			self::LA => 'Lavalleja',
			self::MA => 'Maldonado',
			self::MO => 'Montevideo',
			self::PA => 'Paysandú',
			self::RN => 'Río Negro',
			self::RV => 'Rivera',
			self::RO => 'Rocha',
			self::SA => 'Salto',
			self::SJ => 'San José',
			self::SO => 'Soriano',
			self::TA => 'Tacuarembó',
			self::TT => 'Treinta y Tres',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::AR => 'Artigas',
			self::CA => 'Canelones',
			self::CL => 'Cerro Largo',
			self::CO => 'Colonia',
			self::DU => 'Durazno',
			self::FS => 'Flores',
			self::FD => 'Florida',
			self::LA => 'Lavalleja',
			self::MA => 'Maldonado',
			self::MO => 'Montevideo',
			self::PA => 'Paysandú',
			self::RN => 'Río Negro',
			self::RV => 'Rivera',
			self::RO => 'Rocha',
			self::SA => 'Salto',
			self::SJ => 'San José',
			self::SO => 'Soriano',
			self::TA => 'Tacuarembó',
			self::TT => 'Treinta y Tres',
		};
	}

	// Get capital cities of each department
	public function capital(): string
	{
		return match ($this) {
			self::AR => 'Artigas',
			self::CA => 'Canelones',
			self::CL => 'Melo',
			self::CO => 'Colonia del Sacramento',
			self::DU => 'Durazno',
			self::FS => 'Trinidad',
			self::FD => 'Florida',
			self::LA => 'Minas',
			self::MA => 'Maldonado',
			self::MO => 'Montevideo',
			self::PA => 'Paysandú',
			self::RN => 'Fray Bentos',
			self::RV => 'Rivera',
			self::RO => 'Rocha',
			self::SA => 'Salto',
			self::SJ => 'San José de Mayo',
			self::SO => 'Mercedes',
			self::TA => 'Tacuarembó',
			self::TT => 'Treinta y Tres',
		};
	}

	// Get ISO 3166-2:UY code (full code including country prefix)
	public function isoCode(): string
	{
		return "UY-{$this->value}";
	}

	// Get region (Uruguay is sometimes divided into regions)
	public function region(): string
	{
		return match ($this) {
			self::MO, self::CA => 'Metropolitana',
			self::MA, self::RO => 'Este',
			self::CO, self::SJ, self::SO, self::FS, self::FD, self::DU, self::LA => 'Centro Sur',
			self::PA, self::RN, self::AR, self::SA, self::TA, self::RV, self::CL, self::TT => 'Norte',
		};
	}

	// Get area code prefix for the department (main ones)
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::MO => '2',
			self::CA => '32',
			self::MA => '42',
			self::RO => '47',
			self::CO => '52',
			self::SJ => '34',
			self::SO => '53',
			self::FS => '36',
			self::FD => '35',
			self::DU => '36',
			self::LA => '44',
			self::PA => '72',
			self::RN => '56',
			self::AR => '77',
			self::SA => '73',
			self::TA => '63',
			self::RV => '62',
			self::CL => '64',
			self::TT => '45',
		};
	}

	// Get department by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		$map = [
			2 => self::MO,
			32 => self::CA,
			33 => self::CA,
			42 => self::MA,
			43 => self::MA,
			47 => self::RO,
			52 => self::CO,
			53 => self::CO,
			34 => self::SJ,
			35 => self::FD,
			36 => self::FS,
			44 => self::LA,
			72 => self::PA,
			56 => self::RN,
			77 => self::AR,
			73 => self::SA,
			63 => self::TA,
			62 => self::RV,
			64 => self::CL,
			45 => self::TT,
		];

		return $map[$code] ?? null;
	}
}
