<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum BillStatus: string
{
	case Draft         = 'Draft';
	case Sent          = 'Sent';
	case Unpaid        = 'Unpaid';
	case PartiallyPaid = 'Partially Paid';
	case Paid          = 'Paid';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Draft;

		$normalizedValue = strtolower(trim($value));
		return match ($normalizedValue) {
			'draft' => self::Draft,
			'sent' => self::Sent,
			'unpaid' => self::Unpaid,
			'partially paid', 'partiallypaid', 'partially_paid' => self::PartiallyPaid,
			'paid' => self::Paid,
			default => self::Draft,
		};
	}

	public static function values(): array
	{
		return [
			self::Draft->value,
			self::Sent->value,
			self::Unpaid->value,
			self::PartiallyPaid->value,
			self::Paid->value,
		];
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
			self::Draft->value         => 'Rascunho',
			self::Sent->value          => 'Enviada',
			self::Unpaid->value        => 'Não Paga',
			self::PartiallyPaid->value => 'Parcialmente Paga',
			self::Paid->value          => 'Paga',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Draft->value         => 'Draft',
			self::Sent->value          => 'Sent',
			self::Unpaid->value        => 'Unpaid',
			self::PartiallyPaid->value => 'Partially Paid',
			self::Paid->value          => 'Paid',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Draft->value         => 'Borrador',
			self::Sent->value          => 'Enviada',
			self::Unpaid->value        => 'No Pagada',
			self::PartiallyPaid->value => 'Parcialmente Pagada',
			self::Paid->value          => 'Pagada',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Draft->value         => 'مسودة',
			self::Sent->value          => 'مرسلة',
			self::Unpaid->value        => 'غير مدفوعة',
			self::PartiallyPaid->value => 'مدفوعة جزئياً',
			self::Paid->value          => 'مدفوعة',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Draft->value         => 'Kladde',
			self::Sent->value          => 'Sendt',
			self::Unpaid->value        => 'Ubetalt',
			self::PartiallyPaid->value => 'Delvist Betalt',
			self::Paid->value          => 'Betalt',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Draft->value         => 'Entwurf',
			self::Sent->value          => 'Gesendet',
			self::Unpaid->value        => 'Unbezahlt',
			self::PartiallyPaid->value => 'Teilweise Bezahlt',
			self::Paid->value          => 'Bezahlt',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Draft->value         => 'Brouillon',
			self::Sent->value          => 'Envoyée',
			self::Unpaid->value        => 'Impayée',
			self::PartiallyPaid->value => 'Partiellement Payée',
			self::Paid->value          => 'Payée',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Draft->value         => 'טיוטה',
			self::Sent->value          => 'נשלחה',
			self::Unpaid->value        => 'לא שולמה',
			self::PartiallyPaid->value => 'שולמה חלקית',
			self::Paid->value          => 'שולמה',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Draft->value         => 'Bozza',
			self::Sent->value          => 'Inviata',
			self::Unpaid->value        => 'Non Pagata',
			self::PartiallyPaid->value => 'Parzialmente Pagata',
			self::Paid->value          => 'Pagata',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Draft->value         => '下書き',
			self::Sent->value          => '送信済み',
			self::Unpaid->value        => '未払い',
			self::PartiallyPaid->value => '一部支払い済み',
			self::Paid->value          => '支払い済み',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Draft->value         => 'Concept',
			self::Sent->value          => 'Verzonden',
			self::Unpaid->value        => 'Onbetaald',
			self::PartiallyPaid->value => 'Gedeeltelijk Betaald',
			self::Paid->value          => 'Betaald',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Draft->value         => 'Szkic',
			self::Sent->value          => 'Wysłana',
			self::Unpaid->value        => 'Nieopłacona',
			self::PartiallyPaid->value => 'Częściowo Opłacona',
			self::Paid->value          => 'Opłacona',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Draft->value         => 'Черновик',
			self::Sent->value          => 'Отправлен',
			self::Unpaid->value        => 'Не Оплачен',
			self::PartiallyPaid->value => 'Частично Оплачен',
			self::Paid->value          => 'Оплачен',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Draft->value         => 'Taslak',
			self::Sent->value          => 'Gönderildi',
			self::Unpaid->value        => 'Ödenmemiş',
			self::PartiallyPaid->value => 'Kısmen Ödenmiş',
			self::Paid->value          => 'Ödenmiş',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Draft->value         => '草稿',
			self::Sent->value          => '已发送',
			self::Unpaid->value        => '未支付',
			self::PartiallyPaid->value => '部分支付',
			self::Paid->value          => '已支付',
		];
	}
}
