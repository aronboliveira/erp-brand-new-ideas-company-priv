<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum RatingTitle: int
{
	case Unsatisfactory = 1;
	case NeedsImprovement = 2;
	case Satisfactory = 3;
	case VeryGood = 4;
	case Excellent = 5;

	public static function normalize(string|int|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Satisfactory;

		$normalizedValue = preg_replace('/[^0-9]/', '', (string) $value);
		return match ((int) $normalizedValue) {
			5 => self::Excellent,
			4 => self::VeryGood,
			3 => self::Satisfactory,
			2 => self::NeedsImprovement,
			1 => self::Unsatisfactory,
			default => self::Satisfactory,
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

	public static function labelsEn(): array
	{
		return [
			self::Excellent->value => 'Excellent - 5 stars',
			self::VeryGood->value => 'Very good - 4 stars',
			self::Satisfactory->value => 'Satisfactory - 3 stars',
			self::NeedsImprovement->value => 'Needs improvement - 2 stars',
			self::Unsatisfactory->value => 'Unsatisfactory - 1 star',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Excellent->value => 'Excelente - 5 estrelas',
			self::VeryGood->value => 'Muito bom - 4 estrelas',
			self::Satisfactory->value => 'Satisfatório - 3 estrelas',
			self::NeedsImprovement->value => 'Precisa melhorar - 2 estrelas',
			self::Unsatisfactory->value => 'Insatisfatório - 1 estrela',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Excellent->value => 'Excelente - 5 estrellas',
			self::VeryGood->value => 'Muy bueno - 4 estrellas',
			self::Satisfactory->value => 'Satisfactorio - 3 estrellas',
			self::NeedsImprovement->value => 'Necesita mejorar - 2 estrellas',
			self::Unsatisfactory->value => 'Insatisfactorio - 1 estrella',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Excellent->value => 'ممتاز - 5 نجوم',
			self::VeryGood->value => 'جيد جداً - 4 نجوم',
			self::Satisfactory->value => 'مقبول - 3 نجوم',
			self::NeedsImprovement->value => 'يحتاج تحسين - نجمتان',
			self::Unsatisfactory->value => 'غير مقبول - نجمة واحدة',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Excellent->value => 'Fremragende - 5 stjerner',
			self::VeryGood->value => 'Meget godt - 4 stjerner',
			self::Satisfactory->value => 'Tilfredsstillende - 3 stjerner',
			self::NeedsImprovement->value => 'Kræver forbedring - 2 stjerner',
			self::Unsatisfactory->value => 'Utilfredsstillende - 1 stjerne',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Excellent->value => 'Ausgezeichnet - 5 Sterne',
			self::VeryGood->value => 'Sehr gut - 4 Sterne',
			self::Satisfactory->value => 'Zufriedenstellend - 3 Sterne',
			self::NeedsImprovement->value => 'Muss verbessert werden - 2 Sterne',
			self::Unsatisfactory->value => 'Unbefriedigend - 1 Stern',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Excellent->value => 'Excellent - 5 étoiles',
			self::VeryGood->value => 'Très bien - 4 étoiles',
			self::Satisfactory->value => 'Satisfaisant - 3 étoiles',
			self::NeedsImprovement->value => 'À améliorer - 2 étoiles',
			self::Unsatisfactory->value => 'Insatisfaisant - 1 étoile',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Excellent->value => 'מצוין - 5 כוכבים',
			self::VeryGood->value => 'טוב מאוד - 4 כוכבים',
			self::Satisfactory->value => 'מספק - 3 כוכבים',
			self::NeedsImprovement->value => 'זקוק לשיפור - 2 כוכבים',
			self::Unsatisfactory->value => 'לא מספק - כוכב אחד',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Excellent->value => 'Eccellente - 5 stelle',
			self::VeryGood->value => 'Molto buono - 4 stelle',
			self::Satisfactory->value => 'Soddisfacente - 3 stelle',
			self::NeedsImprovement->value => 'Da migliorare - 2 stelle',
			self::Unsatisfactory->value => 'Insoddisfacente - 1 stella',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Excellent->value => '優れている - 5つ星',
			self::VeryGood->value => '非常に良い - 4つ星',
			self::Satisfactory->value => '満足できる - 3つ星',
			self::NeedsImprovement->value => '改善が必要 - 2つ星',
			self::Unsatisfactory->value => '不満足 - 1つ星',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Excellent->value => 'Uitstekend - 5 sterren',
			self::VeryGood->value => 'Zeer goed - 4 sterren',
			self::Satisfactory->value => 'Bevredigend - 3 sterren',
			self::NeedsImprovement->value => 'Moet verbeterd worden - 2 sterren',
			self::Unsatisfactory->value => 'Onbevredigend - 1 ster',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Excellent->value => 'Doskonałe - 5 gwiazdek',
			self::VeryGood->value => 'Bardzo dobre - 4 gwiazdki',
			self::Satisfactory->value => 'Zadowalające - 3 gwiazdki',
			self::NeedsImprovement->value => 'Wymaga poprawy - 2 gwiazdki',
			self::Unsatisfactory->value => 'Niezadowalające - 1 gwiazdka',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Excellent->value => 'Отлично - 5 звезд',
			self::VeryGood->value => 'Очень хорошо - 4 звезды',
			self::Satisfactory->value => 'Удовлетворительно - 3 звезды',
			self::NeedsImprovement->value => 'Требует улучшения - 2 звезды',
			self::Unsatisfactory->value => 'Неудовлетворительно - 1 звезда',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Excellent->value => 'Mükemmel - 5 yıldız',
			self::VeryGood->value => 'Çok iyi - 4 yıldız',
			self::Satisfactory->value => 'Memnun edici - 3 yıldız',
			self::NeedsImprovement->value => 'Geliştirilmeli - 2 yıldız',
			self::Unsatisfactory->value => 'Memnuniyetsiz - 1 yıldız',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Excellent->value => '优秀 - 5星',
			self::VeryGood->value => '非常好 - 4星',
			self::Satisfactory->value => '满意 - 3星',
			self::NeedsImprovement->value => '需要改进 - 2星',
			self::Unsatisfactory->value => '不满意 - 1星',
		];
	}

	/**
	 * Get the label for a specific rating in the specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? '';
	}

	/**
	 * Get all labels in descending order (5 to 1) for the specified language
	 */
	public static function labelsDescending($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$labels = self::labels($lang);
		return [
			5 => $labels[5] ?? '',
			4 => $labels[4] ?? '',
			3 => $labels[3] ?? '',
			2 => $labels[2] ?? '',
			1 => $labels[1] ?? '',
		];
	}

	/**
	 * Get the emoji/stars representation of the rating
	 */
	public function getStars(): string
	{
		return match ($this) {
			self::Excellent => '★★★★★',
			self::VeryGood => '★★★★☆',
			self::Satisfactory => '★★★☆☆',
			self::NeedsImprovement => '★★☆☆☆',
			self::Unsatisfactory => '★☆☆☆☆',
		};
	}

	/**
	 * Get the color associated with the rating (for UI display)
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::Excellent => '#10b981', // green
			self::VeryGood => '#34d399', // light green
			self::Satisfactory => '#fbbf24', // yellow
			self::NeedsImprovement => '#f97316', // orange
			self::Unsatisfactory => '#ef4444', // red
		};
	}

	/**
	 * Check if the rating is positive (4+ stars)
	 */
	public function isPositive(): bool
	{
		return $this->value >= 4;
	}

	/**
	 * Check if the rating is negative (2 stars or less)
	 */
	public function isNegative(): bool
	{
		return $this->value <= 2;
	}

	/**
	 * Check if the rating is neutral (3 stars)
	 */
	public function isNeutral(): bool
	{
		return $this->value === 3;
	}

	/**
	 * Get the text description without the star count
	 */
	public function getDescription($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$label = $this->label($lang);
		// Remove the star count part (e.g., " - 5 stars")
		return preg_replace('/\s*-\s*\d+\s*(star|estrela|estrella|نجوم?|stjerne|Stern|étoile|כוכב|stelle|つ星|sterren|gwiazdek|звезд|yıldız|星)s?/i', '', $label);
	}

	/**
	 * Get the minimum threshold for this rating category
	 */
	public function getMinThreshold(): int
	{
		return match ($this) {
			self::Excellent => 90,
			self::VeryGood => 75,
			self::Satisfactory => 60,
			self::NeedsImprovement => 40,
			self::Unsatisfactory => 0,
		};
	}

	/**
	 * Convert a numeric score (0-100) to a RatingTitle
	 */
	public static function fromScore(int $score): self
	{
		return match (true) {
			$score >= 90 => self::Excellent,
			$score >= 75 => self::VeryGood,
			$score >= 60 => self::Satisfactory,
			$score >= 40 => self::NeedsImprovement,
			default => self::Unsatisfactory,
		};
	}

	/**
	 * Get the average numeric value for this rating
	 */
	public function getAverageValue(): float
	{
		return match ($this) {
			self::Excellent => 4.75,
			self::VeryGood => 4.25,
			self::Satisfactory => 3.0,
			self::NeedsImprovement => 2.0,
			self::Unsatisfactory => 1.0,
		};
	}

	/**
	 * Get the next higher rating (or null if already highest)
	 */
	public function getNextHigher(): ?self
	{
		return match ($this) {
			self::Unsatisfactory => self::NeedsImprovement,
			self::NeedsImprovement => self::Satisfactory,
			self::Satisfactory => self::VeryGood,
			self::VeryGood => self::Excellent,
			self::Excellent => null,
		};
	}

	/**
	 * Get the next lower rating (or null if already lowest)
	 */
	public function getNextLower(): ?self
	{
		return match ($this) {
			self::Excellent => self::VeryGood,
			self::VeryGood => self::Satisfactory,
			self::Satisfactory => self::NeedsImprovement,
			self::NeedsImprovement => self::Unsatisfactory,
			self::Unsatisfactory => null,
		};
	}
}
