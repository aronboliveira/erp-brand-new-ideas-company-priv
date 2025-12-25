<?php

namespace App\Enums;

enum ColombiaDepartment: string
{
	// Capital District (Bogotá)
	case DC = 'DC';  // Bogotá, D.C.
		// Departments
	case AMA = 'AMA'; // Amazonas
	case ANT = 'ANT'; // Antioquia
	case ARA = 'ARA'; // Arauca
	case ATL = 'ATL'; // Atlántico
	case BOL = 'BOL'; // Bolívar
	case BOY = 'BOY'; // Boyacá
	case CAL = 'CAL'; // Caldas
	case CAQ = 'CAQ'; // Caquetá
	case CAS = 'CAS'; // Casanare
	case CAU = 'CAU'; // Cauca
	case CES = 'CES'; // Cesar
	case CHO = 'CHO'; // Chocó
	case COR = 'COR'; // Córdoba
	case CUN = 'CUN'; // Cundinamarca
	case GUA = 'GUA'; // Guainía
	case GUV = 'GUV'; // Guaviare
	case HUI = 'HUI'; // Huila
	case LAG = 'LAG'; // La Guajira
	case MAG = 'MAG'; // Magdalena
	case MET = 'MET'; // Meta
	case NAR = 'NAR'; // Nariño
	case NSA = 'NSA'; // Norte de Santander
	case PUT = 'PUT'; // Putumayo
	case QUI = 'QUI'; // Quindío
	case RIS = 'RIS'; // Risaralda
	case SAP = 'SAP'; // San Andrés y Providencia
	case SAN = 'SAN'; // Santander
	case SUC = 'SUC'; // Sucre
	case TOL = 'TOL'; // Tolima
	case VAC = 'VAC'; // Valle del Cauca
	case VAU = 'VAU'; // Vaupés
	case VID = 'VID'; // Vichada

	// Colombia area codes (códigos de área) by department
	public const AREA_CODES = [
		1,           // Bogotá D.C.
		2,           // Cali (Valle del Cauca)
		4,           // Medellín (Antioquia)
		5,           // Bucaramanga (Santander)
		6,           // Armenia (Quindío)
		7,           // Manizales (Caldas)
		8,           // Barranquilla (Atlántico)
		9,           // Cartagena (Bolívar)
		10,          // Mobile numbers
		91,          // Mobile numbers
		92,          // Mobile numbers
		93,          // Mobile numbers
		94,          // Mobile numbers
		95,          // Mobile numbers
		96,          // Mobile numbers
		97,          // Mobile numbers
		98,          // Mobile numbers
		99,          // Mobile numbers
		200,         // Mobile numbers
		300,         // Mobile numbers
		301,         // Mobile numbers
		302,         // Mobile numbers
		303,         // Mobile numbers
		304,         // Mobile numbers
		305,         // Mobile numbers
		310,         // Mobile numbers
		311,         // Mobile numbers
		312,         // Mobile numbers
		313,         // Mobile numbers
		314,         // Mobile numbers
		315,         // Mobile numbers
		316,         // Mobile numbers
		317,         // Mobile numbers
		318,         // Mobile numbers
		319,         // Mobile numbers
		320,         // Mobile numbers
		321,         // Mobile numbers
		322,         // Mobile numbers
		323,         // Mobile numbers
		324,         // Mobile numbers
		325,         // Mobile numbers
		326,         // Mobile numbers
		327,         // Mobile numbers
		328,         // Mobile numbers
		329,         // Mobile numbers
		330,         // Mobile numbers
		331,         // Mobile numbers
		332,         // Mobile numbers
		333,         // Mobile numbers
		334,         // Mobile numbers
		335,         // Mobile numbers
		336,         // Mobile numbers
		337,         // Mobile numbers
		338,         // Mobile numbers
		339,         // Mobile numbers
		340,         // Mobile numbers
		341,         // Mobile numbers
		342,         // Mobile numbers
		343,         // Mobile numbers
		344,         // Mobile numbers
		345,         // Mobile numbers
		346,         // Mobile numbers
		347,         // Mobile numbers
		348,         // Mobile numbers
		349,         // Mobile numbers
		350,         // Mobile numbers
		351,         // Mobile numbers
		352,         // Mobile numbers
		353,         // Mobile numbers
		354,         // Mobile numbers
		355,         // Mobile numbers
		356,         // Mobile numbers
		357,         // Mobile numbers
		358,         // Mobile numbers
		359,         // Mobile numbers
		360,         // Mobile numbers
		361,         // Mobile numbers
		362,         // Mobile numbers
		363,         // Mobile numbers
		364,         // Mobile numbers
		365,         // Mobile numbers
		366,         // Mobile numbers
		367,         // Mobile numbers
		368,         // Mobile numbers
		369,         // Mobile numbers
		370,         // Mobile numbers
		371,         // Mobile numbers
		372,         // Mobile numbers
		373,         // Mobile numbers
		374,         // Mobile numbers
		375,         // Mobile numbers
		376,         // Mobile numbers
		377,         // Mobile numbers
		378,         // Mobile numbers
		379,         // Mobile numbers
		380,         // Mobile numbers
		381,         // Mobile numbers
		382,         // Mobile numbers
		383,         // Mobile numbers
		384,         // Mobile numbers
		385,         // Mobile numbers
		386,         // Mobile numbers
		387,         // Mobile numbers
		388,         // Mobile numbers
		389,         // Mobile numbers
		390,         // Mobile numbers
		391,         // Mobile numbers
		392,         // Mobile numbers
		393,         // Mobile numbers
		394,         // Mobile numbers
		395,         // Mobile numbers
		396,         // Mobile numbers
		397,         // Mobile numbers
		398,         // Mobile numbers
		399,         // Mobile numbers
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
			// Bogotá D.C.
			'BOGOTA'                       => self::DC,
			'BOGOTÁ'                       => self::DC,
			'BOGOTA D.C.'                  => self::DC,
			'BOGOTÁ D.C.'                  => self::DC,
			'DISTRITO CAPITAL'             => self::DC,
			'CAPITAL DISTRICT'             => self::DC,
			// Departments
			'AMAZONAS'                     => self::AMA,
			'ANTIOQUIA'                    => self::ANT,
			'ARA'                          => self::ARA,
			'ARAUCA'                       => self::ARA,
			'ATLANTICO'                    => self::ATL,
			'ATLÁNTICO'                    => self::ATL,
			'BOLIVAR'                      => self::BOL,
			'BOLÍVAR'                      => self::BOL,
			'BOYACA'                       => self::BOY,
			'BOYACÁ'                       => self::BOY,
			'CALDAS'                       => self::CAL,
			'CAQUETA'                      => self::CAQ,
			'CAQUETÁ'                      => self::CAQ,
			'CASANARE'                     => self::CAS,
			'CAUCA'                        => self::CAU,
			'CESAR'                        => self::CES,
			'CHOCO'                        => self::CHO,
			'CHOCÓ'                        => self::CHO,
			'CORDOBA'                      => self::COR,
			'CÓRDOBA'                      => self::COR,
			'CUNDINAMARCA'                 => self::CUN,
			'GUAINIA'                      => self::GUA,
			'GUAINÍA'                      => self::GUA,
			'GUAVIARE'                     => self::GUV,
			'HUI'                          => self::HUI,
			'HUILA'                        => self::HUI,
			'LA GUAJIRA'                   => self::LAG,
			'GUAJIRA'                      => self::LAG,
			'MAGDALENA'                    => self::MAG,
			'META'                         => self::MET,
			'NARINO'                       => self::NAR,
			'NARIÑO'                       => self::NAR,
			'NORTE DE SANTANDER'           => self::NSA,
			'PUTUMAYO'                     => self::PUT,
			'QUINDIO'                      => self::QUI,
			'QUINDÍO'                      => self::QUI,
			'RISARALDA'                    => self::RIS,
			'SAN ANDRES Y PROVIDENCIA'     => self::SAP,
			'SAN ANDRÉS Y PROVIDENCIA'     => self::SAP,
			'SAN ANDRES'                   => self::SAP,
			'SAN ANDRÉS'                   => self::SAP,
			'ARCHIPIELAGO DE SAN ANDRES'   => self::SAP,
			'ARCHIPIÉLAGO DE SAN ANDRÉS'   => self::SAP,
			'SANTANDER'                    => self::SAN,
			'SUCRE'                        => self::SUC,
			'TOLIMA'                       => self::TOL,
			'VALLE DEL CAUCA'              => self::VAC,
			'VALLE'                        => self::VAC,
			'VAUPES'                       => self::VAU,
			'VAUPÉS'                       => self::VAU,
			'VICHADA'                      => self::VID,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::DC => 'Bogotá, D.C.',
			self::AMA => 'Amazonas',
			self::ANT => 'Antioquia',
			self::ARA => 'Arauca',
			self::ATL => 'Atlántico',
			self::BOL => 'Bolívar',
			self::BOY => 'Boyacá',
			self::CAL => 'Caldas',
			self::CAQ => 'Caquetá',
			self::CAS => 'Casanare',
			self::CAU => 'Cauca',
			self::CES => 'Cesar',
			self::CHO => 'Chocó',
			self::COR => 'Córdoba',
			self::CUN => 'Cundinamarca',
			self::GUA => 'Guainía',
			self::GUV => 'Guaviare',
			self::HUI => 'Huila',
			self::LAG => 'La Guajira',
			self::MAG => 'Magdalena',
			self::MET => 'Meta',
			self::NAR => 'Nariño',
			self::NSA => 'Norte de Santander',
			self::PUT => 'Putumayo',
			self::QUI => 'Quindío',
			self::RIS => 'Risaralda',
			self::SAP => 'San Andrés y Providencia',
			self::SAN => 'Santander',
			self::SUC => 'Sucre',
			self::TOL => 'Tolima',
			self::VAC => 'Valle del Cauca',
			self::VAU => 'Vaupés',
			self::VID => 'Vichada',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::DC => 'Bogotá, D.C.',
			self::AMA => 'Amazonas',
			self::ANT => 'Antioquia',
			self::ARA => 'Arauca',
			self::ATL => 'Atlantico',
			self::BOL => 'Bolivar',
			self::BOY => 'Boyaca',
			self::CAL => 'Caldas',
			self::CAQ => 'Caqueta',
			self::CAS => 'Casanare',
			self::CAU => 'Cauca',
			self::CES => 'Cesar',
			self::CHO => 'Choco',
			self::COR => 'Cordoba',
			self::CUN => 'Cundinamarca',
			self::GUA => 'Guainia',
			self::GUV => 'Guaviare',
			self::HUI => 'Huila',
			self::LAG => 'La Guajira',
			self::MAG => 'Magdalena',
			self::MET => 'Meta',
			self::NAR => 'Narino',
			self::NSA => 'Norte de Santander',
			self::PUT => 'Putumayo',
			self::QUI => 'Quindio',
			self::RIS => 'Risaralda',
			self::SAP => 'San Andres and Providencia',
			self::SAN => 'Santander',
			self::SUC => 'Sucre',
			self::TOL => 'Tolima',
			self::VAC => 'Valle del Cauca',
			self::VAU => 'Vaupe',
			self::VID => 'Vichada',
		};
	}

	// Get capital cities of each department
	public function capital(): string
	{
		return match ($this) {
			self::DC => 'Bogotá',
			self::AMA => 'Leticia',
			self::ANT => 'Medellín',
			self::ARA => 'Arauca',
			self::ATL => 'Barranquilla',
			self::BOL => 'Cartagena',
			self::BOY => 'Tunja',
			self::CAL => 'Manizales',
			self::CAQ => 'Florencia',
			self::CAS => 'Yopal',
			self::CAU => 'Popayán',
			self::CES => 'Valledupar',
			self::CHO => 'Quibdó',
			self::COR => 'Montería',
			self::CUN => 'Bogotá', // Note: Cundinamarca's capital is Bogotá, but it's a separate entity
			self::GUA => 'Inírida',
			self::GUV => 'San José del Guaviare',
			self::HUI => 'Neiva',
			self::LAG => 'Riohacha',
			self::MAG => 'Santa Marta',
			self::MET => 'Villavicencio',
			self::NAR => 'Pasto',
			self::NSA => 'San José de Cúcuta',
			self::PUT => 'Mocoa',
			self::QUI => 'Armenia',
			self::RIS => 'Pereira',
			self::SAP => 'San Andrés',
			self::SAN => 'Bucaramanga',
			self::SUC => 'Sincelejo',
			self::TOL => 'Ibagué',
			self::VAC => 'Cali',
			self::VAU => 'Mitú',
			self::VID => 'Puerto Carreño',
		};
	}

	// Get ISO 3166-2:CO code (full code including country prefix)
	public function isoCode(): string
	{
		return "CO-{$this->value}";
	}

	// Get region (Colombia is divided into 6 natural regions)
	public function region(): string
	{
		return match ($this) {
			self::DC, self::CUN, self::BOY, self::CAL, self::HUI, self::TOL, self::QUI, self::RIS => 'Andina',
			self::ANT, self::NSA, self::SAN => 'Andina (Eje Cafetero/Norte)',
			self::VAC, self::CAU, self::NAR, self::VAC => 'Pacífica',
			self::ATL, self::BOL, self::SUC, self::COR, self::MAG, self::CES, self::LAG => 'Caribe',
			self::MET, self::CAS, self::ARA => 'Orinoquía',
			self::AMA, self::CAQ, self::PUT, self::GUA, self::GUV, self::VAU, self::VID => 'Amazonía',
			self::SAP => 'Insular',
		};
	}

	// Get sub-region (more specific)
	public function subRegion(): string
	{
		return match ($this) {
			self::DC => 'Sabana de Bogotá',
			self::CUN => 'Sabana de Bogotá',
			self::BOY => 'Altiplano Cundiboyacense',
			self::CAL, self::QUI, self::RIS => 'Eje Cafetero',
			self::ANT => 'Antioquia',
			self::NSA, self::SAN => 'Noreste',
			self::VAC => 'Valle del Cauca',
			self::CAU, self::NAR => 'Sur Occidente',
			self::CHO => 'Chocó Biogeográfico',
			self::ATL, self::BOL, self::SUC, self::COR => 'Costa Caribe',
			self::MAG, self::CES, self::LAG => 'Costa Caribe (Extremo Norte)',
			self::MET => 'Llanos Orientales',
			self::CAS, self::ARA => 'Llanos Orientales (Norte)',
			self::AMA, self::CAQ, self::PUT => 'Amazonía',
			self::GUA, self::GUV, self::VAU, self::VID => 'Amazonía (Extremo Sureste)',
			self::SAP => 'Caribe Insular',
			self::HUI => 'Sur Andino',
			self::TOL => 'Centro Andino',
			default => 'General',
		};
	}

	// Get main area code for the department/capital
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::DC => '1',   // Bogotá
			self::VAC => '2',  // Cali
			self::ANT => '4',  // Medellín
			self::SAN => '7',  // Bucaramanga
			self::QUI => '6',  // Armenia
			self::CAL => '6',  // Manizales (shares with Quindío)
			self::RIS => '6',  // Pereira (shares)
			self::ATL => '5',  // Barranquilla
			self::BOL => '5',  // Cartagena
			self::CUN => '1',  // Bogotá (shares with DC)
			self::MAG => '5',  // Santa Marta
			self::SUC => '5',  // Sincelejo
			self::CES => '5',  // Valledupar
			self::LAG => '5',  // Riohacha
			self::COR => '4',  // Montería
			self::NSA => '7',  // Cúcuta
			self::HUI => '8',  // Neiva
			self::TOL => '8',  // Ibagué
			self::CAU => '2',  // Popayán
			self::NAR => '2',  // Pasto
			self::MET => '8',  // Villavicencio
			self::CHO => '4',  // Quibdó
			self::CAS => '8',  // Yopal
			self::ARA => '7',  // Arauca
			self::PUT => '8',  // Mocoa
			self::AMA => '8',  // Leticia
			self::CAQ => '8',  // Florencia
			self::GUA => '8',  // Inírida
			self::GUV => '8',  // San José del Guaviare
			self::VAU => '8',  // Mitú
			self::VID => '8',  // Puerto Carreño
			self::SAP => '8',  // San Andrés
		};
	}

	// Get department by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		$map = [
			1 => self::DC,   // Bogotá
			2 => self::VAC,  // Cali (Valle del Cauca)
			4 => self::ANT,  // Medellín (Antioquia)
			5 => self::ATL,  // Barranquilla (Atlántico) - also other Caribbean departments
			6 => self::QUI,  // Armenia (Quindío) - also Caldas, Risaralda
			7 => self::SAN,  // Bucaramanga (Santander)
			8 => self::HUI,  // Neiva (Huila) - many departments share 8
		];

		return $map[$code] ?? null;
	}

	// Get population rank (approximate)
	public function populationRank(): int
	{
		return match ($this) {
			self::DC => 1,   // Bogotá - most populous
			self::ANT => 2,  // Antioquia
			self::VAC => 3,  // Valle del Cauca
			self::CUN => 4,  // Cundinamarca
			self::SAN => 5,  // Santander
			self::BOL => 6,  // Bolívar
			self::ATL => 7,  // Atlántico
			self::NSA => 8,  // Norte de Santander
			self::COR => 9,  // Córdoba
			self::MAG => 10, // Magdalena
			self::TOL => 11, // Tolima
			self::HUI => 12, // Huila
			self::RIS => 13, // Risaralda
			self::CAL => 14, // Caldas
			self::QUI => 15, // Quindío
			self::NAR => 16, // Nariño
			self::CAU => 17, // Cauca
			self::SUC => 18, // Sucre
			self::CES => 19, // Cesar
			self::MET => 20, // Meta
			self::BOY => 21, // Boyacá
			self::CHO => 22, // Chocó
			self::CAS => 23, // Casanare
			self::ARA => 24, // Arauca
			self::PUT => 25, // Putumayo
			self::CAQ => 26, // Caquetá
			self::LAG => 27, // La Guajira
			self::GUV => 28, // Guaviare
			self::AMA => 29, // Amazonas
			self::VAU => 30, // Vaupés
			self::GUA => 31, // Guainía
			self::VID => 32, // Vichada
			self::SAP => 33, // San Andrés y Providencia - least populous
		};
	}

	// Get area rank (approximate, in square km)
	public function areaRank(): int
	{
		return match ($this) {
			self::AMA => 1,  // Amazonas - largest
			self::VID => 2,  // Vichada
			self::CAQ => 3,  // Caquetá
			self::MET => 4,  // Meta
			self::ANT => 5,  // Antioquia
			self::VAU => 6,  // Vaupés
			self::GUA => 7,  // Guainía
			self::VAC => 8,  // Valle del Cauca
			self::GUV => 9,  // Guaviare
			self::BOL => 10, // Bolívar
			self::CUN => 11, // Cundinamarca
			self::CES => 12, // Cesar
			self::ARA => 13, // Arauca
			self::CHO => 14, // Chocó
			self::CAS => 15, // Casanare
			self::NAR => 16, // Nariño
			self::COR => 17, // Córdoba
			self::MAG => 18, // Magdalena
			self::PUT => 19, // Putumayo
			self::SAN => 20, // Santander
			self::TOL => 21, // Tolima
			self::HUI => 22, // Huila
			self::CAU => 23, // Cauca
			self::NSA => 24, // Norte de Santander
			self::BOY => 25, // Boyacá
			self::CAL => 26, // Caldas
			self::SUC => 27, // Sucre
			self::ATL => 28, // Atlántico
			self::LAG => 29, // La Guajira
			self::QUI => 30, // Quindío
			self::RIS => 31, // Risaralda
			self::DC => 32,  // Bogotá D.C.
			self::SAP => 33, // San Andrés y Providencia - smallest
		};
	}

	// Get whether the department is coastal
	public function isCoastal(): bool
	{
		return match ($this) {
			self::ATL, self::BOL, self::SUC, self::COR, self::MAG, self::CHO, self::NAR, self::CAU, self::VAC, self::LAG, self::SAP => true,
			default => false,
		};
	}

	// Get whether the department has Pacific coast
	public function hasPacificCoast(): bool
	{
		return match ($this) {
			self::CHO, self::CAU, self::NAR, self::VAC => true,
			default => false,
		};
	}

	// Get whether the department has Caribbean coast
	public function hasCaribbeanCoast(): bool
	{
		return match ($this) {
			self::ATL, self::BOL, self::SUC, self::COR, self::MAG, self::LAG, self::SAP => true,
			default => false,
		};
	}

	// Get major economic activity
	public function majorEconomicActivity(): string
	{
		return match ($this) {
			self::DC => 'Services, Government, Industry',
			self::ANT => 'Industry, Coffee, Services',
			self::VAC => 'Agriculture, Industry, Services',
			self::CUN => 'Agriculture, Industry, Services',
			self::SAN => 'Industry, Commerce, Services',
			self::BOL => 'Tourism, Industry, Port',
			self::ATL => 'Port, Industry, Commerce',
			self::NSA => 'Commerce, Industry, Agriculture',
			self::COR => 'Agriculture, Livestock',
			self::MAG => 'Tourism, Agriculture, Port',
			self::TOL => 'Agriculture, Industry',
			self::HUI => 'Agriculture, Oil',
			self::RIS => 'Coffee, Industry, Commerce',
			self::CAL => 'Coffee, Industry, Commerce',
			self::QUI => 'Coffee, Tourism, Agriculture',
			self::NAR => 'Agriculture, Livestock',
			self::CAU => 'Agriculture, Mining',
			self::SUC => 'Agriculture, Livestock',
			self::CES => 'Agriculture, Mining, Energy',
			self::MET => 'Oil, Agriculture, Livestock',
			self::BOY => 'Agriculture, Industry, Tourism',
			self::CHO => 'Mining, Forestry, Fishing',
			self::CAS => 'Oil, Agriculture, Livestock',
			self::ARA => 'Oil, Agriculture',
			self::PUT => 'Oil, Agriculture',
			self::CAQ => 'Agriculture, Livestock, Forestry',
			self::LAG => 'Mining, Tourism, Energy',
			self::GUV => 'Agriculture, Forestry',
			self::AMA => 'Tourism, Forestry',
			self::VAU => 'Tourism, Mining',
			self::GUA => 'Tourism, Mining',
			self::VID => 'Agriculture, Mining',
			self::SAP => 'Tourism, Commerce',
		};
	}

	// Get UNESCO World Heritage Sites in the department
	public function unescoSites(): array
	{
		return match ($this) {
			self::DC => ['Historic Centre of Santa Cruz de Mompox'],
			self::BOL => ['Port, Fortresses and Group of Monuments, Cartagena'],
			self::MAG => ['Los Katíos National Park', 'Malpelo Fauna and Flora Sanctuary'],
			self::CHO => ['Los Katíos National Park'],
			self::VAC => ['Coffee Cultural Landscape of Colombia'],
			self::CAL => ['Coffee Cultural Landscape of Colombia'],
			self::QUI => ['Coffee Cultural Landscape of Colombia'],
			self::RIS => ['Coffee Cultural Landscape of Colombia'],
			self::SAN => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			self::CAQ => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			self::GUV => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			self::AMA => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			self::VAU => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			self::GUA => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			self::PUT => ['Chiribiquete National Park - "The Maloca of the Jaguar"'],
			default => [],
		};
	}
}
