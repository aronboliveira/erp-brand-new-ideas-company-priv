<?php

namespace App\Enums;

enum PortugalState: string
{
	case AV = 'AV';
	case BJ = 'BJ';
	case BR = 'BR';
	case BG = 'BG';
	case CB = 'CB';
	case CR = 'CR';
	case CT = 'CT';
	case EV = 'EV';
	case FR = 'FR';
	case GD = 'GD';
	case LV = 'LV';
	case LS = 'LS';
	case PT = 'PT';
	case PS = 'PS';
	case ST = 'ST';
	case SB = 'SB';
	case VC = 'VC';
	case VR = 'VR';
	case VS = 'VS';
	case AC = 'AC';
	case MD = 'MD';

	public static function normalize(?string $value): ?self
	{
		$v = strtoupper(trim((string) $value));
		if ($v === '')
			return null;

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			'AVEIRO' => self::AV,
			'BEJA' => self::BJ,
			'BRAGA' => self::BR,
			'BRAGANCA' => self::BG,
			'BRAGANÇA' => self::BG,
			'CASTELO BRANCO' => self::CB,
			'COIMBRA' => self::CR,
			'SANTARÉM' => self::ST,
			'SANTAREM' => self::ST,
			'SANTARÉM' => self::ST,
			'ÉVORA' => self::EV,
			'EVORA' => self::EV,
			'FARO' => self::FR,
			'GUARDA' => self::GD,
			'LEIRIA' => self::LV,
			'LISBOA' => self::LS,
			'LISBON' => self::LS,
			'PORTALEGRE' => self::PT,
			'PORTO' => self::PS,
			'SETÚBAL' => self::SB,
			'SETUBAL' => self::SB,
			'VIANA DO CASTELO' => self::VC,
			'VILA REAL' => self::VR,
			'VISEU' => self::VS,
			'AÇORES' => self::AC,
			'ACORES' => self::AC,
			'AZORES' => self::AC,
			'MADEIRA' => self::MD,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::AV => 'Aveiro',
			self::BJ => 'Beja',
			self::BR => 'Braga',
			self::BG => 'Bragança',
			self::CB => 'Castelo Branco',
			self::CR => 'Coimbra',
			self::CT => 'Santarém',
			self::EV => 'Évora',
			self::FR => 'Faro',
			self::GD => 'Guarda',
			self::LV => 'Leiria',
			self::LS => 'Lisboa',
			self::PT => 'Portalegre',
			self::PS => 'Porto',
			self::ST => 'Santarém',
			self::SB => 'Setúbal',
			self::VC => 'Viana do Castelo',
			self::VR => 'Vila Real',
			self::VS => 'Viseu',
			self::AC => 'Açores',
			self::MD => 'Madeira',
		};
	}
}
