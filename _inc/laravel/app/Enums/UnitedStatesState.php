<?php

namespace App\Enums;

enum UnitedStatesState: string
{
	case AL = 'AL';
	case AK = 'AK';
	case AZ = 'AZ';
	case AR = 'AR';
	case CA = 'CA';
	case CO = 'CO';
	case CT = 'CT';
	case DE = 'DE';
	case FL = 'FL';
	case GA = 'GA';
	case HI = 'HI';
	case ID = 'ID';
	case IL = 'IL';
	case IN = 'IN';
	case IA = 'IA';
	case KS = 'KS';
	case KY = 'KY';
	case LA = 'LA';
	case ME = 'ME';
	case MD = 'MD';
	case MA = 'MA';
	case MI = 'MI';
	case MN = 'MN';
	case MS = 'MS';
	case MO = 'MO';
	case MT = 'MT';
	case NE = 'NE';
	case NV = 'NV';
	case NH = 'NH';
	case NJ = 'NJ';
	case NM = 'NM';
	case NY = 'NY';
	case NC = 'NC';
	case ND = 'ND';
	case OH = 'OH';
	case OK = 'OK';
	case OR = 'OR';
	case PA = 'PA';
	case RI = 'RI';
	case SC = 'SC';
	case SD = 'SD';
	case TN = 'TN';
	case TX = 'TX';
	case UT = 'UT';
	case VT = 'VT';
	case VA = 'VA';
	case WA = 'WA';
	case WV = 'WV';
	case WI = 'WI';
	case WY = 'WY';
	case DC = 'DC';

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return null;
		$v = strtoupper(trim((string) $value));
		if ($v === '')
			return null;

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			'ALABAMA' => self::AL,
			'ALASKA' => self::AK,
			'ARIZONA' => self::AZ,
			'ARKANSAS' => self::AR,
			'CALIFORNIA' => self::CA,
			'COLORADO' => self::CO,
			'CONNECTICUT' => self::CT,
			'DELAWARE' => self::DE,
			'FLORIDA' => self::FL,
			'GEORGIA' => self::GA,
			'HAWAII' => self::HI,
			'IDAHO' => self::ID,
			'ILLINOIS' => self::IL,
			'INDIANA' => self::IN,
			'IOWA' => self::IA,
			'KANSAS' => self::KS,
			'KENTUCKY' => self::KY,
			'LOUISIANA' => self::LA,
			'MAINE' => self::ME,
			'MARYLAND' => self::MD,
			'MASSACHUSETTS' => self::MA,
			'MICHIGAN' => self::MI,
			'MINNESOTA' => self::MN,
			'MISSISSIPPI' => self::MS,
			'MISSOURI' => self::MO,
			'MONTANA' => self::MT,
			'NEBRASKA' => self::NE,
			'NEVADA' => self::NV,
			'NEW HAMPSHIRE' => self::NH,
			'NEW JERSEY' => self::NJ,
			'NEW MEXICO' => self::NM,
			'NEW YORK' => self::NY,
			'NORTH CAROLINA' => self::NC,
			'NORTH DAKOTA' => self::ND,
			'OHIO' => self::OH,
			'OKLAHOMA' => self::OK,
			'OREGON' => self::OR,
			'PENNSYLVANIA' => self::PA,
			'RHODE ISLAND' => self::RI,
			'SOUTH CAROLINA' => self::SC,
			'SOUTH DAKOTA' => self::SD,
			'TENNESSEE' => self::TN,
			'TEXAS' => self::TX,
			'UTAH' => self::UT,
			'VERMONT' => self::VT,
			'VIRGINIA' => self::VA,
			'WASHINGTON' => self::WA,
			'WEST VIRGINIA' => self::WV,
			'WISCONSIN' => self::WI,
			'WYOMING' => self::WY,
			'DISTRICT OF COLUMBIA' => self::DC,
			'WASHINGTON DC' => self::DC,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::AL => 'Alabama',
			self::AK => 'Alaska',
			self::AZ => 'Arizona',
			self::AR => 'Arkansas',
			self::CA => 'California',
			self::CO => 'Colorado',
			self::CT => 'Connecticut',
			self::DE => 'Delaware',
			self::FL => 'Florida',
			self::GA => 'Georgia',
			self::HI => 'Hawaii',
			self::ID => 'Idaho',
			self::IL => 'Illinois',
			self::IN => 'Indiana',
			self::IA => 'Iowa',
			self::KS => 'Kansas',
			self::KY => 'Kentucky',
			self::LA => 'Louisiana',
			self::ME => 'Maine',
			self::MD => 'Maryland',
			self::MA => 'Massachusetts',
			self::MI => 'Michigan',
			self::MN => 'Minnesota',
			self::MS => 'Mississippi',
			self::MO => 'Missouri',
			self::MT => 'Montana',
			self::NE => 'Nebraska',
			self::NV => 'Nevada',
			self::NH => 'New Hampshire',
			self::NJ => 'New Jersey',
			self::NM => 'New Mexico',
			self::NY => 'New York',
			self::NC => 'North Carolina',
			self::ND => 'North Dakota',
			self::OH => 'Ohio',
			self::OK => 'Oklahoma',
			self::OR => 'Oregon',
			self::PA => 'Pennsylvania',
			self::RI => 'Rhode Island',
			self::SC => 'South Carolina',
			self::SD => 'South Dakota',
			self::TN => 'Tennessee',
			self::TX => 'Texas',
			self::UT => 'Utah',
			self::VT => 'Vermont',
			self::VA => 'Virginia',
			self::WA => 'Washington',
			self::WV => 'West Virginia',
			self::WI => 'Wisconsin',
			self::WY => 'Wyoming',
			self::DC => 'District of Columbia',
		};
	}
}
