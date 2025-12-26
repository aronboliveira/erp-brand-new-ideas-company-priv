<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum ParticipationStatus: string
{
	case Invited = 'invited';
	case Pending = 'pending';
	case Accepted = 'accepted';
	case Declined = 'declined';
	case Tentative = 'tentative';
	case Attended = 'attended';
	case NoShow = 'no_show';
	case Cancelled = 'cancelled';
	case Expired = 'expired';
	case Maybe = 'maybe';
	case Awaiting = 'awaiting';
	case Confirmed = 'confirmed';
	case Unconfirmed = 'unconfirmed';
	case Withdrawn = 'withdrawn';
	case Blocked = 'blocked';

	/**
	 * Normalize input to ParticipationStatus
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
			'invited', 'invite', 'convidado', 'invitado' => self::Invited,
			'pending', 'pendente', 'pendiente', 'enattente' => self::Pending,
			'accepted', 'aceito', 'aceptado', 'accepte' => self::Accepted,
			'declined', 'recusado', 'rechazado', 'refuse' => self::Declined,
			'tentative', 'tentativo', 'provisional', 'provisorio' => self::Tentative,
			'attended', 'compareceu', 'asistio', 'participe' => self::Attended,
			'noshow', 'no_show', 'ausente', 'faltou', 'absent' => self::NoShow,
			'cancelled', 'canceled', 'cancelado', 'anulado' => self::Cancelled,
			'expired', 'expirado', 'vencido', 'expire' => self::Expired,
			'maybe', 'talvez', 'quizas', 'peutetre' => self::Maybe,
			'awaiting', 'aguardando', 'esperando', 'enattente' => self::Awaiting,
			'confirmed', 'confirmado', 'confirme' => self::Confirmed,
			'unconfirmed', 'naoconfirmado', 'sinconfirmar' => self::Unconfirmed,
			'withdrawn', 'retirado', 'retractado', 'retire' => self::Withdrawn,
			'blocked', 'bloqueado', 'bloque' => self::Blocked,
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
	 * Get label for this status in specified language
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
			// Positive statuses
			self::Accepted, self::Attended, self::Confirmed => '#10b981', // green
			self::Maybe, self::Tentative, self::Awaiting => '#f59e0b', // amber

			// Neutral statuses
			self::Invited, self::Pending, self::Unconfirmed => '#3b82f6', // blue

			// Negative statuses
			self::Declined, self::NoShow, self::Withdrawn, self::Blocked => '#ef4444', // red
			self::Cancelled, self::Expired => '#6b7280', // gray
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::Invited => 'mail',
			self::Pending => 'clock',
			self::Accepted => 'check-circle',
			self::Declined => 'x-circle',
			self::Tentative => 'help-circle',
			self::Attended => 'user-check',
			self::NoShow => 'user-x',
			self::Cancelled => 'ban',
			self::Expired => 'alert-triangle',
			self::Maybe => 'minus-circle',
			self::Awaiting => 'loader',
			self::Confirmed => 'shield-check',
			self::Unconfirmed => 'shield',
			self::Withdrawn => 'arrow-left-circle',
			self::Blocked => 'lock',
		};
	}

	/**
	 * Check if this status is positive (accepted/attended)
	 */
	public function isPositive(): bool
	{
		return in_array($this, [
			self::Accepted,
			self::Attended,
			self::Confirmed,
		]);
	}

	/**
	 * Check if this status is negative (declined/cancelled)
	 */
	public function isNegative(): bool
	{
		return in_array($this, [
			self::Declined,
			self::NoShow,
			self::Cancelled,
			self::Expired,
			self::Withdrawn,
			self::Blocked,
		]);
	}

	/**
	 * Check if this status is pending/awaiting response
	 */
	public function isPending(): bool
	{
		return in_array($this, [
			self::Invited,
			self::Pending,
			self::Awaiting,
			self::Unconfirmed,
			self::Tentative,
			self::Maybe,
		]);
	}

	/**
	 * Check if this status is final (no further changes expected)
	 */
	public function isFinal(): bool
	{
		return in_array($this, [
			self::Accepted,
			self::Declined,
			self::Attended,
			self::NoShow,
			self::Cancelled,
			self::Expired,
			self::Confirmed,
			self::Withdrawn,
			self::Blocked,
		]);
	}

	/**
	 * Get the next possible statuses
	 */
	public function getPossibleTransitions(): array
	{
		return match ($this) {
			self::Invited => [self::Accepted, self::Declined, self::Tentative, self::Maybe, self::Cancelled],
			self::Pending => [self::Accepted, self::Declined, self::Tentative, self::Maybe, self::Cancelled],
			self::Awaiting => [self::Accepted, self::Declined, self::Confirmed, self::Cancelled],
			self::Tentative => [self::Accepted, self::Declined, self::Maybe, self::Cancelled],
			self::Maybe => [self::Accepted, self::Declined, self::Tentative, self::Cancelled],
			self::Unconfirmed => [self::Confirmed, self::Declined, self::Cancelled],
			self::Accepted => [self::Attended, self::NoShow, self::Withdrawn, self::Cancelled],
			self::Confirmed => [self::Attended, self::NoShow, self::Withdrawn, self::Cancelled],
			self::Attended, self::NoShow, self::Declined, self::Cancelled, self::Expired, self::Withdrawn, self::Blocked => [],
		};
	}

	/**
	 * Check if transition to another status is valid
	 */
	public function canTransitionTo(self $status): bool
	{
		return in_array($status, $this->getPossibleTransitions());
	}

	/**
	 * Get status order for sorting (invited first, then pending, etc.)
	 */
	public function getOrder(): int
	{
		return match ($this) {
			self::Invited => 1,
			self::Pending => 2,
			self::Awaiting => 3,
			self::Unconfirmed => 4,
			self::Tentative => 5,
			self::Maybe => 6,
			self::Accepted => 7,
			self::Confirmed => 8,
			self::Attended => 9,
			self::Declined => 10,
			self::NoShow => 11,
			self::Withdrawn => 12,
			self::Cancelled => 13,
			self::Expired => 14,
			self::Blocked => 15,
		};
	}

	/**
	 * Get all active statuses (not cancelled/expired/blocked)
	 */
	public static function getActiveStatuses(): array
	{
		return [
			self::Invited,
			self::Pending,
			self::Accepted,
			self::Tentative,
			self::Maybe,
			self::Awaiting,
			self::Confirmed,
			self::Unconfirmed,
		];
	}

	/**
	 * Get all inactive statuses
	 */
	public static function getInactiveStatuses(): array
	{
		return [
			self::Declined,
			self::Attended,
			self::NoShow,
			self::Cancelled,
			self::Expired,
			self::Withdrawn,
			self::Blocked,
		];
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::Invited->value => 'Invited',
			self::Pending->value => 'Pending',
			self::Accepted->value => 'Accepted',
			self::Declined->value => 'Declined',
			self::Tentative->value => 'Tentative',
			self::Attended->value => 'Attended',
			self::NoShow->value => 'No Show',
			self::Cancelled->value => 'Cancelled',
			self::Expired->value => 'Expired',
			self::Maybe->value => 'Maybe',
			self::Awaiting->value => 'Awaiting',
			self::Confirmed->value => 'Confirmed',
			self::Unconfirmed->value => 'Unconfirmed',
			self::Withdrawn->value => 'Withdrawn',
			self::Blocked->value => 'Blocked',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::Invited->value => 'Convidado',
			self::Pending->value => 'Pendente',
			self::Accepted->value => 'Aceito',
			self::Declined->value => 'Recusado',
			self::Tentative->value => 'Tentativo',
			self::Attended->value => 'Compareceu',
			self::NoShow->value => 'Não Compareceu',
			self::Cancelled->value => 'Cancelado',
			self::Expired->value => 'Expirado',
			self::Maybe->value => 'Talvez',
			self::Awaiting->value => 'Aguardando',
			self::Confirmed->value => 'Confirmado',
			self::Unconfirmed->value => 'Não Confirmado',
			self::Withdrawn->value => 'Retirado',
			self::Blocked->value => 'Bloqueado',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::Invited->value => 'Invitado',
			self::Pending->value => 'Pendiente',
			self::Accepted->value => 'Aceptado',
			self::Declined->value => 'Rechazado',
			self::Tentative->value => 'Tentativo',
			self::Attended->value => 'Asistió',
			self::NoShow->value => 'No Asistió',
			self::Cancelled->value => 'Cancelado',
			self::Expired->value => 'Expirado',
			self::Maybe->value => 'Quizás',
			self::Awaiting->value => 'Esperando',
			self::Confirmed->value => 'Confirmado',
			self::Unconfirmed->value => 'No Confirmado',
			self::Withdrawn->value => 'Retirado',
			self::Blocked->value => 'Bloqueado',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::Invited->value => 'Eingeladen',
			self::Pending->value => 'Ausstehend',
			self::Accepted->value => 'Angenommen',
			self::Declined->value => 'Abgelehnt',
			self::Tentative->value => 'Vorläufig',
			self::Attended->value => 'Teilgenommen',
			self::NoShow->value => 'Nicht Erschienen',
			self::Cancelled->value => 'Abgesagt',
			self::Expired->value => 'Abgelaufen',
			self::Maybe->value => 'Vielleicht',
			self::Awaiting->value => 'Wartend',
			self::Confirmed->value => 'Bestätigt',
			self::Unconfirmed->value => 'Nicht Bestätigt',
			self::Withdrawn->value => 'Zurückgezogen',
			self::Blocked->value => 'Gesperrt',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::Invited->value => 'Invité',
			self::Pending->value => 'En Attente',
			self::Accepted->value => 'Accepté',
			self::Declined->value => 'Refusé',
			self::Tentative->value => 'Provisoire',
			self::Attended->value => 'A Assisté',
			self::NoShow->value => 'Absent',
			self::Cancelled->value => 'Annulé',
			self::Expired->value => 'Expiré',
			self::Maybe->value => 'Peut-être',
			self::Awaiting->value => 'En Attente',
			self::Confirmed->value => 'Confirmé',
			self::Unconfirmed->value => 'Non Confirmé',
			self::Withdrawn->value => 'Retiré',
			self::Blocked->value => 'Bloqué',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::Invited->value => 'Invitato',
			self::Pending->value => 'In Attesa',
			self::Accepted->value => 'Accettato',
			self::Declined->value => 'Rifiutato',
			self::Tentative->value => 'Provvisorio',
			self::Attended->value => 'Partecipato',
			self::NoShow->value => 'Assente',
			self::Cancelled->value => 'Annullato',
			self::Expired->value => 'Scaduto',
			self::Maybe->value => 'Forse',
			self::Awaiting->value => 'In Attesa',
			self::Confirmed->value => 'Confermato',
			self::Unconfirmed->value => 'Non Confermato',
			self::Withdrawn->value => 'Ritirato',
			self::Blocked->value => 'Bloccato',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Invited->value => 'Uitgenodigd',
			self::Pending->value => 'In Afwachting',
			self::Accepted->value => 'Geaccepteerd',
			self::Declined->value => 'Afgewezen',
			self::Tentative->value => 'Voorlopig',
			self::Attended->value => 'Aanwezig',
			self::NoShow->value => 'Niet Verschenen',
			self::Cancelled->value => 'Geannuleerd',
			self::Expired->value => 'Verlopen',
			self::Maybe->value => 'Misschien',
			self::Awaiting->value => 'Wachtend',
			self::Confirmed->value => 'Bevestigd',
			self::Unconfirmed->value => 'Niet Bevestigd',
			self::Withdrawn->value => 'Teruggetrokken',
			self::Blocked->value => 'Geblokkeerd',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Invited->value => 'Zaproszony',
			self::Pending->value => 'Oczekujący',
			self::Accepted->value => 'Zaakceptowany',
			self::Declined->value => 'Odrzucony',
			self::Tentative->value => 'Tymczasowy',
			self::Attended->value => 'Uczestniczył',
			self::NoShow->value => 'Nie Obecny',
			self::Cancelled->value => 'Anulowany',
			self::Expired->value => 'Wygasł',
			self::Maybe->value => 'Może',
			self::Awaiting->value => 'Oczekujący',
			self::Confirmed->value => 'Potwierdzony',
			self::Unconfirmed->value => 'Niepotwierdzony',
			self::Withdrawn->value => 'Wycofany',
			self::Blocked->value => 'Zablokowany',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Invited->value => 'Приглашен',
			self::Pending->value => 'В Ожидании',
			self::Accepted->value => 'Принято',
			self::Declined->value => 'Отклонено',
			self::Tentative->value => 'Предварительно',
			self::Attended->value => 'Присутствовал',
			self::NoShow->value => 'Не Явился',
			self::Cancelled->value => 'Отменено',
			self::Expired->value => 'Истекло',
			self::Maybe->value => 'Возможно',
			self::Awaiting->value => 'Ожидание',
			self::Confirmed->value => 'Подтверждено',
			self::Unconfirmed->value => 'Не Подтверждено',
			self::Withdrawn->value => 'Отозвано',
			self::Blocked->value => 'Заблокировано',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Invited->value => 'Davet Edildi',
			self::Pending->value => 'Beklemede',
			self::Accepted->value => 'Kabul Edildi',
			self::Declined->value => 'Reddedildi',
			self::Tentative->value => 'Geçici',
			self::Attended->value => 'Katıldı',
			self::NoShow->value => 'Katılmadı',
			self::Cancelled->value => 'İptal Edildi',
			self::Expired->value => 'Süresi Doldu',
			self::Maybe->value => 'Belki',
			self::Awaiting->value => 'Bekleniyor',
			self::Confirmed->value => 'Onaylandı',
			self::Unconfirmed->value => 'Onaylanmadı',
			self::Withdrawn->value => 'Geri Çekildi',
			self::Blocked->value => 'Engellendi',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::Invited->value => 'مدعو',
			self::Pending->value => 'قيد الانتظار',
			self::Accepted->value => 'مقبول',
			self::Declined->value => 'مرفوض',
			self::Tentative->value => 'مؤقت',
			self::Attended->value => 'حضر',
			self::NoShow->value => 'لم يحضر',
			self::Cancelled->value => 'ملغي',
			self::Expired->value => 'منتهي الصلاحية',
			self::Maybe->value => 'ربما',
			self::Awaiting->value => 'في الانتظار',
			self::Confirmed->value => 'مؤكد',
			self::Unconfirmed->value => 'غير مؤكد',
			self::Withdrawn->value => 'مسحوب',
			self::Blocked->value => 'محظور',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::Invited->value => 'הוזמן',
			self::Pending->value => 'ממתין',
			self::Accepted->value => 'התקבל',
			self::Declined->value => 'נדחה',
			self::Tentative->value => 'זמני',
			self::Attended->value => 'נכח',
			self::NoShow->value => 'לא נכח',
			self::Cancelled->value => 'בוטל',
			self::Expired->value => 'פג תוקף',
			self::Maybe->value => 'אולי',
			self::Awaiting->value => 'מחכה',
			self::Confirmed->value => 'אושר',
			self::Unconfirmed->value => 'לא מאושר',
			self::Withdrawn->value => 'נמשך',
			self::Blocked->value => 'נחסם',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::Invited->value => '招待済み',
			self::Pending->value => '保留中',
			self::Accepted->value => '承諾済み',
			self::Declined->value => '辞退済み',
			self::Tentative->value => '仮',
			self::Attended->value => '出席済み',
			self::NoShow->value => '欠席',
			self::Cancelled->value => 'キャンセル済み',
			self::Expired->value => '期限切れ',
			self::Maybe->value => '未定',
			self::Awaiting->value => '待機中',
			self::Confirmed->value => '確定済み',
			self::Unconfirmed->value => '未確定',
			self::Withdrawn->value => '撤回済み',
			self::Blocked->value => 'ブロック済み',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::Invited->value => 'Invitere',
			self::Pending->value => 'Afventer',
			self::Accepted->value => 'Accepteret',
			self::Declined->value => 'Afvist',
			self::Tentative->value => 'Foreløbig',
			self::Attended->value => 'Deltog',
			self::NoShow->value => 'Mødte Ikke Op',
			self::Cancelled->value => 'Annulleret',
			self::Expired->value => 'Udløbet',
			self::Maybe->value => 'Måske',
			self::Awaiting->value => 'Venter',
			self::Confirmed->value => 'Bekræftet',
			self::Unconfirmed->value => 'Ikke Bekræftet',
			self::Withdrawn->value => 'Trukket Tilbage',
			self::Blocked->value => 'Blokeret',
		];
	}

	// Chinese Labels
	public static function labelsZh(): array
	{
		return [
			self::Invited->value => '已邀请',
			self::Pending->value => '待处理',
			self::Accepted->value => '已接受',
			self::Declined->value => '已拒绝',
			self::Tentative->value => '暂定',
			self::Attended->value => '已参加',
			self::NoShow->value => '未出席',
			self::Cancelled->value => '已取消',
			self::Expired->value => '已过期',
			self::Maybe->value => '可能',
			self::Awaiting->value => '等待中',
			self::Confirmed->value => '已确认',
			self::Unconfirmed->value => '未确认',
			self::Withdrawn->value => '已撤回',
			self::Blocked->value => '已屏蔽',
		];
	}
}
