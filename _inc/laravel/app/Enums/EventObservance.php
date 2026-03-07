<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum EventObservance: string
{
	case Compensatory = 'compensatory';
	case Mandatory = 'mandatory';
	case Optional = 'optional';
	case Required = 'required';
	case Voluntary = 'voluntary';
	case Discretionary = 'discretionary';
	case Conditional = 'conditional';
	case Suggested = 'suggested';
	case Recommended = 'recommended';
	case Advised = 'advised';
	case Expected = 'expected';
	case Encouraged = 'encouraged';
	case Standard = 'standard';
	case Normal = 'normal';
	case Regular = 'regular';
	case Routine = 'routine';
	case Formal = 'formal';
	case Informal = 'informal';
	case Official = 'official';
	case Unofficial = 'unofficial';
	case Legal = 'legal';
	case Contractual = 'contractual';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self) {
			return $value;
		}
		if ($value === null) {
			return self::Standard;
		}

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim($value)));
		return match ($normalizedValue) {
			// Main categories
			'compensatory', 'compensation', 'compensate', 'comp', 'lieu', 'inlieu', 'makeup', 'makeupday', 'substitute' => self::Compensatory,
			'mandatory', 'mandate', 'mandated', 'must', 'required', 'requirement', 'compulsory', 'obligatory', 'forced', 'enforced' => self::Mandatory,
			'optional', 'option', 'optional', 'elective', 'choice', 'choose', 'selective' => self::Optional,
			'required', 'require', 'requirement', 'necessity', 'necessary', 'need', 'needed' => self::Required,
			'voluntary', 'volunteer', 'voluntarily', 'willing', 'unforced', 'uncoerced' => self::Voluntary,
			'discretionary', 'discretion', 'judgment', 'choice', 'option' => self::Discretionary,
			'conditional', 'condition', 'contingent', 'dependent', 'depending', 'subjectto' => self::Conditional,
			'suggested', 'suggestion', 'proposed', 'recommended', 'advised', 'counseled' => self::Suggested,
			'recommended', 'recommendation', 'endorsed', 'approved', 'supported' => self::Recommended,
			'advised', 'advice', 'counsel', 'guidance', 'guided' => self::Advised,
			'expected', 'expectation', 'anticipated', 'predicted', 'foreseen' => self::Expected,
			'encouraged', 'encouragement', 'promoted', 'fostered', 'supported' => self::Encouraged,
			'standard', 'normal', 'regular', 'routine', 'usual', 'typical', 'ordinary', 'common' => self::Standard,
			'normal', 'norm', 'regular', 'usual', 'typical', 'ordinary' => self::Normal,
			'regular', 'reg', 'routine', 'scheduled', 'periodic' => self::Regular,
			'routine', 'habitual', 'customary', 'usual', 'typical' => self::Routine,
			'formal', 'formality', 'official', 'ceremonial', 'ritual' => self::Formal,
			'informal', 'casual', 'unofficial', 'relaxed', 'unceremonious' => self::Informal,
			'official', 'officially', 'authorized', 'approved', 'sanctioned' => self::Official,
			'unofficial', 'unofficially', 'unauthorized', 'unapproved', 'unsanctioned' => self::Unofficial,
			'legal', 'legally', 'lawful', 'statutory', 'juridical' => self::Legal,
			'contractual', 'contract', 'agreement', 'stipulated', 'agreed' => self::Contractual,

			default => self::Standard,
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
	 * Get the label for a specific observance type in the specified language
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
			self::Compensatory => 'arrow-path',
			self::Mandatory, self::Required => 'shield-check',
			self::Optional, self::Discretionary, self::Voluntary => 'hand-raised',
			self::Conditional => 'adjustments-vertical',
			self::Suggested, self::Recommended, self::Advised => 'light-bulb',
			self::Expected, self::Encouraged => 'trending-up',
			self::Standard, self::Normal, self::Regular, self::Routine => 'chart-bar',
			self::Formal, self::Official => 'document-text',
			self::Informal, self::Unofficial => 'document-duplicate',
			self::Legal, self::Contractual => 'scale',
			default => 'calendar',
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::Compensatory => '#8b5cf6', // purple
			self::Mandatory, self::Required => '#ef4444', // red
			self::Optional, self::Voluntary => '#10b981', // green
			self::Discretionary, self::Conditional => '#f59e0b', // amber
			self::Suggested, self::Recommended, self::Advised => '#3b82f6', // blue
			self::Expected, self::Encouraged => '#ec4899', // pink
			self::Standard, self::Normal, self::Regular, self::Routine => '#6b7280', // gray
			self::Formal, self::Official => '#84cc16', // lime
			self::Informal, self::Unofficial => '#f97316', // orange
			self::Legal, self::Contractual => '#6366f1', // indigo
			default => '#9ca3af', // cool gray
		};
	}

	/**
	 * Get the enforcement level of this observance type
	 */
	public function getEnforcementLevel(): int
	{
		return match ($this) {
			self::Mandatory, self::Required, self::Legal, self::Contractual => 1, // Highest enforcement
			self::Compensatory, self::Conditional => 2,
			self::Expected, self::Standard => 3,
			self::Official, self::Formal => 4,
			self::Recommended, self::Advised, self::Encouraged => 5,
			self::Suggested, self::Normal, self::Regular => 6,
			self::Optional, self::Voluntary, self::Discretionary => 7,
			self::Informal, self::Unofficial, self::Routine => 8,
			default => 9,
		};
	}

	/**
	 * Check if this observance type is required/mandatory
	 */
	public function isRequired(): bool
	{
		return in_array($this, [
			self::Mandatory,
			self::Required,
			self::Legal,
			self::Contractual,
			self::Compensatory,
		]);
	}

	/**
	 * Check if this observance type is optional/discretionary
	 */
	public function isOptional(): bool
	{
		return in_array($this, [
			self::Optional,
			self::Voluntary,
			self::Discretionary,
			self::Informal,
			self::Unofficial,
		]);
	}

	/**
	 * Check if this observance type is recommended/suggested
	 */
	public function isRecommended(): bool
	{
		return in_array($this, [
			self::Recommended,
			self::Suggested,
			self::Advised,
			self::Encouraged,
		]);
	}

	/**
	 * Get the typical compliance requirement
	 */
	public function getComplianceRequirement(): string
	{
		return match ($this) {
			self::Mandatory, self::Required => 'must_comply',
			self::Legal, self::Contractual => 'legally_bound',
			self::Compensatory => 'compensation_required',
			self::Conditional => 'conditions_apply',
			self::Official, self::Formal => 'formally_expected',
			self::Recommended, self::Advised => 'strongly_recommended',
			self::Suggested, self::Encouraged => 'recommended',
			self::Expected, self::Standard, self::Normal => 'generally_expected',
			self::Optional, self::Voluntary => 'voluntary',
			self::Discretionary => 'at_discretion',
			self::Informal, self::Unofficial, self::Routine => 'informal',
			default => 'not_specified',
		};
	}

	/**
	 * Get the description of what this observance type means
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
	 * Get the priority for observance type in scheduling
	 */
	public function getSchedulingPriority(): int
	{
		return match ($this) {
			self::Mandatory, self::Required, self::Legal => 1, // Highest priority
			self::Contractual, self::Compensatory => 2,
			self::Official, self::Formal => 3,
			self::Expected, self::Standard => 4,
			self::Conditional, self::Recommended => 5,
			self::Advised, self::Encouraged => 6,
			self::Suggested, self::Normal => 7,
			self::Regular, self::Routine => 8,
			self::Optional, self::Voluntary => 9,
			self::Discretionary, self::Informal => 10,
			self::Unofficial => 11, // Lowest priority
			default => 12,
		};
	}

	/**
	 * Check if this observance type typically requires documentation
	 */
	public function requiresDocumentation(): bool
	{
		return in_array($this, [
			self::Legal,
			self::Contractual,
			self::Official,
			self::Formal,
			self::Compensatory,
			self::Mandatory,
			self::Required,
		]);
	}

	/**
	 * Get the typical notification requirement (in days)
	 */
	public function getNotificationRequirement(): int
	{
		return match ($this) {
			self::Mandatory, self::Required, self::Legal => 30,
			self::Contractual, self::Compensatory => 14,
			self::Official, self::Formal => 7,
			self::Expected, self::Standard => 3,
			self::Recommended, self::Advised => 2,
			self::Suggested, self::Encouraged => 1,
			self::Conditional => 0, // Depends on condition
			self::Optional, self::Voluntary, self::Discretionary => 0,
			self::Informal, self::Unofficial, self::Normal, self::Regular, self::Routine => 0,
			default => 0,
		};
	}

	/**
	 * Get the category group for this observance type
	 */
	public function getCategory(): string
	{
		return match ($this) {
			self::Mandatory, self::Required, self::Legal, self::Contractual => 'binding',
			self::Compensatory, self::Conditional => 'conditional',
			self::Official, self::Formal => 'formal',
			self::Recommended, self::Advised, self::Suggested, self::Encouraged => 'advisory',
			self::Expected, self::Standard, self::Normal, self::Regular, self::Routine => 'standard',
			self::Optional, self::Voluntary, self::Discretionary => 'voluntary',
			self::Informal, self::Unofficial => 'informal',
			default => 'other',
		};
	}

	/**
	 * Check if this observance type can be overridden
	 */
	public function canBeOverridden(): bool
	{
		return match ($this) {
			self::Mandatory, self::Required, self::Legal, self::Contractual => false,
			self::Compensatory, self::Conditional => true, // With conditions
			self::Official, self::Formal => false,
			self::Recommended, self::Advised, self::Suggested, self::Encouraged => true,
			self::Expected, self::Standard, self::Normal, self::Regular, self::Routine => true,
			self::Optional, self::Voluntary, self::Discretionary => true,
			self::Informal, self::Unofficial => true,
			default => true,
		};
	}

	/**
	 * Get the enforcement authority level
	 */
	public function getEnforcementAuthority(): string
	{
		return match ($this) {
			self::Legal => 'government_law',
			self::Contractual => 'contract_parties',
			self::Official, self::Formal => 'organization_management',
			self::Mandatory, self::Required => 'management_authority',
			self::Compensatory => 'hr_department',
			self::Conditional => 'conditional_authority',
			self::Recommended, self::Advised => 'advisor_recommendation',
			self::Suggested, self::Encouraged => 'suggestion_only',
			self::Expected, self::Standard => 'social_norms',
			self::Optional, self::Voluntary => 'individual_choice',
			self::Discretionary => 'manager_discretion',
			self::Informal, self::Unofficial => 'informal_agreement',
			self::Normal, self::Regular, self::Routine => 'established_practice',
			default => 'not_specified',
		};
	}

	public static function labelsEn(): array
	{
		return [
			self::Compensatory->value => 'Compensatory',
			self::Mandatory->value => 'Mandatory',
			self::Optional->value => 'Optional',
			self::Required->value => 'Required',
			self::Voluntary->value => 'Voluntary',
			self::Discretionary->value => 'Discretionary',
			self::Conditional->value => 'Conditional',
			self::Suggested->value => 'Suggested',
			self::Recommended->value => 'Recommended',
			self::Advised->value => 'Advised',
			self::Expected->value => 'Expected',
			self::Encouraged->value => 'Encouraged',
			self::Standard->value => 'Standard',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Regular',
			self::Routine->value => 'Routine',
			self::Formal->value => 'Formal',
			self::Informal->value => 'Informal',
			self::Official->value => 'Official',
			self::Unofficial->value => 'Unofficial',
			self::Legal->value => 'Legal',
			self::Contractual->value => 'Contractual',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Compensatory->value => 'Compensatório',
			self::Mandatory->value => 'Obrigatório',
			self::Optional->value => 'Opcional',
			self::Required->value => 'Requerido',
			self::Voluntary->value => 'Voluntário',
			self::Discretionary->value => 'Discricionário',
			self::Conditional->value => 'Condicional',
			self::Suggested->value => 'Sugerido',
			self::Recommended->value => 'Recomendado',
			self::Advised->value => 'Aconselhado',
			self::Expected->value => 'Esperado',
			self::Encouraged->value => 'Incentivado',
			self::Standard->value => 'Padrão',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Regular',
			self::Routine->value => 'Rotina',
			self::Formal->value => 'Formal',
			self::Informal->value => 'Informal',
			self::Official->value => 'Oficial',
			self::Unofficial->value => 'Não Oficial',
			self::Legal->value => 'Legal',
			self::Contractual->value => 'Contratual',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Compensatory->value => 'Compensatorio',
			self::Mandatory->value => 'Obligatorio',
			self::Optional->value => 'Opcional',
			self::Required->value => 'Requerido',
			self::Voluntary->value => 'Voluntario',
			self::Discretionary->value => 'Discrecional',
			self::Conditional->value => 'Condicional',
			self::Suggested->value => 'Sugerido',
			self::Recommended->value => 'Recomendado',
			self::Advised->value => 'Aconsejado',
			self::Expected->value => 'Esperado',
			self::Encouraged->value => 'Fomentado',
			self::Standard->value => 'Estándar',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Regular',
			self::Routine->value => 'Rutina',
			self::Formal->value => 'Formal',
			self::Informal->value => 'Informal',
			self::Official->value => 'Oficial',
			self::Unofficial->value => 'No Oficial',
			self::Legal->value => 'Legal',
			self::Contractual->value => 'Contractual',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Compensatory->value => 'تعويضي',
			self::Mandatory->value => 'إلزامي',
			self::Optional->value => 'اختياري',
			self::Required->value => 'مطلوب',
			self::Voluntary->value => 'طوعي',
			self::Discretionary->value => 'تقديري',
			self::Conditional->value => 'مشروط',
			self::Suggested->value => 'مقترح',
			self::Recommended->value => 'موصى به',
			self::Advised->value => 'مشورة',
			self::Expected->value => 'متوقع',
			self::Encouraged->value => 'مشجع',
			self::Standard->value => 'قياسي',
			self::Normal->value => 'عادي',
			self::Regular->value => 'منتظم',
			self::Routine->value => 'روتيني',
			self::Formal->value => 'رسمي',
			self::Informal->value => 'غير رسمي',
			self::Official->value => 'رسمي',
			self::Unofficial->value => 'غير رسمي',
			self::Legal->value => 'قانوني',
			self::Contractual->value => 'تعاقدي',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Compensatory->value => 'Ausgleich',
			self::Mandatory->value => 'Verpflichtend',
			self::Optional->value => 'Optional',
			self::Required->value => 'Erforderlich',
			self::Voluntary->value => 'Freiwillig',
			self::Discretionary->value => 'Ermessenssache',
			self::Conditional->value => 'Bedingt',
			self::Suggested->value => 'Vorgeschlagen',
			self::Recommended->value => 'Empfohlen',
			self::Advised->value => 'Beraten',
			self::Expected->value => 'Erwartet',
			self::Encouraged->value => 'Ermutigt',
			self::Standard->value => 'Standard',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Regelmäßig',
			self::Routine->value => 'Routine',
			self::Formal->value => 'Formell',
			self::Informal->value => 'Informell',
			self::Official->value => 'Offiziell',
			self::Unofficial->value => 'Inoffiziell',
			self::Legal->value => 'Gesetzlich',
			self::Contractual->value => 'Vertraglich',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Compensatory->value => 'Compensatoire',
			self::Mandatory->value => 'Obligatoire',
			self::Optional->value => 'Optionnel',
			self::Required->value => 'Requis',
			self::Voluntary->value => 'Volontaire',
			self::Discretionary->value => 'Discrétionnaire',
			self::Conditional->value => 'Conditionnel',
			self::Suggested->value => 'Suggéré',
			self::Recommended->value => 'Recommandé',
			self::Advised->value => 'Conseillé',
			self::Expected->value => 'Attendu',
			self::Encouraged->value => 'Encouragé',
			self::Standard->value => 'Standard',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Régulier',
			self::Routine->value => 'Routine',
			self::Formal->value => 'Formel',
			self::Informal->value => 'Informel',
			self::Official->value => 'Officiel',
			self::Unofficial->value => 'Non Officiel',
			self::Legal->value => 'Légal',
			self::Contractual->value => 'Contractuel',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Compensatory->value => '補償的',
			self::Mandatory->value => '必須',
			self::Optional->value => '任意',
			self::Required->value => '必要',
			self::Voluntary->value => '自発的',
			self::Discretionary->value => '裁量的',
			self::Conditional->value => '条件的',
			self::Suggested->value => '提案',
			self::Recommended->value => '推薦',
			self::Advised->value => '助言',
			self::Expected->value => '期待',
			self::Encouraged->value => '奨励',
			self::Standard->value => '標準',
			self::Normal->value => '通常',
			self::Regular->value => '定期的',
			self::Routine->value => 'ルーチン',
			self::Formal->value => '正式',
			self::Informal->value => '非公式',
			self::Official->value => '公式',
			self::Unofficial->value => '非公式',
			self::Legal->value => '法的',
			self::Contractual->value => '契約的',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Compensatory->value => '补偿性',
			self::Mandatory->value => '强制性',
			self::Optional->value => '可选',
			self::Required->value => '必需',
			self::Voluntary->value => '自愿',
			self::Discretionary->value => '酌情',
			self::Conditional->value => '有条件',
			self::Suggested->value => '建议',
			self::Recommended->value => '推荐',
			self::Advised->value => '建议',
			self::Expected->value => '预期',
			self::Encouraged->value => '鼓励',
			self::Standard->value => '标准',
			self::Normal->value => '正常',
			self::Regular->value => '定期',
			self::Routine->value => '常规',
			self::Formal->value => '正式',
			self::Informal->value => '非正式',
			self::Official->value => '官方',
			self::Unofficial->value => '非官方',
			self::Legal->value => '法律',
			self::Contractual->value => '合同',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Compensatory->value => 'Compensatoir',
			self::Mandatory->value => 'Verplicht',
			self::Optional->value => 'Optioneel',
			self::Required->value => 'Vereist',
			self::Voluntary->value => 'Vrijwillig',
			self::Discretionary->value => 'Discretionair',
			self::Conditional->value => 'Voorwaardelijk',
			self::Suggested->value => 'Voorgesteld',
			self::Recommended->value => 'Aanbevolen',
			self::Advised->value => 'Geadviseerd',
			self::Expected->value => 'Verwacht',
			self::Encouraged->value => 'Aangemoedigd',
			self::Standard->value => 'Standaard',
			self::Normal->value => 'Normaal',
			self::Regular->value => 'Regelmatig',
			self::Routine->value => 'Routine',
			self::Formal->value => 'Formeel',
			self::Informal->value => 'Informeel',
			self::Official->value => 'Officieel',
			self::Unofficial->value => 'Niet-officieel',
			self::Legal->value => 'Wettelijk',
			self::Contractual->value => 'Contractueel',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Compensatory->value => 'Kompensacyjny',
			self::Mandatory->value => 'Obowiązkowy',
			self::Optional->value => 'Opcjonalny',
			self::Required->value => 'Wymagany',
			self::Voluntary->value => 'Dobrowolny',
			self::Discretionary->value => 'Uznaniowy',
			self::Conditional->value => 'Warunkowy',
			self::Suggested->value => 'Sugerowany',
			self::Recommended->value => 'Rekomendowany',
			self::Advised->value => 'Zalecany',
			self::Expected->value => 'Oczekiwany',
			self::Encouraged->value => 'Zachęcany',
			self::Standard->value => 'Standardowy',
			self::Normal->value => 'Normalny',
			self::Regular->value => 'Regularny',
			self::Routine->value => 'Rutynowy',
			self::Formal->value => 'Formalny',
			self::Informal->value => 'Nieformalny',
			self::Official->value => 'Oficjalny',
			self::Unofficial->value => 'Nieoficjalny',
			self::Legal->value => 'Prawny',
			self::Contractual->value => 'Kontraktowy',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Compensatory->value => 'Компенсационный',
			self::Mandatory->value => 'Обязательный',
			self::Optional->value => 'Необязательный',
			self::Required->value => 'Требуемый',
			self::Voluntary->value => 'Добровольный',
			self::Discretionary->value => 'Дискреционный',
			self::Conditional->value => 'Условный',
			self::Suggested->value => 'Предложенный',
			self::Recommended->value => 'Рекомендованный',
			self::Advised->value => 'Советованный',
			self::Expected->value => 'Ожидаемый',
			self::Encouraged->value => 'Поощряемый',
			self::Standard->value => 'Стандартный',
			self::Normal->value => 'Обычный',
			self::Regular->value => 'Регулярный',
			self::Routine->value => 'Рутинный',
			self::Formal->value => 'Формальный',
			self::Informal->value => 'Неформальный',
			self::Official->value => 'Официальный',
			self::Unofficial->value => 'Неофициальный',
			self::Legal->value => 'Юридический',
			self::Contractual->value => 'Контрактный',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Compensatory->value => 'Tazminat',
			self::Mandatory->value => 'Zorunlu',
			self::Optional->value => 'İsteğe Bağlı',
			self::Required->value => 'Gerekli',
			self::Voluntary->value => 'Gönüllü',
			self::Discretionary->value => 'İhtiyari',
			self::Conditional->value => 'Koşullu',
			self::Suggested->value => 'Önerilen',
			self::Recommended->value => 'Tavsiye Edilen',
			self::Advised->value => 'Tavsiye Edilmiş',
			self::Expected->value => 'Beklenen',
			self::Encouraged->value => 'Teşvik Edilen',
			self::Standard->value => 'Standart',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Düzenli',
			self::Routine->value => 'Rutin',
			self::Formal->value => 'Resmi',
			self::Informal->value => 'Gayriresmi',
			self::Official->value => 'Resmi',
			self::Unofficial->value => 'Gayriresmi',
			self::Legal->value => 'Yasal',
			self::Contractual->value => 'Sözleşmeli',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Compensatory->value => 'פיצוי',
			self::Mandatory->value => 'חובה',
			self::Optional->value => 'אופציונלי',
			self::Required->value => 'נדרש',
			self::Voluntary->value => 'התנדבותי',
			self::Discretionary->value => 'דיסקרטי',
			self::Conditional->value => 'מותנה',
			self::Suggested->value => 'מוצע',
			self::Recommended->value => 'מומלץ',
			self::Advised->value => 'מיועץ',
			self::Expected->value => 'צפוי',
			self::Encouraged->value => 'מעודד',
			self::Standard->value => 'סטנדרטי',
			self::Normal->value => 'נורמלי',
			self::Regular->value => 'רגיל',
			self::Routine->value => 'שגרתי',
			self::Formal->value => 'פורמלי',
			self::Informal->value => 'לא פורמלי',
			self::Official->value => 'רשמי',
			self::Unofficial->value => 'לא רשמי',
			self::Legal->value => 'חוקי',
			self::Contractual->value => 'חוזי',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Compensatory->value => 'Compensativo',
			self::Mandatory->value => 'Obbligatorio',
			self::Optional->value => 'Opzionale',
			self::Required->value => 'Richiesto',
			self::Voluntary->value => 'Volontario',
			self::Discretionary->value => 'Discrezionale',
			self::Conditional->value => 'Condizionale',
			self::Suggested->value => 'Suggerito',
			self::Recommended->value => 'Raccomandato',
			self::Advised->value => 'Consigliato',
			self::Expected->value => 'Previsto',
			self::Encouraged->value => 'Incoraggiato',
			self::Standard->value => 'Standard',
			self::Normal->value => 'Normale',
			self::Regular->value => 'Regolare',
			self::Routine->value => 'Di routine',
			self::Formal->value => 'Formale',
			self::Informal->value => 'Informale',
			self::Official->value => 'Ufficiale',
			self::Unofficial->value => 'Non ufficiale',
			self::Legal->value => 'Legale',
			self::Contractual->value => 'Contrattuale',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Compensatory->value => 'Kompensatorisk',
			self::Mandatory->value => 'Obligatorisk',
			self::Optional->value => 'Valgfri',
			self::Required->value => 'Påkrævet',
			self::Voluntary->value => 'Frivillig',
			self::Discretionary->value => 'Skønsmæssig',
			self::Conditional->value => 'Betinget',
			self::Suggested->value => 'Foreslået',
			self::Recommended->value => 'Anbefalet',
			self::Advised->value => 'Rådet',
			self::Expected->value => 'Forventet',
			self::Encouraged->value => 'Opfordret',
			self::Standard->value => 'Standard',
			self::Normal->value => 'Normal',
			self::Regular->value => 'Regelmæssig',
			self::Routine->value => 'Rutine',
			self::Formal->value => 'Formel',
			self::Informal->value => 'Uformel',
			self::Official->value => 'Officiel',
			self::Unofficial->value => 'Uofficiel',
			self::Legal->value => 'Juridisk',
			self::Contractual->value => 'Kontraktuel',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			self::Compensatory->value => 'Compensatory observance to make up for another event or time',
			self::Mandatory->value => 'Mandatory observance that must be followed',
			self::Optional->value => 'Optional observance that can be chosen',
			self::Required->value => 'Required observance that is necessary',
			self::Voluntary->value => 'Voluntary observance based on personal choice',
			self::Discretionary->value => 'Discretionary observance based on judgment',
			self::Conditional->value => 'Conditional observance depending on circumstances',
			self::Suggested->value => 'Suggested observance but not required',
			self::Recommended->value => 'Recommended observance based on advice',
			self::Advised->value => 'Advised observance based on guidance',
			self::Expected->value => 'Expected observance based on norms',
			self::Encouraged->value => 'Encouraged observance that is promoted',
			self::Standard->value => 'Standard observance following normal procedures',
			self::Normal->value => 'Normal observance under regular circumstances',
			self::Regular->value => 'Regular observance at scheduled intervals',
			self::Routine->value => 'Routine observance as part of regular activities',
			self::Formal->value => 'Formal observance with official procedures',
			self::Informal->value => 'Informal observance without formal requirements',
			self::Official->value => 'Official observance with formal recognition',
			self::Unofficial->value => 'Unofficial observance without formal recognition',
			self::Legal->value => 'Legal observance required by law',
			self::Contractual->value => 'Contractual observance based on agreement',
		];
	}

	public static function descriptionsPtBr(): array
	{
		return [
			self::Compensatory->value => 'Observância compensatória para compensar outro evento ou tempo',
			self::Mandatory->value => 'Observância obrigatória que deve ser seguida',
			self::Optional->value => 'Observância opcional que pode ser escolhida',
			self::Required->value => 'Observância necessária que é requerida',
			self::Voluntary->value => 'Observância voluntária baseada na escolha pessoal',
			self::Discretionary->value => 'Observância discricionária baseada no julgamento',
			self::Conditional->value => 'Observância condicional dependendo das circunstâncias',
			self::Suggested->value => 'Observância sugerida mas não obrigatória',
			self::Recommended->value => 'Observância recomendada baseada em conselho',
			self::Advised->value => 'Observância aconselhada baseada em orientação',
			self::Expected->value => 'Observância esperada baseada em normas',
			self::Encouraged->value => 'Observância incentivada que é promovida',
			self::Standard->value => 'Observância padrão seguindo procedimentos normais',
			self::Normal->value => 'Observância normal sob circunstâncias regulares',
			self::Regular->value => 'Observância regular em intervalos programados',
			self::Routine->value => 'Observância de rotina como parte de atividades regulares',
			self::Formal->value => 'Observância formal com procedimentos oficiais',
			self::Informal->value => 'Observância informal sem requisitos formais',
			self::Official->value => 'Observância oficial com reconhecimento formal',
			self::Unofficial->value => 'Observância não oficial sem reconhecimento formal',
			self::Legal->value => 'Observância legal exigida por lei',
			self::Contractual->value => 'Observância contratual baseada em acordo',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			self::Compensatory->value => 'Observancia compensatoria para compensar otro evento o tiempo',
			self::Mandatory->value => 'Observancia obligatoria que debe seguirse',
			self::Optional->value => 'Observancia opcional que puede elegirse',
			self::Required->value => 'Observancia requerida que es necesaria',
			self::Voluntary->value => 'Observancia voluntaria basada en elección personal',
			self::Discretionary->value => 'Observancia discrecional basada en juicio',
			self::Conditional->value => 'Observancia condicional dependiendo de circunstancias',
			self::Suggested->value => 'Observancia sugerida pero no requerida',
			self::Recommended->value => 'Observancia recomendada basada en consejo',
			self::Advised->value => 'Observancia aconsejada basada en orientación',
			self::Expected->value => 'Observancia esperada basada en normas',
			self::Encouraged->value => 'Observancia fomentada que se promueve',
			self::Standard->value => 'Observancia estándar siguiendo procedimientos normales',
			self::Normal->value => 'Observancia normal bajo circunstancias regulares',
			self::Regular->value => 'Observancia regular en intervalos programados',
			self::Routine->value => 'Observancia de rutina como parte de actividades regulares',
			self::Formal->value => 'Observancia formal con procedimientos oficiales',
			self::Informal->value => 'Observancia informal sin requisitos formales',
			self::Official->value => 'Observancia oficial con reconocimiento formal',
			self::Unofficial->value => 'Observancia no oficial sin reconocimiento formal',
			self::Legal->value => 'Observancia legal requerida por ley',
			self::Contractual->value => 'Observancia contractual basada en acuerdo',
		];
	}
}
