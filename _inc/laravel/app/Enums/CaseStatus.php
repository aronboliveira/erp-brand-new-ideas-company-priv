<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum CaseStatus: string
{
	// Basic statuses
	case New = 'new';
	case Open = 'open';
	case InProgress = 'in_progress';
	case OnHold = 'on_hold';
	case WaitingCustomer = 'waiting_customer';
	case WaitingVendor = 'waiting_vendor';
	case WaitingThirdParty = 'waiting_third_party';
	case Escalated = 'escalated';
	case Resolved = 'resolved';
	case Closed = 'closed';
	case Reopened = 'reopened';
	case Cancelled = 'cancelled';
	case Duplicate = 'duplicate';
	case Archived = 'archived';
	case Deleted = 'deleted';

		// Additional statuses
	case UnderReview = 'under_review';
	case PendingApproval = 'pending_approval';
	case Scheduled = 'scheduled';
	case InDevelopment = 'in_development';
	case Testing = 'testing';
	case Deferred = 'deferred';
	case Merged = 'merged';
	case AwaitingFeedback = 'awaiting_feedback';
	case Failed = 'failed';
	case Blocked = 'blocked';

	/**
	 * Normalize input to CaseStatus
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
			// New/received statuses
			'new', 'received', 'created', 'unassigned' => self::New,
			'open', 'active', 'assigned', 'acknowledged' => self::Open,
			'inprogress', 'progress', 'working', 'processing' => self::InProgress,
			'onhold', 'hold', 'paused', 'suspended' => self::OnHold,

			// Waiting statuses
			'waitingcustomer', 'awaitingcustomer', 'customerresponse', 'waitingforcustomer' => self::WaitingCustomer,
			'waitingvendor', 'awaitingvendor', 'vendoresponse' => self::WaitingVendor,
			'waitingthirdparty', 'awaitingthirdparty', 'thirdparty' => self::WaitingThirdParty,

			// Resolution statuses
			'escalated', 'elevated', 'senttolevel2' => self::Escalated,
			'resolved', 'solved', 'completed', 'fixed' => self::Resolved,
			'closed', 'finished', 'done' => self::Closed,
			'reopened', 'reopened', 'reactivated' => self::Reopened,
			'cancelled', 'canceled', 'voided', 'terminated' => self::Cancelled,
			'duplicate', 'duplicateissue' => self::Duplicate,
			'archived', 'filed', 'stored' => self::Archived,
			'deleted', 'removed', 'trashed' => self::Deleted,

			// Additional statuses
			'underreview', 'reviewing', 'beingreviewed' => self::UnderReview,
			'pendingapproval', 'awaitingapproval', 'approvalpending' => self::PendingApproval,
			'scheduled', 'planned', 'scheduledforwork' => self::Scheduled,
			'indevelopment', 'development', 'beingdeveloped' => self::InDevelopment,
			'testing', 'intesting', 'beingtested' => self::Testing,
			'deferred', 'postponed', 'delayed' => self::Deferred,
			'merged', 'combined', 'consolidated' => self::Merged,
			'awaitingfeedback', 'waitingfeedback', 'feedbackpending' => self::AwaitingFeedback,
			'failed', 'unsuccessful', 'notresolved' => self::Failed,
			'blocked', 'stuck', 'impeded' => self::Blocked,

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
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'he', 'he-il' => self::labelsHe(),
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
			// New/active - blue
			self::New, self::Open => '#3b82f6',

			// In progress - yellow/amber
			self::InProgress, self::Testing, self::InDevelopment => '#f59e0b',

			// Waiting/on hold - orange
			self::OnHold, self::WaitingCustomer, self::WaitingVendor,
			self::WaitingThirdParty, self::AwaitingFeedback => '#f97316',

			// Under review - purple
			self::UnderReview, self::PendingApproval, self::Scheduled => '#8b5cf6',

			// Resolved/closed - green
			self::Resolved, self::Closed, self::Merged => '#10b981',

			// Escalated/blocked - red
			self::Escalated, self::Blocked, self::Failed => '#ef4444',

			// Cancelled/duplicate - gray
			self::Cancelled, self::Duplicate, self::Deferred => '#6b7280',

			// Archived/deleted - dark gray
			self::Archived, self::Deleted => '#374151',

			// Reopened - indigo
			self::Reopened => '#6366f1',

			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::New => 'plus-circle',
			self::Open => 'folder-open',
			self::InProgress => 'cogs',
			self::OnHold => 'pause-circle',
			self::WaitingCustomer => 'user-clock',
			self::WaitingVendor => 'truck-loading',
			self::WaitingThirdParty => 'users',
			self::Escalated => 'exclamation-triangle',
			self::Resolved => 'check-circle',
			self::Closed => 'check-double',
			self::Reopened => 'sync',
			self::Cancelled => 'times-circle',
			self::Duplicate => 'copy',
			self::Archived => 'archive',
			self::Deleted => 'trash-alt',
			self::UnderReview => 'search',
			self::PendingApproval => 'clipboard-check',
			self::Scheduled => 'calendar-alt',
			self::InDevelopment => 'code',
			self::Testing => 'flask',
			self::Deferred => 'clock',
			self::Merged => 'code-branch',
			self::AwaitingFeedback => 'comments',
			self::Failed => 'exclamation-circle',
			self::Blocked => 'ban',
		};
	}

	/**
	 * Check if status is active (work still needed)
	 */
	public function isActive(): bool
	{
		return in_array($this, [
			self::New,
			self::Open,
			self::InProgress,
			self::OnHold,
			self::WaitingCustomer,
			self::WaitingVendor,
			self::WaitingThirdParty,
			self::Escalated,
			self::Reopened,
			self::UnderReview,
			self::PendingApproval,
			self::Scheduled,
			self::InDevelopment,
			self::Testing,
			self::AwaitingFeedback,
			self::Blocked,
		]);
	}

	/**
	 * Check if status is resolved/closed
	 */
	public function isResolved(): bool
	{
		return in_array($this, [
			self::Resolved,
			self::Closed,
			self::Merged,
		]);
	}

	/**
	 * Check if status is terminal (no further changes expected)
	 */
	public function isTerminal(): bool
	{
		return in_array($this, [
			self::Closed,
			self::Cancelled,
			self::Duplicate,
			self::Archived,
			self::Deleted,
		]);
	}

	/**
	 * Check if status is waiting for external input
	 */
	public function isWaiting(): bool
	{
		return in_array($this, [
			self::OnHold,
			self::WaitingCustomer,
			self::WaitingVendor,
			self::WaitingThirdParty,
			self::PendingApproval,
			self::AwaitingFeedback,
		]);
	}

	/**
	 * Check if status allows reopening
	 */
	public function canReopen(): bool
	{
		return in_array($this, [
			self::Resolved,
			self::Closed,
			self::Cancelled,
			self::Archived,
		]);
	}

	/**
	 * Get status category
	 */
	public function getCategory(): string
	{
		return match ($this) {
			// Initial/active statuses
			self::New, self::Open => 'initial',

			// Work in progress
			self::InProgress, self::InDevelopment, self::Testing => 'in_progress',

			// Waiting statuses
			self::OnHold, self::WaitingCustomer, self::WaitingVendor,
			self::WaitingThirdParty, self::AwaitingFeedback,
			self::PendingApproval => 'waiting',

			// Review/planning
			self::UnderReview, self::Scheduled, self::Deferred => 'planning',

			// Problem statuses
			self::Escalated, self::Blocked, self::Failed => 'problem',

			// Resolution statuses
			self::Resolved, self::Closed, self::Merged => 'resolved',

			// Reopened
			self::Reopened => 'reopened',

			// Terminal statuses
			self::Cancelled, self::Duplicate, self::Archived, self::Deleted => 'terminal',

			default => 'other',
		};
	}

	/**
	 * Get recommended next statuses
	 */
	public function getNextPossibleStatuses(): array
	{
		return match ($this) {
			self::New => [self::Open, self::InProgress, self::Cancelled],
			self::Open => [self::InProgress, self::OnHold, self::Cancelled],
			self::InProgress => [self::OnHold, self::WaitingCustomer, self::Escalated, self::Resolved],
			self::OnHold => [self::InProgress, self::WaitingCustomer, self::Cancelled],
			self::WaitingCustomer => [self::InProgress, self::Resolved, self::Closed],
			self::WaitingVendor => [self::InProgress, self::OnHold, self::Escalated],
			self::WaitingThirdParty => [self::InProgress, self::Escalated, self::Resolved],
			self::Escalated => [self::InProgress, self::Resolved, self::Closed],
			self::Resolved => [self::Closed, self::Reopened],
			self::Closed => [self::Reopened, self::Archived],
			self::Reopened => [self::InProgress, self::OnHold, self::Escalated],
			self::Cancelled => [self::Reopened, self::Archived],
			self::Duplicate => [self::Closed, self::Archived],
			self::Archived => [],
			self::Deleted => [],
			default => [self::Open, self::InProgress, self::Resolved, self::Closed],
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			self::New->value => 'New',
			self::Open->value => 'Open',
			self::InProgress->value => 'In Progress',
			self::OnHold->value => 'On Hold',
			self::WaitingCustomer->value => 'Waiting for Customer',
			self::WaitingVendor->value => 'Waiting for Vendor',
			self::WaitingThirdParty->value => 'Waiting for Third Party',
			self::Escalated->value => 'Escalated',
			self::Resolved->value => 'Resolved',
			self::Closed->value => 'Closed',
			self::Reopened->value => 'Reopened',
			self::Cancelled->value => 'Cancelled',
			self::Duplicate->value => 'Duplicate',
			self::Archived->value => 'Archived',
			self::Deleted->value => 'Deleted',
			self::UnderReview->value => 'Under Review',
			self::PendingApproval->value => 'Pending Approval',
			self::Scheduled->value => 'Scheduled',
			self::InDevelopment->value => 'In Development',
			self::Testing->value => 'Testing',
			self::Deferred->value => 'Deferred',
			self::Merged->value => 'Merged',
			self::AwaitingFeedback->value => 'Awaiting Feedback',
			self::Failed->value => 'Failed',
			self::Blocked->value => 'Blocked',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			self::New->value => 'Novo',
			self::Open->value => 'Aberto',
			self::InProgress->value => 'Em Andamento',
			self::OnHold->value => 'Em Espera',
			self::WaitingCustomer->value => 'Aguardando Cliente',
			self::WaitingVendor->value => 'Aguardando Fornecedor',
			self::WaitingThirdParty->value => 'Aguardando Terceiros',
			self::Escalated->value => 'Escalado',
			self::Resolved->value => 'Resolvido',
			self::Closed->value => 'Fechado',
			self::Reopened->value => 'Reaberto',
			self::Cancelled->value => 'Cancelado',
			self::Duplicate->value => 'Duplicado',
			self::Archived->value => 'Arquivado',
			self::Deleted->value => 'Excluído',
			self::UnderReview->value => 'Em Revisão',
			self::PendingApproval->value => 'Aguardando Aprovação',
			self::Scheduled->value => 'Agendado',
			self::InDevelopment->value => 'Em Desenvolvimento',
			self::Testing->value => 'Em Teste',
			self::Deferred->value => 'Adiado',
			self::Merged->value => 'Mesclado',
			self::AwaitingFeedback->value => 'Aguardando Feedback',
			self::Failed->value => 'Falhou',
			self::Blocked->value => 'Bloqueado',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			self::New->value => 'Nuevo',
			self::Open->value => 'Abierto',
			self::InProgress->value => 'En Progreso',
			self::OnHold->value => 'En Espera',
			self::WaitingCustomer->value => 'Esperando Cliente',
			self::WaitingVendor->value => 'Esperando Proveedor',
			self::WaitingThirdParty->value => 'Esperando Terceros',
			self::Escalated->value => 'Escalado',
			self::Resolved->value => 'Resuelto',
			self::Closed->value => 'Cerrado',
			self::Reopened->value => 'Reabierto',
			self::Cancelled->value => 'Cancelado',
			self::Duplicate->value => 'Duplicado',
			self::Archived->value => 'Archivado',
			self::Deleted->value => 'Eliminado',
			self::UnderReview->value => 'En Revisión',
			self::PendingApproval->value => 'Pendiente de Aprobación',
			self::Scheduled->value => 'Programado',
			self::InDevelopment->value => 'En Desarrollo',
			self::Testing->value => 'En Prueba',
			self::Deferred->value => 'Diferido',
			self::Merged->value => 'Fusionado',
			self::AwaitingFeedback->value => 'Esperando Comentarios',
			self::Failed->value => 'Fallido',
			self::Blocked->value => 'Bloqueado',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			self::New->value => 'Nouveau',
			self::Open->value => 'Ouvert',
			self::InProgress->value => 'En Cours',
			self::OnHold->value => 'En Attente',
			self::WaitingCustomer->value => 'En Attente du Client',
			self::WaitingVendor->value => 'En Attente du Fournisseur',
			self::WaitingThirdParty->value => 'En Attente de Tiers',
			self::Escalated->value => 'Escaladé',
			self::Resolved->value => 'Résolu',
			self::Closed->value => 'Fermé',
			self::Reopened->value => 'Réouvert',
			self::Cancelled->value => 'Annulé',
			self::Duplicate->value => 'Dupliqué',
			self::Archived->value => 'Archivé',
			self::Deleted->value => 'Supprimé',
			self::UnderReview->value => 'En Révision',
			self::PendingApproval->value => 'En Attente d\'Approbation',
			self::Scheduled->value => 'Planifié',
			self::InDevelopment->value => 'En Développement',
			self::Testing->value => 'En Test',
			self::Deferred->value => 'Différé',
			self::Merged->value => 'Fusionné',
			self::AwaitingFeedback->value => 'En Attente de Retour',
			self::Failed->value => 'Échoué',
			self::Blocked->value => 'Bloqué',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			self::New->value => 'Neu',
			self::Open->value => 'Offen',
			self::InProgress->value => 'In Bearbeitung',
			self::OnHold->value => 'Angehalten',
			self::WaitingCustomer->value => 'Wartet auf Kunden',
			self::WaitingVendor->value => 'Wartet auf Lieferant',
			self::WaitingThirdParty->value => 'Wartet auf Dritte',
			self::Escalated->value => 'Eskaliert',
			self::Resolved->value => 'Gelöst',
			self::Closed->value => 'Geschlossen',
			self::Reopened->value => 'Wiedereröffnet',
			self::Cancelled->value => 'Abgebrochen',
			self::Duplicate->value => 'Duplikat',
			self::Archived->value => 'Archiviert',
			self::Deleted->value => 'Gelöscht',
			self::UnderReview->value => 'In Überprüfung',
			self::PendingApproval->value => 'Genehmigung Ausstehend',
			self::Scheduled->value => 'Geplant',
			self::InDevelopment->value => 'In Entwicklung',
			self::Testing->value => 'In Prüfung',
			self::Deferred->value => 'Zurückgestellt',
			self::Merged->value => 'Zusammengeführt',
			self::AwaitingFeedback->value => 'Wartet auf Feedback',
			self::Failed->value => 'Fehlgeschlagen',
			self::Blocked->value => 'Blockiert',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			self::New->value => 'Nuovo',
			self::Open->value => 'Aperto',
			self::InProgress->value => 'In Corso',
			self::OnHold->value => 'In Attesa',
			self::WaitingCustomer->value => 'In Attesa del Cliente',
			self::WaitingVendor->value => 'In Attesa del Fornitore',
			self::WaitingThirdParty->value => 'In Attesa di Terze Parti',
			self::Escalated->value => 'Escalato',
			self::Resolved->value => 'Risolto',
			self::Closed->value => 'Chiuso',
			self::Reopened->value => 'Riaperto',
			self::Cancelled->value => 'Annullato',
			self::Duplicate->value => 'Duplicato',
			self::Archived->value => 'Archiviato',
			self::Deleted->value => 'Eliminato',
			self::UnderReview->value => 'In Revisione',
			self::PendingApproval->value => 'In Attesa di Approvazione',
			self::Scheduled->value => 'Pianificato',
			self::InDevelopment->value => 'In Sviluppo',
			self::Testing->value => 'In Test',
			self::Deferred->value => 'Rinviato',
			self::Merged->value => 'Unito',
			self::AwaitingFeedback->value => 'In Attesa di Feedback',
			self::Failed->value => 'Fallito',
			self::Blocked->value => 'Bloccato',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			self::New->value => '新規',
			self::Open->value => 'オープン',
			self::InProgress->value => '進行中',
			self::OnHold->value => '保留中',
			self::WaitingCustomer->value => '顧客待ち',
			self::WaitingVendor->value => 'ベンダー待ち',
			self::WaitingThirdParty->value => '第三者待ち',
			self::Escalated->value => 'エスカレーション',
			self::Resolved->value => '解決済み',
			self::Closed->value => 'クローズ',
			self::Reopened->value => '再オープン',
			self::Cancelled->value => 'キャンセル',
			self::Duplicate->value => '重複',
			self::Archived->value => 'アーカイブ',
			self::Deleted->value => '削除済み',
			self::UnderReview->value => 'レビュー中',
			self::PendingApproval->value => '承認待ち',
			self::Scheduled->value => 'スケジュール済み',
			self::InDevelopment->value => '開発中',
			self::Testing->value => 'テスト中',
			self::Deferred->value => '延期',
			self::Merged->value => 'マージ済み',
			self::AwaitingFeedback->value => 'フィードバック待ち',
			self::Failed->value => '失敗',
			self::Blocked->value => 'ブロック',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			self::New->value => 'جديد',
			self::Open->value => 'مفتوح',
			self::InProgress->value => 'قيد التنفيذ',
			self::OnHold->value => 'معلق',
			self::WaitingCustomer->value => 'بانتظار العميل',
			self::WaitingVendor->value => 'بانتظار المورد',
			self::WaitingThirdParty->value => 'بانتظار طرف ثالث',
			self::Escalated->value => 'مصعد',
			self::Resolved->value => 'تم الحل',
			self::Closed->value => 'مغلق',
			self::Reopened->value => 'معاد فتحه',
			self::Cancelled->value => 'ملغي',
			self::Duplicate->value => 'مكرر',
			self::Archived->value => 'مؤرشف',
			self::Deleted->value => 'محذوف',
			self::UnderReview->value => 'قيد المراجعة',
			self::PendingApproval->value => 'بانتظار الموافقة',
			self::Scheduled->value => 'مجدول',
			self::InDevelopment->value => 'قيد التطوير',
			self::Testing->value => 'قيد الاختبار',
			self::Deferred->value => 'مؤجل',
			self::Merged->value => 'مدمج',
			self::AwaitingFeedback->value => 'بانتظار التعليقات',
			self::Failed->value => 'فشل',
			self::Blocked->value => 'محظور',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			self::New->value => 'Ny',
			self::Open->value => 'Åben',
			self::InProgress->value => 'I Gang',
			self::OnHold->value => 'På Hold',
			self::WaitingCustomer->value => 'Venter på Kunde',
			self::WaitingVendor->value => 'Venter på Leverandør',
			self::WaitingThirdParty->value => 'Venter på Tredjepart',
			self::Escalated->value => 'Eskaleret',
			self::Resolved->value => 'Løst',
			self::Closed->value => 'Lukket',
			self::Reopened->value => 'Genåbnet',
			self::Cancelled->value => 'Annulleret',
			self::Duplicate->value => 'Duplikat',
			self::Archived->value => 'Arkiveret',
			self::Deleted->value => 'Slettet',
			self::UnderReview->value => 'Under Gennemgang',
			self::PendingApproval->value => 'Afventer Godkendelse',
			self::Scheduled->value => 'Planlagt',
			self::InDevelopment->value => 'I Udvikling',
			self::Testing->value => 'I Test',
			self::Deferred->value => 'Udskudt',
			self::Merged->value => 'Flettet',
			self::AwaitingFeedback->value => 'Afventer Feedback',
			self::Failed->value => 'Fejlet',
			self::Blocked->value => 'Blokeret',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			self::New->value => 'חדש',
			self::Open->value => 'פתוח',
			self::InProgress->value => 'בתהליך',
			self::OnHold->value => 'בהמתנה',
			self::WaitingCustomer->value => 'מחכה ללקוח',
			self::WaitingVendor->value => 'מחכה לספק',
			self::WaitingThirdParty->value => 'מחכה לצד שלישי',
			self::Escalated->value => 'הועלה',
			self::Resolved->value => 'נפתר',
			self::Closed->value => 'סגור',
			self::Reopened->value => 'נפתח מחדש',
			self::Cancelled->value => 'מבוטל',
			self::Duplicate->value => 'כפול',
			self::Archived->value => 'בארכיון',
			self::Deleted->value => 'נמחק',
			self::UnderReview->value => 'בבדיקה',
			self::PendingApproval->value => 'מחכה לאישור',
			self::Scheduled->value => 'מתוזמן',
			self::InDevelopment->value => 'בפיתוח',
			self::Testing->value => 'בבדיקה',
			self::Deferred->value => 'נדחה',
			self::Merged->value => 'מוזג',
			self::AwaitingFeedback->value => 'מחכה למשוב',
			self::Failed->value => 'נכשל',
			self::Blocked->value => 'חסום',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::New->value => 'Nieuw',
			self::Open->value => 'Open',
			self::InProgress->value => 'In Behandeling',
			self::OnHold->value => 'In Wacht',
			self::WaitingCustomer->value => 'Wacht op Klant',
			self::WaitingVendor->value => 'Wacht op Leverancier',
			self::WaitingThirdParty->value => 'Wacht op Derde',
			self::Escalated->value => 'Geëscaleerd',
			self::Resolved->value => 'Opgelost',
			self::Closed->value => 'Gesloten',
			self::Reopened->value => 'Heropend',
			self::Cancelled->value => 'Geannuleerd',
			self::Duplicate->value => 'Duplicaat',
			self::Archived->value => 'Gearchiveerd',
			self::Deleted->value => 'Verwijderd',
			self::UnderReview->value => 'In Beoordeling',
			self::PendingApproval->value => 'Wacht op Goedkeuring',
			self::Scheduled->value => 'Gepland',
			self::InDevelopment->value => 'In Ontwikkeling',
			self::Testing->value => 'In Test',
			self::Deferred->value => 'Uitgesteld',
			self::Merged->value => 'Samengevoegd',
			self::AwaitingFeedback->value => 'Wacht op Feedback',
			self::Failed->value => 'Mislukt',
			self::Blocked->value => 'Geblokkeerd',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::New->value => 'Nowy',
			self::Open->value => 'Otwarty',
			self::InProgress->value => 'W Trakcie',
			self::OnHold->value => 'Wstrzymany',
			self::WaitingCustomer->value => 'Oczekuje na Klienta',
			self::WaitingVendor->value => 'Oczekuje na Dostawcę',
			self::WaitingThirdParty->value => 'Oczekuje na Trzecią Stronę',
			self::Escalated->value => 'Eskalowany',
			self::Resolved->value => 'Rozwiązany',
			self::Closed->value => 'Zamknięty',
			self::Reopened->value => 'Ponownie Otwarty',
			self::Cancelled->value => 'Anulowany',
			self::Duplicate->value => 'Duplikat',
			self::Archived->value => 'Zarchiwizowany',
			self::Deleted->value => 'Usunięty',
			self::UnderReview->value => 'W Recenzji',
			self::PendingApproval->value => 'Oczekuje na Zatwierdzenie',
			self::Scheduled->value => 'Zaplanowany',
			self::InDevelopment->value => 'W Rozwoju',
			self::Testing->value => 'W Testach',
			self::Deferred->value => 'Odroczony',
			self::Merged->value => 'Połączony',
			self::AwaitingFeedback->value => 'Oczekuje na Opinie',
			self::Failed->value => 'Nieudany',
			self::Blocked->value => 'Zablokowany',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::New->value => 'Новый',
			self::Open->value => 'Открыт',
			self::InProgress->value => 'В Процессе',
			self::OnHold->value => 'На Удержании',
			self::WaitingCustomer->value => 'Ожидает Клиента',
			self::WaitingVendor->value => 'Ожидает Поставщика',
			self::WaitingThirdParty->value => 'Ожидает Третью Сторону',
			self::Escalated->value => 'Эскалирован',
			self::Resolved->value => 'Решён',
			self::Closed->value => 'Закрыт',
			self::Reopened->value => 'Переоткрыт',
			self::Cancelled->value => 'Отменён',
			self::Duplicate->value => 'Дубликат',
			self::Archived->value => 'Архивирован',
			self::Deleted->value => 'Удалён',
			self::UnderReview->value => 'На Рассмотрении',
			self::PendingApproval->value => 'Ожидает Утверждения',
			self::Scheduled->value => 'Запланирован',
			self::InDevelopment->value => 'В Разработке',
			self::Testing->value => 'В Тестировании',
			self::Deferred->value => 'Отложен',
			self::Merged->value => 'Объединён',
			self::AwaitingFeedback->value => 'Ожидает Обратной Связи',
			self::Failed->value => 'Неудачный',
			self::Blocked->value => 'Заблокирован',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::New->value => 'Yeni',
			self::Open->value => 'Açık',
			self::InProgress->value => 'Devam Ediyor',
			self::OnHold->value => 'Beklemede',
			self::WaitingCustomer->value => 'Müşteri Bekleniyor',
			self::WaitingVendor->value => 'Tedarikçi Bekleniyor',
			self::WaitingThirdParty->value => 'Üçüncü Taraf Bekleniyor',
			self::Escalated->value => 'Eskalasyon',
			self::Resolved->value => 'Çözüldü',
			self::Closed->value => 'Kapalı',
			self::Reopened->value => 'Yeniden Açıldı',
			self::Cancelled->value => 'İptal Edildi',
			self::Duplicate->value => 'Çift Kayıt',
			self::Archived->value => 'Arşivlendi',
			self::Deleted->value => 'Silindi',
			self::UnderReview->value => 'İncelemede',
			self::PendingApproval->value => 'Onay Bekleniyor',
			self::Scheduled->value => 'Planlandı',
			self::InDevelopment->value => 'Geliştirmede',
			self::Testing->value => 'Test Ediliyor',
			self::Deferred->value => 'Ertelendi',
			self::Merged->value => 'Birleştirildi',
			self::AwaitingFeedback->value => 'Geri Bildirim Bekleniyor',
			self::Failed->value => 'Başarısız',
			self::Blocked->value => 'Engellendi',
		];
	}

	// Chinese Labels (Simplified)
	public static function labelsZh(): array
	{
		return [
			self::New->value => '新建',
			self::Open->value => '开放',
			self::InProgress->value => '处理中',
			self::OnHold->value => '挂起',
			self::WaitingCustomer->value => '等待客户',
			self::WaitingVendor->value => '等待供应商',
			self::WaitingThirdParty->value => '等待第三方',
			self::Escalated->value => '已升级',
			self::Resolved->value => '已解决',
			self::Closed->value => '已关闭',
			self::Reopened->value => '重新开放',
			self::Cancelled->value => '已取消',
			self::Duplicate->value => '重复',
			self::Archived->value => '已归档',
			self::Deleted->value => '已删除',
			self::UnderReview->value => '审核中',
			self::PendingApproval->value => '待批准',
			self::Scheduled->value => '已安排',
			self::InDevelopment->value => '开发中',
			self::Testing->value => '测试中',
			self::Deferred->value => '已延期',
			self::Merged->value => '已合并',
			self::AwaitingFeedback->value => '等待反馈',
			self::Failed->value => '失败',
			self::Blocked->value => '已阻止',
		];
	}
}
