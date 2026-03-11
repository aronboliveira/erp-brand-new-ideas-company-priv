<?php

namespace App\Enums;

use App\Config\Constants\PermissionsConstants as PMC;
use App\Config\Constants\DatabaseConstants;

enum UserType: string
{
	case SuperAdmin = PMC::SA;
	case Admin      = PMC::ADM;
	case Company    = PMC::CPN;
	case Client     = PMC::CL;
	case Customer   = PMC::CT;
	case Vendor     = PMC::VD;
	case Accountant = PMC::ACT;
	case Hr         = PMC::HR;
	case Employee   = PMC::EMP;

	public static function normalize(string|null|self $value): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Customer;

		$v = mb_strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// SuperAdmin
			'superadmin'            => self::SuperAdmin,
			'super_admin'           => self::SuperAdmin,
			'super admin'           => self::SuperAdmin,
			'super-administrator'   => self::SuperAdmin,
			'superadministrator'    => self::SuperAdmin,
			'super_administrator'   => self::SuperAdmin,
			'super administrator'   => self::SuperAdmin,
			'sa'                    => self::SuperAdmin,
			'superusuario'          => self::SuperAdmin,
			'superusuário'          => self::SuperAdmin,
			'administrador_geral'   => self::SuperAdmin,
			'administrador_global'  => self::SuperAdmin,
			'root'                  => self::SuperAdmin,

			// Admin
			'admin'                 => self::Admin,
			'administrator'         => self::Admin,
			'administrador'         => self::Admin,
			'administrateur'        => self::Admin,
			'adm'                   => self::Admin,
			'administração'         => self::Admin,
			'administracion'        => self::Admin,
			'manager'               => self::Admin,
			'gerente'               => self::Admin,

			// Company
			'company'               => self::Company,
			'empresa'               => self::Company,
			'companhia'             => self::Company,
			'firma'                 => self::Company,
			'business'              => self::Company,
			'enterprise'            => self::Company,
			'corporation'           => self::Company,
			'cpn'                   => self::Company,

			// Client
			'client'                => self::Client,
			'cliente'               => self::Client,
			'cl'                    => self::Client,
			'buyer'                 => self::Client,
			'purchaser'             => self::Client,
			'comprador'             => self::Client,
			'acquirente'            => self::Client,

			// Customer
			'customer'              => self::Customer,
			'consumer'              => self::Customer,
			'cust'                  => self::Customer,
			'ct'                    => self::Customer,
			'end_user'              => self::Customer,
			'usuário_final'         => self::Customer,
			'usuario_final'         => self::Customer,

			// Vendor
			'vendor'                => self::Vendor,
			'vendedor'              => self::Vendor,
			'fornecedor'            => self::Vendor,
			'supplier'              => self::Vendor,
			'vd'                    => self::Vendor,
			'seller'                => self::Vendor,
			'proveedor'             => self::Vendor,
			'fournisseur'           => self::Vendor,
			'lieferant'             => self::Vendor,

			// Accountant
			'accountant'            => self::Accountant,
			'contador'              => self::Accountant,
			'contable'              => self::Accountant,
			'contabile'             => self::Accountant,
			'accounting'            => self::Accountant,
			'act'                   => self::Accountant,
			'bookkeeper'            => self::Accountant,
			'contabilidade'         => self::Accountant,
			'contabilidad'          => self::Accountant,

			// HR
			'hr'                    => self::Hr,
			'humanresources'        => self::Hr,
			'human_resources'       => self::Hr,
			'human resources'       => self::Hr,
			'rh'                    => self::Hr,
			'recursos_humanos'      => self::Hr,
			'recursos humanos'      => self::Hr,
			'personal'              => self::Hr,
			'personnel'             => self::Hr,
			'staff'                 => self::Hr,
			'hr_manager'            => self::Hr,
			'hr manager'            => self::Hr,
			'human resource'        => self::Hr,
			'humain'                => self::Hr,
			'risorseumane'          => self::Hr,
			'risorse umane'         => self::Hr,
			'personalabteilung'     => self::Hr,

			// Employee
			'employee'              => self::Employee,
			'funcionário'           => self::Employee,
			'funcionario'           => self::Employee,
			'empleado'              => self::Employee,
			'collaborator'          => self::Employee,
			'colaborador'           => self::Employee,
			'mitarbeiter'           => self::Employee,
			'dipendente'            => self::Employee,
			'employé'               => self::Employee,
			'werknemer'             => self::Employee,
			'pracownik'             => self::Employee,
			'сотрудник'             => self::Employee,
		];

		return $map[$v] ?? self::Customer;
	}

	public static function isValidValue(string $value): bool
	{
		return self::tryFrom($value) !== null;
	}

	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function names(): array
	{
		return array_column(self::cases(), 'name');
	}

	public function label(): string
	{
		return match ($this) {
			self::SuperAdmin => 'Super Admin',
			self::Admin      => 'Admin',
			self::Company    => 'Company',
			self::Client     => 'Client',
			self::Customer   => 'Customer',
			self::Vendor     => 'Vendor',
			self::Accountant => 'Accountant',
			self::Hr         => 'HR',
			self::Employee   => 'Employee',
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
			self::SuperAdmin->value => 'Super Administrador',
			self::Admin->value      => 'Administrador',
			self::Company->value    => 'Empresa',
			self::Client->value     => 'Cliente',
			self::Customer->value   => 'Cliente',
			self::Vendor->value     => 'Fornecedor',
			self::Accountant->value => 'Contador',
			self::Hr->value         => 'RH',
			self::Employee->value   => 'Funcionário',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::SuperAdmin->value => 'Super Admin',
			self::Admin->value      => 'Admin',
			self::Company->value    => 'Company',
			self::Client->value     => 'Client',
			self::Customer->value   => 'Customer',
			self::Vendor->value     => 'Vendor',
			self::Accountant->value => 'Accountant',
			self::Hr->value         => 'HR',
			self::Employee->value   => 'Employee',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::SuperAdmin->value => 'Super Administrador',
			self::Admin->value      => 'Administrador',
			self::Company->value    => 'Empresa',
			self::Client->value     => 'Cliente',
			self::Customer->value   => 'Cliente',
			self::Vendor->value     => 'Proveedor',
			self::Accountant->value => 'Contador',
			self::Hr->value         => 'RRHH',
			self::Employee->value   => 'Empleado',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::SuperAdmin->value => 'مدير عام',
			self::Admin->value      => 'مدير',
			self::Company->value    => 'شركة',
			self::Client->value     => 'عميل',
			self::Customer->value   => 'زبون',
			self::Vendor->value     => 'بائع',
			self::Accountant->value => 'محاسب',
			self::Hr->value         => 'موارد بشرية',
			self::Employee->value   => 'موظف',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::SuperAdmin->value => 'Super Administrator',
			self::Admin->value      => 'Administrator',
			self::Company->value    => 'Virksomhed',
			self::Client->value     => 'Klient',
			self::Customer->value   => 'Kunde',
			self::Vendor->value     => 'Leverandør',
			self::Accountant->value => 'Revisor',
			self::Hr->value         => 'HR',
			self::Employee->value   => 'Medarbejder',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::SuperAdmin->value => 'Super Administrator',
			self::Admin->value      => 'Administrator',
			self::Company->value    => 'Unternehmen',
			self::Client->value     => 'Kunde',
			self::Customer->value   => 'Kunde',
			self::Vendor->value     => 'Lieferant',
			self::Accountant->value => 'Buchhalter',
			self::Hr->value         => 'Personalabteilung',
			self::Employee->value   => 'Mitarbeiter',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::SuperAdmin->value => 'Super Administrateur',
			self::Admin->value      => 'Administrateur',
			self::Company->value    => 'Entreprise',
			self::Client->value     => 'Client',
			self::Customer->value   => 'Client',
			self::Vendor->value     => 'Fournisseur',
			self::Accountant->value => 'Comptable',
			self::Hr->value         => 'RH',
			self::Employee->value   => 'Employé',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::SuperAdmin->value => 'מנהל על',
			self::Admin->value      => 'מנהל',
			self::Company->value    => 'חברה',
			self::Client->value     => 'לקוח',
			self::Customer->value   => 'לקוח',
			self::Vendor->value     => 'ספק',
			self::Accountant->value => 'רואה חשבון',
			self::Hr->value         => 'משאבי אנוש',
			self::Employee->value   => 'עובד',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::SuperAdmin->value => 'Super Amministratore',
			self::Admin->value      => 'Amministratore',
			self::Company->value    => 'Azienda',
			self::Client->value     => 'Cliente',
			self::Customer->value   => 'Cliente',
			self::Vendor->value     => 'Fornitore',
			self::Accountant->value => 'Contabile',
			self::Hr->value         => 'Risorse Umane',
			self::Employee->value   => 'Dipendente',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::SuperAdmin->value => 'スーパー管理者',
			self::Admin->value      => '管理者',
			self::Company->value    => '会社',
			self::Client->value     => 'クライアント',
			self::Customer->value   => '顧客',
			self::Vendor->value     => 'ベンダー',
			self::Accountant->value => '会計士',
			self::Hr->value         => '人事',
			self::Employee->value   => '従業員',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::SuperAdmin->value => 'Super Beheerder',
			self::Admin->value      => 'Beheerder',
			self::Company->value    => 'Bedrijf',
			self::Client->value     => 'Klant',
			self::Customer->value   => 'Klant',
			self::Vendor->value     => 'Leverancier',
			self::Accountant->value => 'Accountant',
			self::Hr->value         => 'HR',
			self::Employee->value   => 'Werknemer',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::SuperAdmin->value => 'Super Administrator',
			self::Admin->value      => 'Administrator',
			self::Company->value    => 'Firma',
			self::Client->value     => 'Klient',
			self::Customer->value   => 'Klient',
			self::Vendor->value     => 'Dostawca',
			self::Accountant->value => 'Księgowy',
			self::Hr->value         => 'Kadry',
			self::Employee->value   => 'Pracownik',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::SuperAdmin->value => 'Супер Администратор',
			self::Admin->value      => 'Администратор',
			self::Company->value    => 'Компания',
			self::Client->value     => 'Клиент',
			self::Customer->value   => 'Покупатель',
			self::Vendor->value     => 'Поставщик',
			self::Accountant->value => 'Бухгалтер',
			self::Hr->value         => 'Кадры',
			self::Employee->value   => 'Сотрудник',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::SuperAdmin->value => 'Süper Yönetici',
			self::Admin->value      => 'Yönetici',
			self::Company->value    => 'Şirket',
			self::Client->value     => 'Müşteri',
			self::Customer->value   => 'Müşteri',
			self::Vendor->value     => 'Satıcı',
			self::Accountant->value => 'Muhasebeci',
			self::Hr->value         => 'İnsan Kaynakları',
			self::Employee->value   => 'Çalışan',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::SuperAdmin->value => '超级管理员',
			self::Admin->value      => '管理员',
			self::Company->value    => '公司',
			self::Client->value     => '客户',
			self::Customer->value   => '客户',
			self::Vendor->value     => '供应商',
			self::Accountant->value => '会计师',
			self::Hr->value         => '人力资源',
			self::Employee->value   => '员工',
		];
	}

	// Helper methods for business logic
	public function isAdmin(): bool
	{
		return match ($this) {
			self::SuperAdmin, self::Admin => true,
			default => false,
		};
	}

	public function isBusiness(): bool
	{
		return match ($this) {
			self::Company, self::Client, self::Customer, self::Vendor, self::Accountant, self::Hr, self::Employee => true,
			default => false,
		};
	}

	public function isInternal(): bool
	{
		return match ($this) {
			self::SuperAdmin, self::Admin, self::Company, self::Hr, self::Employee => true,
			default => false,
		};
	}

	public function isExternal(): bool
	{
		return match ($this) {
			self::Client, self::Customer, self::Vendor => true,
			default => false,
		};
	}

	public function canManageUsers(): bool
	{
		return match ($this) {
			self::SuperAdmin, self::Admin, self::Hr => true,
			default => false,
		};
	}

	public function canAccessReports(): bool
	{
		return match ($this) {
			self::SuperAdmin, self::Admin, self::Accountant, self::Hr => true,
			default => false,
		};
	}

	public function getPermissionLevel(): int
	{
		return match ($this) {
			self::SuperAdmin => 100,
			self::Admin      => 90,
			self::Company    => 80,
			self::Accountant => 70,
			self::Hr         => 65,
			self::Employee   => 55,
			self::Vendor     => 60,
			self::Client     => 50,
			self::Customer   => 40,
		};
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::SuperAdmin => 'shield-check',
			self::Admin      => 'shield',
			self::Company    => 'building',
			self::Client     => 'briefcase',
			self::Customer   => 'user',
			self::Vendor     => 'truck',
			self::Accountant => 'calculator',
			self::Hr         => 'users',
			self::Employee   => 'id-badge',
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::SuperAdmin => 'purple',
			self::Admin      => 'blue',
			self::Company    => 'indigo',
			self::Client     => 'green',
			self::Customer   => 'teal',
			self::Vendor     => 'orange',
			self::Accountant => 'red',
			self::Hr         => 'pink',
			self::Employee   => 'cyan',
		};
	}
}
