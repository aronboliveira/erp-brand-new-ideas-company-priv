<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum Visibility: string
{
	// Basic visibility levels
	case Public = 'public';
	case Private = 'private';
	case Internal = 'internal';
	case Draft = 'draft';
	case Scheduled = 'scheduled';
	case Archived = 'archived';
	case Protected = 'protected';
	case Unlisted = 'unlisted';

		// Advanced/organizational visibility
	case Restricted = 'restricted';
	case Confidential = 'confidential';
	case Secret = 'secret';
	case TopSecret = 'top_secret';
	case Classified = 'classified';

		// Role-based visibility
	case AdminOnly = 'admin_only';
	case ManagerOnly = 'manager_only';
	case StaffOnly = 'staff_only';
	case MemberOnly = 'member_only';
	case CustomerOnly = 'customer_only';
	case PartnerOnly = 'partner_only';
	case VendorOnly = 'vendor_only';

		// Department/team based
	case DepartmentSpecific = 'department_specific';
	case TeamSpecific = 'team_specific';
	case ProjectSpecific = 'project_specific';
	case LocationSpecific = 'location_specific';

		// Time-based visibility
	case TimeLimited = 'time_limited';
	case Temporary = 'temporary';
	case Expiring = 'expiring';
	case FutureRelease = 'future_release';

		// Content-specific
	case AgeRestricted = 'age_restricted';
	case GeographicRestricted = 'geographic_restricted';
	case LanguageSpecific = 'language_specific';
	case PlatformSpecific = 'platform_specific';

		// Collaboration
	case Shared = 'shared';
	case CollaboratorsOnly = 'collaborators_only';
	case InviteOnly = 'invite_only';
	case GuestAccess = 'guest_access';

		// System/technical
	case SystemOnly = 'system_only';
	case MaintenanceMode = 'maintenance_mode';
	case Beta = 'beta';
	case Preview = 'preview';
	case Staging = 'staging';

	/**
	 * Normalize input to Visibility
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
			// Basic visibility
			'public', 'open', 'everyone', 'all' => self::Public,
			'private', 'personal', 'personalonly', 'self' => self::Private,
			'internal', 'companyonly', 'organization', 'orgonly' => self::Internal,
			'draft', 'unpublished', 'notpublished', 'inprogress' => self::Draft,
			'scheduled', 'scheduledforpublish', 'futurepublish' => self::Scheduled,
			'archived', 'deactivated', 'inactive', 'historical' => self::Archived,
			'protected', 'passwordprotected', 'accesscontrolled' => self::Protected,
			'unlisted', 'hidden', 'notinlist', 'directaccessonly' => self::Unlisted,

			// Advanced security
			'restricted', 'limitedaccess', 'controlled' => self::Restricted,
			'confidential', 'sensitive', 'forinternaluse' => self::Confidential,
			'secret', 'highlyconfidential', 'restrictedaccess' => self::Secret,
			'topsecret', 'toplevelsecret', 'ultrasecret' => self::TopSecret,
			'classified', 'securityclassified', 'officialuseonly' => self::Classified,

			// Role-based
			'adminonly', 'administrators', 'sysadmin' => self::AdminOnly,
			'manageronly', 'management', 'supervisors' => self::ManagerOnly,
			'staffonly', 'employees', 'workersonly' => self::StaffOnly,
			'memberonly', 'members', 'registeredusers' => self::MemberOnly,
			'customeronly', 'clients', 'subscribers' => self::CustomerOnly,
			'partneronly', 'businesspartners', 'affiliates' => self::PartnerOnly,
			'vendoronly', 'suppliers', 'contractors' => self::VendorOnly,

			// Department/team
			'departmentspecific', 'departmental', 'deptonly' => self::DepartmentSpecific,
			'teamspecific', 'teamonly', 'workinggroup' => self::TeamSpecific,
			'projectspecific', 'projectonly', 'initiativespecific' => self::ProjectSpecific,
			'locationspecific', 'regional', 'branchspecific' => self::LocationSpecific,

			// Time-based
			'timelimited', 'limitedtime', 'temporaryaccess' => self::TimeLimited,
			'temporary', 'temp', 'provisional' => self::Temporary,
			'expiring', 'expiresoon', 'limitedduration' => self::Expiring,
			'futurerelease', 'prerelease', 'upcoming' => self::FutureRelease,

			// Content-specific
			'agerestricted', 'adultsonly', 'mature' => self::AgeRestricted,
			'geographicrestricted', 'regionlocked', 'countryspecific' => self::GeographicRestricted,
			'languagespecific', 'localeonly', 'localized' => self::LanguageSpecific,
			'platformspecific', 'deviceonly', 'osonly' => self::PlatformSpecific,

			// Collaboration
			'shared', 'sharedaccess', 'sharedwithothers' => self::Shared,
			'collaboratorsonly', 'teammembers', 'workinggrouponly' => self::CollaboratorsOnly,
			'inviteonly', 'byinvitation', 'invitationrequired' => self::InviteOnly,
			'guestaccess', 'guests', 'visitoraccess' => self::GuestAccess,

			// System/technical
			'systemonly', 'system', 'technicalonly' => self::SystemOnly,
			'maintenancemode', 'maintenance', 'underconstruction' => self::MaintenanceMode,
			'beta', 'betatesting', 'experimental' => self::Beta,
			'preview', 'previewonly', 'sneakpeek' => self::Preview,
			'staging', 'stagingenvironment', 'testenvironment' => self::Staging,

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
	 * Get label for this visibility level in specified language
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
			// Public/open - green
			self::Public, self::Shared, self::GuestAccess => '#10b981',

			// Private/restricted - red/orange
			self::Private, self::Restricted, self::Confidential,
			self::Secret, self::TopSecret, self::Classified => '#ef4444',

			// Internal/organizational - blue
			self::Internal, self::StaffOnly, self::MemberOnly,
			self::DepartmentSpecific, self::TeamSpecific,
			self::ProjectSpecific, self::LocationSpecific => '#3b82f6',

			// Draft/working - yellow
			self::Draft, self::Scheduled, self::Temporary,
			self::Beta, self::Preview, self::Staging => '#f59e0b',

			// Archived/inactive - gray
			self::Archived, self::Expiring, self::MaintenanceMode => '#6b7280',

			// Protected/controlled - purple
			self::Protected, self::Unlisted, self::TimeLimited,
			self::AgeRestricted, self::GeographicRestricted,
			self::LanguageSpecific, self::PlatformSpecific => '#8b5cf6',

			// Role-based - indigo
			self::AdminOnly, self::ManagerOnly, self::CustomerOnly,
			self::PartnerOnly, self::VendorOnly, self::SystemOnly => '#6366f1',

			// Collaboration - teal
			self::CollaboratorsOnly, self::InviteOnly => '#14b8a6',

			// Future/temporary - amber
			self::FutureRelease => '#d97706',

			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Public/open
			self::Public => 'globe',
			self::Shared => 'share-alt',
			self::GuestAccess => 'user-friends',

			// Private/restricted
			self::Private => 'lock',
			self::Restricted, self::Confidential => 'shield-alt',
			self::Secret, self::TopSecret, self::Classified => 'user-secret',

			// Internal/organizational
			self::Internal => 'building',
			self::StaffOnly, self::MemberOnly => 'users',
			self::DepartmentSpecific => 'sitemap',
			self::TeamSpecific => 'user-friends',
			self::ProjectSpecific => 'project-diagram',
			self::LocationSpecific => 'map-marker-alt',

			// Draft/working
			self::Draft => 'edit',
			self::Scheduled => 'calendar-alt',
			self::Temporary => 'clock',
			self::Beta => 'flask',
			self::Preview => 'eye',
			self::Staging => 'code-branch',

			// Archived/inactive
			self::Archived => 'archive',
			self::Expiring => 'hourglass-end',
			self::MaintenanceMode => 'tools',

			// Protected/controlled
			self::Protected => 'key',
			self::Unlisted => 'eye-slash',
			self::TimeLimited => 'hourglass-half',
			self::AgeRestricted => 'birthday-cake',
			self::GeographicRestricted => 'globe-americas',
			self::LanguageSpecific => 'language',
			self::PlatformSpecific => 'laptop',

			// Role-based
			self::AdminOnly => 'user-shield',
			self::ManagerOnly => 'user-tie',
			self::CustomerOnly => 'user-tag',
			self::PartnerOnly => 'handshake',
			self::VendorOnly => 'truck',
			self::SystemOnly => 'server',

			// Collaboration
			self::CollaboratorsOnly => 'user-check',
			self::InviteOnly => 'envelope',

			// Future
			self::FutureRelease => 'rocket',

			default => 'eye',
		};
	}

	/**
	 * Check if this is a publicly accessible visibility
	 */
	public function isPublic(): bool
	{
		return in_array($this, [
			self::Public,
			self::Shared,
			self::GuestAccess,
			self::Unlisted,
		]);
	}

	/**
	 * Check if this is a restricted/private visibility
	 */
	public function isRestricted(): bool
	{
		return in_array($this, [
			self::Private,
			self::Restricted,
			self::Confidential,
			self::Secret,
			self::TopSecret,
			self::Classified,
			self::Protected,
			self::InviteOnly,
		]);
	}

	/**
	 * Check if this is an organizational/internal visibility
	 */
	public function isInternal(): bool
	{
		return in_array($this, [
			self::Internal,
			self::StaffOnly,
			self::MemberOnly,
			self::DepartmentSpecific,
			self::TeamSpecific,
			self::ProjectSpecific,
			self::LocationSpecific,
			self::CollaboratorsOnly,
		]);
	}

	/**
	 * Check if this is a draft/work in progress visibility
	 */
	public function isDraft(): bool
	{
		return in_array($this, [
			self::Draft,
			self::Scheduled,
			self::Beta,
			self::Preview,
			self::Staging,
			self::FutureRelease,
		]);
	}

	/**
	 * Check if this is role-based visibility
	 */
	public function isRoleBased(): bool
	{
		return in_array($this, [
			self::AdminOnly,
			self::ManagerOnly,
			self::StaffOnly,
			self::MemberOnly,
			self::CustomerOnly,
			self::PartnerOnly,
			self::VendorOnly,
		]);
	}

	/**
	 * Check if visibility has time restrictions
	 */
	public function hasTimeRestrictions(): bool
	{
		return in_array($this, [
			self::TimeLimited,
			self::Temporary,
			self::Expiring,
			self::Scheduled,
			self::FutureRelease,
		]);
	}

	/**
	 * Get security level (1-5, where 5 is highest security)
	 */
	public function getSecurityLevel(): int
	{
		return match ($this) {
			self::Public, self::Shared, self::GuestAccess => 1,
			self::Unlisted, self::MemberOnly, self::CustomerOnly => 2,
			self::Internal, self::StaffOnly, self::DepartmentSpecific => 3,
			self::Private, self::Protected, self::Restricted => 4,
			self::Confidential, self::Secret, self::TopSecret,
			self::Classified, self::AdminOnly => 5,
			default => 3,
		};
	}

	/**
	 * Get recommended access control method
	 */
	public function getAccessControlMethod(): string
	{
		return match ($this) {
			self::Public, self::Unlisted => 'No authentication required',
			self::Private, self::Restricted => 'User authentication required',
			self::Internal, self::StaffOnly => 'Organization authentication',
			self::Protected => 'Password or token required',
			self::Confidential, self::Secret => 'Role-based access control',
			self::TopSecret, self::Classified => 'Multi-factor authentication + auditing',
			self::AdminOnly, self::ManagerOnly => 'Role hierarchy access control',
			self::DepartmentSpecific, self::TeamSpecific => 'Group-based permissions',
			self::TimeLimited, self::Expiring => 'Time-based access tokens',
			self::AgeRestricted => 'Age verification system',
			self::GeographicRestricted => 'IP-based geolocation',
			self::LanguageSpecific => 'Language preference detection',
			self::InviteOnly => 'Invitation code system',
			self::Beta, self::Preview => 'Beta testing program access',
			default => 'Standard authentication',
		};
	}

	/**
	 * Get visibility category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Public access
			self::Public, self::Shared, self::GuestAccess,
			self::Unlisted => 'public_access',

			// Private/restricted
			self::Private, self::Protected, self::Restricted,
			self::Confidential, self::Secret, self::TopSecret,
			self::Classified => 'restricted_access',

			// Organizational
			self::Internal, self::StaffOnly, self::MemberOnly,
			self::DepartmentSpecific, self::TeamSpecific,
			self::ProjectSpecific, self::LocationSpecific => 'organizational',

			// Role-based
			self::AdminOnly, self::ManagerOnly, self::CustomerOnly,
			self::PartnerOnly, self::VendorOnly => 'role_based',

			// Time/content based
			self::TimeLimited, self::Temporary, self::Expiring,
			self::Scheduled, self::FutureRelease => 'time_based',

			// Content restrictions
			self::AgeRestricted, self::GeographicRestricted,
			self::LanguageSpecific, self::PlatformSpecific => 'content_restricted',

			// Collaboration
			self::CollaboratorsOnly, self::InviteOnly => 'collaboration',

			// Draft/development
			self::Draft, self::Beta, self::Preview,
			self::Staging, self::MaintenanceMode => 'development',

			// Archived
			self::Archived => 'archived',

			// System
			self::SystemOnly => 'system',

			default => 'other',
		};
	}

	/**
	 * Check if visibility should be indexed by search engines
	 */
	public function shouldIndex(): bool
	{
		return match ($this) {
			self::Public => true,
			self::Unlisted => false, // Unlisted shouldn't appear in public listings but can be accessed
			default => false, // All others should not be indexed
		};
	}

	/**
	 * Check if content requires authentication
	 */
	public function requiresAuthentication(): bool
	{
		return !in_array($this, [
			self::Public,
			self::Unlisted, // Can be accessed without auth via direct link
		]);
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Public',
			self::Private->value => 'Private',
			self::Internal->value => 'Internal',
			self::Draft->value => 'Draft',
			self::Scheduled->value => 'Scheduled',
			self::Archived->value => 'Archived',
			self::Protected->value => 'Protected',
			self::Unlisted->value => 'Unlisted',

			// Advanced security
			self::Restricted->value => 'Restricted',
			self::Confidential->value => 'Confidential',
			self::Secret->value => 'Secret',
			self::TopSecret->value => 'Top Secret',
			self::Classified->value => 'Classified',

			// Role-based
			self::AdminOnly->value => 'Admin Only',
			self::ManagerOnly->value => 'Manager Only',
			self::StaffOnly->value => 'Staff Only',
			self::MemberOnly->value => 'Member Only',
			self::CustomerOnly->value => 'Customer Only',
			self::PartnerOnly->value => 'Partner Only',
			self::VendorOnly->value => 'Vendor Only',

			// Department/team
			self::DepartmentSpecific->value => 'Department Specific',
			self::TeamSpecific->value => 'Team Specific',
			self::ProjectSpecific->value => 'Project Specific',
			self::LocationSpecific->value => 'Location Specific',

			// Time-based
			self::TimeLimited->value => 'Time Limited',
			self::Temporary->value => 'Temporary',
			self::Expiring->value => 'Expiring',
			self::FutureRelease->value => 'Future Release',

			// Content-specific
			self::AgeRestricted->value => 'Age Restricted',
			self::GeographicRestricted->value => 'Geographic Restricted',
			self::LanguageSpecific->value => 'Language Specific',
			self::PlatformSpecific->value => 'Platform Specific',

			// Collaboration
			self::Shared->value => 'Shared',
			self::CollaboratorsOnly->value => 'Collaborators Only',
			self::InviteOnly->value => 'Invite Only',
			self::GuestAccess->value => 'Guest Access',

			// System/technical
			self::SystemOnly->value => 'System Only',
			self::MaintenanceMode->value => 'Maintenance Mode',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Preview',
			self::Staging->value => 'Staging',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Público',
			self::Private->value => 'Privado',
			self::Internal->value => 'Interno',
			self::Draft->value => 'Rascunho',
			self::Scheduled->value => 'Agendado',
			self::Archived->value => 'Arquivado',
			self::Protected->value => 'Protegido',
			self::Unlisted->value => 'Não Listado',

			// Advanced security
			self::Restricted->value => 'Restrito',
			self::Confidential->value => 'Confidencial',
			self::Secret->value => 'Secreto',
			self::TopSecret->value => 'Ultra Secreto',
			self::Classified->value => 'Classificado',

			// Role-based
			self::AdminOnly->value => 'Apenas Admin',
			self::ManagerOnly->value => 'Apenas Gerentes',
			self::StaffOnly->value => 'Apenas Funcionários',
			self::MemberOnly->value => 'Apenas Membros',
			self::CustomerOnly->value => 'Apenas Clientes',
			self::PartnerOnly->value => 'Apenas Parceiros',
			self::VendorOnly->value => 'Apenas Fornecedores',

			// Department/team
			self::DepartmentSpecific->value => 'Específico do Departamento',
			self::TeamSpecific->value => 'Específico da Equipe',
			self::ProjectSpecific->value => 'Específico do Projeto',
			self::LocationSpecific->value => 'Específico da Localização',

			// Time-based
			self::TimeLimited->value => 'Tempo Limitado',
			self::Temporary->value => 'Temporário',
			self::Expiring->value => 'Expirando',
			self::FutureRelease->value => 'Lançamento Futuro',

			// Content-specific
			self::AgeRestricted->value => 'Restrição de Idade',
			self::GeographicRestricted->value => 'Restrição Geográfica',
			self::LanguageSpecific->value => 'Idioma Específico',
			self::PlatformSpecific->value => 'Plataforma Específica',

			// Collaboration
			self::Shared->value => 'Compartilhado',
			self::CollaboratorsOnly->value => 'Apenas Colaboradores',
			self::InviteOnly->value => 'Apenas por Convite',
			self::GuestAccess->value => 'Acesso de Convidado',

			// System/technical
			self::SystemOnly->value => 'Apenas Sistema',
			self::MaintenanceMode->value => 'Modo Manutenção',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Pré-visualização',
			self::Staging->value => 'Staging',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Público',
			self::Private->value => 'Privado',
			self::Internal->value => 'Interno',
			self::Draft->value => 'Borrador',
			self::Scheduled->value => 'Programado',
			self::Archived->value => 'Archivado',
			self::Protected->value => 'Protegido',
			self::Unlisted->value => 'No Listado',

			// Advanced security
			self::Restricted->value => 'Restringido',
			self::Confidential->value => 'Confidencial',
			self::Secret->value => 'Secreto',
			self::TopSecret->value => 'Ultra Secreto',
			self::Classified->value => 'Clasificado',

			// Role-based
			self::AdminOnly->value => 'Solo Admin',
			self::ManagerOnly->value => 'Solo Gerentes',
			self::StaffOnly->value => 'Solo Personal',
			self::MemberOnly->value => 'Solo Miembros',
			self::CustomerOnly->value => 'Solo Clientes',
			self::PartnerOnly->value => 'Solo Socios',
			self::VendorOnly->value => 'Solo Proveedores',

			// Department/team
			self::DepartmentSpecific->value => 'Específico del Departamento',
			self::TeamSpecific->value => 'Específico del Equipo',
			self::ProjectSpecific->value => 'Específico del Proyecto',
			self::LocationSpecific->value => 'Específico de la Ubicación',

			// Time-based
			self::TimeLimited->value => 'Tiempo Limitado',
			self::Temporary->value => 'Temporal',
			self::Expiring->value => 'Caducando',
			self::FutureRelease->value => 'Lanzamiento Futuro',

			// Content-specific
			self::AgeRestricted->value => 'Restricción de Edad',
			self::GeographicRestricted->value => 'Restricción Geográfica',
			self::LanguageSpecific->value => 'Idioma Específico',
			self::PlatformSpecific->value => 'Plataforma Específica',

			// Collaboration
			self::Shared->value => 'Compartido',
			self::CollaboratorsOnly->value => 'Solo Colaboradores',
			self::InviteOnly->value => 'Solo por Invitación',
			self::GuestAccess->value => 'Acceso de Invitado',

			// System/technical
			self::SystemOnly->value => 'Solo Sistema',
			self::MaintenanceMode->value => 'Modo Mantenimiento',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Vista Previa',
			self::Staging->value => 'Staging',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Öffentlich',
			self::Private->value => 'Privat',
			self::Internal->value => 'Intern',
			self::Draft->value => 'Entwurf',
			self::Scheduled->value => 'Geplant',
			self::Archived->value => 'Archiviert',
			self::Protected->value => 'Geschützt',
			self::Unlisted->value => 'Nicht Gelistet',

			// Advanced security
			self::Restricted->value => 'Eingeschränkt',
			self::Confidential->value => 'Vertraulich',
			self::Secret->value => 'Geheim',
			self::TopSecret->value => 'Streng Geheim',
			self::Classified->value => 'Verschlüsselt',

			// Role-based
			self::AdminOnly->value => 'Nur Admin',
			self::ManagerOnly->value => 'Nur Manager',
			self::StaffOnly->value => 'Nur Mitarbeiter',
			self::MemberOnly->value => 'Nur Mitglieder',
			self::CustomerOnly->value => 'Nur Kunden',
			self::PartnerOnly->value => 'Nur Partner',
			self::VendorOnly->value => 'Nur Lieferanten',

			// Department/team
			self::DepartmentSpecific->value => 'Abteilungsspezifisch',
			self::TeamSpecific->value => 'Teamspezifisch',
			self::ProjectSpecific->value => 'Projektspezifisch',
			self::LocationSpecific->value => 'Standortspezifisch',

			// Time-based
			self::TimeLimited->value => 'Zeitlich Begrenzt',
			self::Temporary->value => 'Vorübergehend',
			self::Expiring->value => 'Ablaufend',
			self::FutureRelease->value => 'Zukünftige Veröffentlichung',

			// Content-specific
			self::AgeRestricted->value => 'Altersbeschränkt',
			self::GeographicRestricted->value => 'Geografisch Beschränkt',
			self::LanguageSpecific->value => 'Sprachspezifisch',
			self::PlatformSpecific->value => 'Plattformspezifisch',

			// Collaboration
			self::Shared->value => 'Geteilt',
			self::CollaboratorsOnly->value => 'Nur Mitarbeiter',
			self::InviteOnly->value => 'Nur auf Einladung',
			self::GuestAccess->value => 'Gastzugang',

			// System/technical
			self::SystemOnly->value => 'Nur System',
			self::MaintenanceMode->value => 'Wartungsmodus',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Vorschau',
			self::Staging->value => 'Staging',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Public',
			self::Private->value => 'Privé',
			self::Internal->value => 'Interne',
			self::Draft->value => 'Brouillon',
			self::Scheduled->value => 'Planifié',
			self::Archived->value => 'Archivé',
			self::Protected->value => 'Protégé',
			self::Unlisted->value => 'Non Répertorié',

			// Advanced security
			self::Restricted->value => 'Restreint',
			self::Confidential->value => 'Confidentiel',
			self::Secret->value => 'Secret',
			self::TopSecret->value => 'Top Secret',
			self::Classified->value => 'Classifié',

			// Role-based
			self::AdminOnly->value => 'Admin Seulement',
			self::ManagerOnly->value => 'Managers Seulement',
			self::StaffOnly->value => 'Personnel Seulement',
			self::MemberOnly->value => 'Membres Seulement',
			self::CustomerOnly->value => 'Clients Seulement',
			self::PartnerOnly->value => 'Partenaires Seulement',
			self::VendorOnly->value => 'Fournisseurs Seulement',

			// Department/team
			self::DepartmentSpecific->value => 'Spécifique au Département',
			self::TeamSpecific->value => 'Spécifique à l\'Équipe',
			self::ProjectSpecific->value => 'Spécifique au Projet',
			self::LocationSpecific->value => 'Spécifique à l\'Emplacement',

			// Time-based
			self::TimeLimited->value => 'Temps Limitée',
			self::Temporary->value => 'Temporaire',
			self::Expiring->value => 'Expirant',
			self::FutureRelease->value => 'Sortie Future',

			// Content-specific
			self::AgeRestricted->value => 'Restriction d\'Âge',
			self::GeographicRestricted->value => 'Restriction Géographique',
			self::LanguageSpecific->value => 'Langue Spécifique',
			self::PlatformSpecific->value => 'Plateforme Spécifique',

			// Collaboration
			self::Shared->value => 'Partagé',
			self::CollaboratorsOnly->value => 'Collaborateurs Seulement',
			self::InviteOnly->value => 'Sur Invitation Seulement',
			self::GuestAccess->value => 'Accès Invité',

			// System/technical
			self::SystemOnly->value => 'Système Seulement',
			self::MaintenanceMode->value => 'Mode Maintenance',
			self::Beta->value => 'Bêta',
			self::Preview->value => 'Aperçu',
			self::Staging->value => 'Staging',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Pubblico',
			self::Private->value => 'Privato',
			self::Internal->value => 'Interno',
			self::Draft->value => 'Bozza',
			self::Scheduled->value => 'Programmato',
			self::Archived->value => 'Archiviato',
			self::Protected->value => 'Protetto',
			self::Unlisted->value => 'Non Elencato',

			// Advanced security
			self::Restricted->value => 'Ristretto',
			self::Confidential->value => 'Riservato',
			self::Secret->value => 'Segreto',
			self::TopSecret->value => 'Top Secret',
			self::Classified->value => 'Classificato',

			// Role-based
			self::AdminOnly->value => 'Solo Admin',
			self::ManagerOnly->value => 'Solo Manager',
			self::StaffOnly->value => 'Solo Personale',
			self::MemberOnly->value => 'Solo Membri',
			self::CustomerOnly->value => 'Solo Clienti',
			self::PartnerOnly->value => 'Solo Partner',
			self::VendorOnly->value => 'Solo Fornitori',

			// Department/team
			self::DepartmentSpecific->value => 'Specifico del Dipartimento',
			self::TeamSpecific->value => 'Specifico del Team',
			self::ProjectSpecific->value => 'Specifico del Progetto',
			self::LocationSpecific->value => 'Specifico della Posizione',

			// Time-based
			self::TimeLimited->value => 'Tempo Limitato',
			self::Temporary->value => 'Temporaneo',
			self::Expiring->value => 'In Scadenza',
			self::FutureRelease->value => 'Rilascio Futuro',

			// Content-specific
			self::AgeRestricted->value => 'Limitazione d\'Età',
			self::GeographicRestricted->value => 'Limitazione Geografica',
			self::LanguageSpecific->value => 'Lingua Specifica',
			self::PlatformSpecific->value => 'Piattaforma Specifica',

			// Collaboration
			self::Shared->value => 'Condiviso',
			self::CollaboratorsOnly->value => 'Solo Collaboratori',
			self::InviteOnly->value => 'Solo su Invito',
			self::GuestAccess->value => 'Accesso Ospite',

			// System/technical
			self::SystemOnly->value => 'Solo Sistema',
			self::MaintenanceMode->value => 'Modalità Manutenzione',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Anteprima',
			self::Staging->value => 'Staging',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Openbaar',
			self::Private->value => 'Privé',
			self::Internal->value => 'Intern',
			self::Draft->value => 'Concept',
			self::Scheduled->value => 'Gepland',
			self::Archived->value => 'Gearchiveerd',
			self::Protected->value => 'Beveiligd',
			self::Unlisted->value => 'Niet Gelijst',

			// Advanced security
			self::Restricted->value => 'Beperkt',
			self::Confidential->value => 'Vertrouwelijk',
			self::Secret->value => 'Geheim',
			self::TopSecret->value => 'Topgeheim',
			self::Classified->value => 'Geclassificeerd',

			// Role-based
			self::AdminOnly->value => 'Alleen Admin',
			self::ManagerOnly->value => 'Alleen Managers',
			self::StaffOnly->value => 'Alleen Personeel',
			self::MemberOnly->value => 'Alleen Leden',
			self::CustomerOnly->value => 'Alleen Klanten',
			self::PartnerOnly->value => 'Alleen Partners',
			self::VendorOnly->value => 'Alleen Leveranciers',

			// Department/team
			self::DepartmentSpecific->value => 'Afdeling Specifiek',
			self::TeamSpecific->value => 'Team Specifiek',
			self::ProjectSpecific->value => 'Project Specifiek',
			self::LocationSpecific->value => 'Locatie Specifiek',

			// Time-based
			self::TimeLimited->value => 'Tijdelijk Beperkt',
			self::Temporary->value => 'Tijdelijk',
			self::Expiring->value => 'Verlopend',
			self::FutureRelease->value => 'Toekomstige Release',

			// Content-specific
			self::AgeRestricted->value => 'Leeftijdsbeperking',
			self::GeographicRestricted->value => 'Geografische Beperking',
			self::LanguageSpecific->value => 'Taal Specifiek',
			self::PlatformSpecific->value => 'Platform Specifiek',

			// Collaboration
			self::Shared->value => 'Gedeeld',
			self::CollaboratorsOnly->value => 'Alleen Medewerkers',
			self::InviteOnly->value => 'Alleen op Uitnodiging',
			self::GuestAccess->value => 'Gasttoegang',

			// System/technical
			self::SystemOnly->value => 'Alleen Systeem',
			self::MaintenanceMode->value => 'Onderhoudsmodus',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Voorbeeld',
			self::Staging->value => 'Staging',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Publiczny',
			self::Private->value => 'Prywatny',
			self::Internal->value => 'Wewnętrzny',
			self::Draft->value => 'Szkic',
			self::Scheduled->value => 'Zaplanowany',
			self::Archived->value => 'Zarchiwizowany',
			self::Protected->value => 'Chroniony',
			self::Unlisted->value => 'Niewymieniony',

			// Advanced security
			self::Restricted->value => 'Ograniczony',
			self::Confidential->value => 'Poufny',
			self::Secret->value => 'Tajny',
			self::TopSecret->value => 'Ściśle Tajny',
			self::Classified->value => 'Klasyfikowany',

			// Role-based
			self::AdminOnly->value => 'Tylko Admin',
			self::ManagerOnly->value => 'Tylko Menedżerowie',
			self::StaffOnly->value => 'Tylko Pracownicy',
			self::MemberOnly->value => 'Tylko Członkowie',
			self::CustomerOnly->value => 'Tylko Klienci',
			self::PartnerOnly->value => 'Tylko Partnerzy',
			self::VendorOnly->value => 'Tylko Dostawcy',

			// Department/team
			self::DepartmentSpecific->value => 'Specyficzny dla Działu',
			self::TeamSpecific->value => 'Specyficzny dla Zespołu',
			self::ProjectSpecific->value => 'Specyficzny dla Projektu',
			self::LocationSpecific->value => 'Specyficzny dla Lokalizacji',

			// Time-based
			self::TimeLimited->value => 'Czasowo Ograniczony',
			self::Temporary->value => 'Tymczasowy',
			self::Expiring->value => 'Wygasający',
			self::FutureRelease->value => 'Przyszła Wersja',

			// Content-specific
			self::AgeRestricted->value => 'Ograniczenie Wiekowe',
			self::GeographicRestricted->value => 'Ograniczenie Geograficzne',
			self::LanguageSpecific->value => 'Specyficzny Język',
			self::PlatformSpecific->value => 'Specyficzna Platforma',

			// Collaboration
			self::Shared->value => 'Współdzielony',
			self::CollaboratorsOnly->value => 'Tylko Współpracownicy',
			self::InviteOnly->value => 'Tylko na Zaproszenie',
			self::GuestAccess->value => 'Dostęp Gościa',

			// System/technical
			self::SystemOnly->value => 'Tylko System',
			self::MaintenanceMode->value => 'Tryb Konserwacji',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Podgląd',
			self::Staging->value => 'Staging',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Публичный',
			self::Private->value => 'Приватный',
			self::Internal->value => 'Внутренний',
			self::Draft->value => 'Черновик',
			self::Scheduled->value => 'Запланированный',
			self::Archived->value => 'Архивированный',
			self::Protected->value => 'Защищенный',
			self::Unlisted->value => 'Не в списке',

			// Advanced security
			self::Restricted->value => 'Ограниченный',
			self::Confidential->value => 'Конфиденциальный',
			self::Secret->value => 'Секретный',
			self::TopSecret->value => 'Совершенно Секретно',
			self::Classified->value => 'Классифицированный',

			// Role-based
			self::AdminOnly->value => 'Только Админы',
			self::ManagerOnly->value => 'Только Менеджеры',
			self::StaffOnly->value => 'Только Сотрудники',
			self::MemberOnly->value => 'Только Участники',
			self::CustomerOnly->value => 'Только Клиенты',
			self::PartnerOnly->value => 'Только Партнеры',
			self::VendorOnly->value => 'Только Поставщики',

			// Department/team
			self::DepartmentSpecific->value => 'Для Отдела',
			self::TeamSpecific->value => 'Для Команды',
			self::ProjectSpecific->value => 'Для Проекта',
			self::LocationSpecific->value => 'Для Локации',

			// Time-based
			self::TimeLimited->value => 'Ограниченное Время',
			self::Temporary->value => 'Временный',
			self::Expiring->value => 'Истекающий',
			self::FutureRelease->value => 'Будущий Выпуск',

			// Content-specific
			self::AgeRestricted->value => 'Возрастное Ограничение',
			self::GeographicRestricted->value => 'Географическое Ограничение',
			self::LanguageSpecific->value => 'Для Языка',
			self::PlatformSpecific->value => 'Для Платформы',

			// Collaboration
			self::Shared->value => 'Общий',
			self::CollaboratorsOnly->value => 'Только Сотрудники',
			self::InviteOnly->value => 'Только по Приглашению',
			self::GuestAccess->value => 'Гостевой Доступ',

			// System/technical
			self::SystemOnly->value => 'Только Система',
			self::MaintenanceMode->value => 'Режим Обслуживания',
			self::Beta->value => 'Бета',
			self::Preview->value => 'Предпросмотр',
			self::Staging->value => 'Стейдинг',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Herkese Açık',
			self::Private->value => 'Özel',
			self::Internal->value => 'Dahili',
			self::Draft->value => 'Taslak',
			self::Scheduled->value => 'Planlanmış',
			self::Archived->value => 'Arşivlenmiş',
			self::Protected->value => 'Korumalı',
			self::Unlisted->value => 'Listelenmemiş',

			// Advanced security
			self::Restricted->value => 'Kısıtlı',
			self::Confidential->value => 'Gizli',
			self::Secret->value => 'Sır',
			self::TopSecret->value => 'Çok Gizli',
			self::Classified->value => 'Sınıflandırılmış',

			// Role-based
			self::AdminOnly->value => 'Sadece Yönetici',
			self::ManagerOnly->value => 'Sadece Yöneticiler',
			self::StaffOnly->value => 'Sadece Personel',
			self::MemberOnly->value => 'Sadece Üyeler',
			self::CustomerOnly->value => 'Sadece Müşteriler',
			self::PartnerOnly->value => 'Sadece Ortaklar',
			self::VendorOnly->value => 'Sadece Tedarikçiler',

			// Department/team
			self::DepartmentSpecific->value => 'Departmana Özel',
			self::TeamSpecific->value => 'Takıma Özel',
			self::ProjectSpecific->value => 'Projeye Özel',
			self::LocationSpecific->value => 'Konuma Özel',

			// Time-based
			self::TimeLimited->value => 'Zaman Sınırlı',
			self::Temporary->value => 'Geçici',
			self::Expiring->value => 'Sona Eren',
			self::FutureRelease->value => 'Gelecek Sürüm',

			// Content-specific
			self::AgeRestricted->value => 'Yaş Sınırlamalı',
			self::GeographicRestricted->value => 'Coğrafi Sınırlamalı',
			self::LanguageSpecific->value => 'Dile Özel',
			self::PlatformSpecific->value => 'Platforma Özel',

			// Collaboration
			self::Shared->value => 'Paylaşılan',
			self::CollaboratorsOnly->value => 'Sadece İşbirlikçiler',
			self::InviteOnly->value => 'Sadece Davet ile',
			self::GuestAccess->value => 'Misafir Erişimi',

			// System/technical
			self::SystemOnly->value => 'Sadece Sistem',
			self::MaintenanceMode->value => 'Bakım Modu',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Ön İzleme',
			self::Staging->value => 'Staging',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'عام',
			self::Private->value => 'خاص',
			self::Internal->value => 'داخلي',
			self::Draft->value => 'مسودة',
			self::Scheduled->value => 'مجدول',
			self::Archived->value => 'مؤرشف',
			self::Protected->value => 'محمي',
			self::Unlisted->value => 'غير مدرج',

			// Advanced security
			self::Restricted->value => 'مقيد',
			self::Confidential->value => 'سري',
			self::Secret->value => 'سري للغاية',
			self::TopSecret->value => 'سري جداً',
			self::Classified->value => 'مصنف',

			// Role-based
			self::AdminOnly->value => 'للمسؤولين فقط',
			self::ManagerOnly->value => 'للمديرين فقط',
			self::StaffOnly->value => 'للموظفين فقط',
			self::MemberOnly->value => 'للأعضاء فقط',
			self::CustomerOnly->value => 'للعملاء فقط',
			self::PartnerOnly->value => 'للشركاء فقط',
			self::VendorOnly->value => 'للموردين فقط',

			// Department/team
			self::DepartmentSpecific->value => 'محدد القسم',
			self::TeamSpecific->value => 'محدد الفريق',
			self::ProjectSpecific->value => 'محدد المشروع',
			self::LocationSpecific->value => 'محدد الموقع',

			// Time-based
			self::TimeLimited->value => 'محدد الوقت',
			self::Temporary->value => 'مؤقت',
			self::Expiring->value => 'منتهي الصلاحية',
			self::FutureRelease->value => 'إصدار مستقبلي',

			// Content-specific
			self::AgeRestricted->value => 'مقيد العمر',
			self::GeographicRestricted->value => 'مقيد جغرافياً',
			self::LanguageSpecific->value => 'محدد اللغة',
			self::PlatformSpecific->value => 'محدد المنصة',

			// Collaboration
			self::Shared->value => 'مشترك',
			self::CollaboratorsOnly->value => 'للمتعاونين فقط',
			self::InviteOnly->value => 'بالدعوة فقط',
			self::GuestAccess->value => 'وصول الضيوف',

			// System/technical
			self::SystemOnly->value => 'للنظام فقط',
			self::MaintenanceMode->value => 'وضع الصيانة',
			self::Beta->value => 'بيتا',
			self::Preview->value => 'معاينة',
			self::Staging->value => 'ستيجينج',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'ציבורי',
			self::Private->value => 'פרטי',
			self::Internal->value => 'פנימי',
			self::Draft->value => 'טיוטה',
			self::Scheduled->value => 'מתוזמן',
			self::Archived->value => 'בארכיון',
			self::Protected->value => 'מוגן',
			self::Unlisted->value => 'לא רשום',

			// Advanced security
			self::Restricted->value => 'מוגבל',
			self::Confidential->value => 'סודי',
			self::Secret->value => 'סוד',
			self::TopSecret->value => 'סודי ביותר',
			self::Classified->value => 'מסווג',

			// Role-based
			self::AdminOnly->value => 'למנהלים בלבד',
			self::ManagerOnly->value => 'למנהלים בלבד',
			self::StaffOnly->value => 'לעובדים בלבד',
			self::MemberOnly->value => 'לחברים בלבד',
			self::CustomerOnly->value => 'ללקוחות בלבד',
			self::PartnerOnly->value => 'לשותפים בלבד',
			self::VendorOnly->value => 'לספקים בלבד',

			// Department/team
			self::DepartmentSpecific->value => 'ספציפי למחלקה',
			self::TeamSpecific->value => 'ספציפי לצוות',
			self::ProjectSpecific->value => 'ספציפי לפרויקט',
			self::LocationSpecific->value => 'ספציפי למיקום',

			// Time-based
			self::TimeLimited->value => 'מוגבל זמן',
			self::Temporary->value => 'זמני',
			self::Expiring->value => 'פג תוקף',
			self::FutureRelease->value => 'שחרור עתידי',

			// Content-specific
			self::AgeRestricted->value => 'מוגבל גיל',
			self::GeographicRestricted->value => 'מוגבל גיאוגרפית',
			self::LanguageSpecific->value => 'ספציפי לשפה',
			self::PlatformSpecific->value => 'ספציפי לפלטפורמה',

			// Collaboration
			self::Shared->value => 'משותף',
			self::CollaboratorsOnly->value => 'למשתפי פעולה בלבד',
			self::InviteOnly->value => 'בהזמנה בלבד',
			self::GuestAccess->value => 'גישת אורח',

			// System/technical
			self::SystemOnly->value => 'למערכת בלבד',
			self::MaintenanceMode->value => 'מצב תחזוקה',
			self::Beta->value => 'בטא',
			self::Preview->value => 'תצוגה מקדימה',
			self::Staging->value => 'סטייג\'ינג',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Basic visibility
			self::Public->value => '公開',
			self::Private->value => '非公開',
			self::Internal->value => '内部',
			self::Draft->value => '下書き',
			self::Scheduled->value => '予定済み',
			self::Archived->value => 'アーカイブ',
			self::Protected->value => '保護済み',
			self::Unlisted->value => '未掲載',

			// Advanced security
			self::Restricted->value => '制限付き',
			self::Confidential->value => '機密',
			self::Secret->value => '秘密',
			self::TopSecret->value => '極秘',
			self::Classified->value => '機密指定',

			// Role-based
			self::AdminOnly->value => '管理者のみ',
			self::ManagerOnly->value => 'マネージャーのみ',
			self::StaffOnly->value => 'スタッフのみ',
			self::MemberOnly->value => 'メンバーのみ',
			self::CustomerOnly->value => '顧客のみ',
			self::PartnerOnly->value => 'パートナーのみ',
			self::VendorOnly->value => 'ベンダーのみ',

			// Department/team
			self::DepartmentSpecific->value => '部門限定',
			self::TeamSpecific->value => 'チーム限定',
			self::ProjectSpecific->value => 'プロジェクト限定',
			self::LocationSpecific->value => '場所限定',

			// Time-based
			self::TimeLimited->value => '期間限定',
			self::Temporary->value => '一時的',
			self::Expiring->value => '期限切れ間近',
			self::FutureRelease->value => '将来リリース',

			// Content-specific
			self::AgeRestricted->value => '年齢制限',
			self::GeographicRestricted->value => '地域制限',
			self::LanguageSpecific->value => '言語限定',
			self::PlatformSpecific->value => 'プラットフォーム限定',

			// Collaboration
			self::Shared->value => '共有',
			self::CollaboratorsOnly->value => '共同作業者のみ',
			self::InviteOnly->value => '招待制',
			self::GuestAccess->value => 'ゲストアクセス',

			// System/technical
			self::SystemOnly->value => 'システムのみ',
			self::MaintenanceMode->value => 'メンテナンスモード',
			self::Beta->value => 'ベータ版',
			self::Preview->value => 'プレビュー',
			self::Staging->value => 'ステージング',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Basic visibility
			self::Public->value => 'Offentlig',
			self::Private->value => 'Privat',
			self::Internal->value => 'Intern',
			self::Draft->value => 'Kladde',
			self::Scheduled->value => 'Planlagt',
			self::Archived->value => 'Arkiveret',
			self::Protected->value => 'Beskyttet',
			self::Unlisted->value => 'Ikke Listet',

			// Advanced security
			self::Restricted->value => 'Begrænset',
			self::Confidential->value => 'Fortrolig',
			self::Secret->value => 'Hemmelig',
			self::TopSecret->value => 'Tophemmelig',
			self::Classified->value => 'Klassificeret',

			// Role-based
			self::AdminOnly->value => 'Kun Admin',
			self::ManagerOnly->value => 'Kun Ledere',
			self::StaffOnly->value => 'Kun Personale',
			self::MemberOnly->value => 'Kun Medlemmer',
			self::CustomerOnly->value => 'Kun Kunder',
			self::PartnerOnly->value => 'Kun Partnere',
			self::VendorOnly->value => 'Kun Leverandører',

			// Department/team
			self::DepartmentSpecific->value => 'Afdelingsspecifik',
			self::TeamSpecific->value => 'Teamspecifik',
			self::ProjectSpecific->value => 'Projektspecifik',
			self::LocationSpecific->value => 'Lokalitetspecifik',

			// Time-based
			self::TimeLimited->value => 'Tidsbegrænset',
			self::Temporary->value => 'Midlertidig',
			self::Expiring->value => 'Udløber',
			self::FutureRelease->value => 'Fremtidig Udgivelse',

			// Content-specific
			self::AgeRestricted->value => 'Alderbegrænsning',
			self::GeographicRestricted->value => 'Geografisk Begrænsning',
			self::LanguageSpecific->value => 'Sprogspecifik',
			self::PlatformSpecific->value => 'Platformspecifik',

			// Collaboration
			self::Shared->value => 'Delt',
			self::CollaboratorsOnly->value => 'Kun Samarbejdspartnere',
			self::InviteOnly->value => 'Kun på Invitation',
			self::GuestAccess->value => 'Gæsteadgang',

			// System/technical
			self::SystemOnly->value => 'Kun System',
			self::MaintenanceMode->value => 'Vedligeholdelsestilstand',
			self::Beta->value => 'Beta',
			self::Preview->value => 'Forhåndsvisning',
			self::Staging->value => 'Staging',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			// Basic visibility
			self::Public->value => '公开',
			self::Private->value => '私密',
			self::Internal->value => '内部',
			self::Draft->value => '草稿',
			self::Scheduled->value => '已计划',
			self::Archived->value => '已归档',
			self::Protected->value => '受保护',
			self::Unlisted->value => '未列出',

			// Advanced security
			self::Restricted->value => '受限',
			self::Confidential->value => '机密',
			self::Secret->value => '秘密',
			self::TopSecret->value => '绝密',
			self::Classified->value => '已分类',

			// Role-based
			self::AdminOnly->value => '仅管理员',
			self::ManagerOnly->value => '仅经理',
			self::StaffOnly->value => '仅员工',
			self::MemberOnly->value => '仅成员',
			self::CustomerOnly->value => '仅客户',
			self::PartnerOnly->value => '仅合作伙伴',
			self::VendorOnly->value => '仅供应商',

			// Department/team
			self::DepartmentSpecific->value => '部门特定',
			self::TeamSpecific->value => '团队特定',
			self::ProjectSpecific->value => '项目特定',
			self::LocationSpecific->value => '位置特定',

			// Time-based
			self::TimeLimited->value => '时间限制',
			self::Temporary->value => '临时',
			self::Expiring->value => '即将过期',
			self::FutureRelease->value => '未来发布',

			// Content-specific
			self::AgeRestricted->value => '年龄限制',
			self::GeographicRestricted->value => '地理限制',
			self::LanguageSpecific->value => '语言特定',
			self::PlatformSpecific->value => '平台特定',

			// Collaboration
			self::Shared->value => '已共享',
			self::CollaboratorsOnly->value => '仅协作者',
			self::InviteOnly->value => '仅邀请',
			self::GuestAccess->value => '访客访问',

			// System/technical
			self::SystemOnly->value => '仅系统',
			self::MaintenanceMode->value => '维护模式',
			self::Beta->value => '测试版',
			self::Preview->value => '预览',
			self::Staging->value => '暂存',
		];
	}
}
