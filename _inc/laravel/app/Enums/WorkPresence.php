<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum WorkPresence: string
{
	case Onsite = 'onsite';
	case Office = 'office';
	case Remote = 'remote';
	case Hybrid = 'hybrid';
	case Flexible = 'flexible';
	case Travel = 'travel';
	case ClientSite = 'client_site';
	case FieldWork = 'field_work';
	case Mobile = 'mobile';
	case CoWorking = 'co_working';
	case HomeOffice = 'home_office';
	case SatelliteOffice = 'satellite_office';
	case RegionalOffice = 'regional_office';
	case Headquarters = 'headquarters';
	case Offshore = 'offshore';
	case Nomadic = 'nomadic';
	case FullyRemote = 'fully_remote';
	case RemoteFirst = 'remote_first';
	case OfficeFirst = 'office_first';
	case PartiallyRemote = 'partially_remote';
	case OccasionalOffice = 'occasional_office';
	case NeverRemote = 'never_remote';
	case AlwaysRemote = 'always_remote';
	case Rotational = 'rotational';
	case ProjectBased = 'project_based';
	case Seasonal = 'seasonal';
	case FlyInFlyOut = 'fly_in_fly_out';
	case DigitalNomad = 'digital_nomad';
	case Anywhere = 'anywhere';
	case Distributed = 'distributed';
	case Virtual = 'virtual';

	/**
	 * Normalize input to WorkPresence
	 */
	public static function normalize(string|int|null|self $value = null): ?self
	{
		if ($value instanceof self) {
			return $value;
		}

		if ($value === null) {
			return null;
		}

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
		return match ($normalizedValue) {
			// Onsite/Office types
			'onsite', 'onsite', 'presencial', 'local' => self::Onsite,
			'office', 'escritorio', 'escritório', 'bureau' => self::Office,
			'headquarters', 'hq', 'sede', 'matriz' => self::Headquarters,
			'satelliteoffice', 'satellite', 'filial', 'branch' => self::SatelliteOffice,
			'regionaloffice', 'regional', 'regional' => self::RegionalOffice,
			'officefirst', 'predominantlyoffice' => self::OfficeFirst,
			'neverremote', 'notremote', 'alwaysonsite' => self::NeverRemote,

			// Remote types
			'remote', 'remoto', 'teletrabalho', 'distance' => self::Remote,
			'fullyremote', 'completelyremote', '100remote' => self::FullyRemote,
			'alwaysremote', 'permanentlyremote' => self::AlwaysRemote,
			'remotefirst', 'remotefirst', 'distributedfirst' => self::RemoteFirst,
			'homeoffice', 'home', 'casa', 'domicilio' => self::HomeOffice,
			'virtual', 'virtualoffice', 'online' => self::Virtual,
			'digitalnomad', 'nomad', 'nomadicworker' => self::DigitalNomad,
			'anywhere', 'workfromanywhere', 'wfa' => self::Anywhere,
			'distributed', 'globallydistributed' => self::Distributed,

			// Hybrid/Flexible
			'hybrid', 'hibrido', 'híbrido', 'mixed' => self::Hybrid,
			'flexible', 'flexivel', 'flexible', 'adaptable' => self::Flexible,
			'partiallyremote', 'partialremote', 'sometimesremote' => self::PartiallyRemote,
			'occasionaloffice', 'occasionallyoffice', 'someoffice' => self::OccasionalOffice,

			// Travel/Mobile
			'travel', 'traveling', 'viajante', 'viagens' => self::Travel,
			'mobile', 'movel', 'móvel', 'onroad' => self::Mobile,
			'clientSite', 'clientsite', 'clientlocation', 'client' => self::ClientSite,
			'fieldwork', 'field', 'campo', 'terreno' => self::FieldWork,
			'rotational', 'rotation', 'rotativo', 'turnos' => self::Rotational,
			'flyinflyout', 'fifo', 'commute', 'commuter' => self::FlyInFlyOut,

			// Other arrangements
			'coworking', 'coworkingspace', 'sharedoffice' => self::CoWorking,
			'offshore', 'offsite', 'international' => self::Offshore,
			'nomadic', 'nomad', 'locationindependent' => self::Nomadic,
			'projectbased', 'projectlocation', 'varybyproject' => self::ProjectBased,
			'seasonal', 'seasonallocation', 'temporarylocation' => self::Seasonal,

			default => null,
		};
	}

	/**
	 * Get labels in specified language
	 */
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

	/**
	 * Get label for this work presence type in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			// Onsite/Office - blue
			self::Onsite, self::Office, self::Headquarters,
			self::SatelliteOffice, self::RegionalOffice, self::OfficeFirst,
			self::NeverRemote, self::CoWorking => '#3b82f6',

			// Remote - green
			self::Remote, self::FullyRemote, self::AlwaysRemote,
			self::RemoteFirst, self::HomeOffice, self::Virtual,
			self::DigitalNomad, self::Anywhere, self::Distributed => '#10b981',

			// Hybrid/Flexible - purple
			self::Hybrid, self::Flexible, self::PartiallyRemote,
			self::OccasionalOffice, self::Rotational => '#8b5cf6',

			// Travel/Mobile/Field - amber
			self::Travel, self::Mobile, self::ClientSite,
			self::FieldWork, self::FlyInFlyOut => '#f59e0b',

			// Special/Other - gray
			self::Offshore, self::Nomadic, self::ProjectBased,
			self::Seasonal => '#6b7280',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Office/Onsite
			self::Onsite, self::Office, self::Headquarters,
			self::SatelliteOffice, self::RegionalOffice => 'building',

			// Remote
			self::Remote, self::FullyRemote, self::AlwaysRemote,
			self::Virtual, self::Anywhere => 'house',

			// Home office specific
			self::HomeOffice => 'home',
			self::DigitalNomad => 'globe',
			self::Distributed => 'network-wired',

			// Hybrid/Flexible
			self::Hybrid, self::Flexible, self::PartiallyRemote,
			self::OccasionalOffice => 'arrows-left-right',

			// Travel/Mobile
			self::Travel, self::Mobile, self::FlyInFlyOut => 'plane',
			self::ClientSite => 'user-friends',
			self::FieldWork => 'map',

			// Other
			self::CoWorking => 'users',
			self::RemoteFirst => 'laptop-house',
			self::OfficeFirst => 'building-user',
			self::Rotational => 'sync',
			self::ProjectBased => 'project-diagram',
			self::Seasonal => 'calendar',
			self::Offshore => 'anchor',
			self::Nomadic => 'route',

			default => 'briefcase',
		};
	}

	/**
	 * Check if this is primarily a remote work arrangement
	 */
	public function isRemote(): bool
	{
		return in_array($this, [
			self::Remote,
			self::FullyRemote,
			self::AlwaysRemote,
			self::RemoteFirst,
			self::HomeOffice,
			self::Virtual,
			self::DigitalNomad,
			self::Anywhere,
			self::Distributed,
		]);
	}

	/**
	 * Check if this is primarily an onsite/office arrangement
	 */
	public function isOnsite(): bool
	{
		return in_array($this, [
			self::Onsite,
			self::Office,
			self::Headquarters,
			self::SatelliteOffice,
			self::RegionalOffice,
			self::OfficeFirst,
			self::NeverRemote,
			self::CoWorking,
		]);
	}

	/**
	 * Check if this is a hybrid arrangement
	 */
	public function isHybrid(): bool
	{
		return in_array($this, [
			self::Hybrid,
			self::Flexible,
			self::PartiallyRemote,
			self::OccasionalOffice,
			self::Rotational,
		]);
	}

	/**
	 * Check if this involves travel/mobility
	 */
	public function involvesTravel(): bool
	{
		return in_array($this, [
			self::Travel,
			self::Mobile,
			self::ClientSite,
			self::FieldWork,
			self::FlyInFlyOut,
			self::Nomadic,
			self::DigitalNomad,
		]);
	}

	/**
	 * Check if this is a flexible arrangement
	 */
	public function isFlexible(): bool
	{
		return in_array($this, [
			self::Flexible,
			self::Hybrid,
			self::PartiallyRemote,
			self::OccasionalOffice,
			self::RemoteFirst,
			self::OfficeFirst,
			self::Anywhere,
		]);
	}

	/**
	 * Get the expected office presence percentage (0-100)
	 */
	public function getOfficePresencePercentage(): int
	{
		return match ($this) {
			self::AlwaysRemote, self::FullyRemote, self::Remote,
			self::Virtual, self::DigitalNomad, self::Anywhere => 0,

			self::RemoteFirst, self::PartiallyRemote => 20,
			self::OccasionalOffice, self::Hybrid => 40,
			self::Flexible => 50,
			self::Rotational => 60,
			self::OfficeFirst => 80,
			self::Onsite, self::Office, self::CoWorking => 100,
			self::NeverRemote, self::Headquarters => 100,

			// Travel/mobile varies
			self::Travel, self::Mobile, self::ClientSite,
			self::FieldWork, self::FlyInFlyOut, self::Nomadic => 10,

			// Others
			self::SatelliteOffice, self::RegionalOffice => 100,
			self::Offshore => 100,
			self::ProjectBased => 50,
			self::Seasonal => 50,
			self::Distributed => 0,
			self::HomeOffice => 0,
		};
	}

	/**
	 * Get simplified category
	 */
	public function getCategory(): string
	{
		if ($this->isRemote()) {
			return 'remote';
		} elseif ($this->isOnsite()) {
			return 'onsite';
		} elseif ($this->isHybrid()) {
			return 'hybrid';
		} elseif ($this->involvesTravel()) {
			return 'travel';
		} else {
			return 'other';
		}
	}

	/**
	 * Check if location is fixed
	 */
	public function isLocationFixed(): bool
	{
		return !in_array($this, [
			self::Travel,
			self::Mobile,
			self::DigitalNomad,
			self::Nomadic,
			self::Anywhere,
			self::Distributed,
			self::FieldWork,
			self::ClientSite,
		]);
	}

	/**
	 * Get recommended communication tools
	 */
	public function getRecommendedTools(): array
	{
		return match ($this) {
			self::Remote, self::FullyRemote, self::AlwaysRemote,
			self::RemoteFirst, self::Virtual, self::Distributed => [
				'video_conferencing',
				'instant_messaging',
				'project_management',
				'cloud_storage',
				'virtual_whiteboard',
			],

			self::Hybrid, self::Flexible, self::PartiallyRemote,
			self::OccasionalOffice => [
				'video_conferencing',
				'hybrid_meeting_tech',
				'collaboration_tools',
				'desk_booking',
			],

			self::Travel, self::Mobile, self::FieldWork,
			self::DigitalNomad, self::Nomadic => [
				'mobile_apps',
				'offline_access',
				'satellite_communication',
				'timezone_tools',
			],

			self::Onsite, self::Office, self::Headquarters,
			self::NeverRemote => [
				'in_person_meetings',
				'office_communication',
				'shared_workspaces',
			],

			default => ['general_communication'],
		};
	}

	/**
	 * Get timezone considerations
	 */
	public function getTimezoneConsideration(): string
	{
		return match ($this) {
			self::Distributed, self::Anywhere, self::DigitalNomad,
			self::Offshore => 'Multiple timezones, async work recommended',

			self::Remote, self::FullyRemote, self::RemoteFirst =>
			'Flexible timezones, core hours recommended',

			self::Hybrid, self::Flexible =>
			'Mix of local and flexible hours',

			self::Onsite, self::Office, self::Headquarters =>
			'Local timezone, fixed hours',

			self::Travel, self::Mobile, self::FlyInFlyOut =>
			'Variable timezones, requires flexibility',

			default => 'Depends on specific arrangement',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Onsite->value => 'On-Site',
			self::Office->value => 'Office',
			self::Remote->value => 'Remote',
			self::Hybrid->value => 'Hybrid',
			self::Flexible->value => 'Flexible',
			self::Travel->value => 'Travel',
			self::ClientSite->value => 'Client Site',
			self::FieldWork->value => 'Field Work',
			self::Mobile->value => 'Mobile',
			self::CoWorking->value => 'Co-Working Space',
			self::HomeOffice->value => 'Home Office',
			self::SatelliteOffice->value => 'Satellite Office',
			self::RegionalOffice->value => 'Regional Office',
			self::Headquarters->value => 'Headquarters',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomadic',
			self::FullyRemote->value => 'Fully Remote',
			self::RemoteFirst->value => 'Remote-First',
			self::OfficeFirst->value => 'Office-First',
			self::PartiallyRemote->value => 'Partially Remote',
			self::OccasionalOffice->value => 'Occasional Office',
			self::NeverRemote->value => 'Never Remote',
			self::AlwaysRemote->value => 'Always Remote',
			self::Rotational->value => 'Rotational',
			self::ProjectBased->value => 'Project-Based Location',
			self::Seasonal->value => 'Seasonal Location',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Digital Nomad',
			self::Anywhere->value => 'Work From Anywhere',
			self::Distributed->value => 'Distributed',
			self::Virtual->value => 'Virtual Office',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Onsite->value => 'Presencial',
			self::Office->value => 'Escritório',
			self::Remote->value => 'Remoto',
			self::Hybrid->value => 'Híbrido',
			self::Flexible->value => 'Flexível',
			self::Travel->value => 'Viagens',
			self::ClientSite->value => 'Cliente',
			self::FieldWork->value => 'Trabalho de Campo',
			self::Mobile->value => 'Móvel',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Home Office',
			self::SatelliteOffice->value => 'Escritório Satélite',
			self::RegionalOffice->value => 'Escritório Regional',
			self::Headquarters->value => 'Matriz',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nômade',
			self::FullyRemote->value => 'Totalmente Remoto',
			self::RemoteFirst->value => 'Remoto-Primeiro',
			self::OfficeFirst->value => 'Escritório-Primeiro',
			self::PartiallyRemote->value => 'Parcialmente Remoto',
			self::OccasionalOffice->value => 'Escritório Ocasional',
			self::NeverRemote->value => 'Nunca Remoto',
			self::AlwaysRemote->value => 'Sempre Remoto',
			self::Rotational->value => 'Rotativo',
			self::ProjectBased->value => 'Local por Projeto',
			self::Seasonal->value => 'Local Sazonal',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Nômade Digital',
			self::Anywhere->value => 'Trabalhe de Qualquer Lugar',
			self::Distributed->value => 'Distribuído',
			self::Virtual->value => 'Escritório Virtual',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Onsite->value => 'Presencial',
			self::Office->value => 'Oficina',
			self::Remote->value => 'Remoto',
			self::Hybrid->value => 'Híbrido',
			self::Flexible->value => 'Flexible',
			self::Travel->value => 'Viajes',
			self::ClientSite->value => 'Cliente',
			self::FieldWork->value => 'Trabajo de Campo',
			self::Mobile->value => 'Móvil',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Oficina en Casa',
			self::SatelliteOffice->value => 'Oficina Satélite',
			self::RegionalOffice->value => 'Oficina Regional',
			self::Headquarters->value => 'Sede',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nómada',
			self::FullyRemote->value => 'Totalmente Remoto',
			self::RemoteFirst->value => 'Remoto-Primero',
			self::OfficeFirst->value => 'Oficina-Primero',
			self::PartiallyRemote->value => 'Parcialmente Remoto',
			self::OccasionalOffice->value => 'Oficina Ocasional',
			self::NeverRemote->value => 'Nunca Remoto',
			self::AlwaysRemote->value => 'Siempre Remoto',
			self::Rotational->value => 'Rotativo',
			self::ProjectBased->value => 'Ubicación por Proyecto',
			self::Seasonal->value => 'Ubicación Estacional',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Nómada Digital',
			self::Anywhere->value => 'Trabaja desde Cualquier Lugar',
			self::Distributed->value => 'Distribuido',
			self::Virtual->value => 'Oficina Virtual',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Onsite->value => 'Vor Ort',
			self::Office->value => 'Büro',
			self::Remote->value => 'Remote',
			self::Hybrid->value => 'Hybrid',
			self::Flexible->value => 'Flexibel',
			self::Travel->value => 'Reisen',
			self::ClientSite->value => 'Kunde',
			self::FieldWork->value => 'Außendienst',
			self::Mobile->value => 'Mobil',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Homeoffice',
			self::SatelliteOffice->value => 'Niederlassung',
			self::RegionalOffice->value => 'Regionalbüro',
			self::Headquarters->value => 'Hauptsitz',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomadisch',
			self::FullyRemote->value => 'Vollständig Remote',
			self::RemoteFirst->value => 'Remote-First',
			self::OfficeFirst->value => 'Büro-First',
			self::PartiallyRemote->value => 'Teilweise Remote',
			self::OccasionalOffice->value => 'Gelegentliches Büro',
			self::NeverRemote->value => 'Nie Remote',
			self::AlwaysRemote->value => 'Immer Remote',
			self::Rotational->value => 'Rotierend',
			self::ProjectBased->value => 'Projektbasierter Standort',
			self::Seasonal->value => 'Saisonstandort',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Digitaler Nomade',
			self::Anywhere->value => 'Arbeiten von Überall',
			self::Distributed->value => 'Verteilt',
			self::Virtual->value => 'Virtuelles Büro',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Onsite->value => 'Sur Site',
			self::Office->value => 'Bureau',
			self::Remote->value => 'Distanciel',
			self::Hybrid->value => 'Hybride',
			self::Flexible->value => 'Flexible',
			self::Travel->value => 'Déplacements',
			self::ClientSite->value => 'Client',
			self::FieldWork->value => 'Travail sur le Terrain',
			self::Mobile->value => 'Mobile',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Télétravail',
			self::SatelliteOffice->value => 'Bureau Satellite',
			self::RegionalOffice->value => 'Bureau Régional',
			self::Headquarters->value => 'Siège Social',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomade',
			self::FullyRemote->value => 'Entièrement à Distance',
			self::RemoteFirst->value => 'Remote-First',
			self::OfficeFirst->value => 'Bureau-First',
			self::PartiallyRemote->value => 'Partiellement à Distance',
			self::OccasionalOffice->value => 'Bureau Occasionnel',
			self::NeverRemote->value => 'Jamais à Distance',
			self::AlwaysRemote->value => 'Toujours à Distance',
			self::Rotational->value => 'Rotation',
			self::ProjectBased->value => 'Localisation par Projet',
			self::Seasonal->value => 'Localisation Saisonnière',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Nomade Numérique',
			self::Anywhere->value => 'Travail de N\'importe Où',
			self::Distributed->value => 'Distribué',
			self::Virtual->value => 'Bureau Virtuel',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Onsite->value => 'In Sede',
			self::Office->value => 'Ufficio',
			self::Remote->value => 'Remoto',
			self::Hybrid->value => 'Ibrido',
			self::Flexible->value => 'Flessibile',
			self::Travel->value => 'Viaggi',
			self::ClientSite->value => 'Cliente',
			self::FieldWork->value => 'Lavoro sul Campo',
			self::Mobile->value => 'Mobile',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Smart Working',
			self::SatelliteOffice->value => 'Ufficio Satellite',
			self::RegionalOffice->value => 'Ufficio Regionale',
			self::Headquarters->value => 'Sede Centrale',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomade',
			self::FullyRemote->value => 'Completamente Remoto',
			self::RemoteFirst->value => 'Remote-First',
			self::OfficeFirst->value => 'Ufficio-First',
			self::PartiallyRemote->value => 'Parzialmente Remoto',
			self::OccasionalOffice->value => 'Ufficio Occasionale',
			self::NeverRemote->value => 'Mai Remoto',
			self::AlwaysRemote->value => 'Sempre Remoto',
			self::Rotational->value => 'Rotativo',
			self::ProjectBased->value => 'Localizzazione per Progetto',
			self::Seasonal->value => 'Localizzazione Stagionale',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Nomade Digitale',
			self::Anywhere->value => 'Lavora da Ovunque',
			self::Distributed->value => 'Distribuito',
			self::Virtual->value => 'Ufficio Virtuale',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Onsite->value => 'Op Locatie',
			self::Office->value => 'Kantoor',
			self::Remote->value => 'Remote',
			self::Hybrid->value => 'Hybride',
			self::Flexible->value => 'Flexibel',
			self::Travel->value => 'Reizen',
			self::ClientSite->value => 'Klant',
			self::FieldWork->value => 'Veldwerk',
			self::Mobile->value => 'Mobiel',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Thuiswerken',
			self::SatelliteOffice->value => 'Satellietkantoor',
			self::RegionalOffice->value => 'Regionaal Kantoor',
			self::Headquarters->value => 'Hoofdkantoor',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomadisch',
			self::FullyRemote->value => 'Volledig Remote',
			self::RemoteFirst->value => 'Remote-First',
			self::OfficeFirst->value => 'Kantoor-First',
			self::PartiallyRemote->value => 'Gedeeltelijk Remote',
			self::OccasionalOffice->value => 'Af en toe Kantoor',
			self::NeverRemote->value => 'Nooit Remote',
			self::AlwaysRemote->value => 'Altijd Remote',
			self::Rotational->value => 'Roterend',
			self::ProjectBased->value => 'Projectlocatie',
			self::Seasonal->value => 'Seizoenslocatie',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Digitale Nomade',
			self::Anywhere->value => 'Werken van Overal',
			self::Distributed->value => 'Gedistribueerd',
			self::Virtual->value => 'Virtueel Kantoor',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Onsite->value => 'Na Miejscu',
			self::Office->value => 'Biuro',
			self::Remote->value => 'Zdalnie',
			self::Hybrid->value => 'Hybrydowo',
			self::Flexible->value => 'Elastycznie',
			self::Travel->value => 'Podróże',
			self::ClientSite->value => 'Klient',
			self::FieldWork->value => 'Praca w Terenie',
			self::Mobile->value => 'Mobilnie',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Praca z Domu',
			self::SatelliteOffice->value => 'Biuro Satelitarne',
			self::RegionalOffice->value => 'Biuro Regionalne',
			self::Headquarters->value => 'Siedziba Główna',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomadycznie',
			self::FullyRemote->value => 'W pełni Zdalnie',
			self::RemoteFirst->value => 'Zdalnie-Pierwsze',
			self::OfficeFirst->value => 'Biuro-Pierwsze',
			self::PartiallyRemote->value => 'Częściowo Zdalnie',
			self::OccasionalOffice->value => 'Okazjonalne Biuro',
			self::NeverRemote->value => 'Nigdy Zdalnie',
			self::AlwaysRemote->value => 'Zawsze Zdalnie',
			self::Rotational->value => 'Rotacyjnie',
			self::ProjectBased->value => 'Lokalizacja Projektowa',
			self::Seasonal->value => 'Lokalizacja Sezonowa',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Cyfrowy Nomada',
			self::Anywhere->value => 'Praca z Dowolnego Miejsca',
			self::Distributed->value => 'Rozproszony',
			self::Virtual->value => 'Wirtualne Biuro',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Onsite->value => 'В Офисе',
			self::Office->value => 'Офис',
			self::Remote->value => 'Удаленно',
			self::Hybrid->value => 'Гибридный',
			self::Flexible->value => 'Гибкий',
			self::Travel->value => 'Путешествия',
			self::ClientSite->value => 'Клиент',
			self::FieldWork->value => 'Полевая Работа',
			self::Mobile->value => 'Мобильный',
			self::CoWorking->value => 'Коворкинг',
			self::HomeOffice->value => 'Удаленная Работа',
			self::SatelliteOffice->value => 'Филиал',
			self::RegionalOffice->value => 'Региональный Офис',
			self::Headquarters->value => 'Штаб-Квартира',
			self::Offshore->value => 'Офшор',
			self::Nomadic->value => 'Кочевой',
			self::FullyRemote->value => 'Полностью Удаленно',
			self::RemoteFirst->value => 'Удаленный-Первый',
			self::OfficeFirst->value => 'Офисный-Первый',
			self::PartiallyRemote->value => 'Частично Удаленно',
			self::OccasionalOffice->value => 'Иногда в Офисе',
			self::NeverRemote->value => 'Никогда Удаленно',
			self::AlwaysRemote->value => 'Всегда Удаленно',
			self::Rotational->value => 'Вращающийся',
			self::ProjectBased->value => 'Проектная Локация',
			self::Seasonal->value => 'Сезонная Локация',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Цифровой Кочевник',
			self::Anywhere->value => 'Работа из Любой Точки',
			self::Distributed->value => 'Распределенный',
			self::Virtual->value => 'Виртуальный Офис',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Onsite->value => 'Yerinde',
			self::Office->value => 'Ofis',
			self::Remote->value => 'Uzaktan',
			self::Hybrid->value => 'Hibrit',
			self::Flexible->value => 'Esnek',
			self::Travel->value => 'Seyahat',
			self::ClientSite->value => 'Müşteri',
			self::FieldWork->value => 'Saha Çalışması',
			self::Mobile->value => 'Mobil',
			self::CoWorking->value => 'Ortak Çalışma Alanı',
			self::HomeOffice->value => 'Ev Ofisi',
			self::SatelliteOffice->value => 'Uydu Ofis',
			self::RegionalOffice->value => 'Bölge Ofisi',
			self::Headquarters->value => 'Merkez',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Göçebe',
			self::FullyRemote->value => 'Tamamen Uzaktan',
			self::RemoteFirst->value => 'Uzaktan-Öncelikli',
			self::OfficeFirst->value => 'Ofis-Öncelikli',
			self::PartiallyRemote->value => 'Kısmen Uzaktan',
			self::OccasionalOffice->value => 'Ara Sıra Ofis',
			self::NeverRemote->value => 'Asla Uzaktan Değil',
			self::AlwaysRemote->value => 'Her Zaman Uzaktan',
			self::Rotational->value => 'Dönüşümlü',
			self::ProjectBased->value => 'Proje Bazlı Konum',
			self::Seasonal->value => 'Mevsimsel Konum',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Dijital Göçebe',
			self::Anywhere->value => 'Her Yerden Çalışma',
			self::Distributed->value => 'Dağıtık',
			self::Virtual->value => 'Sanal Ofis',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Onsite->value => 'في الموقع',
			self::Office->value => 'مكتب',
			self::Remote->value => 'عن بعد',
			self::Hybrid->value => 'هجين',
			self::Flexible->value => 'مرن',
			self::Travel->value => 'سفر',
			self::ClientSite->value => 'عميل',
			self::FieldWork->value => 'عمل ميداني',
			self::Mobile->value => 'متنقل',
			self::CoWorking->value => 'مساحة عمل مشتركة',
			self::HomeOffice->value => 'عمل من المنزل',
			self::SatelliteOffice->value => 'مكتب تابع',
			self::RegionalOffice->value => 'مكتب إقليمي',
			self::Headquarters->value => 'المقر الرئيسي',
			self::Offshore->value => 'أوفشور',
			self::Nomadic->value => 'بدوي',
			self::FullyRemote->value => 'عن بعد بالكامل',
			self::RemoteFirst->value => 'عن بعد أولاً',
			self::OfficeFirst->value => 'مكتب أولاً',
			self::PartiallyRemote->value => 'جزئياً عن بعد',
			self::OccasionalOffice->value => 'مكتب أحياناً',
			self::NeverRemote->value => 'أبداً عن بعد',
			self::AlwaysRemote->value => 'دائماً عن بعد',
			self::Rotational->value => 'دوري',
			self::ProjectBased->value => 'موقع المشروع',
			self::Seasonal->value => 'موقع موسمي',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'بدوي رقمي',
			self::Anywhere->value => 'العمل من أي مكان',
			self::Distributed->value => 'موزع',
			self::Virtual->value => 'مكتب افتراضي',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Onsite->value => 'במקום',
			self::Office->value => 'משרד',
			self::Remote->value => 'מרוחק',
			self::Hybrid->value => 'היברידי',
			self::Flexible->value => 'גמיש',
			self::Travel->value => 'נסיעות',
			self::ClientSite->value => 'לקוח',
			self::FieldWork->value => 'עבודה בשטח',
			self::Mobile->value => 'נייד',
			self::CoWorking->value => 'קו-וורקינג',
			self::HomeOffice->value => 'עבודה מהבית',
			self::SatelliteOffice->value => 'משרד לוויין',
			self::RegionalOffice->value => 'משרד אזורי',
			self::Headquarters->value => 'מטה',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'נודד',
			self::FullyRemote->value => 'מרוחק לחלוטין',
			self::RemoteFirst->value => 'מרוחק-ראשון',
			self::OfficeFirst->value => 'משרד-ראשון',
			self::PartiallyRemote->value => 'מרוחק חלקית',
			self::OccasionalOffice->value => 'משרד מזדמן',
			self::NeverRemote->value => 'אף פעם לא מרוחק',
			self::AlwaysRemote->value => 'תמיד מרוחק',
			self::Rotational->value => 'רוטציוני',
			self::ProjectBased->value => 'מיקום לפי פרויקט',
			self::Seasonal->value => 'מיקום עונתי',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'נומאד דיגיטלי',
			self::Anywhere->value => 'עבודה מכל מקום',
			self::Distributed->value => 'מבוזר',
			self::Virtual->value => 'משרד וירטואלי',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Onsite->value => 'オフィス出社',
			self::Office->value => 'オフィス',
			self::Remote->value => 'リモート',
			self::Hybrid->value => 'ハイブリッド',
			self::Flexible->value => 'フレキシブル',
			self::Travel->value => '出張',
			self::ClientSite->value => 'クライアント先',
			self::FieldWork->value => 'フィールドワーク',
			self::Mobile->value => 'モバイル',
			self::CoWorking->value => 'コワーキングスペース',
			self::HomeOffice->value => '在宅勤務',
			self::SatelliteOffice->value => 'サテライトオフィス',
			self::RegionalOffice->value => '地域オフィス',
			self::Headquarters->value => '本社',
			self::Offshore->value => 'オフショア',
			self::Nomadic->value => 'ノマド',
			self::FullyRemote->value => '完全リモート',
			self::RemoteFirst->value => 'リモートファースト',
			self::OfficeFirst->value => 'オフィスファースト',
			self::PartiallyRemote->value => '部分リモート',
			self::OccasionalOffice->value => '時々オフィス',
			self::NeverRemote->value => 'リモート不可',
			self::AlwaysRemote->value => '常時リモート',
			self::Rotational->value => 'ローテーション',
			self::ProjectBased->value => 'プロジェクト別場所',
			self::Seasonal->value => '季節別場所',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'デジタルノマド',
			self::Anywhere->value => 'どこでも仕事',
			self::Distributed->value => '分散型',
			self::Virtual->value => 'バーチャルオフィス',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Onsite->value => 'På Stedet',
			self::Office->value => 'Kontor',
			self::Remote->value => 'Fjern',
			self::Hybrid->value => 'Hybrid',
			self::Flexible->value => 'Fleksibel',
			self::Travel->value => 'Rejser',
			self::ClientSite->value => 'Kunde',
			self::FieldWork->value => 'Markarbejde',
			self::Mobile->value => 'Mobil',
			self::CoWorking->value => 'Coworking',
			self::HomeOffice->value => 'Hjemmekontor',
			self::SatelliteOffice->value => 'Satellitkontor',
			self::RegionalOffice->value => 'Regionalt Kontor',
			self::Headquarters->value => 'Hovedkvarter',
			self::Offshore->value => 'Offshore',
			self::Nomadic->value => 'Nomadisk',
			self::FullyRemote->value => 'Fuldstændig Fjern',
			self::RemoteFirst->value => 'Fjern-Først',
			self::OfficeFirst->value => 'Kontor-Først',
			self::PartiallyRemote->value => 'Delvist Fjern',
			self::OccasionalOffice->value => 'Lejlighedsvis Kontor',
			self::NeverRemote->value => 'Aldrig Fjern',
			self::AlwaysRemote->value => 'Altid Fjern',
			self::Rotational->value => 'Rotation',
			self::ProjectBased->value => 'Projektplacering',
			self::Seasonal->value => 'Sæsonplacering',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => 'Digital Nomade',
			self::Anywhere->value => 'Arbejd Overalt',
			self::Distributed->value => 'Distribueret',
			self::Virtual->value => 'Virtuelt Kontor',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::Onsite->value => '现场办公',
			self::Office->value => '办公室',
			self::Remote->value => '远程',
			self::Hybrid->value => '混合',
			self::Flexible->value => '灵活',
			self::Travel->value => '出差',
			self::ClientSite->value => '客户现场',
			self::FieldWork->value => '现场工作',
			self::Mobile->value => '移动',
			self::CoWorking->value => '共享办公',
			self::HomeOffice->value => '家庭办公',
			self::SatelliteOffice->value => '卫星办公室',
			self::RegionalOffice->value => '区域办公室',
			self::Headquarters->value => '总部',
			self::Offshore->value => '离岸',
			self::Nomadic->value => '游牧式',
			self::FullyRemote->value => '完全远程',
			self::RemoteFirst->value => '远程优先',
			self::OfficeFirst->value => '办公室优先',
			self::PartiallyRemote->value => '部分远程',
			self::OccasionalOffice->value => '偶尔办公室',
			self::NeverRemote->value => '从不远程',
			self::AlwaysRemote->value => '始终远程',
			self::Rotational->value => '轮换',
			self::ProjectBased->value => '项目地点',
			self::Seasonal->value => '季节地点',
			self::FlyInFlyOut->value => 'Fly-In Fly-Out',
			self::DigitalNomad->value => '数字游民',
			self::Anywhere->value => '随时随地工作',
			self::Distributed->value => '分布式',
			self::Virtual->value => '虚拟办公室',
		];
	}
}
