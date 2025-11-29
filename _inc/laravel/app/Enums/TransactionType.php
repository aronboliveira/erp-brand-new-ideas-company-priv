<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum TransactionType: string
{
	case Bill = 'bill';
	case Invoice = 'invoice';
	case Pos = 'pos';
	case Other = 'other';

	public static function values(): array
	{
		return [
			self::Bill->value,
			self::Invoice->value,
			self::Pos->value,
			self::Other->value,
		];
	}

	public static function normalize(string|null|self $value): ?self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null) return self::Other;

		return match (strtolower(trim($value))) {
			'bill', 'bills', 'billing' => self::Bill,
			'invoice', 'invoices', 'invoicing' => self::Invoice,
			'pos', 'point_of_sale', 'pointofsale' => self::Pos,
			'other', 'others', 'misc' => self::Other,
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
			self::Bill->value => 'Conta',
			self::Invoice->value => 'Fatura',
			self::Pos->value => 'Ponto de Venda',
			self::Other->value => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Bill->value => 'Bill',
			self::Invoice->value => 'Invoice',
			self::Pos->value => 'Point of Sale',
			self::Other->value => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Bill->value => 'Cuenta',
			self::Invoice->value => 'Factura',
			self::Pos->value => 'Punto de Venta',
			self::Other->value => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Bill->value => 'فاتورة',
			self::Invoice->value => 'فاتورة رسمية',
			self::Pos->value => 'نقطة البيع',
			self::Other->value => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Bill->value => 'Regning',
			self::Invoice->value => 'Faktura',
			self::Pos->value => 'Salgssted',
			self::Other->value => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Bill->value => 'Rechnung',
			self::Invoice->value => 'Faktura',
			self::Pos->value => 'Verkaufsstelle',
			self::Other->value => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Bill->value => 'Facture',
			self::Invoice->value => 'Facture Officielle',
			self::Pos->value => 'Point de Vente',
			self::Other->value => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Bill->value => 'חשבון',
			self::Invoice->value => 'חשבונית',
			self::Pos->value => 'נקודת מכירה',
			self::Other->value => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Bill->value => 'Conto',
			self::Invoice->value => 'Fattura',
			self::Pos->value => 'Punto Vendita',
			self::Other->value => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Bill->value => '請求書',
			self::Invoice->value => 'インボイス',
			self::Pos->value => '販売時点情報管理',
			self::Other->value => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Bill->value => 'Rekening',
			self::Invoice->value => 'Factuur',
			self::Pos->value => 'Verkooppunt',
			self::Other->value => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Bill->value => 'Rachunek',
			self::Invoice->value => 'Faktura',
			self::Pos->value => 'Punkt sprzedaży',
			self::Other->value => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Bill->value => 'Счет',
			self::Invoice->value => 'Накладная',
			self::Pos->value => 'Точка продаж',
			self::Other->value => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Bill->value => 'Fatura',
			self::Invoice->value => 'Resmi Fatura',
			self::Pos->value => 'Satış Noktası',
			self::Other->value => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Bill->value => '账单',
			self::Invoice->value => '发票',
			self::Pos->value => '销售点',
			self::Other->value => '其他',
		];
	}
}
