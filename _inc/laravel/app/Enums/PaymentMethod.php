<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PaymentMethod: string
{
	case BankTransfer = 'bank_transfer';
	case Pix          = 'pix';
	case Ted          = 'ted';
	case Doc          = 'doc';
	case WireTransfer = 'wire_transfer';
	case CardDebit    = 'card_debit';
	case CardCredit   = 'card_credit';
	case Cash         = 'cash';
	case Other        = 'other';
	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		match ($lang) {
			'pt-br' => fn() => self::labelsPtBr(),
			'pt' => fn() => self::labelsPtBr(),
			'es' => fn() => self::labelsEs(),
			'es-es' => fn() => self::labelsEs(),
			'ar' => fn() => self::labelsAr(),
			'ar-sa' => fn() => self::labelsAr(),
			'da' => fn() => self::labelsDa(),
			'da-dk' => fn() => self::labelsDa(),
			'de' => fn() => self::labelsDe(),
			'de-de' => fn() => self::labelsDe(),
			'fr' => fn() => self::labelsFr(),
			'fr-fr' => fn() => self::labelsFr(),
			'he' => fn() => self::labelsHe(),
			'he-il' => fn() => self::labelsHe(),
			'it' => fn() => self::labelsIt(),
			'it-it' => fn() => self::labelsIt(),
			'ja' => fn() => self::labelsJa(),
			'ja-jp' => fn() => self::labelsJa(),
			'nl' => fn() => self::labelsNl(),
			'nl-nl' => fn() => self::labelsNl(),
			'pl' => fn() => self::labelsPl(),
			'pl-pl' => fn() => self::labelsPl(),
			'ru' => fn() => self::labelsRu(),
			'ru-ru' => fn() => self::labelsRu(),
			'tr' => fn() => self::labelsTr(),
			'tr-tr' => fn() => self::labelsTr(),
			'zh' => fn() => self::labelsZh(),
			'zh-cn' => fn() => self::labelsZh(),
			default => fn() => self::labelsEn(),
		};
		return [
			self::BankTransfer->value => 'Bank Transfer',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Wire Transfer',
			self::CardDebit->value    => 'Card Debit',
			self::CardCredit->value   => 'Card Credit',
			self::Cash->value         => 'Cash',
			self::Other->value        => 'Other',
		];
	}
	public function labelsPtBr(): array
	{
		return [
			self::BankTransfer->value => 'Transferência Bancária',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Transferência Eletrônica',
			self::CardDebit->value    => 'Cartão de Débito',
			self::CardCredit->value   => 'Cartão de Crédito',
			self::Cash->value         => 'Dinheiro',
			self::Other->value        => 'Outro',
		];
	}
	public function labelsEn(): array
	{
		return [
			self::BankTransfer->value => 'Bank Transfer',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Wire Transfer',
			self::CardDebit->value    => 'Card Debit',
			self::CardCredit->value   => 'Card Credit',
			self::Cash->value         => 'Cash',
			self::Other->value        => 'Other',
		];
	}
	public function labelsEs(): array
	{
		return [
			self::BankTransfer->value => 'Transferencia Bancaria',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Transferencia Electrónica',
			self::CardDebit->value    => 'Tarjeta de Débito',
			self::CardCredit->value   => 'Tarjeta de Crédito',
			self::Cash->value         => 'Efectivo',
			self::Other->value        => 'Otro',
		];
	}
	public function labelsAr(): array
	{
		return [
			self::BankTransfer->value => 'التحويل المصرفي',
			self::Pix->value          => 'بيكس',
			self::Ted->value          => 'تيد',
			self::Doc->value          => 'دوك',
			self::WireTransfer->value => 'التحويل الإلكتروني',
			self::CardDebit->value    => 'بطاقة الخصم',
			self::CardCredit->value   => 'بطاقة الائتمان',
			self::Cash->value         => 'نقدا',
			self::Other->value        => 'آخر',
		];
	}
	public function labelsDa(): array
	{
		return [
			self::BankTransfer->value => 'Bankoverførsel',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Elektronisk Overførsel',
			self::CardDebit->value    => 'Debetkort',
			self::CardCredit->value   => 'Kreditkort',
			self::Cash->value         => 'Kontanter',
			self::Other->value        => 'Andet',
		];
	}
	public function labelsDe(): array
	{
		return [
			self::BankTransfer->value => 'Banküberweisung',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Drahtüberweisung',
			self::CardDebit->value    => 'Debitkarte',
			self::CardCredit->value   => 'Kreditkarte',
			self::Cash->value         => 'Bargeld',
			self::Other->value        => 'Andere',
		];
	}
	public function labelsFr(): array
	{
		return [
			self::BankTransfer->value => 'Virement Bancaire',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Virement Électronique',
			self::CardDebit->value    => 'Carte de Débit',
			self::CardCredit->value   => 'Carte de Crédit',
			self::Cash->value         => 'Espèces',
			self::Other->value        => 'Autre',
		];
	}
	public function labelsHe(): array
	{
		return [
			self::BankTransfer->value => 'העברה בנקאית',
			self::Pix->value          => 'פיקס',
			self::Ted->value          => 'טד',
			self::Doc->value          => 'דוק',
			self::WireTransfer->value => 'העברה אלקטרונית',
			self::CardDebit->value    => 'כרטיס חיוב',
			self::CardCredit->value   => 'כרטיס אשראי',
			self::Cash->value         => 'מזומן',
			self::Other->value        => 'אחר',
		];
	}
	public function labelsIt(): array
	{
		return [
			self::BankTransfer->value => 'Bonifico Bancario',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Bonifico Elettronico',
			self::CardDebit->value    => 'Carta di Debito',
			self::CardCredit->value   => 'Carta di Credito',
			self::Cash->value         => 'Contanti',
			self::Other->value        => 'Altro',
		];
	}
	public function labelsJa(): array
	{
		return [
			self::BankTransfer->value => '銀行振込',
			self::Pix->value          => 'ピックス',
			self::Ted->value          => 'テッド',
			self::Doc->value          => 'ドック',
			self::WireTransfer->value => '電信送金',
			self::CardDebit->value    => 'デビットカード',
			self::CardCredit->value   => 'クレジットカード',
			self::Cash->value         => '現金',
			self::Other->value        => 'その他',
		];
	}
	public function labelsNl(): array
	{
		return [
			self::BankTransfer->value => 'Bankoverschrijving',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Elektronische Overschrijving',
			self::CardDebit->value    => 'Debetkaart',
			self::CardCredit->value   => 'Creditcard',
			self::Cash->value         => 'Contant',
			self::Other->value        => 'Anders',
		];
	}
	public function labelsPl(): array
	{
		return [
			self::BankTransfer->value => 'Przelew Bankowy',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Przelew Elektroniczny',
			self::CardDebit->value    => 'Karta Debetowa',
			self::CardCredit->value   => 'Karta Kredytowa',
			self::Cash->value         => 'Gotówka',
			self::Other->value        => 'Inne',
		];
	}
	public function labelsRu(): array
	{
		return [
			self::BankTransfer->value => 'Банковский перевод',
			self::Pix->value          => 'Пикс',
			self::Ted->value          => 'ТЕД',
			self::Doc->value          => 'ДОК',
			self::WireTransfer->value => 'Электронный перевод',
			self::CardDebit->value    => 'Дебетовая карта',
			self::CardCredit->value   => 'Кредитная карта',
			self::Cash->value         => 'Наличные',
			self::Other->value        => 'Другое',
		];
	}
	public function labelsTr(): array
	{
		return [
			self::BankTransfer->value => 'Banka Havalesi',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => 'Elektronik Havale',
			self::CardDebit->value    => 'Banka Kartı',
			self::CardCredit->value   => 'Kredi Kartı',
			self::Cash->value         => 'Nakit',
			self::Other->value        => 'Diğer',
		];
	}
	public function labelsZh(): array
	{
		return [
			self::BankTransfer->value => '银行转账',
			self::Pix->value          => 'Pix',
			self::Ted->value          => 'TED',
			self::Doc->value          => 'DOC',
			self::WireTransfer->value => '电子转账',
			self::CardDebit->value    => '借记卡',
			self::CardCredit->value   => '信用卡',
			self::Cash->value         => '现金',
			self::Other->value        => '其他',
		];
	}
}
