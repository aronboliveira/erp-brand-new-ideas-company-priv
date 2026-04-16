<?php

namespace App\Enums;

use BackedEnum;
use App\Config\Constants\DatabaseConstants;

enum PaymentStatus: string
{
	case Pending           = 'pending';
	case Processing        = 'processing';
	case Authorized        = 'authorized';
	case Completed         = 'completed';
	case Success           = 'success';
	case Failed            = 'failed';
	case Cancelled         = 'cancelled';
	case Refunded          = 'refunded';
	case PartiallyRefunded = 'partially_refunded';
	case Expired           = 'expired';
	case Declined          = 'declined';
	case Disputed          = 'disputed';
	case Undefined         = 'undefined';

	public static function normalize(null|string|BackedEnum $v): self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;

		$k = strtolower(trim((string) $v));

		$aliases = [
			'awaiting'            => 'pending',
			'waiting'             => 'pending',
			'in_progress'         => 'processing',
			'approved'            => 'authorized',
			'success'             => 'completed',
			'successful'          => 'completed',
			'paid'                => 'completed',
			'complete'            => 'completed',
			'error'               => 'failed',
			'failure'             => 'failed',
			'canceled'            => 'cancelled',
			'refund'              => 'refunded',
			'partial_refund'      => 'partially_refunded',
			'chargeback'          => 'disputed',
			'rejected'            => 'declined',
			'pendente'            => 'pending',
			'processando'         => 'processing',
			'autorizado'          => 'authorized',
			'concluido'           => 'completed',
			'pago'                => 'completed',
			'falhou'              => 'failed',
			'cancelado'           => 'cancelled',
			'reembolsado'         => 'refunded',
			'parcialmente_reembolsado' => 'partially_refunded',
			'expirado'            => 'expired',
			'recusado'            => 'declined',
			'contestado'          => 'disputed',
		];

		$k = $aliases[$k] ?? $k;

		return self::tryFrom($k) ?? self::Undefined;
	}

	public static function getIndex(?string $case): int
	{
		return match ($case) {
			self::Pending->value => 0,
			self::Authorized->value => 2,
			self::Completed->value => 3,
			self::Failed->value => 4,
			self::Cancelled->value => 5,
			self::Refunded->value => 6,
			self::PartiallyRefunded->value => 7,
			self::Expired->value => 8,
			self::Declined->value => 9,
			self::Disputed->value => 10,
			self::Undefined->value => 11,
			default => 1
		};
	}

	public static function getAllIndexes(): array
	{
		return array_values(
			array_map(fn($value) => self::getIndex($value), self::values())
		);
	}

	public static function values(): array
	{
		return [
			self::Pending->value,
			self::Processing->value,
			self::Authorized->value,
			self::Completed->value,
			self::Success->value,
			self::Failed->value,
			self::Cancelled->value,
			self::Refunded->value,
			self::PartiallyRefunded->value,
			self::Expired->value,
			self::Declined->value,
			self::Disputed->value,
			self::Undefined->value,
		];
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
			self::Pending->value           => 'Pendente',
			self::Processing->value        => 'Processando',
			self::Authorized->value        => 'Autorizado',
			self::Completed->value         => 'Concluído',
			self::Failed->value            => 'Falhou',
			self::Cancelled->value         => 'Cancelado',
			self::Refunded->value          => 'Reembolsado',
			self::PartiallyRefunded->value => 'Parcialmente Reembolsado',
			self::Expired->value           => 'Expirado',
			self::Declined->value          => 'Recusado',
			self::Disputed->value          => 'Contestado',
			self::Undefined->value         => 'Indefinido',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Pending->value           => 'Pending',
			self::Processing->value        => 'Processing',
			self::Authorized->value        => 'Authorized',
			self::Completed->value         => 'Completed',
			self::Failed->value            => 'Failed',
			self::Cancelled->value         => 'Cancelled',
			self::Refunded->value          => 'Refunded',
			self::PartiallyRefunded->value => 'Partially Refunded',
			self::Expired->value           => 'Expired',
			self::Declined->value          => 'Declined',
			self::Disputed->value          => 'Disputed',
			self::Undefined->value         => 'Undefined',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Pending->value           => 'Pendiente',
			self::Processing->value        => 'Procesando',
			self::Authorized->value        => 'Autorizado',
			self::Completed->value         => 'Completado',
			self::Failed->value            => 'Fallido',
			self::Cancelled->value         => 'Cancelado',
			self::Refunded->value          => 'Reembolsado',
			self::PartiallyRefunded->value => 'Parcialmente Reembolsado',
			self::Expired->value           => 'Expirado',
			self::Declined->value          => 'Rechazado',
			self::Disputed->value          => 'Disputado',
			self::Undefined->value         => 'Indefinido',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Pending->value           => 'قيد الانتظار',
			self::Processing->value        => 'جاري المعالجة',
			self::Authorized->value        => 'مصرح به',
			self::Completed->value         => 'مكتمل',
			self::Failed->value            => 'فشل',
			self::Cancelled->value         => 'ملغى',
			self::Refunded->value          => 'مسترد',
			self::PartiallyRefunded->value => 'مسترد جزئيًا',
			self::Expired->value           => 'منتهي الصلاحية',
			self::Declined->value          => 'مرفوض',
			self::Disputed->value          => 'متنازع عليه',
			self::Undefined->value         => 'غير محدد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Pending->value           => 'Afventer',
			self::Processing->value        => 'Behandler',
			self::Authorized->value        => 'Autoriseret',
			self::Completed->value         => 'Afsluttet',
			self::Failed->value            => 'Fejlet',
			self::Cancelled->value         => 'Annulleret',
			self::Refunded->value          => 'Refunderet',
			self::PartiallyRefunded->value => 'Delvist Refunderet',
			self::Expired->value           => 'Udløbet',
			self::Declined->value          => 'Afvist',
			self::Disputed->value          => 'Bestridt',
			self::Undefined->value         => 'Udefineret',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Pending->value           => 'Ausstehend',
			self::Processing->value        => 'In Bearbeitung',
			self::Authorized->value        => 'Autorisiert',
			self::Completed->value         => 'Abgeschlossen',
			self::Failed->value            => 'Fehlgeschlagen',
			self::Cancelled->value         => 'Abgebrochen',
			self::Refunded->value          => 'Erstattet',
			self::PartiallyRefunded->value => 'Teilweise Erstattet',
			self::Expired->value           => 'Abgelaufen',
			self::Declined->value          => 'Abgelehnt',
			self::Disputed->value          => 'Bestritten',
			self::Undefined->value         => 'Undefiniert',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Pending->value           => 'En Attente',
			self::Processing->value        => 'En Cours',
			self::Authorized->value        => 'Autorisé',
			self::Completed->value         => 'Terminé',
			self::Failed->value            => 'Échoué',
			self::Cancelled->value         => 'Annulé',
			self::Refunded->value          => 'Remboursé',
			self::PartiallyRefunded->value => 'Partiellement Remboursé',
			self::Expired->value           => 'Expiré',
			self::Declined->value          => 'Refusé',
			self::Disputed->value          => 'Contesté',
			self::Undefined->value         => 'Indéfini',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Pending->value           => 'ממתין',
			self::Processing->value        => 'בעיבוד',
			self::Authorized->value        => 'מאושר',
			self::Completed->value         => 'הושלם',
			self::Failed->value            => 'נכשל',
			self::Cancelled->value         => 'בוטל',
			self::Refunded->value          => 'הוחזר',
			self::PartiallyRefunded->value => 'הוחזר חלקית',
			self::Expired->value           => 'פג תוקף',
			self::Declined->value          => 'נדחה',
			self::Disputed->value          => 'שנוי במחלוקת',
			self::Undefined->value         => 'לא מוגדר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Pending->value           => 'In Sospeso',
			self::Processing->value        => 'In Elaborazione',
			self::Authorized->value        => 'Autorizzato',
			self::Completed->value         => 'Completato',
			self::Failed->value            => 'Fallito',
			self::Cancelled->value         => 'Annullato',
			self::Refunded->value          => 'Rimborsato',
			self::PartiallyRefunded->value => 'Parzialmente Rimborsato',
			self::Expired->value           => 'Scaduto',
			self::Declined->value          => 'Rifiutato',
			self::Disputed->value          => 'Contestato',
			self::Undefined->value         => 'Indefinito',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Pending->value           => '保留中',
			self::Processing->value        => '処理中',
			self::Authorized->value        => '承認済み',
			self::Completed->value         => '完了',
			self::Failed->value            => '失敗',
			self::Cancelled->value         => 'キャンセル',
			self::Refunded->value          => '返金済み',
			self::PartiallyRefunded->value => '一部返金済み',
			self::Expired->value           => '期限切れ',
			self::Declined->value          => '拒否',
			self::Disputed->value          => '係争中',
			self::Undefined->value         => '未定義',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Pending->value           => 'In Afwachting',
			self::Processing->value        => 'Verwerken',
			self::Authorized->value        => 'Geautoriseerd',
			self::Completed->value         => 'Voltooid',
			self::Failed->value            => 'Mislukt',
			self::Cancelled->value         => 'Geannuleerd',
			self::Refunded->value          => 'Terugbetaald',
			self::PartiallyRefunded->value => 'Gedeeltelijk Terugbetaald',
			self::Expired->value           => 'Verlopen',
			self::Declined->value          => 'Afgewezen',
			self::Disputed->value          => 'Betwist',
			self::Undefined->value         => 'Ongedefinieerd',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Pending->value           => 'Oczekujący',
			self::Processing->value        => 'Przetwarzanie',
			self::Authorized->value        => 'Autoryzowany',
			self::Completed->value         => 'Zakończony',
			self::Failed->value            => 'Niepowodzenie',
			self::Cancelled->value         => 'Anulowany',
			self::Refunded->value          => 'Zwrócony',
			self::PartiallyRefunded->value => 'Częściowo Zwrócony',
			self::Expired->value           => 'Przedawniony',
			self::Declined->value          => 'Odrzucony',
			self::Disputed->value          => 'Sporny',
			self::Undefined->value         => 'Niezdefiniowany',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Pending->value           => 'В Ожидании',
			self::Processing->value        => 'Обработка',
			self::Authorized->value        => 'Авторизован',
			self::Completed->value         => 'Завершен',
			self::Failed->value            => 'Не Удалось',
			self::Cancelled->value         => 'Отменен',
			self::Refunded->value          => 'Возвращен',
			self::PartiallyRefunded->value => 'Частично Возвращен',
			self::Expired->value           => 'Истек',
			self::Declined->value          => 'Отклонен',
			self::Disputed->value          => 'Оспаривается',
			self::Undefined->value         => 'Неопределен',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Pending->value           => 'Beklemede',
			self::Processing->value        => 'İşleniyor',
			self::Authorized->value        => 'Onaylandı',
			self::Completed->value         => 'Tamamlandı',
			self::Failed->value            => 'Başarısız',
			self::Cancelled->value         => 'İptal Edildi',
			self::Refunded->value          => 'İade Edildi',
			self::PartiallyRefunded->value => 'Kısmen İade Edildi',
			self::Expired->value           => 'Süresi Doldu',
			self::Declined->value          => 'Reddedildi',
			self::Disputed->value          => 'İtiraz Edildi',
			self::Undefined->value         => 'Tanımsız',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Pending->value           => '待处理',
			self::Processing->value        => '处理中',
			self::Authorized->value        => '已授权',
			self::Completed->value         => '已完成',
			self::Failed->value            => '失败',
			self::Cancelled->value         => '已取消',
			self::Refunded->value          => '已退款',
			self::PartiallyRefunded->value => '部分退款',
			self::Expired->value           => '已过期',
			self::Declined->value          => '已拒绝',
			self::Disputed->value          => '有争议',
			self::Undefined->value         => '未定义',
		];
	}
}
