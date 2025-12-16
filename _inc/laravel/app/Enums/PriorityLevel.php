<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PriorityLevel: string
{
	case None         = 'none';
	case Low          = 'low';
	case Medium       = 'medium';
	case High         = 'high';
	case Critical     = 'critical';
	case Urgent       = 'urgent';
	case Blocker      = 'blocker';
	case Immediate    = 'immediate';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Medium; // Default to Medium priority

		$normalizedValue = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($value ?? '')));
		return match ($normalizedValue) {
			// None variations
			'none', 'na', 'notapplicable', 'unassigned', 'unprioritized', 'undefined' => self::None,

			// Low variations
			'low', 'lowest', 'minor', 'trivial', 'deferred', 'someday' => self::Low,

			// Medium variations
			'medium', 'normal', 'standard', 'moderate', 'regular', 'default' => self::Medium,

			// High variations
			'high', 'important', 'significant', 'major', 'elevated' => self::High,

			// Critical variations
			'critical', 'criticalpath', 'essential', 'vital', 'musthave' => self::Critical,

			// Urgent variations
			'urgent', 'asap', 'rush', 'expedite', 'timely', 'timecritical' => self::Urgent,

			// Blocker variations
			'blocker', 'blocking', 'showstopper', 'impediment', 'dependency' => self::Blocker,

			// Immediate variations
			'immediate', 'now', 'emergency', 'stat', 'drop everything', 'top' => self::Immediate,

			default => self::Medium,
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
			self::None->value      => 'Nenhuma',
			self::Low->value       => 'Baixa',
			self::Medium->value    => 'Média',
			self::High->value      => 'Alta',
			self::Critical->value  => 'Crítica',
			self::Urgent->value    => 'Urgente',
			self::Blocker->value   => 'Bloqueadora',
			self::Immediate->value => 'Imediata',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::None->value      => 'None',
			self::Low->value       => 'Low',
			self::Medium->value    => 'Medium',
			self::High->value      => 'High',
			self::Critical->value  => 'Critical',
			self::Urgent->value    => 'Urgent',
			self::Blocker->value   => 'Blocker',
			self::Immediate->value => 'Immediate',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::None->value      => 'Ninguna',
			self::Low->value       => 'Baja',
			self::Medium->value    => 'Media',
			self::High->value      => 'Alta',
			self::Critical->value  => 'Crítica',
			self::Urgent->value    => 'Urgente',
			self::Blocker->value   => 'Bloqueante',
			self::Immediate->value => 'Inmediata',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::None->value      => 'بدون',
			self::Low->value       => 'منخفضة',
			self::Medium->value    => 'متوسطة',
			self::High->value      => 'عالية',
			self::Critical->value  => 'حرجة',
			self::Urgent->value    => 'عاجلة',
			self::Blocker->value   => 'معيقة',
			self::Immediate->value => 'فورية',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::None->value      => 'Ingen',
			self::Low->value       => 'Lav',
			self::Medium->value    => 'Medium',
			self::High->value      => 'Høj',
			self::Critical->value  => 'Kritisk',
			self::Urgent->value    => 'Haster',
			self::Blocker->value   => 'Blokerende',
			self::Immediate->value => 'Umiddelbar',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::None->value      => 'Keine',
			self::Low->value       => 'Niedrig',
			self::Medium->value    => 'Mittel',
			self::High->value      => 'Hoch',
			self::Critical->value  => 'Kritisch',
			self::Urgent->value    => 'Dringend',
			self::Blocker->value   => 'Blockierend',
			self::Immediate->value => 'Sofort',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::None->value      => 'Aucune',
			self::Low->value       => 'Basse',
			self::Medium->value    => 'Moyenne',
			self::High->value      => 'Haute',
			self::Critical->value  => 'Critique',
			self::Urgent->value    => 'Urgente',
			self::Blocker->value   => 'Bloquante',
			self::Immediate->value => 'Immédiate',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::None->value      => 'ללא',
			self::Low->value       => 'נמוכה',
			self::Medium->value    => 'בינונית',
			self::High->value      => 'גבוהה',
			self::Critical->value  => 'קריטית',
			self::Urgent->value    => 'דחופה',
			self::Blocker->value   => 'חוסמת',
			self::Immediate->value => 'מיידית',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::None->value      => 'Nessuna',
			self::Low->value       => 'Bassa',
			self::Medium->value    => 'Media',
			self::High->value      => 'Alta',
			self::Critical->value  => 'Critica',
			self::Urgent->value    => 'Urgente',
			self::Blocker->value   => 'Bloccante',
			self::Immediate->value => 'Immediata',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::None->value      => 'なし',
			self::Low->value       => '低',
			self::Medium->value    => '中',
			self::High->value      => '高',
			self::Critical->value  => '緊急',
			self::Urgent->value    => '至急',
			self::Blocker->value   => 'ブロッカー',
			self::Immediate->value => '即時',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::None->value      => 'Geen',
			self::Low->value       => 'Laag',
			self::Medium->value    => 'Gemiddeld',
			self::High->value      => 'Hoog',
			self::Critical->value  => 'Kritisch',
			self::Urgent->value    => 'Dringend',
			self::Blocker->value   => 'Blokkerend',
			self::Immediate->value => 'Onmiddellijk',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::None->value      => 'Brak',
			self::Low->value       => 'Niska',
			self::Medium->value    => 'Średnia',
			self::High->value      => 'Wysoka',
			self::Critical->value  => 'Krytyczna',
			self::Urgent->value    => 'Pilna',
			self::Blocker->value   => 'Blokująca',
			self::Immediate->value => 'Natychmiastowa',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::None->value      => 'Без',
			self::Low->value       => 'Низкий',
			self::Medium->value    => 'Средний',
			self::High->value      => 'Высокий',
			self::Critical->value  => 'Критический',
			self::Urgent->value    => 'Срочный',
			self::Blocker->value   => 'Блокирующий',
			self::Immediate->value => 'Немедленный',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::None->value      => 'Yok',
			self::Low->value       => 'Düşük',
			self::Medium->value    => 'Orta',
			self::High->value      => 'Yüksek',
			self::Critical->value  => 'Kritik',
			self::Urgent->value    => 'Acil',
			self::Blocker->value   => 'Engelleyici',
			self::Immediate->value => 'Anında',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::None->value      => '无',
			self::Low->value       => '低',
			self::Medium->value    => '中',
			self::High->value      => '高',
			self::Critical->value  => '严重',
			self::Urgent->value    => '紧急',
			self::Blocker->value   => '阻碍',
			self::Immediate->value => '立即',
		];
	}

	/**
	 * Get CSS color classes for each priority level (for UI implementation)
	 */
	public static function colorClasses(): array
	{
		return [
			self::None->value      => 'priority-none',
			self::Low->value       => 'priority-low',
			self::Medium->value    => 'priority-medium',
			self::High->value      => 'priority-high',
			self::Critical->value  => 'priority-critical',
			self::Urgent->value    => 'priority-urgent',
			self::Blocker->value   => 'priority-blocker',
			self::Immediate->value => 'priority-immediate',
		];
	}

	/**
	 * Get icon names for each priority level (for UI implementation)
	 */
	public static function icons(): array
	{
		return [
			self::None->value      => 'flag-outline',
			self::Low->value       => 'flag',
			self::Medium->value    => 'flag',
			self::High->value      => 'flag',
			self::Critical->value  => 'alert-circle',
			self::Urgent->value    => 'alert',
			self::Blocker->value   => 'block-helper',
			self::Immediate->value => 'flash',
		];
	}

	/**
	 * Get numerical weight for sorting/comparison purposes
	 */
	public function weight(): int
	{
		return match ($this) {
			self::None      => 0,
			self::Low       => 1,
			self::Medium    => 2,
			self::High      => 3,
			self::Urgent    => 4,
			self::Critical  => 5,
			self::Blocker   => 6,
			self::Immediate => 7,
		};
	}

	/**
	 * Check if priority is above a certain threshold
	 */
	public function isAtLeast(self $threshold): bool
	{
		return $this->weight() >= $threshold->weight();
	}

	/**
	 * Get priorities grouped by severity for UI selection
	 */
	public static function groupedOptions(): array
	{
		return [
			'standard' => [
				self::Low,
				self::Medium,
				self::High,
			],
			'elevated' => [
				self::Critical,
				self::Urgent,
			],
			'exceptional' => [
				self::Blocker,
				self::Immediate,
			],
			'unprioritized' => [
				self::None,
			],
		];
	}
}
