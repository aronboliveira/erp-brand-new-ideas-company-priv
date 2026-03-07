<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum HolidayType: string
{
	case National = 'national';
	case State = 'state';
	case Municipal = 'municipal';
	case Regional = 'regional';
	case Religious = 'religious';
	case Optional = 'optional';
	case Company = 'company';
	case Federal = 'federal';
	case Provincial = 'provincial';
	case Cantonal = 'cantonal';
	case Local = 'local';
	case Cultural = 'cultural';
	case Historical = 'historical';
	case Floating = 'floating';
	case Observance = 'observance';
	case School = 'school';
	case Bank = 'bank';
	case Public = 'public';
	case Private = 'private';
	case Mandatory = 'mandatory';
	case Special = 'special';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Public;

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim($value)));
		return match ($normalizedValue) {
			// Main categories
			'national', 'country', 'countrywide', 'nationwide' => self::National,
			'state', 'province', 'provincial', 'territory', 'regional' => self::State,
			'municipal', 'city', 'town', 'local', 'communal' => self::Municipal,
			'religious', 'faith', 'church', 'temple', 'mosque', 'spiritual' => self::Religious,
			'optional', 'voluntary', 'elective', 'discretionary' => self::Optional,
			'company', 'corporate', 'business', 'workplace', 'employer' => self::Company,
			'federal', 'federation', 'government' => self::Federal,
			'regional', 'region' => self::Regional,
			'provincial', 'province' => self::Provincial,
			'cantonal', 'canton', 'county' => self::Cantonal,
			'local', 'locality', 'community' => self::Local,
			'cultural', 'culture', 'ethnic', 'heritage' => self::Cultural,
			'historical', 'history', 'commemoration', 'anniversary' => self::Historical,
			'floating', 'movable', 'variable', 'lunar' => self::Floating,
			'observance', 'awareness', 'commemorative', 'memorial' => self::Observance,
			'school', 'academic', 'educational', 'university', 'college' => self::School,
			'bank', 'financial', 'banking' => self::Bank,
			'public', 'statutory', 'official', 'legal' => self::Public,
			'private', 'personal', 'individual' => self::Private,
			'mandatory', 'required', 'compulsory', 'obligatory' => self::Mandatory,
			'special', 'extra', 'additional', 'exceptional' => self::Special,

			default => self::Public,
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

	/**
	 * Get the label for a specific holiday type in the specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? '';
	}

	/**
	 * Get the icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::National, self::Federal => 'flag',
			self::State, self::Provincial, self::Regional => 'map',
			self::Municipal, self::Cantonal, self::Local => 'building-office',
			self::Religious => 'church',
			self::Cultural, self::Historical => 'book-open',
			self::Optional, self::Floating => 'calendar-days',
			self::Company, self::Bank, self::Private => 'building-library',
			self::School => 'academic-cap',
			self::Public, self::Mandatory => 'shield-check',
			self::Observance, self::Special => 'star',
			default => 'calendar',
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::National, self::Federal => '#ef4444', // red
			self::State, self::Provincial, self::Regional => '#3b82f6', // blue
			self::Municipal, self::Cantonal, self::Local => '#10b981', // green
			self::Religious => '#8b5cf6', // purple
			self::Cultural, self::Historical => '#f59e0b', // amber
			self::Optional, self::Floating => '#6b7280', // gray
			self::Company, self::Bank, self::Private => '#6366f1', // indigo
			self::School => '#ec4899', // pink
			self::Public, self::Mandatory => '#84cc16', // lime
			self::Observance, self::Special => '#f97316', // orange
			default => '#9ca3af', // cool gray
		};
	}

	/**
	 * Get the hierarchy level of this holiday type
	 */
	public function getHierarchyLevel(): int
	{
		return match ($this) {
			self::National, self::Federal => 1, // Highest level
			self::State, self::Provincial, self::Regional => 2,
			self::Municipal, self::Cantonal, self::Local => 3,
			self::Company, self::School, self::Bank => 4,
			self::Religious, self::Cultural, self::Historical => 5,
			self::Public, self::Private, self::Mandatory => 6,
			self::Optional, self::Floating, self::Observance, self::Special => 7,
			default => 8,
		};
	}

	/**
	 * Check if this holiday type is government-mandated
	 */
	public function isGovernmentMandated(): bool
	{
		return in_array($this, [
			self::National,
			self::Federal,
			self::State,
			self::Provincial,
			self::Municipal,
			self::Public,
			self::Mandatory,
		]);
	}

	/**
	 * Check if this holiday type is optional/voluntary
	 */
	public function isOptional(): bool
	{
		return in_array($this, [self::Optional, self::Floating, self::Private]);
	}

	/**
	 * Check if this holiday type is organization-specific
	 */
	public function isOrganizationSpecific(): bool
	{
		return in_array($this, [self::Company, self::School, self::Bank]);
	}

	/**
	 * Get the typical duration in days for this holiday type
	 */
	public function getTypicalDuration(): int
	{
		return match ($this) {
			self::National, self::Federal => 1,
			self::Religious => 1,
			self::Company, self::School => 1,
			self::Cultural, self::Historical => 1,
			self::Observance, self::Special => 1,
			default => 1,
		};
	}

	/**
	 * Get the description of what this holiday type means
	 */
	public function getDescription($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$descriptions = self::descriptions($lang);
		return $descriptions[$this->value] ?? '';
	}

	public static function descriptions($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::descriptionsPtBr(),
			'es', 'es-es' => self::descriptionsEs(),
			default => self::descriptionsEn(),
		};
	}

	/**
	 * Get the legal status of this holiday type
	 */
	public function getLegalStatus(): string
	{
		return match ($this) {
			self::National, self::Federal, self::Public, self::Mandatory => 'legally_required',
			self::State, self::Provincial, self::Municipal => 'jurisdiction_specific',
			self::Company, self::School, self::Bank => 'organizational',
			self::Religious, self::Cultural, self::Historical => 'cultural_traditional',
			self::Optional, self::Floating, self::Private, self::Special => 'discretionary',
			default => 'other',
		};
	}

	/**
	 * Check if this holiday type typically grants paid time off
	 */
	public function grantsPaidTimeOff(): bool
	{
		return match ($this) {
			self::National, self::Federal, self::State, self::Public,
			self::Mandatory, self::Bank => true,
			self::Company, self::School => true,
			self::Religious, self::Cultural, self::Historical => true,
			default => false,
		};
	}

	/**
	 * Get the category group for this holiday type
	 */
	public function getCategory(): string
	{
		return match ($this) {
			self::National, self::Federal, self::State, self::Provincial,
			self::Municipal, self::Cantonal, self::Local, self::Regional => 'government',
			self::Religious, self::Cultural, self::Historical, self::Observance => 'cultural',
			self::Company, self::School, self::Bank, self::Private => 'organizational',
			self::Optional, self::Floating, self::Special => 'special',
			self::Public, self::Mandatory => 'mandatory',
			default => 'general',
		};
	}

	/**
	 * Get the priority for holiday overlap resolution
	 */
	public function getPriority(): int
	{
		return match ($this) {
			self::National, self::Federal => 1, // Highest priority
			self::State, self::Provincial => 2,
			self::Municipal, self::Cantonal, self::Local => 3,
			self::Public, self::Mandatory => 4,
			self::Bank => 5,
			self::Religious => 6,
			self::School => 7,
			self::Company => 8,
			self::Cultural, self::Historical, self::Observance => 9,
			self::Optional, self::Floating, self::Private, self::Special => 10,
			default => 11,
		};
	}

	/**
	 * Check if this holiday type should be displayed in public calendars
	 */
	public function isPubliclyVisible(): bool
	{
		return match ($this) {
			self::National, self::Federal, self::State, self::Provincial,
			self::Municipal, self::Public, self::Religious, self::Cultural,
			self::Historical, self::Observance, self::Bank => true,
			self::Company, self::School, self::Private, self::Optional => false,
			default => false,
		};
	}

	/**
	 * Get typical observance countries/regions for this holiday type
	 */
	public function getTypicalObservance(): array
	{
		return match ($this) {
			self::National => ['country_wide', 'all_regions'],
			self::Federal => ['federal_jurisdiction', 'federal_territories'],
			self::State => ['specific_state', 'state_jurisdiction'],
			self::Provincial => ['province_wide', 'provincial_jurisdiction'],
			self::Municipal => ['city_wide', 'municipal_jurisdiction'],
			self::Cantonal => ['canton_wide', 'county_jurisdiction'],
			self::Local => ['local_community', 'neighborhood'],
			self::Regional => ['regional_area', 'multi_jurisdiction'],
			default => ['varies', 'context_dependent'],
		};
	}

	/**
	 * Get the notification period before this holiday (in days)
	 */
	public function getNotificationPeriod(): int
	{
		return match ($this) {
			self::National, self::Federal, self::Public => 30,
			self::State, self::Provincial, self::Regional => 21,
			self::Municipal, self::Cantonal, self::Local => 14,
			self::Religious, self::Cultural, self::Historical => 7,
			self::Company, self::School, self::Bank => 14,
			self::Optional, self::Floating, self::Private, self::Special => 7,
			default => 14,
		};
	}

	public static function labelsEn(): array
	{
		return [
			self::National->value => 'National Holiday',
			self::State->value => 'State/Province Holiday',
			self::Municipal->value => 'Municipal/City Holiday',
			self::Regional->value => 'Regional Holiday',
			self::Religious->value => 'Religious Holiday',
			self::Optional->value => 'Optional Holiday',
			self::Company->value => 'Company Holiday',
			self::Federal->value => 'Federal Holiday',
			self::Provincial->value => 'Provincial Holiday',
			self::Cantonal->value => 'Cantonal/County Holiday',
			self::Local->value => 'Local Holiday',
			self::Cultural->value => 'Cultural Holiday',
			self::Historical->value => 'Historical Holiday',
			self::Floating->value => 'Floating Holiday',
			self::Observance->value => 'Observance Day',
			self::School->value => 'School Holiday',
			self::Bank->value => 'Bank Holiday',
			self::Public->value => 'Public Holiday',
			self::Private->value => 'Private Holiday',
			self::Mandatory->value => 'Mandatory Holiday',
			self::Special->value => 'Special Holiday',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::National->value => 'Feriado Nacional',
			self::State->value => 'Feriado Estadual',
			self::Municipal->value => 'Feriado Municipal',
			self::Regional->value => 'Feriado Regional',
			self::Religious->value => 'Feriado Religioso',
			self::Optional->value => 'Feriado Opcional',
			self::Company->value => 'Feriado da Empresa',
			self::Federal->value => 'Feriado Federal',
			self::Provincial->value => 'Feriado Provincial',
			self::Cantonal->value => 'Feriado Cantonal',
			self::Local->value => 'Feriado Local',
			self::Cultural->value => 'Feriado Cultural',
			self::Historical->value => 'Feriado Histórico',
			self::Floating->value => 'Feriado Móvel',
			self::Observance->value => 'Dia de Observância',
			self::School->value => 'Feriado Escolar',
			self::Bank->value => 'Feriado Bancário',
			self::Public->value => 'Feriado Público',
			self::Private->value => 'Feriado Privado',
			self::Mandatory->value => 'Feriado Obrigatório',
			self::Special->value => 'Feriado Especial',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::National->value => 'Fiesta Nacional',
			self::State->value => 'Fiesta Estatal/Provincial',
			self::Municipal->value => 'Fiesta Municipal',
			self::Regional->value => 'Fiesta Regional',
			self::Religious->value => 'Fiesta Religiosa',
			self::Optional->value => 'Fiesta Opcional',
			self::Company->value => 'Fiesta de la Empresa',
			self::Federal->value => 'Fiesta Federal',
			self::Provincial->value => 'Fiesta Provincial',
			self::Cantonal->value => 'Fiesta Cantonal',
			self::Local->value => 'Fiesta Local',
			self::Cultural->value => 'Fiesta Cultural',
			self::Historical->value => 'Fiesta Histórica',
			self::Floating->value => 'Fiesta Variable',
			self::Observance->value => 'Día de Observancia',
			self::School->value => 'Fiesta Escolar',
			self::Bank->value => 'Fiesta Bancaria',
			self::Public->value => 'Fiesta Pública',
			self::Private->value => 'Fiesta Privada',
			self::Mandatory->value => 'Fiesta Obligatoria',
			self::Special->value => 'Fiesta Especial',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::National->value => 'عطلة وطنية',
			self::State->value => 'عطلة الولاية/المقاطعة',
			self::Municipal->value => 'عطلة بلدية',
			self::Regional->value => 'عطلة إقليمية',
			self::Religious->value => 'عطلة دينية',
			self::Optional->value => 'عطلة اختيارية',
			self::Company->value => 'عطلة الشركة',
			self::Federal->value => 'عطلة اتحادية',
			self::Provincial->value => 'عطلة إقليمية',
			self::Cantonal->value => 'عطلة كانتونية',
			self::Local->value => 'عطلة محلية',
			self::Cultural->value => 'عطلة ثقافية',
			self::Historical->value => 'عطلة تاريخية',
			self::Floating->value => 'عطلة متحركة',
			self::Observance->value => 'يوم مراقبة',
			self::School->value => 'عطلة مدرسية',
			self::Bank->value => 'عطلة بنكية',
			self::Public->value => 'عطلة عامة',
			self::Private->value => 'عطلة خاصة',
			self::Mandatory->value => 'عطلة إجبارية',
			self::Special->value => 'عطلة خاصة',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::National->value => 'National Helligdag',
			self::State->value => 'Stats-/Provins Helligdag',
			self::Municipal->value => 'Kommunal Helligdag',
			self::Regional->value => 'Regional Helligdag',
			self::Religious->value => 'Religiøs Helligdag',
			self::Optional->value => 'Valgfri Helligdag',
			self::Company->value => 'Virksomheds Helligdag',
			self::Federal->value => 'Føderal Helligdag',
			self::Provincial->value => 'Provinsiel Helligdag',
			self::Cantonal->value => 'Kantonal Helligdag',
			self::Local->value => 'Lokal Helligdag',
			self::Cultural->value => 'Kulturel Helligdag',
			self::Historical->value => 'Historisk Helligdag',
			self::Floating->value => 'Flydende Helligdag',
			self::Observance->value => 'Observansdag',
			self::School->value => 'Skoleferie',
			self::Bank->value => 'Bank Helligdag',
			self::Public->value => 'Offentlig Helligdag',
			self::Private->value => 'Privat Helligdag',
			self::Mandatory->value => 'Obligatorisk Helligdag',
			self::Special->value => 'Speciel Helligdag',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::National->value => 'Nationalfeiertag',
			self::State->value => 'Landes-/Provinzfeiertag',
			self::Municipal->value => 'Gemeindefeiertag',
			self::Regional->value => 'Regionalfeiertag',
			self::Religious->value => 'Religiöser Feiertag',
			self::Optional->value => 'Optionaler Feiertag',
			self::Company->value => 'Firmenfeiertag',
			self::Federal->value => 'Bundesfeiertag',
			self::Provincial->value => 'Provinzfeiertag',
			self::Cantonal->value => 'Kantonaler Feiertag',
			self::Local->value => 'Lokaler Feiertag',
			self::Cultural->value => 'Kultureller Feiertag',
			self::Historical->value => 'Historischer Feiertag',
			self::Floating->value => 'Beweglicher Feiertag',
			self::Observance->value => 'Gedenktag',
			self::School->value => 'Schulfeiertag',
			self::Bank->value => 'Bankfeiertag',
			self::Public->value => 'Öffentlicher Feiertag',
			self::Private->value => 'Privater Feiertag',
			self::Mandatory->value => 'Pflichtfeiertag',
			self::Special->value => 'Besonderer Feiertag',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::National->value => 'Fête Nationale',
			self::State->value => 'Fête d\'État/Provinciale',
			self::Municipal->value => 'Fête Municipale',
			self::Regional->value => 'Fête Régionale',
			self::Religious->value => 'Fête Religieuse',
			self::Optional->value => 'Fête Facultative',
			self::Company->value => 'Fête d\'Entreprise',
			self::Federal->value => 'Fête Fédérale',
			self::Provincial->value => 'Fête Provinciale',
			self::Cantonal->value => 'Fête Cantonale',
			self::Local->value => 'Fête Locale',
			self::Cultural->value => 'Fête Culturelle',
			self::Historical->value => 'Fête Historique',
			self::Floating->value => 'Fête Mobile',
			self::Observance->value => 'Jour d\'Observation',
			self::School->value => 'Vacance Scolaire',
			self::Bank->value => 'Jour Férié Bancaire',
			self::Public->value => 'Jour Férié Public',
			self::Private->value => 'Jour Férié Privé',
			self::Mandatory->value => 'Jour Férié Obligatoire',
			self::Special->value => 'Jour Férié Spécial',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::National->value => 'חג לאומי',
			self::State->value => 'חג מדינה/מחוז',
			self::Municipal->value => 'חג עירוני',
			self::Regional->value => 'חג אזורי',
			self::Religious->value => 'חג דתי',
			self::Optional->value => 'חג אופציונלי',
			self::Company->value => 'חג חברה',
			self::Federal->value => 'חג פדרלי',
			self::Provincial->value => 'חג פרובינציאלי',
			self::Cantonal->value => 'חג קנטוני',
			self::Local->value => 'חג מקומי',
			self::Cultural->value => 'חג תרבותי',
			self::Historical->value => 'חג היסטורי',
			self::Floating->value => 'חג נע',
			self::Observance->value => 'יום התבוננות',
			self::School->value => 'חופשת בית ספר',
			self::Bank->value => 'חג בנק',
			self::Public->value => 'חג ציבורי',
			self::Private->value => 'חג פרטי',
			self::Mandatory->value => 'חג חובה',
			self::Special->value => 'חג מיוחד',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::National->value => 'Festa Nazionale',
			self::State->value => 'Festa Regionale/Provinciale',
			self::Municipal->value => 'Festa Comunale',
			self::Regional->value => 'Festa Regionale',
			self::Religious->value => 'Festa Religiosa',
			self::Optional->value => 'Festa Facoltativa',
			self::Company->value => 'Festa Aziendale',
			self::Federal->value => 'Festa Federale',
			self::Provincial->value => 'Festa Provinciale',
			self::Cantonal->value => 'Festa Cantonale',
			self::Local->value => 'Festa Locale',
			self::Cultural->value => 'Festa Culturale',
			self::Historical->value => 'Festa Storica',
			self::Floating->value => 'Festa Mobile',
			self::Observance->value => 'Giorno di Osservanza',
			self::School->value => 'Vacanza Scolastica',
			self::Bank->value => 'Giorno Festivo Bancario',
			self::Public->value => 'Festa Pubblica',
			self::Private->value => 'Festa Privata',
			self::Mandatory->value => 'Festa Obbligatoria',
			self::Special->value => 'Festa Speciale',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::National->value => '国民の祝日',
			self::State->value => '州/県の祝日',
			self::Municipal->value => '市町村の祝日',
			self::Regional->value => '地域の祝日',
			self::Religious->value => '宗教的祝日',
			self::Optional->value => '任意の祝日',
			self::Company->value => '会社の祝日',
			self::Federal->value => '連邦の祝日',
			self::Provincial->value => '州の祝日',
			self::Cantonal->value => '郡の祝日',
			self::Local->value => '地方の祝日',
			self::Cultural->value => '文化的祝日',
			self::Historical->value => '歴史的祝日',
			self::Floating->value => '移動祝日',
			self::Observance->value => '記念日',
			self::School->value => '学校の祝日',
			self::Bank->value => '銀行の祝日',
			self::Public->value => '公休日',
			self::Private->value => '私的祝日',
			self::Mandatory->value => '必須祝日',
			self::Special->value => '特別祝日',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::National->value => 'Nationale Feestdag',
			self::State->value => 'Staats-/Provincie Feestdag',
			self::Municipal->value => 'Gemeentelijke Feestdag',
			self::Regional->value => 'Regionale Feestdag',
			self::Religious->value => 'Religieuze Feestdag',
			self::Optional->value => 'Optionele Feestdag',
			self::Company->value => 'Bedrijfsfeestdag',
			self::Federal->value => 'Federale Feestdag',
			self::Provincial->value => 'Provinciale Feestdag',
			self::Cantonal->value => 'Kantonnale Feestdag',
			self::Local->value => 'Lokale Feestdag',
			self::Cultural->value => 'Culturele Feestdag',
			self::Historical->value => 'Historische Feestdag',
			self::Floating->value => 'Beweeglijke Feestdag',
			self::Observance->value => 'Herdenkingsdag',
			self::School->value => 'Schoolvakantie',
			self::Bank->value => 'Bankfeestdag',
			self::Public->value => 'Openbare Feestdag',
			self::Private->value => 'Privé Feestdag',
			self::Mandatory->value => 'Verplichte Feestdag',
			self::Special->value => 'Bijzondere Feestdag',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::National->value => 'Święto Narodowe',
			self::State->value => 'Święto Wojewódzkie',
			self::Municipal->value => 'Święto Miejskie',
			self::Regional->value => 'Święto Regionalne',
			self::Religious->value => 'Święto Religijne',
			self::Optional->value => 'Święto Opcjonalne',
			self::Company->value => 'Święto Firmowe',
			self::Federal->value => 'Święto Federalne',
			self::Provincial->value => 'Święto Prowincjonalne',
			self::Cantonal->value => 'Święto Kantonalne',
			self::Local->value => 'Święto Lokalne',
			self::Cultural->value => 'Święto Kulturowe',
			self::Historical->value => 'Święto Historyczne',
			self::Floating->value => 'Ruchome Święto',
			self::Observance->value => 'Dzień Upamiętniający',
			self::School->value => 'Święto Szkolne',
			self::Bank->value => 'Święto Bankowe',
			self::Public->value => 'Święto Publiczne',
			self::Private->value => 'Święto Prywatne',
			self::Mandatory->value => 'Święto Obowiązkowe',
			self::Special->value => 'Święto Specjalne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::National->value => 'Национальный праздник',
			self::State->value => 'Государственный/Областной праздник',
			self::Municipal->value => 'Муниципальный праздник',
			self::Regional->value => 'Региональный праздник',
			self::Religious->value => 'Религиозный праздник',
			self::Optional->value => 'Дополнительный праздник',
			self::Company->value => 'Корпоративный праздник',
			self::Federal->value => 'Федеральный праздник',
			self::Provincial->value => 'Провинциальный праздник',
			self::Cantonal->value => 'Кантональный праздник',
			self::Local->value => 'Местный праздник',
			self::Cultural->value => 'Культурный праздник',
			self::Historical->value => 'Исторический праздник',
			self::Floating->value => 'Подвижный праздник',
			self::Observance->value => 'День памяти',
			self::School->value => 'Школьный праздник',
			self::Bank->value => 'Банковский праздник',
			self::Public->value => 'Государственный праздник',
			self::Private->value => 'Частный праздник',
			self::Mandatory->value => 'Обязательный праздник',
			self::Special->value => 'Особый праздник',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::National->value => 'Ulusal Bayram',
			self::State->value => 'Eyalet/İl Bayramı',
			self::Municipal->value => 'Belediye Bayramı',
			self::Regional->value => 'Bölgesel Bayram',
			self::Religious->value => 'Dini Bayram',
			self::Optional->value => 'İsteğe Bağlı Bayram',
			self::Company->value => 'Şirket Bayramı',
			self::Federal->value => 'Federal Bayram',
			self::Provincial->value => 'İl Bayramı',
			self::Cantonal->value => 'Kanton Bayramı',
			self::Local->value => 'Yerel Bayram',
			self::Cultural->value => 'Kültürel Bayram',
			self::Historical->value => 'Tarihi Bayram',
			self::Floating->value => 'Değişken Bayram',
			self::Observance->value => 'Anma Günü',
			self::School->value => 'Okul Tatili',
			self::Bank->value => 'Banka Tatili',
			self::Public->value => 'Resmi Tatil',
			self::Private->value => 'Özel Tatil',
			self::Mandatory->value => 'Zorunlu Tatil',
			self::Special->value => 'Özel Tatil',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::National->value => '国庆节',
			self::State->value => '州/省假日',
			self::Municipal->value => '市假日',
			self::Regional->value => '地区假日',
			self::Religious->value => '宗教假日',
			self::Optional->value => '可选假日',
			self::Company->value => '公司假日',
			self::Federal->value => '联邦假日',
			self::Provincial->value => '省假日',
			self::Cantonal->value => '县/郡假日',
			self::Local->value => '地方假日',
			self::Cultural->value => '文化假日',
			self::Historical->value => '历史假日',
			self::Floating->value => '浮动假日',
			self::Observance->value => '纪念日',
			self::School->value => '学校假日',
			self::Bank->value => '银行假日',
			self::Public->value => '公共假日',
			self::Private->value => '私人假日',
			self::Mandatory->value => '强制假日',
			self::Special->value => '特别假日',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			self::National->value => 'Holiday observed throughout the entire country',
			self::State->value => 'Holiday observed within a specific state or province',
			self::Municipal->value => 'Holiday observed within a specific city or municipality',
			self::Regional->value => 'Holiday observed within a specific region',
			self::Religious->value => 'Holiday based on religious observance',
			self::Optional->value => 'Optional holiday that employees can choose to take',
			self::Company->value => 'Holiday specific to a particular company or organization',
			self::Federal->value => 'Federal government mandated holiday',
			self::Provincial->value => 'Holiday specific to a province',
			self::Cantonal->value => 'Holiday specific to a canton or county',
			self::Local->value => 'Holiday observed at local community level',
			self::Cultural->value => 'Holiday celebrating cultural heritage',
			self::Historical->value => 'Holiday commemorating historical events',
			self::Floating->value => 'Holiday with variable date each year',
			self::Observance->value => 'Day of observance or awareness',
			self::School->value => 'School-specific holiday or break',
			self::Bank->value => 'Bank holiday when banks are closed',
			self::Public->value => 'Public holiday recognized by government',
			self::Private->value => 'Private or personal holiday',
			self::Mandatory->value => 'Mandatory holiday that must be observed',
			self::Special->value => 'Special occasion holiday',
		];
	}

	public static function descriptionsPtBr(): array
	{
		return [
			self::National->value => 'Feriado observado em todo o país',
			self::State->value => 'Feriado observado em um estado ou província específica',
			self::Municipal->value => 'Feriado observado em uma cidade ou município específico',
			self::Regional->value => 'Feriado observado em uma região específica',
			self::Religious->value => 'Feriado baseado em observância religiosa',
			self::Optional->value => 'Feriado opcional que os funcionários podem escolher tirar',
			self::Company->value => 'Feriado específico de uma empresa ou organização',
			self::Federal->value => 'Feriado determinado pelo governo federal',
			self::Provincial->value => 'Feriado específico de uma província',
			self::Cantonal->value => 'Feriado específico de um cantão ou condado',
			self::Local->value => 'Feriado observado em nível comunitário local',
			self::Cultural->value => 'Feriado que celebra a herança cultural',
			self::Historical->value => 'Feriado que comemora eventos históricos',
			self::Floating->value => 'Feriado com data variável a cada ano',
			self::Observance->value => 'Dia de observância ou conscientização',
			self::School->value => 'Feriado específico da escola ou período de férias',
			self::Bank->value => 'Feriado bancário quando os bancos estão fechados',
			self::Public->value => 'Feriado público reconhecido pelo governo',
			self::Private->value => 'Feriado privado ou pessoal',
			self::Mandatory->value => 'Feriado obrigatório que deve ser observado',
			self::Special->value => 'Feriado de ocasião especial',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			self::National->value => 'Festivo observado en todo el país',
			self::State->value => 'Festivo observado en un estado o provincia específica',
			self::Municipal->value => 'Festivo observado en una ciudad o municipio específico',
			self::Regional->value => 'Festivo observado en una región específica',
			self::Religious->value => 'Festivo basado en observancia religiosa',
			self::Optional->value => 'Festivo opcional que los empleados pueden elegir tomar',
			self::Company->value => 'Festivo específico de una empresa u organización',
			self::Federal->value => 'Festivo determinado por el gobierno federal',
			self::Provincial->value => 'Festivo específico de una provincia',
			self::Cantonal->value => 'Festivo específico de un cantón o condado',
			self::Local->value => 'Festivo observado a nivel comunitario local',
			self::Cultural->value => 'Festivo que celebra el patrimonio cultural',
			self::Historical->value => 'Festivo que conmemora eventos históricos',
			self::Floating->value => 'Festivo con fecha variable cada año',
			self::Observance->value => 'Día de observancia o concienciación',
			self::School->value => 'Festivo específico de la escuela o período de vacaciones',
			self::Bank->value => 'Festivo bancario cuando los bancos están cerrados',
			self::Public->value => 'Festivo público reconocido por el gobierno',
			self::Private->value => 'Festivo privado o personal',
			self::Mandatory->value => 'Festivo obligatorio que debe observarse',
			self::Special->value => 'Festivo de ocasión especial',
		];
	}
}
