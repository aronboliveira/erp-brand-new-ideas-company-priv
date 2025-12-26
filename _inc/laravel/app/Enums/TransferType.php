<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum TransferType: string
{
	case Salary        = 'salary';
	case SpecialPayment = 'special_payment';
	case TaxPayment    = 'tax_payment';
	case LoanPayment   = 'loan_payment';
	case Investment    = 'investment';
	case Withdrawal    = 'withdrawal';
	case Internal      = 'internal';
	case Rent          = 'rent';
	case Service       = 'service';
	case Purchase      = 'purchase';
	case Refund        = 'refund';
	case Other         = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Other;

		$v = strtolower(trim($value));

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Salary
			'salary'     => self::Salary,
			'salario'    => self::Salary,
			'salário'    => self::Salary,
			'wage'       => self::Salary,
			'payroll'    => self::Salary,

			// Special Payment
			'special_payment' => self::SpecialPayment,
			'special'         => self::SpecialPayment,
			'bonus'           => self::SpecialPayment,
			'extra'           => self::SpecialPayment,
			'vl_spl_pay'      => self::SpecialPayment,

			// Tax Payment
			'tax_payment' => self::TaxPayment,
			'tax'         => self::TaxPayment,
			'imposto'     => self::TaxPayment,
			'taxes'       => self::TaxPayment,
			'vl_tax_pay'  => self::TaxPayment,

			// Loan Payment
			'loan_payment' => self::LoanPayment,
			'loan'         => self::LoanPayment,
			'emprestimo'   => self::LoanPayment,
			'empréstimo'   => self::LoanPayment,
			'vl_ln_pay'    => self::LoanPayment,

			// Investment
			'investment' => self::Investment,
			'invest'     => self::Investment,
			'investimento' => self::Investment,

			// Withdrawal
			'withdrawal' => self::Withdrawal,
			'withdraw'   => self::Withdrawal,
			'saque'      => self::Withdrawal,
			'retirada'   => self::Withdrawal,

			// Internal
			'internal'   => self::Internal,
			'interno'    => self::Internal,
			'transfer'   => self::Internal,

			// Rent
			'rent'       => self::Rent,
			'aluguel'    => self::Rent,
			'rental'     => self::Rent,

			// Service
			'service'    => self::Service,
			'servico'    => self::Service,
			'serviço'    => self::Service,

			// Purchase
			'purchase'   => self::Purchase,
			'buy'        => self::Purchase,
			'compra'     => self::Purchase,

			// Refund
			'refund'     => self::Refund,
			'reembolso'  => self::Refund,
			'return'     => self::Refund,

			// Other
			'other'      => self::Other,
			'outro'      => self::Other,
			'misc'       => self::Other,
			'miscellaneous' => self::Other,
		];

		return $map[$v] ?? self::Other;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Salary         => 'Salary',
			self::SpecialPayment => 'Special Payment',
			self::TaxPayment     => 'Tax Payment',
			self::LoanPayment    => 'Loan Payment',
			self::Investment     => 'Investment',
			self::Withdrawal     => 'Withdrawal',
			self::Internal       => 'Internal Transfer',
			self::Rent           => 'Rent',
			self::Service        => 'Service',
			self::Purchase       => 'Purchase',
			self::Refund         => 'Refund',
			self::Other          => 'Other',
		};
	}

	public function isIncome(): bool
	{
		return match ($this) {
			self::Salary, self::Investment, self::Refund => true,
			default => false,
		};
	}

	public function isExpense(): bool
	{
		return match ($this) {
			self::TaxPayment, self::LoanPayment, self::Withdrawal, self::Rent, self::Service, self::Purchase => true,
			default => false,
		};
	}

	public function isTransfer(): bool
	{
		return match ($this) {
			self::Internal, self::SpecialPayment => true,
			default => false,
		};
	}

	public function getCategory(): string
	{
		return match ($this) {
			self::Salary, self::Investment, self::Refund => 'income',
			self::TaxPayment, self::LoanPayment, self::Withdrawal, self::Rent, self::Service, self::Purchase => 'expense',
			self::Internal, self::SpecialPayment => 'transfer',
			self::Other => 'other',
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
			self::Salary->value         => 'Salário',
			self::SpecialPayment->value => 'Pagamento Especial',
			self::TaxPayment->value     => 'Pagamento de Imposto',
			self::LoanPayment->value    => 'Pagamento de Empréstimo',
			self::Investment->value     => 'Investimento',
			self::Withdrawal->value     => 'Saque',
			self::Internal->value       => 'Transferência Interna',
			self::Rent->value           => 'Aluguel',
			self::Service->value        => 'Serviço',
			self::Purchase->value       => 'Compra',
			self::Refund->value         => 'Reembolso',
			self::Other->value          => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Salary->value         => 'Salary',
			self::SpecialPayment->value => 'Special Payment',
			self::TaxPayment->value     => 'Tax Payment',
			self::LoanPayment->value    => 'Loan Payment',
			self::Investment->value     => 'Investment',
			self::Withdrawal->value     => 'Withdrawal',
			self::Internal->value       => 'Internal Transfer',
			self::Rent->value           => 'Rent',
			self::Service->value        => 'Service',
			self::Purchase->value       => 'Purchase',
			self::Refund->value         => 'Refund',
			self::Other->value          => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Salary->value         => 'Salario',
			self::SpecialPayment->value => 'Pago Especial',
			self::TaxPayment->value     => 'Pago de Impuesto',
			self::LoanPayment->value    => 'Pago de Préstamo',
			self::Investment->value     => 'Inversión',
			self::Withdrawal->value     => 'Retiro',
			self::Internal->value       => 'Transferencia Interna',
			self::Rent->value           => 'Alquiler',
			self::Service->value        => 'Servicio',
			self::Purchase->value       => 'Compra',
			self::Refund->value         => 'Reembolso',
			self::Other->value          => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Salary->value         => 'راتب',
			self::SpecialPayment->value => 'دفعة خاصة',
			self::TaxPayment->value     => 'دفع الضرائب',
			self::LoanPayment->value    => 'دفع القرض',
			self::Investment->value     => 'استثمار',
			self::Withdrawal->value     => 'سحب',
			self::Internal->value       => 'تحويل داخلي',
			self::Rent->value           => 'إيجار',
			self::Service->value        => 'خدمة',
			self::Purchase->value       => 'شراء',
			self::Refund->value         => 'استرداد',
			self::Other->value          => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Salary->value         => 'Løn',
			self::SpecialPayment->value => 'Speciel Betaling',
			self::TaxPayment->value     => 'Skattebetaling',
			self::LoanPayment->value    => 'Lånebetaling',
			self::Investment->value     => 'Investering',
			self::Withdrawal->value     => 'Udbetaling',
			self::Internal->value       => 'Intern Overførsel',
			self::Rent->value           => 'Leje',
			self::Service->value        => 'Service',
			self::Purchase->value       => 'Køb',
			self::Refund->value         => 'Tilbagebetaling',
			self::Other->value          => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Salary->value         => 'Gehalt',
			self::SpecialPayment->value => 'Sonderzahlung',
			self::TaxPayment->value     => 'Steuerzahlung',
			self::LoanPayment->value    => 'Darlehenszahlung',
			self::Investment->value     => 'Investition',
			self::Withdrawal->value     => 'Auszahlung',
			self::Internal->value       => 'Interne Überweisung',
			self::Rent->value           => 'Miete',
			self::Service->value        => 'Dienstleistung',
			self::Purchase->value       => 'Kauf',
			self::Refund->value         => 'Rückerstattung',
			self::Other->value          => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Salary->value         => 'Salaire',
			self::SpecialPayment->value => 'Paiement Spécial',
			self::TaxPayment->value     => 'Paiement d\'Impôt',
			self::LoanPayment->value    => 'Paiement de Prêt',
			self::Investment->value     => 'Investissement',
			self::Withdrawal->value     => 'Retrait',
			self::Internal->value       => 'Transfert Interne',
			self::Rent->value           => 'Loyer',
			self::Service->value        => 'Service',
			self::Purchase->value       => 'Achat',
			self::Refund->value         => 'Remboursement',
			self::Other->value          => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Salary->value         => 'משכורת',
			self::SpecialPayment->value => 'תשלום מיוחד',
			self::TaxPayment->value     => 'תשלום מס',
			self::LoanPayment->value    => 'תשלום הלוואה',
			self::Investment->value     => 'השקעה',
			self::Withdrawal->value     => 'משיכה',
			self::Internal->value       => 'העברה פנימית',
			self::Rent->value           => 'שכירות',
			self::Service->value        => 'שירות',
			self::Purchase->value       => 'רכישה',
			self::Refund->value         => 'החזר',
			self::Other->value          => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Salary->value         => 'Stipendio',
			self::SpecialPayment->value => 'Pagamento Speciale',
			self::TaxPayment->value     => 'Pagamento Tasse',
			self::LoanPayment->value    => 'Pagamento Prestito',
			self::Investment->value     => 'Investimento',
			self::Withdrawal->value     => 'Prelievo',
			self::Internal->value       => 'Trasferimento Interno',
			self::Rent->value           => 'Affitto',
			self::Service->value        => 'Servizio',
			self::Purchase->value       => 'Acquisto',
			self::Refund->value         => 'Rimborso',
			self::Other->value          => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Salary->value         => '給与',
			self::SpecialPayment->value => '特別支払い',
			self::TaxPayment->value     => '税金支払い',
			self::LoanPayment->value    => 'ローン支払い',
			self::Investment->value     => '投資',
			self::Withdrawal->value     => '引き出し',
			self::Internal->value       => '内部振替',
			self::Rent->value           => '家賃',
			self::Service->value        => 'サービス',
			self::Purchase->value       => '購入',
			self::Refund->value         => '返金',
			self::Other->value          => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Salary->value         => 'Salaris',
			self::SpecialPayment->value => 'Speciale Betaling',
			self::TaxPayment->value     => 'Belastingbetaling',
			self::LoanPayment->value    => 'Leningbetaling',
			self::Investment->value     => 'Investering',
			self::Withdrawal->value     => 'Opname',
			self::Internal->value       => 'Interne Overboeking',
			self::Rent->value           => 'Huur',
			self::Service->value        => 'Dienst',
			self::Purchase->value       => 'Aankoop',
			self::Refund->value         => 'Terugbetaling',
			self::Other->value          => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Salary->value         => 'Wynagrodzenie',
			self::SpecialPayment->value => 'Płatność Specjalna',
			self::TaxPayment->value     => 'Płatność Podatku',
			self::LoanPayment->value    => 'Spłata Pożyczki',
			self::Investment->value     => 'Inwestycja',
			self::Withdrawal->value     => 'Wypłata',
			self::Internal->value       => 'Przelew Wewnętrzny',
			self::Rent->value           => 'Czynsz',
			self::Service->value        => 'Usługa',
			self::Purchase->value       => 'Zakup',
			self::Refund->value         => 'Zwrot',
			self::Other->value          => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Salary->value         => 'Зарплата',
			self::SpecialPayment->value => 'Специальный Платеж',
			self::TaxPayment->value     => 'Налоговый Платеж',
			self::LoanPayment->value    => 'Платеж по Кредиту',
			self::Investment->value     => 'Инвестиция',
			self::Withdrawal->value     => 'Снятие',
			self::Internal->value       => 'Внутренний Перевод',
			self::Rent->value           => 'Аренда',
			self::Service->value        => 'Услуга',
			self::Purchase->value       => 'Покупка',
			self::Refund->value         => 'Возврат',
			self::Other->value          => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Salary->value         => 'Maaş',
			self::SpecialPayment->value => 'Özel Ödeme',
			self::TaxPayment->value     => 'Vergi Ödemesi',
			self::LoanPayment->value    => 'Kredi Ödemesi',
			self::Investment->value     => 'Yatırım',
			self::Withdrawal->value     => 'Para Çekme',
			self::Internal->value       => 'Dahili Transfer',
			self::Rent->value           => 'Kira',
			self::Service->value        => 'Hizmet',
			self::Purchase->value       => 'Satın Alma',
			self::Refund->value         => 'İade',
			self::Other->value          => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Salary->value         => '工资',
			self::SpecialPayment->value => '特殊付款',
			self::TaxPayment->value     => '税务付款',
			self::LoanPayment->value    => '贷款付款',
			self::Investment->value     => '投资',
			self::Withdrawal->value     => '取款',
			self::Internal->value       => '内部转账',
			self::Rent->value           => '租金',
			self::Service->value        => '服务',
			self::Purchase->value       => '购买',
			self::Refund->value         => '退款',
			self::Other->value          => '其他',
		];
	}
}
