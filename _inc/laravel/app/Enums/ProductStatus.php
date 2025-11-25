<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum ProductStatus: string
{
	case Active   = 'active';
	case Paused   = 'paused';
	case Inactive = 'inactive';
	case Undefined = 'undefined';

	public static function normalize(null|string|BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;

		$k = strtolower(trim((string)$v));

		$aliases = [
			'activo'      => 'active',
			'ativo'       => 'active',
			'pausado'     => 'paused',
			'inactivo'    => 'inactive',
			'inativo'     => 'inactive',
			'indefinido'  => 'undefined',
			'undefined'   => 'undefined',
		];

		$k = $aliases[$k] ?? $k;
		return self::tryFrom($k) ?? self::Undefined;
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
			self::Active->value   => 'Ativo',
			self::Paused->value   => 'Pausado',
			self::Inactive->value => 'Inativo',
			self::Undefined->value => 'Indefinido',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Active->value   => 'Active',
			self::Paused->value   => 'Paused',
			self::Inactive->value => 'Inactive',
			self::Undefined->value => 'Undefined',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Active->value   => 'Activo',
			self::Paused->value   => 'Pausado',
			self::Inactive->value => 'Inactivo',
			self::Undefined->value => 'Indefinido',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Active->value   => 'نشط',
			self::Paused->value   => 'متوقف',
			self::Inactive->value => 'غير نشط',
			self::Undefined->value => 'غير محدد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Active->value   => 'Aktiv',
			self::Paused->value   => 'Pauset',
			self::Inactive->value => 'Inaktiv',
			self::Undefined->value => 'Udefineret',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Active->value   => 'Aktiv',
			self::Paused->value   => 'Pausiert',
			self::Inactive->value => 'Inaktiv',
			self::Undefined->value => 'Undefiniert',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Active->value   => 'Actif',
			self::Paused->value   => 'En Pause',
			self::Inactive->value => 'Inactif',
			self::Undefined->value => 'Indéfini',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Active->value   => 'פעיל',
			self::Paused->value   => 'מושהה',
			self::Inactive->value => 'לא פעיל',
			self::Undefined->value => 'לא מוגדר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Active->value   => 'Attivo',
			self::Paused->value   => 'In Pausa',
			self::Inactive->value => 'Inattivo',
			self::Undefined->value => 'Indefinito',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Active->value   => 'アクティブ',
			self::Paused->value   => '一時停止',
			self::Inactive->value => '非アクティブ',
			self::Undefined->value => '未定義',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Active->value   => 'Actief',
			self::Paused->value   => 'Gepauzeerd',
			self::Inactive->value => 'Inactief',
			self::Undefined->value => 'Ongedefinieerd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Active->value   => 'Aktywny',
			self::Paused->value   => 'Wstrzymany',
			self::Inactive->value => 'Nieaktywny',
			self::Undefined->value => 'Niezdefiniowany',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Active->value   => 'Активный',
			self::Paused->value   => 'Приостановлен',
			self::Inactive->value => 'Неактивный',
			self::Undefined->value => 'Неопределен',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Active->value   => 'Aktif',
			self::Paused->value   => 'Duraklatıldı',
			self::Inactive->value => 'Etkin Değil',
			self::Undefined->value => 'Tanımsız',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Active->value   => '活跃',
			self::Paused->value   => '已暂停',
			self::Inactive->value => '非活跃',
			self::Undefined->value => '未定义',
		];
	}
}
