<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum WorkShift: string
{
	case FullTime = 'full_time';
	case PartTime = 'part_time';
	case Mixed = 'mixed';
	case Rotating = 'rotating';
	case Night = 'night';
	case Evening = 'evening';
	case Day = 'day';
	case Split = 'split';
	case OnCall = 'on_call';
	case Flexible = 'flexible';
	case Compressed = 'compressed';
	case Seasonal = 'seasonal';
	case Internship = 'internship';
	case Freelance = 'freelance';
	case Temporary = 'temporary';
	case Contract = 'contract';
	case Casual = 'casual';
	case ZeroHours = 'zero_hours';
	case ProjectBased = 'project_based';
	case ShiftWork = 'shift_work';
	case FixedShift = 'fixed_shift';
	case SwingShift = 'swing_shift';
	case Graveyard = 'graveyard';
	case Afternoon = 'afternoon';
	case Morning = 'morning';
	case TwentyFourSeven = '24_7';
	case FourDayWeek = '4_day_week';
	case NineDayFortnight = '9_day_fortnight';
	case ReducedHours = 'reduced_hours';
	case JobShare = 'job_share';
	case TermTime = 'term_time';
	case Annualized = 'annualized';
	case BankHours = 'bank_hours';
	case OnDemand = 'on_demand';
	case PeakSeason = 'peak_season';

	/**
	 * Normalize input to WorkShift
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
			// Full-time arrangements
			'fulltime', 'full', 'tempointegral', 'jornadacompleta' => self::FullTime,
			'40hours', '40h', 'eightfive', '9to5' => self::FullTime,

			// Part-time arrangements
			'parttime', 'part', 'meioperiodo', 'mediajornada' => self::PartTime,
			'halftime', 'half', '20hours', '20h' => self::PartTime,
			'reducedhours', 'reduced', 'jornadareduzida' => self::ReducedHours,

			// Alternative schedules
			'4dayweek', '4days', 'fourday', 'semana4dias' => self::FourDayWeek,
			'9dayfortnight', '9days', 'nineday' => self::NineDayFortnight,
			'compressed', 'compressedschedule', 'jornadacomprimida' => self::Compressed,
			'flexible', 'flex', 'flexível', 'flexitime' => self::Flexible,

			// Time-specific shifts
			'day', 'daytime', 'diurno', 'matutino' => self::Day,
			'morning', 'manhã', 'morning', 'madrugada' => self::Morning,
			'afternoon', 'tarde', 'vespertino' => self::Afternoon,
			'evening', 'noite', 'eveningshift' => self::Evening,
			'night', 'nocturno', 'nightshift', 'noturno' => self::Night,
			'graveyard', 'graveyardshift', 'madrugada' => self::Graveyard,
			'swing', 'swingshift', 'turnointegral' => self::SwingShift,

			// Rotating/Mixed patterns
			'rotating', 'rotation', 'rotativo', 'rotacional' => self::Rotating,
			'mixed', 'varied', 'variado', 'mixto' => self::Mixed,
			'split', 'splitshift', 'turnodividido' => self::Split,
			'shiftwork', 'shifts', 'turnos' => self::ShiftWork,
			'fixedshift', 'fixed', 'turnofijo' => self::FixedShift,

			// On-call arrangements
			'oncall', 'oncallduty', 'disponibilidade' => self::OnCall,
			'zerohours', 'zerohour', 'horaszero' => self::ZeroHours,
			'ondemand', 'demandbased', 'sob demanda' => self::OnDemand,
			'bankhours', 'hourbank', 'bancohoras' => self::BankHours,

			// Contract types
			'temporary', 'temp', 'temporário', 'temporal' => self::Temporary,
			'contract', 'contrato', 'contractual' => self::Contract,
			'casual', 'casualwork', 'ocasional' => self::Casual,
			'freelance', 'freelancer', 'autônomo' => self::Freelance,
			'internship', 'intern', 'estágio', 'pasantía' => self::Internship,

			// Project/Seasonal arrangements
			'projectbased', 'project', 'projeto' => self::ProjectBased,
			'seasonal', 'season', 'sazonal', 'temporada' => self::Seasonal,
			'peakseason', 'peak', 'alta temporada' => self::PeakSeason,
			'termtime', 'term', 'escolar' => self::TermTime,

			// Special arrangements
			'jobshare', 'jobsharing', 'compartilhamento' => self::JobShare,
			'annualized', 'annualizedhours', 'horasanualizadas' => self::Annualized,
			'247', '24x7', 'alwayson', 'continuous' => self::TwentyFourSeven,

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
	 * Get label for this work shift in specified language
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
			// Full-time arrangements - blue
			self::FullTime, self::FixedShift, self::Contract,
			self::TwentyFourSeven => '#3b82f6',

			// Part-time arrangements - green
			self::PartTime, self::ReducedHours, self::FourDayWeek,
			self::NineDayFortnight, self::JobShare, self::TermTime => '#10b981',

			// Flexible/Alternative - purple
			self::Flexible, self::Compressed, self::Annualized,
			self::BankHours, self::OnDemand => '#8b5cf6',

			// Time-specific shifts - amber/orange
			self::Day, self::Morning, self::Afternoon,
			self::Evening, self::Night, self::Graveyard,
			self::SwingShift, self::Split => '#f59e0b',

			// Rotating/Mixed - indigo
			self::Rotating, self::Mixed, self::ShiftWork => '#6366f1',

			// Contract/Temporary - gray
			self::Temporary, self::Casual, self::Seasonal,
			self::PeakSeason, self::ZeroHours => '#6b7280',

			// Special arrangements - teal
			self::Freelance, self::ProjectBased, self::OnCall,
			self::Internship => '#14b8a6',

			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Full-time
			self::FullTime, self::FixedShift => 'business-time',
			self::Contract => 'file-contract',
			self::TwentyFourSeven => 'clock',

			// Part-time
			self::PartTime, self::ReducedHours => 'clock-half',
			self::FourDayWeek => 'calendar-minus',
			self::NineDayFortnight => 'calendar-week',
			self::JobShare => 'users',

			// Flexible
			self::Flexible, self::Compressed => 'arrows-alt-h',
			self::Annualized, self::BankHours => 'calendar-alt',

			// Time-specific
			self::Day, self::Morning => 'sun',
			self::Afternoon => 'cloud-sun',
			self::Evening => 'moon',
			self::Night, self::Graveyard => 'moon-stars',
			self::SwingShift => 'exchange-alt',
			self::Split => 'cut',

			// Rotating
			self::Rotating, self::Mixed => 'sync',
			self::ShiftWork => 'users-cog',

			// Temporary/Seasonal
			self::Temporary, self::Seasonal, self::PeakSeason => 'calendar',
			self::Casual, self::ZeroHours => 'random',
			self::OnDemand => 'bolt',

			// Special
			self::Freelance, self::ProjectBased => 'user-tie',
			self::OnCall => 'phone-alt',
			self::Internship => 'graduation-cap',
			self::TermTime => 'school',

			default => 'briefcase-clock',
		};
	}

	/**
	 * Check if this is a full-time arrangement
	 */
	public function isFullTime(): bool
	{
		return in_array($this, [
			self::FullTime,
			self::FixedShift,
			self::Contract,
			self::TwentyFourSeven,
		]);
	}

	/**
	 * Check if this is a part-time arrangement
	 */
	public function isPartTime(): bool
	{
		return in_array($this, [
			self::PartTime,
			self::ReducedHours,
			self::FourDayWeek,
			self::NineDayFortnight,
			self::JobShare,
			self::TermTime,
		]);
	}

	/**
	 * Check if this involves shift work
	 */
	public function isShiftWork(): bool
	{
		return in_array($this, [
			self::Rotating,
			self::ShiftWork,
			self::Night,
			self::Evening,
			self::Day,
			self::Graveyard,
			self::SwingShift,
			self::Split,
			self::Mixed,
		]);
	}

	/**
	 * Check if this is a flexible arrangement
	 */
	public function isFlexible(): bool
	{
		return in_array($this, [
			self::Flexible,
			self::Compressed,
			self::Annualized,
			self::BankHours,
			self::OnDemand,
			self::Freelance,
		]);
	}

	/**
	 * Check if this is a temporary arrangement
	 */
	public function isTemporary(): bool
	{
		return in_array($this, [
			self::Temporary,
			self::Seasonal,
			self::PeakSeason,
			self::Casual,
			self::ZeroHours,
			self::Internship,
			self::ProjectBased,
		]);
	}

	/**
	 * Get typical weekly hours (approximate range)
	 */
	public function getTypicalHours(): array
	{
		return match ($this) {
			self::FullTime, self::FixedShift, self::Contract,
			self::Compressed, self::Annualized => ['min' => 35, 'max' => 44],

			self::PartTime, self::ReducedHours, self::JobShare,
			self::TermTime => ['min' => 10, 'max' => 30],

			self::FourDayWeek => ['min' => 32, 'max' => 40],
			self::NineDayFortnight => ['min' => 36, 'max' => 40],

			self::Casual, self::ZeroHours, self::OnDemand => ['min' => 0, 'max' => 20],

			self::Freelance, self::ProjectBased => ['min' => 0, 'max' => 60],

			self::Internship => ['min' => 20, 'max' => 40],

			self::Seasonal, self::PeakSeason => ['min' => 30, 'max' => 60],

			self::TwentyFourSeven => ['min' => 35, 'max' => 84],

			default => ['min' => 30, 'max' => 40],
		};
	}

	/**
	 * Check if this typically includes night hours
	 */
	public function includesNightHours(): bool
	{
		return in_array($this, [
			self::Night,
			self::Graveyard,
			self::TwentyFourSeven,
			self::Rotating,
			self::ShiftWork,
			self::Mixed,
		]);
	}

	/**
	 * Get legal considerations for Brazil (CLT)
	 */
	public function getBrazilCLTConsiderations(): string
	{
		return match ($this) {
			self::Night => 'Additional 20% night shift premium required',
			self::PartTime => 'Maximum 25 hours per week, limited benefits',
			self::Temporary => 'Maximum 90 days (can be renewed once)',
			self::Internship => 'Must be enrolled in educational institution',
			self::Freelance => 'Not covered by CLT, considered PJ/MEI',
			self::OnCall => 'Minimum 11-hour rest between calls required',
			self::ZeroHours => 'Not recognized under Brazilian CLT',
			self::BankHours => 'Flexible hours system with compensation time',
			self::Compressed => 'Possible with overtime compensation',
			default => 'Standard CLT regulations apply',
		};
	}

	/**
	 * Get benefits typically associated
	 */
	public function getTypicalBenefits(): array
	{
		return match ($this) {
			self::FullTime, self::FixedShift, self::Contract => [
				'health_insurance',
				'paid_vacation',
				'retirement',
				'sick_leave',
			],

			self::PartTime, self::ReducedHours, self::JobShare => [
				'proportional_vacation',
				'proportional_benefits',
			],

			self::Flexible, self::Compressed, self::Annualized => [
				'flexible_scheduling',
				'remote_work_possible',
			],

			self::Night, self::Evening, self::Graveyard => [
				'shift_premium',
				'transportation_assistance',
				'meal_vouchers',
			],

			self::Freelance, self::ProjectBased => [
				'flexible_scheduling',
				'multiple_clients',
			],

			self::Internship => [
				'educational_credit',
				'work_experience',
				'potential_hire',
			],

			self::Seasonal, self::Temporary, self::Casual => [
				'temporary_employment',
				'seasonal_bonus',
			],

			default => ['basic_employment'],
		};
	}

	/**
	 * Get recommended for specific worker types
	 */
	public function getRecommendedFor(): array
	{
		return match ($this) {
			self::PartTime, self::Flexible => [
				'students',
				'parents',
				'caregivers',
				'semi_retired',
			],

			self::Night, self::Evening => [
				'night_owls',
				'students_daytime',
				'second_job',
			],

			self::Freelance, self::ProjectBased => [
				'professionals',
				'consultants',
				'creatives',
			],

			self::Internship => [
				'students',
				'recent_graduates',
				'career_changers',
			],

			self::Seasonal => [
				'students_summer',
				'travelers',
				'retirees',
			],

			self::JobShare => [
				'parents',
				'caregivers',
				'phased_retirement',
			],

			default => ['general_workers'],
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::FullTime->value => 'Full-Time',
			self::PartTime->value => 'Part-Time',
			self::Mixed->value => 'Mixed Shifts',
			self::Rotating->value => 'Rotating Shifts',
			self::Night->value => 'Night Shift',
			self::Evening->value => 'Evening Shift',
			self::Day->value => 'Day Shift',
			self::Split->value => 'Split Shift',
			self::OnCall->value => 'On-Call',
			self::Flexible->value => 'Flexible Hours',
			self::Compressed->value => 'Compressed Week',
			self::Seasonal->value => 'Seasonal',
			self::Internship->value => 'Internship',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Temporary',
			self::Contract->value => 'Contract',
			self::Casual->value => 'Casual',
			self::ZeroHours->value => 'Zero-Hours',
			self::ProjectBased->value => 'Project-Based',
			self::ShiftWork->value => 'Shift Work',
			self::FixedShift->value => 'Fixed Shift',
			self::SwingShift->value => 'Swing Shift',
			self::Graveyard->value => 'Graveyard Shift',
			self::Afternoon->value => 'Afternoon Shift',
			self::Morning->value => 'Morning Shift',
			self::TwentyFourSeven->value => '24/7 Operations',
			self::FourDayWeek->value => '4-Day Week',
			self::NineDayFortnight->value => '9-Day Fortnight',
			self::ReducedHours->value => 'Reduced Hours',
			self::JobShare->value => 'Job Share',
			self::TermTime->value => 'Term Time Only',
			self::Annualized->value => 'Annualized Hours',
			self::BankHours->value => 'Bank Hours',
			self::OnDemand->value => 'On-Demand',
			self::PeakSeason->value => 'Peak Season',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::FullTime->value => 'Tempo Integral',
			self::PartTime->value => 'Meio Período',
			self::Mixed->value => 'Turnos Mistos',
			self::Rotating->value => 'Turnos Rotativos',
			self::Night->value => 'Turno da Noite',
			self::Evening->value => 'Turno da Tarde',
			self::Day->value => 'Turno do Dia',
			self::Split->value => 'Turno Dividido',
			self::OnCall->value => 'Sobreaviso',
			self::Flexible->value => 'Horário Flexível',
			self::Compressed->value => 'Semana Comprimida',
			self::Seasonal->value => 'Sazonal',
			self::Internship->value => 'Estágio',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Temporário',
			self::Contract->value => 'Contrato',
			self::Casual->value => 'Ocasional',
			self::ZeroHours->value => 'Horas Zero',
			self::ProjectBased->value => 'Por Projeto',
			self::ShiftWork->value => 'Trabalho em Turnos',
			self::FixedShift->value => 'Turno Fixo',
			self::SwingShift->value => 'Turno Integral',
			self::Graveyard->value => 'Turno da Madrugada',
			self::Afternoon->value => 'Turno da Tarde',
			self::Morning->value => 'Turno da Manhã',
			self::TwentyFourSeven->value => 'Operação 24/7',
			self::FourDayWeek->value => 'Semana de 4 Dias',
			self::NineDayFortnight->value => '9 Dias em 2 Semanas',
			self::ReducedHours->value => 'Horas Reduzidas',
			self::JobShare->value => 'Compartilhamento de Cargo',
			self::TermTime->value => 'Apenas em Período Letivo',
			self::Annualized->value => 'Horas Anualizadas',
			self::BankHours->value => 'Banco de Horas',
			self::OnDemand->value => 'Sob Demanda',
			self::PeakSeason->value => 'Alta Temporada',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::FullTime->value => 'Tiempo Completo',
			self::PartTime->value => 'Medio Tiempo',
			self::Mixed->value => 'Turnos Mixtos',
			self::Rotating->value => 'Turnos Rotativos',
			self::Night->value => 'Turno Nocturno',
			self::Evening->value => 'Turno Vespertino',
			self::Day->value => 'Turno Diurno',
			self::Split->value => 'Turno Dividido',
			self::OnCall->value => 'Disponibilidad',
			self::Flexible->value => 'Horario Flexible',
			self::Compressed->value => 'Semana Comprimida',
			self::Seasonal->value => 'Temporal',
			self::Internship->value => 'Prácticas',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Temporal',
			self::Contract->value => 'Contrato',
			self::Casual->value => 'Ocasional',
			self::ZeroHours->value => 'Horas Cero',
			self::ProjectBased->value => 'Por Proyecto',
			self::ShiftWork->value => 'Trabajo por Turnos',
			self::FixedShift->value => 'Turno Fijo',
			self::SwingShift->value => 'Turno Continuo',
			self::Graveyard->value => 'Turno Madrugada',
			self::Afternoon->value => 'Turno Tarde',
			self::Morning->value => 'Turno Mañana',
			self::TwentyFourSeven->value => 'Operación 24/7',
			self::FourDayWeek->value => 'Semana de 4 Días',
			self::NineDayFortnight->value => '9 Días en 2 Semanas',
			self::ReducedHours->value => 'Horas Reducidas',
			self::JobShare->value => 'Compartición de Puesto',
			self::TermTime->value => 'Solo en Período Escolar',
			self::Annualized->value => 'Horas Anualizadas',
			self::BankHours->value => 'Banco de Horas',
			self::OnDemand->value => 'Bajo Demanda',
			self::PeakSeason->value => 'Temporada Alta',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::FullTime->value => 'Vollzeit',
			self::PartTime->value => 'Teilzeit',
			self::Mixed->value => 'Gemischte Schichten',
			self::Rotating->value => 'Wechselschicht',
			self::Night->value => 'Nachtschicht',
			self::Evening->value => 'Spätschicht',
			self::Day->value => 'Tagschicht',
			self::Split->value => 'Geteilte Schicht',
			self::OnCall->value => 'Bereitschaftsdienst',
			self::Flexible->value => 'Gleitzeit',
			self::Compressed->value => 'Verdichtete Woche',
			self::Seasonal->value => 'Saisonarbeit',
			self::Internship->value => 'Praktikum',
			self::Freelance->value => 'Freiberuflich',
			self::Temporary->value => 'Zeitarbeit',
			self::Contract->value => 'Vertrag',
			self::Casual->value => 'Gelegenheitsarbeit',
			self::ZeroHours->value => 'Null-Stunden-Vertrag',
			self::ProjectBased->value => 'Projektbezogen',
			self::ShiftWork->value => 'Schichtarbeit',
			self::FixedShift->value => 'Feste Schicht',
			self::SwingShift->value => 'Wechselschicht',
			self::Graveyard->value => 'Nachtschicht (spät)',
			self::Afternoon->value => 'Nachmittagsschicht',
			self::Morning->value => 'Morgenschicht',
			self::TwentyFourSeven->value => '24/7 Betrieb',
			self::FourDayWeek->value => '4-Tage-Woche',
			self::NineDayFortnight->value => '9-Tage-in-2-Wochen',
			self::ReducedHours->value => 'Reduzierte Stunden',
			self::JobShare->value => 'Jobsharing',
			self::TermTime->value => 'Nur Schulzeit',
			self::Annualized->value => 'Jahresarbeitszeit',
			self::BankHours->value => 'Arbeitszeitkonto',
			self::OnDemand->value => 'Bei Bedarf',
			self::PeakSeason->value => 'Hauptsaison',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::FullTime->value => 'Temps Plein',
			self::PartTime->value => 'Temps Partiel',
			self::Mixed->value => 'Horaires Mixtes',
			self::Rotating->value => 'Rotation d\'Horaires',
			self::Night->value => 'Nuit',
			self::Evening->value => 'Soir',
			self::Day->value => 'Jour',
			self::Split->value => 'Horaires Coupés',
			self::OnCall->value => 'Disponibilité',
			self::Flexible->value => 'Horaires Flexibles',
			self::Compressed->value => 'Semaine Compressée',
			self::Seasonal->value => 'Saisonnier',
			self::Internship->value => 'Stage',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Temporaire',
			self::Contract->value => 'Contrat',
			self::Casual->value => 'Occasionnel',
			self::ZeroHours->value => 'Zéro Heures',
			self::ProjectBased->value => 'Par Projet',
			self::ShiftWork->value => 'Travail en Equipes',
			self::FixedShift->value => 'Horaire Fixe',
			self::SwingShift->value => 'Rotation Continue',
			self::Graveyard->value => 'Nuit Tardive',
			self::Afternoon->value => 'Après-midi',
			self::Morning->value => 'Matin',
			self::TwentyFourSeven->value => 'Opération 24/7',
			self::FourDayWeek->value => 'Semaine de 4 Jours',
			self::NineDayFortnight->value => '9 Jours en 2 Semaines',
			self::ReducedHours->value => 'Heures Réduites',
			self::JobShare->value => 'Partage d\'Emploi',
			self::TermTime->value => 'Période Scolaire Seulement',
			self::Annualized->value => 'Heures Annualisées',
			self::BankHours->value => 'Compte Épargne Temps',
			self::OnDemand->value => 'Sur Demande',
			self::PeakSeason->value => 'Haute Saison',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::FullTime->value => 'دوام كامل',
			self::PartTime->value => 'دوام جزئي',
			self::Mixed->value => 'مناوبات مختلطة',
			self::Rotating->value => 'مناوبات دورية',
			self::Night->value => 'مناوبة ليلية',
			self::Evening->value => 'مناوبة مسائية',
			self::Day->value => 'مناوبة نهارية',
			self::Split->value => 'مناوبة مقسمة',
			self::OnCall->value => 'مناوبة استعداد',
			self::Flexible->value => 'ساعات مرنة',
			self::Compressed->value => 'أسبوع عمل مضغوط',
			self::Seasonal->value => 'موسمي',
			self::Internship->value => 'تدريب',
			self::Freelance->value => 'عمل حر',
			self::Temporary->value => 'مؤقت',
			self::Contract->value => 'عقد',
			self::Casual->value => 'عمل عرضي',
			self::ZeroHours->value => 'عقد بلا ساعات',
			self::ProjectBased->value => 'قائم على المشروع',
			self::ShiftWork->value => 'عمل بنظام المناوبات',
			self::FixedShift->value => 'مناوبة ثابتة',
			self::SwingShift->value => 'مناوبة متغيرة',
			self::Graveyard->value => 'مناوبة ليلية متأخرة',
			self::Afternoon->value => 'مناوبة بعد الظهر',
			self::Morning->value => 'مناوبة صباحية',
			self::TwentyFourSeven->value => 'تشغيل 24/7',
			self::FourDayWeek->value => 'أسبوع عمل 4 أيام',
			self::NineDayFortnight->value => '9 أيام كل أسبوعين',
			self::ReducedHours->value => 'ساعات مخفضة',
			self::JobShare->value => 'تقاسم الوظيفة',
			self::TermTime->value => 'خلال الفصل الدراسي فقط',
			self::Annualized->value => 'ساعات سنوية',
			self::BankHours->value => 'بنك الساعات',
			self::OnDemand->value => 'حسب الطلب',
			self::PeakSeason->value => 'موسم الذروة',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::FullTime->value => 'Fuldtid',
			self::PartTime->value => 'Deltid',
			self::Mixed->value => 'Blandede vagter',
			self::Rotating->value => 'Roterende vagter',
			self::Night->value => 'Nattevagt',
			self::Evening->value => 'Aftenvagt',
			self::Day->value => 'Dagvagt',
			self::Split->value => 'Delt vagt',
			self::OnCall->value => 'Tilkaldevagt',
			self::Flexible->value => 'Fleksible arbejdstider',
			self::Compressed->value => 'Komprimeret arbejdsuge',
			self::Seasonal->value => 'Sæsonarbejde',
			self::Internship->value => 'Praktik',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Midlertidig',
			self::Contract->value => 'Kontrakt',
			self::Casual->value => 'Lejlighedsvis',
			self::ZeroHours->value => 'Nul-timers kontrakt',
			self::ProjectBased->value => 'Projektbaseret',
			self::ShiftWork->value => 'Skifteholdsarbejde',
			self::FixedShift->value => 'Fast vagt',
			self::SwingShift->value => 'Skiftende vagt',
			self::Graveyard->value => 'Sen nattevagt',
			self::Afternoon->value => 'Eftermiddagsvagt',
			self::Morning->value => 'Morgenvagt',
			self::TwentyFourSeven->value => 'Drift 24/7',
			self::FourDayWeek->value => '4-dages uge',
			self::NineDayFortnight->value => '9 dage på 2 uger',
			self::ReducedHours->value => 'Nedsatte timer',
			self::JobShare->value => 'Jobdeling',
			self::TermTime->value => 'Kun i skoleperioder',
			self::Annualized->value => 'Årsnormtimer',
			self::BankHours->value => 'Timebank',
			self::OnDemand->value => 'Ved behov',
			self::PeakSeason->value => 'Højsæson',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::FullTime->value => 'משרה מלאה',
			self::PartTime->value => 'משרה חלקית',
			self::Mixed->value => 'משמרות מעורבות',
			self::Rotating->value => 'משמרות מתחלפות',
			self::Night->value => 'משמרת לילה',
			self::Evening->value => 'משמרת ערב',
			self::Day->value => 'משמרת יום',
			self::Split->value => 'משמרת מפוצלת',
			self::OnCall->value => 'כוננות',
			self::Flexible->value => 'שעות גמישות',
			self::Compressed->value => 'שבוע מרוכז',
			self::Seasonal->value => 'עונתי',
			self::Internship->value => 'התמחות',
			self::Freelance->value => 'פרילנס',
			self::Temporary->value => 'זמני',
			self::Contract->value => 'חוזה',
			self::Casual->value => 'מזדמן',
			self::ZeroHours->value => 'חוזה אפס שעות',
			self::ProjectBased->value => 'לפי פרויקט',
			self::ShiftWork->value => 'עבודה במשמרות',
			self::FixedShift->value => 'משמרת קבועה',
			self::SwingShift->value => 'משמרת ביניים',
			self::Graveyard->value => 'משמרת לילה מאוחרת',
			self::Afternoon->value => 'משמרת אחר הצהריים',
			self::Morning->value => 'משמרת בוקר',
			self::TwentyFourSeven->value => 'פעילות 24/7',
			self::FourDayWeek->value => 'שבוע עבודה בן 4 ימים',
			self::NineDayFortnight->value => '9 ימים בשבועיים',
			self::ReducedHours->value => 'שעות מופחתות',
			self::JobShare->value => 'חלוקת משרה',
			self::TermTime->value => 'רק בתקופת לימודים',
			self::Annualized->value => 'שעות שנתיות',
			self::BankHours->value => 'בנק שעות',
			self::OnDemand->value => 'לפי דרישה',
			self::PeakSeason->value => 'עונת שיא',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::FullTime->value => 'フルタイム',
			self::PartTime->value => 'パートタイム',
			self::Mixed->value => '混合シフト',
			self::Rotating->value => 'ローテーション勤務',
			self::Night->value => '夜勤',
			self::Evening->value => '夕方シフト',
			self::Day->value => '日勤',
			self::Split->value => '分割シフト',
			self::OnCall->value => 'オンコール',
			self::Flexible->value => 'フレックスタイム',
			self::Compressed->value => '圧縮勤務週',
			self::Seasonal->value => '季節雇用',
			self::Internship->value => 'インターンシップ',
			self::Freelance->value => 'フリーランス',
			self::Temporary->value => '臨時',
			self::Contract->value => '契約',
			self::Casual->value => 'カジュアル',
			self::ZeroHours->value => 'ゼロ時間契約',
			self::ProjectBased->value => 'プロジェクトベース',
			self::ShiftWork->value => 'シフト勤務',
			self::FixedShift->value => '固定シフト',
			self::SwingShift->value => 'スイングシフト',
			self::Graveyard->value => '深夜シフト',
			self::Afternoon->value => '午後シフト',
			self::Morning->value => '朝シフト',
			self::TwentyFourSeven->value => '24時間365日運用',
			self::FourDayWeek->value => '週4日勤務',
			self::NineDayFortnight->value => '2週間で9日勤務',
			self::ReducedHours->value => '短時間勤務',
			self::JobShare->value => 'ジョブシェア',
			self::TermTime->value => '学期中のみ',
			self::Annualized->value => '年間所定時間',
			self::BankHours->value => '時間バンク',
			self::OnDemand->value => 'オンデマンド',
			self::PeakSeason->value => '繁忙期',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::FullTime->value => 'Voltijd',
			self::PartTime->value => 'Deeltijd',
			self::Mixed->value => 'Gemengde diensten',
			self::Rotating->value => 'Roulerende diensten',
			self::Night->value => 'Nachtdienst',
			self::Evening->value => 'Avonddienst',
			self::Day->value => 'Dagdienst',
			self::Split->value => 'Gesplitste dienst',
			self::OnCall->value => 'Oproepdienst',
			self::Flexible->value => 'Flexibele uren',
			self::Compressed->value => 'Gecomprimeerde werkweek',
			self::Seasonal->value => 'Seizoenswerk',
			self::Internship->value => 'Stage',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Tijdelijk',
			self::Contract->value => 'Contract',
			self::Casual->value => 'Incidenteel',
			self::ZeroHours->value => 'Nulurencontract',
			self::ProjectBased->value => 'Projectmatig',
			self::ShiftWork->value => 'Ploegendienst',
			self::FixedShift->value => 'Vaste dienst',
			self::SwingShift->value => 'Wisseldienst',
			self::Graveyard->value => 'Late nachtdienst',
			self::Afternoon->value => 'Middagdienst',
			self::Morning->value => 'Ochtenddienst',
			self::TwentyFourSeven->value => '24/7 operatie',
			self::FourDayWeek->value => '4-daagse werkweek',
			self::NineDayFortnight->value => '9 dagen in 2 weken',
			self::ReducedHours->value => 'Verminderde uren',
			self::JobShare->value => 'Jobdeling',
			self::TermTime->value => 'Alleen tijdens schoolperiode',
			self::Annualized->value => 'Jaaruren',
			self::BankHours->value => 'Urenbank',
			self::OnDemand->value => 'Op afroep',
			self::PeakSeason->value => 'Hoogseizoen',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::FullTime->value => 'Pełny etat',
			self::PartTime->value => 'Niepełny etat',
			self::Mixed->value => 'Zmiany mieszane',
			self::Rotating->value => 'Zmiany rotacyjne',
			self::Night->value => 'Zmiana nocna',
			self::Evening->value => 'Zmiana wieczorna',
			self::Day->value => 'Zmiana dzienna',
			self::Split->value => 'Zmiana dzielona',
			self::OnCall->value => 'Dyżur',
			self::Flexible->value => 'Elastyczne godziny',
			self::Compressed->value => 'Skompresowany tydzień pracy',
			self::Seasonal->value => 'Sezonowa',
			self::Internship->value => 'Staż',
			self::Freelance->value => 'Freelance',
			self::Temporary->value => 'Tymczasowa',
			self::Contract->value => 'Umowa',
			self::Casual->value => 'Dorywcza',
			self::ZeroHours->value => 'Umowa zero godzin',
			self::ProjectBased->value => 'Projektowa',
			self::ShiftWork->value => 'Praca zmianowa',
			self::FixedShift->value => 'Stała zmiana',
			self::SwingShift->value => 'Zmiana wahadłowa',
			self::Graveyard->value => 'Późna zmiana nocna',
			self::Afternoon->value => 'Zmiana popołudniowa',
			self::Morning->value => 'Zmiana poranna',
			self::TwentyFourSeven->value => 'Praca 24/7',
			self::FourDayWeek->value => '4-dniowy tydzień pracy',
			self::NineDayFortnight->value => '9 dni w 2 tygodnie',
			self::ReducedHours->value => 'Skrócony wymiar godzin',
			self::JobShare->value => 'Dzielenie etatu',
			self::TermTime->value => 'Tylko w okresie nauki',
			self::Annualized->value => 'Godziny roczne',
			self::BankHours->value => 'Bank godzin',
			self::OnDemand->value => 'Na żądanie',
			self::PeakSeason->value => 'Szczyt sezonu',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::FullTime->value => 'Полная занятость',
			self::PartTime->value => 'Неполная занятость',
			self::Mixed->value => 'Смешанные смены',
			self::Rotating->value => 'Ротационные смены',
			self::Night->value => 'Ночная смена',
			self::Evening->value => 'Вечерняя смена',
			self::Day->value => 'Дневная смена',
			self::Split->value => 'Разделённая смена',
			self::OnCall->value => 'Дежурство по вызову',
			self::Flexible->value => 'Гибкий график',
			self::Compressed->value => 'Сжатая рабочая неделя',
			self::Seasonal->value => 'Сезонная работа',
			self::Internship->value => 'Стажировка',
			self::Freelance->value => 'Фриланс',
			self::Temporary->value => 'Временная работа',
			self::Contract->value => 'Контракт',
			self::Casual->value => 'Подработка',
			self::ZeroHours->value => 'Контракт на ноль часов',
			self::ProjectBased->value => 'Проектная работа',
			self::ShiftWork->value => 'Работа посменно',
			self::FixedShift->value => 'Фиксированная смена',
			self::SwingShift->value => 'Плавающая смена',
			self::Graveyard->value => 'Поздняя ночная смена',
			self::Afternoon->value => 'Послеобеденная смена',
			self::Morning->value => 'Утренняя смена',
			self::TwentyFourSeven->value => 'Работа 24/7',
			self::FourDayWeek->value => '4-дневная неделя',
			self::NineDayFortnight->value => '9 дней за 2 недели',
			self::ReducedHours->value => 'Сокращённые часы',
			self::JobShare->value => 'Разделение ставки',
			self::TermTime->value => 'Только в учебный период',
			self::Annualized->value => 'Годовые часы',
			self::BankHours->value => 'Банк часов',
			self::OnDemand->value => 'По требованию',
			self::PeakSeason->value => 'Пиковый сезон',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::FullTime->value => 'Tam Zamanlı',
			self::PartTime->value => 'Yarı Zamanlı',
			self::Mixed->value => 'Karma Vardiya',
			self::Rotating->value => 'Dönüşümlü Vardiya',
			self::Night->value => 'Gece Vardiyası',
			self::Evening->value => 'Akşam Vardiyası',
			self::Day->value => 'Gündüz Vardiyası',
			self::Split->value => 'Bölünmüş Vardiya',
			self::OnCall->value => 'Nöbet',
			self::Flexible->value => 'Esnek Saatler',
			self::Compressed->value => 'Sıkıştırılmış Hafta',
			self::Seasonal->value => 'Sezonluk',
			self::Internship->value => 'Staj',
			self::Freelance->value => 'Serbest Çalışma',
			self::Temporary->value => 'Geçici',
			self::Contract->value => 'Sözleşmeli',
			self::Casual->value => 'Gündelik',
			self::ZeroHours->value => 'Sıfır Saat Sözleşmesi',
			self::ProjectBased->value => 'Proje Bazlı',
			self::ShiftWork->value => 'Vardiyalı Çalışma',
			self::FixedShift->value => 'Sabit Vardiya',
			self::SwingShift->value => 'Değişken Vardiya',
			self::Graveyard->value => 'Geç Gece Vardiyası',
			self::Afternoon->value => 'Öğleden Sonra Vardiyası',
			self::Morning->value => 'Sabah Vardiyası',
			self::TwentyFourSeven->value => '7/24 Operasyon',
			self::FourDayWeek->value => '4 Günlük Hafta',
			self::NineDayFortnight->value => '2 Haftada 9 Gün',
			self::ReducedHours->value => 'Azaltılmış Saatler',
			self::JobShare->value => 'İş Paylaşımı',
			self::TermTime->value => 'Sadece Dönem Süresince',
			self::Annualized->value => 'Yıllık Saatler',
			self::BankHours->value => 'Saat Bankası',
			self::OnDemand->value => 'Talep Üzerine',
			self::PeakSeason->value => 'Yoğun Sezon',
		];
	}

	// Chinese (Simplified) Labels
	public static function labelsZh(): array
	{
		return [
			self::FullTime->value => '全职',
			self::PartTime->value => '兼职',
			self::Mixed->value => '混合班次',
			self::Rotating->value => '轮班',
			self::Night->value => '夜班',
			self::Evening->value => '晚班',
			self::Day->value => '白班',
			self::Split->value => '分段班次',
			self::OnCall->value => '待命',
			self::Flexible->value => '弹性工时',
			self::Compressed->value => '压缩工作周',
			self::Seasonal->value => '季节性',
			self::Internship->value => '实习',
			self::Freelance->value => '自由职业',
			self::Temporary->value => '临时',
			self::Contract->value => '合同制',
			self::Casual->value => '零工',
			self::ZeroHours->value => '零工时合同',
			self::ProjectBased->value => '项目制',
			self::ShiftWork->value => '轮班工作',
			self::FixedShift->value => '固定班次',
			self::SwingShift->value => '交替班次',
			self::Graveyard->value => '深夜班',
			self::Afternoon->value => '午后班',
			self::Morning->value => '早班',
			self::TwentyFourSeven->value => '24/7 运营',
			self::FourDayWeek->value => '四天工作制',
			self::NineDayFortnight->value => '两周九天',
			self::ReducedHours->value => '减少工时',
			self::JobShare->value => '共享岗位',
			self::TermTime->value => '仅学期期间',
			self::Annualized->value => '年化工时',
			self::BankHours->value => '工时银行',
			self::OnDemand->value => '按需',
			self::PeakSeason->value => '旺季',
		];
	}

	public static function labelsIt(): array
	{
		return [self::FullTime->value => 'Tempo Pieno', self::PartTime->value => 'Part-Time', self::Mixed->value => 'Turni Misti', self::Rotating->value => 'Turni Rotativi', self::Night->value => 'Turno di Notte', self::Evening->value => 'Turno di Sera', self::Day->value => 'Turno di Giorno', self::Split->value => 'Turno Spezzato', self::OnCall->value => 'Reperibilità', self::Flexible->value => 'Orario Flessibile', self::Compressed->value => 'Settimana Compressa', self::Seasonal->value => 'Stagionale', self::Internship->value => 'Tirocinio', self::Freelance->value => 'Freelance', self::Temporary->value => 'Temporaneo', self::Contract->value => 'Contratto', self::Casual->value => 'Occasionale', self::ZeroHours->value => 'Zero Ore', self::ProjectBased->value => 'A Progetto', self::ShiftWork->value => 'Lavoro a Turni', self::FixedShift->value => 'Turno Fisso', self::SwingShift->value => 'Turno Continuato', self::Graveyard->value => 'Turno di Notte Tarda', self::Afternoon->value => 'Turno Pomeridiano', self::Morning->value => 'Turno Mattutino', self::TwentyFourSeven->value => 'Operazione 24/7', self::FourDayWeek->value => 'Settimana di 4 Giorni', self::NineDayFortnight->value => '9 Giorni in 2 Settimane', self::ReducedHours->value => 'Ore Ridotte', self::JobShare->value => 'Condivisione del Lavoro', self::TermTime->value => 'Solo Periodo Scolastico', self::Annualized->value => 'Ore Annualizzate', self::BankHours->value => 'Banca Ore', self::OnDemand->value => 'Su Richiesta', self::PeakSeason->value => 'Alta Stagione',];
	}
}
