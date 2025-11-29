<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum PosStatus: string
{
	case Active      = 'active';
	case Busy        = 'busy';
	case Available   = 'available';
	case Closed      = 'closed';
	case Maintenance = 'maintenance';
	case Error       = 'error';
	case Processing  = 'processing';
	case Idle        = 'idle';
	case Suspended   = 'suspended';
	case Other 		   = 'other'; // * only for testing

	public static function values(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function normalize(string|self|null $value): ?self
	{
		if ($value === null)
			return self::Other;
		if ($value instanceof self)
			return $value;
		$v = strtolower(trim($value));
		if ($v === '')
			return null;

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// Active
			'on'          => self::Active,
			'operational' => self::Active,
			'running'     => self::Active,
			'open'        => self::Active,

			// Busy
			'occupied'    => self::Busy,
			'working'     => self::Busy,
			'in use'      => self::Busy,
			'in_use'      => self::Busy,

			// Available
			'ready'       => self::Available,
			'free'        => self::Available,

			// Closed
			'shutdown'    => self::Closed,
			'ended'       => self::Closed,
			'finished'    => self::Closed,

			// Maintenance
			'maintain'    => self::Maintenance,
			'repair'      => self::Maintenance,
			'servicing'   => self::Maintenance,
			'updating'    => self::Maintenance,

			// Error
			'failed'      => self::Error,
			'fault'       => self::Error,
			'problem'     => self::Error,
			'crash'       => self::Error,

			// Processing
			'calculating' => self::Processing,

			// Idle
			'waiting'     => self::Idle,
			'standby'     => self::Idle,
			'sleep'       => self::Idle,

			// Suspended
			'paused'      => self::Suspended,
			'hold'        => self::Suspended,
			'stopped'     => self::Suspended,
			'blocked'     => self::Suspended,
		];

		return $map[$v] ?? null;
	}

	public function label(): string
	{
		return match ($this) {
			self::Active      => 'Active',
			self::Busy        => 'Busy',
			self::Available   => 'Available',
			self::Closed      => 'Closed',
			self::Maintenance => 'Maintenance',
			self::Error       => 'Error',
			self::Processing  => 'Processing',
			self::Idle        => 'Idle',
			self::Suspended   => 'Suspended',
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
			self::Active->value      => 'Ativo',
			self::Busy->value        => 'Ocupado',
			self::Available->value   => 'Disponível',
			self::Closed->value      => 'Fechado',
			self::Maintenance->value => 'Manutenção',
			self::Error->value       => 'Erro',
			self::Processing->value  => 'Processando',
			self::Idle->value        => 'Inativo',
			self::Suspended->value   => 'Suspenso',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Active->value      => 'Active',
			self::Busy->value        => 'Busy',
			self::Available->value   => 'Available',
			self::Closed->value      => 'Closed',
			self::Maintenance->value => 'Maintenance',
			self::Error->value       => 'Error',
			self::Processing->value  => 'Processing',
			self::Idle->value        => 'Idle',
			self::Suspended->value   => 'Suspended',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Active->value      => 'Activo',
			self::Busy->value        => 'Ocupado',
			self::Available->value   => 'Disponible',
			self::Closed->value      => 'Cerrado',
			self::Maintenance->value => 'Mantenimiento',
			self::Error->value       => 'Error',
			self::Processing->value  => 'Procesando',
			self::Idle->value        => 'Inactivo',
			self::Suspended->value   => 'Suspendido',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Active->value      => 'نشط',
			self::Busy->value        => 'مشغول',
			self::Available->value   => 'متاح',
			self::Closed->value      => 'مغلق',
			self::Maintenance->value => 'صيانة',
			self::Error->value       => 'خطأ',
			self::Processing->value  => 'معالجة',
			self::Idle->value        => 'خامل',
			self::Suspended->value   => 'موقوف',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Active->value      => 'Aktiv',
			self::Busy->value        => 'Optaget',
			self::Available->value   => 'Tilgængelig',
			self::Closed->value      => 'Lukket',
			self::Maintenance->value => 'Vedligeholdelse',
			self::Error->value       => 'Fejl',
			self::Processing->value  => 'Behandler',
			self::Idle->value        => 'Inaktiv',
			self::Suspended->value   => 'Suspenderet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Active->value      => 'Aktiv',
			self::Busy->value        => 'Beschäftigt',
			self::Available->value   => 'Verfügbar',
			self::Closed->value      => 'Geschlossen',
			self::Maintenance->value => 'Wartung',
			self::Error->value       => 'Fehler',
			self::Processing->value  => 'Verarbeitung',
			self::Idle->value        => 'Inaktiv',
			self::Suspended->value   => 'Ausgesetzt',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Active->value      => 'Actif',
			self::Busy->value        => 'Occupé',
			self::Available->value   => 'Disponible',
			self::Closed->value      => 'Fermé',
			self::Maintenance->value => 'Maintenance',
			self::Error->value       => 'Erreur',
			self::Processing->value  => 'Traitement',
			self::Idle->value        => 'Inactif',
			self::Suspended->value   => 'Suspendu',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Active->value      => 'פעיל',
			self::Busy->value        => 'תפוס',
			self::Available->value   => 'זמין',
			self::Closed->value      => 'סגור',
			self::Maintenance->value => 'תחזוקה',
			self::Error->value       => 'שגיאה',
			self::Processing->value  => 'מעבד',
			self::Idle->value        => 'לא פעיל',
			self::Suspended->value   => 'מושהה',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Active->value      => 'Attivo',
			self::Busy->value        => 'Occupato',
			self::Available->value   => 'Disponibile',
			self::Closed->value      => 'Chiuso',
			self::Maintenance->value => 'Manutenzione',
			self::Error->value       => 'Errore',
			self::Processing->value  => 'Elaborazione',
			self::Idle->value        => 'Inattivo',
			self::Suspended->value   => 'Sospeso',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Active->value      => 'アクティブ',
			self::Busy->value        => 'ビジー',
			self::Available->value   => '利用可能',
			self::Closed->value      => '閉鎖',
			self::Maintenance->value => 'メンテナンス',
			self::Error->value       => 'エラー',
			self::Processing->value  => '処理中',
			self::Idle->value        => 'アイドル',
			self::Suspended->value   => '一時停止',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Active->value      => 'Actief',
			self::Busy->value        => 'Bezet',
			self::Available->value   => 'Beschikbaar',
			self::Closed->value      => 'Gesloten',
			self::Maintenance->value => 'Onderhoud',
			self::Error->value       => 'Fout',
			self::Processing->value  => 'Verwerken',
			self::Idle->value        => 'Inactief',
			self::Suspended->value   => 'Opgeschort',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Active->value      => 'Aktywny',
			self::Busy->value        => 'Zajęty',
			self::Available->value   => 'Dostępny',
			self::Closed->value      => 'Zamknięty',
			self::Maintenance->value => 'Konserwacja',
			self::Error->value       => 'Błąd',
			self::Processing->value  => 'Przetwarzanie',
			self::Idle->value        => 'Bezczynny',
			self::Suspended->value   => 'Zawieszony',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Active->value      => 'Активный',
			self::Busy->value        => 'Занят',
			self::Available->value   => 'Доступен',
			self::Closed->value      => 'Закрыт',
			self::Maintenance->value => 'Обслуживание',
			self::Error->value       => 'Ошибка',
			self::Processing->value  => 'Обработка',
			self::Idle->value        => 'Неактивен',
			self::Suspended->value   => 'Приостановлен',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Active->value      => 'Aktif',
			self::Busy->value        => 'Meşgul',
			self::Available->value   => 'Müsait',
			self::Closed->value      => 'Kapalı',
			self::Maintenance->value => 'Bakım',
			self::Error->value       => 'Hata',
			self::Processing->value  => 'İşleniyor',
			self::Idle->value        => 'Boşta',
			self::Suspended->value   => 'Askıya Alındı',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Active->value      => '活跃',
			self::Busy->value        => '忙碌',
			self::Available->value   => '可用',
			self::Closed->value      => '关闭',
			self::Maintenance->value => '维护',
			self::Error->value       => '错误',
			self::Processing->value  => '处理中',
			self::Idle->value        => '空闲',
			self::Suspended->value   => '暂停',
		];
	}

	// Additional helper methods for business logic
	public function isOperational(): bool
	{
		return match ($this) {
			self::Active, self::Available, self::Idle, self::Processing => true,
			default => false,
		};
	}

	public function isProblematic(): bool
	{
		return match ($this) {
			self::Error, self::Maintenance, self::Suspended => true,
			default => false,
		};
	}

	public function canProcessTransactions(): bool
	{
		return match ($this) {
			self::Active, self::Available, self::Processing => true,
			default => false,
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Active, self::Available => 'green',
			self::Closed, self::Suspended => 'red',
			self::Busy, self::Processing => 'orange',
			self::Maintenance => 'blue',
			self::Error => 'red',
			self::Idle => 'gray',
		};
	}
}
