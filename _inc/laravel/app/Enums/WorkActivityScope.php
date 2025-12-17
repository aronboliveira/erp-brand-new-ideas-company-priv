<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum WorkActivityScope: string
{
	case Internal = 'internal';
	case External = 'external';
	case Hybrid = 'hybrid';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Internal;

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			'internal', 'interno', 'intern', 'interne', 'interna' => self::Internal,
			'external', 'externo', 'extern', 'externe', 'externa' => self::External,
			'hybrid', 'hibrido', 'hybride', 'misto', 'mixed' => self::Hybrid,
			default => self::Internal,
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
	 * Get the label for a specific work activity scope in the specified language
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
			self::Internal => 'building',
			self::External => 'globe',
			self::Hybrid => 'arrows-left-right',
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::Internal => '#3b82f6', // blue
			self::External => '#10b981', // green
			self::Hybrid => '#8b5cf6', // purple
		};
	}

	/**
	 * Check if this scope is internal-only
	 */
	public function isInternal(): bool
	{
		return $this === self::Internal;
	}

	/**
	 * Check if this scope is external-only
	 */
	public function isExternal(): bool
	{
		return $this === self::External;
	}

	/**
	 * Check if this scope is hybrid
	 */
	public function isHybrid(): bool
	{
		return $this === self::Hybrid;
	}

	/**
	 * Get the description of what this scope means
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
			'ar', 'ar-sa' => self::descriptionsAr(),
			'da', 'da-dk' => self::descriptionsDa(),
			'de', 'de-de' => self::descriptionsDe(),
			'fr', 'fr-fr' => self::descriptionsFr(),
			'he', 'he-il' => self::descriptionsHe(),
			'it', 'it-it' => self::descriptionsIt(),
			'ja', 'ja-jp' => self::descriptionsJa(),
			'nl', 'nl-nl' => self::descriptionsNl(),
			'pl', 'pl-pl' => self::descriptionsPl(),
			'ru', 'ru-ru' => self::descriptionsRu(),
			'tr', 'tr-tr' => self::descriptionsTr(),
			'zh', 'zh-cn' => self::descriptionsZh(),
			default => self::descriptionsEn(),
		};
	}

	/**
	 * Get the typical location type for this scope
	 */
	public function getLocationType(): string
	{
		return match ($this) {
			self::Internal => 'office',
			self::External => 'remote',
			self::Hybrid => 'flexible',
		};
	}

	/**
	 * Get the default work arrangement percentage
	 * Returns an array with [internal_percentage, external_percentage]
	 */
	public function getDefaultDistribution(): array
	{
		return match ($this) {
			self::Internal => [100, 0],
			self::External => [0, 100],
			self::Hybrid => [60, 40], // Default 60% internal, 40% external
		};
	}

	/**
	 * Get the typical communication tools for this scope
	 */
	public function getCommunicationTools(): array
	{
		return match ($this) {
			self::Internal => ['internal_messaging', 'intranet', 'company_email'],
			self::External => ['external_email', 'video_conferencing', 'client_portal'],
			self::Hybrid => array_merge(
				self::Internal->getCommunicationTools(),
				self::External->getCommunicationTools()
			),
		};
	}

	/**
	 * Get the security level required for this scope
	 */
	public function getSecurityLevel(): string
	{
		return match ($this) {
			self::Internal => 'high',
			self::External => 'medium',
			self::Hybrid => 'mixed',
		};
	}

	/**
	 * Check if this scope requires external communication protocols
	 */
	public function requiresExternalProtocols(): bool
	{
		return in_array($this, [self::External, self::Hybrid]);
	}

	/**
	 * Check if this scope allows remote work
	 */
	public function allowsRemoteWork(): bool
	{
		return in_array($this, [self::External, self::Hybrid]);
	}

	/**
	 * Get the recommended meeting frequency for this scope
	 */
	public function getMeetingFrequency(): string
	{
		return match ($this) {
			self::Internal => 'daily',
			self::External => 'weekly',
			self::Hybrid => 'bi-weekly',
		};
	}

	/**
	 * Get the typical cost structure for this scope
	 */
	public function getCostStructure(): string
	{
		return match ($this) {
			self::Internal => 'fixed',
			self::External => 'variable',
			self::Hybrid => 'mixed',
		};
	}

	/**
	 * Convert to the original array format for backward compatibility
	 */
	public static function toOriginalArray($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$labels = self::labels($lang);
		return [
			$labels['internal'],  // Internal
			$labels['external'],  // External
			$labels['hybrid'],    // Hybrid
		];
	}

	/**
	 * Get all cases in the original order
	 */
	public static function inOriginalOrder(): array
	{
		return [
			self::Internal,
			self::External,
			self::Hybrid,
		];
	}

	/**
	 * Get the next logical scope (for workflow progression)
	 */
	public function getNextScope(): ?self
	{
		return match ($this) {
			self::Internal => self::Hybrid,
			self::Hybrid => self::External,
			self::External => null,
		};
	}

	/**
	 * Get the previous logical scope (for workflow regression)
	 */
	public function getPreviousScope(): ?self
	{
		return match ($this) {
			self::External => self::Hybrid,
			self::Hybrid => self::Internal,
			self::Internal => null,
		};
	}

	/**
	 * Calculate scope compatibility between two scopes
	 */
	public function isCompatibleWith(self $otherScope): bool
	{
		if ($this === self::Hybrid || $otherScope === self::Hybrid) {
			return true; // Hybrid is compatible with everything
		}

		return $this === $otherScope;
	}

	public static function labelsEn(): array
	{
		return [
			self::Internal->value => 'Internal',
			self::External->value => 'External',
			self::Hybrid->value => 'Hybrid',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Internal->value => 'Interno',
			self::External->value => 'Externo',
			self::Hybrid->value => 'Híbrido',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Internal->value => 'Interno',
			self::External->value => 'Externo',
			self::Hybrid->value => 'Híbrido',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Internal->value => 'داخلي',
			self::External->value => 'خارجي',
			self::Hybrid->value => 'هجين',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Internal->value => 'Intern',
			self::External->value => 'Ekstern',
			self::Hybrid->value => 'Hybrid',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Internal->value => 'Intern',
			self::External->value => 'Extern',
			self::Hybrid->value => 'Hybrid',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Internal->value => 'Interne',
			self::External->value => 'Externe',
			self::Hybrid->value => 'Hybride',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Internal->value => 'פנימי',
			self::External->value => 'חיצוני',
			self::Hybrid->value => 'היברידי',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Internal->value => 'Interno',
			self::External->value => 'Esterno',
			self::Hybrid->value => 'Ibrido',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Internal->value => '内部',
			self::External->value => '外部',
			self::Hybrid->value => 'ハイブリッド',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Internal->value => 'Intern',
			self::External->value => 'Extern',
			self::Hybrid->value => 'Hybride',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Internal->value => 'Wewnętrzny',
			self::External->value => 'Zewnętrzny',
			self::Hybrid->value => 'Hybrydowy',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Internal->value => 'Внутренний',
			self::External->value => 'Внешний',
			self::Hybrid->value => 'Гибридный',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Internal->value => 'İç',
			self::External->value => 'Dış',
			self::Hybrid->value => 'Karma',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Internal->value => '内部',
			self::External->value => '外部',
			self::Hybrid->value => '混合',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			self::Internal->value => 'Work activities performed entirely within the organization',
			self::External->value => 'Work activities involving external parties or locations',
			self::Hybrid->value => 'Combination of internal and external work activities',
		];
	}

	public static function descriptionsPtBr(): array
	{
		return [
			self::Internal->value => 'Atividades de trabalho realizadas inteiramente dentro da organização',
			self::External->value => 'Atividades de trabalho envolvendo partes ou locais externos',
			self::Hybrid->value => 'Combinação de atividades de trabalho internas e externas',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			self::Internal->value => 'Actividades laborales realizadas completamente dentro de la organización',
			self::External->value => 'Actividades laborales que involucran partes o ubicaciones externas',
			self::Hybrid->value => 'Combinación de actividades laborales internas y externas',
		];
	}

	public static function descriptionsAr(): array
	{
		return [
			self::Internal->value => 'أنشطة العمل التي يتم تنفيذها بالكامل داخل المنظمة',
			self::External->value => 'أنشطة العمل التي تتضمن أطرافًا أو مواقع خارجية',
			self::Hybrid->value => 'مزيج من أنشطة العمل الداخلية والخارجية',
		];
	}

	public static function descriptionsDa(): array
	{
		return [
			self::Internal->value => 'Arbejdsaktiviteter udført helt inden for organisationen',
			self::External->value => 'Arbejdsaktiviteter med eksterne parter eller steder',
			self::Hybrid->value => 'Kombination af interne og eksterne arbejdsaktiviteter',
		];
	}

	public static function descriptionsDe(): array
	{
		return [
			self::Internal->value => 'Arbeitsaktivitäten, die vollständig innerhalb der Organisation durchgeführt werden',
			self::External->value => 'Arbeitsaktivitäten mit externen Parteien oder Standorten',
			self::Hybrid->value => 'Kombination aus internen und externen Arbeitsaktivitäten',
		];
	}

	public static function descriptionsFr(): array
	{
		return [
			self::Internal->value => 'Activités de travail réalisées entièrement au sein de l\'organisation',
			self::External->value => 'Activités de travail impliquant des parties ou des lieux externes',
			self::Hybrid->value => 'Combinaison d\'activités de travail internes et externes',
		];
	}

	public static function descriptionsHe(): array
	{
		return [
			self::Internal->value => 'פעילויות עבודה המתבצעות לחלוטין בתוך הארגון',
			self::External->value => 'פעילויות עבודה הכרוכות בצדדים או במיקומים חיצוניים',
			self::Hybrid->value => 'שילוב של פעילויות עבודה פנימיות וחיצוניות',
		];
	}

	public static function descriptionsIt(): array
	{
		return [
			self::Internal->value => 'Attività lavorative svolte interamente all\'interno dell\'organizzazione',
			self::External->value => 'Attività lavorative che coinvolgono parti o luoghi esterni',
			self::Hybrid->value => 'Combinazione di attività lavorative interne ed esterne',
		];
	}

	public static function descriptionsJa(): array
	{
		return [
			self::Internal->value => '組織内で完全に行われる作業活動',
			self::External->value => '外部の関係者や場所を含む作業活動',
			self::Hybrid->value => '内部と外部の作業活動の組み合わせ',
		];
	}

	public static function descriptionsNl(): array
	{
		return [
			self::Internal->value => 'Werkactiviteiten die volledig binnen de organisatie worden uitgevoerd',
			self::External->value => 'Werkactiviteiten met externe partijen of locaties',
			self::Hybrid->value => 'Combinatie van interne en externe werkactiviteiten',
		];
	}

	public static function descriptionsPl(): array
	{
		return [
			self::Internal->value => 'Działania pracy wykonywane całkowicie w ramach organizacji',
			self::External->value => 'Działania pracy obejmujące strony lub lokalizacje zewnętrzne',
			self::Hybrid->value => 'Połączenie wewnętrznych i zewnętrznych działań pracy',
		];
	}

	public static function descriptionsRu(): array
	{
		return [
			self::Internal->value => 'Рабочая деятельность, осуществляемая полностью внутри организации',
			self::External->value => 'Рабочая деятельность с участием внешних сторон или мест',
			self::Hybrid->value => 'Комбинация внутренней и внешней рабочей деятельности',
		];
	}

	public static function descriptionsTr(): array
	{
		return [
			self::Internal->value => 'Tamamen organizasyon içinde gerçekleştirilen iş aktiviteleri',
			self::External->value => 'Dış tarafları veya konumları içeren iş aktiviteleri',
			self::Hybrid->value => 'İç ve dış iş aktivitelerinin birleşimi',
		];
	}

	public static function descriptionsZh(): array
	{
		return [
			self::Internal->value => '完全在组织内执行的工作活动',
			self::External->value => '涉及外部方或地点的工作活动',
			self::Hybrid->value => '内部和外部工作活动的结合',
		];
	}
}
