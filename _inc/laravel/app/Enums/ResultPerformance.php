<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ResultPerformance: int
{
	case NotConcluded = 0;
	case Satisfactory = 1;
	case Average = 2;
	case Poor = 3;
	case Excellent = 4;

	public static function normalize(string|int|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::NotConcluded;

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
		return match ($normalizedValue) {
			'0', 'notconcluded', 'notconcluded', 'notconcluded', 'nc', 'pending', 'incomplete' => self::NotConcluded,
			'1', 'satisfactory', 'satisfactory', 'good', 'adequate', 'acceptable' => self::Satisfactory,
			'2', 'average', 'average', 'medium', 'moderate', 'fair', 'standard' => self::Average,
			'3', 'poor', 'poor', 'bad', 'unsatisfactory', 'weak', 'low' => self::Poor,
			'4', 'excellent', 'excellent', 'outstanding', 'exceptional', 'superb', 'great' => self::Excellent,
			default => self::NotConcluded,
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
	 * Get the label for a specific performance level in the specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? '';
	}

	/**
	 * Get the description of what this performance level means
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

	public function getScore(): int
	{
		return match ($this) {
			self::NotConcluded => 0,
			self::Poor => 25,
			self::Average => 50,
			self::Satisfactory => 75,
			self::Excellent => 100,
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::NotConcluded => '#6b7280', // gray
			self::Poor => '#ef4444', // red
			self::Average => '#f59e0b', // amber
			self::Satisfactory => '#10b981', // green
			self::Excellent => '#3b82f6', // blue
		};
	}

	/**
	 * Get the icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::NotConcluded => 'clock',
			self::Poor => 'thumbs-down',
			self::Average => 'minus-circle',
			self::Satisfactory => 'check-circle',
			self::Excellent => 'trophy',
		};
	}

	/**
	 * Check if this performance level is positive
	 */
	public function isPositive(): bool
	{
		return in_array($this, [self::Satisfactory, self::Excellent]);
	}

	/**
	 * Check if this performance level is negative
	 */
	public function isNegative(): bool
	{
		return in_array($this, [self::Poor, self::NotConcluded]);
	}

	/**
	 * Check if this performance level is neutral/average
	 */
	public function isNeutral(): bool
	{
		return $this === self::Average;
	}

	/**
	 * Get the next higher performance level (or null if already highest)
	 */
	public function getNextHigher(): ?self
	{
		return match ($this) {
			self::NotConcluded => self::Poor,
			self::Poor => self::Average,
			self::Average => self::Satisfactory,
			self::Satisfactory => self::Excellent,
			self::Excellent => null,
		};
	}

	/**
	 * Get the previous lower performance level (or null if already lowest)
	 */
	public function getPreviousLower(): ?self
	{
		return match ($this) {
			self::Excellent => self::Satisfactory,
			self::Satisfactory => self::Average,
			self::Average => self::Poor,
			self::Poor => self::NotConcluded,
			self::NotConcluded => null,
		};
	}

	/**
	 * Convert a numeric score (0-100) to ResultPerformance
	 */
	public static function fromScore(int $score): self
	{
		return match (true) {
			$score >= 90 => self::Excellent,
			$score >= 70 => self::Satisfactory,
			$score >= 50 => self::Average,
			$score >= 30 => self::Poor,
			default => self::NotConcluded,
		};
	}

	/**
	 * Get the rating title equivalent for this performance level
	 */
	public function toRatingTitle(): RatingTitle
	{
		return match ($this) {
			self::Excellent => RatingTitle::Excellent,
			self::Satisfactory => RatingTitle::VeryGood,
			self::Average => RatingTitle::Satisfactory,
			self::Poor => RatingTitle::NeedsImprovement,
			self::NotConcluded => RatingTitle::Unsatisfactory,
		};
	}

	/**
	 * Get the order index for sorting (0 = NotConcluded, 4 = Excellent)
	 */
	public function getOrder(): int
	{
		return $this->value;
	}

	/**
	 * Get performance levels sorted from worst to best
	 */
	public static function sortedWorstToBest(): array
	{
		return [
			self::NotConcluded,
			self::Poor,
			self::Average,
			self::Satisfactory,
			self::Excellent,
		];
	}

	/**
	 * Get performance levels sorted from best to worst
	 */
	public static function sortedBestToWorst(): array
	{
		return array_reverse(self::sortedWorstToBest());
	}

	/**
	 * Convert to the original array format for backward compatibility
	 */
	public static function toOriginalArray($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$labels = self::labels($lang);
		return [
			$labels[0], // Not Concluded
			$labels[1], // Satisfactory
			$labels[2], // Average
			$labels[3], // Poor
			$labels[4], // Excellent
		];
	}


	public static function labelsEn(): array
	{
		return [
			self::NotConcluded->value => 'Not Concluded',
			self::Satisfactory->value => 'Satisfactory',
			self::Average->value => 'Average',
			self::Poor->value => 'Poor',
			self::Excellent->value => 'Excellent',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::NotConcluded->value => 'Não Concluído',
			self::Satisfactory->value => 'Satisfatório',
			self::Average->value => 'Médio',
			self::Poor->value => 'Fraco',
			self::Excellent->value => 'Excelente',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::NotConcluded->value => 'No Concluido',
			self::Satisfactory->value => 'Satisfactorio',
			self::Average->value => 'Promedio',
			self::Poor->value => 'Pobre',
			self::Excellent->value => 'Excelente',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::NotConcluded->value => 'غير مكتمل',
			self::Satisfactory->value => 'مُرضٍ',
			self::Average->value => 'متوسط',
			self::Poor->value => 'ضعيف',
			self::Excellent->value => 'ممتاز',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::NotConcluded->value => 'Ikke Konkluderet',
			self::Satisfactory->value => 'Tilfredsstillende',
			self::Average->value => 'Gennemsnitlig',
			self::Poor->value => 'Dårlig',
			self::Excellent->value => 'Fremragende',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::NotConcluded->value => 'Nicht Abgeschlossen',
			self::Satisfactory->value => 'Befriedigend',
			self::Average->value => 'Durchschnittlich',
			self::Poor->value => 'Schlecht',
			self::Excellent->value => 'Ausgezeichnet',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::NotConcluded->value => 'Non Conclu',
			self::Satisfactory->value => 'Satisfaisant',
			self::Average->value => 'Moyen',
			self::Poor->value => 'Faible',
			self::Excellent->value => 'Excellent',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::NotConcluded->value => 'לא הושלם',
			self::Satisfactory->value => 'מספק',
			self::Average->value => 'ממוצע',
			self::Poor->value => 'חלש',
			self::Excellent->value => 'מעולה',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::NotConcluded->value => 'Non Concluso',
			self::Satisfactory->value => 'Soddisfacente',
			self::Average->value => 'Medio',
			self::Poor->value => 'Scarso',
			self::Excellent->value => 'Eccellente',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::NotConcluded->value => '未完了',
			self::Satisfactory->value => '満足できる',
			self::Average->value => '平均',
			self::Poor->value => '不良',
			self::Excellent->value => '優れている',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::NotConcluded->value => 'Niet Afgerond',
			self::Satisfactory->value => 'Bevredigend',
			self::Average->value => 'Gemiddeld',
			self::Poor->value => 'Slecht',
			self::Excellent->value => 'Uitstekend',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::NotConcluded->value => 'Nie Zakończony',
			self::Satisfactory->value => 'Zadowalający',
			self::Average->value => 'Średni',
			self::Poor->value => 'Słaby',
			self::Excellent->value => 'Doskonały',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::NotConcluded->value => 'Не Завершено',
			self::Satisfactory->value => 'Удовлетворительно',
			self::Average->value => 'Средний',
			self::Poor->value => 'Плохо',
			self::Excellent->value => 'Отлично',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::NotConcluded->value => 'Sonuçlanmadı',
			self::Satisfactory->value => 'Memnun Edici',
			self::Average->value => 'Orta',
			self::Poor->value => 'Zayıf',
			self::Excellent->value => 'Mükemmel',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::NotConcluded->value => '未完成',
			self::Satisfactory->value => '满意',
			self::Average->value => '平均',
			self::Poor->value => '差',
			self::Excellent->value => '优秀',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			self::NotConcluded->value => 'Evaluation not yet completed or pending review',
			self::Poor->value => 'Below expectations, requires significant improvement',
			self::Average->value => 'Meets basic requirements but has room for improvement',
			self::Satisfactory->value => 'Meets all expectations with good performance',
			self::Excellent->value => 'Exceeds expectations and demonstrates exceptional performance',
		];
	}

	public static function descriptionsPtBr(): array
	{
		return [
			self::NotConcluded->value => 'Avaliação ainda não concluída ou pendente de revisão',
			self::Poor->value => 'Abaixo das expectativas, requer melhoria significativa',
			self::Average->value => 'Atende aos requisitos básicos, mas tem espaço para melhorias',
			self::Satisfactory->value => 'Atende todas as expectativas com bom desempenho',
			self::Excellent->value => 'Excede expectativas e demonstra desempenho excepcional',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			self::NotConcluded->value => 'Evaluación aún no completada o pendiente de revisión',
			self::Poor->value => 'Por debajo de las expectativas, requiere mejora significativa',
			self::Average->value => 'Cumple con los requisitos básicos pero tiene margen de mejora',
			self::Satisfactory->value => 'Cumple todas las expectativas con buen desempeño',
			self::Excellent->value => 'Supera las expectativas y demuestra un desempeño excepcional',
		];
	}

	public static function descriptionsAr(): array
	{
		return [
			self::NotConcluded->value => 'لم تكتمل التقييم بعد أو قيد المراجعة',
			self::Poor->value => 'أقل من التوقعات، يتطلب تحسينًا كبيرًا',
			self::Average->value => 'يلبي المتطلبات الأساسية ولكن لديه مجال للتحسين',
			self::Satisfactory->value => 'يلبي جميع التوقعات بأداء جيد',
			self::Excellent->value => 'يتجاوز التوقعات ويظهر أداءً استثنائيًا',
		];
	}

	public static function descriptionsDa(): array
	{
		return [
			self::NotConcluded->value => 'Evaluering endnu ikke afsluttet eller afventer gennemgang',
			self::Poor->value => 'Under forventningerne, kræver betydelig forbedring',
			self::Average->value => 'Opfylder grundlæggende krav, men har plads til forbedring',
			self::Satisfactory->value => 'Opfylder alle forventninger med god præstation',
			self::Excellent->value => 'Overgår forventningerne og viser exceptionel præstation',
		];
	}

	public static function descriptionsDe(): array
	{
		return [
			self::NotConcluded->value => 'Bewertung noch nicht abgeschlossen oder steht noch aus',
			self::Poor->value => 'Unter den Erwartungen, erfordert erhebliche Verbesserung',
			self::Average->value => 'Erfüllt grundlegende Anforderungen, hat aber Verbesserungspotenzial',
			self::Satisfactory->value => 'Erfüllt alle Erwartungen mit guter Leistung',
			self::Excellent->value => 'Übertrifft die Erwartungen und zeigt außergewöhnliche Leistung',
		];
	}

	public static function descriptionsFr(): array
	{
		return [
			self::NotConcluded->value => 'Évaluation pas encore terminée ou en attente de révision',
			self::Poor->value => 'Inférieur aux attentes, nécessite une amélioration significative',
			self::Average->value => 'Répond aux exigences de base mais a une marge d\'amélioration',
			self::Satisfactory->value => 'Répond à toutes les attentes avec de bonnes performances',
			self::Excellent->value => 'Dépasse les attentes et démontre des performances exceptionnelles',
		];
	}

	public static function descriptionsHe(): array
	{
		return [
			self::NotConcluded->value => 'ההערכה עדיין לא הושלמה או ממתינה לבדיקה',
			self::Poor->value => 'מתחת לציפיות, דורש שיפור משמעותי',
			self::Average->value => 'עומד בדרישות הבסיסיות אך יש מקום לשיפור',
			self::Satisfactory->value => 'עומד בכל הציפיות עם ביצועים טובים',
			self::Excellent->value => 'עולה על הציפיות ומדגים ביצועים יוצאי דופן',
		];
	}

	public static function descriptionsIt(): array
	{
		return [
			self::NotConcluded->value => 'Valutazione non ancora completata o in attesa di revisione',
			self::Poor->value => 'Al di sotto delle aspettative, richiede un miglioramento significativo',
			self::Average->value => 'Soddisfa i requisiti di base ma ha margine di miglioramento',
			self::Satisfactory->value => 'Soddisfa tutte le aspettative con buone prestazioni',
			self::Excellent->value => 'Supera le aspettative e dimostra prestazioni eccezionali',
		];
	}

	public static function descriptionsJa(): array
	{
		return [
			self::NotConcluded->value => '評価がまだ完了していない、またはレビュー待ち',
			self::Poor->value => '期待以下、大幅な改善が必要',
			self::Average->value => '基本的な要件を満たしているが改善の余地がある',
			self::Satisfactory->value => 'すべての期待を良いパフォーマンスで満たす',
			self::Excellent->value => '期待を超え、卓越したパフォーマンスを示す',
		];
	}

	public static function descriptionsNl(): array
	{
		return [
			self::NotConcluded->value => 'Evaluatie nog niet voltooid of in afwachting van beoordeling',
			self::Poor->value => 'Onder de verwachtingen, vereist aanzienlijke verbetering',
			self::Average->value => 'Voldoet aan basisvereisten maar heeft ruimte voor verbetering',
			self::Satisfactory->value => 'Voldoet aan alle verwachtingen met goede prestaties',
			self::Excellent->value => 'Overtreft verwachtingen en toont uitzonderlijke prestaties',
		];
	}

	public static function descriptionsPl(): array
	{
		return [
			self::NotConcluded->value => 'Ocena jeszcze nie zakończona lub oczekuje na przegląd',
			self::Poor->value => 'Poniżej oczekiwań, wymaga znacznej poprawy',
			self::Average->value => 'Spełnia podstawowe wymagania, ale ma pole do poprawy',
			self::Satisfactory->value => 'Spełnia wszystkie oczekiwania z dobrą wydajnością',
			self::Excellent->value => 'Przewyższa oczekiwania i wykazuje wyjątkowe osiągnięcia',
		];
	}

	public static function descriptionsRu(): array
	{
		return [
			self::NotConcluded->value => 'Оценка еще не завершена или ожидает проверки',
			self::Poor->value => 'Ниже ожиданий, требует значительного улучшения',
			self::Average->value => 'Соответствует базовым требованиям, но есть возможности для улучшения',
			self::Satisfactory->value => 'Соответствует всем ожиданиям с хорошей производительностью',
			self::Excellent->value => 'Превышает ожидания и демонстрирует исключительную производительность',
		];
	}

	public static function descriptionsTr(): array
	{
		return [
			self::NotConcluded->value => 'Değerlendirme henüz tamamlanmadı veya inceleme bekliyor',
			self::Poor->value => 'Beklentilerin altında, önemli iyileştirme gerektirir',
			self::Average->value => 'Temel gereksinimleri karşılar ancak iyileştirme alanı vardır',
			self::Satisfactory->value => 'Tüm beklentileri iyi performansla karşılar',
			self::Excellent->value => 'Beklentileri aşar ve olağanüstü performans sergiler',
		];
	}

	public static function descriptionsZh(): array
	{
		return [
			self::NotConcluded->value => '评估尚未完成或待审核',
			self::Poor->value => '低于预期，需要显著改进',
			self::Average->value => '满足基本要求，但有改进空间',
			self::Satisfactory->value => '以良好表现满足所有期望',
			self::Excellent->value => '超出预期并表现出卓越表现',
		];
	}

	/**
	 * Get the numeric score (0-100) for this performance level
	 */
}
