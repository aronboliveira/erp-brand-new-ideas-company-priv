<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum DeductionType: string
{
	case Legal      = 'legal';
	case Voluntary  = 'voluntary';
	case Judicial   = 'judicial';
	case Syndical   = 'syndical';
	case Benefit    = 'benefit';
	case Loan       = 'loan';
	case Advance    = 'advance';
	case Other      = 'other';

	public static function normalize(null|string|BackedEnum $v): ?self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Other;
		$k = strtolower(trim((string)$v));
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
			self::Legal->value     => 'Legal',
			self::Voluntary->value => 'Voluntária',
			self::Judicial->value  => 'Judicial',
			self::Syndical->value  => 'Sindical',
			self::Benefit->value   => 'Benefício',
			self::Loan->value      => 'Empréstimo',
			self::Advance->value   => 'Adiantamento',
			self::Other->value     => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Legal->value     => 'Legal',
			self::Voluntary->value => 'Voluntary',
			self::Judicial->value  => 'Judicial',
			self::Syndical->value  => 'Syndical',
			self::Benefit->value   => 'Benefit',
			self::Loan->value      => 'Loan',
			self::Advance->value   => 'Advance',
			self::Other->value     => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Legal->value     => 'Legal',
			self::Voluntary->value => 'Voluntaria',
			self::Judicial->value  => 'Judicial',
			self::Syndical->value  => 'Sindical',
			self::Benefit->value   => 'Beneficio',
			self::Loan->value      => 'Préstamo',
			self::Advance->value   => 'Adelanto',
			self::Other->value     => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Legal->value     => 'قانوني',
			self::Voluntary->value => 'طوعي',
			self::Judicial->value  => 'قضائي',
			self::Syndical->value  => 'نقابي',
			self::Benefit->value   => 'استحقاق',
			self::Loan->value      => 'قرض',
			self::Advance->value   => 'سلفة',
			self::Other->value     => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Legal->value     => 'Lovbestemt',
			self::Voluntary->value => 'Frivillig',
			self::Judicial->value  => 'Judiciel',
			self::Syndical->value  => 'Fagforening',
			self::Benefit->value   => 'Fordel',
			self::Loan->value      => 'Lån',
			self::Advance->value   => 'Forskud',
			self::Other->value     => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Legal->value     => 'Gesetzlich',
			self::Voluntary->value => 'Freiwillig',
			self::Judicial->value  => 'Gerichtlich',
			self::Syndical->value  => 'Gewerkschaft',
			self::Benefit->value   => 'Vorteil',
			self::Loan->value      => 'Darlehen',
			self::Advance->value   => 'Vorschuss',
			self::Other->value     => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Legal->value     => 'Légal',
			self::Voluntary->value => 'Volontaire',
			self::Judicial->value  => 'Judiciaire',
			self::Syndical->value  => 'Syndical',
			self::Benefit->value   => 'Avantage',
			self::Loan->value      => 'Prêt',
			self::Advance->value   => 'Avance',
			self::Other->value     => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Legal->value     => 'חוקי',
			self::Voluntary->value => 'התנדבותי',
			self::Judicial->value  => 'משפטי',
			self::Syndical->value  => 'איגוד מקצועי',
			self::Benefit->value   => 'הטבה',
			self::Loan->value      => 'הלוואה',
			self::Advance->value   => 'מקדמה',
			self::Other->value     => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Legal->value     => 'Legale',
			self::Voluntary->value => 'Volontario',
			self::Judicial->value  => 'Giudiziario',
			self::Syndical->value  => 'Sindacale',
			self::Benefit->value   => 'Beneficio',
			self::Loan->value      => 'Prestito',
			self::Advance->value   => 'Anticipo',
			self::Other->value     => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Legal->value     => '法定',
			self::Voluntary->value => '任意',
			self::Judicial->value  => '司法',
			self::Syndical->value  => '組合',
			self::Benefit->value   => '福利厚生',
			self::Loan->value      => 'ローン',
			self::Advance->value   => '前払い',
			self::Other->value     => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Legal->value     => 'Wettelijk',
			self::Voluntary->value => 'Vrijwillig',
			self::Judicial->value  => 'Gerechtelijk',
			self::Syndical->value  => 'Vakbond',
			self::Benefit->value   => 'Voordeel',
			self::Loan->value      => 'Lening',
			self::Advance->value   => 'Voorschot',
			self::Other->value     => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Legal->value     => 'Prawny',
			self::Voluntary->value => 'Dobrowolny',
			self::Judicial->value  => 'Sądowy',
			self::Syndical->value  => 'Związkowy',
			self::Benefit->value   => 'Benefit',
			self::Loan->value      => 'Pożyczka',
			self::Advance->value   => 'Zaliczka',
			self::Other->value     => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Legal->value     => 'Законный',
			self::Voluntary->value => 'Добровольный',
			self::Judicial->value  => 'Судебный',
			self::Syndical->value  => 'Профсоюзный',
			self::Benefit->value   => 'Льгота',
			self::Loan->value      => 'Займ',
			self::Advance->value   => 'Аванс',
			self::Other->value     => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Legal->value     => 'Yasal',
			self::Voluntary->value => 'Gönüllü',
			self::Judicial->value  => 'Yargı',
			self::Syndical->value  => 'Sendika',
			self::Benefit->value   => 'Yarar',
			self::Loan->value      => 'Kredi',
			self::Advance->value   => 'Avans',
			self::Other->value     => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Legal->value     => '法定',
			self::Voluntary->value => '自愿',
			self::Judicial->value  => '司法',
			self::Syndical->value  => '工会',
			self::Benefit->value   => '福利',
			self::Loan->value      => '贷款',
			self::Advance->value   => '预支',
			self::Other->value     => '其他',
		];
	}
}
