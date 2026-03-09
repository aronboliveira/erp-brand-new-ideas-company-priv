<?php

namespace App\Enums;

enum PeruDepartment: string
{
	// Constitutional Province of Callao (has regional autonomy)
	case CAL = 'CAL';  // Callao
		// Departments
	case AMA = 'AMA';  // Amazonas
	case ANC = 'ANC';  // Áncash
	case APU = 'APU';  // Apurímac
	case ARE = 'ARE';  // Arequipa
	case AYA = 'AYA';  // Ayacucho
	case CAJ = 'CAJ';  // Cajamarca
	case CUS = 'CUS';  // Cusco
	case HUV = 'HUV';  // Huancavelica
	case HUC = 'HUC';  // Huánuco
	case ICA = 'ICA';  // Ica
	case JUN = 'JUN';  // Junín
	case LAL = 'LAL';  // La Libertad
	case LAM = 'LAM';  // Lambayeque
	case LIM = 'LIM';  // Lima (department)
	case LOR = 'LOR';  // Loreto
	case MDD = 'MDD';  // Madre de Dios
	case MOQ = 'MOQ';  // Moquegua
	case PAS = 'PAS';  // Pasco
	case PIU = 'PIU';  // Piura
	case PUN = 'PUN';  // Puno
	case SAM = 'SAM';  // San Martín
	case TAC = 'TAC';  // Tacna
	case TUM = 'TUM';  // Tumbes
	case UCA = 'UCA';  // Ucayali

	// Peru area codes (códigos de área) by department
	public const AREA_CODES = [
		1,           // Lima and Callao
		4,           // Arequipa
		5,           // Tacna, Moquegua
		6,           // Puno, Cusco
		7,           // Huancavelica, Ayacucho
		8,           // Ica, Huánuco
		9,           // Mobile numbers
		14,          // Ancash
		16,          // La Libertad
		17,          // Lambayeque, Piura, Tumbes
		19,          // Cajamarca, Amazonas
		22,          // San Martín, Loreto
		23,          // Ucayali, Madre de Dios
		24,          // Junín, Pasco, Huánuco
		25,          // Áncash, La Libertad
		27,          // Ica, Ayacucho
		34,          // Arequipa, Moquegua, Tacna
		36,          // Cusco, Puno, Apurímac
		37,          // Apurímac, Cusco
		38,          // Huancavelica, Junín
		39,          // Pasco, Junín
		41,          // Piura, Tumbes, Lambayeque
		42,          // Lambayeque, La Libertad
		43,          // Amazonas, Cajamarca
		44,          // Cajamarca, La Libertad
		45,          // San Martín, Loreto
		46,          // Loreto, Ucayali
		47,          // Ucayali, Madre de Dios
		48,          // Madre de Dios, Cusco
		49,          // Mobile numbers
		51,          // Mobile numbers
		52,          // Mobile numbers
		53,          // Mobile numbers
		54,          // Mobile numbers
		55,          // Mobile numbers
		56,          // Mobile numbers
		57,          // Mobile numbers
		58,          // Mobile numbers
		59,          // Mobile numbers
		61,          // Mobile numbers
		62,          // Mobile numbers
		63,          // Mobile numbers
		64,          // Mobile numbers
		65,          // Mobile numbers
		66,          // Mobile numbers
		67,          // Mobile numbers
		68,          // Mobile numbers
		69,          // Mobile numbers
		71,          // Mobile numbers
		72,          // Mobile numbers
		73,          // Mobile numbers
		74,          // Mobile numbers
		75,          // Mobile numbers
		76,          // Mobile numbers
		77,          // Mobile numbers
		78,          // Mobile numbers
		79,          // Mobile numbers
		81,          // Mobile numbers
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
			// Callao (Constitutional Province)
			'CALLAO'                       => self::CAL,
			'PROVINCIA CONSTITUCIONAL DEL CALLAO' => self::CAL,
			'PROVINCIA CONSTITUCIONAL DE CALLAO' => self::CAL,
			// Departments
			'AMAZONAS'                     => self::AMA,
			'AN CASH'                      => self::ANC, // Common misspelling
			'ANCASH'                       => self::ANC,
			'ÁNCASH'                       => self::ANC,
			'APURIMAC'                     => self::APU,
			'APURÍMAC'                     => self::APU,
			'AREQUIPA'                     => self::ARE,
			'AYACUCHO'                     => self::AYA,
			'CAJAMARCA'                    => self::CAJ,
			'CUSCO'                        => self::CUS,
			'CUSCO'                        => self::CUS,
			'HUANCAVELICA'                 => self::HUV,
			'HUANUCO'                      => self::HUC,
			'HUÁNUCO'                      => self::HUC,
			'ICA'                          => self::ICA,
			'JUNIN'                        => self::JUN,
			'JUNÍN'                        => self::JUN,
			'LA LIBERTAD'                  => self::LAL,
			'LAMBAYEQUE'                   => self::LAM,
			'LIMA'                         => self::LIM,
			'LIMA DEPARTMENT'              => self::LIM,
			'LIMA DEPARTAMENTO'            => self::LIM,
			'LORETO'                       => self::LOR,
			'MADRE DE DIOS'                => self::MDD,
			'MOQUEGUA'                     => self::MOQ,
			'PASCO'                        => self::PAS,
			'PIURA'                        => self::PIU,
			'PUNO'                         => self::PUN,
			'SAN MARTIN'                   => self::SAM,
			'SAN MARTÍN'                   => self::SAM,
			'TACNA'                        => self::TAC,
			'TUMBES'                       => self::TUM,
			'UCAYALI'                      => self::UCA,
			'UCAYALI'                      => self::UCA,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::CAL => 'Callao',
			self::AMA => 'Amazonas',
			self::ANC => 'Áncash',
			self::APU => 'Apurímac',
			self::ARE => 'Arequipa',
			self::AYA => 'Ayacucho',
			self::CAJ => 'Cajamarca',
			self::CUS => 'Cusco',
			self::HUV => 'Huancavelica',
			self::HUC => 'Huánuco',
			self::ICA => 'Ica',
			self::JUN => 'Junín',
			self::LAL => 'La Libertad',
			self::LAM => 'Lambayeque',
			self::LIM => 'Lima',
			self::LOR => 'Loreto',
			self::MDD => 'Madre de Dios',
			self::MOQ => 'Moquegua',
			self::PAS => 'Pasco',
			self::PIU => 'Piura',
			self::PUN => 'Puno',
			self::SAM => 'San Martín',
			self::TAC => 'Tacna',
			self::TUM => 'Tumbes',
			self::UCA => 'Ucayali',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::CAL => 'Callao',
			self::AMA => 'Amazonas',
			self::ANC => 'Ancash',
			self::APU => 'Apurimac',
			self::ARE => 'Arequipa',
			self::AYA => 'Ayacucho',
			self::CAJ => 'Cajamarca',
			self::CUS => 'Cusco',
			self::HUV => 'Huancavelica',
			self::HUC => 'Huanuco',
			self::ICA => 'Ica',
			self::JUN => 'Junin',
			self::LAL => 'La Libertad',
			self::LAM => 'Lambayeque',
			self::LIM => 'Lima',
			self::LOR => 'Loreto',
			self::MDD => 'Madre de Dios',
			self::MOQ => 'Moquegua',
			self::PAS => 'Pasco',
			self::PIU => 'Piura',
			self::PUN => 'Puno',
			self::SAM => 'San Martin',
			self::TAC => 'Tacna',
			self::TUM => 'Tumbes',
			self::UCA => 'Ucayali',
		};
	}

	// Get capital cities of each department
	public function capital(): string
	{
		return match ($this) {
			self::CAL => 'Callao',
			self::AMA => 'Chachapoyas',
			self::ANC => 'Huaraz',
			self::APU => 'Abancay',
			self::ARE => 'Arequipa',
			self::AYA => 'Ayacucho',
			self::CAJ => 'Cajamarca',
			self::CUS => 'Cusco',
			self::HUV => 'Huancavelica',
			self::HUC => 'Huánuco',
			self::ICA => 'Ica',
			self::JUN => 'Huancayo',
			self::LAL => 'Trujillo',
			self::LAM => 'Chiclayo',
			self::LIM => 'Huacho',
			self::LOR => 'Iquitos',
			self::MDD => 'Puerto Maldonado',
			self::MOQ => 'Moquegua',
			self::PAS => 'Cerro de Pasco',
			self::PIU => 'Piura',
			self::PUN => 'Puno',
			self::SAM => 'Moyobamba',
			self::TAC => 'Tacna',
			self::TUM => 'Tumbes',
			self::UCA => 'Pucallpa',
		};
	}

	// Note: The capital of Peru (Lima City) is in the Lima Province, not Lima Department
	public function isCapitalCityInDepartment(): bool
	{
		return match ($this) {
			// Lima City (capital of Peru) is in Lima Province, which is separate from Lima Department
			self::LIM => false, // Huacho is the capital of Lima Department, not Lima City
			default => true,
		};
	}

	// Get ISO 3166-2:PE code (full code including country prefix)
	public function isoCode(): string
	{
		return "PE-{$this->value}";
	}

	// Get region (Peru is divided into three main regions)
	public function region(): string
	{
		return match ($this) {
			self::TUM, self::PIU, self::LAM, self::LOR, self::AMA, self::CAJ, self::SAM, self::UCA, self::MDD => 'Costa',
			self::CAL, self::LIM, self::ICA, self::ARE, self::MOQ, self::TAC => 'Sierra',
			self::ANC, self::HUC, self::PAS, self::JUN, self::HUV, self::AYA, self::APU, self::CUS, self::PUN, self::LAL => 'Selva',
		};
	}

	// Get main area code for the department
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::CAL => '1',
			self::LIM => '1',
			self::ARE => '54',
			self::TAC => '52',
			self::MOQ => '53',
			self::PUN => '51',
			self::CUS => '84',
			self::HUV => '67',
			self::AYA => '66',
			self::ICA => '56',
			self::HUC => '62',
			self::ANC => '43',
			self::LAL => '44',
			self::LAM => '74',
			self::PIU => '73',
			self::TUM => '72',
			self::CAJ => '76',
			self::AMA => '41',
			self::SAM => '42',
			self::LOR => '65',
			self::UCA => '61',
			self::MDD => '82',
			self::JUN => '64',
			self::PAS => '63',
			self::APU => '83',
		};
	}

	// Get department by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		$map = [
			// Lima and Callao
			1 => self::LIM,
			// Arequipa
			54 => self::ARE,
			// Tacna
			52 => self::TAC,
			// Moquegua
			53 => self::MOQ,
			// Puno
			51 => self::PUN,
			// Cusco
			84 => self::CUS,
			// Huancavelica
			67 => self::HUV,
			// Ayacucho
			66 => self::AYA,
			// Ica
			56 => self::ICA,
			// Huánuco
			62 => self::HUC,
			// Ancash
			43 => self::ANC,
			// La Libertad
			44 => self::LAL,
			// Lambayeque
			74 => self::LAM,
			// Piura
			73 => self::PIU,
			// Tumbes
			72 => self::TUM,
			// Cajamarca
			76 => self::CAJ,
			// Amazonas
			41 => self::AMA,
			// San Martín
			42 => self::SAM,
			// Loreto
			65 => self::LOR,
			// Ucayali
			61 => self::UCA,
			// Madre de Dios
			82 => self::MDD,
			// Junín
			64 => self::JUN,
			// Pasco
			63 => self::PAS,
			// Apurímac
			83 => self::APU,
		];

		return $map[$code] ?? null;
	}

	// Get common alternative names
	public function alternativeNames(): array
	{
		return match ($this) {
			self::CAL => ['El Primer Puerto'],
			self::LIM => ['Department of Lima', 'Lima Region'],
			self::ARE => ['La Ciudad Blanca', 'The White City'],
			self::CUS => ['Cuzco', 'Qosqo'],
			self::LAL => ['Freedom'],
			self::LOR => ['Loreto Region', 'Maynas'],
			self::SAM => ['San Martin Region'],
			default => [],
		};
	}

	// Get the Lima Metropolitan Area (special case)
	public function isInLimaMetropolitanArea(): bool
	{
		return match ($this) {
			self::CAL, self::LIM => true,
			default => false,
		};
	}
}
