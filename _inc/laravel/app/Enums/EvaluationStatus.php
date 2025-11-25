<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum EvaluationStatus: string
{
	case Draft     = 'draft';
	case Pending   = 'pending';
	case Active    = 'active';
	case Suspended = 'suspended';
	case Completed = 'completed';
	case Cancelled = 'cancelled';
	case Expired   = 'expired';
	case Archived  = 'archived';
	case Undefined = 'undefined';
	case Accept    = 'accept';
	case Decline   = 'decline';

	public static function normalize(null|string|BackedEnum $v): self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;

		$k = strtolower(trim((string) $v));

		$aliases = [
			'canceled'    => 'cancelled',
			'complete'    => 'completed',
			'finished'    => 'completed',
			'in_progress' => 'active',
			'rascunho'    => 'draft',
			'pendente'    => 'pending',
			'ativo'       => 'active',
			'cancelado'   => 'cancelled',
			'concluido'   => 'completed',
			'expirado'    => 'expired',
			'arquivado'   => 'archived',
			'aceito'      => 'accept',
			'recusado'    => 'decline',
		];

		$k = $aliases[$k] ?? $k;

		return self::tryFrom($k) ?? self::Undefined;
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
			self::Draft->value     => 'Rascunho',
			self::Pending->value   => 'Pendente',
			self::Active->value    => 'Ativo',
			self::Suspended->value => 'Suspenso',
			self::Completed->value => 'Concluído',
			self::Cancelled->value => 'Cancelado',
			self::Expired->value   => 'Expirado',
			self::Archived->value  => 'Arquivado',
			self::Undefined->value => 'Indefinido',
			self::Accept->value    => 'Aceitar',
			self::Decline->value   => 'Recusar',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Draft->value     => 'Draft',
			self::Pending->value   => 'Pending',
			self::Active->value    => 'Active',
			self::Suspended->value => 'Suspended',
			self::Completed->value => 'Completed',
			self::Cancelled->value => 'Cancelled',
			self::Expired->value   => 'Expired',
			self::Archived->value  => 'Archived',
			self::Undefined->value => 'Undefined',
			self::Accept->value    => 'Accept',
			self::Decline->value   => 'Decline',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Draft->value     => 'Borrador',
			self::Pending->value   => 'Pendiente',
			self::Active->value    => 'Activo',
			self::Suspended->value => 'Suspendido',
			self::Completed->value => 'Completado',
			self::Cancelled->value => 'Cancelado',
			self::Expired->value   => 'Expirado',
			self::Archived->value  => 'Archivado',
			self::Undefined->value => 'Indefinido',
			self::Accept->value    => 'Aceptar',
			self::Decline->value   => 'Rechazar',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Draft->value     => 'مسودة',
			self::Pending->value   => 'قيد الانتظار',
			self::Active->value    => 'نشط',
			self::Suspended->value => 'معلقة',
			self::Completed->value => 'مكتمل',
			self::Cancelled->value => 'ملغى',
			self::Expired->value   => 'منتهي الصلاحية',
			self::Archived->value  => 'مؤرشف',
			self::Undefined->value => 'غير محدد',
			self::Accept->value    => 'قبول',
			self::Decline->value   => 'رفض',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Draft->value     => 'Kladde',
			self::Pending->value   => 'Afventer',
			self::Active->value    => 'Aktiv',
			self::Suspended->value => 'Suspenderet',
			self::Completed->value => 'Afsluttet',
			self::Cancelled->value => 'Annulleret',
			self::Expired->value   => 'Udløbet',
			self::Archived->value  => 'Arkiveret',
			self::Undefined->value => 'Udefineret',
			self::Accept->value    => 'Acceptere',
			self::Decline->value   => 'Afvise',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Draft->value     => 'Entwurf',
			self::Pending->value   => 'Ausstehend',
			self::Active->value    => 'Aktiv',
			self::Suspended->value => 'Ausgesetzt',
			self::Completed->value => 'Abgeschlossen',
			self::Cancelled->value => 'Abgebrochen',
			self::Expired->value   => 'Abgelaufen',
			self::Archived->value  => 'Archiviert',
			self::Undefined->value => 'Undefiniert',
			self::Accept->value    => 'Akzeptieren',
			self::Decline->value   => 'Ablehnen',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Draft->value     => 'Brouillon',
			self::Pending->value   => 'En Attente',
			self::Active->value    => 'Actif',
			self::Suspended->value => 'Suspendu',
			self::Completed->value => 'Terminé',
			self::Cancelled->value => 'Annulé',
			self::Expired->value   => 'Expiré',
			self::Archived->value  => 'Archivé',
			self::Undefined->value => 'Indéfini',
			self::Accept->value    => 'Accepter',
			self::Decline->value   => 'Refuser',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Draft->value     => 'טיוטה',
			self::Pending->value   => 'ממתין',
			self::Active->value    => 'פעיל',
			self::Suspended->value => 'מושעה',
			self::Completed->value => 'הושלם',
			self::Cancelled->value => 'בוטל',
			self::Expired->value   => 'פג תוקף',
			self::Archived->value  => 'בארכיון',
			self::Undefined->value => 'לא מוגדר',
			self::Accept->value    => 'לקבל',
			self::Decline->value   => 'לדחות',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Draft->value     => 'Bozza',
			self::Pending->value   => 'In Sospeso',
			self::Active->value    => 'Attivo',
			self::Suspended->value => 'Sospeso',
			self::Completed->value => 'Completato',
			self::Cancelled->value => 'Annullato',
			self::Expired->value   => 'Scaduto',
			self::Archived->value  => 'Archiviato',
			self::Undefined->value => 'Indefinito',
			self::Accept->value    => 'Accettare',
			self::Decline->value   => 'Rifiutare',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Draft->value     => '下書き',
			self::Pending->value   => '保留中',
			self::Active->value    => 'アクティブ',
			self::Suspended->value => '停止',
			self::Completed->value => '完了',
			self::Cancelled->value => 'キャンセル',
			self::Expired->value   => '期限切れ',
			self::Archived->value  => 'アーカイブ',
			self::Undefined->value => '未定義',
			self::Accept->value    => '受け入れる',
			self::Decline->value   => '拒否',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Draft->value     => 'Concept',
			self::Pending->value   => 'In Afwachting',
			self::Active->value    => 'Actief',
			self::Suspended->value => 'Geschorst',
			self::Completed->value => 'Voltooid',
			self::Cancelled->value => 'Geannuleerd',
			self::Expired->value   => 'Verlopen',
			self::Archived->value  => 'Gearchiveerd',
			self::Undefined->value => 'Ongedefinieerd',
			self::Accept->value    => 'Accepteren',
			self::Decline->value   => 'Weigeren',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Draft->value     => 'Szkic',
			self::Pending->value   => 'Oczekujący',
			self::Active->value    => 'Aktywny',
			self::Suspended->value => 'Zawieszony',
			self::Completed->value => 'Zakończony',
			self::Cancelled->value => 'Anulowany',
			self::Expired->value   => 'Przedawniony',
			self::Archived->value  => 'Zarchiwizowany',
			self::Undefined->value => 'Niezdefiniowany',
			self::Accept->value    => 'Zaakceptować',
			self::Decline->value   => 'Odrzucić',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Draft->value     => 'Черновик',
			self::Pending->value   => 'В Ожидании',
			self::Active->value    => 'Активный',
			self::Suspended->value => 'Приостановлен',
			self::Completed->value => 'Завершен',
			self::Cancelled->value => 'Отменен',
			self::Expired->value   => 'Истек',
			self::Archived->value  => 'Архивирован',
			self::Undefined->value => 'Неопределен',
			self::Accept->value    => 'Принять',
			self::Decline->value   => 'Отклонить',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Draft->value     => 'Taslak',
			self::Pending->value   => 'Beklemede',
			self::Active->value    => 'Aktif',
			self::Suspended->value => 'Askıya Alındı',
			self::Completed->value => 'Tamamlandı',
			self::Cancelled->value => 'İptal Edildi',
			self::Expired->value   => 'Süresi Doldu',
			self::Archived->value  => 'Arşivlendi',
			self::Undefined->value => 'Tanımsız',
			self::Accept->value    => 'Kabul Etmek',
			self::Decline->value   => 'Reddetmek',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Draft->value     => '草稿',
			self::Pending->value   => '待处理',
			self::Active->value    => '活跃',
			self::Suspended->value => '已暂停',
			self::Completed->value => '已完成',
			self::Cancelled->value => '已取消',
			self::Expired->value   => '已过期',
			self::Archived->value  => '已归档',
			self::Undefined->value => '未定义',
			self::Accept->value    => '接受',
			self::Decline->value   => '拒绝',
		];
	}
}
