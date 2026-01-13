<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum MeetingType: string
{
	// Meeting types
	case Instant = 'instant';
	case Scheduled = 'scheduled';
	case RecurringFixed = 'recurring_fixed';
	case RecurringNoFixed = 'recurring_no_fixed';
	case Webinar = 'webinar';
	case PersonalRoom = 'personal_room';

	/**
	 * Normalize input to MeetingType
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
			// Instant
			'instant', 'now', 'immediate', 'quick', 'adhoc',
			'spontaneous', 'onnow', 'startnow' => self::Instant,

			// Scheduled
			'scheduled', 'planned', 'booked', 'appointment',
			'reserved', 'future', 'upcoming' => self::Scheduled,

			// Recurring fixed
			'recurringfixed', 'recurring', 'repeating', 'regular',
			'weekly', 'daily', 'monthly', 'fixedrecurring' => self::RecurringFixed,

			// Recurring no fixed
			'recurringnofixed', 'flexiblerecurring', 'recurringflexible',
			'variablerecurring', 'recurringvariable' => self::RecurringNoFixed,

			// Webinar
			'webinar', 'onlineevent', 'virtualevent', 'seminar',
			'workshop', 'training', 'conference' => self::Webinar,

			// Personal room
			'personalroom', 'personal', 'myroom', 'privateroom',
			'dedicatedroom', 'permanentroom' => self::PersonalRoom,

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
	 * Get label for this meeting type in specified language
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
			self::Instant => '#ef4444', // Red - urgent/now
			self::Scheduled => '#3b82f6', // Blue - planned
			self::RecurringFixed => '#10b981', // Green - regular/recurring
			self::RecurringNoFixed => '#8b5cf6', // Purple - flexible recurring
			self::Webinar => '#f59e0b', // Yellow/amber - event/webinar
			self::PersonalRoom => '#6366f1', // Indigo - personal/private
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::Instant => 'bolt',
			self::Scheduled => 'calendar-alt',
			self::RecurringFixed => 'redo-alt',
			self::RecurringNoFixed => 'sync-alt',
			self::Webinar => 'video',
			self::PersonalRoom => 'home',
		};
	}

	/**
	 * Check if meeting type is recurring
	 */
	public function isRecurring(): bool
	{
		return in_array($this, [
			self::RecurringFixed,
			self::RecurringNoFixed,
		]);
	}

	/**
	 * Check if meeting type is scheduled in advance
	 */
	public function isScheduled(): bool
	{
		return in_array($this, [
			self::Scheduled,
			self::RecurringFixed,
			self::RecurringNoFixed,
			self::Webinar,
		]);
	}

	/**
	 * Check if meeting type is immediate/instant
	 */
	public function isInstant(): bool
	{
		return $this === self::Instant;
	}

	/**
	 * Check if meeting type is a webinar/large event
	 */
	public function isWebinar(): bool
	{
		return $this === self::Webinar;
	}

	/**
	 * Check if meeting type has a personal/dedicated room
	 */
	public function hasPersonalRoom(): bool
	{
		return $this === self::PersonalRoom;
	}

	/**
	 * Check if meeting type has fixed schedule
	 */
	public function hasFixedSchedule(): bool
	{
		return in_array($this, [
			self::Scheduled,
			self::RecurringFixed,
			self::Webinar,
		]);
	}

	/**
	 * Get meeting type category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			self::Instant => 'instant',
			self::Scheduled => 'one_time',
			self::RecurringFixed => 'recurring_fixed',
			self::RecurringNoFixed => 'recurring_flexible',
			self::Webinar => 'event',
			self::PersonalRoom => 'personal',
		};
	}

	/**
	 * Get description of the meeting type
	 */
	public function getDescription(): string
	{
		return match ($this) {
			self::Instant => 'Start an immediate meeting right now',
			self::Scheduled => 'Schedule a meeting for a specific date and time',
			self::RecurringFixed => 'Create recurring meetings with fixed intervals',
			self::RecurringNoFixed => 'Create recurring meetings with flexible scheduling',
			self::Webinar => 'Host a webinar or large online event',
			self::PersonalRoom => 'Access your personal meeting room',
		};
	}

	/**
	 * Check if meeting type supports multiple participants
	 */
	public function supportsMultipleParticipants(): bool
	{
		return true; // All meeting types support multiple participants
	}

	/**
	 * Check if meeting type supports recording
	 */
	public function supportsRecording(): bool
	{
		return true; // Most meeting types support recording
	}

	/**
	 * Get typical use case for this meeting type
	 */
	public function getTypicalUseCase(): string
	{
		return match ($this) {
			self::Instant => 'Quick team huddles, urgent discussions',
			self::Scheduled => 'Client meetings, interviews, one-time events',
			self::RecurringFixed => 'Weekly team meetings, stand-ups, regular check-ins',
			self::RecurringNoFixed => 'Flexible team meetings, on-demand training',
			self::Webinar => 'Large audiences, public events, training sessions',
			self::PersonalRoom => 'Personal office hours, client consultations',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Instant->value => 'Instant Meeting',
			self::Scheduled->value => 'Scheduled Meeting',
			self::RecurringFixed->value => 'Recurring Meeting (Fixed)',
			self::RecurringNoFixed->value => 'Recurring Meeting (Flexible)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Personal Room',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Instant->value => 'Reunião Instantânea',
			self::Scheduled->value => 'Reunião Agendada',
			self::RecurringFixed->value => 'Reunião Recorrente (Fixa)',
			self::RecurringNoFixed->value => 'Reunião Recorrente (Flexível)',
			self::Webinar->value => 'Webinário',
			self::PersonalRoom->value => 'Sala Pessoal',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Instant->value => 'Reunión Instantánea',
			self::Scheduled->value => 'Reunión Programada',
			self::RecurringFixed->value => 'Reunión Recurrente (Fija)',
			self::RecurringNoFixed->value => 'Reunión Recurrente (Flexible)',
			self::Webinar->value => 'Seminario Web',
			self::PersonalRoom->value => 'Sala Personal',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Instant->value => 'Réunion Instantanée',
			self::Scheduled->value => 'Réunion Planifiée',
			self::RecurringFixed->value => 'Réunion Récurrente (Fixée)',
			self::RecurringNoFixed->value => 'Réunion Récurrente (Flexible)',
			self::Webinar->value => 'Webinaire',
			self::PersonalRoom->value => 'Salle Personnelle',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Instant->value => 'Sofort-Meeting',
			self::Scheduled->value => 'Geplantes Meeting',
			self::RecurringFixed->value => 'Wiederkehrendes Meeting (Fest)',
			self::RecurringNoFixed->value => 'Wiederkehrendes Meeting (Flexibel)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Persönlicher Raum',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Instant->value => 'Riunione Istantanea',
			self::Scheduled->value => 'Riunione Programmata',
			self::RecurringFixed->value => 'Riunione Ricorrente (Fissa)',
			self::RecurringNoFixed->value => 'Riunione Ricorrente (Flessibile)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Sala Personale',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Instant->value => 'Directe Vergadering',
			self::Scheduled->value => 'Geplande Vergadering',
			self::RecurringFixed->value => 'Terugkerende Vergadering (Vast)',
			self::RecurringNoFixed->value => 'Terugkerende Vergadering (Flexibel)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Persoonlijke Kamer',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Instant->value => 'Natychmiastowe Spotkanie',
			self::Scheduled->value => 'Zaplanowane Spotkanie',
			self::RecurringFixed->value => 'Powtarzające Się Spotkanie (Stałe)',
			self::RecurringNoFixed->value => 'Powtarzające Się Spotkanie (Elastyczne)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Pokój Osobisty',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Instant->value => 'Мгновенная Встреча',
			self::Scheduled->value => 'Запланированная Встреча',
			self::RecurringFixed->value => 'Повторяющаяся Встреча (Фиксированная)',
			self::RecurringNoFixed->value => 'Повторяющаяся Встреча (Гибкая)',
			self::Webinar->value => 'Вебинар',
			self::PersonalRoom->value => 'Личная Комната',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Instant->value => 'Anlık Toplantı',
			self::Scheduled->value => 'Planlanmış Toplantı',
			self::RecurringFixed->value => 'Tekrarlanan Toplantı (Sabit)',
			self::RecurringNoFixed->value => 'Tekrarlanan Toplantı (Esnek)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Kişisel Oda',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Instant->value => 'اجتماع فوري',
			self::Scheduled->value => 'اجتماع مجدول',
			self::RecurringFixed->value => 'اجتماع متكرر (ثابت)',
			self::RecurringNoFixed->value => 'اجتماع متكرر (مرن)',
			self::Webinar->value => 'ندوة عبر الإنترنت',
			self::PersonalRoom->value => 'غرفة شخصية',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Instant->value => 'פגישה מיידית',
			self::Scheduled->value => 'פגישה מתוזמנת',
			self::RecurringFixed->value => 'פגישה חוזרת (קבועה)',
			self::RecurringNoFixed->value => 'פגישה חוזרת (גמישה)',
			self::Webinar->value => 'וובינר',
			self::PersonalRoom->value => 'חדר אישי',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Instant->value => 'インスタントミーティング',
			self::Scheduled->value => 'スケジュール済みミーティング',
			self::RecurringFixed->value => '定期的なミーティング (固定)',
			self::RecurringNoFixed->value => '定期的なミーティング (柔軟)',
			self::Webinar->value => 'ウェビナー',
			self::PersonalRoom->value => '個人ルーム',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Instant->value => 'Øjeblikkeligt Møde',
			self::Scheduled->value => 'Planlagt Møde',
			self::RecurringFixed->value => 'Tilbagevendende Møde (Fast)',
			self::RecurringNoFixed->value => 'Tilbagevendende Møde (Fleksibel)',
			self::Webinar->value => 'Webinar',
			self::PersonalRoom->value => 'Personligt Rum',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::Instant->value => '即时会议',
			self::Scheduled->value => '预定会议',
			self::RecurringFixed->value => '定期会议 (固定)',
			self::RecurringNoFixed->value => '定期会议 (灵活)',
			self::Webinar->value => '网络研讨会',
			self::PersonalRoom->value => '个人会议室',
		];
	}
}
