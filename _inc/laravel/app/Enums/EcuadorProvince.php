<?php

namespace App\Enums;

enum EcuadorProvince: string
{
	// Ecuador provinces (ISO 3166-2:EC codes)
	case A = 'A';   // Azuay
	case B = 'B';   // Bolívar
	case C = 'C';   // Carchi
	case D = 'D';   // Orellana
	case E = 'E';   // Esmeraldas
	case F = 'F';   // Cañar
	case G = 'G';   // Guayas
	case H = 'H';   // Chimborazo
	case I = 'I';   // Imbabura
	case J = 'J';   // Loja (Note: In ISO, L is Loja but J is also used historically)
	case K = 'K';   // Sucumbíos
	case L = 'L';   // Loja (ISO code, but vehicle plate uses L for Loja)
	case M = 'M';   // Manabí
	case N = 'N';   // Napo
	case O = 'O';   // El Oro
	case P = 'P';   // Pichincha
	case R = 'R';   // Los Ríos
	case S = 'S';   // Morona Santiago
	case T = 'T';   // Tungurahua
	case U = 'U';   // Galápagos
	case V = 'V';   // Pastaza (Note: Vehicle plate uses V, ISO uses Y)
	case W = 'W';   // Santo Domingo de los Tsáchilas
	case X = 'X';   // Cotopaxi
	case Y = 'Y';   // Pastaza (ISO code)
	case Z = 'Z';   // Zamora Chinchipe

	// Ecuador area codes (códigos de área) by province
	public const AREA_CODES = [
		2,           // Guayas (Guayaquil)
		3,           // Azuay (Cuenca)
		4,           // Manabí (Portoviejo), Santo Domingo
		5,           // Tungurahua (Ambato), Cotopaxi
		6,           // Chimborazo (Riobamba), Bolívar
		7,           // El Oro (Machala), Loja, Zamora
		8,           // Esmeraldas
		9,           // Mobile numbers
		22,          // Santo Domingo de los Tsáchilas
		23,          // Los Ríos (Quevedo, Babahoyo)
		24,          // Manabí (Manta, Portoviejo)
		25,          // Manabí (Portoviejo, Manta)
		26,          // Guayas (Guayaquil, Durán, Samborondón)
		27,          // Guayas (Guayaquil)
		28,          // Guayas (Guayaquil)
		29,          // Guayas (Guayaquil)
		32,          // Pichincha (Quito)
		33,          // Pichincha (Quito)
		34,          // Pichincha (Quito)
		35,          // Pichincha (Quito)
		36,          // Pichincha (Quito)
		37,          // Pichincha (Quito)
		38,          // Pichincha (Quito)
		39,          // Pichincha (Quito)
		42,          // Imbabura (Ibarra), Carchi
		43,          // Imbabura (Ibarra)
		44,          // Carchi (Tulcán)
		45,          // Esmeraldas (Esmeraldas city)
		46,          // Esmeraldas (Atacames, same)
		47,          // Sucumbíos (Lago Agrio)
		48,          // Sucumbíos (Lago Agrio, Shushufindi)
		49,          // Orellana (El Coca)
		52,          // Napo (Tena), Pastaza
		53,          // Pastaza (Puyo)
		54,          // Morona Santiago (Macas)
		55,          // Zamora Chinchipe (Zamora)
		56,          // Loja (Loja city)
		57,          // Loja (Loja city, Catamayo)
		58,          // El Oro (Machala)
		59,          // El Oro (Machala, Santa Rosa)
		62,          // Azuay (Cuenca)
		63,          // Azuay (Cuenca)
		64,          // Azuay (Cuenca)
		65,          // Cañar (Azogues)
		66,          // Cañar (Azogues)
		67,          // Chimborazo (Riobamba)
		68,          // Chimborazo (Riobamba, Guaranda)
		69,          // Bolívar (Guaranda)
		72,          // Tungurahua (Ambato)
		73,          // Tungurahua (Ambato)
		74,          // Cotopaxi (Latacunga)
		75,          // Cotopaxi (Latacunga)
		76,          // Santo Domingo de los Tsáchilas
		77,          // Santo Domingo de los Tsáchilas
		78,          // Los Ríos (Quevedo)
		79,          // Los Ríos (Quevedo, Babahoyo)
		82,          // Galápagos (Puerto Ayora, Puerto Baquerizo Moreno)
		83,          // Galápagos (San Cristóbal, Santa Cruz)
		84,          // Galápagos (Isabela)
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
			// Provinces
			'AZUAY'                     => self::A,
			'BOLIVAR'                   => self::B,
			'BOLÍVAR'                   => self::B,
			'CARCHI'                    => self::C,
			'ORELLANA'                  => self::D,
			'ESMERALDAS'                => self::E,
			'CANAR'                     => self::F,
			'CAÑAR'                     => self::F,
			'GUAYAS'                    => self::G,
			'CHIMBORAZO'                => self::H,
			'IMBABURA'                  => self::I,
			'LOJA'                      => self::L,  // Note: L is ISO code, J is vehicle plate
			'LOJA PROVINCE'             => self::L,
			'SUCUMBIOS'                 => self::K,
			'SUCUMBÍOS'                 => self::K,
			'MANABI'                    => self::M,
			'MANABÍ'                    => self::M,
			'NAPO'                      => self::N,
			'EL ORO'                    => self::O,
			'PICHINCHA'                 => self::P,
			'LOS RIOS'                  => self::R,
			'LOS RÍOS'                  => self::R,
			'MORONA SANTIAGO'           => self::S,
			'TUNGURAHUA'                => self::T,
			'GALAPAGOS'                 => self::U,
			'GALÁPAGOS'                 => self::U,
			'PASTAZA'                   => self::Y,  // Y is ISO code, V is vehicle plate
			'SANTO DOMINGO DE LOS TSACHILAS' => self::W,
			'SANTO DOMINGO DE LOS TSÁCHILAS' => self::W,
			'SANTO DOMINGO'             => self::W,
			'COTOPAXI'                  => self::X,
			'ZAMORA CHINCHIPE'          => self::Z,
			// Vehicle plate codes (different from ISO)
			'V'                         => self::Y,  // Vehicle plate for Pastaza
			'J'                         => self::L,  // Vehicle plate for Loja
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::A => 'Azuay',
			self::B => 'Bolívar',
			self::C => 'Carchi',
			self::D => 'Orellana',
			self::E => 'Esmeraldas',
			self::F => 'Cañar',
			self::G => 'Guayas',
			self::H => 'Chimborazo',
			self::I => 'Imbabura',
			self::J => 'Loja',  // Historical/vehicle plate code
			self::K => 'Sucumbíos',
			self::L => 'Loja',  // ISO code
			self::M => 'Manabí',
			self::N => 'Napo',
			self::O => 'El Oro',
			self::P => 'Pichincha',
			self::R => 'Los Ríos',
			self::S => 'Morona Santiago',
			self::T => 'Tungurahua',
			self::U => 'Galápagos',
			self::V => 'Pastaza',  // Vehicle plate code
			self::W => 'Santo Domingo de los Tsáchilas',
			self::X => 'Cotopaxi',
			self::Y => 'Pastaza',  // ISO code
			self::Z => 'Zamora Chinchipe',
		};
	}

	// Optional: Get English labels for internationalization
	public function labelEn(): string
	{
		return match ($this) {
			self::A => 'Azuay',
			self::B => 'Bolivar',
			self::C => 'Carchi',
			self::D => 'Orellana',
			self::E => 'Esmeraldas',
			self::F => 'Canar',
			self::G => 'Guayas',
			self::H => 'Chimborazo',
			self::I => 'Imbabura',
			self::J => 'Loja',
			self::K => 'Sucumbios',
			self::L => 'Loja',
			self::M => 'Manabi',
			self::N => 'Napo',
			self::O => 'El Oro',
			self::P => 'Pichincha',
			self::R => 'Los Rios',
			self::S => 'Morona Santiago',
			self::T => 'Tungurahua',
			self::U => 'Galapagos',
			self::V => 'Pastaza',
			self::W => 'Santo Domingo de los Tsachilas',
			self::X => 'Cotopaxi',
			self::Y => 'Pastaza',
			self::Z => 'Zamora Chinchipe',
		};
	}

	// Get capital cities of each province
	public function capital(): string
	{
		return match ($this) {
			self::A => 'Cuenca',
			self::B => 'Guaranda',
			self::C => 'Tulcán',
			self::D => 'Puerto Francisco de Orellana (El Coca)',
			self::E => 'Esmeraldas',
			self::F => 'Azogues',
			self::G => 'Guayaquil',
			self::H => 'Riobamba',
			self::I => 'Ibarra',
			self::J => 'Loja',
			self::K => 'Nueva Loja (Lago Agrio)',
			self::L => 'Loja',
			self::M => 'Portoviejo',
			self::N => 'Tena',
			self::O => 'Machala',
			self::P => 'Quito',
			self::R => 'Babahoyo',
			self::S => 'Macas',
			self::T => 'Ambato',
			self::U => 'Puerto Baquerizo Moreno',
			self::V => 'Puyo',
			self::W => 'Santo Domingo de los Colorados',
			self::X => 'Latacunga',
			self::Y => 'Puyo',
			self::Z => 'Zamora',
		};
	}

	// Get ISO 3166-2:EC code (full code including country prefix)
	public function isoCode(): string
	{
		return "EC-{$this->value}";
	}

	// Get region (Ecuador is divided into four natural regions)
	public function region(): string
	{
		return match ($this) {
			self::E, self::M, self::G, self::R, self::O, self::W => 'Costa',
			self::P, self::I, self::C, self::X, self::T, self::H, self::B, self::A, self::F, self::L => 'Sierra',
			self::S, self::N, self::D, self::K, self::Z, self::Y, self::V => 'Amazonía',
			self::U => 'Insular',
		};
	}

	// Get vehicle plate code (may differ from ISO code)
	public function vehiclePlateCode(): string
	{
		return match ($this) {
			self::A => 'A',
			self::B => 'B',
			self::C => 'C',
			self::D => 'O',
			self::E => 'E',
			self::F => 'F',
			self::G => 'G',
			self::H => 'H',
			self::I => 'I',
			self::J => 'L', // Loja uses L on plates
			self::K => 'K',
			self::L => 'L', // Loja uses L on plates
			self::M => 'M',
			self::N => 'N',
			self::O => 'O',
			self::P => 'P',
			self::R => 'R',
			self::S => 'S',
			self::T => 'T',
			self::U => 'U',
			self::V => 'V', // Pastaza uses V on plates
			self::W => 'W',
			self::X => 'X',
			self::Y => 'V', // Pastaza uses V on plates
			self::Z => 'Z',
		};
	}

	// Get main area code for the province
	public function mainAreaCode(): string
	{
		return match ($this) {
			self::A => '7',   // Cuenca
			self::B => '3',   // Guaranda
			self::C => '6',   // Tulcán
			self::D => '6',   // El Coca
			self::E => '6',   // Esmeraldas
			self::F => '7',   // Azogues
			self::G => '4',   // Guayaquil
			self::H => '3',   // Riobamba
			self::I => '6',   // Ibarra
			self::J => '7',   // Loja
			self::K => '6',   // Lago Agrio
			self::L => '7',   // Loja
			self::M => '5',   // Portoviejo
			self::N => '6',   // Tena
			self::O => '7',   // Machala
			self::P => '2',   // Quito
			self::R => '5',   // Babahoyo
			self::S => '7',   // Macas
			self::T => '3',   // Ambato
			self::U => '5',   // Galápagos
			self::V => '3',   // Puyo
			self::W => '2',   // Santo Domingo
			self::X => '3',   // Latacunga
			self::Y => '3',   // Puyo
			self::Z => '7',   // Zamora
		};
	}

	// Get province by area code (simplified)
	public static function fromAreaCode(string $areaCode): ?self
	{
		$code = (int) $areaCode;

		// Note: Ecuador area codes are shared between multiple provinces
		// This map returns the primary/default province for each code
		$map = [
			4 => self::G,  // Guayas
			2 => self::P,  // Pichincha (also covers Santo Domingo)
			7 => self::A,  // Azuay (also covers El Oro, Loja, Cañar, Morona Santiago, Zamora Chinchipe)
			5 => self::M,  // Manabí (also covers Los Ríos, Galápagos)
			3 => self::T,  // Tungurahua (also covers Cotopaxi, Chimborazo, Bolívar)
			6 => self::E,  // Esmeraldas (also covers Carchi, Imbabura, Sucumbíos, Orellana, Napo)
		];

		return $map[$code] ?? null;
	}

	// Get the largest city (sometimes different from capital)
	public function largestCity(): string
	{
		return match ($this) {
			self::G => 'Guayaquil',  // Capital and largest
			self::P => 'Quito',      // Capital and largest
			self::A => 'Cuenca',     // Capital and largest
			self::M => 'Portoviejo', // Capital and largest
			self::T => 'Ambato',     // Capital and largest
			self::E => 'Esmeraldas', // Capital and largest
			self::O => 'Machala',    // Capital and largest
			self::L => 'Loja',       // Capital and largest
			self::H => 'Riobamba',   // Capital and largest
			self::I => 'Ibarra',     // Capital and largest
			self::R => 'Quevedo',    // Not the capital (Babahoyo is capital)
			self::W => 'Santo Domingo de los Colorados', // Capital and largest
			self::X => 'Latacunga',  // Capital and largest
			self::C => 'Tulcán',     // Capital and largest
			self::B => 'Guaranda',   // Capital and largest
			self::F => 'Azogues',    // Capital and largest
			self::N => 'Tena',       // Capital and largest
			self::S => 'Macas',      // Capital and largest
			self::K => 'Nueva Loja', // Capital and largest
			self::D => 'El Coca',    // Capital and largest
			self::Z => 'Zamora',     // Capital and largest
			self::Y => 'Puyo',       // Capital and largest
			self::V => 'Puyo',       // Capital and largest
			self::U => 'Puerto Ayora', // Not the capital (largest in Galápagos)
			self::J => 'Loja',       // Capital and largest
		};
	}

	// Get whether province is in the Andes (Sierra region)
	public function isAndean(): bool
	{
		return $this->region() === 'Sierra';
	}

	// Get whether province is coastal
	public function isCoastal(): bool
	{
		return $this->region() === 'Costa';
	}

	// Get whether province is in the Amazon
	public function isAmazonian(): bool
	{
		return $this->region() === 'Amazonía';
	}

	// Get whether province is insular (islands)
	public function isInsular(): bool
	{
		return $this->region() === 'Insular';
	}
}
