<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum AssetType: string
{
	// IT Department
	case Laptop = 'laptop';
	case Desktop = 'desktop';
	case Monitor = 'monitor';
	case Server = 'server';
	case NetworkEquipment = 'network_equipment';
	case SoftwareLicense = 'software_license';
	case MobileDevice = 'mobile_device';
	case Peripherals = 'peripherals';
	case StorageDevice = 'storage_device';
	case SecurityHardware = 'security_hardware';

		// HR Department
	case TrainingMaterial = 'training_material';
	case ErgonomicFurniture = 'ergonomic_furniture';
	case Uniform = 'uniform';
	case SafetyEquipment = 'safety_equipment';
	case OfficeSuppliesHR = 'office_supplies_hr';

		// Finance Department
	case FinancialSoftware = 'financial_software';
	case AccountingHardware = 'accounting_hardware';
	case POSSystem = 'pos_system';
	case DocumentShredder = 'document_shredder';
	case CashHandling = 'cash_handling';

		// Administration
	case OfficeFurniture = 'office_furniture';
	case OfficeSupplies = 'office_supplies';
	case FacilityKey = 'facility_key';
	case CleaningEquipment = 'cleaning_equipment';
	case MaintenanceTool = 'maintenance_tool';

		// Operations
	case Vehicle = 'vehicle';
	case Machinery = 'machinery';
	case ProductionEquipment = 'production_equipment';
	case WarehouseEquipment = 'warehouse_equipment';
	case SafetyGear = 'safety_gear';

		// Sales & Marketing
	case PromotionalMaterial = 'promotional_material';
	case DemoEquipment = 'demo_equipment';
	case PresentationGear = 'presentation_gear';
	case TradeShowBooth = 'trade_show_booth';

		// Research & Development
	case LaboratoryEquipment = 'laboratory_equipment';
	case PrototypingTool = 'prototyping_tool';
	case TestingDevice = 'testing_device';
	case ResearchSoftware = 'research_software';

		// Other
	case Other = 'other';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Other;

		$normalizedValue = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			// IT variations
			'laptop', 'notebook', 'mobilecomputer', 'portablecomputer' => self::Laptop,
			'desktop', 'workstation', 'pc', 'computer' => self::Desktop,
			'monitor', 'display', 'screen' => self::Monitor,
			'server', 'host', 'dataserver', 'computeserver' => self::Server,
			'networkequipment', 'router', 'switch', 'firewall', 'accesspoint', 'nas' => self::NetworkEquipment,
			'softwarelicense', 'software', 'license', 'app', 'application' => self::SoftwareLicense,
			'mobiledevice', 'tablet', 'smartphone', 'phone', 'ipad', 'android' => self::MobileDevice,
			'peripherals', 'keyboard', 'mouse', 'printer', 'scanner', 'webcam' => self::Peripherals,
			'storagedevice', 'harddrive', 'ssd', 'usb', 'externaldrive', 'flashdrive' => self::StorageDevice,
			'securityhardware', 'biometric', 'camera', 'surveillance', 'accesscontrol' => self::SecurityHardware,

			// HR variations
			'trainingmaterial', 'training', 'course', 'manual', 'guide', 'handbook' => self::TrainingMaterial,
			'ergonomicfurniture', 'ergonomicchair', 'standingdesk', 'keyboardtray' => self::ErgonomicFurniture,
			'uniform', 'clothing', 'attire', 'workwear', 'apparel' => self::Uniform,
			'safetyequipment', 'helmet', 'gloves', 'goggles', 'vest' => self::SafetyEquipment,
			'officesupplieshr', 'hrsupplies', 'forms', 'booklets' => self::OfficeSuppliesHR,

			// Finance variations
			'financialsoftware', 'accountingsoftware', 'erp', 'crm', 'taxsoftware' => self::FinancialSoftware,
			'accountinghardware', 'calculator', 'checkprinter', 'barcodescanner' => self::AccountingHardware,
			'possystem', 'pointofsale', 'cashregister', 'cardreader' => self::POSSystem,
			'documentshredder', 'shredder', 'destroyer', 'confidential' => self::DocumentShredder,
			'cashhandling', 'cashbox', 'safe', 'moneycounter', 'cashdrawer' => self::CashHandling,

			// Administration variations
			'officefurniture', 'desk', 'chair', 'cabinet', 'shelf', 'filingcabinet' => self::OfficeFurniture,
			'officesupplies', 'stationery', 'paper', 'pen', 'folder', 'binder' => self::OfficeSupplies,
			'facilitykey', 'key', 'accesscard', 'fob', 'rfid', 'badge' => self::FacilityKey,
			'cleaningequipment', 'vacuum', 'cleaner', 'janitorial', 'mop', 'broom' => self::CleaningEquipment,
			'maintenancetool', 'tool', 'toolkit', 'repair', 'handtool' => self::MaintenanceTool,

			// Operations variations
			'vehicle', 'car', 'truck', 'forklift', 'companycar', 'fleet' => self::Vehicle,
			'machinery', 'machine', 'industrial', 'manufacturing' => self::Machinery,
			'productionequipment', 'assemblyline', 'conveyor', 'press' => self::ProductionEquipment,
			'warehouseequipment', 'palletjack', 'shelving', 'racking', 'conveyorbelt' => self::WarehouseEquipment,
			'safetygear', 'ppe', 'protection', 'hardshell', 'respirator' => self::SafetyGear,

			// Sales & Marketing variations
			'promotionalmaterial', 'brochure', 'flyer', 'banner', 'merchandise' => self::PromotionalMaterial,
			'demoequipment', 'demo', 'sample', 'tester', 'prototype' => self::DemoEquipment,
			'presentationgear', 'projector', 'whiteboard', 'smartboard', 'clicker' => self::PresentationGear,
			'tradeshowbooth', 'booth', 'exhibit', 'display', 'popup' => self::TradeShowBooth,

			// Research & Development variations
			'laboratoryequipment', 'lab', 'microscope', 'centrifuge', 'analyzer' => self::LaboratoryEquipment,
			'prototypingtool', '3dprinter', 'lasercutter', 'cnc', 'maker' => self::PrototypingTool,
			'testingdevice', 'tester', 'meter', 'analyzer', 'oscilloscope' => self::TestingDevice,
			'researchsoftware', 'cad', 'simulation', 'analysis', 'modeling' => self::ResearchSoftware,

			// Other variations
			'other', 'misc', 'miscellaneous', 'unknown', 'unspecified', 'custom' => self::Other,

			default => self::Other,
		};
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
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
			self::Laptop->value => 'Laptop',
			self::Desktop->value => 'Computador Desktop',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Servidor',
			self::NetworkEquipment->value => 'Equipamento de Rede',
			self::SoftwareLicense->value => 'Licença de Software',
			self::MobileDevice->value => 'Dispositivo Móvel',
			self::Peripherals->value => 'Periféricos',
			self::StorageDevice->value => 'Dispositivo de Armazenamento',
			self::SecurityHardware->value => 'Hardware de Segurança',
			self::TrainingMaterial->value => 'Material de Treinamento',
			self::ErgonomicFurniture->value => 'Mobília Ergonômica',
			self::Uniform->value => 'Uniforme',
			self::SafetyEquipment->value => 'Equipamento de Segurança',
			self::OfficeSuppliesHR->value => 'Suprimentos de RH',
			self::FinancialSoftware->value => 'Software Financeiro',
			self::AccountingHardware->value => 'Hardware Contábil',
			self::POSSystem->value => 'Sistema POS',
			self::DocumentShredder->value => 'Fragmentadora de Documentos',
			self::CashHandling->value => 'Equipamento para Manuseio de Dinheiro',
			self::OfficeFurniture->value => 'Mobília de Escritório',
			self::OfficeSupplies->value => 'Suprimentos de Escritório',
			self::FacilityKey->value => 'Chave/Acesso à Instalação',
			self::CleaningEquipment->value => 'Equipamento de Limpeza',
			self::MaintenanceTool->value => 'Ferramenta de Manutenção',
			self::Vehicle->value => 'Veículo',
			self::Machinery->value => 'Maquinário',
			self::ProductionEquipment->value => 'Equipamento de Produção',
			self::WarehouseEquipment->value => 'Equipamento de Armazém',
			self::SafetyGear->value => 'Equipamento de Proteção',
			self::PromotionalMaterial->value => 'Material Promocional',
			self::DemoEquipment->value => 'Equipamento de Demonstração',
			self::PresentationGear->value => 'Equipamento de Apresentação',
			self::TradeShowBooth->value => 'Estande de Feira',
			self::LaboratoryEquipment->value => 'Equipamento de Laboratório',
			self::PrototypingTool->value => 'Ferramenta de Prototipagem',
			self::TestingDevice->value => 'Dispositivo de Teste',
			self::ResearchSoftware->value => 'Software de Pesquisa',
			self::Other->value => 'Outro',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Laptop->value => 'Portátil',
			self::Desktop->value => 'Ordenador de Sobremesa',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Servidor',
			self::NetworkEquipment->value => 'Equipo de Red',
			self::SoftwareLicense->value => 'Licencia de Software',
			self::MobileDevice->value => 'Dispositivo Móvil',
			self::Peripherals->value => 'Periféricos',
			self::StorageDevice->value => 'Dispositivo de Almacenamiento',
			self::SecurityHardware->value => 'Hardware de Seguridad',
			self::TrainingMaterial->value => 'Material de Formación',
			self::ErgonomicFurniture->value => 'Mobiliario Ergonómico',
			self::Uniform->value => 'Uniforme',
			self::SafetyEquipment->value => 'Equipo de Seguridad',
			self::OfficeSuppliesHR->value => 'Suministros de RRHH',
			self::FinancialSoftware->value => 'Software Financiero',
			self::AccountingHardware->value => 'Hardware Contable',
			self::POSSystem->value => 'Sistema TPV',
			self::DocumentShredder->value => 'Trituradora de Documentos',
			self::CashHandling->value => 'Equipo para Manejo de Efectivo',
			self::OfficeFurniture->value => 'Mobiliario de Oficina',
			self::OfficeSupplies->value => 'Suministros de Oficina',
			self::FacilityKey->value => 'Llave/Acceso a Instalaciones',
			self::CleaningEquipment->value => 'Equipo de Limpieza',
			self::MaintenanceTool->value => 'Herramienta de Mantenimiento',
			self::Vehicle->value => 'Vehículo',
			self::Machinery->value => 'Maquinaria',
			self::ProductionEquipment->value => 'Equipo de Producción',
			self::WarehouseEquipment->value => 'Equipo de Almacén',
			self::SafetyGear->value => 'Equipo de Protección',
			self::PromotionalMaterial->value => 'Material Promocional',
			self::DemoEquipment->value => 'Equipo de Demostración',
			self::PresentationGear->value => 'Equipo de Presentación',
			self::TradeShowBooth->value => 'Stand de Feria',
			self::LaboratoryEquipment->value => 'Equipo de Laboratorio',
			self::PrototypingTool->value => 'Herramienta de Prototipado',
			self::TestingDevice->value => 'Dispositivo de Prueba',
			self::ResearchSoftware->value => 'Software de Investigación',
			self::Other->value => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Laptop->value => 'كمبيوتر محمول',
			self::Desktop->value => 'كمبيوتر مكتبي',
			self::Monitor->value => 'شاشة',
			self::Server->value => 'خادم',
			self::NetworkEquipment->value => 'معدات الشبكة',
			self::SoftwareLicense->value => 'ترخيص برمجي',
			self::MobileDevice->value => 'جهاز محمول',
			self::Peripherals->value => 'ملحقات',
			self::StorageDevice->value => 'جهاز تخزين',
			self::SecurityHardware->value => 'عتاد أمني',
			self::TrainingMaterial->value => 'مواد تدريبية',
			self::ErgonomicFurniture->value => 'أثاث مريح',
			self::Uniform->value => 'زي موحد',
			self::SafetyEquipment->value => 'معدات السلامة',
			self::OfficeSuppliesHR->value => 'لوازم الموارد البشرية',
			self::FinancialSoftware->value => 'برنامج مالي',
			self::AccountingHardware->value => 'عتاد محاسبي',
			self::POSSystem->value => 'نظام نقاط البيع',
			self::DocumentShredder->value => 'مُدمر وثائق',
			self::CashHandling->value => 'معدات التعامل النقدي',
			self::OfficeFurniture->value => 'أثاث مكتبي',
			self::OfficeSupplies->value => 'لوازم مكتبية',
			self::FacilityKey->value => 'مفتاح/وصول للمنشأة',
			self::CleaningEquipment->value => 'معدات تنظيف',
			self::MaintenanceTool->value => 'أداة صيانة',
			self::Vehicle->value => 'مركبة',
			self::Machinery->value => 'آلات',
			self::ProductionEquipment->value => 'معدات إنتاج',
			self::WarehouseEquipment->value => 'معدات مستودع',
			self::SafetyGear->value => 'معدات وقاية',
			self::PromotionalMaterial->value => 'مواد ترويجية',
			self::DemoEquipment->value => 'معدات عرض توضيحي',
			self::PresentationGear->value => 'معدات عرض',
			self::TradeShowBooth->value => 'جناح معرض',
			self::LaboratoryEquipment->value => 'معدات مختبر',
			self::PrototypingTool->value => 'أداة نمذجة',
			self::TestingDevice->value => 'جهاز اختبار',
			self::ResearchSoftware->value => 'برنامج بحثي',
			self::Other->value => 'أخرى',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Laptop->value => 'Bærbar computer',
			self::Desktop->value => 'Stationær computer',
			self::Monitor->value => 'Skærm',
			self::Server->value => 'Server',
			self::NetworkEquipment->value => 'Netværksudstyr',
			self::SoftwareLicense->value => 'Softwarelicens',
			self::MobileDevice->value => 'Mobil enhed',
			self::Peripherals->value => 'Periféri',
			self::StorageDevice->value => 'Lagringsenhed',
			self::SecurityHardware->value => 'Sikkerhedshardware',
			self::TrainingMaterial->value => 'Træningsmateriale',
			self::ErgonomicFurniture->value => 'Ergonomisk møbler',
			self::Uniform->value => 'Uniform',
			self::SafetyEquipment->value => 'Sikkerhedsudstyr',
			self::OfficeSuppliesHR->value => 'HR kontorartikler',
			self::FinancialSoftware->value => 'Finansiel software',
			self::AccountingHardware->value => 'Regnskabshardware',
			self::POSSystem->value => 'POS system',
			self::DocumentShredder->value => 'Dokumentdestruktør',
			self::CashHandling->value => 'Kontanthåndteringsudstyr',
			self::OfficeFurniture->value => 'Kontormøbler',
			self::OfficeSupplies->value => 'Kontorartikler',
			self::FacilityKey->value => 'Facilitetsnøgle/adgang',
			self::CleaningEquipment->value => 'Rengøringsudstyr',
			self::MaintenanceTool->value => 'Vedligeholdelsesværktøj',
			self::Vehicle->value => 'Køretøj',
			self::Machinery->value => 'Maskineri',
			self::ProductionEquipment->value => 'Produktionsudstyr',
			self::WarehouseEquipment->value => 'Lagerudstyr',
			self::SafetyGear->value => 'Sikkerhedsudstyr',
			self::PromotionalMaterial->value => 'Promoveringsmateriale',
			self::DemoEquipment->value => 'Demonstrationsudstyr',
			self::PresentationGear->value => 'Præsentationsudstyr',
			self::TradeShowBooth->value => 'Messedis',
			self::LaboratoryEquipment->value => 'Laboratorieudstyr',
			self::PrototypingTool->value => 'Prototypeværktøj',
			self::TestingDevice->value => 'Testenhed',
			self::ResearchSoftware->value => 'Forskningssoftware',
			self::Other->value => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Laptop->value => 'Laptop',
			self::Desktop->value => 'Desktop-Computer',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Server',
			self::NetworkEquipment->value => 'Netzwerkgerät',
			self::SoftwareLicense->value => 'Softwarelizenz',
			self::MobileDevice->value => 'Mobiles Gerät',
			self::Peripherals->value => 'Peripheriegeräte',
			self::StorageDevice->value => 'Speichergerät',
			self::SecurityHardware->value => 'Sicherheitshardware',
			self::TrainingMaterial->value => 'Schulungsmaterial',
			self::ErgonomicFurniture->value => 'Ergonomische Möbel',
			self::Uniform->value => 'Uniform',
			self::SafetyEquipment->value => 'Sicherheitsausrüstung',
			self::OfficeSuppliesHR->value => 'HR-Büromaterial',
			self::FinancialSoftware->value => 'Finanzsoftware',
			self::AccountingHardware->value => 'Buchhaltungshardware',
			self::POSSystem->value => 'POS-System',
			self::DocumentShredder->value => 'Aktenvernichter',
			self::CashHandling->value => 'Bargeldbearbeitungsgerät',
			self::OfficeFurniture->value => 'Büromöbel',
			self::OfficeSupplies->value => 'Büromaterial',
			self::FacilityKey->value => 'Gebäudeschlüssel/Zugang',
			self::CleaningEquipment->value => 'Reinigungsgerät',
			self::MaintenanceTool->value => 'Wartungswerkzeug',
			self::Vehicle->value => 'Fahrzeug',
			self::Machinery->value => 'Maschinen',
			self::ProductionEquipment->value => 'Produktionsausrüstung',
			self::WarehouseEquipment->value => 'Lagerausrüstung',
			self::SafetyGear->value => 'Schutzausrüstung',
			self::PromotionalMaterial->value => 'Werbegeschenke',
			self::DemoEquipment->value => 'Demonstrationsgerät',
			self::PresentationGear->value => 'Präsentationsgerät',
			self::TradeShowBooth->value => 'Messestand',
			self::LaboratoryEquipment->value => 'Laborgerät',
			self::PrototypingTool->value => 'Prototyping-Werkzeug',
			self::TestingDevice->value => 'Testgerät',
			self::ResearchSoftware->value => 'Forschungssoftware',
			self::Other->value => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Laptop->value => 'Ordinateur Portable',
			self::Desktop->value => 'Ordinateur de Bureau',
			self::Monitor->value => 'Moniteur',
			self::Server->value => 'Serveur',
			self::NetworkEquipment->value => 'Équipement Réseau',
			self::SoftwareLicense->value => 'Licence Logicielle',
			self::MobileDevice->value => 'Appareil Mobile',
			self::Peripherals->value => 'Périphériques',
			self::StorageDevice->value => 'Dispositif de Stockage',
			self::SecurityHardware->value => 'Matériel de Sécurité',
			self::TrainingMaterial->value => 'Matériel de Formation',
			self::ErgonomicFurniture->value => 'Mobilier Ergonomique',
			self::Uniform->value => 'Uniforme',
			self::SafetyEquipment->value => 'Équipement de Sécurité',
			self::OfficeSuppliesHR->value => 'Fournitures RH',
			self::FinancialSoftware->value => 'Logiciel Financier',
			self::AccountingHardware->value => 'Matériel Comptable',
			self::POSSystem->value => 'Système de Caisse',
			self::DocumentShredder->value => 'Destructeur de Documents',
			self::CashHandling->value => 'Équipement de Gestion de la Trésorerie',
			self::OfficeFurniture->value => 'Mobilier de Bureau',
			self::OfficeSupplies->value => 'Fournitures de Bureau',
			self::FacilityKey->value => 'Clé/Accès aux Installations',
			self::CleaningEquipment->value => 'Équipement de Nettoyage',
			self::MaintenanceTool->value => 'Outil de Maintenance',
			self::Vehicle->value => 'Véhicule',
			self::Machinery->value => 'Machinerie',
			self::ProductionEquipment->value => 'Équipement de Production',
			self::WarehouseEquipment->value => 'Équipement d\'Entrepôt',
			self::SafetyGear->value => 'Équipement de Protection',
			self::PromotionalMaterial->value => 'Matériel Promotionnel',
			self::DemoEquipment->value => 'Équipement de Démonstration',
			self::PresentationGear->value => 'Équipement de Présentation',
			self::TradeShowBooth->value => 'Stand de Salon',
			self::LaboratoryEquipment->value => 'Équipement de Laboratoire',
			self::PrototypingTool->value => 'Outil de Prototypage',
			self::TestingDevice->value => 'Appareil de Test',
			self::ResearchSoftware->value => 'Logiciel de Recherche',
			self::Other->value => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Laptop->value => 'מחשב נייד',
			self::Desktop->value => 'מחשב שולחני',
			self::Monitor->value => 'מסך',
			self::Server->value => 'שרת',
			self::NetworkEquipment->value => 'ציוד רשת',
			self::SoftwareLicense->value => 'רישיון תוכנה',
			self::MobileDevice->value => 'מכשיר נייד',
			self::Peripherals->value => 'אביזרים',
			self::StorageDevice->value => 'מכשיר אחסון',
			self::SecurityHardware->value => 'חומרת אבטחה',
			self::TrainingMaterial->value => 'חומר הדרכה',
			self::ErgonomicFurniture->value => 'ריהוט ארגונומי',
			self::Uniform->value => 'מדים',
			self::SafetyEquipment->value => 'ציוד בטיחות',
			self::OfficeSuppliesHR->value => 'אספקת משרד למשאבי אנוש',
			self::FinancialSoftware->value => 'תוכנה פיננסית',
			self::AccountingHardware->value => 'חומרת חשבונאות',
			self::POSSystem->value => 'מערכת קופה',
			self::DocumentShredder->value => 'מכשיר השמדת מסמכים',
			self::CashHandling->value => 'ציוד לטיפול במזומן',
			self::OfficeFurniture->value => 'ריהוט משרדי',
			self::OfficeSupplies->value => 'אספקת משרד',
			self::FacilityKey->value => 'מפתח/גישה למתקן',
			self::CleaningEquipment->value => 'ציוד ניקוי',
			self::MaintenanceTool->value => 'כלי תחזוקה',
			self::Vehicle->value => 'כלי רכב',
			self::Machinery->value => 'מכונות',
			self::ProductionEquipment->value => 'ציוד ייצור',
			self::WarehouseEquipment->value => 'ציוד מחסן',
			self::SafetyGear->value => 'ציוד מגן',
			self::PromotionalMaterial->value => 'חומר קידום מכירות',
			self::DemoEquipment->value => 'ציוד הדגמה',
			self::PresentationGear->value => 'ציוד מצגת',
			self::TradeShowBooth->value => 'דוכן תערוכה',
			self::LaboratoryEquipment->value => 'ציוד מעבדה',
			self::PrototypingTool->value => 'כלי אב טיפוס',
			self::TestingDevice->value => 'מכשיר בדיקה',
			self::ResearchSoftware->value => 'תוכנת מחקר',
			self::Other->value => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Laptop->value => 'Laptop',
			self::Desktop->value => 'Computer Desktop',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Server',
			self::NetworkEquipment->value => 'Apparecchiatura di Rete',
			self::SoftwareLicense->value => 'Licenza Software',
			self::MobileDevice->value => 'Dispositivo Mobile',
			self::Peripherals->value => 'Periferiche',
			self::StorageDevice->value => 'Dispositivo di Archiviazione',
			self::SecurityHardware->value => 'Hardware di Sicurezza',
			self::TrainingMaterial->value => 'Materiale di Formazione',
			self::ErgonomicFurniture->value => 'Arredamento Ergonomico',
			self::Uniform->value => 'Uniforme',
			self::SafetyEquipment->value => 'Attrezzatura di Sicurezza',
			self::OfficeSuppliesHR->value => 'Forniture Ufficio Risorse Umane',
			self::FinancialSoftware->value => 'Software Finanziario',
			self::AccountingHardware->value => 'Hardware Contabile',
			self::POSSystem->value => 'Sistema POS',
			self::DocumentShredder->value => 'Distruggidocumenti',
			self::CashHandling->value => 'Attrezzatura per Gestione Contanti',
			self::OfficeFurniture->value => 'Arredamento Ufficio',
			self::OfficeSupplies->value => 'Forniture Ufficio',
			self::FacilityKey->value => 'Chiave/Accesso Struttura',
			self::CleaningEquipment->value => 'Attrezzatura Pulizia',
			self::MaintenanceTool->value => 'Strumento di Manutenzione',
			self::Vehicle->value => 'Veicolo',
			self::Machinery->value => 'Macchinari',
			self::ProductionEquipment->value => 'Attrezzatura Produzione',
			self::WarehouseEquipment->value => 'Attrezzatura Magazzino',
			self::SafetyGear->value => 'Dispositivi di Protezione',
			self::PromotionalMaterial->value => 'Materiale Promozionale',
			self::DemoEquipment->value => 'Attrezzatura Dimostrativa',
			self::PresentationGear->value => 'Attrezzatura Presentazione',
			self::TradeShowBooth->value => 'Stand Fiera',
			self::LaboratoryEquipment->value => 'Attrezzatura Laboratorio',
			self::PrototypingTool->value => 'Strumento Prototipazione',
			self::TestingDevice->value => 'Dispositivo di Test',
			self::ResearchSoftware->value => 'Software di Ricerca',
			self::Other->value => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Laptop->value => 'ノートパソコン',
			self::Desktop->value => 'デスクトップパソコン',
			self::Monitor->value => 'モニター',
			self::Server->value => 'サーバー',
			self::NetworkEquipment->value => 'ネットワーク機器',
			self::SoftwareLicense->value => 'ソフトウェアライセンス',
			self::MobileDevice->value => 'モバイルデバイス',
			self::Peripherals->value => '周辺機器',
			self::StorageDevice->value => 'ストレージデバイス',
			self::SecurityHardware->value => 'セキュリティハードウェア',
			self::TrainingMaterial->value => '研修資料',
			self::ErgonomicFurniture->value => '人間工学に基づいた家具',
			self::Uniform->value => '制服',
			self::SafetyEquipment->value => '安全装備',
			self::OfficeSuppliesHR->value => '人事事務用品',
			self::FinancialSoftware->value => '財務ソフトウェア',
			self::AccountingHardware->value => '会計ハードウェア',
			self::POSSystem->value => 'POSシステム',
			self::DocumentShredder->value => '書類シュレッダー',
			self::CashHandling->value => '現金処理機器',
			self::OfficeFurniture->value => 'オフィス家具',
			self::OfficeSupplies->value => '事務用品',
			self::FacilityKey->value => '施設キー/アクセス',
			self::CleaningEquipment->value => '清掃機器',
			self::MaintenanceTool->value => 'メンテナンス工具',
			self::Vehicle->value => '車両',
			self::Machinery->value => '機械',
			self::ProductionEquipment->value => '生産設備',
			self::WarehouseEquipment->value => '倉庫設備',
			self::SafetyGear->value => '安全装備',
			self::PromotionalMaterial->value => '宣伝資料',
			self::DemoEquipment->value => 'デモ機器',
			self::PresentationGear->value => 'プレゼンテーション機器',
			self::TradeShowBooth->value => '展示会ブース',
			self::LaboratoryEquipment->value => '実験機器',
			self::PrototypingTool->value => 'プロトタイプ工具',
			self::TestingDevice->value => '試験装置',
			self::ResearchSoftware->value => '研究ソフトウェア',
			self::Other->value => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Laptop->value => 'Laptop',
			self::Desktop->value => 'Desktop Computer',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Server',
			self::NetworkEquipment->value => 'Netwerkapparatuur',
			self::SoftwareLicense->value => 'Softwarelicentie',
			self::MobileDevice->value => 'Mobiel Apparaat',
			self::Peripherals->value => 'Randapparatuur',
			self::StorageDevice->value => 'Opslagapparaat',
			self::SecurityHardware->value => 'Beveiligingshardware',
			self::TrainingMaterial->value => 'Trainingsmateriaal',
			self::ErgonomicFurniture->value => 'Ergonomisch Meubilair',
			self::Uniform->value => 'Uniform',
			self::SafetyEquipment->value => 'Veiligheidsuitrusting',
			self::OfficeSuppliesHR->value => 'HR Kantoorbenodigdheden',
			self::FinancialSoftware->value => 'Financiële Software',
			self::AccountingHardware->value => 'Boekhoudhardware',
			self::POSSystem->value => 'Kassasysteem',
			self::DocumentShredder->value => 'Documentenvernietiger',
			self::CashHandling->value => 'Contantgeldverwerkingsapparatuur',
			self::OfficeFurniture->value => 'Kantoor Meubilair',
			self::OfficeSupplies->value => 'Kantoorbenodigdheden',
			self::FacilityKey->value => 'Gebouwsleutel/Toegang',
			self::CleaningEquipment->value => 'Schoonmaakapparatuur',
			self::MaintenanceTool->value => 'Onderhoudsgereedschap',
			self::Vehicle->value => 'Voertuig',
			self::Machinery->value => 'Machines',
			self::ProductionEquipment->value => 'Productieapparatuur',
			self::WarehouseEquipment->value => 'Magazijnapparatuur',
			self::SafetyGear->value => 'Veiligheidsuitrusting',
			self::PromotionalMaterial->value => 'Promotiemateriaal',
			self::DemoEquipment->value => 'Demonstratieapparatuur',
			self::PresentationGear->value => 'Presentatieapparatuur',
			self::TradeShowBooth->value => 'Beursstand',
			self::LaboratoryEquipment->value => 'Laboratoriumapparatuur',
			self::PrototypingTool->value => 'Prototypegereedschap',
			self::TestingDevice->value => 'Testapparaat',
			self::ResearchSoftware->value => 'Onderzoekssoftware',
			self::Other->value => 'Overig',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Laptop->value => 'Laptop',
			self::Desktop->value => 'Komputer Stacjonarny',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Serwer',
			self::NetworkEquipment->value => 'Sprzęt Sieciowy',
			self::SoftwareLicense->value => 'Licencja Oprogramowania',
			self::MobileDevice->value => 'Urządzenie Mobilne',
			self::Peripherals->value => 'Urządzenia Peryferyjne',
			self::StorageDevice->value => 'Urządzenie Pamięci Masowej',
			self::SecurityHardware->value => 'Sprzęt Zabezpieczający',
			self::TrainingMaterial->value => 'Materiały Szkoleniowe',
			self::ErgonomicFurniture->value => 'Meble Ergonomiczne',
			self::Uniform->value => 'Mundur',
			self::SafetyEquipment->value => 'Sprzęt Bezpieczeństwa',
			self::OfficeSuppliesHR->value => 'Zasoby Biurowe HR',
			self::FinancialSoftware->value => 'Oprogramowanie Finansowe',
			self::AccountingHardware->value => 'Sprzęt Księgowy',
			self::POSSystem->value => 'System POS',
			self::DocumentShredder->value => 'Niszczarka Dokumentów',
			self::CashHandling->value => 'Sprzęt do Obsługi Gotówki',
			self::OfficeFurniture->value => 'Meble Biurowe',
			self::OfficeSupplies->value => 'Zasoby Biurowe',
			self::FacilityKey->value => 'Klucz/Dostęp do Obiektu',
			self::CleaningEquipment->value => 'Sprzęt Czystości',
			self::MaintenanceTool->value => 'Narzędzie Konserwacyjne',
			self::Vehicle->value => 'Pojazd',
			self::Machinery->value => 'Maszyny',
			self::ProductionEquipment->value => 'Sprzęt Produkcyjny',
			self::WarehouseEquipment->value => 'Sprzęt Magazynowy',
			self::SafetyGear->value => 'Sprzęt Ochronny',
			self::PromotionalMaterial->value => 'Materiały Promocyjne',
			self::DemoEquipment->value => 'Sprzęt Demonstracyjny',
			self::PresentationGear->value => 'Sprzęt Prezentacyjny',
			self::TradeShowBooth->value => 'Stoisko Targowe',
			self::LaboratoryEquipment->value => 'Sprzęt Laboratoryjny',
			self::PrototypingTool->value => 'Narzędzie do Prototypowania',
			self::TestingDevice->value => 'Urządzenie Testowe',
			self::ResearchSoftware->value => 'Oprogramowanie Badawcze',
			self::Other->value => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Laptop->value => 'Ноутбук',
			self::Desktop->value => 'Настольный компьютер',
			self::Monitor->value => 'Монитор',
			self::Server->value => 'Сервер',
			self::NetworkEquipment->value => 'Сетевое оборудование',
			self::SoftwareLicense->value => 'Лицензия на ПО',
			self::MobileDevice->value => 'Мобильное устройство',
			self::Peripherals->value => 'Периферийные устройства',
			self::StorageDevice->value => 'Устройство хранения данных',
			self::SecurityHardware->value => 'Оборудование безопасности',
			self::TrainingMaterial->value => 'Учебные материалы',
			self::ErgonomicFurniture->value => 'Эргономичная мебель',
			self::Uniform->value => 'Униформа',
			self::SafetyEquipment->value => 'Оборудование безопасности',
			self::OfficeSuppliesHR->value => 'Канцелярские товары для HR',
			self::FinancialSoftware->value => 'Финансовое ПО',
			self::AccountingHardware->value => 'Бухгалтерское оборудование',
			self::POSSystem->value => 'POS-система',
			self::DocumentShredder->value => 'Шредер для документов',
			self::CashHandling->value => 'Оборудование для обработки наличных',
			self::OfficeFurniture->value => 'Офисная мебель',
			self::OfficeSupplies->value => 'Канцелярские товары',
			self::FacilityKey->value => 'Ключ/Доступ к объекту',
			self::CleaningEquipment->value => 'Уборочное оборудование',
			self::MaintenanceTool->value => 'Инструмент для обслуживания',
			self::Vehicle->value => 'Транспортное средство',
			self::Machinery->value => 'Оборудование',
			self::ProductionEquipment->value => 'Производственное оборудование',
			self::WarehouseEquipment->value => 'Складское оборудование',
			self::SafetyGear->value => 'Защитное оборудование',
			self::PromotionalMaterial->value => 'Рекламные материалы',
			self::DemoEquipment->value => 'Демонстрационное оборудование',
			self::PresentationGear->value => 'Оборудование для презентаций',
			self::TradeShowBooth->value => 'Выставочный стенд',
			self::LaboratoryEquipment->value => 'Лабораторное оборудование',
			self::PrototypingTool->value => 'Инструмент для прототипирования',
			self::TestingDevice->value => 'Испытательное устройство',
			self::ResearchSoftware->value => 'Исследовательское ПО',
			self::Other->value => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Laptop->value => 'Dizüstü Bilgisayar',
			self::Desktop->value => 'Masaüstü Bilgisayar',
			self::Monitor->value => 'Monitör',
			self::Server->value => 'Sunucu',
			self::NetworkEquipment->value => 'Ağ Ekipmanı',
			self::SoftwareLicense->value => 'Yazılım Lisansı',
			self::MobileDevice->value => 'Mobil Cihaz',
			self::Peripherals->value => 'Çevre Birimleri',
			self::StorageDevice->value => 'Depolama Cihazı',
			self::SecurityHardware->value => 'Güvenlik Donanımı',
			self::TrainingMaterial->value => 'Eğitim Materyali',
			self::ErgonomicFurniture->value => 'Ergonomik Mobilya',
			self::Uniform->value => 'Üniforma',
			self::SafetyEquipment->value => 'Güvenlik Ekipmanı',
			self::OfficeSuppliesHR->value => 'İK Ofis Malzemeleri',
			self::FinancialSoftware->value => 'Finans Yazılımı',
			self::AccountingHardware->value => 'Muhasebe Donanımı',
			self::POSSystem->value => 'POS Sistemi',
			self::DocumentShredder->value => 'Evrak İmha Makinesi',
			self::CashHandling->value => 'Nakit İşleme Ekipmanı',
			self::OfficeFurniture->value => 'Ofis Mobilyası',
			self::OfficeSupplies->value => 'Ofis Malzemeleri',
			self::FacilityKey->value => 'Tesis Anahtarı/Erişim',
			self::CleaningEquipment->value => 'Temizlik Ekipmanı',
			self::MaintenanceTool->value => 'Bakım Aleti',
			self::Vehicle->value => 'Araç',
			self::Machinery->value => 'Makine',
			self::ProductionEquipment->value => 'Üretim Ekipmanı',
			self::WarehouseEquipment->value => 'Depo Ekipmanı',
			self::SafetyGear->value => 'Güvenlik Donanımı',
			self::PromotionalMaterial->value => 'Tanıtım Materyali',
			self::DemoEquipment->value => 'Demo Ekipmanı',
			self::PresentationGear->value => 'Sunum Ekipmanı',
			self::TradeShowBooth->value => 'Fuar Standı',
			self::LaboratoryEquipment->value => 'Laboratuvar Ekipmanı',
			self::PrototypingTool->value => 'Prototip Aleti',
			self::TestingDevice->value => 'Test Cihazı',
			self::ResearchSoftware->value => 'Araştırma Yazılımı',
			self::Other->value => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Laptop->value => '笔记本电脑',
			self::Desktop->value => '台式电脑',
			self::Monitor->value => '显示器',
			self::Server->value => '服务器',
			self::NetworkEquipment->value => '网络设备',
			self::SoftwareLicense->value => '软件许可证',
			self::MobileDevice->value => '移动设备',
			self::Peripherals->value => '外设',
			self::StorageDevice->value => '存储设备',
			self::SecurityHardware->value => '安全硬件',
			self::TrainingMaterial->value => '培训材料',
			self::ErgonomicFurniture->value => '人体工学家具',
			self::Uniform->value => '制服',
			self::SafetyEquipment->value => '安全设备',
			self::OfficeSuppliesHR->value => '人力资源办公用品',
			self::FinancialSoftware->value => '财务软件',
			self::AccountingHardware->value => '会计硬件',
			self::POSSystem->value => 'POS系统',
			self::DocumentShredder->value => '文件粉碎机',
			self::CashHandling->value => '现金处理设备',
			self::OfficeFurniture->value => '办公家具',
			self::OfficeSupplies->value => '办公用品',
			self::FacilityKey->value => '设施钥匙/访问权限',
			self::CleaningEquipment->value => '清洁设备',
			self::MaintenanceTool->value => '维护工具',
			self::Vehicle->value => '车辆',
			self::Machinery->value => '机械设备',
			self::ProductionEquipment->value => '生产设备',
			self::WarehouseEquipment->value => '仓库设备',
			self::SafetyGear->value => '安全装备',
			self::PromotionalMaterial->value => '宣传材料',
			self::DemoEquipment->value => '演示设备',
			self::PresentationGear->value => '演示设备',
			self::TradeShowBooth->value => '展会展台',
			self::LaboratoryEquipment->value => '实验室设备',
			self::PrototypingTool->value => '原型工具',
			self::TestingDevice->value => '测试设备',
			self::ResearchSoftware->value => '研究软件',
			self::Other->value => '其他',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Laptop->value => 'Laptop',
			self::Desktop->value => 'Desktop Computer',
			self::Monitor->value => 'Monitor',
			self::Server->value => 'Server',
			self::NetworkEquipment->value => 'Network Equipment',
			self::SoftwareLicense->value => 'Software License',
			self::MobileDevice->value => 'Mobile Device',
			self::Peripherals->value => 'Peripherals',
			self::StorageDevice->value => 'Storage Device',
			self::SecurityHardware->value => 'Security Hardware',
			self::TrainingMaterial->value => 'Training Material',
			self::ErgonomicFurniture->value => 'Ergonomic Furniture',
			self::Uniform->value => 'Uniform',
			self::SafetyEquipment->value => 'Safety Equipment',
			self::OfficeSuppliesHR->value => 'HR Office Supplies',
			self::FinancialSoftware->value => 'Financial Software',
			self::AccountingHardware->value => 'Accounting Hardware',
			self::POSSystem->value => 'POS System',
			self::DocumentShredder->value => 'Document Shredder',
			self::CashHandling->value => 'Cash Handling Equipment',
			self::OfficeFurniture->value => 'Office Furniture',
			self::OfficeSupplies->value => 'Office Supplies',
			self::FacilityKey->value => 'Facility Key/Access',
			self::CleaningEquipment->value => 'Cleaning Equipment',
			self::MaintenanceTool->value => 'Maintenance Tool',
			self::Vehicle->value => 'Vehicle',
			self::Machinery->value => 'Machinery',
			self::ProductionEquipment->value => 'Production Equipment',
			self::WarehouseEquipment->value => 'Warehouse Equipment',
			self::SafetyGear->value => 'Safety Gear',
			self::PromotionalMaterial->value => 'Promotional Material',
			self::DemoEquipment->value => 'Demo Equipment',
			self::PresentationGear->value => 'Presentation Gear',
			self::TradeShowBooth->value => 'Trade Show Booth',
			self::LaboratoryEquipment->value => 'Laboratory Equipment',
			self::PrototypingTool->value => 'Prototyping Tool',
			self::TestingDevice->value => 'Testing Device',
			self::ResearchSoftware->value => 'Research Software',
			self::Other->value => 'Other',
		];
	}

	public static function groupedOptions(): array
	{
		return [
			'information_technology' => [
				self::Laptop,
				self::Desktop,
				self::Monitor,
				self::Server,
				self::NetworkEquipment,
				self::SoftwareLicense,
				self::MobileDevice,
				self::Peripherals,
				self::StorageDevice,
				self::SecurityHardware,
			],
			'human_resources' => [
				self::TrainingMaterial,
				self::ErgonomicFurniture,
				self::Uniform,
				self::SafetyEquipment,
				self::OfficeSuppliesHR,
			],
			'finance_accounting' => [
				self::FinancialSoftware,
				self::AccountingHardware,
				self::POSSystem,
				self::DocumentShredder,
				self::CashHandling,
			],
			'administration_facilities' => [
				self::OfficeFurniture,
				self::OfficeSupplies,
				self::FacilityKey,
				self::CleaningEquipment,
				self::MaintenanceTool,
			],
			'operations_production' => [
				self::Vehicle,
				self::Machinery,
				self::ProductionEquipment,
				self::WarehouseEquipment,
				self::SafetyGear,
			],
			'sales_marketing' => [
				self::PromotionalMaterial,
				self::DemoEquipment,
				self::PresentationGear,
				self::TradeShowBooth,
			],
			'research_development' => [
				self::LaboratoryEquipment,
				self::PrototypingTool,
				self::TestingDevice,
				self::ResearchSoftware,
			],
			'other' => [
				self::Other,
			],
		];
	}

	public static function department(self $assetType): ?string
	{
		return match ($assetType) {
			self::Laptop, self::Desktop, self::Monitor, self::Server,
			self::NetworkEquipment, self::SoftwareLicense, self::MobileDevice,
			self::Peripherals, self::StorageDevice, self::SecurityHardware => 'Information Technology',

			self::TrainingMaterial, self::ErgonomicFurniture, self::Uniform,
			self::SafetyEquipment, self::OfficeSuppliesHR => 'Human Resources',

			self::FinancialSoftware, self::AccountingHardware, self::POSSystem,
			self::DocumentShredder, self::CashHandling => 'Finance & Accounting',

			self::OfficeFurniture, self::OfficeSupplies, self::FacilityKey,
			self::CleaningEquipment, self::MaintenanceTool => 'Administration & Facilities',

			self::Vehicle, self::Machinery, self::ProductionEquipment,
			self::WarehouseEquipment, self::SafetyGear => 'Operations & Production',

			self::PromotionalMaterial, self::DemoEquipment, self::PresentationGear,
			self::TradeShowBooth => 'Sales & Marketing',

			self::LaboratoryEquipment, self::PrototypingTool, self::TestingDevice,
			self::ResearchSoftware => 'Research & Development',

			self::Other => 'Other',
		};
	}

	public static function iconClasses(): array
	{
		return [
			self::Laptop->value => 'fas fa-laptop',
			self::Desktop->value => 'fas fa-desktop',
			self::Monitor->value => 'fas fa-tv',
			self::Server->value => 'fas fa-server',
			self::NetworkEquipment->value => 'fas fa-network-wired',
			self::SoftwareLicense->value => 'fas fa-copyright',
			self::MobileDevice->value => 'fas fa-mobile-alt',
			self::Peripherals->value => 'fas fa-keyboard',
			self::StorageDevice->value => 'fas fa-hdd',
			self::SecurityHardware->value => 'fas fa-shield-alt',
			self::TrainingMaterial->value => 'fas fa-book',
			self::ErgonomicFurniture->value => 'fas fa-chair',
			self::Uniform->value => 'fas fa-tshirt',
			self::SafetyEquipment->value => 'fas fa-hard-hat',
			self::OfficeSuppliesHR->value => 'fas fa-clipboard-list',
			self::FinancialSoftware->value => 'fas fa-chart-line',
			self::AccountingHardware->value => 'fas fa-calculator',
			self::POSSystem->value => 'fas fa-cash-register',
			self::DocumentShredder->value => 'fas fa-shredder',
			self::CashHandling->value => 'fas fa-money-bill-wave',
			self::OfficeFurniture->value => 'fas fa-couch',
			self::OfficeSupplies->value => 'fas fa-paperclip',
			self::FacilityKey->value => 'fas fa-key',
			self::CleaningEquipment->value => 'fas fa-broom',
			self::MaintenanceTool->value => 'fas fa-tools',
			self::Vehicle->value => 'fas fa-car',
			self::Machinery->value => 'fas fa-cogs',
			self::ProductionEquipment->value => 'fas fa-industry',
			self::WarehouseEquipment->value => 'fas fa-pallet',
			self::SafetyGear->value => 'fas fa-helmet-safety',
			self::PromotionalMaterial->value => 'fas fa-bullhorn',
			self::DemoEquipment->value => 'fas fa-vial',
			self::PresentationGear->value => 'fas fa-projector',
			self::TradeShowBooth->value => 'fas fa-store',
			self::LaboratoryEquipment->value => 'fas fa-flask',
			self::PrototypingTool->value => 'fas fa-print',
			self::TestingDevice->value => 'fas fa-vial',
			self::ResearchSoftware->value => 'fas fa-microscope',
			self::Other->value => 'fas fa-box',
		];
	}

	public function isDepreciable(): bool
	{
		return match ($this) {
			self::Laptop, self::Desktop, self::Server, self::Vehicle,
			self::Machinery, self::ProductionEquipment, self::OfficeFurniture,
			self::NetworkEquipment, self::LaboratoryEquipment => true,
			default => false,
		};
	}

	public function typicalLifespanYears(): int
	{
		return match ($this) {
			self::Laptop, self::Desktop, self::MobileDevice => 3,
			self::Server, self::NetworkEquipment => 5,
			self::Vehicle => 5,
			self::Machinery, self::ProductionEquipment => 7,
			self::OfficeFurniture => 10,
			self::SoftwareLicense => 1,
			default => 5,
		};
	}
}
