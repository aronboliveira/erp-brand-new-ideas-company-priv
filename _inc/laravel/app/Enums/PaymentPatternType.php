<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PaymentPatternType: string
{
	case Fixed      = 'fixed';
	case Percentage = 'percentage';
	case Other      = 'other';

	public static function normalize(string|PaymentPatternType|null $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Other;
		$v = strtolower(trim($v));
		return match ($v) {
			'fixed', 'fixo', 'fijo', 'fixe' => self::Fixed,
			'percentage', 'percent', 'porcentagem', 'porcentaje' => self::Percentage,
			'other', 'outro', 'otro', 'autre' => self::Other,
			default => null,
		};
	}

	public function isPercentage(): bool
	{
		return $this === self::Percentage;
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
			self::Fixed->value => 'Fixo',
			self::Percentage->value => 'Percentual',
			self::Other->value => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Fixed->value => 'Fixed',
			self::Percentage->value => 'Percentage',
			self::Other->value => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Fixed->value => 'Fijo',
			self::Percentage->value => 'Porcentaje',
			self::Other->value => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Fixed->value => 'ثابت',
			self::Percentage->value => 'النسبة المئوية',
			self::Other->value => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Fixed->value => 'Fast',
			self::Percentage->value => 'Procentdel',
			self::Other->value => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Fixed->value => 'Fest',
			self::Percentage->value => 'Prozentsatz',
			self::Other->value => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Fixed->value => 'Fixe',
			self::Percentage->value => 'Pourcentage',
			self::Other->value => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Fixed->value => 'קבוע',
			self::Percentage->value => 'אחוז',
			self::Other->value => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Fixed->value => 'Fisso',
			self::Percentage->value => 'Percentuale',
			self::Other->value => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Fixed->value => '固定',
			self::Percentage->value => 'パーセンテージ',
			self::Other->value => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Fixed->value => 'Vast',
			self::Percentage->value => 'Percentage',
			self::Other->value => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Fixed->value => 'Stały',
			self::Percentage->value => 'Procent',
			self::Other->value => 'Inny',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Fixed->value => 'Фиксированный',
			self::Percentage->value => 'Процент',
			self::Other->value => 'Другой',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Fixed->value => 'Sabit',
			self::Percentage->value => 'Yüzde',
			self::Other->value => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Fixed->value => '固定',
			self::Percentage->value => '百分比',
			self::Other->value => '其他',
		];
	}
}
