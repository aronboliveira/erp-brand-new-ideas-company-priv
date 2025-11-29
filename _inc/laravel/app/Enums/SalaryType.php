<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum SalaryType: string
{
	case CLT        = 'clt';
	case PJ         = 'pj';
	case Internship = 'internship';
	case Freelancer = 'freelancer';
	case Other      = 'other';

	public static function normalize(string|null|self $v): ?self
	{
		if ($v instanceof self)
			return $v;
		if ($v === null) return self::Other;
		$v = strtolower(trim($v));
		return match ($v) {
			'clt', 'consolidação das leis do trabalho', 'consolidation of labor laws' => self::CLT,
			'pj', 'pessoa jurídica', 'legal entity'                                   => self::PJ,
			'internship', 'estágio', 'internato'                                      => self::Internship,
			'freelancer', 'freelance', 'autônomo'                                     => self::Freelancer,
			'other', 'outro', 'outros'                                               => self::Other,
			default => self::Other,
		};
	}

	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::labelsPtBr(),
			'es', 'es-es' => self::labelsEs(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'he', 'he-il' => self::labelsHe(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'nl', 'nl-nl' => self::labelsNl(),
			'pl', 'pl-pl' => self::labelsPl(),
			'ru', 'ru-ru' => self::labelsRu(),
			'tr', 'tr-tr' => self::labelsTr(),
			'zh', 'zh-cn' => self::labelsZh(),
			default => self::labelsEn(),
		};
	}

	public static function labelsPtBr(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Estágio',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Internship',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Pasantía',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'تدريب',
			self::Freelancer->value => 'العمل الحر',
			self::Other->value => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Praktik',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Praktikum',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Stage',
			self::Freelancer->value => 'Freelance',
			self::Other->value => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'התמחות',
			self::Freelancer->value => 'פרילנסר',
			self::Other->value => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Tirocinio',
			self::Freelancer->value => 'Freelance',
			self::Other->value => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'インターンシップ',
			self::Freelancer->value => 'フリーランス',
			self::Other->value => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Stage',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Staż',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Стажировка',
			self::Freelancer->value => 'Фрилансер',
			self::Other->value => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => 'Staj',
			self::Freelancer->value => 'Freelancer',
			self::Other->value => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::CLT->value => 'CLT',
			self::PJ->value => 'PJ',
			self::Internship->value => '实习',
			self::Freelancer->value => '自由职业者',
			self::Other->value => '其他',
		];
	}
}
