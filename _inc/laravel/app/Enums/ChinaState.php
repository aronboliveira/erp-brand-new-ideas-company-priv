<?php

namespace App\Enums;

enum ChinaState: string
{
	case BJ = 'BJ';
	case TJ = 'TJ';
	case HE = 'HE';
	case SX = 'SX';
	case NM = 'NM';
	case LN = 'LN';
	case JL = 'JL';
	case HL = 'HL';
	case SH = 'SH';
	case JS = 'JS';
	case ZJ = 'ZJ';
	case AH = 'AH';
	case FJ = 'FJ';
	case JX = 'JX';
	case SD = 'SD';
	case HA = 'HA';
	case HB = 'HB';
	case HN = 'HN';
	case GD = 'GD';
	case GX = 'GX';
	case HI = 'HI';
	case CQ = 'CQ';
	case SC = 'SC';
	case GZ = 'GZ';
	case YN = 'YN';
	case XZ = 'XZ';
	case SN = 'SN';
	case GS = 'GS';
	case QH = 'QH';
	case NX = 'NX';
	case XJ = 'XJ';
	case TW = 'TW';
	case HK = 'HK';
	case MO = 'MO';

	public static function normalize(?string $value): ?self
	{
		$v = strtoupper(trim((string) $value));
		if ($v === '')
			return null;

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Beijing
			'BEIJING' => self::BJ,
			'BEIJING SHI' => self::BJ,
			'北京' => self::BJ,
			'北京市' => self::BJ,

			// Tianjin
			'TIANJIN' => self::TJ,
			'TIANJIN SHI' => self::TJ,
			'天津' => self::TJ,
			'天津市' => self::TJ,

			// Hebei
			'HEBEI' => self::HE,
			'河北' => self::HE,
			'河北省' => self::HE,

			// Shanxi
			'SHANXI' => self::SX,
			'山西' => self::SX,
			'山西省' => self::SX,

			// Inner Mongolia
			'NEI MONGOL' => self::NM,
			'INNER MONGOLIA' => self::NM,
			'内蒙古' => self::NM,
			'内蒙古自治区' => self::NM,

			// Liaoning
			'LIAONING' => self::LN,
			'辽宁' => self::LN,
			'辽宁省' => self::LN,

			// Jilin
			'JILIN' => self::JL,
			'吉林' => self::JL,
			'吉林省' => self::JL,

			// Heilongjiang
			'HEILONGJIANG' => self::HL,
			'黑龙江' => self::HL,
			'黑龙江省' => self::HL,

			// Shanghai
			'SHANGHAI' => self::SH,
			'SHANGHAI SHI' => self::SH,
			'上海' => self::SH,
			'上海市' => self::SH,

			// Jiangsu
			'JIANGSU' => self::JS,
			'江苏' => self::JS,
			'江苏省' => self::JS,

			// Zhejiang
			'ZHEJIANG' => self::ZJ,
			'浙江' => self::ZJ,
			'浙江省' => self::ZJ,

			// Anhui
			'ANHUI' => self::AH,
			'安徽' => self::AH,
			'安徽省' => self::AH,

			// Fujian
			'FUJIAN' => self::FJ,
			'福建' => self::FJ,
			'福建省' => self::FJ,

			// Jiangxi
			'JIANGXI' => self::JX,
			'江西' => self::JX,
			'江西省' => self::JX,

			// Shandong
			'SHANDONG' => self::SD,
			'山东' => self::SD,
			'山东省' => self::SD,

			// Henan
			'HENAN' => self::HA,
			'河南' => self::HA,
			'河南省' => self::HA,

			// Hubei
			'HUBEI' => self::HB,
			'湖北' => self::HB,
			'湖北省' => self::HB,

			// Hunan
			'HUNAN' => self::HN,
			'湖南' => self::HN,
			'湖南省' => self::HN,

			// Guangdong
			'GUANGDONG' => self::GD,
			'广东' => self::GD,
			'广东省' => self::GD,

			// Guangxi
			'GUANGXI' => self::GX,
			'GUANGXI ZHUANG' => self::GX,
			'广西' => self::GX,
			'广西壮族自治区' => self::GX,

			// Hainan
			'HAINAN' => self::HI,
			'海南' => self::HI,
			'海南省' => self::HI,

			// Chongqing
			'CHONGQING' => self::CQ,
			'CHONGQING SHI' => self::CQ,
			'重庆' => self::CQ,
			'重庆市' => self::CQ,

			// Sichuan
			'SICHUAN' => self::SC,
			'四川' => self::SC,
			'四川省' => self::SC,

			// Guizhou
			'GUIZHOU' => self::GZ,
			'贵州' => self::GZ,
			'贵州省' => self::GZ,

			// Yunnan
			'YUNNAN' => self::YN,
			'云南' => self::YN,
			'云南省' => self::YN,

			// Tibet
			'XIZANG' => self::XZ,
			'TIBET' => self::XZ,
			'西藏' => self::XZ,
			'西藏自治区' => self::XZ,

			// Shaanxi
			'SHAANXI' => self::SN,
			'陕西' => self::SN,
			'陕西省' => self::SN,

			// Gansu
			'GANSU' => self::GS,
			'甘肃' => self::GS,
			'甘肃省' => self::GS,

			// Qinghai
			'QINGHAI' => self::QH,
			'青海' => self::QH,
			'青海省' => self::QH,

			// Ningxia
			'NINGXIA' => self::NX,
			'宁夏' => self::NX,
			'宁夏回族自治区' => self::NX,

			// Xinjiang
			'XINJIANG' => self::XJ,
			'XINJIANG UYGUR' => self::XJ,
			'新疆' => self::XJ,
			'新疆维吾尔自治区' => self::XJ,

			// Taiwan
			'TAIWAN' => self::TW,
			'台湾' => self::TW,
			'台湾省' => self::TW,

			// Hong Kong
			'HONG KONG' => self::HK,
			'HONG KONG SAR' => self::HK,
			'香港' => self::HK,
			'香港特别行政区' => self::HK,

			// Macao
			'MACAO' => self::MO,
			'MACAO SAR' => self::MO,
			'MACAO SPECIAL ADMINISTRATIVE REGION' => self::MO,
			'澳门' => self::MO,
			'澳门特别行政区' => self::MO,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::BJ => 'Beijing',
			self::TJ => 'Tianjin',
			self::HE => 'Hebei',
			self::SX => 'Shanxi',
			self::NM => 'Inner Mongolia',
			self::LN => 'Liaoning',
			self::JL => 'Jilin',
			self::HL => 'Heilongjiang',
			self::SH => 'Shanghai',
			self::JS => 'Jiangsu',
			self::ZJ => 'Zhejiang',
			self::AH => 'Anhui',
			self::FJ => 'Fujian',
			self::JX => 'Jiangxi',
			self::SD => 'Shandong',
			self::HA => 'Henan',
			self::HB => 'Hubei',
			self::HN => 'Hunan',
			self::GD => 'Guangdong',
			self::GX => 'Guangxi',
			self::HI => 'Hainan',
			self::CQ => 'Chongqing',
			self::SC => 'Sichuan',
			self::GZ => 'Guizhou',
			self::YN => 'Yunnan',
			self::XZ => 'Tibet',
			self::SN => 'Shaanxi',
			self::GS => 'Gansu',
			self::QH => 'Qinghai',
			self::NX => 'Ningxia',
			self::XJ => 'Xinjiang',
			self::TW => 'Taiwan',
			self::HK => 'Hong Kong',
			self::MO => 'Macao',
		};
	}

	public function labelZh(): string
	{
		return match ($this) {
			self::BJ => '北京',
			self::TJ => '天津',
			self::HE => '河北',
			self::SX => '山西',
			self::NM => '内蒙古',
			self::LN => '辽宁',
			self::JL => '吉林',
			self::HL => '黑龙江',
			self::SH => '上海',
			self::JS => '江苏',
			self::ZJ => '浙江',
			self::AH => '安徽',
			self::FJ => '福建',
			self::JX => '江西',
			self::SD => '山东',
			self::HA => '河南',
			self::HB => '湖北',
			self::HN => '湖南',
			self::GD => '广东',
			self::GX => '广西',
			self::HI => '海南',
			self::CQ => '重庆',
			self::SC => '四川',
			self::GZ => '贵州',
			self::YN => '云南',
			self::XZ => '西藏',
			self::SN => '陕西',
			self::GS => '甘肃',
			self::QH => '青海',
			self::NX => '宁夏',
			self::XJ => '新疆',
			self::TW => '台湾',
			self::HK => '香港',
			self::MO => '澳门',
		};
	}

	public function labelFullZh(): string
	{
		return match ($this) {
			self::BJ => '北京市',
			self::TJ => '天津市',
			self::HE => '河北省',
			self::SX => '山西省',
			self::NM => '内蒙古自治区',
			self::LN => '辽宁省',
			self::JL => '吉林省',
			self::HL => '黑龙江省',
			self::SH => '上海市',
			self::JS => '江苏省',
			self::ZJ => '浙江省',
			self::AH => '安徽省',
			self::FJ => '福建省',
			self::JX => '江西省',
			self::SD => '山东省',
			self::HA => '河南省',
			self::HB => '湖北省',
			self::HN => '湖南省',
			self::GD => '广东省',
			self::GX => '广西壮族自治区',
			self::HI => '海南省',
			self::CQ => '重庆市',
			self::SC => '四川省',
			self::GZ => '贵州省',
			self::YN => '云南省',
			self::XZ => '西藏自治区',
			self::SN => '陕西省',
			self::GS => '甘肃省',
			self::QH => '青海省',
			self::NX => '宁夏回族自治区',
			self::XJ => '新疆维吾尔自治区',
			self::TW => '台湾省',
			self::HK => '香港特别行政区',
			self::MO => '澳门特别行政区',
		};
	}
}
