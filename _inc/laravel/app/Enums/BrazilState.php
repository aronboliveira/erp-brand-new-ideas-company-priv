<?php

namespace App\Enums;

enum BrazilState: string
{
	case AC = 'AC';
	case AL = 'AL';
	case AP = 'AP';
	case AM = 'AM';
	case BA = 'BA';
	case CE = 'CE';
	case DF = 'DF';
	case ES = 'ES';
	case GO = 'GO';
	case MA = 'MA';
	case MT = 'MT';
	case MS = 'MS';
	case MG = 'MG';
	case PA = 'PA';
	case PB = 'PB';
	case PR = 'PR';
	case PE = 'PE';
	case PI = 'PI';
	case RJ = 'RJ';
	case RN = 'RN';
	case RS = 'RS';
	case RO = 'RO';
	case RR = 'RR';
	case SC = 'SC';
	case SP = 'SP';
	case SE = 'SE';
	case TO = 'TO';

	public const DDD = [
		11,
		12,
		13,
		14,
		15,
		16,
		17,
		18,
		19, // São Paulo
		21,
		22,
		24, // Rio de Janeiro
		27,
		28, // Espírito Santo
		31,
		32,
		33,
		34,
		35,
		37,
		38, // Minas Gerais
		41,
		42,
		43,
		44,
		45,
		46, // Paraná
		47,
		48,
		49, // Santa Catarina
		51,
		53,
		54,
		55, // Rio Grande do Sul
		61, // Distrito Federal
		62,
		64, // Goiás
		63, // Tocantins
		65,
		66, // Mato Grosso
		67, // Mato Grosso do Sul
		68, // Acre
		69, // Rondônia
		71,
		73,
		74,
		75,
		77, // Bahia
		79, // Sergipe
		81,
		87, // Pernambuco
		82, // Alagoas
		83, // Paraíba
		84, // Rio Grande do Norte
		85,
		88, // Ceará
		86,
		89, // Piauí
		91,
		93,
		94, // Pará
		92,
		97, // Amazonas
		95, // Roraima
		96, // Amapá
		98,
		99  // Maranhão
	];

	public static function normalize(string|null|BrazilState $value): ?self
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
			'ACRE'                 => self::AC,
			'ALAGOAS'              => self::AL,
			'AMAPA'                => self::AP,
			'AMAPÁ'                => self::AP,
			'AMAZONAS'             => self::AM,
			'BAHIA'                => self::BA,
			'CEARA'                => self::CE,
			'CEARÁ'                => self::CE,
			'DISTRITO FEDERAL'     => self::DF,
			'ESPIRITO SANTO'       => self::ES,
			'ESPÍRITO SANTO'       => self::ES,
			'GOIAS'                => self::GO,
			'GOIÁS'                => self::GO,
			'MARANHAO'             => self::MA,
			'MARANHÃO'             => self::MA,
			'MATO GROSSO'          => self::MT,
			'MATO GROSSO DO SUL'   => self::MS,
			'MINAS GERAIS'         => self::MG,
			'PARA'                 => self::PA,
			'PARÁ'                 => self::PA,
			'PARAIBA'              => self::PB,
			'PARAÍBA'              => self::PB,
			'PARANA'               => self::PR,
			'PARANÁ'               => self::PR,
			'PERNAMBUCO'           => self::PE,
			'PIAUI'                => self::PI,
			'PIAUÍ'                => self::PI,
			'RIO DE JANEIRO'       => self::RJ,
			'RIO GRANDE DO NORTE'  => self::RN,
			'RIO GRANDE DO SUL'    => self::RS,
			'RONDONIA'             => self::RO,
			'RONDÔNIA'             => self::RO,
			'RORAIMA'              => self::RR,
			'SANTA CATARINA'       => self::SC,
			'SAO PAULO'            => self::SP,
			'SÃO PAULO'            => self::SP,
			'SERGIPE'              => self::SE,
			'TOCANTINS'            => self::TO,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::AC => 'Acre',
			self::AL => 'Alagoas',
			self::AP => 'Amapá',
			self::AM => 'Amazonas',
			self::BA => 'Bahia',
			self::CE => 'Ceará',
			self::DF => 'Distrito Federal',
			self::ES => 'Espírito Santo',
			self::GO => 'Goiás',
			self::MA => 'Maranhão',
			self::MT => 'Mato Grosso',
			self::MS => 'Mato Grosso do Sul',
			self::MG => 'Minas Gerais',
			self::PA => 'Pará',
			self::PB => 'Paraíba',
			self::PR => 'Paraná',
			self::PE => 'Pernambuco',
			self::PI => 'Piauí',
			self::RJ => 'Rio de Janeiro',
			self::RN => 'Rio Grande do Norte',
			self::RS => 'Rio Grande do Sul',
			self::RO => 'Rondônia',
			self::RR => 'Roraima',
			self::SC => 'Santa Catarina',
			self::SP => 'São Paulo',
			self::SE => 'Sergipe',
			self::TO => 'Tocantins',
		};
	}
}
