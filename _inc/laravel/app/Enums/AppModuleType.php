<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum AppModuleType: string
{
	case Financial = 'financial';
	case Sales = 'sales';
	case CRM = 'crm';
	case HRM = 'hrm';
	case Projects = 'projects';
	case Management = 'management';
	case Inventory = 'inventory';
	case Support = 'support';
	case Database = 'database';
	case Infrastructure = 'infrastructure';
	case Marketing = 'marketing';
	case Custom = 'custom';
	case LandingPage = 'landing_page';
	case Other = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Other;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;
		$map = [
			// Financial
			'financial'         => self::Financial,
			'finance'           => self::Financial,
			'finanças'          => self::Financial,
			'financiero'        => self::Financial,
			'financeiro'        => self::Financial,
			'accounting'        => self::Financial,
			'contabilidade'     => self::Financial,
			'contabilidad'      => self::Financial,
			'billing'           => self::Financial,
			'faturamento'       => self::Financial,
			'facturacion'       => self::Financial,
			'treasury'          => self::Financial,
			'tesouraria'        => self::Financial,

			// Sales
			'sales'             => self::Sales,
			'vendas'            => self::Sales,
			'ventas'            => self::Sales,
			'selling'           => self::Sales,
			'commercial'        => self::Sales,

			// CRM
			'crm'               => self::CRM,
			'customer_relationship_management' => self::CRM,
			'customer_relation' => self::CRM,
			'clients'           => self::CRM,
			'clientes'          => self::CRM,
			'client_management' => self::CRM,

			// HRM
			'hrm'               => self::HRM,
			'human_resources'   => self::HRM,
			'recursos_humanos'  => self::HRM,
			'recursos_humanos'  => self::HRM,
			'hr'                => self::HRM,
			'rh'                => self::HRM,
			'personnel'         => self::HRM,
			'personal'          => self::HRM,

			// Projects
			'projects'          => self::Projects,
			'project'           => self::Projects,
			'project_management' => self::Projects,
			'projetos'          => self::Projects,
			'proyectos'         => self::Projects,
			'task_management'   => self::Projects,
			'tarefas'           => self::Projects,
			'tasks'             => self::Projects,

			// Management
			'management'        => self::Management,
			'gerenciamento'     => self::Management,
			'gestion'           => self::Management,
			'administration'    => self::Management,
			'administração'     => self::Management,
			'administracion'    => self::Management,

			// Inventory
			'inventory'         => self::Inventory,
			'estoque'           => self::Inventory,
			'inventario'        => self::Inventory,
			'stock'             => self::Inventory,
			'almacen'           => self::Inventory,

			// Support
			'support'           => self::Support,
			'suporte'           => self::Support,
			'soporte'           => self::Support,
			'helpdesk'          => self::Support,
			'service_desk'      => self::Support,
			'assistencia'       => self::Support,
			'assistência'       => self::Support,
			'tickets'           => self::Support,
			'chamados'          => self::Support,

			// Database
			'database'          => self::Database,
			'db'                => self::Database,
			'banco_de_dados'    => self::Database,
			'base_de_datos'     => self::Database,
			'data'              => self::Database,
			'dados'             => self::Database,
			'datos'             => self::Database,

			// Infrastructure
			'infrastructure'    => self::Infrastructure,
			'infraestrutura'    => self::Infrastructure,
			'infraestructura'   => self::Infrastructure,
			'system'            => self::Infrastructure,
			'sistema'           => self::Infrastructure,
			'devops'            => self::Infrastructure,
			'servers'           => self::Infrastructure,
			'servidores'        => self::Infrastructure,

			// Marketing
			'marketing'         => self::Marketing,
			'mercado'           => self::Marketing,
			'mercadeo'          => self::Marketing,
			'advertising'       => self::Marketing,
			'publicidade'       => self::Marketing,
			'publicidad'        => self::Marketing,

			// Custom
			'custom'            => self::Custom,
			'personalizado'     => self::Custom,
			'personalizada'     => self::Custom,
			'especial'          => self::Custom,
			'especializado'     => self::Custom,

			// Landing Page
			'landing_page'      => self::LandingPage,
			'landingpage'       => self::LandingPage,
			'landing'           => self::LandingPage,
			'pagina_de_aterrizaje' => self::LandingPage,
			'pagina_de_aterrisagem' => self::LandingPage,
			'lp'                => self::LandingPage,
			'one_page'          => self::LandingPage,

			// Other
			'other'             => self::Other,
			'outro'             => self::Other,
			'otro'              => self::Other,
			'misc'              => self::Other,
			'miscellaneous'     => self::Other,
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
			self::Financial      => 'Financial',
			self::Sales          => 'Sales',
			self::CRM            => 'CRM',
			self::HRM            => 'HRM',
			self::Projects       => 'Projects',
			self::Management     => 'Management',
			self::Inventory      => 'Inventory',
			self::Support        => 'Support',
			self::Database       => 'Database',
			self::Infrastructure => 'Infrastructure',
			self::Marketing      => 'Marketing',
			self::Custom         => 'Custom',
			self::LandingPage    => 'Landing Page',
			self::Other          => 'Other',
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
			self::Financial->value      => 'Financeiro',
			self::Sales->value          => 'Vendas',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'Recursos Humanos',
			self::Projects->value       => 'Projetos',
			self::Management->value     => 'Gestão',
			self::Inventory->value      => 'Estoque',
			self::Support->value        => 'Suporte',
			self::Database->value       => 'Banco de Dados',
			self::Infrastructure->value => 'Infraestrutura',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Personalizado',
			self::LandingPage->value    => 'Página de Destino',
			self::Other->value          => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Financial->value      => 'Financial',
			self::Sales->value          => 'Sales',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'HRM',
			self::Projects->value       => 'Projects',
			self::Management->value     => 'Management',
			self::Inventory->value      => 'Inventory',
			self::Support->value        => 'Support',
			self::Database->value       => 'Database',
			self::Infrastructure->value => 'Infrastructure',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Custom',
			self::LandingPage->value    => 'Landing Page',
			self::Other->value          => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Financial->value      => 'Financiero',
			self::Sales->value          => 'Ventas',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'Recursos Humanos',
			self::Projects->value       => 'Proyectos',
			self::Management->value     => 'Gestión',
			self::Inventory->value      => 'Inventario',
			self::Support->value        => 'Soporte',
			self::Database->value       => 'Base de Datos',
			self::Infrastructure->value => 'Infraestructura',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Personalizado',
			self::LandingPage->value    => 'Página de Aterrizaje',
			self::Other->value          => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Financial->value      => 'مالي',
			self::Sales->value          => 'مبيعات',
			self::CRM->value            => 'إدارة علاقات العملاء',
			self::HRM->value            => 'موارد بشرية',
			self::Projects->value       => 'مشاريع',
			self::Management->value     => 'إدارة',
			self::Inventory->value      => 'المخزون',
			self::Support->value        => 'دعم',
			self::Database->value       => 'قاعدة البيانات',
			self::Infrastructure->value => 'البنية التحتية',
			self::Marketing->value      => 'تسويق',
			self::Custom->value         => 'مخصص',
			self::LandingPage->value    => 'صفحة الهبوط',
			self::Other->value          => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Financial->value      => 'Finansiel',
			self::Sales->value          => 'Salg',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'HRM',
			self::Projects->value       => 'Projekter',
			self::Management->value     => 'Ledelse',
			self::Inventory->value      => 'Lager',
			self::Support->value        => 'Support',
			self::Database->value       => 'Database',
			self::Infrastructure->value => 'Infrastruktur',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Brugerdefineret',
			self::LandingPage->value    => 'Landingsside',
			self::Other->value          => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Financial->value      => 'Finanzen',
			self::Sales->value          => 'Vertrieb',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'Personalwesen',
			self::Projects->value       => 'Projekte',
			self::Management->value     => 'Management',
			self::Inventory->value      => 'Inventar',
			self::Support->value        => 'Support',
			self::Database->value       => 'Datenbank',
			self::Infrastructure->value => 'Infrastruktur',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Benutzerdefiniert',
			self::LandingPage->value    => 'Landing Page',
			self::Other->value          => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Financial->value      => 'Financier',
			self::Sales->value          => 'Ventes',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'Ressources Humaines',
			self::Projects->value       => 'Projets',
			self::Management->value     => 'Gestion',
			self::Inventory->value      => 'Inventaire',
			self::Support->value        => 'Support',
			self::Database->value       => 'Base de Données',
			self::Infrastructure->value => 'Infrastructure',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Personnalisé',
			self::LandingPage->value    => 'Page de Destination',
			self::Other->value          => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Financial->value      => 'כספי',
			self::Sales->value          => 'מכירות',
			self::CRM->value            => 'ניהול קשרי לקוחות',
			self::HRM->value            => 'משאבי אנוש',
			self::Projects->value       => 'פרויקטים',
			self::Management->value     => 'ניהול',
			self::Inventory->value      => 'מלאי',
			self::Support->value        => 'תמיכה',
			self::Database->value       => 'מסד נתונים',
			self::Infrastructure->value => 'תשתית',
			self::Marketing->value      => 'שיווק',
			self::Custom->value         => 'מותאם אישית',
			self::LandingPage->value    => 'דף נחיתה',
			self::Other->value          => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Financial->value      => 'Finanziario',
			self::Sales->value          => 'Vendite',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'Risorse Umane',
			self::Projects->value       => 'Progetti',
			self::Management->value     => 'Gestione',
			self::Inventory->value      => 'Inventario',
			self::Support->value        => 'Supporto',
			self::Database->value       => 'Database',
			self::Infrastructure->value => 'Infrastruttura',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Personalizzato',
			self::LandingPage->value    => 'Pagina di Destinazione',
			self::Other->value          => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Financial->value      => '財務',
			self::Sales->value          => '販売',
			self::CRM->value            => 'CRM',
			self::HRM->value            => '人事',
			self::Projects->value       => 'プロジェクト',
			self::Management->value     => '管理',
			self::Inventory->value      => '在庫',
			self::Support->value        => 'サポート',
			self::Database->value       => 'データベース',
			self::Infrastructure->value => 'インフラストラクチャ',
			self::Marketing->value      => 'マーケティング',
			self::Custom->value         => 'カスタム',
			self::LandingPage->value    => 'ランディングページ',
			self::Other->value          => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Financial->value      => 'Financieel',
			self::Sales->value          => 'Verkoop',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'HRM',
			self::Projects->value       => 'Projecten',
			self::Management->value     => 'Beheer',
			self::Inventory->value      => 'Inventaris',
			self::Support->value        => 'Ondersteuning',
			self::Database->value       => 'Database',
			self::Infrastructure->value => 'Infrastructuur',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Aangepast',
			self::LandingPage->value    => 'Landingspagina',
			self::Other->value          => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Financial->value      => 'Finansowy',
			self::Sales->value          => 'Sprzedaż',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'HRM',
			self::Projects->value       => 'Projekty',
			self::Management->value     => 'Zarządzanie',
			self::Inventory->value      => 'Inwentarz',
			self::Support->value        => 'Wsparcie',
			self::Database->value       => 'Baza Danych',
			self::Infrastructure->value => 'Infrastruktura',
			self::Marketing->value      => 'Marketing',
			self::Custom->value         => 'Niestandardowy',
			self::LandingPage->value    => 'Strona Docelowa',
			self::Other->value          => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Financial->value      => 'Финансовый',
			self::Sales->value          => 'Продажи',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'HRM',
			self::Projects->value       => 'Проекты',
			self::Management->value     => 'Управление',
			self::Inventory->value      => 'Инвентарь',
			self::Support->value        => 'Поддержка',
			self::Database->value       => 'База данных',
			self::Infrastructure->value => 'Инфраструктура',
			self::Marketing->value      => 'Маркетинг',
			self::Custom->value         => 'Пользовательский',
			self::LandingPage->value    => 'Целевая страница',
			self::Other->value          => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Financial->value      => 'Finansal',
			self::Sales->value          => 'Satış',
			self::CRM->value            => 'CRM',
			self::HRM->value            => 'İnsan Kaynakları',
			self::Projects->value       => 'Projeler',
			self::Management->value     => 'Yönetim',
			self::Inventory->value      => 'Envanter',
			self::Support->value        => 'Destek',
			self::Database->value       => 'Veritabanı',
			self::Infrastructure->value => 'Altyapı',
			self::Marketing->value      => 'Pazarlama',
			self::Custom->value         => 'Özel',
			self::LandingPage->value    => 'Açılış Sayfası',
			self::Other->value          => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Financial->value      => '财务',
			self::Sales->value          => '销售',
			self::CRM->value            => '客户关系管理',
			self::HRM->value            => '人力资源',
			self::Projects->value       => '项目',
			self::Management->value     => '管理',
			self::Inventory->value      => '库存',
			self::Support->value        => '支持',
			self::Database->value       => '数据库',
			self::Infrastructure->value => '基础设施',
			self::Marketing->value      => '市场营销',
			self::Custom->value         => '自定义',
			self::LandingPage->value    => '着陆页',
			self::Other->value          => '其他',
		];
	}

	// Helper methods for business logic
	public function isBusinessModule(): bool
	{
		return match ($this) {
			self::Financial, self::Sales, self::CRM, self::HRM, self::Projects,
			self::Management, self::Inventory, self::Marketing => true,
			default => false,
		};
	}

	public function isTechnicalModule(): bool
	{
		return match ($this) {
			self::Database, self::Infrastructure, self::Support => true,
			default => false,
		};
	}

	public function isCoreModule(): bool
	{
		return match ($this) {
			self::Financial, self::Sales, self::CRM, self::HRM, self::Database => true,
			default => false,
		};
	}

	public function isOptionalModule(): bool
	{
		return match ($this) {
			self::Custom, self::LandingPage, self::Other => true,
			default => false,
		};
	}

	public function getCategory(): string
	{
		return match ($this) {
			self::Financial, self::Sales, self::CRM, self::HRM, self::Projects,
			self::Management, self::Inventory, self::Marketing => 'business',
			self::Database, self::Infrastructure, self::Support => 'technical',
			self::Custom, self::LandingPage => 'custom',
			self::Other => 'other',
		};
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Financial      => 'dollar-sign',
			self::Sales          => 'shopping-cart',
			self::CRM            => 'users',
			self::HRM            => 'briefcase',
			self::Projects       => 'folder',
			self::Management     => 'settings',
			self::Inventory      => 'package',
			self::Support        => 'headphones',
			self::Database       => 'database',
			self::Infrastructure => 'server',
			self::Marketing      => 'megaphone',
			self::Custom         => 'tool',
			self::LandingPage    => 'layout',
			self::Other          => 'box',
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Financial      => 'green',
			self::Sales          => 'blue',
			self::CRM            => 'indigo',
			self::HRM            => 'purple',
			self::Projects       => 'yellow',
			self::Management     => 'gray',
			self::Inventory      => 'orange',
			self::Support        => 'red',
			self::Database       => 'cyan',
			self::Infrastructure => 'teal',
			self::Marketing      => 'pink',
			self::Custom         => 'lime',
			self::LandingPage    => 'amber',
			self::Other          => 'slate',
		};
	}
}
