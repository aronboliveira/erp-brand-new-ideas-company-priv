<?php

namespace App\Enums;

enum ArgentinaProvince: string
{
	case C = 'C';  // Ciudad Autónoma de Buenos Aires
	case B = 'B';  // Buenos Aires
	case K = 'K';  // Catamarca
	case H = 'H';  // Chaco
	case U = 'U';  // Chubut
	case X = 'X';  // Córdoba
	case W = 'W';  // Corrientes
	case E = 'E';  // Entre Ríos
	case P = 'P';  // Formosa
	case Y = 'Y';  // Jujuy
	case L = 'L';  // La Pampa
	case F = 'F';  // La Rioja
	case M = 'M';  // Mendoza
	case N = 'N';  // Misiones
	case Q = 'Q';  // Neuquén
	case R = 'R';  // Río Negro
	case A = 'A';  // Salta
	case J = 'J';  // San Juan
	case D = 'D';  // San Luis
	case Z = 'Z';  // Santa Cruz
	case S = 'S';  // Santa Fe
	case G = 'G';  // Santiago del Estero
	case V = 'V';  // Tierra del Fuego, Antártida e Islas del Atlántico Sur
	case T = 'T';  // Tucumán

	// Argentina area codes (códigos de área) by province
	public const AREA_CODES = [
		11,          // Buenos Aires (CABA and Greater BA)
		220,
		221,    // Buenos Aires Province
		223,
		224,    // Buenos Aires Province (Mar del Plata, La Plata)
		226,
		228,    // Buenos Aires Province
		229,
		230,    // Buenos Aires Province
		231,
		234,    // Buenos Aires Province
		235,
		236,    // Buenos Aires Province
		237,
		239,    // Buenos Aires Province
		247,
		249,    // Buenos Aires Province
		260,
		261,    // Mendoza
		262,
		263,    // Mendoza, Neuquén
		264,
		265,    // San Juan, San Luis
		266,
		280,    // Santa Fe, Río Negro
		281,
		290,    // Buenos Aires Province (Bahía Blanca), Santa Cruz
		291,
		292,    // Buenos Aires Province (Bahía Blanca), Chubut
		293,
		294,    // Buenos Aires Province, Río Negro
		295,
		296,    // Chubut, Río Negro
		297,
		298,    // Chubut, Neuquén
		299,
		336,    // Neuquén, Entre Ríos
		338,
		340,    // Entre Ríos, Santa Fe
		341,
		342,    // Santa Fe
		343,
		345,    // Entre Ríos, Corrientes
		348,
		349,    // Santa Fe, Buenos Aires Province
		351,
		352,    // Córdoba
		353,
		354,    // Córdoba
		356,
		357,    // Córdoba, Santa Fe
		358,
		362,    // Córdoba, Chaco
		364,
		370,    // Formosa, Chaco
		371,
		372,    // Jujuy, Salta
		373,
		375,    // Tucumán, Salta
		376,
		377,    // Misiones
		378,
		379,    // Corrientes
		380,
		381,    // La Rioja, Tucumán
		382,
		383,    // Catamarca, Santiago del Estero
		384,
		385,    // La Rioja, Catamarca
		386,
		387,    // Santiago del Estero, Salta
		388,
		389,    // Jujuy, Salta
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
			'CIUDAD AUTONOMA DE BUENOS AIRES' => self::C,
			'CIUDAD AUTÓNOMA DE BUENOS AIRES' => self::C,
			'CIUDAD DE BUENOS AIRES'          => self::C,
			'CABA'                            => self::C,
			'BUENOS AIRES'                    => self::B,
			'BS AS'                           => self::B,
			'BS.AS.'                          => self::B,
			'PROVINCIA DE BUENOS AIRES'       => self::B,
			'CATAMARCA'                       => self::K,
			'CHACO'                           => self::H,
			'CHUBUT'                          => self::U,
			'CORDOBA'                         => self::X,
			'CÓRDOBA'                         => self::X,
			'CORRIENTES'                      => self::W,
			'ENTRE RIOS'                      => self::E,
			'ENTRE RÍOS'                      => self::E,
			'FORMOSA'                         => self::P,
			'JUJUY'                           => self::Y,
			'LA PAMPA'                        => self::L,
			'LA RIOJA'                        => self::F,
			'MENDOZA'                         => self::M,
			'MISIONES'                        => self::N,
			'NEUQUEN'                         => self::Q,
			'NEUQUÉN'                         => self::Q,
			'RIO NEGRO'                       => self::R,
			'RÍO NEGRO'                       => self::R,
			'SALTA'                           => self::A,
			'SAN JUAN'                        => self::J,
			'SAN LUIS'                        => self::D,
			'SANTA CRUZ'                      => self::Z,
			'SANTA FE'                        => self::S,
			'SANTIAGO DEL ESTERO'             => self::G,
			'TIERRA DEL FUEGO'                => self::V,
			'TIERRA DEL FUEGO, ANTARTIDA E ISLAS DEL ATLANTICO SUR' => self::V,
			'TIERRA DEL FUEGO, ANTÁRTIDA E ISLAS DEL ATLÁNTICO SUR' => self::V,
			'TUCUMAN'                         => self::T,
			'TUCUMÁN'                         => self::T,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::C => 'Ciudad Autónoma de Buenos Aires',
			self::B => 'Buenos Aires',
			self::K => 'Catamarca',
			self::H => 'Chaco',
			self::U => 'Chubut',
			self::X => 'Córdoba',
			self::W => 'Corrientes',
			self::E => 'Entre Ríos',
			self::P => 'Formosa',
			self::Y => 'Jujuy',
			self::L => 'La Pampa',
			self::F => 'La Rioja',
			self::M => 'Mendoza',
			self::N => 'Misiones',
			self::Q => 'Neuquén',
			self::R => 'Río Negro',
			self::A => 'Salta',
			self::J => 'San Juan',
			self::D => 'San Luis',
			self::Z => 'Santa Cruz',
			self::S => 'Santa Fe',
			self::G => 'Santiago del Estero',
			self::V => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
			self::T => 'Tucumán',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::C => 'Autonomous City of Buenos Aires',
			self::B => 'Buenos Aires',
			self::K => 'Catamarca',
			self::H => 'Chaco',
			self::U => 'Chubut',
			self::X => 'Córdoba',
			self::W => 'Corrientes',
			self::E => 'Entre Ríos',
			self::P => 'Formosa',
			self::Y => 'Jujuy',
			self::L => 'La Pampa',
			self::F => 'La Rioja',
			self::M => 'Mendoza',
			self::N => 'Misiones',
			self::Q => 'Neuquén',
			self::R => 'Río Negro',
			self::A => 'Salta',
			self::J => 'San Juan',
			self::D => 'San Luis',
			self::Z => 'Santa Cruz',
			self::S => 'Santa Fe',
			self::G => 'Santiago del Estero',
			self::V => 'Tierra del Fuego, Antarctica and South Atlantic Islands',
			self::T => 'Tucumán',
		};
	}

	// Optional: Get capital cities
	public function capital(): string
	{
		return match ($this) {
			self::C => 'Ciudad de Buenos Aires',
			self::B => 'La Plata',
			self::K => 'San Fernando del Valle de Catamarca',
			self::H => 'Resistencia',
			self::U => 'Rawson',
			self::X => 'Córdoba',
			self::W => 'Corrientes',
			self::E => 'Paraná',
			self::P => 'Formosa',
			self::Y => 'San Salvador de Jujuy',
			self::L => 'Santa Rosa',
			self::F => 'La Rioja',
			self::M => 'Mendoza',
			self::N => 'Posadas',
			self::Q => 'Neuquén',
			self::R => 'Viedma',
			self::A => 'Salta',
			self::J => 'San Juan',
			self::D => 'San Luis',
			self::Z => 'Río Gallegos',
			self::S => 'Santa Fe',
			self::G => 'Santiago del Estero',
			self::V => 'Ushuaia',
			self::T => 'San Miguel de Tucumán',
		};
	}

	public function region(): string
	{
		return match ($this) {
			self::C, self::B => 'Centro',
			self::K, self::T, self::S, self::E, self::F, self::J, self::L, self::M, self::N, self::X, self::W, self::A, self::G, self::D, self::Y => 'Norte',
			self::H, self::P, self::U, self::Q, self::R, self::Z, self::V => 'Sur',
		};
	}
}
