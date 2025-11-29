<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ConsumableType: string
{
	case Product = 'product';
	case Service = 'service';
	case Other = 'other';
	case Income = 'income';
	case Expense = 'expense';
	case Asset = 'asset';
	case Liability = 'liability';
	case Equity = 'equity';
	case CostsOfGoodsSold = 'costs of goods sold';

	public static function values(): array
	{
		return [
			self::Product->value,
			self::Service->value,
			self::Income->value,
			self::Expense->value,
			self::Asset->value,
			self::Liability->value,
			self::Equity->value,
			self::CostsOfGoodsSold->value,
			self::Other->value,
		];
	}

	public static function normalize(string|null|self $v): ?self
	{
		if ($v instanceof self)
			return $v;
		if ($v === null)
			return self::Other;
		$v = strtolower(trim($v));
		return match ($v) {
			'product', 'produto', 'producto' => self::Product,
			'service', 'serviço', 'servicio' => self::Service,
			'income', 'receita', 'ingreso' => self::Income,
			'expense', 'despesa', 'gasto' => self::Expense,
			'asset', 'ativo', 'activo' => self::Asset,
			'liability', 'passivo', 'obligación' => self::Liability,
			'equity', 'patrimônio', 'patrimonio' => self::Equity,
			'costs of goods sold', 'custo da mercadoria vendida', 'costo de bienes vendidos' => self::CostsOfGoodsSold,
			'other', 'outro', 'otro' => self::Other,
			default => null,
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
			self::Product->value => 'Produto',
			self::Service->value => 'Serviço',
			self::Income->value => 'Receita',
			self::Expense->value => 'Despesa',
			self::Asset->value => 'Ativo',
			self::Liability->value => 'Passivo',
			self::Equity->value => 'Patrimônio',
			self::CostsOfGoodsSold->value => 'Custo da Mercadoria Vendida',
			self::Other->value => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Product->value => 'Product',
			self::Service->value => 'Service',
			self::Income->value => 'Income',
			self::Expense->value => 'Expense',
			self::Asset->value => 'Asset',
			self::Liability->value => 'Liability',
			self::Equity->value => 'Equity',
			self::CostsOfGoodsSold->value => 'Costs of Goods Sold',
			self::Other->value => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Product->value => 'Producto',
			self::Service->value => 'Servicio',
			self::Income->value => 'Ingreso',
			self::Expense->value => 'Gasto',
			self::Asset->value => 'Activo',
			self::Liability->value => 'Pasivo',
			self::Equity->value => 'Patrimonio',
			self::CostsOfGoodsSold->value => 'Costo de Bienes Vendidos',
			self::Other->value => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Product->value => 'منتج',
			self::Service->value => 'خدمة',
			self::Income->value => 'دخل',
			self::Expense->value => 'مصروف',
			self::Asset->value => 'أصل',
			self::Liability->value => 'التزام',
			self::Equity->value => 'حقوق الملكية',
			self::CostsOfGoodsSold->value => 'تكلفة البضائع المباعة',
			self::Other->value => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Product->value => 'Produkt',
			self::Service->value => 'Service',
			self::Income->value => 'Indtægt',
			self::Expense->value => 'Udgift',
			self::Asset->value => 'Aktiv',
			self::Liability->value => 'Forpligtelse',
			self::Equity->value => 'Egenkapital',
			self::CostsOfGoodsSold->value => 'Vareomkostninger',
			self::Other->value => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Product->value => 'Produkt',
			self::Service->value => 'Dienstleistung',
			self::Income->value => 'Einkommen',
			self::Expense->value => 'Ausgabe',
			self::Asset->value => 'Vermögenswert',
			self::Liability->value => 'Verbindlichkeit',
			self::Equity->value => 'Eigenkapital',
			self::CostsOfGoodsSold->value => 'Wareneinsatzkosten',
			self::Other->value => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Product->value => 'Produit',
			self::Service->value => 'Service',
			self::Income->value => 'Revenu',
			self::Expense->value => 'Dépense',
			self::Asset->value => 'Actif',
			self::Liability->value => 'Passif',
			self::Equity->value => 'Capitaux propres',
			self::CostsOfGoodsSold->value => 'Coût des Marchandises Vendues',
			self::Other->value => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Product->value => 'מוצר',
			self::Service->value => 'שירות',
			self::Income->value => 'הכנסה',
			self::Expense->value => 'הוצאה',
			self::Asset->value => 'נכס',
			self::Liability->value => 'התחייבות',
			self::Equity->value => 'הון עצמי',
			self::CostsOfGoodsSold->value => 'עלות הסחורה שנמכרה',
			self::Other->value => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Product->value => 'Prodotto',
			self::Service->value => 'Servizio',
			self::Income->value => 'Reddito',
			self::Expense->value => 'Spesa',
			self::Asset->value => 'Attività',
			self::Liability->value => 'Passività',
			self::Equity->value => 'Patrimonio netto',
			self::CostsOfGoodsSold->value => 'Costo del Venduto',
			self::Other->value => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Product->value => '製品',
			self::Service->value => 'サービス',
			self::Income->value => '収入',
			self::Expense->value => '経費',
			self::Asset->value => '資産',
			self::Liability->value => '負債',
			self::Equity->value => '資本',
			self::CostsOfGoodsSold->value => '売上原価',
			self::Other->value => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Product->value => 'Product',
			self::Service->value => 'Dienst',
			self::Income->value => 'Inkomen',
			self::Expense->value => 'Uitgave',
			self::Asset->value => 'Activa',
			self::Liability->value => 'Verplichting',
			self::Equity->value => 'Eigen vermogen',
			self::CostsOfGoodsSold->value => 'Kosten van Verkochte Goederen',
			self::Other->value => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Product->value => 'Produkt',
			self::Service->value => 'Usługa',
			self::Income->value => 'Dochód',
			self::Expense->value => 'Wydatek',
			self::Asset->value => 'Aktywa',
			self::Liability->value => 'Zobowiązanie',
			self::Equity->value => 'Kapitał własny',
			self::CostsOfGoodsSold->value => 'Koszt Sprzedanych Towarów',
			self::Other->value => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Product->value => 'Продукт',
			self::Service->value => 'Услуга',
			self::Income->value => 'Доход',
			self::Expense->value => 'Расход',
			self::Asset->value => 'Актив',
			self::Liability->value => 'Обязательство',
			self::Equity->value => 'Собственный капитал',
			self::CostsOfGoodsSold->value => 'Себестоимость проданных товаров',
			self::Other->value => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Product->value => 'Ürün',
			self::Service->value => 'Hizmet',
			self::Income->value => 'Gelir',
			self::Expense->value => 'Gider',
			self::Asset->value => 'Varlık',
			self::Liability->value => 'Yükümlülük',
			self::Equity->value => 'Öz sermaye',
			self::CostsOfGoodsSold->value => 'Satılan Malın Maliyeti',
			self::Other->value => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Product->value => '产品',
			self::Service->value => '服务',
			self::Income->value => '收入',
			self::Expense->value => '支出',
			self::Asset->value => '资产',
			self::Liability->value => '负债',
			self::Equity->value => '权益',
			self::CostsOfGoodsSold->value => '销售成本',
			self::Other->value => '其他',
		];
	}
}
