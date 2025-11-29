<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PosType: string
{
	case CardMachine      = 'card_machine';
	case CashRegister     = 'cash_register';
	case BankTerminal     = 'bank_terminal';
	case MobilePos        = 'mobile_pos';
	case TabletPos        = 'tablet_pos';
	case IntegratedSystem = 'integrated_system';
	case SelfCheckout     = 'self_checkout';
	case Kiosk            = 'kiosk';
	case WebPos           = 'web_pos';
	case PortableTerminal = 'portable_terminal';
	case SmartphonePos    = 'smartphone_pos';
	case VirtualTerminal  = 'virtual_terminal';
	case Other						= 'other';

	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function normalize(string|self|null $value): ?self
	{
		if ($value === null)
			return self::Other;
		if ($value instanceof self)
			return $value;
		$v = strtolower(trim((string) $value));
		if ($v === '')
			return null;

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Card Machine
			'card machine'           => self::CardMachine,
			'maquina de cartao'      => self::CardMachine,
			'máquina de cartão'      => self::CardMachine,
			'terminal de cartao'     => self::CardMachine,
			'terminal de cartão'     => self::CardMachine,
			'pinpad'                 => self::CardMachine,
			'pdv'                    => self::CardMachine,
			'pos terminal'           => self::CardMachine,

			// Cash Register
			'cash register'          => self::CashRegister,
			'caixa'                  => self::CashRegister,
			'caixa registradora'     => self::CashRegister,
			'register'               => self::CashRegister,
			'frente de caixa'        => self::CashRegister,
			'checkout'               => self::CashRegister,

			// Bank Terminal
			'bank terminal'          => self::BankTerminal,
			'terminal bancario'      => self::BankTerminal,
			'terminal bancário'      => self::BankTerminal,
			'tef'                    => self::BankTerminal,
			'transferencia eletronica' => self::BankTerminal,
			'transferência eletrônica' => self::BankTerminal,

			// Mobile POS
			'mobile'                 => self::MobilePos,
			'mobile terminal'        => self::MobilePos,
			'pos movel'              => self::MobilePos,
			'pos móvel'              => self::MobilePos,
			'portatil'               => self::MobilePos,
			'portátil'               => self::MobilePos,

			// Tablet POS
			'tablet'                 => self::TabletPos,
			'ipad'                   => self::TabletPos,
			'tablet terminal'        => self::TabletPos,

			// Integrated System
			'integrated'             => self::IntegratedSystem,
			'sistema integrado'      => self::IntegratedSystem,
			'erp'                    => self::IntegratedSystem,
			'management system'      => self::IntegratedSystem,
			'sistema de gestao'      => self::IntegratedSystem,
			'sistema de gestão'      => self::IntegratedSystem,

			// Self Checkout
			'self checkout'          => self::SelfCheckout,
			'self service'           => self::SelfCheckout,
			'auto atendimento'       => self::SelfCheckout,
			'checkout automatico'    => self::SelfCheckout,
			'checkout automático'    => self::SelfCheckout,

			// Kiosk
			'quiosque'               => self::Kiosk,
			'kiosk terminal'         => self::Kiosk,
			'interactive kiosk'      => self::Kiosk,

			// Web POS
			'web'                    => self::WebPos,
			'online pos'             => self::WebPos,
			'browser pos'            => self::WebPos,
			'cloud pos'              => self::WebPos,

			// Portable Terminal
			'portable'               => self::PortableTerminal,
			'wireless terminal'      => self::PortableTerminal,
			'terminal sem fio'       => self::PortableTerminal,

			// Smartphone POS
			'smartphone'             => self::SmartphonePos,
			'celular'                => self::SmartphonePos,
			'phone pos'              => self::SmartphonePos,

			// Virtual Terminal
			'virtual'                => self::VirtualTerminal,
			'terminal virtual'       => self::VirtualTerminal,
			'online terminal'        => self::VirtualTerminal,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::CardMachine      => 'Card Machine',
			self::CashRegister     => 'Cash Register',
			self::BankTerminal     => 'Bank Terminal',
			self::MobilePos        => 'Mobile POS',
			self::TabletPos        => 'Tablet POS',
			self::IntegratedSystem => 'Integrated System',
			self::SelfCheckout     => 'Self Checkout',
			self::Kiosk            => 'Kiosk',
			self::WebPos           => 'Web POS',
			self::PortableTerminal => 'Portable Terminal',
			self::SmartphonePos    => 'Smartphone POS',
			self::VirtualTerminal  => 'Virtual Terminal',
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
			self::CardMachine->value      => 'Máquina de Cartão',
			self::CashRegister->value     => 'Caixa Registradora',
			self::BankTerminal->value     => 'Terminal Bancário',
			self::MobilePos->value        => 'POS Móvel',
			self::TabletPos->value        => 'POS em Tablet',
			self::IntegratedSystem->value => 'Sistema Integrado',
			self::SelfCheckout->value     => 'Auto Atendimento',
			self::Kiosk->value            => 'Quiosque',
			self::WebPos->value           => 'POS Web',
			self::PortableTerminal->value => 'Terminal Portátil',
			self::SmartphonePos->value    => 'POS em Smartphone',
			self::VirtualTerminal->value  => 'Terminal Virtual',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::CardMachine->value      => 'Card Machine',
			self::CashRegister->value     => 'Cash Register',
			self::BankTerminal->value     => 'Bank Terminal',
			self::MobilePos->value        => 'Mobile POS',
			self::TabletPos->value        => 'Tablet POS',
			self::IntegratedSystem->value => 'Integrated System',
			self::SelfCheckout->value     => 'Self Checkout',
			self::Kiosk->value            => 'Kiosk',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'Portable Terminal',
			self::SmartphonePos->value    => 'Smartphone POS',
			self::VirtualTerminal->value  => 'Virtual Terminal',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::CardMachine->value      => 'Máquina de Tarjeta',
			self::CashRegister->value     => 'Caja Registradora',
			self::BankTerminal->value     => 'Terminal Bancario',
			self::MobilePos->value        => 'POS Móvil',
			self::TabletPos->value        => 'POS en Tableta',
			self::IntegratedSystem->value => 'Sistema Integrado',
			self::SelfCheckout->value     => 'Autoservicio',
			self::Kiosk->value            => 'Quiosco',
			self::WebPos->value           => 'POS Web',
			self::PortableTerminal->value => 'Terminal Portátil',
			self::SmartphonePos->value    => 'POS en Smartphone',
			self::VirtualTerminal->value  => 'Terminal Virtual',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::CardMachine->value      => 'آلة البطاقة',
			self::CashRegister->value     => 'سجل النقد',
			self::BankTerminal->value     => 'جهاز الصراف الآلي',
			self::MobilePos->value        => 'نقطة بيع محمولة',
			self::TabletPos->value        => 'نقطة بيع لوحية',
			self::IntegratedSystem->value => 'نظام متكامل',
			self::SelfCheckout->value     => 'الدفع الذاتي',
			self::Kiosk->value            => 'كشك',
			self::WebPos->value           => 'نقطة بيع ويب',
			self::PortableTerminal->value => 'جهاز محمول',
			self::SmartphonePos->value    => 'نقطة بيع هاتف',
			self::VirtualTerminal->value  => 'جهاز افتراضي',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::CardMachine->value      => 'Kortmaskine',
			self::CashRegister->value     => 'Kasseapparat',
			self::BankTerminal->value     => 'Bankterminal',
			self::MobilePos->value        => 'Mobil POS',
			self::TabletPos->value        => 'Tablet POS',
			self::IntegratedSystem->value => 'Integreret System',
			self::SelfCheckout->value     => 'Selvbetjening',
			self::Kiosk->value            => 'Kiosk',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'Bærbar Terminal',
			self::SmartphonePos->value    => 'Smartphone POS',
			self::VirtualTerminal->value  => 'Virtuel Terminal',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::CardMachine->value      => 'Kartenlesegerät',
			self::CashRegister->value     => 'Kasse',
			self::BankTerminal->value     => 'Bankterminal',
			self::MobilePos->value        => 'Mobiles POS',
			self::TabletPos->value        => 'Tablet POS',
			self::IntegratedSystem->value => 'Integriertes System',
			self::SelfCheckout->value     => 'Selbstbedienung',
			self::Kiosk->value            => 'Kiosk',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'Tragbarer Terminal',
			self::SmartphonePos->value    => 'Smartphone POS',
			self::VirtualTerminal->value  => 'Virtueller Terminal',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::CardMachine->value      => 'Terminal de Paiement',
			self::CashRegister->value     => 'Caisse Enregistreuse',
			self::BankTerminal->value     => 'Terminal Bancaire',
			self::MobilePos->value        => 'TPE Mobile',
			self::TabletPos->value        => 'TPE sur Tablette',
			self::IntegratedSystem->value => 'Système Intégré',
			self::SelfCheckout->value     => 'Caisse Libre-Service',
			self::Kiosk->value            => 'Kiosque',
			self::WebPos->value           => 'TPE Web',
			self::PortableTerminal->value => 'Terminal Portable',
			self::SmartphonePos->value    => 'TPE sur Smartphone',
			self::VirtualTerminal->value  => 'Terminal Virtuel',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::CardMachine->value      => 'מכונת כרטיסים',
			self::CashRegister->value     => 'קופה רושמת',
			self::BankTerminal->value     => 'מסוף בנק',
			self::MobilePos->value        => 'קופה ניידת',
			self::TabletPos->value        => 'קופה בטאבלט',
			self::IntegratedSystem->value => 'מערכת משולבת',
			self::SelfCheckout->value     => 'קופה עצמית',
			self::Kiosk->value            => 'קיוסק',
			self::WebPos->value           => 'קופה מקוונת',
			self::PortableTerminal->value => 'מסוף נייד',
			self::SmartphonePos->value    => 'קופה בסמארטפון',
			self::VirtualTerminal->value  => 'מסוף וירטואלי',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::CardMachine->value      => 'Terminale di Pagamento',
			self::CashRegister->value     => 'Registratore di Cassa',
			self::BankTerminal->value     => 'Terminale Bancario',
			self::MobilePos->value        => 'POS Mobile',
			self::TabletPos->value        => 'POS su Tablet',
			self::IntegratedSystem->value => 'Sistema Integrato',
			self::SelfCheckout->value     => 'Self Checkout',
			self::Kiosk->value            => 'Chiosco',
			self::WebPos->value           => 'POS Web',
			self::PortableTerminal->value => 'Terminale Portatile',
			self::SmartphonePos->value    => 'POS su Smartphone',
			self::VirtualTerminal->value  => 'Terminale Virtuale',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::CardMachine->value      => 'カード端末',
			self::CashRegister->value     => 'レジ',
			self::BankTerminal->value     => '銀行端末',
			self::MobilePos->value        => 'モバイルPOS',
			self::TabletPos->value        => 'タブレットPOS',
			self::IntegratedSystem->value => '統合システム',
			self::SelfCheckout->value     => 'セルフレジ',
			self::Kiosk->value            => 'キオスク',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'ポータブル端末',
			self::SmartphonePos->value    => 'スマートフォンPOS',
			self::VirtualTerminal->value  => '仮想端末',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::CardMachine->value      => 'Kaartapparaat',
			self::CashRegister->value     => 'Kassa',
			self::BankTerminal->value     => 'Bankterminal',
			self::MobilePos->value        => 'Mobiel POS',
			self::TabletPos->value        => 'Tablet POS',
			self::IntegratedSystem->value => 'Geïntegreerd Systeem',
			self::SelfCheckout->value     => 'Zelfbediening',
			self::Kiosk->value            => 'Kiosk',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'Draagbare Terminal',
			self::SmartphonePos->value    => 'Smartphone POS',
			self::VirtualTerminal->value  => 'Virtuele Terminal',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::CardMachine->value      => 'Terminal Płatniczy',
			self::CashRegister->value     => 'Kasa Fiskalna',
			self::BankTerminal->value     => 'Terminal Bankowy',
			self::MobilePos->value        => 'Mobilny POS',
			self::TabletPos->value        => 'POS na Tablecie',
			self::IntegratedSystem->value => 'Zintegrowany System',
			self::SelfCheckout->value     => 'Samodzielna Kasa',
			self::Kiosk->value            => 'Kiosk',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'Terminal Przenośny',
			self::SmartphonePos->value    => 'POS na Smartfonie',
			self::VirtualTerminal->value  => 'Terminal Wirtualny',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::CardMachine->value      => 'Карточный Терминал',
			self::CashRegister->value     => 'Кассовый Аппарат',
			self::BankTerminal->value     => 'Банковский Терминал',
			self::MobilePos->value        => 'Мобильный POS',
			self::TabletPos->value        => 'POS на Планшете',
			self::IntegratedSystem->value => 'Интегрированная Система',
			self::SelfCheckout->value     => 'Самообслуживание',
			self::Kiosk->value            => 'Киоск',
			self::WebPos->value           => 'Веб POS',
			self::PortableTerminal->value => 'Портативный Терминал',
			self::SmartphonePos->value    => 'POS на Смартфоне',
			self::VirtualTerminal->value  => 'Виртуальный Терминал',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::CardMachine->value      => 'Kartlı Ödeme Terminali',
			self::CashRegister->value     => 'POS Cihazı',
			self::BankTerminal->value     => 'Banka Terminali',
			self::MobilePos->value        => 'Mobil POS',
			self::TabletPos->value        => 'Tablet POS',
			self::IntegratedSystem->value => 'Entegre Sistem',
			self::SelfCheckout->value     => 'Self Servis',
			self::Kiosk->value            => 'Kiosk',
			self::WebPos->value           => 'Web POS',
			self::PortableTerminal->value => 'Taşınabilir Terminal',
			self::SmartphonePos->value    => 'Akıllı Telefon POS',
			self::VirtualTerminal->value  => 'Sanal Terminal',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::CardMachine->value      => '刷卡机',
			self::CashRegister->value     => '收银机',
			self::BankTerminal->value     => '银行终端',
			self::MobilePos->value        => '移动POS',
			self::TabletPos->value        => '平板POS',
			self::IntegratedSystem->value => '集成系统',
			self::SelfCheckout->value     => '自助结账',
			self::Kiosk->value            => '自助服务亭',
			self::WebPos->value           => '网页POS',
			self::PortableTerminal->value => '便携终端',
			self::SmartphonePos->value    => '手机POS',
			self::VirtualTerminal->value  => '虚拟终端',
		];
	}

	// Helper methods for business logic
	public function isMobile(): bool
	{
		return match ($this) {
			self::MobilePos, self::TabletPos, self::SmartphonePos, self::PortableTerminal => true,
			default => false,
		};
	}

	public function isStationary(): bool
	{
		return match ($this) {
			self::CashRegister, self::CardMachine, self::BankTerminal, self::IntegratedSystem, self::Kiosk => true,
			default => false,
		};
	}

	public function isVirtual(): bool
	{
		return match ($this) {
			self::WebPos, self::VirtualTerminal => true,
			default => false,
		};
	}

	public function requiresHardware(): bool
	{
		return match ($this) {
			self::WebPos, self::VirtualTerminal => false,
			default => true,
		};
	}

	public function getCategory(): string
	{
		return match ($this) {
			self::MobilePos, self::TabletPos, self::SmartphonePos, self::PortableTerminal => 'mobile',
			self::CashRegister, self::CardMachine, self::BankTerminal, self::IntegratedSystem => 'stationary',
			self::SelfCheckout, self::Kiosk => 'self_service',
			self::WebPos, self::VirtualTerminal => 'virtual',
		};
	}
}
