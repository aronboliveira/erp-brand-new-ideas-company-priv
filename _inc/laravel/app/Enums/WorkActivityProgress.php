<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum WorkActivityProgress: string
{
	case Pending = 'pending';
	case Started = 'started';
	case Completed = 'completed';
	case Terminated = 'terminated';

	public static function normalize(string|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::Pending;

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim($value)));
		return match ($normalizedValue) {
			'pending', 'pendente', 'pendiente', 'enattente', 'wartend', '未定' => self::Pending,
			'started', 'iniciado', 'iniciada', 'commence', 'begonnen', '開始' => self::Started,
			'completed', 'concluido', 'concluida', 'termine', 'abgeschlossen', '完了' => self::Completed,
			'terminated', 'terminado', 'terminada', 'interrompu', 'beendet', '終了' => self::Terminated,
			default => self::Pending,
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
	 * Get the label for a specific progress status in the specified language
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
			self::Pending => 'clock',
			self::Started => 'play-circle',
			self::Completed => 'check-circle',
			self::Terminated => 'x-circle',
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::Pending => '#f59e0b', // amber
			self::Started => '#3b82f6', // blue
			self::Completed => '#10b981', // green
			self::Terminated => '#ef4444', // red
		};
	}

	/**
	 * Check if the activity is active (started but not completed)
	 */
	public function isActive(): bool
	{
		return $this === self::Started;
	}

	/**
	 * Check if the activity is completed
	 */
	public function isCompleted(): bool
	{
		return $this === self::Completed;
	}

	/**
	 * Check if the activity is pending
	 */
	public function isPending(): bool
	{
		return $this === self::Pending;
	}

	/**
	 * Check if the activity is terminated/cancelled
	 */
	public function isTerminated(): bool
	{
		return $this === self::Terminated;
	}

	/**
	 * Check if the activity is finished (completed or terminated)
	 */
	public function isFinished(): bool
	{
		return in_array($this, [self::Completed, self::Terminated]);
	}

	/**
	 * Check if the activity can be started from current status
	 */
	public function canBeStarted(): bool
	{
		return $this === self::Pending;
	}

	/**
	 * Check if the activity can be completed from current status
	 */
	public function canBeCompleted(): bool
	{
		return $this === self::Started;
	}

	/**
	 * Check if the activity can be terminated from current status
	 */
	public function canBeTerminated(): bool
	{
		return in_array($this, [self::Pending, self::Started]);
	}

	/**
	 * Get the next logical status in workflow
	 */
	public function getNextStatus(): ?self
	{
		return match ($this) {
			self::Pending => self::Started,
			self::Started => self::Completed,
			self::Completed => null,
			self::Terminated => null,
		};
	}

	/**
	 * Get the previous logical status in workflow
	 */
	public function getPreviousStatus(): ?self
	{
		return match ($this) {
			self::Started => self::Pending,
			self::Completed => self::Started,
			self::Terminated => self::Started,
			self::Pending => null,
		};
	}

	/**
	 * Get the completion percentage for this status
	 */
	public function getCompletionPercentage(): int
	{
		return match ($this) {
			self::Pending => 0,
			self::Started => 50,
			self::Completed => 100,
			self::Terminated => 100,
		};
	}

	/**
	 * Get the description of what this status means
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
	 * Get the status transition matrix (allowed transitions)
	 */
	public function getAllowedTransitions(): array
	{
		return match ($this) {
			self::Pending => [self::Started, self::Terminated],
			self::Started => [self::Completed, self::Terminated],
			self::Completed => [],
			self::Terminated => [],
		};
	}

	/**
	 * Check if transition to another status is allowed
	 */
	public function canTransitionTo(self $targetStatus): bool
	{
		return in_array($targetStatus, $this->getAllowedTransitions());
	}

	/**
	 * Get the action verb for this status
	 */
	public function getActionVerb(): string
	{
		return match ($this) {
			self::Pending => 'start',
			self::Started => 'complete',
			self::Completed => 'reopen',
			self::Terminated => 'resume',
		};
	}

	/**
	 * Get the typical timeline estimate for this status
	 */
	public function getTimelineEstimate(): string
	{
		return match ($this) {
			self::Pending => 'future',
			self::Started => 'current',
			self::Completed => 'past',
			self::Terminated => 'past',
		};
	}

	/**
	 * Get the notification type for status change
	 */
	public function getNotificationType(): string
	{
		return match ($this) {
			self::Pending => 'info',
			self::Started => 'success',
			self::Completed => 'success',
			self::Terminated => 'warning',
		};
	}

	/**
	 * Convert to the original array format for backward compatibility
	 */
	public static function toOriginalArray($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$labels = self::labels($lang);
		return [
			$labels['pending'],   // Pending
			$labels['started'],   // Started
			$labels['completed'], // Completed
			$labels['terminated'], // Terminated
		];
	}

	/**
	 * Get statuses that represent "in progress"
	 */
	public static function getInProgressStatuses(): array
	{
		return [self::Started];
	}

	/**
	 * Get statuses that represent "not started"
	 */
	public static function getNotStartedStatuses(): array
	{
		return [self::Pending];
	}

	/**
	 * Get statuses that represent "final" (no further changes expected)
	 */
	public static function getFinalStatuses(): array
	{
		return [self::Completed, self::Terminated];
	}

	/**
	 * Get the sort order for display purposes
	 */
	public function getSortOrder(): int
	{
		return match ($this) {
			self::Pending => 1,
			self::Started => 2,
			self::Terminated => 3,
			self::Completed => 4,
		};
	}

	public static function labelsEn(): array
	{
		return [
			self::Pending->value => 'Pending',
			self::Started->value => 'Started',
			self::Completed->value => 'Completed',
			self::Terminated->value => 'Terminated',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::Pending->value => 'Pendente',
			self::Started->value => 'Iniciado',
			self::Completed->value => 'Concluído',
			self::Terminated->value => 'Terminado',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Pending->value => 'Pendiente',
			self::Started->value => 'Iniciado',
			self::Completed->value => 'Completado',
			self::Terminated->value => 'Terminado',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Pending->value => 'معلق',
			self::Started->value => 'بدأ',
			self::Completed->value => 'مكتمل',
			self::Terminated->value => 'منتهي',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Pending->value => 'Afventer',
			self::Started->value => 'Startet',
			self::Completed->value => 'Afsluttet',
			self::Terminated->value => 'Afsluttet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Pending->value => 'Ausstehend',
			self::Started->value => 'Gestartet',
			self::Completed->value => 'Abgeschlossen',
			self::Terminated->value => 'Beendet',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Pending->value => 'En Attente',
			self::Started->value => 'Démarré',
			self::Completed->value => 'Terminé',
			self::Terminated->value => 'Interrompu',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Pending->value => 'ממתין',
			self::Started->value => 'התחיל',
			self::Completed->value => 'הושלם',
			self::Terminated->value => 'הופסק',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Pending->value => 'In Attesa',
			self::Started->value => 'Iniziato',
			self::Completed->value => 'Completato',
			self::Terminated->value => 'Terminato',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Pending->value => '保留中',
			self::Started->value => '開始',
			self::Completed->value => '完了',
			self::Terminated->value => '終了',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Pending->value => 'In Afwachting',
			self::Started->value => 'Gestart',
			self::Completed->value => 'Voltooid',
			self::Terminated->value => 'Beëindigd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Pending->value => 'Oczekujące',
			self::Started->value => 'Rozpoczęte',
			self::Completed->value => 'Zakończone',
			self::Terminated->value => 'Zakończone',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Pending->value => 'В Ожидании',
			self::Started->value => 'Начато',
			self::Completed->value => 'Завершено',
			self::Terminated->value => 'Прекращено',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Pending->value => 'Beklemede',
			self::Started->value => 'Başladı',
			self::Completed->value => 'Tamamlandı',
			self::Terminated->value => 'Sonlandırıldı',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Pending->value => '待处理',
			self::Started->value => '已开始',
			self::Completed->value => '已完成',
			self::Terminated->value => '已终止',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			self::Pending->value => 'Activity has been created but not yet started',
			self::Started->value => 'Activity is currently in progress',
			self::Completed->value => 'Activity has been successfully finished',
			self::Terminated->value => 'Activity was stopped before completion',
		];
	}

	public static function descriptionsPtBr(): array
	{
		return [
			self::Pending->value => 'Atividade foi criada mas ainda não iniciada',
			self::Started->value => 'Atividade está atualmente em andamento',
			self::Completed->value => 'Atividade foi concluída com sucesso',
			self::Terminated->value => 'Atividade foi interrompida antes da conclusão',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			self::Pending->value => 'La actividad ha sido creada pero aún no se ha iniciado',
			self::Started->value => 'La actividad está actualmente en progreso',
			self::Completed->value => 'La actividad se ha completado con éxito',
			self::Terminated->value => 'La actividad se detuvo antes de completarse',
		];
	}

	public static function descriptionsAr(): array
	{
		return [
			self::Pending->value => 'تم إنشاء النشاط ولكن لم يبدأ بعد',
			self::Started->value => 'النشاط قيد التنفيذ حاليًا',
			self::Completed->value => 'تم الانتهاء من النشاط بنجاح',
			self::Terminated->value => 'تم إيقاف النشاط قبل الانتهاء',
		];
	}

	public static function descriptionsDa(): array
	{
		return [
			self::Pending->value => 'Aktivitet er blevet oprettet, men endnu ikke startet',
			self::Started->value => 'Aktivitet er i øjeblikket i gang',
			self::Completed->value => 'Aktivitet er blevet afsluttet med succes',
			self::Terminated->value => 'Aktivitet blev stoppet før afslutning',
		];
	}

	public static function descriptionsDe(): array
	{
		return [
			self::Pending->value => 'Aktivität wurde erstellt, aber noch nicht gestartet',
			self::Started->value => 'Aktivität ist derzeit in Bearbeitung',
			self::Completed->value => 'Aktivität wurde erfolgreich abgeschlossen',
			self::Terminated->value => 'Aktivität wurde vor Abschluss gestoppt',
		];
	}

	public static function descriptionsFr(): array
	{
		return [
			self::Pending->value => 'L\'activité a été créée mais n\'a pas encore commencé',
			self::Started->value => 'L\'activité est actuellement en cours',
			self::Completed->value => 'L\'activité a été terminée avec succès',
			self::Terminated->value => 'L\'activité a été arrêtée avant son achèvement',
		];
	}

	public static function descriptionsHe(): array
	{
		return [
			self::Pending->value => 'הפעילות נוצרה אך עדיין לא החלה',
			self::Started->value => 'הפעילות כרגע מתקדמת',
			self::Completed->value => 'הפעילות הושלמה בהצלחה',
			self::Terminated->value => 'הפעילות הופסקה לפני השלמתה',
		];
	}

	public static function descriptionsIt(): array
	{
		return [
			self::Pending->value => 'L\'attività è stata creata ma non ancora avviata',
			self::Started->value => 'L\'attività è attualmente in corso',
			self::Completed->value => 'L\'attività è stata completata con successo',
			self::Terminated->value => 'L\'attività è stata interrotta prima del completamento',
		];
	}

	public static function descriptionsJa(): array
	{
		return [
			self::Pending->value => 'アクティビティは作成されましたが、まだ開始されていません',
			self::Started->value => 'アクティビティは現在進行中です',
			self::Completed->value => 'アクティビティは正常に完了しました',
			self::Terminated->value => 'アクティビティは完了前に停止されました',
		];
	}

	public static function descriptionsNl(): array
	{
		return [
			self::Pending->value => 'Activiteit is aangemaakt maar nog niet gestart',
			self::Started->value => 'Activiteit is momenteel in uitvoering',
			self::Completed->value => 'Activiteit is succesvol afgerond',
			self::Terminated->value => 'Activiteit werd gestopt voor voltooiing',
		];
	}

	public static function descriptionsPl(): array
	{
		return [
			self::Pending->value => 'Aktywność została utworzona, ale jeszcze nie rozpoczęta',
			self::Started->value => 'Aktywność jest obecnie w toku',
			self::Completed->value => 'Aktywność została pomyślnie zakończona',
			self::Terminated->value => 'Aktywność została zatrzymana przed ukończeniem',
		];
	}

	public static function descriptionsRu(): array
	{
		return [
			self::Pending->value => 'Активность создана, но еще не начата',
			self::Started->value => 'Активность в настоящее время выполняется',
			self::Completed->value => 'Активность успешно завершена',
			self::Terminated->value => 'Активность была остановлена до завершения',
		];
	}

	public static function descriptionsTr(): array
	{
		return [
			self::Pending->value => 'Etkinlik oluşturuldu ancak henüz başlatılmadı',
			self::Started->value => 'Etkinlik şu anda devam ediyor',
			self::Completed->value => 'Etkinlik başarıyla tamamlandı',
			self::Terminated->value => 'Etkinlik tamamlanmadan durduruldu',
		];
	}

	public static function descriptionsZh(): array
	{
		return [
			self::Pending->value => '活动已创建但尚未开始',
			self::Started->value => '活动目前正在进行中',
			self::Completed->value => '活动已成功完成',
			self::Terminated->value => '活动在完成前已停止',
		];
	}
}
