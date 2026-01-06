<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum Frequency: string
{
	case Monthly     = 'monthly';
	case Weekly      = 'weekly';
	case Biweekly    = 'biweekly';
	case Quaternaly  = 'quaternaly';
	case Semimonthly = 'semimonthly';
	case Semestral   = 'semestral';
	case Annual      = 'annual';
	case Once        = 'once';
	case Variable    = 'variable';
	case Hourly      = 'hourly';

	public static function normalize(null|string|BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Variable;

		$k = strtolower(trim((string)$v));
		$aliases = [
			'semiannual'    => 'semestral',
			'semi-annual'   => 'semestral',
			'quaternaly'    => 'quaternaly',
			'quarterly'     => 'quaternaly',
			'quarteraly'    => 'quaternaly',
			'quarter-ly'    => 'quaternaly',
			'bi-monthly'    => 'semimonthly',
			'bimonthly'     => 'semimonthly',
			'half-yearly'    => 'semestral',
			'half yearly'   => 'semestral',
			'semi_monthly'  => 'semimonthly',
			'semi-monthly'  => 'semimonthly',
			'bimestral'     => 'biweekly',
			'fortnightly'   => 'biweekly',
			'yearly'        => 'annual',
			'annual'				=> 'annual',
			'mensal'        => 'monthly',
			'semanal'       => 'weekly',
			'quinzenal'     => 'biweekly',
			'semestral'     => 'semestral',
			'anual'         => 'annual',
			'uma_vez'       => 'once',
			'variável'      => 'variable',
			'horária'       => 'hourly',
		];

		$k = $aliases[$k] ?? $k;
		return self::tryFrom($k);
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
			self::Monthly->value     => 'Mensal',
			self::Weekly->value      => 'Semanal',
			self::Biweekly->value    => 'Quinzenal',
			self::Semimonthly->value => 'Bimestral',
			self::Semestral->value   => 'Semestral',
			self::Annual->value      => 'Anual',
			self::Once->value        => 'Uma Vez',
			self::Variable->value    => 'Variável',
			self::Hourly->value      => 'Horária',
			self::Quaternaly->value      => 'Anual',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Monthly->value     => 'Monthly',
			self::Weekly->value      => 'Weekly',
			self::Biweekly->value    => 'Biweekly',
			self::Semimonthly->value => 'Semimonthly',
			self::Semestral->value   => 'Semestral',
			self::Annual->value      => 'Annual',
			self::Once->value        => 'Once',
			self::Variable->value    => 'Variable',
			self::Hourly->value      => 'Hourly',
			self::Quaternaly->value      => 'Quaternaly',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Monthly->value     => 'Mensual',
			self::Weekly->value      => 'Semanal',
			self::Biweekly->value    => 'Quincenal',
			self::Semimonthly->value => 'Bimestral',
			self::Semestral->value   => 'Semestral',
			self::Annual->value      => 'Anual',
			self::Once->value        => 'Una Vez',
			self::Variable->value    => 'Variable',
			self::Hourly->value      => 'Por Hora',
			self::Quaternaly->value      => 'Trimestral',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Monthly->value     => 'شهري',
			self::Weekly->value      => 'أسبوعي',
			self::Biweekly->value    => 'كل أسبوعين',
			self::Semimonthly->value => 'نصف شهري',
			self::Semestral->value   => 'نصف سنوي',
			self::Annual->value      => 'سنوي',
			self::Once->value        => 'مرة واحدة',
			self::Variable->value    => 'متغير',
			self::Hourly->value      => 'كل ساعة',
			self::Quaternaly->value      => 'ربع سنوي',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Monthly->value     => 'Månedlig',
			self::Weekly->value      => 'Ugentlig',
			self::Biweekly->value    => 'Hver Anden Uge',
			self::Semimonthly->value => 'Halvmånedlig',
			self::Semestral->value   => 'Halvårlig',
			self::Annual->value      => 'Årlig',
			self::Once->value        => 'En Gang',
			self::Variable->value    => 'Variabel',
			self::Hourly->value      => 'Hver Time',
			self::Quaternaly->value      => 'Kvartalsvis',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Monthly->value     => 'Monatlich',
			self::Weekly->value      => 'Wöchentlich',
			self::Biweekly->value    => 'Zweiwöchentlich',
			self::Semimonthly->value => 'Halbmonatlich',
			self::Semestral->value   => 'Halbjährlich',
			self::Annual->value      => 'Jährlich',
			self::Once->value        => 'Einmalig',
			self::Variable->value    => 'Variabel',
			self::Hourly->value      => 'Stündlich',
			self::Quaternaly->value      => 'Vierteljährlich',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Monthly->value     => 'Mensuel',
			self::Weekly->value      => 'Hebdomadaire',
			self::Biweekly->value    => 'Bimensuel',
			self::Semimonthly->value => 'Bimestriel',
			self::Semestral->value   => 'Semestriel',
			self::Annual->value      => 'Annuel',
			self::Once->value        => 'Une Fois',
			self::Variable->value    => 'Variable',
			self::Hourly->value      => 'Horaire',
			self::Quaternaly->value      => 'Trimestriel',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Monthly->value     => 'חודשי',
			self::Weekly->value      => 'שבועי',
			self::Biweekly->value    => 'דו-שבועי',
			self::Semimonthly->value => 'דו-חודשי',
			self::Semestral->value   => 'חצי שנתי',
			self::Annual->value      => 'שנתי',
			self::Once->value        => 'פעם אחת',
			self::Variable->value    => 'משתנה',
			self::Hourly->value      => 'שעתי',
			self::Quaternaly->value      => 'רבעוני',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Monthly->value     => 'Mensile',
			self::Weekly->value      => 'Settimanale',
			self::Biweekly->value    => 'Bisettimanale',
			self::Semimonthly->value => 'Bimestrale',
			self::Semestral->value   => 'Semestrale',
			self::Annual->value      => 'Annuale',
			self::Once->value        => 'Una Volta',
			self::Variable->value    => 'Variabile',
			self::Hourly->value      => 'Orario',
			self::Quaternaly->value      => 'Trimestrale',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Monthly->value     => '月次',
			self::Weekly->value      => '週次',
			self::Biweekly->value    => '隔週',
			self::Semimonthly->value => '半月次',
			self::Semestral->value   => '半期',
			self::Annual->value      => '年次',
			self::Once->value        => '一度',
			self::Variable->value    => '変動',
			self::Hourly->value      => '時間ごと',
			self::Quaternaly->value      => '四半期ごと',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Monthly->value     => 'Maandelijks',
			self::Weekly->value      => 'Wekelijks',
			self::Biweekly->value    => 'Tweewekelijks',
			self::Semimonthly->value => 'Halfmaandelijks',
			self::Semestral->value   => 'Halfjaarlijks',
			self::Annual->value      => 'Jaarlijks',
			self::Once->value        => 'Eenmalig',
			self::Variable->value    => 'Variabel',
			self::Hourly->value      => 'Uurlijks',
			self::Quaternaly->value      => 'Kwartaal',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Monthly->value     => 'Miesięczny',
			self::Weekly->value      => 'Tygodniowy',
			self::Biweekly->value    => 'Dwutygodniowy',
			self::Semimonthly->value => 'Półmiesięczny',
			self::Semestral->value   => 'Półroczny',
			self::Annual->value      => 'Roczny',
			self::Once->value        => 'Jednorazowy',
			self::Variable->value    => 'Zmienny',
			self::Hourly->value      => 'Godzinowy',
			self::Quaternaly->value      => 'Kwartalny',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Monthly->value     => 'Ежемесячно',
			self::Weekly->value      => 'Еженедельно',
			self::Biweekly->value    => 'Раз в Две Недели',
			self::Semimonthly->value => 'Дважды в Месяц',
			self::Semestral->value   => 'Раз в Полгода',
			self::Annual->value      => 'Ежегодно',
			self::Once->value        => 'Один Раз',
			self::Variable->value    => 'Переменный',
			self::Hourly->value      => 'Почасовой',
			self::Quaternaly->value      => 'Ежеквартально',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Monthly->value     => 'Aylık',
			self::Weekly->value      => 'Haftalık',
			self::Biweekly->value    => 'İki Haftada Bir',
			self::Semimonthly->value => 'Ayda İki Kez',
			self::Semestral->value   => 'Altı Aylık',
			self::Annual->value      => 'Yıllık',
			self::Once->value        => 'Bir Kez',
			self::Variable->value    => 'Değişken',
			self::Hourly->value      => 'Saatlik',
			self::Quaternaly->value      => 'Üç Aylık',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Monthly->value     => '每月',
			self::Weekly->value      => '每周',
			self::Biweekly->value    => '每两周',
			self::Semimonthly->value => '每半月',
			self::Semestral->value   => '每半年',
			self::Annual->value      => '每年',
			self::Once->value        => '一次',
			self::Variable->value    => '可变',
			self::Hourly->value      => '每小时',
			self::Quaternaly->value      => '每季度',
		];
	}
}
