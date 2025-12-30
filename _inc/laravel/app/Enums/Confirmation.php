<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum Confirmation: string
{
	// Basic confirmation statuses
	case Pending = 'pending';
	case Confirmed = 'confirmed';
	case Declined = 'declined';
	case Cancelled = 'cancelled';
	case Expired = 'expired';
	case Failed = 'failed';
	case OnHold = 'on_hold';
	case Rescheduled = 'rescheduled';
	case Tentative = 'tentative';

	/**
	 * Normalize input to Confirmation
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
			'pending', 'waiting', 'awaiting', 'inreview' => self::Pending,
			'confirmed', 'confirmed', 'accepted', 'approved', 'yes' => self::Confirmed,
			'declined', 'rejected', 'denied', 'no' => self::Declined,
			'cancelled', 'canceled', 'terminated', 'void' => self::Cancelled,
			'expired', 'timedout', 'timeout' => self::Expired,
			'failed', 'error', 'unsuccessful' => self::Failed,
			'onhold', 'hold', 'paused', 'delayed' => self::OnHold,
			'rescheduled', 'moved', 'postponed' => self::Rescheduled,
			'tentative', 'maybe', 'provisional', 'conditional' => self::Tentative,
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
	 * Get label for this confirmation status in specified language
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
			self::Pending, self::Tentative, self::OnHold => '#f59e0b', // Yellow/amber
			self::Confirmed => '#10b981', // Green
			self::Declined, self::Failed, self::Cancelled => '#ef4444', // Red
			self::Expired => '#6b7280', // Gray
			self::Rescheduled => '#8b5cf6', // Purple
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::Pending => 'clock',
			self::Confirmed => 'check-circle',
			self::Declined => 'times-circle',
			self::Cancelled => 'ban',
			self::Expired => 'hourglass-end',
			self::Failed => 'exclamation-circle',
			self::OnHold => 'pause-circle',
			self::Rescheduled => 'calendar-alt',
			self::Tentative => 'question-circle',
		};
	}

	/**
	 * Check if status is active/awaiting action
	 */
	public function isActive(): bool
	{
		return in_array($this, [
			self::Pending,
			self::OnHold,
			self::Tentative,
		]);
	}

	/**
	 * Check if status is finalized (no further action needed)
	 */
	public function isFinalized(): bool
	{
		return in_array($this, [
			self::Confirmed,
			self::Declined,
			self::Cancelled,
			self::Expired,
			self::Failed,
		]);
	}

	/**
	 * Check if status is positive/affirmative
	 */
	public function isPositive(): bool
	{
		return in_array($this, [
			self::Confirmed,
		]);
	}

	/**
	 * Check if status is negative
	 */
	public function isNegative(): bool
	{
		return in_array($this, [
			self::Declined,
			self::Cancelled,
			self::Failed,
		]);
	}

	/**
	 * Check if status can be changed
	 */
	public function isMutable(): bool
	{
		return in_array($this, [
			self::Pending,
			self::OnHold,
			self::Tentative,
		]);
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Pending->value => 'Pending',
			self::Confirmed->value => 'Confirmed',
			self::Declined->value => 'Declined',
			self::Cancelled->value => 'Cancelled',
			self::Expired->value => 'Expired',
			self::Failed->value => 'Failed',
			self::OnHold->value => 'On Hold',
			self::Rescheduled->value => 'Rescheduled',
			self::Tentative->value => 'Tentative',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Pending->value => 'Pendente',
			self::Confirmed->value => 'Confirmado',
			self::Declined->value => 'Recusado',
			self::Cancelled->value => 'Cancelado',
			self::Expired->value => 'Expirado',
			self::Failed->value => 'Falhou',
			self::OnHold->value => 'Em Espera',
			self::Rescheduled->value => 'Reagendado',
			self::Tentative->value => 'Tentativo',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Pending->value => 'Pendiente',
			self::Confirmed->value => 'Confirmado',
			self::Declined->value => 'Rechazado',
			self::Cancelled->value => 'Cancelado',
			self::Expired->value => 'Expirado',
			self::Failed->value => 'Fallido',
			self::OnHold->value => 'En Espera',
			self::Rescheduled->value => 'Reprogramado',
			self::Tentative->value => 'Tentativo',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Pending->value => 'En Attente',
			self::Confirmed->value => 'Confirmé',
			self::Declined->value => 'Refusé',
			self::Cancelled->value => 'Annulé',
			self::Expired->value => 'Expiré',
			self::Failed->value => 'Échoué',
			self::OnHold->value => 'En Suspens',
			self::Rescheduled->value => 'Replanifié',
			self::Tentative->value => 'Tentative',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Pending->value => 'Ausstehend',
			self::Confirmed->value => 'Bestätigt',
			self::Declined->value => 'Abgelehnt',
			self::Cancelled->value => 'Abgesagt',
			self::Expired->value => 'Abgelaufen',
			self::Failed->value => 'Fehlgeschlagen',
			self::OnHold->value => 'Angehalten',
			self::Rescheduled->value => 'Neu Geplant',
			self::Tentative->value => 'Vorläufig',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Pending->value => 'قيد الانتظار',
			self::Confirmed->value => 'مؤكد',
			self::Declined->value => 'مرفوض',
			self::Cancelled->value => 'ملغى',
			self::Expired->value => 'منتهي الصلاحية',
			self::Failed->value => 'فشل',
			self::OnHold->value => 'معلّق',
			self::Rescheduled->value => 'أُعيدت جدولته',
			self::Tentative->value => 'مبدئي',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Pending->value => 'Afventer',
			self::Confirmed->value => 'Bekræftet',
			self::Declined->value => 'Afvist',
			self::Cancelled->value => 'Annulleret',
			self::Expired->value => 'Udløbet',
			self::Failed->value => 'Mislykkedes',
			self::OnHold->value => 'Sat på pause',
			self::Rescheduled->value => 'Omlagt',
			self::Tentative->value => 'Foreløbig',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Pending->value => 'ממתין',
			self::Confirmed->value => 'מאושר',
			self::Declined->value => 'נדחה',
			self::Cancelled->value => 'בוטל',
			self::Expired->value => 'פג תוקף',
			self::Failed->value => 'נכשל',
			self::OnHold->value => 'בהמתנה',
			self::Rescheduled->value => 'נקבע מחדש',
			self::Tentative->value => 'מותנה',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Pending->value => 'In Attesa',
			self::Confirmed->value => 'Confermato',
			self::Declined->value => 'Rifiutato',
			self::Cancelled->value => 'Annullato',
			self::Expired->value => 'Scaduto',
			self::Failed->value => 'Fallito',
			self::OnHold->value => 'In Sospeso',
			self::Rescheduled->value => 'Ripianificato',
			self::Tentative->value => 'Provvisorio',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Pending->value => '保留中',
			self::Confirmed->value => '確認済み',
			self::Declined->value => '辞退',
			self::Cancelled->value => 'キャンセル',
			self::Expired->value => '期限切れ',
			self::Failed->value => '失敗',
			self::OnHold->value => '保留',
			self::Rescheduled->value => '再調整',
			self::Tentative->value => '暫定',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Pending->value => 'In Afwachting',
			self::Confirmed->value => 'Bevestigd',
			self::Declined->value => 'Afgewezen',
			self::Cancelled->value => 'Geannuleerd',
			self::Expired->value => 'Verlopen',
			self::Failed->value => 'Mislukt',
			self::OnHold->value => 'In de wacht',
			self::Rescheduled->value => 'Verzet',
			self::Tentative->value => 'Voorlopig',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Pending->value => 'Oczekujące',
			self::Confirmed->value => 'Potwierdzone',
			self::Declined->value => 'Odrzucone',
			self::Cancelled->value => 'Anulowane',
			self::Expired->value => 'Wygasłe',
			self::Failed->value => 'Nieudane',
			self::OnHold->value => 'Wstrzymane',
			self::Rescheduled->value => 'Przełożone',
			self::Tentative->value => 'Wstępne',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Pending->value => 'Ожидание',
			self::Confirmed->value => 'Подтверждено',
			self::Declined->value => 'Отклонено',
			self::Cancelled->value => 'Отменено',
			self::Expired->value => 'Истекло',
			self::Failed->value => 'Ошибка',
			self::OnHold->value => 'Приостановлено',
			self::Rescheduled->value => 'Перенесено',
			self::Tentative->value => 'Предварительно',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Pending->value => 'Beklemede',
			self::Confirmed->value => 'Onaylandı',
			self::Declined->value => 'Reddedildi',
			self::Cancelled->value => 'İptal Edildi',
			self::Expired->value => 'Süresi Doldu',
			self::Failed->value => 'Başarısız',
			self::OnHold->value => 'Beklemeye Alındı',
			self::Rescheduled->value => 'Yeniden Planlandı',
			self::Tentative->value => 'Geçici',
		];
	}

	// Chinese (Simplified) Labels
	public static function labelsZh(): array
	{
		return [
			self::Pending->value => '待处理',
			self::Confirmed->value => '已确认',
			self::Declined->value => '已拒绝',
			self::Cancelled->value => '已取消',
			self::Expired->value => '已过期',
			self::Failed->value => '失败',
			self::OnHold->value => '暂停',
			self::Rescheduled->value => '已改期',
			self::Tentative->value => '暂定',
		];
	}
}
