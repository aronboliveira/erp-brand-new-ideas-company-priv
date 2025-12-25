<?php

namespace App\Enums;

enum VenezuelaState: string
{
    // Capital District
    case A = 'A';  // Capital District (Distrito Capital)
        // States
    case B = 'B';  // Anzoátegui
    case C = 'C';  // Apure
    case D = 'D';  // Aragua
    case E = 'E';  // Barinas
    case F = 'F';  // Bolívar
    case G = 'G';  // Carabobo
    case H = 'H';  // Cojedes
    case I = 'I';  // Falcón
    case J = 'J';  // Guárico
    case K = 'K';  // Lara
    case L = 'L';  // Mérida
    case M = 'M';  // Miranda
    case N = 'N';  // Monagas
    case O = 'O';  // Nueva Esparta
    case P = 'P';  // Portuguesa
    case R = 'R';  // Sucre
    case S = 'S';  // Táchira
    case T = 'T';  // Trujillo
    case U = 'U';  // Yaracuy
    case V = 'V';  // Zulia
    case W = 'W';  // Federal Dependencies (Dependencias Federales)
    case X = 'X';  // Vargas
    case Y = 'Y';  // Delta Amacuro
    case Z = 'Z';  // Amazonas

    // Venezuela area codes (códigos de área) by state
    public const AREA_CODES = [
        212,        // Caracas (Capital District), Vargas, Miranda
        241,        // Carabobo
        242,        // Carabobo
        243,        // Aragua
        244,        // Aragua
        245,        // Carabobo, Aragua
        246,        // Guárico
        247,        // Apure, Guárico
        248,        // Amazonas
        249,        // Mobile numbers
        251,        // Lara, Yaracuy
        252,        // Lara
        253,        // Lara, Yaracuy
        254,        // Lara, Yaracuy
        255,        // Lara, Portuguesa
        256,        // Portuguesa
        257,        // Portuguesa
        258,        // Cojedes
        259,        // Falcón
        261,        // Zulia
        262,        // Zulia
        263,        // Zulia
        264,        // Zulia
        265,        // Zulia
        266,        // Zulia
        267,        // Zulia
        268,        // Falcón, Zulia
        269,        // Falcón
        271,        // Trujillo, Zulia
        272,        // Trujillo
        273,        // Barinas
        274,        // Mérida, Zulia
        275,        // Mérida, Táchira, Zulia
        276,        // Táchira, Mérida
        277,        // Táchira
        278,        // Barinas
        279,        // Táchira
        281,        // Anzoátegui
        282,        // Anzoátegui
        283,        // Anzoátegui
        285,        // Bolívar
        286,        // Bolívar
        287,        // Delta Amacuro
        288,        // Bolívar
        291,        // Monagas
        292,        // Monagas
        293,        // Sucre
        294,        // Sucre
        295,        // Nueva Esparta
        414,        // Mobile (Movilnet)
        416,        // Mobile (Movistar)
        424,        // Mobile (Digitel)
        426,        // Mobile (Movistar)
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
            'CARACAS'                       => self::A,
            'DISTRITO CAPITAL'              => self::A,
            'CAPITAL DISTRICT'              => self::A,
            'DC'                            => self::A,
            // States
            'ANZOATEGUI'                    => self::B,
            'ANZOÁTEGUI'                    => self::B,
            'APURE'                         => self::C,
            'ARAGUA'                        => self::D,
            'BARINAS'                       => self::E,
            'BOLIVAR'                       => self::F,
            'BOLÍVAR'                       => self::F,
            'CARABOBO'                      => self::G,
            'COJEDES'                       => self::H,
            'FALCON'                        => self::I,
            'FALCÓN'                        => self::I,
            'GUARICO'                       => self::J,
            'GUÁRICO'                       => self::J,
            'LARA'                          => self::K,
            'MERIDA'                        => self::L,
            'MÉRIDA'                        => self::L,
            'MIRANDA'                       => self::M,
            'MONAGAS'                       => self::N,
            'NUEVA ESPARTA'                 => self::O,
            'MARGARITA'                     => self::O, // Main island of Nueva Esparta
            'PORTUGUESA'                    => self::P,
            'SUCRE'                         => self::R,
            'TACHIRA'                       => self::S,
            'TÁCHIRA'                       => self::S,
            'TRUJILLO'                      => self::T,
            'YARACUY'                       => self::U,
            'ZULIA'                         => self::V,
            'DEPENDENCIAS FEDERALES'        => self::W,
            'FEDERAL DEPENDENCIES'          => self::W,
            'VARGAS'                        => self::X,
            'LITORAL'                       => self::X, // Vargas is coastal/litoral
            'DELTA AMACURO'                 => self::Y,
            'AMAZONAS'                      => self::Z,
        ];

        return $map[$v] ?? null;
    }

    public function label(): string
    {
        return match ($this) {
            self::A => 'Distrito Capital',
            self::B => 'Anzoátegui',
            self::C => 'Apure',
            self::D => 'Aragua',
            self::E => 'Barinas',
            self::F => 'Bolívar',
            self::G => 'Carabobo',
            self::H => 'Cojedes',
            self::I => 'Falcón',
            self::J => 'Guárico',
            self::K => 'Lara',
            self::L => 'Mérida',
            self::M => 'Miranda',
            self::N => 'Monagas',
            self::O => 'Nueva Esparta',
            self::P => 'Portuguesa',
            self::R => 'Sucre',
            self::S => 'Táchira',
            self::T => 'Trujillo',
            self::U => 'Yaracuy',
            self::V => 'Zulia',
            self::W => 'Dependencias Federales',
            self::X => 'Vargas',
            self::Y => 'Delta Amacuro',
            self::Z => 'Amazonas',
        };
    }

    // Optional: Get English labels for internationalization
    public function labelEn(): string
    {
        return match ($this) {
            self::A => 'Capital District',
            self::B => 'Anzoategui',
            self::C => 'Apure',
            self::D => 'Aragua',
            self::E => 'Barinas',
            self::F => 'Bolivar',
            self::G => 'Carabobo',
            self::H => 'Cojedes',
            self::I => 'Falcon',
            self::J => 'Guarico',
            self::K => 'Lara',
            self::L => 'Merida',
            self::M => 'Miranda',
            self::N => 'Monagas',
            self::O => 'Nueva Esparta',
            self::P => 'Portuguesa',
            self::R => 'Sucre',
            self::S => 'Tachira',
            self::T => 'Trujillo',
            self::U => 'Yaracuy',
            self::V => 'Zulia',
            self::W => 'Federal Dependencies',
            self::X => 'Vargas',
            self::Y => 'Delta Amacuro',
            self::Z => 'Amazonas',
        };
    }

    // Get capital cities of each state
    public function capital(): string
    {
        return match ($this) {
            self::A => 'Caracas',
            self::B => 'Barcelona',
            self::C => 'San Fernando de Apure',
            self::D => 'Maracay',
            self::E => 'Barinas',
            self::F => 'Ciudad Bolívar',
            self::G => 'Valencia',
            self::H => 'San Carlos',
            self::I => 'Coro',
            self::J => 'San Juan de los Morros',
            self::K => 'Barquisimeto',
            self::L => 'Mérida',
            self::M => 'Los Teques',
            self::N => 'Maturín',
            self::O => 'La Asunción',
            self::P => 'Guanare',
            self::R => 'Cumaná',
            self::S => 'San Cristóbal',
            self::T => 'Trujillo',
            self::U => 'San Felipe',
            self::V => 'Maracaibo',
            self::W => 'Los Roques', // Main island of federal dependencies
            self::X => 'La Guaira',
            self::Y => 'Tucupita',
            self::Z => 'Puerto Ayacucho',
        };
    }

    // Get ISO 3166-2:VE code (full code including country prefix)
    public function isoCode(): string
    {
        return "VE-{$this->value}";
    }

    // Get region (Venezuela is divided into regions)
    public function region(): string
    {
        return match ($this) {
            self::A, self::G, self::D, self::M, self::X => 'Central',
            self::I, self::R, self::O, self::N, self::B => 'Northeastern',
            self::K, self::U, self::P, self::H, self::J, self::E => 'Central-Western',
            self::V, self::L, self::S, self::T, self::F => 'Zulian',
            self::F, self::Y, self::Z, self::C => 'Guayana',
            self::W => 'Insular',
        };
    }

    // Get sub-region (more specific)
    public function subRegion(): string
    {
        return match ($this) {
            self::A => 'Capital',
            self::G => 'Central (Valencia)',
            self::D => 'Central (Aragua)',
            self::M => 'Central (Miranda)',
            self::X => 'Litoral',
            self::I => 'Western Coast',
            self::R => 'Eastern Coast',
            self::O => 'Islands',
            self::N => 'Eastern',
            self::B => 'Northeast',
            self::K => 'Central-West (Lara)',
            self::U => 'Central-West (Yaracuy)',
            self::P => 'Central-West (Portuguesa)',
            self::H => 'Central-West (Cojedes)',
            self::J => 'Llanos',
            self::E => 'Western Llanos',
            self::V => 'Lake Maracaibo',
            self::L => 'Andes',
            self::S => 'Andes (Táchira)',
            self::T => 'Andes (Trujillo)',
            self::F => 'Guayana',
            self::Y => 'Delta',
            self::Z => 'Amazon',
            self::C => 'Llanos (Apure)',
            self::W => 'Caribbean Islands',
        };
    }

    // Get main area code for the state
    public function mainAreaCode(): string
    {
        return match ($this) {
            self::A => '212',  // Caracas
            self::B => '281',  // Anzoátegui
            self::C => '247',  // Apure
            self::D => '243',  // Aragua
            self::E => '273',  // Barinas
            self::F => '285',  // Bolívar
            self::G => '241',  // Carabobo
            self::H => '258',  // Cojedes
            self::I => '259',  // Falcón
            self::J => '246',  // Guárico
            self::K => '251',  // Lara
            self::L => '274',  // Mérida
            self::M => '212',  // Miranda (shares with Capital)
            self::N => '291',  // Monagas
            self::O => '295',  // Nueva Esparta
            self::P => '255',  // Portuguesa
            self::R => '293',  // Sucre
            self::S => '276',  // Táchira
            self::T => '272',  // Trujillo
            self::U => '251',  // Yaracuy (shares with Lara)
            self::V => '261',  // Zulia
            self::W => '212',  // Federal Dependencies (usually Caracas code)
            self::X => '212',  // Vargas (shares with Caracas)
            self::Y => '287',  // Delta Amacuro
            self::Z => '248',  // Amazonas
        };
    }

    // Get state by area code (simplified)
    public static function fromAreaCode(string $areaCode): ?self
    {
        $code = (int) $areaCode;

        $map = [
            212 => self::A,   // Caracas (also Miranda, Vargas)
            241 => self::G,   // Carabobo
            242 => self::G,
            243 => self::D,   // Aragua
            244 => self::D,
            245 => self::G,   // Also Aragua
            246 => self::J,   // Guárico
            247 => self::C,   // Apure (also Guárico)
            248 => self::Z,   // Amazonas
            251 => self::K,   // Lara (also Yaracuy)
            252 => self::K,
            253 => self::K,
            254 => self::K,
            255 => self::P,   // Portuguesa (also Lara)
            256 => self::P,
            257 => self::P,
            258 => self::H,   // Cojedes
            259 => self::I,   // Falcón
            261 => self::V,   // Zulia
            262 => self::V,
            263 => self::V,
            264 => self::V,
            265 => self::V,
            266 => self::V,
            267 => self::V,
            268 => self::I,   // Falcón (also Zulia)
            269 => self::I,
            271 => self::T,   // Trujillo (also Zulia)
            272 => self::T,
            273 => self::E,   // Barinas
            274 => self::L,   // Mérida (also Zulia)
            275 => self::L,   // Mérida (also Táchira, Zulia)
            276 => self::S,   // Táchira (also Mérida)
            277 => self::S,
            278 => self::E,   // Barinas
            279 => self::S,
            281 => self::B,   // Anzoátegui
            282 => self::B,
            283 => self::B,
            285 => self::F,   // Bolívar
            286 => self::F,
            287 => self::Y,   // Delta Amacuro
            288 => self::F,
            291 => self::N,   // Monagas
            292 => self::N,
            293 => self::R,   // Sucre
            294 => self::R,
            295 => self::O,   // Nueva Esparta
        ];

        return $map[$code] ?? null;
    }

    // Get population rank (approximate)
    public function populationRank(): int
    {
        return match ($this) {
            self::A => 1,   // Capital District - most populous
            self::V => 2,   // Zulia
            self::M => 3,   // Miranda
            self::G => 4,   // Carabobo
            self::L => 5,   // Lara
            self::A => 6,   // Aragua
            self::B => 7,   // Anzoátegui
            self::S => 8,   // Táchira
            self::K => 9,   // Bolívar
            self::N => 10,  // Monagas
            self::I => 11,  // Falcón
            self::P => 12,  // Portuguesa
            self::R => 13,  // Sucre
            self::T => 14,  // Trujillo
            self::U => 15,  // Yaracuy
            self::E => 16,  // Barinas
            self::O => 17,  // Nueva Esparta
            self::J => 18,  // Guárico
            self::H => 19,  // Cojedes
            self::X => 20,  // Vargas
            self::C => 21,  // Apure
            self::Y => 22,  // Delta Amacuro
            self::Z => 23,  // Amazonas
            self::W => 24,  // Federal Dependencies - least populous
        };
    }

    // Get area rank (approximate, in square km)
    public function areaRank(): int
    {
        return match ($this) {
            self::Z => 1,   // Amazonas - largest
            self::F => 2,   // Bolívar
            self::C => 3,   // Apure
            self::V => 4,   // Zulia
            self::Y => 5,   // Delta Amacuro
            self::B => 6,   // Anzoátegui
            self::I => 7,   // Falcón
            self::J => 8,   // Guárico
            self::S => 9,   // Táchira
            self::M => 10,  // Miranda
            self::L => 11,  // Mérida
            self::T => 12,  // Trujillo
            self::K => 13,  // Lara
            self::P => 14,  // Portuguesa
            self::N => 15,  // Monagas
            self::R => 16,  // Sucre
            self::E => 17,  // Barinas
            self::G => 18,  // Carabobo
            self::D => 19,  // Aragua
            self::U => 20,  // Yaracuy
            self::O => 21,  // Nueva Esparta
            self::X => 22,  // Vargas
            self::H => 23,  // Cojedes
            self::A => 24,  // Capital District
            self::W => 25,  // Federal Dependencies - smallest
        };
    }

    // Get whether state is coastal
    public function isCoastal(): bool
    {
        return match ($this) {
            self::A, self::X, self::M, self::V, self::I, self::R, self::O, self::B, self::N, self::Y, self::W => true,
            default => false,
        };
    }

    // Get whether state is in the Andes
    public function isAndean(): bool
    {
        return match ($this) {
            self::L, self::S, self::T => true,
            default => false,
        };
    }

    // Get whether state is in the Llanos (plains)
    public function isLlanos(): bool
    {
        return match ($this) {
            self::C, self::E, self::J, self::H, self::P, self::B, self::N, self::F => true,
            default => false,
        };
    }

    // Get major economic activity
    public function majorEconomicActivity(): string
    {
        return match ($this) {
            self::A => 'Government, Services, Finance',
            self::B => 'Oil, Petrochemicals, Agriculture',
            self::C => 'Cattle Ranching, Agriculture',
            self::D => 'Industry, Agriculture, Services',
            self::E => 'Agriculture, Cattle, Oil',
            self::F => 'Mining (Iron, Gold, Diamonds), Hydropower',
            self::G => 'Industry, Manufacturing, Agriculture',
            self::H => 'Agriculture, Cattle',
            self::I => 'Oil, Tourism, Fishing',
            self::J => 'Agriculture, Cattle',
            self::K => 'Commerce, Agriculture, Industry',
            self::L => 'Tourism, Agriculture, Education',
            self::M => 'Industry, Services, Agriculture',
            self::N => 'Oil, Gas, Agriculture',
            self::O => 'Tourism, Fishing',
            self::P => 'Agriculture, Cattle',
            self::R => 'Fishing, Tourism, Agriculture',
            self::S => 'Agriculture, Border Commerce, Oil',
            self::T => 'Agriculture, Coffee, Tourism',
            self::U => 'Agriculture, Sugar, Tourism',
            self::V => 'Oil, Petrochemicals, Fishing',
            self::W => 'Fishing, Tourism',
            self::X => 'Port, Tourism, Fishing',
            self::Y => 'Fishing, Oil, Agriculture',
            self::Z => 'Mining, Tourism, Indigenous Economy',
        };
    }

    // Get UNESCO World Heritage Sites in the state
    public function unescoSites(): array
    {
        return match ($this) {
            self::F => ['Ciudad Universitaria de Caracas', 'Canaima National Park'],
            self::I => ['Coro and its Port'],
            self::O => [], // La Asunción has historical significance
            default => [],
        };
    }

    // Get whether state has significant oil production
    public function hasOilProduction(): bool
    {
        return match ($this) {
            self::V, self::M, self::B, self::N, self::F, self::I, self::E, self::Y => true,
            default => false,
        };
    }

    // Get indigenous territories or presence
    public function indigenousGroups(): array
    {
        return match ($this) {
            self::Z => ['Yanomami', 'Piaroa', 'Guajibo'],
            self::Y => ['Warao'],
            self::F => ['Pemón', 'Kariña'],
            self::C => ['Guajibo', 'Pumé'],
            self::W => ['No permanent indigenous population'],
            default => [],
        };
    }

    // Get major natural features
    public function naturalFeatures(): array
    {
        return match ($this) {
            self::A => ['Ávila National Park', 'Guaire River'],
            self::F => ['Angel Falls (highest waterfall)', 'Canaima National Park', 'Orinoco River'],
            self::L => ['Sierra Nevada de Mérida', 'Pico Bolívar (highest peak)'],
            self::O => ['Margarita Island', 'Coche Island', 'Cubagua Island'],
            self::V => ['Lake Maracaibo', 'Catatumbo lightning'],
            self::Z => ['Orinoco River', 'Amazon rainforest'],
            self::I => ['Medanos de Coro (sand dunes)', 'Paraguaná Peninsula'],
            self::R => ['Mochima National Park', 'Araya Peninsula'],
            self::X => ['Caribbean coast', 'Ávila mountain'],
            default => [],
        };
    }
}
