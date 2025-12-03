<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum LogType: string
{
	case Error = 'error';
	case Warning = 'warning';
	case Info = 'info';
	case Debug = 'debug';
	case Critical = 'critical';
	case Alert = 'alert';
	case Emergency = 'emergency';
	case Notice = 'notice';
	case Security = 'security';
	case Access = 'access';
	case Query = 'query';
	case Job = 'job';
	case Event = 'event';
	case Audit = 'audit';
	case Performance = 'performance';
	case Api = 'api';
	case Database = 'database';
	case Authentication = 'authentication';
	case Authorization = 'authorization';
	case Validation = 'validation';
	case Mail = 'mail';
	case Notification = 'notification';
	case Cache = 'cache';
	case Session = 'session';
	case Queue = 'queue';
	case Schedule = 'schedule';
	case Console = 'console';
	case System = 'system';
	case Other = 'other';

	public static function normalize(?string $value): self
	{
		if ($value === null) {
			return self::Other;
		}

		$v = strtolower(trim($value));

		// If it's already a valid case value, return it directly
		foreach (self::cases() as $case) {
			if ($case->value === $v) {
				return $case;
			}
		}

		$map = [
			// Error
			'error'         => self::Error,
			'erro'          => self::Error,
			'exception'     => self::Error,
			'exceção'       => self::Error,
			'falha'         => self::Error,
			'failure'       => self::Error,

			// Warning
			'warning'       => self::Warning,
			'warn'          => self::Warning,
			'alerta'        => self::Warning,
			'advertência'   => self::Warning,

			// Info
			'info'          => self::Info,
			'information'   => self::Info,
			'informação'    => self::Info,
			'informacion'   => self::Info,

			// Debug
			'debug'         => self::Debug,
			'depuração'     => self::Debug,
			'depuracion'    => self::Debug,

			// Critical
			'critical'      => self::Critical,
			'critico'       => self::Critical,
			'crítico'       => self::Critical,

			// Alert
			'alert'         => self::Alert,
			'alerta'        => self::Alert,

			// Emergency
			'emergency'     => self::Emergency,
			'emergencia'    => self::Emergency,
			'emergência'    => self::Emergency,

			// Notice
			'notice'        => self::Notice,
			'noticia'       => self::Notice,
			'notícia'       => self::Notice,

			// Security
			'security'      => self::Security,
			'segurança'     => self::Security,
			'seguranca'     => self::Security,
			'security_log'  => self::Security,

			// Access
			'access'        => self::Access,
			'acesso'        => self::Access,
			'access_log'    => self::Access,
			'http'          => self::Access,
			'request'       => self::Access,

			// Query
			'query'         => self::Query,
			'consulta'      => self::Query,
			'sql'           => self::Query,
			'database_query' => self::Query,

			// Job
			'job'           => self::Job,
			'trabalho'      => self::Job,
			'queue_job'     => self::Job,
			'queue'         => self::Job,

			// Event
			'event'         => self::Event,
			'evento'        => self::Event,

			// Audit
			'audit'         => self::Audit,
			'auditoria'     => self::Audit,
			'audit_log'     => self::Audit,

			// Performance
			'performance'   => self::Performance,
			'desempenho'    => self::Performance,
			'performance_log' => self::Performance,

			// API
			'api'           => self::Api,
			'api_log'       => self::Api,
			'rest'          => self::Api,
			'graphql'       => self::Api,

			// Database
			'database'      => self::Database,
			'banco de dados' => self::Database,
			'db'            => self::Database,

			// Authentication
			'authentication' => self::Authentication,
			'auth'          => self::Authentication,
			'autenticacao'  => self::Authentication,
			'autenticación' => self::Authentication,
			'login'         => self::Authentication,
			'logout'        => self::Authentication,

			// Authorization
			'authorization' => self::Authorization,
			'autorizacao'   => self::Authorization,
			'autorización'  => self::Authorization,
			'permission'    => self::Authorization,

			// Validation
			'validation'    => self::Validation,
			'validação'     => self::Validation,
			'validacion'    => self::Validation,

			// Mail
			'mail'          => self::Mail,
			'email'         => self::Mail,
			'correo'        => self::Mail,
			'correio'       => self::Mail,

			// Notification
			'notification'  => self::Notification,
			'notificação'   => self::Notification,
			'notificacion'  => self::Notification,

			// Cache
			'cache'         => self::Cache,
			'caching'       => self::Cache,
			'cached'        => self::Cache,

			// Session
			'session'       => self::Session,
			'sessão'        => self::Session,
			'sesion'        => self::Session,

			// Queue
			'queue'         => self::Queue,
			'fila'          => self::Queue,
			'cola'          => self::Queue,

			// Schedule
			'schedule'      => self::Schedule,
			'agendamento'   => self::Schedule,
			'agendamiento'  => self::Schedule,
			'cron'          => self::Schedule,

			// Console
			'console'       => self::Console,
			'command'       => self::Console,
			'comando'       => self::Console,

			// System
			'system'        => self::System,
			'sistema'       => self::System,
			'system_log'    => self::System,

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

	public function label(): string
	{
		return match ($this) {
			self::Error           => 'Error',
			self::Warning         => 'Warning',
			self::Info            => 'Info',
			self::Debug           => 'Debug',
			self::Critical        => 'Critical',
			self::Alert           => 'Alert',
			self::Emergency       => 'Emergency',
			self::Notice          => 'Notice',
			self::Security        => 'Security',
			self::Access          => 'Access',
			self::Query           => 'Query',
			self::Job             => 'Job',
			self::Event           => 'Event',
			self::Audit           => 'Audit',
			self::Performance     => 'Performance',
			self::Api             => 'API',
			self::Database        => 'Database',
			self::Authentication  => 'Authentication',
			self::Authorization   => 'Authorization',
			self::Validation      => 'Validation',
			self::Mail            => 'Mail',
			self::Notification    => 'Notification',
			self::Cache           => 'Cache',
			self::Session         => 'Session',
			self::Queue           => 'Queue',
			self::Schedule        => 'Schedule',
			self::Console         => 'Console',
			self::System          => 'System',
			self::Other           => 'Other',
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
			self::Error->value           => 'Erro',
			self::Warning->value         => 'Aviso',
			self::Info->value            => 'Informação',
			self::Debug->value           => 'Depuração',
			self::Critical->value        => 'Crítico',
			self::Alert->value           => 'Alerta',
			self::Emergency->value       => 'Emergência',
			self::Notice->value          => 'Notícia',
			self::Security->value        => 'Segurança',
			self::Access->value          => 'Acesso',
			self::Query->value           => 'Consulta',
			self::Job->value             => 'Trabalho',
			self::Event->value           => 'Evento',
			self::Audit->value           => 'Auditoria',
			self::Performance->value     => 'Desempenho',
			self::Api->value             => 'API',
			self::Database->value        => 'Banco de Dados',
			self::Authentication->value  => 'Autenticação',
			self::Authorization->value   => 'Autorização',
			self::Validation->value      => 'Validação',
			self::Mail->value            => 'E-mail',
			self::Notification->value    => 'Notificação',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Sessão',
			self::Queue->value           => 'Fila',
			self::Schedule->value        => 'Agendamento',
			self::Console->value         => 'Console',
			self::System->value          => 'Sistema',
			self::Other->value           => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Error->value           => 'Error',
			self::Warning->value         => 'Warning',
			self::Info->value            => 'Info',
			self::Debug->value           => 'Debug',
			self::Critical->value        => 'Critical',
			self::Alert->value           => 'Alert',
			self::Emergency->value       => 'Emergency',
			self::Notice->value          => 'Notice',
			self::Security->value        => 'Security',
			self::Access->value          => 'Access',
			self::Query->value           => 'Query',
			self::Job->value             => 'Job',
			self::Event->value           => 'Event',
			self::Audit->value           => 'Audit',
			self::Performance->value     => 'Performance',
			self::Api->value             => 'API',
			self::Database->value        => 'Database',
			self::Authentication->value  => 'Authentication',
			self::Authorization->value   => 'Authorization',
			self::Validation->value      => 'Validation',
			self::Mail->value            => 'Mail',
			self::Notification->value    => 'Notification',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Session',
			self::Queue->value           => 'Queue',
			self::Schedule->value        => 'Schedule',
			self::Console->value         => 'Console',
			self::System->value          => 'System',
			self::Other->value           => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Error->value           => 'Error',
			self::Warning->value         => 'Advertencia',
			self::Info->value            => 'Información',
			self::Debug->value           => 'Depuración',
			self::Critical->value        => 'Crítico',
			self::Alert->value           => 'Alerta',
			self::Emergency->value       => 'Emergencia',
			self::Notice->value          => 'Noticia',
			self::Security->value        => 'Seguridad',
			self::Access->value          => 'Acceso',
			self::Query->value           => 'Consulta',
			self::Job->value             => 'Trabajo',
			self::Event->value           => 'Evento',
			self::Audit->value           => 'Auditoría',
			self::Performance->value     => 'Rendimiento',
			self::Api->value             => 'API',
			self::Database->value        => 'Base de Datos',
			self::Authentication->value  => 'Autenticación',
			self::Authorization->value   => 'Autorización',
			self::Validation->value      => 'Validación',
			self::Mail->value            => 'Correo',
			self::Notification->value    => 'Notificación',
			self::Cache->value           => 'Caché',
			self::Session->value         => 'Sesión',
			self::Queue->value           => 'Cola',
			self::Schedule->value        => 'Programación',
			self::Console->value         => 'Consola',
			self::System->value          => 'Sistema',
			self::Other->value           => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Error->value           => 'خطأ',
			self::Warning->value         => 'تحذير',
			self::Info->value            => 'معلومات',
			self::Debug->value           => 'تصحيح',
			self::Critical->value        => 'حرج',
			self::Alert->value           => 'تنبيه',
			self::Emergency->value       => 'طارئ',
			self::Notice->value          => 'إشعار',
			self::Security->value        => 'أمان',
			self::Access->value          => 'وصول',
			self::Query->value           => 'استعلام',
			self::Job->value             => 'وظيفة',
			self::Event->value           => 'حدث',
			self::Audit->value           => 'تدقيق',
			self::Performance->value     => 'أداء',
			self::Api->value             => 'API',
			self::Database->value        => 'قاعدة البيانات',
			self::Authentication->value  => 'مصادقة',
			self::Authorization->value   => 'تفويض',
			self::Validation->value      => 'تحقق',
			self::Mail->value            => 'بريد',
			self::Notification->value    => 'إشعار',
			self::Cache->value           => 'ذاكرة تخزين مؤقت',
			self::Session->value         => 'جلسة',
			self::Queue->value           => 'طابور',
			self::Schedule->value        => 'جدولة',
			self::Console->value         => 'وحدة التحكم',
			self::System->value          => 'نظام',
			self::Other->value           => 'آخر',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Error->value           => 'Fejl',
			self::Warning->value         => 'Advarsel',
			self::Info->value            => 'Info',
			self::Debug->value           => 'Fejlfinding',
			self::Critical->value        => 'Kritisk',
			self::Alert->value           => 'Alarm',
			self::Emergency->value       => 'Nødstilfælde',
			self::Notice->value          => 'Meddelelse',
			self::Security->value        => 'Sikkerhed',
			self::Access->value          => 'Adgang',
			self::Query->value           => 'Forespørgsel',
			self::Job->value             => 'Job',
			self::Event->value           => 'Begivenhed',
			self::Audit->value           => 'Revision',
			self::Performance->value     => 'Ydeevne',
			self::Api->value             => 'API',
			self::Database->value        => 'Database',
			self::Authentication->value  => 'Godkendelse',
			self::Authorization->value   => 'Autorisation',
			self::Validation->value      => 'Validering',
			self::Mail->value            => 'Mail',
			self::Notification->value    => 'Notifikation',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Session',
			self::Queue->value           => 'Kø',
			self::Schedule->value        => 'Tidsplan',
			self::Console->value         => 'Konsol',
			self::System->value          => 'System',
			self::Other->value           => 'Andet',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Error->value           => 'Fehler',
			self::Warning->value         => 'Warnung',
			self::Info->value            => 'Info',
			self::Debug->value           => 'Debug',
			self::Critical->value        => 'Kritisch',
			self::Alert->value           => 'Alarm',
			self::Emergency->value       => 'Notfall',
			self::Notice->value          => 'Hinweis',
			self::Security->value        => 'Sicherheit',
			self::Access->value          => 'Zugriff',
			self::Query->value           => 'Abfrage',
			self::Job->value             => 'Job',
			self::Event->value           => 'Ereignis',
			self::Audit->value           => 'Audit',
			self::Performance->value     => 'Leistung',
			self::Api->value             => 'API',
			self::Database->value        => 'Datenbank',
			self::Authentication->value  => 'Authentifizierung',
			self::Authorization->value   => 'Autorisierung',
			self::Validation->value      => 'Validierung',
			self::Mail->value            => 'Mail',
			self::Notification->value    => 'Benachrichtigung',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Sitzung',
			self::Queue->value           => 'Warteschlange',
			self::Schedule->value        => 'Zeitplan',
			self::Console->value         => 'Konsole',
			self::System->value          => 'System',
			self::Other->value           => 'Andere',
		];
	}

	// French labels
	public static function labelsFr(): array
	{
		return [
			self::Error->value           => 'Erreur',
			self::Warning->value         => 'Avertissement',
			self::Info->value            => 'Info',
			self::Debug->value           => 'Débogage',
			self::Critical->value        => 'Critique',
			self::Alert->value           => 'Alerte',
			self::Emergency->value       => 'Urgence',
			self::Notice->value          => 'Avis',
			self::Security->value        => 'Sécurité',
			self::Access->value          => 'Accès',
			self::Query->value           => 'Requête',
			self::Job->value             => 'Tâche',
			self::Event->value           => 'Événement',
			self::Audit->value           => 'Audit',
			self::Performance->value     => 'Performance',
			self::Api->value             => 'API',
			self::Database->value        => 'Base de données',
			self::Authentication->value  => 'Authentification',
			self::Authorization->value   => 'Autorisation',
			self::Validation->value      => 'Validation',
			self::Mail->value            => 'Courrier',
			self::Notification->value    => 'Notification',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Session',
			self::Queue->value           => 'File d\'attente',
			self::Schedule->value        => 'Planification',
			self::Console->value         => 'Console',
			self::System->value          => 'Système',
			self::Other->value           => 'Autre',
		];
	}

	// Hebrew labels
	public static function labelsHe(): array
	{
		return [
			self::Error->value           => 'שגיאה',
			self::Warning->value         => 'אזהרה',
			self::Info->value            => 'מידע',
			self::Debug->value           => 'ניפוי שגיאות',
			self::Critical->value        => 'קריטי',
			self::Alert->value           => 'התראה',
			self::Emergency->value       => 'חירום',
			self::Notice->value          => 'הודעה',
			self::Security->value        => 'אבטחה',
			self::Access->value          => 'גישה',
			self::Query->value           => 'שאילתה',
			self::Job->value             => 'עבודה',
			self::Event->value           => 'אירוע',
			self::Audit->value           => 'ביקורת',
			self::Performance->value     => 'ביצועים',
			self::Api->value             => 'API',
			self::Database->value        => 'מסד נתונים',
			self::Authentication->value  => 'אימות',
			self::Authorization->value   => 'הרשאה',
			self::Validation->value      => 'אימות',
			self::Mail->value            => 'דואר',
			self::Notification->value    => 'התראה',
			self::Cache->value           => 'מטמון',
			self::Session->value         => 'סשן',
			self::Queue->value           => 'תור',
			self::Schedule->value        => 'תזמון',
			self::Console->value         => 'קונסולה',
			self::System->value          => 'מערכת',
			self::Other->value           => 'אחר',
		];
	}

	// Italian labels
	public static function labelsIt(): array
	{
		return [
			self::Error->value           => 'Errore',
			self::Warning->value         => 'Avviso',
			self::Info->value            => 'Informazione',
			self::Debug->value           => 'Debug',
			self::Critical->value        => 'Critico',
			self::Alert->value           => 'Allarme',
			self::Emergency->value       => 'Emergenza',
			self::Notice->value          => 'Avviso',
			self::Security->value        => 'Sicurezza',
			self::Access->value          => 'Accesso',
			self::Query->value           => 'Query',
			self::Job->value             => 'Lavoro',
			self::Event->value           => 'Evento',
			self::Audit->value           => 'Audit',
			self::Performance->value     => 'Prestazione',
			self::Api->value             => 'API',
			self::Database->value        => 'Database',
			self::Authentication->value  => 'Autenticazione',
			self::Authorization->value   => 'Autorizzazione',
			self::Validation->value      => 'Validazione',
			self::Mail->value            => 'Posta',
			self::Notification->value    => 'Notifica',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Sessione',
			self::Queue->value           => 'Coda',
			self::Schedule->value        => 'Pianificazione',
			self::Console->value         => 'Console',
			self::System->value          => 'Sistema',
			self::Other->value           => 'Altro',
		];
	}

	// Japanese labels
	public static function labelsJa(): array
	{
		return [
			self::Error->value           => 'エラー',
			self::Warning->value         => '警告',
			self::Info->value            => '情報',
			self::Debug->value           => 'デバッグ',
			self::Critical->value        => '致命的',
			self::Alert->value           => 'アラート',
			self::Emergency->value       => '緊急',
			self::Notice->value          => '通知',
			self::Security->value        => 'セキュリティ',
			self::Access->value          => 'アクセス',
			self::Query->value           => 'クエリ',
			self::Job->value             => 'ジョブ',
			self::Event->value           => 'イベント',
			self::Audit->value           => '監査',
			self::Performance->value     => 'パフォーマンス',
			self::Api->value             => 'API',
			self::Database->value        => 'データベース',
			self::Authentication->value  => '認証',
			self::Authorization->value   => '認可',
			self::Validation->value      => '検証',
			self::Mail->value            => 'メール',
			self::Notification->value    => '通知',
			self::Cache->value           => 'キャッシュ',
			self::Session->value         => 'セッション',
			self::Queue->value           => 'キュー',
			self::Schedule->value        => 'スケジュール',
			self::Console->value         => 'コンソール',
			self::System->value          => 'システム',
			self::Other->value           => 'その他',
		];
	}

	// Dutch labels
	public static function labelsNl(): array
	{
		return [
			self::Error->value           => 'Fout',
			self::Warning->value         => 'Waarschuwing',
			self::Info->value            => 'Info',
			self::Debug->value           => 'Debug',
			self::Critical->value        => 'Kritiek',
			self::Alert->value           => 'Alarm',
			self::Emergency->value       => 'Noodgeval',
			self::Notice->value          => 'Kennisgeving',
			self::Security->value        => 'Beveiliging',
			self::Access->value          => 'Toegang',
			self::Query->value           => 'Query',
			self::Job->value             => 'Taak',
			self::Event->value           => 'Gebeurtenis',
			self::Audit->value           => 'Audit',
			self::Performance->value     => 'Prestatie',
			self::Api->value             => 'API',
			self::Database->value        => 'Database',
			self::Authentication->value  => 'Authenticatie',
			self::Authorization->value   => 'Autorisatie',
			self::Validation->value      => 'Validatie',
			self::Mail->value            => 'Mail',
			self::Notification->value    => 'Melding',
			self::Cache->value           => 'Cache',
			self::Session->value         => 'Sessie',
			self::Queue->value           => 'Wachtrij',
			self::Schedule->value        => 'Schema',
			self::Console->value         => 'Console',
			self::System->value          => 'Systeem',
			self::Other->value           => 'Anders',
		];
	}

	// Polish labels
	public static function labelsPl(): array
	{
		return [
			self::Error->value           => 'Błąd',
			self::Warning->value         => 'Ostrzeżenie',
			self::Info->value            => 'Informacja',
			self::Debug->value           => 'Debug',
			self::Critical->value        => 'Krytyczny',
			self::Alert->value           => 'Alarm',
			self::Emergency->value       => 'Nagły',
			self::Notice->value          => 'Powiadomienie',
			self::Security->value        => 'Bezpieczeństwo',
			self::Access->value          => 'Dostęp',
			self::Query->value           => 'Zapytanie',
			self::Job->value             => 'Zadanie',
			self::Event->value           => 'Zdarzenie',
			self::Audit->value           => 'Audyt',
			self::Performance->value     => 'Wydajność',
			self::Api->value             => 'API',
			self::Database->value        => 'Baza danych',
			self::Authentication->value  => 'Uwierzytelnianie',
			self::Authorization->value   => 'Autoryzacja',
			self::Validation->value      => 'Walidacja',
			self::Mail->value            => 'Poczta',
			self::Notification->value    => 'Powiadomienie',
			self::Cache->value           => 'Pamięć podręczna',
			self::Session->value         => 'Sesja',
			self::Queue->value           => 'Kolejka',
			self::Schedule->value        => 'Harmonogram',
			self::Console->value         => 'Konsola',
			self::System->value          => 'System',
			self::Other->value           => 'Inne',
		];
	}

	// Russian labels
	public static function labelsRu(): array
	{
		return [
			self::Error->value           => 'Ошибка',
			self::Warning->value         => 'Предупреждение',
			self::Info->value            => 'Информация',
			self::Debug->value           => 'Отладка',
			self::Critical->value        => 'Критический',
			self::Alert->value           => 'Тревога',
			self::Emergency->value       => 'Чрезвычайная ситуация',
			self::Notice->value          => 'Уведомление',
			self::Security->value        => 'Безопасность',
			self::Access->value          => 'Доступ',
			self::Query->value           => 'Запрос',
			self::Job->value             => 'Задача',
			self::Event->value           => 'Событие',
			self::Audit->value           => 'Аудит',
			self::Performance->value     => 'Производительность',
			self::Api->value             => 'API',
			self::Database->value        => 'База данных',
			self::Authentication->value  => 'Аутентификация',
			self::Authorization->value   => 'Авторизация',
			self::Validation->value      => 'Проверка',
			self::Mail->value            => 'Почта',
			self::Notification->value    => 'Уведомление',
			self::Cache->value           => 'Кэш',
			self::Session->value         => 'Сессия',
			self::Queue->value           => 'Очередь',
			self::Schedule->value        => 'Расписание',
			self::Console->value         => 'Консоль',
			self::System->value          => 'Система',
			self::Other->value           => 'Другое',
		];
	}

	// Turkish labels
	public static function labelsTr(): array
	{
		return [
			self::Error->value           => 'Hata',
			self::Warning->value         => 'Uyarı',
			self::Info->value            => 'Bilgi',
			self::Debug->value           => 'Hata Ayıklama',
			self::Critical->value        => 'Kritik',
			self::Alert->value           => 'Alarm',
			self::Emergency->value       => 'Acil Durum',
			self::Notice->value          => 'Bildirim',
			self::Security->value        => 'Güvenlik',
			self::Access->value          => 'Erişim',
			self::Query->value           => 'Sorgu',
			self::Job->value             => 'İş',
			self::Event->value           => 'Olay',
			self::Audit->value           => 'Denetim',
			self::Performance->value     => 'Performans',
			self::Api->value             => 'API',
			self::Database->value        => 'Veritabanı',
			self::Authentication->value  => 'Kimlik Doğrulama',
			self::Authorization->value   => 'Yetkilendirme',
			self::Validation->value      => 'Doğrulama',
			self::Mail->value            => 'Posta',
			self::Notification->value    => 'Bildirim',
			self::Cache->value           => 'Önbellek',
			self::Session->value         => 'Oturum',
			self::Queue->value           => 'Kuyruk',
			self::Schedule->value        => 'Zamanlama',
			self::Console->value         => 'Konsol',
			self::System->value          => 'Sistem',
			self::Other->value           => 'Diğer',
		];
	}

	// Chinese labels
	public static function labelsZh(): array
	{
		return [
			self::Error->value           => '错误',
			self::Warning->value         => '警告',
			self::Info->value            => '信息',
			self::Debug->value           => '调试',
			self::Critical->value        => '严重',
			self::Alert->value           => '警报',
			self::Emergency->value       => '紧急',
			self::Notice->value          => '通知',
			self::Security->value        => '安全',
			self::Access->value          => '访问',
			self::Query->value           => '查询',
			self::Job->value             => '作业',
			self::Event->value           => '事件',
			self::Audit->value           => '审计',
			self::Performance->value     => '性能',
			self::Api->value             => 'API',
			self::Database->value        => '数据库',
			self::Authentication->value  => '认证',
			self::Authorization->value   => '授权',
			self::Validation->value      => '验证',
			self::Mail->value            => '邮件',
			self::Notification->value    => '通知',
			self::Cache->value           => '缓存',
			self::Session->value         => '会话',
			self::Queue->value           => '队列',
			self::Schedule->value        => '计划',
			self::Console->value         => '控制台',
			self::System->value          => '系统',
			self::Other->value           => '其他',
		];
	}

	// Helper methods for business logic
	public function isErrorLevel(): bool
	{
		return match ($this) {
			self::Error, self::Critical, self::Alert, self::Emergency => true,
			default => false,
		};
	}

	public function isInfoLevel(): bool
	{
		return match ($this) {
			self::Info, self::Notice, self::Debug => true,
			default => false,
		};
	}

	public function isWarningLevel(): bool
	{
		return $this === self::Warning;
	}

	public function isSecurityRelated(): bool
	{
		return match ($this) {
			self::Security, self::Authentication, self::Authorization,
			self::Audit, self::Access => true,
			default => false,
		};
	}

	public function isSystemRelated(): bool
	{
		return match ($this) {
			self::System, self::Performance, self::Cache, self::Queue,
			self::Session, self::Schedule, self::Console => true,
			default => false,
		};
	}

	public function getSeverityLevel(): int
	{
		return match ($this) {
			self::Emergency  => 1,
			self::Alert      => 2,
			self::Critical   => 3,
			self::Error      => 4,
			self::Warning    => 5,
			self::Notice     => 6,
			self::Info       => 7,
			self::Debug      => 8,
			default          => 9,
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Error, self::Critical, self::Emergency => 'red',
			self::Warning, self::Alert => 'orange',
			self::Notice => 'yellow',
			self::Info, self::Debug => 'blue',
			self::Security => 'purple',
			self::Access, self::Api => 'green',
			default => 'gray',
		};
	}
}
