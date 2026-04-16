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
	case Benefit      = 'Benefit';
	case Other        = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Other;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;
		$map = [
			// Bank Transfer
			'bank_transfer' => self::BankTransfer,
			'bank'          => self::BankTransfer,
			'transfer'      => self::BankTransfer,
			'bank transfer' => self::BankTransfer,

			// Pix
			'pix'           => self::Pix,

			// TED
			'ted'           => self::Ted,

			// DOC
			'doc'           => self::Doc,

			// Wire Transfer
			'wire_transfer' => self::WireTransfer,
			'wire'          => self::WireTransfer,
			'wire transfer' => self::WireTransfer,
			'electronic'    => self::WireTransfer,

			// Card Debit
			'card_debit'    => self::CardDebit,
			'debit'         => self::CardDebit,
			'debit card'    => self::CardDebit,
			'cartao debito' => self::CardDebit,
			'cartão débito' => self::CardDebit,

			// Card Credit
			'card_credit'   => self::CardCredit,
			'credit'        => self::CardCredit,
			'credit card'   => self::CardCredit,
			'cartao credito' => self::CardCredit,
			'cartão crédito' => self::CardCredit,

			// Cash
			'cash'          => self::Cash,
			'dinheiro'      => self::Cash,
			'money'         => self::Cash,
			'efectivo'      => self::Cash,
			'especie'       => self::Cash,

			// Other
			'other'         => self::Other,
			'outro'         => self::Other,
			'otro'          => self::Other,
		];

		return $map[$v] ?? self::Other;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function isCard(): bool
	{
		return match ($this) {
			self::CardDebit, self::CardCredit => true,
			default => false,
		};
	}

	public function isBankTransfer(): bool
	{
		return match ($this) {
			self::BankTransfer, self::Pix, self::Ted, self::Doc, self::WireTransfer => true,
			default => false,
		};
	}

	public function isInstant(): bool
	{
		return match ($this) {
			self::Pix, self::Cash => true,
			default => false,
		};
	}

	public function requiresProcessing(): bool
	{
		return match ($this) {
			self::BankTransfer, self::Ted, self::Doc, self::WireTransfer, self::CardDebit, self::CardCredit => true,
			default => false,
		};
	}

	public function getCategory(): string
	{
		return match ($this) {
			self::CardDebit, self::CardCredit => 'card',
			self::BankTransfer, self::Pix, self::Ted, self::Doc, self::WireTransfer => 'bank',
			self::Cash => 'cash',
			self::Other => 'other',
		};
	}

	public function label(): string
	{
		return match ($this) {
			self::BankTransfer => 'Bank Transfer',
			self::Pix          => 'Pix',
			self::Ted          => 'TED',
			self::Doc          => 'DOC',
			self::WireTransfer => 'Wire Transfer',
			self::CardDebit    => 'Card Debit',
			self::CardCredit   => 'Card Credit',
			self::Cash         => 'Cash',
			self::Other        => 'Other',
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

	public static function labelsEn(): array
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

	public static function labelsEs(): array
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

	public static function labelsAr(): array
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

	public static function labelsDa(): array
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

	public static function labelsDe(): array
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

	public static function labelsFr(): array
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

	public static function labelsHe(): array
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

	public static function labelsIt(): array
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

	public static function labelsJa(): array
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

	public static function labelsNl(): array
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

	public static function labelsPl(): array
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

	public static function labelsRu(): array
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

	public static function labelsTr(): array
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

	public static function labelsZh(): array
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
