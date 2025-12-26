<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PaymentType: string
{
	case Manual     = 'manual';
	case Recurring  = 'recurring';
	case Scheduled  = 'scheduled';
	case Instant    = 'instant';
	case Automatic  = 'automatic';
	case OneTime    = 'one_time';
	case Subscription = 'subscription';
	case Installment = 'installment';
	case Deferred   = 'deferred';
	case Advance    = 'advance';
	case Other      = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null)
			return self::Other;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;
		$map = [
			// Manual
			'manual'        => self::Manual,
			'manual payment' => self::Manual,
			'manualmente'   => self::Manual,
			'manualmente'   => self::Manual,

			// Recurring
			'recurring'     => self::Recurring,
			'recurrente'    => self::Recurring,
			'recorrente'    => self::Recurring,
			'repeat'        => self::Recurring,
			'repetir'       => self::Recurring,

			// Scheduled
			'scheduled'     => self::Scheduled,
			'agendado'      => self::Scheduled,
			'agendada'      => self::Scheduled,
			'programado'    => self::Scheduled,
			'programada'    => self::Scheduled,
			'future'        => self::Scheduled,
			'futuro'        => self::Scheduled,

			// Instant
			'instant'       => self::Instant,
			'instantaneo'   => self::Instant,
			'instantâneo'   => self::Instant,
			'immediate'     => self::Instant,
			'imediato'      => self::Instant,
			'now'           => self::Instant,
			'agora'         => self::Instant,

			// Automatic
			'automatic'     => self::Automatic,
			'automatico'    => self::Automatic,
			'automático'    => self::Automatic,
			'auto'          => self::Automatic,

			// One Time
			'one_time'      => self::OneTime,
			'one time'      => self::OneTime,
			'onetime'       => self::OneTime,
			'single'        => self::OneTime,
			'único'         => self::OneTime,
			'unico'         => self::OneTime,

			// Subscription
			'subscription'  => self::Subscription,
			'assinatura'    => self::Subscription,
			'subscricao'    => self::Subscription,
			'subscripción'  => self::Subscription,

			// Installment
			'installment'   => self::Installment,
			'parcela'       => self::Installment,
			'parcelado'     => self::Installment,
			'installments'  => self::Installment,
			'parcelas'      => self::Installment,

			// Deferred
			'deferred'      => self::Deferred,
			'diferido'      => self::Deferred,
			'postponed'     => self::Deferred,
			'adiado'        => self::Deferred,

			// Advance
			'advance'       => self::Advance,
			'adiantamento'  => self::Advance,
			'adelanto'      => self::Advance,
			'prepayment'    => self::Advance,
			'prepago'       => self::Advance,

			// Other
			'other'         => self::Other,
			'outro'         => self::Other,
			'otro'          => self::Other,
			'misc'          => self::Other,
		];

		return $map[$v] ?? self::Other;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function isAutomatic(): bool
	{
		return match ($this) {
			self::Recurring, self::Automatic, self::Subscription => true,
			default => false,
		};
	}

	public function isScheduled(): bool
	{
		return match ($this) {
			self::Scheduled, self::Deferred, self::Advance => true,
			default => false,
		};
	}

	public function isImmediate(): bool
	{
		return match ($this) {
			self::Manual, self::Instant, self::OneTime => true,
			default => false,
		};
	}

	public function requiresApproval(): bool
	{
		return match ($this) {
			self::Manual, self::Advance => true,
			default => false,
		};
	}

	public function canBeRecurring(): bool
	{
		return match ($this) {
			self::Recurring, self::Subscription, self::Installment => true,
			default => false,
		};
	}

	public function getProcessingTime(): string
	{
		return match ($this) {
			self::Instant    => 'immediate',
			self::Scheduled  => 'future',
			self::Deferred   => 'delayed',
			self::Advance    => 'pre_payment',
			default          => 'standard',
		};
	}

	public function label(): string
	{
		return match ($this) {
			self::Manual       => 'Manual',
			self::Recurring    => 'Recurring',
			self::Scheduled    => 'Scheduled',
			self::Instant      => 'Instant',
			self::Automatic    => 'Automatic',
			self::OneTime      => 'One Time',
			self::Subscription => 'Subscription',
			self::Installment  => 'Installment',
			self::Deferred     => 'Deferred',
			self::Advance      => 'Advance',
			self::Other        => 'Other',
		};
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
			self::Manual->value       => 'Manual',
			self::Recurring->value    => 'Recorrente',
			self::Scheduled->value    => 'Agendado',
			self::Instant->value      => 'Instantâneo',
			self::Automatic->value    => 'Automático',
			self::OneTime->value      => 'Única Vez',
			self::Subscription->value => 'Assinatura',
			self::Installment->value  => 'Parcelado',
			self::Deferred->value     => 'Diferido',
			self::Advance->value      => 'Adiantamento',
			self::Other->value        => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Manual->value       => 'Manual',
			self::Recurring->value    => 'Recurring',
			self::Scheduled->value    => 'Scheduled',
			self::Instant->value      => 'Instant',
			self::Automatic->value    => 'Automatic',
			self::OneTime->value      => 'One Time',
			self::Subscription->value => 'Subscription',
			self::Installment->value  => 'Installment',
			self::Deferred->value     => 'Deferred',
			self::Advance->value      => 'Advance',
			self::Other->value        => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Manual->value       => 'Manual',
			self::Recurring->value    => 'Recurrente',
			self::Scheduled->value    => 'Programado',
			self::Instant->value      => 'Instantáneo',
			self::Automatic->value    => 'Automático',
			self::OneTime->value      => 'Una Vez',
			self::Subscription->value => 'Suscripción',
			self::Installment->value  => 'Pago a Plazos',
			self::Deferred->value     => 'Diferido',
			self::Advance->value      => 'Adelanto',
			self::Other->value        => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Manual->value       => 'يدوي',
			self::Recurring->value    => 'متكرر',
			self::Scheduled->value    => 'مجدول',
			self::Instant->value      => 'فوري',
			self::Automatic->value    => 'تلقائي',
			self::OneTime->value      => 'مرة واحدة',
			self::Subscription->value => 'اشتراك',
			self::Installment->value  => 'قسط',
			self::Deferred->value     => 'مؤجل',
			self::Advance->value      => 'مقدّم',
			self::Other->value        => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Manual->value       => 'Manuel',
			self::Recurring->value    => 'Tilbagevendende',
			self::Scheduled->value    => 'Planlagt',
			self::Instant->value      => 'Øjeblikkelig',
			self::Automatic->value    => 'Automatisk',
			self::OneTime->value      => 'Engangs',
			self::Subscription->value => 'Abonnement',
			self::Installment->value  => 'Afsdrag',
			self::Deferred->value     => 'Udsat',
			self::Advance->value      => 'Forudbetaling',
			self::Other->value        => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Manual->value       => 'Manuell',
			self::Recurring->value    => 'Wiederkehrend',
			self::Scheduled->value    => 'Geplant',
			self::Instant->value      => 'Sofort',
			self::Automatic->value    => 'Automatisch',
			self::OneTime->value      => 'Einmalig',
			self::Subscription->value => 'Abonnement',
			self::Installment->value  => 'Ratenzahlung',
			self::Deferred->value     => 'Aufgeschoben',
			self::Advance->value      => 'Vorauszahlung',
			self::Other->value        => 'Andere',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Manual->value       => 'Manuel',
			self::Recurring->value    => 'Récurrent',
			self::Scheduled->value    => 'Planifié',
			self::Instant->value      => 'Instantané',
			self::Automatic->value    => 'Automatique',
			self::OneTime->value      => 'Unique',
			self::Subscription->value => 'Abonnement',
			self::Installment->value  => 'Versement',
			self::Deferred->value     => 'Différé',
			self::Advance->value      => 'Avance',
			self::Other->value        => 'Autre',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Manual->value       => 'ידני',
			self::Recurring->value    => 'חוזר',
			self::Scheduled->value    => 'מתוזמן',
			self::Instant->value      => 'מיידי',
			self::Automatic->value    => 'אוטומטי',
			self::OneTime->value      => 'פעם אחת',
			self::Subscription->value => 'מנוי',
			self::Installment->value  => 'תשלום',
			self::Deferred->value     => 'נדחה',
			self::Advance->value      => 'מקדמה',
			self::Other->value        => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Manual->value       => 'Manuale',
			self::Recurring->value    => 'Ricorrente',
			self::Scheduled->value    => 'Programmato',
			self::Instant->value      => 'Istantaneo',
			self::Automatic->value    => 'Automatico',
			self::OneTime->value      => 'Una Tantum',
			self::Subscription->value => 'Abbonamento',
			self::Installment->value  => 'Rateale',
			self::Deferred->value     => 'Differito',
			self::Advance->value      => 'Anticipo',
			self::Other->value        => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Manual->value       => '手動',
			self::Recurring->value    => '定期',
			self::Scheduled->value    => '予定',
			self::Instant->value      => '即時',
			self::Automatic->value    => '自動',
			self::OneTime->value      => '一回限り',
			self::Subscription->value => 'サブスクリプション',
			self::Installment->value  => '分割払い',
			self::Deferred->value     => '延期',
			self::Advance->value      => '前払い',
			self::Other->value        => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Manual->value       => 'Handmatig',
			self::Recurring->value    => 'Terugkerend',
			self::Scheduled->value    => 'Gepland',
			self::Instant->value      => 'Direct',
			self::Automatic->value    => 'Automatisch',
			self::OneTime->value      => 'Eenmalig',
			self::Subscription->value => 'Abonnement',
			self::Installment->value  => 'Termijn',
			self::Deferred->value     => 'Uitgesteld',
			self::Advance->value      => 'Voorschot',
			self::Other->value        => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Manual->value       => 'Ręczny',
			self::Recurring->value    => 'Cykliczny',
			self::Scheduled->value    => 'Zaplanowany',
			self::Instant->value      => 'Natychmiastowy',
			self::Automatic->value    => 'Automatyczny',
			self::OneTime->value      => 'Jednorazowy',
			self::Subscription->value => 'Subskrypcja',
			self::Installment->value  => 'Ratalny',
			self::Deferred->value     => 'Odroczony',
			self::Advance->value      => 'Zaliczka',
			self::Other->value        => 'Inne',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Manual->value       => 'Ручной',
			self::Recurring->value    => 'Повторяющийся',
			self::Scheduled->value    => 'Запланированный',
			self::Instant->value      => 'Мгновенный',
			self::Automatic->value    => 'Автоматический',
			self::OneTime->value      => 'Однократный',
			self::Subscription->value => 'Подписка',
			self::Installment->value  => 'Рассрочка',
			self::Deferred->value     => 'Отложенный',
			self::Advance->value      => 'Аванс',
			self::Other->value        => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Manual->value       => 'Manuel',
			self::Recurring->value    => 'Tekrarlanan',
			self::Scheduled->value    => 'Zamanlanmış',
			self::Instant->value      => 'Anında',
			self::Automatic->value    => 'Otomatik',
			self::OneTime->value      => 'Tek Seferlik',
			self::Subscription->value => 'Abonelik',
			self::Installment->value  => 'Taksit',
			self::Deferred->value     => 'Ertelenmiş',
			self::Advance->value      => 'Avans',
			self::Other->value        => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Manual->value       => '手动',
			self::Recurring->value    => '定期',
			self::Scheduled->value    => '预定',
			self::Instant->value      => '即时',
			self::Automatic->value    => '自动',
			self::OneTime->value      => '一次性',
			self::Subscription->value => '订阅',
			self::Installment->value  => '分期付款',
			self::Deferred->value     => '延期',
			self::Advance->value      => '预付款',
			self::Other->value        => '其他',
		];
	}
}
