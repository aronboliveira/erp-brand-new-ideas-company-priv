<?php

namespace App\Config\Constants;

class LangsConstants
{
	private const DELEGATION_AR = 'يرجى الاتصال بالدعم الفني أو مسؤول المجال.';
	private const DELEGATION_DA = 'Kontakt venligst teknisk support eller din domæneadministrator.';
	private const DELEGATION_DE = 'Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.';
	private const DELEGATION_EN = 'Please contact technical support or your domain administrator.';
	private const DELEGATION_ES = 'Por favor, contacte al soporte técnico o a su administrador de dominio.';
	private const DELEGATION_FR = 'Veuillez contacter le support technique ou votre administrateur de domaine.';
	private const DELEGATION_HE = 'אנא פנה לתמיכה טכנית או למנהל הדומיין שלך.';
	private const DELEGATION_IT = 'Si prega di contattare il supporto tecnico o l\'amministratore del dominio.';
	private const DELEGATION_JA = 'テクニカルサポートまたはドメイン管理者にご連絡ください。';
	private const DELEGATION_NL = 'Neem contact op met technische ondersteuning of uw domeinbeheerder.';
	private const DELEGATION_PL = 'Skontaktuj się z pomocą techniczną lub administratorem domeny.';
	private const DELEGATION_PT = 'Por favor, contacte o suporte técnico ou o administrador do domínio.';
	private const DELEGATION_PTBR = 'Por favor, entre em contato com o suporte técnico ou o administrador do domínio.';
	private const DELEGATION_RU = 'Пожалуйста, свяжитесь со службой технической поддержки или администратором домена.';
	private const DELEGATION_TR = 'Lütfen teknik destek veya alan yöneticinizle iletişime geçin.';
	private const DELEGATION_ZH = '请联系技术支持或您的域名管理员。';
	public const DEFAULT_CLIENT_MESSAGES = [
		'route_not_found'    => 'Sorry, the requested page could not be found.',
		'method_not_allowed' => 'That action is not permitted.',
		'internal_error'     => 'An internal error occurred. Please try again.',
		'banned_user'        => 'You are banned from this site. Redirecting to login page…',
		'login_required'     => 'Please log in to continue.',
		'link_not_found'     => 'The requested link could not be found. Please try again later or consider contacting support.',
	];
	public const ERROR_MESSAGES = [
		'ar' => [
			'route_not_found'         => 'عذرًا، لم يتم العثور على الصفحة المطلوبة.',
			'method_not_allowed'      => 'هذا الإجراء غير مسموح به.',
			'internal_error'          => 'حدث خطأ داخلي. الرجاء معاودة المحاولة.',
			'banned_user'             => 'تم حظرك من هذا الموقع. يتم التوجيه إلى صفحة تسجيل الدخول…',
			'login_required'          => 'يرجى تسجيل الدخول للمتابعة.',
			'invalid_user'            => 'تعذر استرداد مستخدم صالح',
			'must_login'              => 'يجب عليك تسجيل الدخول للقيام بذلك.',
			'credential_check_failed' => 'حدث خطأ أثناء التحقق من بيانات الاعتماد الخاصة بك. حاول مرة أخرى لاحقًا.',
			'page_load_failed'        => 'فشل تحميل الصفحة',
			'authentication_error'    => 'حدث خطأ في المصادقة.',
		],
		'da' => [
			'route_not_found'         => 'Beklager, den ønskede side kunne ikke findes.',
			'method_not_allowed'      => 'Denne handling er ikke tilladt.',
			'internal_error'          => 'Der opstod en intern fejl. Prøv venligst igen.',
			'banned_user'             => 'Du er udelukket fra dette websted. Omdirigerer til login-siden…',
			'login_required'          => 'Log venligst ind for at fortsætte.',
			'invalid_user'            => 'Kunne ikke hente en gyldig bruger',
			'must_login'              => 'Du skal være logget ind for at gøre det.',
			'credential_check_failed' => 'Noget gik galt under kontrol af dine legitimationsoplysninger. Prøv igen senere.',
			'page_load_failed'        => 'Siden kunne ikke indlæses',
			'authentication_error'    => 'Der opstod en godkendelsesfejl.',
		],
		'de' => [
			'route_not_found'         => 'Entschuldigung, die angeforderte Seite wurde nicht gefunden.',
			'method_not_allowed'      => 'Diese Aktion ist nicht erlaubt.',
			'internal_error'          => 'Ein interner Fehler ist aufgetreten. Bitte versuchen Sie es erneut.',
			'banned_user'             => 'Sie sind von dieser Website gesperrt. Weiterleitung zur Anmeldeseite…',
			'login_required'          => 'Bitte melden Sie sich an, um fortzufahren.',
			'invalid_user'            => 'Konnte keinen gültigen Benutzer abrufen',
			'must_login'              => 'Sie müssen angemeldet sein, um dies zu tun.',
			'credential_check_failed' => 'Beim Überprüfen Ihrer Anmeldedaten ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.',
			'page_load_failed'        => 'Seite konnte nicht geladen werden',
			'authentication_error'    => 'Authentifizierungsfehler ist aufgetreten.',
		],
		'en' => [
			'route_not_found'         => 'Sorry, the requested page could not be found.',
			'method_not_allowed'      => 'That action is not permitted.',
			'internal_error'          => 'An internal error occurred. Please try again.',
			'banned_user'             => 'You are banned from this site. Redirecting to login page…',
			'login_required'          => 'Please log in to continue.',
			'invalid_user'            => 'Could not retrieve a valid user',
			'must_login'              => 'You must be logged in to do that.',
			'credential_check_failed' => 'Something went wrong checking your credentials. Try again later.',
			'page_load_failed'        => 'Page failed to load',
			'authentication_error'    => 'Authentication error occurred.',
		],
		'es' => [
			'route_not_found'         => 'Lo sentimos, no se pudo encontrar la página solicitada.',
			'method_not_allowed'      => 'Esa acción no está permitida.',
			'internal_error'          => 'Se produjo un error interno. Por favor, inténtelo de nuevo.',
			'banned_user'             => 'Has sido baneado de este sitio. Redirigiendo a la página de inicio de sesión…',
			'login_required'          => 'Por favor, inicie sesión para continuar.',
			'invalid_user'            => 'No se pudo recuperar un usuario válido',
			'must_login'              => 'Debes iniciar sesión para hacer eso.',
			'credential_check_failed' => 'Algo salió mal al verificar tus credenciales. Inténtalo de nuevo más tarde.',
			'page_load_failed'        => 'La página no se pudo cargar',
			'authentication_error'    => 'Se produjo un error de autenticación.',
		],
		'fr' => [
			'route_not_found'         => 'Désolé, la page demandée n\'a pas pu être trouvée.',
			'method_not_allowed'      => 'Cette action n\'est pas autorisée.',
			'internal_error'          => 'Une erreur interne s\'est produite. Veuillez réessayer.',
			'banned_user'             => 'Vous êtes banni de ce site. Redirection vers la page de connexion…',
			'login_required'          => 'Veuillez vous connecter pour continuer.',
			'invalid_user'            => 'Impossible de récupérer un utilisateur valide',
			'must_login'              => 'Vous devez être connecté pour faire cela.',
			'credential_check_failed' => 'Un problème est survenu lors de la vérification de vos identifiants. Réessayez plus tard.',
			'page_load_failed'        => 'Échec du chargement de la page',
			'authentication_error'    => 'Une erreur d\'authentification s\'est produite.',
		],
		'he' => [
			'route_not_found'         => 'מצטערים, הדף המבוקש לא נמצא.',
			'method_not_allowed'      => 'פעולה זו אינה מותרת.',
			'internal_error'          => 'אירעה שגיאה פנימית. נא לנסות שוב.',
			'banned_user'             => 'הוחרגת מהאתר. מפנה לדף ההתחברות…',
			'login_required'          => 'אנא התחבר כדי להמשיך.',
			'invalid_user'            => 'לא ניתן לאחזר משתמש תקין',
			'must_login'              => 'עליך להיות מחובר כדי לבצע פעולה זו.',
			'credential_check_failed' => 'משהו השתבש בבדיקת פרטייך. נא לנסות שוב מאוחר יותר.',
			'page_load_failed'        => 'טעינת הדף נכשלה',
			'authentication_error'    => 'אירעה שגיאת אימות.',
		],
		'it' => [
			'route_not_found'         => 'Spiacenti, la pagina richiesta non è stata trovata.',
			'method_not_allowed'      => 'Quell\'azione non è consentita.',
			'internal_error'          => 'Si è verificato un errore interno. Per favore riprova.',
			'banned_user'             => 'Sei bannato da questo sito. Reindirizzamento alla pagina di login…',
			'login_required'          => 'Per favore accedi per continuare.',
			'invalid_user'            => 'Impossibile recuperare un utente valido',
			'must_login'              => 'Devi essere loggato per farlo.',
			'credential_check_failed' => 'Qualcosa è andato storto durante il controllo delle credenziali. Riprova più tardi.',
			'page_load_failed'        => 'Caricamento della pagina fallito',
			'authentication_error'    => 'Si è verificato un errore di autenticazione.',
		],
		'ja' => [
			'route_not_found'         => '申し訳ありませんが、要求されたページが見つかりませんでした。',
			'method_not_allowed'      => 'その操作は許可されていません。',
			'internal_error'          => '内部エラーが発生しました。もう一度お試しください。',
			'banned_user'             => 'このサイトからアクセス禁止されています。ログインページにリダイレクトしています…',
			'login_required'          => '続行するにはログインしてください。',
			'invalid_user'            => '有効なユーザーを取得できませんでした',
			'must_login'              => 'その操作を行うにはログインする必要があります。',
			'credential_check_failed' => '資格情報の確認中に問題が発生しました。後でもう一度お試しください。',
			'page_load_failed'        => 'ページの読み込みに失敗しました',
			'authentication_error'    => '認証エラーが発生しました。',
		],
		'nl' => [
			'route_not_found'         => 'Sorry, de gevraagde pagina kon niet worden gevonden.',
			'method_not_allowed'      => 'Die actie is niet toegestaan.',
			'internal_error'          => 'Er is een interne fout opgetreden. Probeer het opnieuw.',
			'banned_user'             => 'U bent verbannen van deze site. Doorverwijzen naar inlogpagina…',
			'login_required'          => 'Log in om verder te gaan.',
			'invalid_user'            => 'Kon geen geldige gebruiker ophalen',
			'must_login'              => 'U moet ingelogd zijn om dat te doen.',
			'credential_check_failed' => 'Er ging iets mis bij het controleren van uw inloggegevens. Probeer het later opnieuw.',
			'page_load_failed'        => 'Pagina kon niet worden geladen',
			'authentication_error'    => 'Er is een authenticatiefout opgetreden.',
		],
		'pl' => [
			'route_not_found'         => 'Przepraszamy, nie znaleziono żądanej strony.',
			'method_not_allowed'      => 'Ta akcja nie jest dozwolona.',
			'internal_error'          => 'Wystąpił wewnętrzny błąd. Proszę spróbować ponownie.',
			'banned_user'             => 'Zostałeś zbanowany na tej stronie. Przekierowywanie do strony logowania…',
			'login_required'          => 'Zaloguj się, aby kontynuować.',
			'invalid_user'            => 'Nie można pobrać prawidłowego użytkownika',
			'must_login'              => 'Aby to zrobić, musisz być zalogowany.',
			'credential_check_failed' => 'Coś poszło nie tak podczas sprawdzania twoich danych. Spróbuj ponownie później.',
			'page_load_failed'        => 'Nie udało się załadować strony',
			'authentication_error'    => 'Wystąpił błąd uwierzytelniania.',
		],
		'pt' => [
			'route_not_found'         => 'Desculpe, a página solicitada não foi encontrada.',
			'method_not_allowed'      => 'Essa ação não é permitida.',
			'internal_error'          => 'Ocorreu um erro interno. Por favor, tente novamente.',
			'banned_user'             => 'Você está banido deste site. Redirecionando para a página de login…',
			'login_required'          => 'Por favor, faça login para continuar.',
			'invalid_user'            => 'Não foi possível recuperar um usuário válido',
			'must_login'              => 'Você precisa estar logado para fazer isso.',
			'credential_check_failed' => 'Algo deu errado ao verificar suas credenciais. Tente novamente mais tarde.',
			'page_load_failed'        => 'Falha ao carregar a página',
			'authentication_error'    => 'Ocorreu um erro de autenticação.',
		],
		'pt-br' => [
			'route_not_found'         => 'Desculpe, não foi possível encontrar a página solicitada.',
			'method_not_allowed'      => 'Essa ação não é permitida.',
			'internal_error'          => 'Ocorreu um erro interno. Por favor, tente novamente.',
			'banned_user'             => 'Você está banido deste site. Redirecionando para a página de login…',
			'login_required'          => 'Por favor, faça login para continuar.',
			'invalid_user'            => 'Não foi possível recuperar um usuário válido',
			'must_login'              => 'Você precisa estar logado para fazer isso.',
			'credential_check_failed' => 'Algo deu errado ao verificar suas credenciais. Tente novamente mais tarde.',
			'page_load_failed'        => 'Falha ao carregar a página',
			'authentication_error'    => 'Ocorreu um erro de autenticação.',
		],
		'ru' => [
			'route_not_found'         => 'Извините, запрашиваемая страница не найдена.',
			'method_not_allowed'      => 'Это действие не разрешено.',
			'internal_error'          => 'Произошла внутренняя ошибка. Пожалуйста, попробуйте еще раз.',
			'banned_user'             => 'Вы забанены на этом сайте. Перенаправление на страницу входа…',
			'login_required'          => 'Пожалуйста, войдите, чтобы продолжить.',
			'invalid_user'            => 'Не удалось получить действительного пользователя',
			'must_login'              => 'Вы должны войти в систему, чтобы сделать это.',
			'credential_check_failed' => 'Произошла ошибка при проверке ваших учетных данных. Попробуйте позже.',
			'page_load_failed'        => 'Не удалось загрузить страницу',
			'authentication_error'    => 'Произошла ошибка аутентификации.',
		],
		'tr' => [
			'route_not_found'         => 'Üzgünüz, istenen sayfa bulunamadı.',
			'method_not_allowed'      => 'Bu eyleme izin verilmiyor.',
			'internal_error'          => 'Dahili bir hata oluştu. Lütfen tekrar deneyin.',
			'banned_user'             => 'Bu siteden yasaklandınız. Giriş sayfasına yönlendiriliyor…',
			'login_required'          => 'Devam etmek için lütfen giriş yapın.',
			'invalid_user'            => 'Geçerli bir kullanıcı alınamadı',
			'must_login'              => 'Bunu yapmak için giriş yapmalısınız.',
			'credential_check_failed' => 'Kimlik bilgileriniz kontrol edilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
			'page_load_failed'        => 'Sayfa yüklenemedi',
			'authentication_error'    => 'Kimlik doğrulama hatası oluştu.',
		],
		'zh' => [
			'route_not_found'         => '抱歉，找不到请求的页面。',
			'method_not_allowed'      => '该操作不被允许。',
			'internal_error'          => '发生内部错误，请重试。',
			'banned_user'             => '您已被禁止访问此网站。正在重定向到登录页面…',
			'login_required'          => '请登录以继续。',
			'invalid_user'            => '无法检索有效用户',
			'must_login'              => '您必须登录才能执行该操作。',
			'credential_check_failed' => '检查您的凭据时出错。请稍后重试。',
			'page_load_failed'        => '页面加载失败',
			'authentication_error'    => '发生身份验证错误。',
		],
	];
	public const LINK_MESSAGES = [
		ViewsConstants::ACC_AST => [
			'ar' => [
				'account_asset_setup_unavailable' => 'مسار إعداد أصول الحساب غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'account_asset_setup_unavailable' => 'Kontoaktivopsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'account_asset_setup_unavailable' => 'Kontoanlagen-Einrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'account_asset_setup_unavailable' => 'Account Assets Setup route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'account_asset_setup_unavailable' => 'La ruta de configuración de activos de cuenta no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'account_asset_setup_unavailable' => 'La route de configuration des actifs de compte est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'account_asset_setup_unavailable' => 'נתיב הגדרת נכסי חשבון אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'account_asset_setup_unavailable' => 'La rotta di configurazione delle attività del conto non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'account_asset_setup_unavailable' => '口座資産設定ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'account_asset_setup_unavailable' => 'Rekeningactiva-installatieroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'account_asset_setup_unavailable' => 'Trasa konfiguracji aktywów konta jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'account_asset_setup_unavailable' => 'A rota de configuração de ativos de conta não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'account_asset_setup_unavailable' => 'A rota de configuração de ativos da conta não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'account_asset_setup_unavailable' => 'Маршрут настройки активов счета недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'account_asset_setup_unavailable' => 'Hesap varlıkları kurulum rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'account_asset_setup_unavailable' => '账户资产设置路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::ALW => [
			'ar' => [
				'allowance_store_route_unavailable' => 'مسار متجر البدلات غير متاح. ' . self::DELEGATION_AR,
				'allowance_update_route_unavailable' => 'مسار تحديث البدل غير متاح. ' . self::DELEGATION_AR,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_AR,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_AR,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'allowance_store_route_unavailable' => 'Godtgørelsesbutikrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'allowance_update_route_unavailable' => 'Godtgørelsesopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_DA,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_DA,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'allowance_store_route_unavailable' => 'Zulage-Speicher-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'allowance_update_route_unavailable' => 'Zulage-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_DE,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_DE,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'allowance_store_route_unavailable' => 'Allowance store route is unavailable. ' . self::DELEGATION_EN,
				'allowance_update_route_unavailable' => 'Allowance update route is unavailable. ' . self::DELEGATION_EN,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_EN,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_EN,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'allowance_store_route_unavailable' => 'La ruta de almacenamiento de asignaciones no está disponible. ' . self::DELEGATION_ES,
				'allowance_update_route_unavailable' => 'La ruta de actualización de asignaciones no está disponible. ' . self::DELEGATION_ES,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_ES,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_ES,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'allowance_store_route_unavailable' => 'La route de stockage des allocations n\'est pas disponible. ' . self::DELEGATION_FR,
				'allowance_update_route_unavailable' => 'La route de mise à jour des allocations n\'est pas disponible. ' . self::DELEGATION_FR,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_FR,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_FR,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'allowance_store_route_unavailable' => 'נתיב חנות הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE,
				'allowance_update_route_unavailable' => 'נתיב עדכון הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_HE,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_HE,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'allowance_store_route_unavailable' => 'La rotta dello store delle indennità non è disponibile. ' . self::DELEGATION_IT,
				'allowance_update_route_unavailable' => 'La rotta di aggiornamento delle indennità non è disponibile. ' . self::DELEGATION_IT,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_IT,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_IT,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'allowance_store_route_unavailable' => '手当ストアルートは利用できません。' . self::DELEGATION_JA,
				'allowance_update_route_unavailable' => '手当更新ルートは利用できません。' . self::DELEGATION_JA,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_JA,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_JA,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'allowance_store_route_unavailable' => 'Vergoeding opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'allowance_update_route_unavailable' => 'Vergoeding updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_NL,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_NL,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'allowance_store_route_unavailable' => 'Trasa sklepowa dodatek jest niedostępna. ' . self::DELEGATION_PL,
				'allowance_update_route_unavailable' => 'Trasa aktualizacji dodatek jest niedostępna. ' . self::DELEGATION_PL,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_PL,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_PL,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'allowance_store_route_unavailable' => 'A rota de armazenamento de subsídios não está disponível. ' . self::DELEGATION_PT,
				'allowance_update_route_unavailable' => 'A rota de atualização de subsídios não está disponível. ' . self::DELEGATION_PT,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_PT,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_PT,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'allowance_store_route_unavailable' => 'A rota de armazenamento de subsídios não está disponível. ' . self::DELEGATION_PTBR,
				'allowance_update_route_unavailable' => 'A rota de atualização de subsídios não está disponível. ' . self::DELEGATION_PTBR,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_PTBR,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_PTBR,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'allowance_store_route_unavailable' => 'Маршрут хранения надбавок недоступен. ' . self::DELEGATION_RU,
				'allowance_update_route_unavailable' => 'Маршрут обновления надбавок недоступен. ' . self::DELEGATION_RU,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_RU,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_RU,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'allowance_store_route_unavailable' => 'Ödenek depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'allowance_update_route_unavailable' => 'Ödenek güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_TR,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_TR,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'allowance_store_route_unavailable' => '津贴存储路由不可用。' . self::DELEGATION_ZH,
				'allowance_update_route_unavailable' => '津贴更新路由不可用。' . self::DELEGATION_ZH,
				'allowance_create_route_unavailable' => 'Allowance create route is unavailable. ' . self::DELEGATION_ZH,
				'allowance_edit_route_unavailable' => 'Allowance edit route is unavailable. ' . self::DELEGATION_ZH,
				'allowance_destroy_route_unavailable' => 'Allowance destroy route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::ALW_OPT => [
			'ar' => [
				'allowance_option_store_route_unavailable' => 'مسار متجر خيارات البدل غير متاح. ' . self::DELEGATION_AR,
				'allowance_option_update_route_unavailable' => 'مسار تحديث خيار البدل غير متاح. ' . self::DELEGATION_AR,
				'allowance_option_edit_route_unavailable' => 'مسار تعديل خيار البدل غير متاح. ' . self::DELEGATION_AR,
				'allowance_option_destroy_route_unavailable' => 'مسار حذف خيار البدل غير متاح. ' . self::DELEGATION_AR,
				'allowance_option_create_route_unavailable' => 'مسار إنشاء خيار البدل غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'allowance_option_store_route_unavailable' => 'Godtgørelsesindstillingsbutikrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'allowance_option_update_route_unavailable' => 'Godtgørelsesindstillingsopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'allowance_option_edit_route_unavailable' => 'Rute til redigering af godtgørelsesindstilling er ikke tilgængelig. ' . self::DELEGATION_DA,
				'allowance_option_destroy_route_unavailable' => 'Rute til sletning af godtgørelsesindstilling er ikke tilgængelig. ' . self::DELEGATION_DA,
				'allowance_option_create_route_unavailable' => 'Rute til oprettelse af godtgørelsesindstilling er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'allowance_option_store_route_unavailable' => 'Zulagenoptions-Speicher-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'allowance_option_update_route_unavailable' => 'Zulagenoptions-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'allowance_option_edit_route_unavailable' => 'Route zum Bearbeiten von Zulagenoptionen ist nicht verfügbar. ' . self::DELEGATION_DE,
				'allowance_option_destroy_route_unavailable' => 'Route zum Löschen von Zulagenoptionen ist nicht verfügbar. ' . self::DELEGATION_DE,
				'allowance_option_create_route_unavailable' => 'Route zum Erstellen von Zulagenoptionen ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'allowance_option_store_route_unavailable' => 'Allowance option store route is unavailable. ' . self::DELEGATION_EN,
				'allowance_option_update_route_unavailable' => 'Allowance option update route is unavailable. ' . self::DELEGATION_EN,
				'allowance_option_edit_route_unavailable' => 'Edit Allowance Option route is unavailable. ' . self::DELEGATION_EN,
				'allowance_option_destroy_route_unavailable' => 'Delete Allowance Option route is unavailable. ' . self::DELEGATION_EN,
				'allowance_option_create_route_unavailable' => 'Create Allowance Option route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'allowance_option_store_route_unavailable' => 'La ruta de almacenamiento de opciones de asignación no está disponible. ' . self::DELEGATION_ES,
				'allowance_option_update_route_unavailable' => 'La ruta de actualización de opciones de asignación no está disponible. ' . self::DELEGATION_ES,
				'allowance_option_edit_route_unavailable' => 'La ruta de edición de opciones de asignación no está disponible. ' . self::DELEGATION_ES,
				'allowance_option_destroy_route_unavailable' => 'La ruta de eliminación de opciones de asignación no está disponible. ' . self::DELEGATION_ES,
				'allowance_option_create_route_unavailable' => 'La ruta de creación de opciones de asignación no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'allowance_option_store_route_unavailable' => 'La route de stockage des options d\'allocation n\'est pas disponible. ' . self::DELEGATION_FR,
				'allowance_option_update_route_unavailable' => 'La route de mise à jour des options d\'allocation n\'est pas disponible. ' . self::DELEGATION_FR,
				'allowance_option_edit_route_unavailable' => 'La route d\'édition des options d\'allocation n\'est pas disponible. ' . self::DELEGATION_FR,
				'allowance_option_destroy_route_unavailable' => 'La route de suppression des options d\'allocation n\'est pas disponible. ' . self::DELEGATION_FR,
				'allowance_option_create_route_unavailable' => 'La route de création des options d\'allocation n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'allowance_option_store_route_unavailable' => 'נתיב חנות אפשרויות הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE,
				'allowance_option_update_route_unavailable' => 'נתיב עדכון אפשרות הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE,
				'allowance_option_edit_route_unavailable' => 'נתיב עריכת אפשרות הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE,
				'allowance_option_destroy_route_unavailable' => 'נתיב מחיקת אפשרות הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE,
				'allowance_option_create_route_unavailable' => 'נתיב יצירת אפשרות הדמי נסיעה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'allowance_option_store_route_unavailable' => 'La rotta dello store delle opzioni di indennità non è disponibile. ' . self::DELEGATION_IT,
				'allowance_option_update_route_unavailable' => 'La rotta di aggiornamento delle opzioni di indennità non è disponibile. ' . self::DELEGATION_IT,
				'allowance_option_edit_route_unavailable' => 'La rotta di modifica delle opzioni di indennità non è disponibile. ' . self::DELEGATION_IT,
				'allowance_option_destroy_route_unavailable' => 'La rotta di eliminazione delle opzioni di indennità non è disponibile. ' . self::DELEGATION_IT,
				'allowance_option_create_route_unavailable' => 'La rotta di creazione delle opzioni di indennità non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'allowance_option_store_route_unavailable' => '手当オプションストアルートは利用できません。' . self::DELEGATION_JA,
				'allowance_option_update_route_unavailable' => '手当オプション更新ルートは利用できません。' . self::DELEGATION_JA,
				'allowance_option_edit_route_unavailable' => '手当オプション編集ルートは利用できません。' . self::DELEGATION_JA,
				'allowance_option_destroy_route_unavailable' => '手当オプション削除ルートは利用できません。' . self::DELEGATION_JA,
				'allowance_option_create_route_unavailable' => '手当オプション作成ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'allowance_option_store_route_unavailable' => 'Vergoedingsoptie-opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'allowance_option_update_route_unavailable' => 'Vergoedingsoptie-updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'allowance_option_edit_route_unavailable' => 'Route voor het bewerken van vergoedingsopties is niet beschikbaar. ' . self::DELEGATION_NL,
				'allowance_option_destroy_route_unavailable' => 'Route voor het verwijderen van vergoedingsopties is niet beschikbaar. ' . self::DELEGATION_NL,
				'allowance_option_create_route_unavailable' => 'Route voor het maken van vergoedingsopties is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'allowance_option_store_route_unavailable' => 'Trasa sklepu z opcjami dodatków jest niedostępna. ' . self::DELEGATION_PL,
				'allowance_option_update_route_unavailable' => 'Trasa aktualizacji opcji dodatku jest niedostępna. ' . self::DELEGATION_PL,
				'allowance_option_edit_route_unavailable' => 'Trasa edycji opcji dodatku jest niedostępna. ' . self::DELEGATION_PL,
				'allowance_option_destroy_route_unavailable' => 'Trasa usuwania opcji dodatku jest niedostępna. ' . self::DELEGATION_PL,
				'allowance_option_create_route_unavailable' => 'Trasa tworzenia opcji dodatku jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'allowance_option_store_route_unavailable' => 'A rota de armazenamento de opções de subsídio não está disponível. ' . self::DELEGATION_PT,
				'allowance_option_update_route_unavailable' => 'A rota de atualização de opções de subsídio não está disponível. ' . self::DELEGATION_PT,
				'allowance_option_edit_route_unavailable' => 'A rota de edição de opções de subsídio não está disponível. ' . self::DELEGATION_PT,
				'allowance_option_destroy_route_unavailable' => 'A rota de exclusão de opções de subsídio não está disponível. ' . self::DELEGATION_PT,
				'allowance_option_create_route_unavailable' => 'A rota de criação de opções de subsídio não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'allowance_option_store_route_unavailable' => 'A rota de armazenamento de opções de subsídio não está disponível. ' . self::DELEGATION_PTBR,
				'allowance_option_update_route_unavailable' => 'A rota de atualização de opções de subsídio não está disponível. ' . self::DELEGATION_PTBR,
				'allowance_option_edit_route_unavailable' => 'A rota de edição de opções de subsídio não está disponível. ' . self::DELEGATION_PTBR,
				'allowance_option_destroy_route_unavailable' => 'A rota de exclusão de opções de subsídio não está disponível. ' . self::DELEGATION_PTBR,
				'allowance_option_create_route_unavailable' => 'A rota de criação de opções de subsídio não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'allowance_option_store_route_unavailable' => 'Маршрут хранения опций надбавок недоступен. ' . self::DELEGATION_RU,
				'allowance_option_update_route_unavailable' => 'Маршрут обновления опций надбавок недоступен. ' . self::DELEGATION_RU,
				'allowance_option_edit_route_unavailable' => 'Маршрут редактирования опций надбавок недоступен. ' . self::DELEGATION_RU,
				'allowance_option_destroy_route_unavailable' => 'Маршрут удаления опций надбавок недоступен. ' . self::DELEGATION_RU,
				'allowance_option_create_route_unavailable' => 'Маршрут создания опций надбавок недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'allowance_option_store_route_unavailable' => 'Ödenek seçeneği depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'allowance_option_update_route_unavailable' => 'Ödenek seçeneği güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'allowance_option_edit_route_unavailable' => 'Ödenek seçeneği düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'allowance_option_destroy_route_unavailable' => 'Ödenek seçeneği silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'allowance_option_create_route_unavailable' => 'Ödenek seçeneği oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'allowance_option_store_route_unavailable' => '津贴选项存储路由不可用。' . self::DELEGATION_ZH,
				'allowance_option_update_route_unavailable' => '津贴选项更新路由不可用。' . self::DELEGATION_ZH,
				'allowance_option_edit_route_unavailable' => '编辑津贴选项路由不可用。' . self::DELEGATION_ZH,
				'allowance_option_destroy_route_unavailable' => '删除津贴选项路由不可用。' . self::DELEGATION_ZH,
				'allowance_option_create_route_unavailable' => '创建津贴选项路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::ANC => [
			'ar' => [
				'announcement_index_route_unavailable' => 'مسار فهرس الإعلانات غير متاح. ' . self::DELEGATION_AR,
				'announcement_create_route_unavailable' => 'مسار إنشاء الإعلان غير متاح. ' . self::DELEGATION_AR,
				'announcement_store_route_unavailable' => 'مسار تخزين الإعلان غير متاح. ' . self::DELEGATION_AR,
				'announcement_update_route_unavailable' => 'مسار تحديث الإعلان غير متاح. ' . self::DELEGATION_AR,
				'announcement_edit_route_unavailable' => 'مسار تعديل الإعلان غير متاح. ' . self::DELEGATION_AR,
				'announcement_destroy_route_unavailable' => 'مسار حذف الإعلان غير متاح. ' . self::DELEGATION_AR,
				'announcement_generate_route_unavailable' => 'مسار التوليد بالذكاء الاصطناعي غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'announcement_index_route_unavailable' => 'Meddelelsesindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'announcement_create_route_unavailable' => 'Opret meddelelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'announcement_store_route_unavailable' => 'Gem meddelelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'announcement_update_route_unavailable' => 'Opdater meddelelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'announcement_edit_route_unavailable' => 'Rediger meddelelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'announcement_destroy_route_unavailable' => 'Slet meddelelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'announcement_generate_route_unavailable' => 'Generer med AI-rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'announcement_index_route_unavailable' => 'Ankündigungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'announcement_create_route_unavailable' => 'Ankündigungserstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'announcement_store_route_unavailable' => 'Ankündigungsspeicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'announcement_update_route_unavailable' => 'Ankündigungsaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'announcement_edit_route_unavailable' => 'Ankündigungsbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'announcement_destroy_route_unavailable' => 'Ankündigungslöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'announcement_generate_route_unavailable' => 'Generieren-mit-KI-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'announcement_index_route_unavailable' => 'Announcement index route is unavailable. ' . self::DELEGATION_EN,
				'announcement_create_route_unavailable' => 'Create Announcement route is unavailable. ' . self::DELEGATION_EN,
				'announcement_store_route_unavailable' => 'Announcement create route is unavailable. ' . self::DELEGATION_EN,
				'announcement_update_route_unavailable' => 'Announcement update route is unavailable. ' . self::DELEGATION_EN,
				'announcement_edit_route_unavailable' => 'Edit Announcement route is unavailable. ' . self::DELEGATION_EN,
				'announcement_destroy_route_unavailable' => 'Delete Announcement route is unavailable. ' . self::DELEGATION_EN,
				'announcement_generate_route_unavailable' => 'Generate with AI route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'announcement_index_route_unavailable' => 'La ruta del índice de anuncios no está disponible. ' . self::DELEGATION_ES,
				'announcement_create_route_unavailable' => 'La ruta de creación de anuncios no está disponible. ' . self::DELEGATION_ES,
				'announcement_store_route_unavailable' => 'La ruta de almacenamiento de anuncios no está disponible. ' . self::DELEGATION_ES,
				'announcement_update_route_unavailable' => 'La ruta de actualización de anuncios no está disponible. ' . self::DELEGATION_ES,
				'announcement_edit_route_unavailable' => 'La ruta de edición de anuncios no está disponible. ' . self::DELEGATION_ES,
				'announcement_destroy_route_unavailable' => 'La ruta de eliminación de anuncios no está disponible. ' . self::DELEGATION_ES,
				'announcement_generate_route_unavailable' => 'La ruta de generación con IA no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'announcement_index_route_unavailable' => 'La route de l\'index des annonces est indisponible. ' . self::DELEGATION_FR,
				'announcement_create_route_unavailable' => 'La route de création d\'annonce est indisponible. ' . self::DELEGATION_FR,
				'announcement_store_route_unavailable' => 'La route de stockage d\'annonce est indisponible. ' . self::DELEGATION_FR,
				'announcement_update_route_unavailable' => 'La route de mise à jour d\'annonce est indisponible. ' . self::DELEGATION_FR,
				'announcement_edit_route_unavailable' => 'La route d\'édition d\'annonce est indisponible. ' . self::DELEGATION_FR,
				'announcement_destroy_route_unavailable' => 'La route de suppression d\'annonce est indisponible. ' . self::DELEGATION_FR,
				'announcement_generate_route_unavailable' => 'La route de génération par IA est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'announcement_index_route_unavailable' => 'נתיב אינדקס הודעות אינו זמין. ' . self::DELEGATION_HE,
				'announcement_create_route_unavailable' => 'נתיב יצירת הודעה אינו זמין. ' . self::DELEGATION_HE,
				'announcement_store_route_unavailable' => 'נתיב אחסון הודעה אינו זמין. ' . self::DELEGATION_HE,
				'announcement_update_route_unavailable' => 'נתיב עדכון הודעה אינו זמין. ' . self::DELEGATION_HE,
				'announcement_edit_route_unavailable' => 'נתיב עריכת הודעה אינו זמין. ' . self::DELEGATION_HE,
				'announcement_destroy_route_unavailable' => 'נתיב מחיקת הודעה אינו זמין. ' . self::DELEGATION_HE,
				'announcement_generate_route_unavailable' => 'נתיב יצירה באמצעות בינה מלאכותית אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'announcement_index_route_unavailable' => 'La rotta dell\'indice degli annunci non è disponibile. ' . self::DELEGATION_IT,
				'announcement_create_route_unavailable' => 'La rotta di creazione degli annunci non è disponibile. ' . self::DELEGATION_IT,
				'announcement_store_route_unavailable' => 'La rotta di memorizzazione degli annunci non è disponibile. ' . self::DELEGATION_IT,
				'announcement_update_route_unavailable' => 'La rotta di aggiornamento degli annunci non è disponibile. ' . self::DELEGATION_IT,
				'announcement_edit_route_unavailable' => 'La rotta di modifica degli annunci non è disponibile. ' . self::DELEGATION_IT,
				'announcement_destroy_route_unavailable' => 'La rotta di eliminazione degli annunci non è disponibile. ' . self::DELEGATION_IT,
				'announcement_generate_route_unavailable' => 'La rotta di generazione con IA non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'announcement_index_route_unavailable' => '告知インデックスルートは利用できません。' . self::DELEGATION_JA,
				'announcement_create_route_unavailable' => '告知作成ルートは利用できません。' . self::DELEGATION_JA,
				'announcement_store_route_unavailable' => '告知保存ルートは利用できません。' . self::DELEGATION_JA,
				'announcement_update_route_unavailable' => '告知更新ルートは利用できません。' . self::DELEGATION_JA,
				'announcement_edit_route_unavailable' => '告知編集ルートは利用できません。' . self::DELEGATION_JA,
				'announcement_destroy_route_unavailable' => '告知削除ルートは利用できません。' . self::DELEGATION_JA,
				'announcement_generate_route_unavailable' => 'AIによる生成ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'announcement_index_route_unavailable' => 'Aankondigingsindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'announcement_create_route_unavailable' => 'Aankondiging-aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'announcement_store_route_unavailable' => 'Aankondiging-opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'announcement_update_route_unavailable' => 'Aankondiging-updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'announcement_edit_route_unavailable' => 'Aankondiging-bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'announcement_destroy_route_unavailable' => 'Aankondiging-verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'announcement_generate_route_unavailable' => 'Genereren met AI-route is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'announcement_index_route_unavailable' => 'Trasa indeksu ogłoszeń jest niedostępna. ' . self::DELEGATION_PL,
				'announcement_create_route_unavailable' => 'Trasa tworzenia ogłoszeń jest niedostępna. ' . self::DELEGATION_PL,
				'announcement_store_route_unavailable' => 'Trasa przechowywania ogłoszeń jest niedostępna. ' . self::DELEGATION_PL,
				'announcement_update_route_unavailable' => 'Trasa aktualizacji ogłoszeń jest niedostępna. ' . self::DELEGATION_PL,
				'announcement_edit_route_unavailable' => 'Trasa edycji ogłoszeń jest niedostępna. ' . self::DELEGATION_PL,
				'announcement_destroy_route_unavailable' => 'Trasa usuwania ogłoszeń jest niedostępna. ' . self::DELEGATION_PL,
				'announcement_generate_route_unavailable' => 'Trasa generowania za pomocą AI jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'announcement_index_route_unavailable' => 'A rota do índice de anúncios não está disponível. ' . self::DELEGATION_PT,
				'announcement_create_route_unavailable' => 'A rota de criação de anúncios não está disponível. ' . self::DELEGATION_PT,
				'announcement_store_route_unavailable' => 'A rota de armazenamento de anúncios não está disponível. ' . self::DELEGATION_PT,
				'announcement_update_route_unavailable' => 'A rota de atualização de anúncios não está disponível. ' . self::DELEGATION_PT,
				'announcement_edit_route_unavailable' => 'A rota de edição de anúncios não está disponível. ' . self::DELEGATION_PT,
				'announcement_destroy_route_unavailable' => 'A rota de exclusão de anúncios não está disponível. ' . self::DELEGATION_PT,
				'announcement_generate_route_unavailable' => 'A rota de geração por IA não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'announcement_index_route_unavailable' => 'A rota do índice de anúncios não está disponível. ' . self::DELEGATION_PTBR,
				'announcement_create_route_unavailable' => 'A rota de criação de anúncios não está disponível. ' . self::DELEGATION_PTBR,
				'announcement_store_route_unavailable' => 'A rota de armazenamento de anúncios não está disponível. ' . self::DELEGATION_PTBR,
				'announcement_update_route_unavailable' => 'A rota de atualização de anúncios não está disponível. ' . self::DELEGATION_PTBR,
				'announcement_edit_route_unavailable' => 'A rota de edição de anúncios não está disponível. ' . self::DELEGATION_PTBR,
				'announcement_destroy_route_unavailable' => 'A rota de exclusão de anúncios não está disponível. ' . self::DELEGATION_PTBR,
				'announcement_generate_route_unavailable' => 'A rota de geração por IA não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'announcement_index_route_unavailable' => 'Маршрут индекса объявлений недоступен. ' . self::DELEGATION_RU,
				'announcement_create_route_unavailable' => 'Маршрут создания объявлений недоступен. ' . self::DELEGATION_RU,
				'announcement_store_route_unavailable' => 'Маршрут хранения объявлений недоступен. ' . self::DELEGATION_RU,
				'announcement_update_route_unavailable' => 'Маршрут обновления объявлений недоступен. ' . self::DELEGATION_RU,
				'announcement_edit_route_unavailable' => 'Маршрут редактирования объявлений недоступен. ' . self::DELEGATION_RU,
				'announcement_destroy_route_unavailable' => 'Маршрут удаления объявлений недоступен. ' . self::DELEGATION_RU,
				'announcement_generate_route_unavailable' => 'Маршрут генерации с ИИ недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'announcement_index_route_unavailable' => 'Duyuru indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'announcement_create_route_unavailable' => 'Duyuru oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'announcement_store_route_unavailable' => 'Duyuru depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'announcement_update_route_unavailable' => 'Duyuru güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'announcement_edit_route_unavailable' => 'Duyuru düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'announcement_destroy_route_unavailable' => 'Duyuru silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'announcement_generate_route_unavailable' => 'Yapay zeka ile oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'announcement_index_route_unavailable' => '公告索引路由不可用。' . self::DELEGATION_ZH,
				'announcement_create_route_unavailable' => '创建公告路由不可用。' . self::DELEGATION_ZH,
				'announcement_store_route_unavailable' => '公告存储路由不可用。' . self::DELEGATION_ZH,
				'announcement_update_route_unavailable' => '公告更新路由不可用。' . self::DELEGATION_ZH,
				'announcement_edit_route_unavailable' => '编辑公告路由不可用。' . self::DELEGATION_ZH,
				'announcement_destroy_route_unavailable' => '删除公告路由不可用。' . self::DELEGATION_ZH,
				'announcement_generate_route_unavailable' => 'AI生成路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::APR => [
			'ar' => [
				'appraisal_index_route_unavailable' => 'مسار فهرس التقييم غير متاح. ' . self::DELEGATION_AR,
				'appraisal_store_route_unavailable' => 'مسار تخزين التقييم غير متاح. ' . self::DELEGATION_AR,
				'appraisal_update_route_unavailable' => 'مسار تحديث التقييم غير متاح. ' . self::DELEGATION_AR,
				'appraisal_show_route_unavailable' => 'مسار عرض التقييم غير متاح. ' . self::DELEGATION_AR,
				'appraisal_edit_route_unavailable' => 'مسار تعديل التقييم غير متاح. ' . self::DELEGATION_AR,
				'appraisal_destroy_route_unavailable' => 'مسار حذف التقييم غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'appraisal_index_route_unavailable' => 'Evalueringsindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'appraisal_store_route_unavailable' => 'Evalueringslagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'appraisal_update_route_unavailable' => 'Evalueringsopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'appraisal_show_route_unavailable' => 'Evalueringsvisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'appraisal_edit_route_unavailable' => 'Evalueringsredigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'appraisal_destroy_route_unavailable' => 'Evalueringssletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'appraisal_index_route_unavailable' => 'Beurteilungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'appraisal_store_route_unavailable' => 'Beurteilungsspeicher-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'appraisal_update_route_unavailable' => 'Beurteilungsaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'appraisal_show_route_unavailable' => 'Beurteilungsanzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'appraisal_edit_route_unavailable' => 'Beurteilungsbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'appraisal_destroy_route_unavailable' => 'Beurteilungslöschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'appraisal_index_route_unavailable' => 'Appraisal index route is unavailable. ' . self::DELEGATION_EN,
				'appraisal_store_route_unavailable' => 'Appraisal store route is unavailable. ' . self::DELEGATION_EN,
				'appraisal_update_route_unavailable' => 'Appraisal update route is unavailable. ' . self::DELEGATION_EN,
				'appraisal_show_route_unavailable' => 'Appraisal show route is unavailable. ' . self::DELEGATION_EN,
				'appraisal_edit_route_unavailable' => 'Appraisal edit route is unavailable. ' . self::DELEGATION_EN,
				'appraisal_destroy_route_unavailable' => 'Appraisal delete route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'appraisal_index_route_unavailable' => 'La ruta del índice de evaluación no está disponible. ' . self::DELEGATION_ES,
				'appraisal_store_route_unavailable' => 'La ruta de almacenamiento de evaluación no está disponible. ' . self::DELEGATION_ES,
				'appraisal_update_route_unavailable' => 'La ruta de actualización de evaluación no está disponible. ' . self::DELEGATION_ES,
				'appraisal_show_route_unavailable' => 'La ruta de visualización de evaluación no está disponible. ' . self::DELEGATION_ES,
				'appraisal_edit_route_unavailable' => 'La ruta de edición de evaluación no está disponible. ' . self::DELEGATION_ES,
				'appraisal_destroy_route_unavailable' => 'La ruta de eliminación de evaluación no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'appraisal_index_route_unavailable' => 'La route de l\'index d\'évaluation est indisponible. ' . self::DELEGATION_FR,
				'appraisal_store_route_unavailable' => 'La route de stockage d\'évaluation est indisponible. ' . self::DELEGATION_FR,
				'appraisal_update_route_unavailable' => 'La route de mise à jour d\'évaluation est indisponible. ' . self::DELEGATION_FR,
				'appraisal_show_route_unavailable' => 'La route d\'affichage d\'évaluation est indisponible. ' . self::DELEGATION_FR,
				'appraisal_edit_route_unavailable' => 'La route d\'édition d\'évaluation est indisponible. ' . self::DELEGATION_FR,
				'appraisal_destroy_route_unavailable' => 'La route de suppression d\'évaluation est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'appraisal_index_route_unavailable' => 'נתיב אינדקס הערכה אינו זמין. ' . self::DELEGATION_HE,
				'appraisal_store_route_unavailable' => 'נתיב אחסון הערכה אינו זמין. ' . self::DELEGATION_HE,
				'appraisal_update_route_unavailable' => 'נתיב עדכון הערכה אינו זמין. ' . self::DELEGATION_HE,
				'appraisal_show_route_unavailable' => 'נתיב הצגת הערכה אינו זמין. ' . self::DELEGATION_HE,
				'appraisal_edit_route_unavailable' => 'נתיב עריכת הערכה אינו זמין. ' . self::DELEGATION_HE,
				'appraisal_destroy_route_unavailable' => 'נתיב מחיקת הערכה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'appraisal_index_route_unavailable' => 'La rotta dell\'indice di valutazione non è disponibile. ' . self::DELEGATION_IT,
				'appraisal_store_route_unavailable' => 'La rotta di memorizzazione della valutazione non è disponibile. ' . self::DELEGATION_IT,
				'appraisal_update_route_unavailable' => 'La rotta di aggiornamento della valutazione non è disponibile. ' . self::DELEGATION_IT,
				'appraisal_show_route_unavailable' => 'La rotta di visualizzazione della valutazione non è disponibile. ' . self::DELEGATION_IT,
				'appraisal_edit_route_unavailable' => 'La rotta di modifica della valutazione non è disponibile. ' . self::DELEGATION_IT,
				'appraisal_destroy_route_unavailable' => 'La rotta di eliminazione della valutazione non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'appraisal_index_route_unavailable' => '評価インデックスルートは利用できません。' . self::DELEGATION_JA,
				'appraisal_store_route_unavailable' => '評価保存ルートは利用できません。' . self::DELEGATION_JA,
				'appraisal_update_route_unavailable' => '評価更新ルートは利用できません。' . self::DELEGATION_JA,
				'appraisal_show_route_unavailable' => '評価表示ルートは利用できません。' . self::DELEGATION_JA,
				'appraisal_edit_route_unavailable' => '評価編集ルートは利用できません。' . self::DELEGATION_JA,
				'appraisal_destroy_route_unavailable' => '評価削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'appraisal_index_route_unavailable' => 'Beoordelingsindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'appraisal_store_route_unavailable' => 'Beoordelingsopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'appraisal_update_route_unavailable' => 'Beoordelingsupdateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'appraisal_show_route_unavailable' => 'Beoordelingsweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'appraisal_edit_route_unavailable' => 'Beoordelingsbewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'appraisal_destroy_route_unavailable' => 'Beoordelingsverwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'appraisal_index_route_unavailable' => 'Trasa indeksu ocen jest niedostępna. ' . self::DELEGATION_PL,
				'appraisal_store_route_unavailable' => 'Trasa przechowywania ocen jest niedostępna. ' . self::DELEGATION_PL,
				'appraisal_update_route_unavailable' => 'Trasa aktualizacji ocen jest niedostępna. ' . self::DELEGATION_PL,
				'appraisal_show_route_unavailable' => 'Trasa wyświetlania ocen jest niedostępna. ' . self::DELEGATION_PL,
				'appraisal_edit_route_unavailable' => 'Trasa edycji ocen jest niedostępna. ' . self::DELEGATION_PL,
				'appraisal_destroy_route_unavailable' => 'Trasa usuwania ocen jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'appraisal_index_route_unavailable' => 'A rota do índice de avaliação não está disponível. ' . self::DELEGATION_PT,
				'appraisal_store_route_unavailable' => 'A rota de armazenamento de avaliação não está disponível. ' . self::DELEGATION_PT,
				'appraisal_update_route_unavailable' => 'A rota de atualização de avaliação não está disponível. ' . self::DELEGATION_PT,
				'appraisal_show_route_unavailable' => 'A rota de exibição de avaliação não está disponível. ' . self::DELEGATION_PT,
				'appraisal_edit_route_unavailable' => 'A rota de edição de avaliação não está disponível. ' . self::DELEGATION_PT,
				'appraisal_destroy_route_unavailable' => 'A rota de exclusão de avaliação não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'appraisal_index_route_unavailable' => 'A rota do índice de avaliação não está disponível. ' . self::DELEGATION_PTBR,
				'appraisal_store_route_unavailable' => 'A rota de armazenamento de avaliação não está disponível. ' . self::DELEGATION_PTBR,
				'appraisal_update_route_unavailable' => 'A rota de atualização de avaliação não está disponível. ' . self::DELEGATION_PTBR,
				'appraisal_show_route_unavailable' => 'A rota de exibição de avaliação não está disponível. ' . self::DELEGATION_PTBR,
				'appraisal_edit_route_unavailable' => 'A rota de edição de avaliação não está disponível. ' . self::DELEGATION_PTBR,
				'appraisal_destroy_route_unavailable' => 'A rota de exclusão de avaliação não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'appraisal_index_route_unavailable' => 'Маршрут индекса оценки недоступен. ' . self::DELEGATION_RU,
				'appraisal_store_route_unavailable' => 'Маршрут хранения оценки недоступен. ' . self::DELEGATION_RU,
				'appraisal_update_route_unavailable' => 'Маршрут обновления оценки недоступен. ' . self::DELEGATION_RU,
				'appraisal_show_route_unavailable' => 'Маршрут просмотра оценки недоступен. ' . self::DELEGATION_RU,
				'appraisal_edit_route_unavailable' => 'Маршрут редактирования оценки недоступен. ' . self::DELEGATION_RU,
				'appraisal_destroy_route_unavailable' => 'Маршрут удаления оценки недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'appraisal_index_route_unavailable' => 'Değerlendirme indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'appraisal_store_route_unavailable' => 'Değerlendirme depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'appraisal_update_route_unavailable' => 'Değerlendirme güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'appraisal_show_route_unavailable' => 'Değerlendirme görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'appraisal_edit_route_unavailable' => 'Değerlendirme düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'appraisal_destroy_route_unavailable' => 'Değerlendirme silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'appraisal_index_route_unavailable' => '评估索引路由不可用。' . self::DELEGATION_ZH,
				'appraisal_store_route_unavailable' => '评估存储路由不可用。' . self::DELEGATION_ZH,
				'appraisal_update_route_unavailable' => '评估更新路由不可用。' . self::DELEGATION_ZH,
				'appraisal_show_route_unavailable' => '评估查看路由不可用。' . self::DELEGATION_ZH,
				'appraisal_edit_route_unavailable' => '评估编辑路由不可用。' . self::DELEGATION_ZH,
				'appraisal_destroy_route_unavailable' => '评估删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::ATD => [
			'ar' => [
				'index_attendance_unavailable' => 'مسار فهرس حضور الموظفين غير متاح. ' . self::DELEGATION_AR,
				'csv_attendance_unavailable' => 'مسار استيراد ملف CSV للموظفين غير متاح. ' . self::DELEGATION_AR,
				'edit_attendance_unavailable' => 'مسار تعديل الحضور غير متاح. ' . self::DELEGATION_AR,
				'delete_attendance_unavailable' => 'مسار حذف الحضور غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'index_attendance_unavailable' => 'Medarbejder tilstedeværelsesindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'csv_attendance_unavailable' => 'Import medarbejder CSV-filrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'edit_attendance_unavailable' => 'Rediger tilstedeværelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'delete_attendance_unavailable' => 'Slet tilstedeværelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'index_attendance_unavailable' => 'Die Mitarbeiter-Anwesenheitsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'csv_attendance_unavailable' => 'Die Route zum Importieren von Mitarbeiter-CSV-Dateien ist nicht verfügbar. ' . self::DELEGATION_DE,
				'edit_attendance_unavailable' => 'Die Anwesenheitsbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'delete_attendance_unavailable' => 'Die Anwesenheitslöschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'index_attendance_unavailable' => 'Employee attendance index route is unavailable. ' . self::DELEGATION_EN,
				'csv_attendance_unavailable' => 'Import employee CSV file route is unavailable. ' . self::DELEGATION_EN,
				'edit_attendance_unavailable' => 'Edit attendance route is unavailable. ' . self::DELEGATION_EN,
				'delete_attendance_unavailable' => 'Delete attendance route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'index_attendance_unavailable' => 'La ruta del índice de asistencia de empleados no está disponible.' . self::DELEGATION_ES,
				'csv_attendance_unavailable' => 'La ruta de importación de archivos CSV de empleados no está disponible.' . self::DELEGATION_ES,
				'edit_attendance_unavailable' => 'La ruta de edición de asistencia no está disponible.' . self::DELEGATION_ES,
				'delete_attendance_unavailable' => 'La ruta de eliminación de asistencia no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'index_attendance_unavailable' => 'La route de l\'index de présence des employés est indisponible.' . self::DELEGATION_FR,
				'csv_attendance_unavailable' => 'La route d\'importation de fichiers CSV des employés est indisponible.' . self::DELEGATION_FR,
				'edit_attendance_unavailable' => 'La route de modification de présence est indisponible.' . self::DELEGATION_FR,
				'delete_attendance_unavailable' => 'La route de suppression de présence est indisponible.' . self::DELEGATION_FR
			],
			'he' => [
				'index_attendance_unavailable' => 'מסלול אינדקס נוכחות עובדים אינו זמין. ' . self::DELEGATION_HE,
				'csv_attendance_unavailable' => 'מסלול ייבוא קובץ CSV של עובדים אינו זמין. ' . self::DELEGATION_HE,
				'edit_attendance_unavailable' => 'מסלול עריכת נוכחות אינו זמין. ' . self::DELEGATION_HE,
				'delete_attendance_unavailable' => 'מסלול מחיקת נוכחות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'index_attendance_unavailable' => 'La rotta dell\'indice delle presenze dei dipendenti non è disponibile. ' . self::DELEGATION_IT,
				'csv_attendance_unavailable' => 'La rotta di importazione del file CSV dei dipendenti non è disponibile. ' . self::DELEGATION_IT,
				'edit_attendance_unavailable' => 'La rotta di modifica della presenza non è disponibile. ' . self::DELEGATION_IT,
				'delete_attendance_unavailable' => 'La rotta di eliminazione della presenza non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'index_attendance_unavailable' => '従業員勤怠インデックスルートは利用できません。' . self::DELEGATION_JA,
				'csv_attendance_unavailable' => '従業員CSVファイルインポートルートは利用できません。' . self::DELEGATION_JA,
				'edit_attendance_unavailable' => '勤怠編集ルートは利用できません。' . self::DELEGATION_JA,
				'delete_attendance_unavailable' => '勤怠削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'index_attendance_unavailable' => 'Medewerker aanwezigheidsindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'csv_attendance_unavailable' => 'Import medewerker CSV-bestandsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'edit_attendance_unavailable' => 'Aanwezigheidsbewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'delete_attendance_unavailable' => 'Aanwezigheidsverwijdingsroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'index_attendance_unavailable' => 'Trasa indeksu obecności pracowników jest niedostępna. ' . self::DELEGATION_PL,
				'csv_attendance_unavailable' => 'Trasa importu pliku CSV pracowników jest niedostępna. ' . self::DELEGATION_PL,
				'edit_attendance_unavailable' => 'Trasa edycji obecności jest niedostępna. ' . self::DELEGATION_PL,
				'delete_attendance_unavailable' => 'Trasa usuwania obecności jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'index_attendance_unavailable' => 'A rota de índice de presença de funcionários não está disponível.' . self::DELEGATION_PT,
				'csv_attendance_unavailable' => 'A rota de importação de ficheiro CSV de funcionários não está disponível.' . self::DELEGATION_PT,
				'edit_attendance_unavailable' => 'A rota de edição de presença não está disponível.' . self::DELEGATION_PT,
				'delete_attendance_unavailable' => 'A rota de eliminação de presença não está disponível.' . self::DELEGATION_PT
			],
			'pt-br' => [
				'index_attendance_unavailable' => 'A rota de índice de atendimento de funcionários não está disponível. ' . self::DELEGATION_PTBR,
				'csv_attendance_unavailable' => 'A rota de importação de arquivo CSV de funcionários não está disponível. ' . self::DELEGATION_PTBR,
				'edit_attendance_unavailable' => 'A rota de edição de atendimento não está disponível. ' . self::DELEGATION_PTBR,
				'delete_attendance_unavailable' => 'A rota de exclusão de atendimento não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'index_attendance_unavailable' => 'Маршрут индекса посещаемости сотрудников недоступен. ' . self::DELEGATION_RU,
				'csv_attendance_unavailable' => 'Маршрут импорта CSV-файла сотрудников недоступен. ' . self::DELEGATION_RU,
				'edit_attendance_unavailable' => 'Маршрут редактирования посещаемости недоступен. ' . self::DELEGATION_RU,
				'delete_attendance_unavailable' => 'Маршрут удаления посещаемости недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'index_attendance_unavailable' => 'Personel katılım indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'csv_attendance_unavailable' => 'Personel CSV dosyası içe aktarma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'edit_attendance_unavailable' => 'Katılım düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'delete_attendance_unavailable' => 'Katılım silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'index_attendance_unavailable' => '员工考勤索引路由不可用。' . self::DELEGATION_ZH,
				'csv_attendance_unavailable' => '导入员工CSV文件路由不可用。' . self::DELEGATION_ZH,
				'edit_attendance_unavailable' => '编辑考勤路由不可用。' . self::DELEGATION_ZH,
				'delete_attendance_unavailable' => '删除考勤路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::AUT => [
			'ar' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_AR,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_AR,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_AR,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_AR,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_AR,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_AR,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_AR,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_DA,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_DA,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_DA,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_DA,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_DA,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_DA,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_DA,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_DE,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_DE,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_DE,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_DE,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_DE,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_DE,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_DE,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_EN,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_EN,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_EN,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_EN,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_EN,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_EN,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_EN,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_ES,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_ES,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_ES,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_ES,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_ES,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_ES,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_ES,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_FR,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_FR,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_FR,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_FR,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_FR,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_FR,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_FR,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_HE,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_HE,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_HE,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_HE,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_HE,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_HE,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_HE,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_IT,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_IT,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_IT,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_IT,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_IT,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_IT,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_IT,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_JA,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_JA,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_JA,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_JA,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_JA,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_JA,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_JA,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_NL,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_NL,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_NL,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_NL,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_NL,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_NL,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_NL,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_PL,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_PL,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_PL,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_PL,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_PL,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_PL,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_PL,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_PT,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_PT,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_PT,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_PT,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_PT,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_PT,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_PT,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_PTBR,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_PTBR,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_PTBR,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_PTBR,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_PTBR,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_PTBR,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_PTBR,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_RU,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_RU,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_RU,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_RU,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_RU,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_RU,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_RU,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_TR,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_TR,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_TR,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_TR,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_TR,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_TR,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_TR,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'login_unavailable' => 'Login route is unavailable. ' . self::DELEGATION_ZH,
				'register_unavailable' => 'Registration route is unavailable. ' . self::DELEGATION_ZH,
				'localized_register_unavailable' => 'Translated registration route is unavailable. ' . self::DELEGATION_ZH,
				'verification_notice_route_unavailable' => 'Verification notice route is unavailable. ' . self::DELEGATION_ZH,
				'verification_send_route_unavailable' => 'Resend verification route is unavailable. ' . self::DELEGATION_ZH,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_ZH,
				'password_request_route_unavailable' => 'Password request route is unavailable. ' . self::DELEGATION_ZH,
				'password_email_route_unavailable' => 'Password reset email route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::AWD => [
			'ar' => [
				'award_index_route_unavailable' => 'مسار فهرس الجوائز غير متاح. ' . self::DELEGATION_AR,
				'award_store_route_unavailable' => 'مسار تخزين الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_generate_route_unavailable' => 'مسار توليد الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_update_route_unavailable' => 'مسار تحديث الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_edit_route_unavailable' => 'مسار تعديل الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_destroy_route_unavailable' => 'مسار حذف الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_create_route_unavailable' => 'مسار إنشاء الجائزة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'award_index_route_unavailable' => 'Prisindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_store_route_unavailable' => 'Prislagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_generate_route_unavailable' => 'Prisgenereringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_update_route_unavailable' => 'Prisopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_edit_route_unavailable' => 'Prisredigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_destroy_route_unavailable' => 'Prissletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_create_route_unavailable' => 'Prisoprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'award_index_route_unavailable' => 'Auszeichnungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_store_route_unavailable' => 'Auszeichnungsspeicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_generate_route_unavailable' => 'Auszeichnungserzeugungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_update_route_unavailable' => 'Auszeichnungsaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_edit_route_unavailable' => 'Auszeichnungsbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_destroy_route_unavailable' => 'Auszeichnungslöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_create_route_unavailable' => 'Auszeichnungserstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'award_index_route_unavailable' => 'Award index route is unavailable. ' . self::DELEGATION_EN,
				'award_store_route_unavailable' => 'Award store route is unavailable. ' . self::DELEGATION_EN,
				'award_generate_route_unavailable' => 'Award generate route is unavailable. ' . self::DELEGATION_EN,
				'award_update_route_unavailable' => 'Award update route is unavailable. ' . self::DELEGATION_EN,
				'award_edit_route_unavailable' => 'Award edit route is unavailable. ' . self::DELEGATION_EN,
				'award_destroy_route_unavailable' => 'Award destroy route is unavailable. ' . self::DELEGATION_EN,
				'award_create_route_unavailable' => 'Award create route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'award_index_route_unavailable' => 'La ruta del índice de premios no está disponible. ' . self::DELEGATION_ES,
				'award_store_route_unavailable' => 'La ruta de almacenamiento de premios no está disponible. ' . self::DELEGATION_ES,
				'award_generate_route_unavailable' => 'La ruta de generación de premios no está disponible. ' . self::DELEGATION_ES,
				'award_update_route_unavailable' => 'La ruta de actualización de premios no está disponible. ' . self::DELEGATION_ES,
				'award_edit_route_unavailable' => 'La ruta de edición de premios no está disponible. ' . self::DELEGATION_ES,
				'award_destroy_route_unavailable' => 'La ruta de eliminación de premios no está disponible. ' . self::DELEGATION_ES,
				'award_create_route_unavailable' => 'La ruta de creación de premios no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'award_index_route_unavailable' => 'La route de l\'index des récompenses est indisponible. ' . self::DELEGATION_FR,
				'award_store_route_unavailable' => 'La route de stockage des récompenses est indisponible. ' . self::DELEGATION_FR,
				'award_generate_route_unavailable' => 'La route de génération des récompenses est indisponible. ' . self::DELEGATION_FR,
				'award_update_route_unavailable' => 'La route de mise à jour des récompenses est indisponible. ' . self::DELEGATION_FR,
				'award_edit_route_unavailable' => 'La route d\'édition des récompenses est indisponible. ' . self::DELEGATION_FR,
				'award_destroy_route_unavailable' => 'La route de suppression des récompenses est indisponible. ' . self::DELEGATION_FR,
				'award_create_route_unavailable' => 'La route de création des récompenses est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'award_index_route_unavailable' => 'נתיב אינדקס פרסים אינו זמין. ' . self::DELEGATION_HE,
				'award_store_route_unavailable' => 'נתיב אחסון פרס אינו זמין. ' . self::DELEGATION_HE,
				'award_generate_route_unavailable' => 'נתיב יצירת פרס אינו זמין. ' . self::DELEGATION_HE,
				'award_update_route_unavailable' => 'נתיב עדכון פרס אינו זמין. ' . self::DELEGATION_HE,
				'award_edit_route_unavailable' => 'נתיב עריכת פרס אינו זמין. ' . self::DELEGATION_HE,
				'award_destroy_route_unavailable' => 'נתיב מחיקת פרס אינו זמין. ' . self::DELEGATION_HE,
				'award_create_route_unavailable' => 'נתיב יצירת פרס אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'award_index_route_unavailable' => 'La rotta dell\'indice dei premi non è disponibile. ' . self::DELEGATION_IT,
				'award_store_route_unavailable' => 'La rotta di memorizzazione del premio non è disponibile. ' . self::DELEGATION_IT,
				'award_generate_route_unavailable' => 'La rotta di generazione del premio non è disponibile. ' . self::DELEGATION_IT,
				'award_update_route_unavailable' => 'La rotta di aggiornamento del premio non è disponibile. ' . self::DELEGATION_IT,
				'award_edit_route_unavailable' => 'La rotta di modifica del premio non è disponibile. ' . self::DELEGATION_IT,
				'award_destroy_route_unavailable' => 'La rotta di eliminazione del premio non è disponibile. ' . self::DELEGATION_IT,
				'award_create_route_unavailable' => 'La rotta di creazione del premio non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'award_index_route_unavailable' => '賞インデックスルートは利用できません。' . self::DELEGATION_JA,
				'award_store_route_unavailable' => '賞保存ルートは利用できません。' . self::DELEGATION_JA,
				'award_generate_route_unavailable' => '賞生成ルートは利用できません。' . self::DELEGATION_JA,
				'award_update_route_unavailable' => '賞更新ルートは利用できません。' . self::DELEGATION_JA,
				'award_edit_route_unavailable' => '賞編集ルートは利用できません。' . self::DELEGATION_JA,
				'award_destroy_route_unavailable' => '賞削除ルートは利用できません。' . self::DELEGATION_JA,
				'award_create_route_unavailable' => '賞作成ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'award_index_route_unavailable' => 'Prijzenindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_store_route_unavailable' => 'Prijzenopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_generate_route_unavailable' => 'Prijzengeneratieroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_update_route_unavailable' => 'Prijzenupdateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_edit_route_unavailable' => 'Prijzenbewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_destroy_route_unavailable' => 'Prijzenverwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_create_route_unavailable' => 'Prijzenaanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'award_index_route_unavailable' => 'Trasa indeksu nagród jest niedostępna. ' . self::DELEGATION_PL,
				'award_store_route_unavailable' => 'Trasa przechowywania nagród jest niedostępna. ' . self::DELEGATION_PL,
				'award_generate_route_unavailable' => 'Trasa generowania nagród jest niedostępna. ' . self::DELEGATION_PL,
				'award_update_route_unavailable' => 'Trasa aktualizacji nagród jest niedostępna. ' . self::DELEGATION_PL,
				'award_edit_route_unavailable' => 'Trasa edycji nagród jest niedostępna. ' . self::DELEGATION_PL,
				'award_destroy_route_unavailable' => 'Trasa usuwania nagród jest niedostępna. ' . self::DELEGATION_PL,
				'award_create_route_unavailable' => 'Trasa tworzenia nagród jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'award_index_route_unavailable' => 'A rota do índice de prémios não está disponível. ' . self::DELEGATION_PT,
				'award_store_route_unavailable' => 'A rota de armazenamento de prémios não está disponível. ' . self::DELEGATION_PT,
				'award_generate_route_unavailable' => 'A rota de geração de prémios não está disponível. ' . self::DELEGATION_PT,
				'award_update_route_unavailable' => 'A rota de atualização de prémios não está disponível. ' . self::DELEGATION_PT,
				'award_edit_route_unavailable' => 'A rota de edição de prémios não está disponível. ' . self::DELEGATION_PT,
				'award_destroy_route_unavailable' => 'A rota de eliminação de prémios não está disponível. ' . self::DELEGATION_PT,
				'award_create_route_unavailable' => 'A rota de criação de prémios não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'award_index_route_unavailable' => 'A rota do índice de premiações não está disponível. ' . self::DELEGATION_PTBR,
				'award_store_route_unavailable' => 'A rota de armazenamento de premiações não está disponível. ' . self::DELEGATION_PTBR,
				'award_generate_route_unavailable' => 'A rota de geração de premiações não está disponível. ' . self::DELEGATION_PTBR,
				'award_update_route_unavailable' => 'A rota de atualização de premiações não está disponível. ' . self::DELEGATION_PTBR,
				'award_edit_route_unavailable' => 'A rota de edição de premiações não está disponível. ' . self::DELEGATION_PTBR,
				'award_destroy_route_unavailable' => 'A rota de eliminação de premiações não está disponível. ' . self::DELEGATION_PTBR,
				'award_create_route_unavailable' => 'A rota de criação de premiações não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'award_index_route_unavailable' => 'Маршрут индекса наград недоступен. ' . self::DELEGATION_RU,
				'award_store_route_unavailable' => 'Маршрут хранения награды недоступен. ' . self::DELEGATION_RU,
				'award_generate_route_unavailable' => 'Маршрут генерации награды недоступен. ' . self::DELEGATION_RU,
				'award_update_route_unavailable' => 'Маршрут обновления награды недоступен. ' . self::DELEGATION_RU,
				'award_edit_route_unavailable' => 'Маршрут редактирования награды недоступен. ' . self::DELEGATION_RU,
				'award_destroy_route_unavailable' => 'Маршрут удаления награды недоступен. ' . self::DELEGATION_RU,
				'award_create_route_unavailable' => 'Маршрут создания награды недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'award_index_route_unavailable' => 'Ödül indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_store_route_unavailable' => 'Ödül depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_generate_route_unavailable' => 'Ödül oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_update_route_unavailable' => 'Ödül güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_edit_route_unavailable' => 'Ödül düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_destroy_route_unavailable' => 'Ödül silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_create_route_unavailable' => 'Ödül oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'award_index_route_unavailable' => '奖项索引路由不可用。' . self::DELEGATION_ZH,
				'award_store_route_unavailable' => '奖项存储路由不可用。' . self::DELEGATION_ZH,
				'award_generate_route_unavailable' => '奖项生成路由不可用。' . self::DELEGATION_ZH,
				'award_update_route_unavailable' => '奖项更新路由不可用。' . self::DELEGATION_ZH,
				'award_edit_route_unavailable' => '奖项编辑路由不可用。' . self::DELEGATION_ZH,
				'award_destroy_route_unavailable' => '奖项删除路由不可用。' . self::DELEGATION_ZH,
				'award_create_route_unavailable' => '奖项创建路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::AWD_TP => [
			'ar' => [
				'award_type_update_route_unavailable' => 'مسار تحديث نوع الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_type_store_route_unavailable' => 'مسار تخزين نوع الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_type_create_route_unavailable' => 'مسار إنشاء نوع الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_type_edit_route_unavailable' => 'مسار تعديل نوع الجائزة غير متاح. ' . self::DELEGATION_AR,
				'award_type_destroy_route_unavailable' => 'مسار حذف نوع الجائزة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'award_type_update_route_unavailable' => 'Pristypeopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_type_store_route_unavailable' => 'Pristypelagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_type_create_route_unavailable' => 'Opret pris type rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_type_edit_route_unavailable' => 'Pris type redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'award_type_destroy_route_unavailable' => 'Slet pris type rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'award_type_update_route_unavailable' => 'Auszeichnungstyp-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_type_store_route_unavailable' => 'Auszeichnungstyp-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_type_create_route_unavailable' => 'Auszeichnungstyp-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_type_edit_route_unavailable' => 'Auszeichnungstyp-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'award_type_destroy_route_unavailable' => 'Auszeichnungstyp-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'award_type_update_route_unavailable' => 'Award Type update route is unavailable. ' . self::DELEGATION_EN,
				'award_type_store_route_unavailable' => 'Award Type store route is unavailable. ' . self::DELEGATION_EN,
				'award_type_create_route_unavailable' => 'Create Award Type route is unavailable. ' . self::DELEGATION_EN,
				'award_type_edit_route_unavailable' => 'Award Type edit route is unavailable. ' . self::DELEGATION_EN,
				'award_type_destroy_route_unavailable' => 'Delete Award Type route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'award_type_update_route_unavailable' => 'La ruta de actualización de tipo de premio no está disponible. ' . self::DELEGATION_ES,
				'award_type_store_route_unavailable' => 'La ruta de almacenamiento de tipo de premio no está disponible. ' . self::DELEGATION_ES,
				'award_type_create_route_unavailable' => 'La ruta de creación de tipo de premio no está disponible. ' . self::DELEGATION_ES,
				'award_type_edit_route_unavailable' => 'La ruta de edición de tipo de premio no está disponible. ' . self::DELEGATION_ES,
				'award_type_destroy_route_unavailable' => 'La ruta de eliminación de tipo de premio no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'award_type_update_route_unavailable' => 'La route de mise à jour du type de récompense est indisponible. ' . self::DELEGATION_FR,
				'award_type_store_route_unavailable' => 'La route de stockage du type de récompense est indisponible. ' . self::DELEGATION_FR,
				'award_type_create_route_unavailable' => 'La route de création du type de récompense est indisponible. ' . self::DELEGATION_FR,
				'award_type_edit_route_unavailable' => 'La route d\'édition du type de récompense est indisponible. ' . self::DELEGATION_FR,
				'award_type_destroy_route_unavailable' => 'La route de suppression du type de récompense est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'award_type_update_route_unavailable' => 'נתיב עדכון סוג הפרס אינו זמין. ' . self::DELEGATION_HE,
				'award_type_store_route_unavailable' => 'נתיב אחסון סוג הפרס אינו זמין. ' . self::DELEGATION_HE,
				'award_type_create_route_unavailable' => 'נתיב יצירת סוג הפרס אינו זמין. ' . self::DELEGATION_HE,
				'award_type_edit_route_unavailable' => 'נתיב עריכת סוג הפרס אינו זמין. ' . self::DELEGATION_HE,
				'award_type_destroy_route_unavailable' => 'נתיב מחיקת סוג הפרס אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'award_type_update_route_unavailable' => 'La rotta di aggiornamento del tipo di premio non è disponibile. ' . self::DELEGATION_IT,
				'award_type_store_route_unavailable' => 'La rotta di memorizzazione del tipo di premio non è disponibile. ' . self::DELEGATION_IT,
				'award_type_create_route_unavailable' => 'La rotta di creazione del tipo di premio non è disponibile. ' . self::DELEGATION_IT,
				'award_type_edit_route_unavailable' => 'La rotta di modifica del tipo di premio non è disponibile. ' . self::DELEGATION_IT,
				'award_type_destroy_route_unavailable' => 'La rotta di eliminazione del tipo di premio non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'award_type_update_route_unavailable' => '表彰タイプ更新ルートは利用できません。' . self::DELEGATION_JA,
				'award_type_store_route_unavailable' => '表彰タイプ保存ルートは利用できません。' . self::DELEGATION_JA,
				'award_type_create_route_unavailable' => '表彰タイプ作成ルートは利用できません。' . self::DELEGATION_JA,
				'award_type_edit_route_unavailable' => '表彰タイプ編集ルートは利用できません。' . self::DELEGATION_JA,
				'award_type_destroy_route_unavailable' => '表彰タイプ削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'award_type_update_route_unavailable' => 'Prijstype-updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_type_store_route_unavailable' => 'Prijstype-opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_type_create_route_unavailable' => 'Prijstype-aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_type_edit_route_unavailable' => 'Prijstype-bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'award_type_destroy_route_unavailable' => 'Prijstype-verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'award_type_update_route_unavailable' => 'Trasa aktualizacji typu nagrody jest niedostępna. ' . self::DELEGATION_PL,
				'award_type_store_route_unavailable' => 'Trasa przechowywania typu nagrody jest niedostępna. ' . self::DELEGATION_PL,
				'award_type_create_route_unavailable' => 'Trasa tworzenia typu nagrody jest niedostępna. ' . self::DELEGATION_PL,
				'award_type_edit_route_unavailable' => 'Trasa edycji typu nagrody jest niedostępna. ' . self::DELEGATION_PL,
				'award_type_destroy_route_unavailable' => 'Trasa usuwania typu nagrody jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'award_type_update_route_unavailable' => 'A rota de atualização de tipo de prêmio não está disponível. ' . self::DELEGATION_PT,
				'award_type_store_route_unavailable' => 'A rota de armazenamento de tipo de prêmio não está disponível. ' . self::DELEGATION_PT,
				'award_type_create_route_unavailable' => 'A rota de criação de tipo de prêmio não está disponível. ' . self::DELEGATION_PT,
				'award_type_edit_route_unavailable' => 'A rota de edição de tipo de prêmio não está disponível. ' . self::DELEGATION_PT,
				'award_type_destroy_route_unavailable' => 'A rota de exclusão de tipo de prêmio não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'award_type_update_route_unavailable' => 'A rota de atualização de tipo de prêmio não está disponível. ' . self::DELEGATION_PTBR,
				'award_type_store_route_unavailable' => 'A rota de armazenamento de tipo de prêmio não está disponível. ' . self::DELEGATION_PTBR,
				'award_type_create_route_unavailable' => 'A rota de criação de tipo de prêmio não está disponível. ' . self::DELEGATION_PTBR,
				'award_type_edit_route_unavailable' => 'A rota de edição de tipo de prêmio não está disponível. ' . self::DELEGATION_PTBR,
				'award_type_destroy_route_unavailable' => 'A rota de exclusão de tipo de prêmio não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'award_type_update_route_unavailable' => 'Маршрут обновления типа награды недоступен. ' . self::DELEGATION_RU,
				'award_type_store_route_unavailable' => 'Маршрут хранения типа награды недоступен. ' . self::DELEGATION_RU,
				'award_type_create_route_unavailable' => 'Маршрут создания типа награды недоступен. ' . self::DELEGATION_RU,
				'award_type_edit_route_unavailable' => 'Маршрут редактирования типа награды недоступен. ' . self::DELEGATION_RU,
				'award_type_destroy_route_unavailable' => 'Маршрут удаления типа награды недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'award_type_update_route_unavailable' => 'Ödül tipi güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_type_store_route_unavailable' => 'Ödül tipi depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_type_create_route_unavailable' => 'Ödül tipi oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_type_edit_route_unavailable' => 'Ödül tipi düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'award_type_destroy_route_unavailable' => 'Ödül tipi silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'award_type_update_route_unavailable' => '奖励类型更新路由不可用。' . self::DELEGATION_ZH,
				'award_type_store_route_unavailable' => '奖励类型存储路由不可用。' . self::DELEGATION_ZH,
				'award_type_create_route_unavailable' => '创建奖励类型路由不可用。' . self::DELEGATION_ZH,
				'award_type_edit_route_unavailable' => '奖励类型编辑路由不可用。' . self::DELEGATION_ZH,
				'award_type_destroy_route_unavailable' => '删除奖励类型路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::BDG => [
			'ar' => [
				'budget_index_route_unavailable' => 'مسار مخطط الميزانية غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'budget_index_route_unavailable' => 'Budsætningsplanlægningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'budget_index_route_unavailable' => 'Budgetplaner-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'budget_index_route_unavailable' => 'Budget Planner route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'budget_index_route_unavailable' => 'La ruta del Planificador de presupuestos no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'budget_index_route_unavailable' => 'La route du planificateur de budget n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'budget_index_route_unavailable' => 'נתיב מתכנן התקציב אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'budget_index_route_unavailable' => 'Il percorso del planner di budget non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'budget_index_route_unavailable' => '予算プランナールートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'budget_index_route_unavailable' => 'Budgetplannerroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'budget_index_route_unavailable' => 'Trasa planera budżetu jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'budget_index_route_unavailable' => 'A rota do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'budget_index_route_unavailable' => 'A rota do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'budget_index_route_unavailable' => 'Маршрут планировщика бюджета недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'budget_index_route_unavailable' => 'Bütçe Planlayıcı rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'budget_index_route_unavailable' => '预算计划路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::BIL => [
			'ar' => [
				'bill_index_route_unavailable'        => 'مسار قائمة الفواتير غير متاح. '                . self::DELEGATION_AR,
				'bill_store_route_unavailable'        => 'مسار حفظ الفاتورة غير متاح. '                  . self::DELEGATION_AR,
				'bill_vendor_fetch_route_unavailable' => 'مسار جلب بيانات البائع غير متاح. '            . self::DELEGATION_AR,
				'product_fetch_route_unavailable'     => 'مسار جلب المنتج غير متاح. '                  . self::DELEGATION_AR,
				'bill_pdf_route_unavailable'          => 'مسار ملف PDF للفاتورة غير متاح. '           . self::DELEGATION_AR,
				'bill_link_copy_route_unavailable'    => 'مسار نسخ رابط الفاتورة غير متاح. '          . self::DELEGATION_AR,
				'bill_update_route_unavailable'       => 'مسار تحديث الفاتورة غير متاح. '             . self::DELEGATION_AR,
				'bill_create_route_unavailable'       => 'مسار إنشاء الفاتورة غير متاح. '            . self::DELEGATION_AR,
				'bill_export_route_unavailable'       => 'مسار تصدير الفاتورة غير متاح. '             . self::DELEGATION_AR,
				'bill_show_route_unavailable'         => 'مسار عرض الفاتورة غير متاح. '               . self::DELEGATION_AR,
				'bill_duplicate_route_unavailable'    => 'مسار تكرار الفاتورة غير متاح. '            . self::DELEGATION_AR,
				'bill_edit_route_unavailable'         => 'مسار تعديل الفاتورة غير متاح. '            . self::DELEGATION_AR,
				'bill_destroy_route_unavailable'      => 'مسار حذف الفاتورة غير متاح. '              . self::DELEGATION_AR,
				'bill_payment_route_unavailable'      => 'مسار دفع الفاتورة غير متاح. '              . self::DELEGATION_AR,
				'bill_sent_route_unavailable'         => 'مسار إرسال الفاتورة غير متاح. '            . self::DELEGATION_AR,
				'bill_resent_route_unavailable'       => 'مسار إعادة إرسال الفاتورة غير متاح. '      . self::DELEGATION_AR,
				'bill_debit_note_route_unavailable'   => 'مسار إضافة مذكرة الخصم غير متاح. '         . self::DELEGATION_AR,
				'payment_receipt_unavailable'         => 'إيصال الدفع غير متاح. '                   . self::DELEGATION_AR,
				'payment_destroy_route_unavailable'   => 'مسار حذف الدفع غير متاح. '                . self::DELEGATION_AR,
			],
			'da' => [
				'bill_index_route_unavailable'        => 'Fakturoversigtsrute er ikke tilgængelig. '         . self::DELEGATION_DA,
				'bill_store_route_unavailable'        => 'Faktura lagringsrute er ikke tilgængelig. '         . self::DELEGATION_DA,
				'bill_vendor_fetch_route_unavailable' => 'Leverandørophentningsrute er ikke tilgængelig. '  . self::DELEGATION_DA,
				'product_fetch_route_unavailable'     => 'Produktophentningsrute er ikke tilgængelig. '      . self::DELEGATION_DA,
				'bill_pdf_route_unavailable'          => 'Faktura PDF-rute er ikke tilgængelig. '           . self::DELEGATION_DA,
				'bill_link_copy_route_unavailable'    => 'Faktura linkkopi-rute er ikke tilgængelig. '      . self::DELEGATION_DA,
				'bill_update_route_unavailable'       => 'Faktura opdateringsrute er ikke tilgængelig. '    . self::DELEGATION_DA,
				'bill_create_route_unavailable'       => 'Faktura oprettelsesrute er ikke tilgængelig. '    . self::DELEGATION_DA,
				'bill_export_route_unavailable'       => 'Faktura exporteringsrute er ikke tilgængelig. '   . self::DELEGATION_DA,
				'bill_show_route_unavailable'         => 'Faktura visningsrute er ikke tilgængelig. '       . self::DELEGATION_DA,
				'bill_duplicate_route_unavailable'    => 'Faktura duplikatrute er ikke tilgængelig. '       . self::DELEGATION_DA,
				'bill_edit_route_unavailable'         => 'Faktura redigeringsrute er ikke tilgængelig. '    . self::DELEGATION_DA,
				'bill_destroy_route_unavailable'      => 'Faktura sletningsrute er ikke tilgængelig. '      . self::DELEGATION_DA,
				'bill_payment_route_unavailable'      => 'Faktura betalingsrute er ikke tilgængelig. '      . self::DELEGATION_DA,
				'bill_sent_route_unavailable'         => 'Faktura sendingsrute er ikke tilgængelig. '       . self::DELEGATION_DA,
				'bill_resent_route_unavailable'       => 'Faktura gensendelsesrute er ikke tilgængelig. '   . self::DELEGATION_DA,
				'bill_debit_note_route_unavailable'   => 'Debetnota tilføjelsesrute er ikke tilgængelig. '  . self::DELEGATION_DA,
				'payment_receipt_unavailable'         => 'Betalingskvittering er ikke tilgængelig. '        . self::DELEGATION_DA,
				'payment_destroy_route_unavailable'   => 'Betalings sletningsrute er ikke tilgængelig. '   . self::DELEGATION_DA,
			],
			'de' => [
				'bill_index_route_unavailable'        => 'Rechnungsindexroute ist nicht verfügbar. '               . self::DELEGATION_DE,
				'bill_store_route_unavailable'        => 'Rechnungsspeicherroute ist nicht verfügbar. '           . self::DELEGATION_DE,
				'bill_vendor_fetch_route_unavailable' => 'Lieferantenabrufroute ist nicht verfügbar. '            . self::DELEGATION_DE,
				'product_fetch_route_unavailable'     => 'Produkt-Abrufroute ist nicht verfügbar. '               . self::DELEGATION_DE,
				'bill_pdf_route_unavailable'          => 'Rechnung-PDF-Route ist nicht verfügbar. '               . self::DELEGATION_DE,
				'bill_link_copy_route_unavailable'    => 'Rechnungs-Link-Kopierroute ist nicht verfügbar. '       . self::DELEGATION_DE,
				'bill_update_route_unavailable'       => 'Rechnungsaktualisierungsroute ist nicht verfügbar. '    . self::DELEGATION_DE,
				'bill_create_route_unavailable'       => 'Rechnungserstellungsroute ist nicht verfügbar. '        . self::DELEGATION_DE,
				'bill_export_route_unavailable'       => 'Rechnungsexportroute ist nicht verfügbar. '             . self::DELEGATION_DE,
				'bill_show_route_unavailable'         => 'Rechnungsansichtroute ist nicht verfügbar. '            . self::DELEGATION_DE,
				'bill_duplicate_route_unavailable'    => 'Rechnungsduplizierungsroute ist nicht verfügbar. '      . self::DELEGATION_DE,
				'bill_edit_route_unavailable'         => 'Rechnungsbearbeitungsroute ist nicht verfügbar. '       . self::DELEGATION_DE,
				'bill_destroy_route_unavailable'      => 'Rechnungslöschroute ist nicht verfügbar. '              . self::DELEGATION_DE,
				'bill_payment_route_unavailable'      => 'Rechnungszahlungsroute ist nicht verfügbar. '           . self::DELEGATION_DE,
				'bill_sent_route_unavailable'         => 'Rechnungssendungsroute ist nicht verfügbar. '           . self::DELEGATION_DE,
				'bill_resent_route_unavailable'       => 'Rechnungs-Erneut-Sendungsroute ist nicht verfügbar. '  . self::DELEGATION_DE,
				'bill_debit_note_route_unavailable'   => 'Debit-Notiz-Hinzufügeroute ist nicht verfügbar. '       . self::DELEGATION_DE,
				'payment_receipt_unavailable'         => 'Zahlungsbeleg ist nicht verfügbar. '                    . self::DELEGATION_DE,
				'payment_destroy_route_unavailable'   => 'Zahlungslöschroute ist nicht verfügbar. '               . self::DELEGATION_DE,
			],
			'en' => [
				'bill_index_route_unavailable'        => 'Bill index route is unavailable. '             . self::DELEGATION_EN,
				'bill_store_route_unavailable'        => 'Bill store route is unavailable. '             . self::DELEGATION_EN,
				'bill_vendor_fetch_route_unavailable' => 'Bill vendor fetch route is unavailable. '      . self::DELEGATION_EN,
				'product_fetch_route_unavailable'     => 'Product fetch route is unavailable. '          . self::DELEGATION_EN,
				'bill_pdf_route_unavailable'          => 'Bill PDF route is unavailable. '               . self::DELEGATION_EN,
				'bill_link_copy_route_unavailable'    => 'Bill link copy route is unavailable. '         . self::DELEGATION_EN,
				'bill_update_route_unavailable'       => 'Bill update route is unavailable. '            . self::DELEGATION_EN,
				'bill_create_route_unavailable'       => 'Bill create route is unavailable. '            . self::DELEGATION_EN,
				'bill_export_route_unavailable'       => 'Bill export route is unavailable. '            . self::DELEGATION_EN,
				'bill_show_route_unavailable'         => 'Bill view route is unavailable. '              . self::DELEGATION_EN,
				'bill_duplicate_route_unavailable'    => 'Bill duplicate route is unavailable. '         . self::DELEGATION_EN,
				'bill_edit_route_unavailable'         => 'Bill edit route is unavailable. '              . self::DELEGATION_EN,
				'bill_destroy_route_unavailable'      => 'Bill destroy route is unavailable. '           . self::DELEGATION_EN,
				'bill_payment_route_unavailable'      => 'Bill payment route is unavailable. '           . self::DELEGATION_EN,
				'bill_sent_route_unavailable'         => 'Bill send route is unavailable. '              . self::DELEGATION_EN,
				'bill_resent_route_unavailable'       => 'Bill resend route is unavailable. '            . self::DELEGATION_EN,
				'bill_debit_note_route_unavailable'   => 'Add debit note route is unavailable. '         . self::DELEGATION_EN,
				'payment_receipt_unavailable'         => 'Payment receipt is unavailable. '              . self::DELEGATION_EN,
				'payment_destroy_route_unavailable'   => 'Payment delete route is unavailable. '         . self::DELEGATION_EN,
			],
			'es' => [
				'bill_index_route_unavailable'        => 'La ruta de índice de facturas no está disponible. '               . self::DELEGATION_ES,
				'bill_store_route_unavailable'        => 'La ruta de almacenamiento de facturas no está disponible. '      . self::DELEGATION_ES,
				'bill_vendor_fetch_route_unavailable' => 'La ruta de obtención de datos del proveedor no está disponible. ' . self::DELEGATION_ES,
				'product_fetch_route_unavailable'     => 'La ruta de obtención de productos no está disponible. '         . self::DELEGATION_ES,
				'bill_pdf_route_unavailable'          => 'La ruta de PDF de facturas no está disponible. '                . self::DELEGATION_ES,
				'bill_link_copy_route_unavailable'    => 'La ruta de copia de enlace de facturas no está disponible. '    . self::DELEGATION_ES,
				'bill_update_route_unavailable'       => 'La ruta de actualización de facturas no está disponible. '      . self::DELEGATION_ES,
				'bill_create_route_unavailable'       => 'La ruta de creación de facturas no está disponible. '          . self::DELEGATION_ES,
				'bill_export_route_unavailable'       => 'La ruta de exportación de facturas no está disponible. '       . self::DELEGATION_ES,
				'bill_show_route_unavailable'         => 'La ruta de visualización de facturas no está disponible. '     . self::DELEGATION_ES,
				'bill_duplicate_route_unavailable'    => 'La ruta de duplicado de facturas no está disponible. '         . self::DELEGATION_ES,
				'bill_edit_route_unavailable'         => 'La ruta de edición de facturas no está disponible. '           . self::DELEGATION_ES,
				'bill_destroy_route_unavailable'      => 'La ruta de eliminación de facturas no está disponible. '       . self::DELEGATION_ES,
				'bill_payment_route_unavailable'      => 'La ruta de pago de facturas no está disponible. '              . self::DELEGATION_ES,
				'bill_sent_route_unavailable'         => 'La ruta de envío de facturas no está disponible. '             . self::DELEGATION_ES,
				'bill_resent_route_unavailable'       => 'La ruta de reenvío de facturas no está disponible. '           . self::DELEGATION_ES,
				'bill_debit_note_route_unavailable'   => 'La ruta de adición de nota de débito no está disponible. '     . self::DELEGATION_ES,
				'payment_receipt_unavailable'         => 'El recibo de pago no está disponible. '                        . self::DELEGATION_ES,
				'payment_destroy_route_unavailable'   => 'La ruta de eliminación de pagos no está disponible. '          . self::DELEGATION_ES,
			],
			'fr' => [
				'bill_index_route_unavailable'        => 'La route d’index des factures est indisponible. '            . self::DELEGATION_FR,
				'bill_store_route_unavailable'        => 'La route de stockage des factures est indisponible. '       . self::DELEGATION_FR,
				'bill_vendor_fetch_route_unavailable' => 'La route de récupération du fournisseur est indisponible. ' . self::DELEGATION_FR,
				'product_fetch_route_unavailable'     => 'La route de récupération des produits est indisponible. '   . self::DELEGATION_FR,
				'bill_pdf_route_unavailable'          => 'La route PDF des factures est indisponible. '               . self::DELEGATION_FR,
				'bill_link_copy_route_unavailable'    => 'La route de copie du lien des factures est indisponible. '  . self::DELEGATION_FR,
				'bill_update_route_unavailable'       => 'La route de mise à jour des factures est indisponible. '   . self::DELEGATION_FR,
				'bill_create_route_unavailable'       => 'La route de création des factures est indisponible. '      . self::DELEGATION_FR,
				'bill_export_route_unavailable'       => 'La route d’exportation des factures est indisponible. '   . self::DELEGATION_FR,
				'bill_show_route_unavailable'         => 'La route de visualisation des factures est indisponible. ' . self::DELEGATION_FR,
				'bill_duplicate_route_unavailable'    => 'La route de duplication des factures est indisponible. '  . self::DELEGATION_FR,
				'bill_edit_route_unavailable'         => 'La route d’édition des factures est indisponible. '       . self::DELEGATION_FR,
				'bill_destroy_route_unavailable'      => 'La route de suppression des factures est indisponible. '  . self::DELEGATION_FR,
				'bill_payment_route_unavailable'      => 'La route de paiement des factures est indisponible. '     . self::DELEGATION_FR,
				'bill_sent_route_unavailable'         => 'La route d’envoi des factures est indisponible. '         . self::DELEGATION_FR,
				'bill_resent_route_unavailable'       => 'La route de renvoi des factures est indisponible. '       . self::DELEGATION_FR,
				'bill_debit_note_route_unavailable'   => 'La route d’ajout de note de débit est indisponible. '     . self::DELEGATION_FR,
				'payment_receipt_unavailable'         => 'Le reçu de paiement n’est pas disponible. '               . self::DELEGATION_FR,
				'payment_destroy_route_unavailable'   => 'La route de suppression des paiements est indisponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'bill_index_route_unavailable'        => 'נתיב האינדקס של החשבוניות אינו זמין. '              . self::DELEGATION_HE,
				'bill_store_route_unavailable'        => 'נתיב שמירת החשבונית אינו זמין. '                  . self::DELEGATION_HE,
				'bill_vendor_fetch_route_unavailable' => 'נתיב שליפת הספק אינו זמין. '                    . self::DELEGATION_HE,
				'product_fetch_route_unavailable'     => 'נתיב שליפת המוצר אינו זמין. '                 . self::DELEGATION_HE,
				'bill_pdf_route_unavailable'          => 'נתיב PDF של החשבונית אינו זמין. '              . self::DELEGATION_HE,
				'bill_link_copy_route_unavailable'    => 'נתיב העתקת הקישור של החשבונית אינו זמין. '      . self::DELEGATION_HE,
				'bill_update_route_unavailable'       => 'נתיב עדכון החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_create_route_unavailable'       => 'נתיב יצירת החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_export_route_unavailable'       => 'נתיב ייצוא החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_show_route_unavailable'         => 'נתיב הצגת החשבונית אינו זמין. '                . self::DELEGATION_HE,
				'bill_duplicate_route_unavailable'    => 'נתיב שכפול החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_edit_route_unavailable'         => 'נתיב עריכת החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_destroy_route_unavailable'      => 'נתיב מחיקת החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_payment_route_unavailable'      => 'נתיב תשלום החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_sent_route_unavailable'         => 'נתיב שליחת החשבונית אינו זמין. '               . self::DELEGATION_HE,
				'bill_resent_route_unavailable'       => 'נתיב שליחת החשבונית מחדש אינו זמין. '         . self::DELEGATION_HE,
				'bill_debit_note_route_unavailable'   => 'נתיב הוספת הערת חיוב אינו זמין. '              . self::DELEGATION_HE,
				'payment_receipt_unavailable'         => 'קבלת התשלום אינה זמינה. '                    . self::DELEGATION_HE,
				'payment_destroy_route_unavailable'   => 'נתיב מחיקת התשלום אינו זמין. '                . self::DELEGATION_HE,
			],
			'it' => [
				'bill_index_route_unavailable'        => 'La rotta dell’indice delle fatture non è disponibile. '            . self::DELEGATION_IT,
				'bill_store_route_unavailable'        => 'La rotta di memorizzazione delle fatture non è disponibile. '      . self::DELEGATION_IT,
				'bill_vendor_fetch_route_unavailable' => 'La rotta di recupero fornitore non è disponibile. '               . self::DELEGATION_IT,
				'product_fetch_route_unavailable'     => 'La rotta di recupero prodotto non è disponibile. '                . self::DELEGATION_IT,
				'bill_pdf_route_unavailable'          => 'La rotta PDF delle fatture non è disponibile. '                   . self::DELEGATION_IT,
				'bill_link_copy_route_unavailable'    => 'La rotta di copia del collegamento delle fatture non è disponibile.' . self::DELEGATION_IT,
				'bill_update_route_unavailable'       => 'La rotta di aggiornamento delle fatture non è disponibile. '       . self::DELEGATION_IT,
				'bill_create_route_unavailable'       => 'La rotta di creazione delle fatture non è disponibile. '           . self::DELEGATION_IT,
				'bill_export_route_unavailable'       => 'La rotta di esportazione delle fatture non è disponibile. '        . self::DELEGATION_IT,
				'bill_show_route_unavailable'         => 'La rotta di visualizzazione delle fatture non è disponibile. '     . self::DELEGATION_IT,
				'bill_duplicate_route_unavailable'    => 'La rotta di duplicazione delle fatture non è disponibile. '       . self::DELEGATION_IT,
				'bill_edit_route_unavailable'         => 'La rotta di modifica delle fatture non è disponibile. '           . self::DELEGATION_IT,
				'bill_destroy_route_unavailable'      => 'La rotta di eliminazione delle fatture non è disponibile. '       . self::DELEGATION_IT,
				'bill_payment_route_unavailable'      => 'La rotta di pagamento delle fatture non è disponibile. '         . self::DELEGATION_IT,
				'bill_sent_route_unavailable'         => 'La rotta di invio delle fatture non è disponibile. '             . self::DELEGATION_IT,
				'bill_resent_route_unavailable'       => 'La rotta di rinvio delle fatture non è disponibile. '            . self::DELEGATION_IT,
				'bill_debit_note_route_unavailable'   => 'La rotta di aggiunta di nota di addebito non è disponibile. '     . self::DELEGATION_IT,
				'payment_receipt_unavailable'         => 'La ricevuta di pagamento non è disponibile. '                     . self::DELEGATION_IT,
				'payment_destroy_route_unavailable'   => 'La rotta di eliminazione dei pagamenti non è disponibile. '      . self::DELEGATION_IT,
			],
			'ja' => [
				'bill_index_route_unavailable'        => '請求書一覧ルートは利用できません。'                         . self::DELEGATION_JA,
				'bill_store_route_unavailable'        => '請求書保存ルートは利用できません。'                         . self::DELEGATION_JA,
				'bill_vendor_fetch_route_unavailable' => 'ベンダー取得ルートは利用できません。'                      . self::DELEGATION_JA,
				'product_fetch_route_unavailable'     => '商品取得ルートは利用できません。'                         . self::DELEGATION_JA,
				'bill_pdf_route_unavailable'          => '請求書PDFルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_link_copy_route_unavailable'    => '請求書リンクコピー用ルートは利用できません。'               . self::DELEGATION_JA,
				'bill_update_route_unavailable'       => '請求書更新ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_create_route_unavailable'       => '請求書作成ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_export_route_unavailable'       => '請求書エクスポートルートは利用できません。'                . self::DELEGATION_JA,
				'bill_show_route_unavailable'         => '請求書表示ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_duplicate_route_unavailable'    => '請求書複製ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_edit_route_unavailable'         => '請求書編集ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_destroy_route_unavailable'      => '請求書削除ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_payment_route_unavailable'      => '請求書支払いルートは利用できません。'                      . self::DELEGATION_JA,
				'bill_sent_route_unavailable'         => '請求書送信ルートは利用できません。'                        . self::DELEGATION_JA,
				'bill_resent_route_unavailable'       => '請求書再送信ルートは利用できません。'                      . self::DELEGATION_JA,
				'bill_debit_note_route_unavailable'   => 'デビットノート追加ルートは利用できません。'                  . self::DELEGATION_JA,
				'payment_receipt_unavailable'         => '支払い領収書は利用できません。'                            . self::DELEGATION_JA,
				'payment_destroy_route_unavailable'   => '支払い削除ルートは利用できません。'                         . self::DELEGATION_JA,
			],
			'nl' => [
				'bill_index_route_unavailable'        => 'Factuuroverzicht-route is niet beschikbaar. '         . self::DELEGATION_NL,
				'bill_store_route_unavailable'        => 'Factuuropslagroute is niet beschikbaar. '             . self::DELEGATION_NL,
				'bill_vendor_fetch_route_unavailable' => 'Leveranciersophaalroute is niet beschikbaar. '        . self::DELEGATION_NL,
				'product_fetch_route_unavailable'     => 'Productophaalroute is niet beschikbaar. '             . self::DELEGATION_NL,
				'bill_pdf_route_unavailable'          => 'Factuur-PDF-route is niet beschikbaar. '             . self::DELEGATION_NL,
				'bill_link_copy_route_unavailable'    => 'Factuurlinkkopieerroute is niet beschikbaar. '       . self::DELEGATION_NL,
				'bill_update_route_unavailable'       => 'Factuurbijwerkingsroute is niet beschikbaar. '       . self::DELEGATION_NL,
				'bill_create_route_unavailable'       => 'Factuuraanmaakroute is niet beschikbaar. '           . self::DELEGATION_NL,
				'bill_export_route_unavailable'       => 'Factuurexportroute is niet beschikbaar. '            . self::DELEGATION_NL,
				'bill_show_route_unavailable'         => 'Factuurweergaveroute is niet beschikbaar. '          . self::DELEGATION_NL,
				'bill_duplicate_route_unavailable'    => 'Factuurduplicaatroute is niet beschikbaar. '         . self::DELEGATION_NL,
				'bill_edit_route_unavailable'         => 'Factuurbewerkingsroute is niet beschikbaar. '        . self::DELEGATION_NL,
				'bill_destroy_route_unavailable'      => 'Factuurverwijderroute is niet beschikbaar. '         . self::DELEGATION_NL,
				'bill_payment_route_unavailable'      => 'Factuurbetaalroute is niet beschikbaar. '           . self::DELEGATION_NL,
				'bill_sent_route_unavailable'         => 'Factuurverzendroute is niet beschikbaar. '           . self::DELEGATION_NL,
				'bill_resent_route_unavailable'       => 'Factuurherverzendroute is niet beschikbaar. '       . self::DELEGATION_NL,
				'bill_debit_note_route_unavailable'   => 'Debetnota-aanmaakroute is niet beschikbaar. '       . self::DELEGATION_NL,
				'payment_receipt_unavailable'         => 'Betalingsbewijs is niet beschikbaar. '               . self::DELEGATION_NL,
				'payment_destroy_route_unavailable'   => 'Betalingsverwijderroute is niet beschikbaar. '      . self::DELEGATION_NL,
			],
			'pl' => [
				'bill_index_route_unavailable'        => 'Trasa indeksu faktur jest niedostępna. '                   . self::DELEGATION_PL,
				'bill_store_route_unavailable'        => 'Trasa zapisu faktury jest niedostępna. '                  . self::DELEGATION_PL,
				'bill_vendor_fetch_route_unavailable' => 'Trasa pobierania dostawcy jest niedostępna. '            . self::DELEGATION_PL,
				'product_fetch_route_unavailable'     => 'Trasa pobierania produktu jest niedostępna. '            . self::DELEGATION_PL,
				'bill_pdf_route_unavailable'          => 'Trasa PDF faktury jest niedostępna. '                    . self::DELEGATION_PL,
				'bill_link_copy_route_unavailable'    => 'Trasa kopiowania linku faktury jest niedostępna. '       . self::DELEGATION_PL,
				'bill_update_route_unavailable'       => 'Trasa aktualizacji faktury jest niedostępna. '           . self::DELEGATION_PL,
				'bill_create_route_unavailable'       => 'Trasa tworzenia faktury jest niedostępna. '              . self::DELEGATION_PL,
				'bill_export_route_unavailable'       => 'Trasa eksportu faktury jest niedostępna. '               . self::DELEGATION_PL,
				'bill_show_route_unavailable'         => 'Trasa wyświetlania faktury jest niedostępna. '           . self::DELEGATION_PL,
				'bill_duplicate_route_unavailable'    => 'Trasa duplikowania faktury jest niedostępna. '           . self::DELEGATION_PL,
				'bill_edit_route_unavailable'         => 'Trasa edycji faktury jest niedostępna. '                 . self::DELEGATION_PL,
				'bill_destroy_route_unavailable'      => 'Trasa usuwania faktury jest niedostępna. '               . self::DELEGATION_PL,
				'bill_payment_route_unavailable'      => 'Trasa płatności faktury jest niedostępna. '              . self::DELEGATION_PL,
				'bill_sent_route_unavailable'         => 'Trasa wysyłki faktury jest niedostępna. '                . self::DELEGATION_PL,
				'bill_resent_route_unavailable'       => 'Trasa ponownej wysyłki faktury jest niedostępna. '       . self::DELEGATION_PL,
				'bill_debit_note_route_unavailable'   => 'Trasa dodawania noty obciążeniowej jest niedostępna. '   . self::DELEGATION_PL,
				'payment_receipt_unavailable'         => 'Potwierdzenie płatności jest niedostępne. '             . self::DELEGATION_PL,
				'payment_destroy_route_unavailable'   => 'Trasa usuwania płatności jest niedostępna. '            . self::DELEGATION_PL,
			],
			'pt' => [
				'bill_index_route_unavailable'        => 'A rota de índice de faturas não está disponível. '            . self::DELEGATION_PT,
				'bill_store_route_unavailable'        => 'A rota de armazenamento de faturas não está disponível. '    . self::DELEGATION_PT,
				'bill_vendor_fetch_route_unavailable' => 'A rota de obtenção do fornecedor não está disponível. '      . self::DELEGATION_PT,
				'product_fetch_route_unavailable'     => 'A rota de obtenção de produtos não está disponível. '       . self::DELEGATION_PT,
				'bill_pdf_route_unavailable'          => 'A rota de PDF de faturas não está disponível. '            . self::DELEGATION_PT,
				'bill_link_copy_route_unavailable'    => 'A rota de cópia de link de faturas não está disponível. '  . self::DELEGATION_PT,
				'bill_update_route_unavailable'       => 'A rota de atualização de faturas não está disponível. '    . self::DELEGATION_PT,
				'bill_create_route_unavailable'       => 'A rota de criação de faturas não está disponível. '        . self::DELEGATION_PT,
				'bill_export_route_unavailable'       => 'A rota de exportação de faturas não está disponível. '     . self::DELEGATION_PT,
				'bill_show_route_unavailable'         => 'A rota de visualização de faturas não está disponível. '   . self::DELEGATION_PT,
				'bill_duplicate_route_unavailable'    => 'A rota de duplicação de faturas não está disponível. '     . self::DELEGATION_PT,
				'bill_edit_route_unavailable'         => 'A rota de edição de faturas não está disponível. '        . self::DELEGATION_PT,
				'bill_destroy_route_unavailable'      => 'A rota de eliminação de faturas não está disponível. '     . self::DELEGATION_PT,
				'bill_payment_route_unavailable'      => 'A rota de pagamento de faturas não está disponível. '     . self::DELEGATION_PT,
				'bill_sent_route_unavailable'         => 'A rota de envio de faturas não está disponível. '         . self::DELEGATION_PT,
				'bill_resent_route_unavailable'       => 'A rota de reenvio de faturas não está disponível. '       . self::DELEGATION_PT,
				'bill_debit_note_route_unavailable'   => 'A rota de adição de nota de débito não está disponível. ' . self::DELEGATION_PT,
				'payment_receipt_unavailable'         => 'O recibo de pagamento não está disponível. '              . self::DELEGATION_PT,
				'payment_destroy_route_unavailable'   => 'A rota de eliminação de pagamentos não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'bill_index_route_unavailable'        => 'A rota de listagem de faturas não está disponível. '        . self::DELEGATION_PTBR,
				'bill_store_route_unavailable'        => 'A rota de armazenamento de faturas não está disponível. '   . self::DELEGATION_PTBR,
				'bill_vendor_fetch_route_unavailable' => 'A rota de busca de fornecedor não está disponível. '        . self::DELEGATION_PTBR,
				'product_fetch_route_unavailable'     => 'A rota de busca de produtos não está disponível. '         . self::DELEGATION_PTBR,
				'bill_pdf_route_unavailable'          => 'A rota de PDF de faturas não está disponível. '          . self::DELEGATION_PTBR,
				'bill_link_copy_route_unavailable'    => 'A rota de cópia de link de faturas não está disponível. ' . self::DELEGATION_PTBR,
				'bill_update_route_unavailable'       => 'A rota de atualização de faturas não está disponível. '   . self::DELEGATION_PTBR,
				'bill_create_route_unavailable'       => 'A rota de criação de faturas não está disponível. '      . self::DELEGATION_PTBR,
				'bill_export_route_unavailable'       => 'A rota de exportação de faturas não está disponível. '   . self::DELEGATION_PTBR,
				'bill_show_route_unavailable'         => 'A rota de visualização de faturas não está disponível. ' . self::DELEGATION_PTBR,
				'bill_duplicate_route_unavailable'    => 'A rota de duplicação de faturas não está disponível. '   . self::DELEGATION_PTBR,
				'bill_edit_route_unavailable'         => 'A rota de edição de faturas não está disponível. '      . self::DELEGATION_PTBR,
				'bill_destroy_route_unavailable'      => 'A rota de exclusão de faturas não está disponível. '    . self::DELEGATION_PTBR,
				'bill_payment_route_unavailable'      => 'A rota de pagamento de faturas não está disponível. '  . self::DELEGATION_PTBR,
				'bill_sent_route_unavailable'         => 'A rota de envio de faturas não está disponível. '      . self::DELEGATION_PTBR,
				'bill_resent_route_unavailable'       => 'A rota de reenvio de faturas não está disponível. '    . self::DELEGATION_PTBR,
				'bill_debit_note_route_unavailable'   => 'A rota de adição de nota de débito não está disponível.' . self::DELEGATION_PTBR,
				'payment_receipt_unavailable'         => 'O comprovante de pagamento não está disponível. '      . self::DELEGATION_PTBR,
				'payment_destroy_route_unavailable'   => 'A rota de exclusão de pagamentos não está disponível.' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'bill_index_route_unavailable'        => 'Маршрут индекса счетов недоступен. '                 . self::DELEGATION_RU,
				'bill_store_route_unavailable'        => 'Маршрут сохранения счета недоступен. '              . self::DELEGATION_RU,
				'bill_vendor_fetch_route_unavailable' => 'Маршрут получения данных поставщика недоступен. '    . self::DELEGATION_RU,
				'product_fetch_route_unavailable'     => 'Маршрут получения продукта недоступен. '             . self::DELEGATION_RU,
				'bill_pdf_route_unavailable'          => 'Маршрут PDF счета недоступен. '                     . self::DELEGATION_RU,
				'bill_link_copy_route_unavailable'    => 'Маршрут копирования ссылки счета недоступен. '      . self::DELEGATION_RU,
				'bill_update_route_unavailable'       => 'Маршрут обновления счета недоступен. '              . self::DELEGATION_RU,
				'bill_create_route_unavailable'       => 'Маршрут создания счета недоступен. '                . self::DELEGATION_RU,
				'bill_export_route_unavailable'       => 'Маршрут экспорта счета недоступен. '                . self::DELEGATION_RU,
				'bill_show_route_unavailable'         => 'Маршрут просмотра счета недоступен. '               . self::DELEGATION_RU,
				'bill_duplicate_route_unavailable'    => 'Маршрут дублирования счета недоступен. '            . self::DELEGATION_RU,
				'bill_edit_route_unavailable'         => 'Маршрут редактирования счета недоступен. '          . self::DELEGATION_RU,
				'bill_destroy_route_unavailable'      => 'Маршрут удаления счета недоступен. '                . self::DELEGATION_RU,
				'bill_payment_route_unavailable'      => 'Маршрут оплаты счета недоступен. '                  . self::DELEGATION_RU,
				'bill_sent_route_unavailable'         => 'Маршрут отправки счета недоступен. '                . self::DELEGATION_RU,
				'bill_resent_route_unavailable'       => 'Маршрут повторной отправки счета недоступен. '      . self::DELEGATION_RU,
				'bill_debit_note_route_unavailable'   => 'Маршрут добавления дебетового уведомления недоступен.' . self::DELEGATION_RU,
				'payment_receipt_unavailable'         => 'Квитанция об оплате недоступна. '                   . self::DELEGATION_RU,
				'payment_destroy_route_unavailable'   => 'Маршрут удаления оплаты недоступен. '               . self::DELEGATION_RU,
			],
			'tr' => [
				'bill_index_route_unavailable'        => 'Fatura indeks rotası kullanılamıyor. '         . self::DELEGATION_TR,
				'bill_store_route_unavailable'        => 'Fatura kaydetme rotası kullanılamıyor. '        . self::DELEGATION_TR,
				'bill_vendor_fetch_route_unavailable' => 'Tedarikçi alma rotası kullanılamıyor. '         . self::DELEGATION_TR,
				'product_fetch_route_unavailable'     => 'Ürün alma rotası kullanılamıyor. '              . self::DELEGATION_TR,
				'bill_pdf_route_unavailable'          => 'Fatura PDF rotası kullanılamıyor. '             . self::DELEGATION_TR,
				'bill_link_copy_route_unavailable'    => 'Fatura bağlantı kopyalama rotası kullanılamıyor.' . self::DELEGATION_TR,
				'bill_update_route_unavailable'       => 'Fatura güncelleme rotası kullanılamıyor. '      . self::DELEGATION_TR,
				'bill_create_route_unavailable'       => 'Fatura oluşturma rotası kullanılamıyor. '       . self::DELEGATION_TR,
				'bill_export_route_unavailable'       => 'Fatura dışa aktarma rotası kullanılamıyor. '     . self::DELEGATION_TR,
				'bill_show_route_unavailable'         => 'Fatura görüntüleme rotası kullanılamıyor. '     . self::DELEGATION_TR,
				'bill_duplicate_route_unavailable'    => 'Fatura çoğaltma rotası kullanılamıyor. '        . self::DELEGATION_TR,
				'bill_edit_route_unavailable'         => 'Fatura düzenleme rotası kullanılamıyor. '       . self::DELEGATION_TR,
				'bill_destroy_route_unavailable'      => 'Fatura silme rotası kullanılamıyor. '          . self::DELEGATION_TR,
				'bill_payment_route_unavailable'      => 'Fatura ödeme rotası kullanılamıyor. '          . self::DELEGATION_TR,
				'bill_sent_route_unavailable'         => 'Fatura gönderme rotası kullanılamıyor. '       . self::DELEGATION_TR,
				'bill_resent_route_unavailable'       => 'Fatura yeniden gönderme rotası kullanılamıyor.' . self::DELEGATION_TR,
				'bill_debit_note_route_unavailable'   => 'Borç notu ekleme rotası kullanılamıyor. '      . self::DELEGATION_TR,
				'payment_receipt_unavailable'         => 'Ödeme makbuzu kullanılamıyor. '               . self::DELEGATION_TR,
				'payment_destroy_route_unavailable'   => 'Ödeme silme rotası kullanılamıyor. '          . self::DELEGATION_TR,
			],
			'zh' => [
				'bill_index_route_unavailable'        => '账单索引路由不可用。'                   . self::DELEGATION_ZH,
				'bill_store_route_unavailable'        => '账单存储路由不可用。'                   . self::DELEGATION_ZH,
				'bill_vendor_fetch_route_unavailable' => '供应商获取路由不可用。'               . self::DELEGATION_ZH,
				'product_fetch_route_unavailable'     => '产品获取路由不可用。'                 . self::DELEGATION_ZH,
				'bill_pdf_route_unavailable'          => '账单 PDF 路由不可用。'                 . self::DELEGATION_ZH,
				'bill_link_copy_route_unavailable'    => '账单链接复制路由不可用。'             . self::DELEGATION_ZH,
				'bill_update_route_unavailable'       => '账单更新路由不可用。'                 . self::DELEGATION_ZH,
				'bill_create_route_unavailable'       => '账单创建路由不可用。'                 . self::DELEGATION_ZH,
				'bill_export_route_unavailable'       => '账单导出路由不可用。'                 . self::DELEGATION_ZH,
				'bill_show_route_unavailable'         => '账单查看路由不可用。'                 . self::DELEGATION_ZH,
				'bill_duplicate_route_unavailable'    => '账单复制路由不可用。'                 . self::DELEGATION_ZH,
				'bill_edit_route_unavailable'         => '账单编辑路由不可用。'                 . self::DELEGATION_ZH,
				'bill_destroy_route_unavailable'      => '账单删除路由不可用。'                 . self::DELEGATION_ZH,
				'bill_payment_route_unavailable'      => '账单支付路由不可用。'                 . self::DELEGATION_ZH,
				'bill_sent_route_unavailable'         => '账单发送路由不可用。'                 . self::DELEGATION_ZH,
				'bill_resent_route_unavailable'       => '账单重新发送路由不可用。'             . self::DELEGATION_ZH,
				'bill_debit_note_route_unavailable'   => '添加借项通知路由不可用。'             . self::DELEGATION_ZH,
				'payment_receipt_unavailable'         => '付款收据不可用。'                     . self::DELEGATION_ZH,
				'payment_destroy_route_unavailable'   => '付款删除路由不可用。'                 . self::DELEGATION_ZH,
			],
		],
		ViewsConstants::BNK_ACC => [
			'ar' => [
				'bank_account_index_route_unavailable' => 'مسار الحساب البنكي غير متاح. ' . self::DELEGATION_AR,
				'bank_account_create_route_unavailable' => 'مسار إنشاء حساب بنكي جديد غير متاح. ' . self::DELEGATION_AR,
				'bank_account_store_route_unavailable' => 'مسار تخزين الحساب البنكي غير متاح. ' . self::DELEGATION_AR,
				'bank_account_update_route_unavailable' => 'مسار تحديث الحساب البنكي غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'bank_account_index_route_unavailable' => 'Bankkontorute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bank_account_create_route_unavailable' => 'Opret ny bankkontorute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bank_account_store_route_unavailable' => 'Bankkontolagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bank_account_update_route_unavailable' => 'Bankkontoopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'bank_account_index_route_unavailable' => 'Bankkonto-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bank_account_create_route_unavailable' => 'Route für neue Bankkontoerstellung ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bank_account_store_route_unavailable' => 'Bankkonto-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bank_account_update_route_unavailable' => 'Bankkonto-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'bank_account_index_route_unavailable' => 'Bank Account route is unavailable. ' . self::DELEGATION_EN,
				'bank_account_create_route_unavailable' => 'Create New Bank Account route is unavailable. ' . self::DELEGATION_EN,
				'bank_account_store_route_unavailable' => 'Bank Account store route is unavailable. ' . self::DELEGATION_EN,
				'bank_account_update_route_unavailable' => 'Bank Account update route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'bank_account_index_route_unavailable' => 'La ruta de cuenta bancaria no está disponible. ' . self::DELEGATION_ES,
				'bank_account_create_route_unavailable' => 'La ruta de creación de nueva cuenta bancaria no está disponible. ' . self::DELEGATION_ES,
				'bank_account_store_route_unavailable' => 'La ruta de almacenamiento de cuenta bancaria no está disponible. ' . self::DELEGATION_ES,
				'bank_account_update_route_unavailable' => 'La ruta de actualización de cuenta bancaria no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'bank_account_index_route_unavailable' => 'La route du compte bancaire est indisponible. ' . self::DELEGATION_FR,
				'bank_account_create_route_unavailable' => 'La route de création de nouveau compte bancaire est indisponible. ' . self::DELEGATION_FR,
				'bank_account_store_route_unavailable' => 'La route de stockage du compte bancaire est indisponible. ' . self::DELEGATION_FR,
				'bank_account_update_route_unavailable' => 'La route de mise à jour du compte bancaire est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'bank_account_index_route_unavailable' => 'נתיב חשבון בנק אינו זמין. ' . self::DELEGATION_HE,
				'bank_account_create_route_unavailable' => 'נתיב יצירת חשבון בנק חדש אינו זמין. ' . self::DELEGATION_HE,
				'bank_account_store_route_unavailable' => 'נתיב אחסון חשבון בנק אינו זמין. ' . self::DELEGATION_HE,
				'bank_account_update_route_unavailable' => 'נתיב עדכון חשבון בנק אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'bank_account_index_route_unavailable' => 'La rotta del conto bancario non è disponibile. ' . self::DELEGATION_IT,
				'bank_account_create_route_unavailable' => 'La rotta di creazione nuovo conto bancario non è disponibile. ' . self::DELEGATION_IT,
				'bank_account_store_route_unavailable' => 'La rotta di memorizzazione conto bancario non è disponibile. ' . self::DELEGATION_IT,
				'bank_account_update_route_unavailable' => 'La rotta di aggiornamento conto bancario non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'bank_account_index_route_unavailable' => '銀行口座ルートは利用できません。' . self::DELEGATION_JA,
				'bank_account_create_route_unavailable' => '新規銀行口座作成ルートは利用できません。' . self::DELEGATION_JA,
				'bank_account_store_route_unavailable' => '銀行口座保存ルートは利用できません。' . self::DELEGATION_JA,
				'bank_account_update_route_unavailable' => '銀行口座更新ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'bank_account_index_route_unavailable' => 'Bankrekeningroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bank_account_create_route_unavailable' => 'Route voor nieuwe bankrekening is niet beschikbaar. ' . self::DELEGATION_NL,
				'bank_account_store_route_unavailable' => 'Bankrekeningopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bank_account_update_route_unavailable' => 'Bankrekeningupdateroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'bank_account_index_route_unavailable' => 'Trasa konta bankowego jest niedostępna. ' . self::DELEGATION_PL,
				'bank_account_create_route_unavailable' => 'Trasa tworzenia nowego konta bankowego jest niedostępna. ' . self::DELEGATION_PL,
				'bank_account_store_route_unavailable' => 'Trasa przechowywania konta bankowego jest niedostępna. ' . self::DELEGATION_PL,
				'bank_account_update_route_unavailable' => 'Trasa aktualizacji konta bankowego jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'bank_account_index_route_unavailable' => 'A rota da conta bancária não está disponível. ' . self::DELEGATION_PT,
				'bank_account_create_route_unavailable' => 'A rota de criação de nova conta bancária não está disponível. ' . self::DELEGATION_PT,
				'bank_account_store_route_unavailable' => 'A rota de armazenamento de conta bancária não está disponível. ' . self::DELEGATION_PT,
				'bank_account_update_route_unavailable' => 'A rota de atualização de conta bancária não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'bank_account_index_route_unavailable' => 'A rota da conta bancária não está disponível. ' . self::DELEGATION_PTBR,
				'bank_account_create_route_unavailable' => 'A rota de criação de nova conta bancária não está disponível. ' . self::DELEGATION_PTBR,
				'bank_account_store_route_unavailable' => 'A rota de armazenamento de conta bancária não está disponível. ' . self::DELEGATION_PTBR,
				'bank_account_update_route_unavailable' => 'A rota de atualização de conta bancária não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'bank_account_index_route_unavailable' => 'Маршрут банковского счета недоступен. ' . self::DELEGATION_RU,
				'bank_account_create_route_unavailable' => 'Маршрут создания нового банковского счета недоступен. ' . self::DELEGATION_RU,
				'bank_account_store_route_unavailable' => 'Маршрут хранения банковского счета недоступен. ' . self::DELEGATION_RU,
				'bank_account_update_route_unavailable' => 'Маршрут обновления банковского счета недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'bank_account_index_route_unavailable' => 'Banka hesabı rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bank_account_create_route_unavailable' => 'Yeni banka hesabı oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bank_account_store_route_unavailable' => 'Banka hesabı depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bank_account_update_route_unavailable' => 'Banka hesabı güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'bank_account_index_route_unavailable' => '银行账户路由不可用。' . self::DELEGATION_ZH,
				'bank_account_create_route_unavailable' => '创建新银行账户路由不可用。' . self::DELEGATION_ZH,
				'bank_account_store_route_unavailable' => '银行账户存储路由不可用。' . self::DELEGATION_ZH,
				'bank_account_update_route_unavailable' => '银行账户更新路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::BNK_TRF => [
			'ar' => [
				'bank_transfer_index_route_unavailable' => 'مسار التحويل البنكي غير متاح. ' . self::DELEGATION_AR,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_AR,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_AR,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_AR,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_AR,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'bank_transfer_index_route_unavailable' => 'Bankoverførselsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_DA,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_DA,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_DA,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_DA,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'bank_transfer_index_route_unavailable' => 'Banküberweisungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_DE,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_DE,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_DE,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_DE,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'bank_transfer_index_route_unavailable' => 'Transfer route is unavailable. ' . self::DELEGATION_EN,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_EN,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_EN,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_EN,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_EN,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'bank_transfer_index_route_unavailable' => 'La ruta de transferencia bancaria no está disponible. ' . self::DELEGATION_ES,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_ES,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_ES,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_ES,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_ES,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'bank_transfer_index_route_unavailable' => 'La route de virement bancaire est indisponible. ' . self::DELEGATION_FR,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_FR,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_FR,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_FR,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_FR,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'bank_transfer_index_route_unavailable' => 'נתיב העברה בנקאית אינו זמין. ' . self::DELEGATION_HE,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_HE,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_HE,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_HE,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_HE,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'bank_transfer_index_route_unavailable' => 'La rotta del bonifico bancario non è disponibile. ' . self::DELEGATION_IT,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_IT,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_IT,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_IT,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_IT,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'bank_transfer_index_route_unavailable' => '銀行振込ルートは利用できません。' . self::DELEGATION_JA,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_JA,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_JA,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_JA,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_JA,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'bank_transfer_index_route_unavailable' => 'Bankoverschrijvingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_NL,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_NL,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_NL,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_NL,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'bank_transfer_index_route_unavailable' => 'Trasa przelewu bankowego jest niedostępna. ' . self::DELEGATION_PL,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_PL,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_PL,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_PL,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_PL,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'bank_transfer_index_route_unavailable' => 'A rota de transferência bancária não está disponível. ' . self::DELEGATION_PT,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_PT,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_PT,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_PT,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_PT,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'bank_transfer_index_route_unavailable' => 'A rota de transferência bancária não está disponível. ' . self::DELEGATION_PTBR,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_PTBR,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_PTBR,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_PTBR,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_PTBR,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'bank_transfer_index_route_unavailable' => 'Маршрут банковского перевода недоступен. ' . self::DELEGATION_RU,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_RU,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_RU,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_RU,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_RU,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'bank_transfer_index_route_unavailable' => 'Banka transfer rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_TR,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_TR,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_TR,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_TR,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'bank_transfer_index_route_unavailable' => '银行转账路由不可用。' . self::DELEGATION_ZH,
				'fallback_route_unavailable' => 'Fallback route is unavailable. ' . self::DELEGATION_ZH,
				'bank_transfer_update_route_unavailable' => 'Bank transfer update route is unavailable. ' . self::DELEGATION_ZH,
				'create_bank_transfer_unavailable' => 'Create bank transfer route is unavailable. ' . self::DELEGATION_ZH,
				'transfer_edit_route_unavailable' => 'Transfer edit route is unavailable. ' . self::DELEGATION_ZH,
				'transfer_destroy_route_unavailable' => 'Transfer destroy route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::BRC => [
			'ar' => [
				'hrm_system_setup_route_unavailable' => 'مسار إعداد نظام إدارة الموارد البشرية غير متاح. ' . self::DELEGATION_AR,
				'branch_store_route_unavailable' => 'مسار تخزين الفرع غير متاح. ' . self::DELEGATION_AR,
				'branch_update_route_unavailable' => 'مسار تحديث الفرع غير متاح. ' . self::DELEGATION_AR,
				'branch_destroy_route_unavailable' => 'مسار حذف الفرع غير متاح. ' . self::DELEGATION_AR,
				'branch_edit_route_unavailable' => 'مسار تعديل الفرع غير متاح. ' . self::DELEGATION_AR,
				'branch_create_route_unavailable' => 'مسار إنشاء الفرع غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'hrm_system_setup_route_unavailable' => 'Human Resources Management System Opsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'branch_store_route_unavailable' => 'Filial lagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'branch_update_route_unavailable' => 'Filial opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'branch_destroy_route_unavailable' => 'Filial sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'branch_edit_route_unavailable' => 'Filial redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'branch_create_route_unavailable' => 'Filial oprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'hrm_system_setup_route_unavailable' => 'Human Resources Management System Einrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'branch_store_route_unavailable' => 'Zweigstelle-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'branch_update_route_unavailable' => 'Zweigstelle-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'branch_destroy_route_unavailable' => 'Zweigstelle-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'branch_edit_route_unavailable' => 'Zweigstelle-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'branch_create_route_unavailable' => 'Zweigstelle-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'hrm_system_setup_route_unavailable' => 'Human Resources Management System Setup route is unavailable. ' . self::DELEGATION_EN,
				'branch_store_route_unavailable' => 'Branch store route is unavailable. ' . self::DELEGATION_EN,
				'branch_update_route_unavailable' => 'Branch update route is unavailable. ' . self::DELEGATION_EN,
				'branch_destroy_route_unavailable' => 'Branch destroy route is unavailable. ' . self::DELEGATION_EN,
				'branch_edit_route_unavailable' => 'Branch edit route is unavailable. ' . self::DELEGATION_EN,
				'branch_create_route_unavailable' => 'Branch create route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'hrm_system_setup_route_unavailable' => 'La ruta de configuración del Sistema de Gestión de Recursos Humanos no está disponible. ' . self::DELEGATION_ES,
				'branch_store_route_unavailable' => 'La ruta de almacenamiento de sucursal no está disponible. ' . self::DELEGATION_ES,
				'branch_update_route_unavailable' => 'La ruta de actualización de sucursal no está disponible. ' . self::DELEGATION_ES,
				'branch_destroy_route_unavailable' => 'La ruta de eliminación de sucursal no está disponible. ' . self::DELEGATION_ES,
				'branch_edit_route_unavailable' => 'La ruta de edición de sucursal no está disponible. ' . self::DELEGATION_ES,
				'branch_create_route_unavailable' => 'La ruta de creación de sucursal no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'hrm_system_setup_route_unavailable' => 'La route de configuration du système de gestion des ressources humaines est indisponible. ' . self::DELEGATION_FR,
				'branch_store_route_unavailable' => 'La route de stockage des succursales est indisponible. ' . self::DELEGATION_FR,
				'branch_update_route_unavailable' => 'La route de mise à jour des succursales est indisponible. ' . self::DELEGATION_FR,
				'branch_destroy_route_unavailable' => 'La route de suppression des succursales est indisponible. ' . self::DELEGATION_FR,
				'branch_edit_route_unavailable' => 'La route d\'édition des succursales est indisponible. ' . self::DELEGATION_FR,
				'branch_create_route_unavailable' => 'La route de création des succursales est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'hrm_system_setup_route_unavailable' => 'נתיב הגדרת מערכת ניהול משאבי אנוש אינו זמין. ' . self::DELEGATION_HE,
				'branch_store_route_unavailable' => 'נתיב אחסון הסניף אינו זמין. ' . self::DELEGATION_HE,
				'branch_update_route_unavailable' => 'נתיב עדכון הסניף אינו זמין. ' . self::DELEGATION_HE,
				'branch_destroy_route_unavailable' => 'נתיב מחיקת הסניף אינו זמין. ' . self::DELEGATION_HE,
				'branch_edit_route_unavailable' => 'נתיב עריכת הסניף אינו זמין. ' . self::DELEGATION_HE,
				'branch_create_route_unavailable' => 'נתיב יצירת הסניף אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'hrm_system_setup_route_unavailable' => 'La rotta di configurazione del Sistema di Gestione delle Risorse Umane non è disponibile. ' . self::DELEGATION_IT,
				'branch_store_route_unavailable' => 'La rotta di memorizzazione della filiale non è disponibile. ' . self::DELEGATION_IT,
				'branch_update_route_unavailable' => 'La rotta di aggiornamento della filiale non è disponibile. ' . self::DELEGATION_IT,
				'branch_destroy_route_unavailable' => 'La rotta di eliminazione della filiale non è disponibile. ' . self::DELEGATION_IT,
				'branch_edit_route_unavailable' => 'La rotta di modifica della filiale non è disponibile. ' . self::DELEGATION_IT,
				'branch_create_route_unavailable' => 'La rotta di creazione della filiale non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'hrm_system_setup_route_unavailable' => '人事管理システム設定ルートは利用できません。' . self::DELEGATION_JA,
				'branch_store_route_unavailable' => '支店保存ルートは利用できません。' . self::DELEGATION_JA,
				'branch_update_route_unavailable' => '支店更新ルートは利用できません。' . self::DELEGATION_JA,
				'branch_destroy_route_unavailable' => '支店削除ルートは利用できません。' . self::DELEGATION_JA,
				'branch_edit_route_unavailable' => '支店編集ルートは利用できません。' . self::DELEGATION_JA,
				'branch_create_route_unavailable' => '支店作成ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'hrm_system_setup_route_unavailable' => 'Human Resources Management Systeem Instellingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'branch_store_route_unavailable' => 'Filiaal opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'branch_update_route_unavailable' => 'Filiaal updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'branch_destroy_route_unavailable' => 'Filiaal verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'branch_edit_route_unavailable' => 'Filiaal bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'branch_create_route_unavailable' => 'Filiaal aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'hrm_system_setup_route_unavailable' => 'Trasa konfiguracji systemu zarządzania zasobami ludzkimi jest niedostępna. ' . self::DELEGATION_PL,
				'branch_store_route_unavailable' => 'Trasa sklepowa oddziału jest niedostępna. ' . self::DELEGATION_PL,
				'branch_update_route_unavailable' => 'Trasa aktualizacji oddziału jest niedostępna. ' . self::DELEGATION_PL,
				'branch_destroy_route_unavailable' => 'Trasa usuwania oddziału jest niedostępna. ' . self::DELEGATION_PL,
				'branch_edit_route_unavailable' => 'Trasa edycji oddziału jest niedostępna. ' . self::DELEGATION_PL,
				'branch_create_route_unavailable' => 'Trasa tworzenia oddziału jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'hrm_system_setup_route_unavailable' => 'A rota de configuração do Sistema de Gestão de Recursos Humanos não está disponível. ' . self::DELEGATION_PT,
				'branch_store_route_unavailable' => 'A rota de armazenamento da filial não está disponível. ' . self::DELEGATION_PT,
				'branch_update_route_unavailable' => 'A rota de atualização da filial não está disponível. ' . self::DELEGATION_PT,
				'branch_destroy_route_unavailable' => 'A rota de destruição da filial não está disponível. ' . self::DELEGATION_PT,
				'branch_edit_route_unavailable' => 'A rota de edição da filial não está disponível. ' . self::DELEGATION_PT,
				'branch_create_route_unavailable' => 'A rota de criação da filial não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'hrm_system_setup_route_unavailable' => 'A rota de configuração do Sistema de Gerenciamento de Recursos Humanos não está disponível. ' . self::DELEGATION_PTBR,
				'branch_store_route_unavailable' => 'A rota de armazenamento da filial não está disponível. ' . self::DELEGATION_PTBR,
				'branch_update_route_unavailable' => 'A rota de atualização da filial não está disponível. ' . self::DELEGATION_PTBR,
				'branch_destroy_route_unavailable' => 'A rota de destruição da filial não está disponível. ' . self::DELEGATION_PTBR,
				'branch_edit_route_unavailable' => 'A rota de edição da filial não está disponível. ' . self::DELEGATION_PTBR,
				'branch_create_route_unavailable' => 'A rota de criação da filial não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'hrm_system_setup_route_unavailable' => 'Маршрут настройки системы управления персоналом недоступен. ' . self::DELEGATION_RU,
				'branch_store_route_unavailable' => 'Маршрут хранения филиала недоступен. ' . self::DELEGATION_RU,
				'branch_update_route_unavailable' => 'Маршрут обновления филиала недоступен. ' . self::DELEGATION_RU,
				'branch_destroy_route_unavailable' => 'Маршрут удаления филиала недоступен. ' . self::DELEGATION_RU,
				'branch_edit_route_unavailable' => 'Маршрут редактирования филиала недоступен. ' . self::DELEGATION_RU,
				'branch_create_route_unavailable' => 'Маршрут создания филиала недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'hrm_system_setup_route_unavailable' => 'İnsan Kaynakları Yönetim Sistemi Kurulum rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'branch_store_route_unavailable' => 'Şube depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'branch_update_route_unavailable' => 'Şube güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'branch_destroy_route_unavailable' => 'Şube silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'branch_edit_route_unavailable' => 'Şube düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'branch_create_route_unavailable' => 'Şube oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'hrm_system_setup_route_unavailable' => '人力资源管理系统设置路由不可用。' . self::DELEGATION_ZH,
				'branch_store_route_unavailable' => '分支机构存储路由不可用。' . self::DELEGATION_ZH,
				'branch_update_route_unavailable' => '分支机构更新路由不可用。' . self::DELEGATION_ZH,
				'branch_destroy_route_unavailable' => '分支机构销毁路由不可用。' . self::DELEGATION_ZH,
				'branch_edit_route_unavailable' => '分支机构编辑路由不可用。' . self::DELEGATION_ZH,
				'branch_create_route_unavailable' => '分支机构创建路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::BDG => [
			'ar' => [
				'budget_planner_index_route_unavailable' => 'مسار فهرس مخطط الميزانية غير متاح. ' . self::DELEGATION_AR,
				'budget_planner_store_route_unavailable' => 'مسار مخطط الميزانية غير متاح. ' . self::DELEGATION_AR,
				'expense_cancel_route_unavailable' => 'مسار قسم المصروفات غير متاح. ' . self::DELEGATION_AR,
				'budget_update_route_unavailable' => 'مسار تحديث الميزانية غير متاح. ' . self::DELEGATION_AR,
				'budget_planner_create_route_unavailable' => 'مسار إنشاء مخطط الميزانية غير متاح. ' . self::DELEGATION_AR,
				'budget_plan_edit_route_unavailable' => 'مسار التعديل غير متاح. ' . self::DELEGATION_AR,
				'budget_plan_view_route_unavailable' => 'مسار العرض غير متاح. ' . self::DELEGATION_AR,
				'budget_plan_destroy_route_unavailable' => 'مسار إزالة خطة الميزانية غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'budget_planner_index_route_unavailable' => 'Budget Planner-indeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'budget_planner_store_route_unavailable' => 'Budget Planner-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'expense_cancel_route_unavailable' => 'Udgiftsafdelingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'budget_update_route_unavailable' => 'Budgetopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'budget_planner_create_route_unavailable' => 'Budget Planner oprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'budget_plan_edit_route_unavailable' => 'Redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'budget_plan_view_route_unavailable' => 'Visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'budget_plan_destroy_route_unavailable' => 'Sletningsrute for budgetplan er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'budget_planner_index_route_unavailable' => 'Budget Planner-Indexroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'budget_planner_store_route_unavailable' => 'Budget Planner-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'expense_cancel_route_unavailable' => 'Kostenbereich-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'budget_update_route_unavailable' => 'Budget-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'budget_planner_create_route_unavailable' => 'Budget Planner-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'budget_plan_edit_route_unavailable' => 'Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'budget_plan_view_route_unavailable' => 'Ansichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'budget_plan_destroy_route_unavailable' => 'Budgetplan-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'budget_planner_index_route_unavailable' => 'Budget Planner index route is unavailable. ' . self::DELEGATION_EN,
				'budget_planner_store_route_unavailable' => 'Budget Planner route is unavailable. ' . self::DELEGATION_EN,
				'expense_cancel_route_unavailable' => 'Expense section route is unavailable. ' . self::DELEGATION_EN,
				'budget_update_route_unavailable' => 'Budget update route is unavailable. ' . self::DELEGATION_EN,
				'budget_planner_create_route_unavailable' => 'Budget Planner create route is unavailable. ' . self::DELEGATION_EN,
				'budget_plan_edit_route_unavailable' => 'Edit route is unavailable. ' . self::DELEGATION_EN,
				'budget_plan_view_route_unavailable' => 'View route is unavailable. ' . self::DELEGATION_EN,
				'budget_plan_destroy_route_unavailable' => 'Budget plan destroy route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'budget_planner_index_route_unavailable' => 'La ruta de índice del Planificador de presupuestos no está disponible. ' . self::DELEGATION_ES,
				'budget_planner_store_route_unavailable' => 'La ruta del Planificador de presupuestos no está disponible. ' . self::DELEGATION_ES,
				'expense_cancel_route_unavailable' => 'La ruta de la sección de gastos no está disponible. ' . self::DELEGATION_ES,
				'budget_update_route_unavailable' => 'La ruta de actualización de presupuesto no está disponible. ' . self::DELEGATION_ES,
				'budget_planner_create_route_unavailable' => 'La ruta de creación del Planificador de presupuestos no está disponible. ' . self::DELEGATION_ES,
				'budget_plan_edit_route_unavailable' => 'La ruta de edición no está disponible. ' . self::DELEGATION_ES,
				'budget_plan_view_route_unavailable' => 'La ruta de visualización no está disponible. ' . self::DELEGATION_ES,
				'budget_plan_destroy_route_unavailable' => 'La ruta de eliminación del plan de presupuesto no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'budget_planner_index_route_unavailable' => 'La route d\'index du planificateur de budget n\'est pas disponible. ' . self::DELEGATION_FR,
				'budget_planner_store_route_unavailable' => 'La route du planificateur de budget n\'est pas disponible. ' . self::DELEGATION_FR,
				'expense_cancel_route_unavailable' => 'La route de la section des dépenses n\'est pas disponible. ' . self::DELEGATION_FR,
				'budget_update_route_unavailable' => 'La route de mise à jour du budget n\'est pas disponible. ' . self::DELEGATION_FR,
				'budget_planner_create_route_unavailable' => 'La route de création du planificateur de budget n\'est pas disponible. ' . self::DELEGATION_FR,
				'budget_plan_edit_route_unavailable' => 'La route d\'édition n\'est pas disponible. ' . self::DELEGATION_FR,
				'budget_plan_view_route_unavailable' => 'La route de visualisation n\'est pas disponible. ' . self::DELEGATION_FR,
				'budget_plan_destroy_route_unavailable' => 'La route de suppression du plan budgétaire n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'budget_planner_index_route_unavailable' => 'נתיב האינדקס של מתכנן התקציב אינו זמין. ' . self::DELEGATION_HE,
				'budget_planner_store_route_unavailable' => 'נתיב מתכנן התקציב אינו זמין. ' . self::DELEGATION_HE,
				'expense_cancel_route_unavailable' => 'נתיב חלק ההוצאות אינו זמין. ' . self::DELEGATION_HE,
				'budget_update_route_unavailable' => 'נתיב עדכון התקציב אינו זמין. ' . self::DELEGATION_HE,
				'budget_planner_create_route_unavailable' => 'נתיב יצירת מתכנן התקציב אינו זמין. ' . self::DELEGATION_HE,
				'budget_plan_edit_route_unavailable' => 'נתיב העריכה אינו זמין. ' . self::DELEGATION_HE,
				'budget_plan_view_route_unavailable' => 'נתיב התצוגה אינו זמין. ' . self::DELEGATION_HE,
				'budget_plan_destroy_route_unavailable' => 'נתיב ההשמדה של תוכנית התקציב אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'budget_planner_index_route_unavailable' => 'La rotta dell\'indice del Budget Planner non è disponibile. ' . self::DELEGATION_IT,
				'budget_planner_store_route_unavailable' => 'La rotta del Budget Planner non è disponibile. ' . self::DELEGATION_IT,
				'expense_cancel_route_unavailable' => 'La rotta della sezione spese non è disponibile. ' . self::DELEGATION_IT,
				'budget_update_route_unavailable' => 'La rotta di aggiornamento del budget non è disponibile. ' . self::DELEGATION_IT,
				'budget_planner_create_route_unavailable' => 'La rotta di creazione del Budget Planner non è disponibile. ' . self::DELEGATION_IT,
				'budget_plan_edit_route_unavailable' => 'La rotta di modifica non è disponibile. ' . self::DELEGATION_IT,
				'budget_plan_view_route_unavailable' => 'La rotta di visualizzazione non è disponibile. ' . self::DELEGATION_IT,
				'budget_plan_destroy_route_unavailable' => 'La rotta di eliminazione del piano budget non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'budget_planner_index_route_unavailable' => '予算プランナーのインデックスルートは利用できません。' . self::DELEGATION_JA,
				'budget_planner_store_route_unavailable' => '予算プランナールートは利用できません。' . self::DELEGATION_JA,
				'expense_cancel_route_unavailable' => '経費セクションのルートは利用できません。' . self::DELEGATION_JA,
				'budget_update_route_unavailable' => '予算更新ルートは利用できません。' . self::DELEGATION_JA,
				'budget_planner_create_route_unavailable' => '予算プランナー作成ルートは利用できません。' . self::DELEGATION_JA,
				'budget_plan_edit_route_unavailable' => '編集ルートは利用できません。' . self::DELEGATION_JA,
				'budget_plan_view_route_unavailable' => '表示ルートは利用できません。' . self::DELEGATION_JA,
				'budget_plan_destroy_route_unavailable' => '予算計画削除ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'budget_planner_index_route_unavailable' => 'Budgetplanner-indexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'budget_planner_store_route_unavailable' => 'Budgetplanner-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'expense_cancel_route_unavailable' => 'Uitgavensectie-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'budget_update_route_unavailable' => 'Budgetupdate-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'budget_planner_create_route_unavailable' => 'Budgetplanner-aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'budget_plan_edit_route_unavailable' => 'Bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'budget_plan_view_route_unavailable' => 'Bekijkroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'budget_plan_destroy_route_unavailable' => 'Budgetplan-verwijderingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'budget_planner_index_route_unavailable' => 'Trasa indeksu planera budżetu jest niedostępna. ' . self::DELEGATION_PL,
				'budget_planner_store_route_unavailable' => 'Trasa planera budżetu jest niedostępna. ' . self::DELEGATION_PL,
				'expense_cancel_route_unavailable' => 'Trasa sekcji wydatków jest niedostępna. ' . self::DELEGATION_PL,
				'budget_update_route_unavailable' => 'Trasa aktualizacji budżetu jest niedostępna. ' . self::DELEGATION_PL,
				'budget_planner_create_route_unavailable' => 'Trasa tworzenia planera budżetu jest niedostępna. ' . self::DELEGATION_PL,
				'budget_plan_edit_route_unavailable' => 'Trasa edycji jest niedostępna. ' . self::DELEGATION_PL,
				'budget_plan_view_route_unavailable' => 'Trasa podglądu jest niedostępna. ' . self::DELEGATION_PL,
				'budget_plan_destroy_route_unavailable' => 'Trasa usuwania planu budżetu jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'budget_planner_index_route_unavailable' => 'A rota de índice do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PT,
				'budget_planner_store_route_unavailable' => 'A rota do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PT,
				'expense_cancel_route_unavailable' => 'A rota da seção de despesas não está disponível. ' . self::DELEGATION_PT,
				'budget_update_route_unavailable' => 'A rota de atualização de orçamento não está disponível. ' . self::DELEGATION_PT,
				'budget_planner_create_route_unavailable' => 'A rota de criação do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PT,
				'budget_plan_edit_route_unavailable' => 'A rota de edição não está disponível. ' . self::DELEGATION_PT,
				'budget_plan_view_route_unavailable' => 'A rota de visualização não está disponível. ' . self::DELEGATION_PT,
				'budget_plan_destroy_route_unavailable' => 'A rota de destruição do plano orçamental não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'budget_planner_index_route_unavailable' => 'A rota de índice do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PTBR,
				'budget_planner_store_route_unavailable' => 'A rota do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PTBR,
				'expense_cancel_route_unavailable' => 'A rota da seção de despesas não está disponível. ' . self::DELEGATION_PTBR,
				'budget_update_route_unavailable' => 'A rota de atualização de orçamento não está disponível. ' . self::DELEGATION_PTBR,
				'budget_planner_create_route_unavailable' => 'A rota de criação do Planejador de Orçamento não está disponível. ' . self::DELEGATION_PTBR,
				'budget_plan_edit_route_unavailable' => 'A rota de edição não está disponível. ' . self::DELEGATION_PTBR,
				'budget_plan_view_route_unavailable' => 'A rota de visualização não está disponível. ' . self::DELEGATION_PTBR,
				'budget_plan_destroy_route_unavailable' => 'A rota de exclusão do plano orçamentário não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'budget_planner_index_route_unavailable' => 'Маршрут индекса планировщика бюджета недоступен. ' . self::DELEGATION_RU,
				'budget_planner_store_route_unavailable' => 'Маршрут планировщика бюджета недоступен. ' . self::DELEGATION_RU,
				'expense_cancel_route_unavailable' => 'Маршрут раздела расходов недоступен. ' . self::DELEGATION_RU,
				'budget_update_route_unavailable' => 'Маршрут обновления бюджета недоступен. ' . self::DELEGATION_RU,
				'budget_planner_create_route_unavailable' => 'Маршрут создания планировщика бюджета недоступен. ' . self::DELEGATION_RU,
				'budget_plan_edit_route_unavailable' => 'Маршрут редактирования недоступен. ' . self::DELEGATION_RU,
				'budget_plan_view_route_unavailable' => 'Маршрут просмотра недоступен. ' . self::DELEGATION_RU,
				'budget_plan_destroy_route_unavailable' => 'Маршрут удаления бюджетного плана недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'budget_planner_index_route_unavailable' => 'Bütçe Planlayıcı indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'budget_planner_store_route_unavailable' => 'Bütçe Planlayıcı rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'expense_cancel_route_unavailable' => 'Gider bölümü rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'budget_update_route_unavailable' => 'Bütçe güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'budget_planner_create_route_unavailable' => 'Bütçe Planlayıcı oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'budget_plan_edit_route_unavailable' => 'Düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'budget_plan_view_route_unavailable' => 'Görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'budget_plan_destroy_route_unavailable' => 'Bütçe planı silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'budget_planner_index_route_unavailable' => '预算计划器索引路由不可用。' . self::DELEGATION_ZH,
				'budget_planner_store_route_unavailable' => '预算计划器路由不可用。' . self::DELEGATION_ZH,
				'expense_cancel_route_unavailable' => '费用部分路由不可用。' . self::DELEGATION_ZH,
				'budget_update_route_unavailable' => '预算更新路由不可用。' . self::DELEGATION_ZH,
				'budget_planner_create_route_unavailable' => '预算计划器创建路由不可用。' . self::DELEGATION_ZH,
				'budget_plan_edit_route_unavailable' => '编辑路由不可用。' . self::DELEGATION_ZH,
				'budget_plan_view_route_unavailable' => '查看路由不可用。' . self::DELEGATION_ZH,
				'budget_plan_destroy_route_unavailable' => '预算计划删除路由不可用。' . self::DELEGATION_ZH,
			],
		],
		ViewsConstants::BUG => [
			'ar' => ['bug_view_route_unavailable' => 'مسار الأخطاء غير متاح. ' . self::DELEGATION_AR],
			'da' => ['bug_view_route_unavailable' => 'Fejl-rute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['bug_view_route_unavailable' => 'Fehler-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['bug_view_route_unavailable' => 'Bug route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['bug_view_route_unavailable' => 'La ruta de errores no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['bug_view_route_unavailable' => 'La route des bugs n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['bug_view_route_unavailable' => 'נתיב באגים אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['bug_view_route_unavailable' => 'La rotta dei bug non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['bug_view_route_unavailable' => 'バグルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['bug_view_route_unavailable' => 'Bugroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['bug_view_route_unavailable' => 'Trasa błędów jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['bug_view_route_unavailable' => 'A rota de bugs não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['bug_view_route_unavailable' => 'A rota de bugs não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['bug_view_route_unavailable' => 'Маршрут ошибок недоступен. ' . self::DELEGATION_RU],
			'tr' => ['bug_view_route_unavailable' => 'Hata rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['bug_view_route_unavailable' => '错误路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::BUG_STT => [
			'ar' => [
				'bug_status_index_route_unavailable' => 'مسار حالة الأخطاء غير متاح. ' . self::DELEGATION_AR,
				'bug_status_store_route_unavailable' => 'مسار تخزين حالة الخطأ غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'bug_status_update_route_unavailable' => 'مسار تحديث حالة الخطأ غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'bug_status_edit_route_unavailable' => 'مسار تعديل حالة الخطأ غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'bug_status_destroy_route_unavailable' => 'مسار حذف حالة الخطأ غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR
			],
			'da' => [
				'bug_status_index_route_unavailable' => 'Fejlstatusrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bug_status_store_route_unavailable' => 'Fejlstatus lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bug_status_update_route_unavailable' => 'Fejlstatus opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bug_status_edit_route_unavailable' => 'Fejlstatus redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bug_status_destroy_route_unavailable' => 'Fejlstatus sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'bug_status_index_route_unavailable' => 'Fehlerstatus-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bug_status_store_route_unavailable' => 'Bug-Status-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bug_status_update_route_unavailable' => 'Bug-Status-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bug_status_edit_route_unavailable' => 'Bug-Status-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bug_status_destroy_route_unavailable' => 'Bug-Status-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'bug_status_index_route_unavailable' => 'Bug Status route is unavailable. ' . self::DELEGATION_EN,
				'bug_status_store_route_unavailable' => 'Bug Status store route is unavailable. ' . self::DELEGATION_EN,
				'bug_status_update_route_unavailable' => 'Bug Status update route is unavailable. ' . self::DELEGATION_EN,
				'bug_status_edit_route_unavailable' => 'Edit Bug Status route is unavailable. ' . self::DELEGATION_EN,
				'bug_status_destroy_route_unavailable' => 'Delete Bug Status route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'bug_status_index_route_unavailable' => 'La ruta de estado de errores no está disponible. ' . self::DELEGATION_ES,
				'bug_status_store_route_unavailable' => 'La ruta de almacenamiento de estado de error no está disponible. ' . self::DELEGATION_ES,
				'bug_status_update_route_unavailable' => 'La ruta de actualización de estado de error no está disponible. ' . self::DELEGATION_ES,
				'bug_status_edit_route_unavailable' => 'La ruta de edición de estado de error no está disponible. ' . self::DELEGATION_ES,
				'bug_status_destroy_route_unavailable' => 'La ruta de eliminación de estado de error no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'bug_status_index_route_unavailable' => 'La route du statut des bugs n\'est pas disponible. ' . self::DELEGATION_FR,
				'bug_status_store_route_unavailable' => 'La route de stockage du statut de bug est indisponible. ' . self::DELEGATION_FR,
				'bug_status_update_route_unavailable' => 'La route de mise à jour du statut de bug est indisponible. ' . self::DELEGATION_FR,
				'bug_status_edit_route_unavailable' => 'La route d\'édition du statut de bug est indisponible. ' . self::DELEGATION_FR,
				'bug_status_destroy_route_unavailable' => 'La route de suppression du statut de bug est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'bug_status_index_route_unavailable' => 'נתיב סטטוס באגים אינו זמין. ' . self::DELEGATION_HE,
				'bug_status_store_route_unavailable' => 'נתיב אחסון סטטוס באגים אינו זמין. ' . self::DELEGATION_HE,
				'bug_status_update_route_unavailable' => 'נתיב עדכון סטטוס באגים אינו זמין. ' . self::DELEGATION_HE,
				'bug_status_edit_route_unavailable' => 'נתיב עריכת סטטוס באגים אינו זמין. ' . self::DELEGATION_HE,
				'bug_status_destroy_route_unavailable' => 'נתיב מחיקת סטטוס באגים אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'bug_status_index_route_unavailable' => 'La rotta dello stato dei bug non è disponibile. ' . self::DELEGATION_IT,
				'bug_status_store_route_unavailable' => 'La rotta di archiviazione dello stato dei bug non è disponibile. ' . self::DELEGATION_IT,
				'bug_status_update_route_unavailable' => 'La rotta di aggiornamento dello stato dei bug non è disponibile. ' . self::DELEGATION_IT,
				'bug_status_edit_route_unavailable' => 'La rotta di modifica dello stato dei bug non è disponibile. ' . self::DELEGATION_IT,
				'bug_status_destroy_route_unavailable' => 'La rotta di eliminazione dello stato dei bug non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'bug_status_index_route_unavailable' => 'バグステータスルートは利用できません。' . self::DELEGATION_JA,
				'bug_status_store_route_unavailable' => 'バグステータス保存ルートは利用できません。' . self::DELEGATION_JA,
				'bug_status_update_route_unavailable' => 'バグステータス更新ルートは利用できません。' . self::DELEGATION_JA,
				'bug_status_edit_route_unavailable' => 'バグステータス編集ルートは利用できません。' . self::DELEGATION_JA,
				'bug_status_destroy_route_unavailable' => 'バグステータス削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'bug_status_index_route_unavailable' => 'Bugstatusroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bug_status_store_route_unavailable' => 'Bugstatus opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bug_status_update_route_unavailable' => 'Bugstatus updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bug_status_edit_route_unavailable' => 'Bugstatus bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bug_status_destroy_route_unavailable' => 'Bugstatus verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'bug_status_index_route_unavailable' => 'Trasa statusu błędów jest niedostępna. ' . self::DELEGATION_PL,
				'bug_status_store_route_unavailable' => 'Trasa przechowywania statusu błędu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'bug_status_update_route_unavailable' => 'Trasa aktualizacji statusu błędu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'bug_status_edit_route_unavailable' => 'Trasa edycji statusu błędu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'bug_status_destroy_route_unavailable' => 'Trasa usuwania statusu błędu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL
			],
			'pt' => [
				'bug_status_index_route_unavailable' => 'A rota de status de bugs não está disponível. ' . self::DELEGATION_PT,
				'bug_status_store_route_unavailable' => 'A rota de armazenamento de status de bug não está disponível. ' . self::DELEGATION_PT,
				'bug_status_update_route_unavailable' => 'A rota de atualização de status de bug não está disponível. ' . self::DELEGATION_PT,
				'bug_status_edit_route_unavailable' => 'A rota de edição de status de bug não está disponível. ' . self::DELEGATION_PT,
				'bug_status_destroy_route_unavailable' => 'A rota de exclusão de status de bug não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'bug_status_index_route_unavailable' => 'A rota de status de bugs não está disponível. ' . self::DELEGATION_PTBR,
				'bug_status_store_route_unavailable' => 'A rota de armazenamento de status de bug não está disponível. ' . self::DELEGATION_PTBR,
				'bug_status_update_route_unavailable' => 'A rota de atualização de status de bug não está disponível. ' . self::DELEGATION_PTBR,
				'bug_status_edit_route_unavailable' => 'A rota de edição de status de bug não está disponível. ' . self::DELEGATION_PTBR,
				'bug_status_destroy_route_unavailable' => 'A rota de exclusão de status de bug não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'bug_status_index_route_unavailable' => 'Маршрут статуса ошибок недоступен. ' . self::DELEGATION_RU,
				'bug_status_store_route_unavailable' => 'Маршрут сохранения статуса ошибки недоступен. ' . self::DELEGATION_RU,
				'bug_status_update_route_unavailable' => 'Маршрут обновления статуса ошибки недоступен. ' . self::DELEGATION_RU,
				'bug_status_edit_route_unavailable' => 'Маршрут редактирования статуса ошибки недоступен. ' . self::DELEGATION_RU,
				'bug_status_destroy_route_unavailable' => 'Маршрут удаления статуса ошибки недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'bug_status_index_route_unavailable' => 'Hata Durumu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bug_status_store_route_unavailable' => 'Hata Durumu depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bug_status_update_route_unavailable' => 'Hata Durumu güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bug_status_edit_route_unavailable' => 'Hata Durumu düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bug_status_destroy_route_unavailable' => 'Hata Durumu silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'bug_status_index_route_unavailable' => '错误状态路由不可用。' . self::DELEGATION_ZH,
				'bug_status_store_route_unavailable' => '错误状态存储路由不可用。' . self::DELEGATION_ZH,
				'bug_status_update_route_unavailable' => '错误状态更新路由不可用。' . self::DELEGATION_ZH,
				'bug_status_edit_route_unavailable' => '错误状态编辑路由不可用。' . self::DELEGATION_ZH,
				'bug_status_destroy_route_unavailable' => '错误状态删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		'business' => [
			'ar' => [
				'business_setting_route_unavailable' => 'مسار إعدادات الأعمال غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'business_setting_route_unavailable' => 'Business indstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'business_setting_route_unavailable' => 'Geschäftseinstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'business_setting_route_unavailable' => 'Business setting route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'business_setting_route_unavailable' => 'La ruta de configuración de negocio no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'business_setting_route_unavailable' => 'La route des paramètres commerciaux n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'business_setting_route_unavailable' => 'נתיב הגדרת העסק אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'business_setting_route_unavailable' => 'La rotta delle impostazioni aziendali non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'business_setting_route_unavailable' => 'ビジネス設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'business_setting_route_unavailable' => 'Bedrijfsinstellingenroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'business_setting_route_unavailable' => 'Trasa ustawień biznesowych jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'business_setting_route_unavailable' => 'A rota de definições de negócio não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'business_setting_route_unavailable' => 'A rota de configurações de negócios não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'business_setting_route_unavailable' => 'Маршрут бизнес-настроек недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'business_setting_route_unavailable' => 'İş ayarı rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'business_setting_route_unavailable' => '业务设置路由不可用。' . self::DELEGATION_ZH,
			]
		],
		'chats' => [
			'ar' => ['messenger_index_route_unavailable' => 'مسار المراسلة غير متاح. ' . self::DELEGATION_AR],
			'da' => ['messenger_index_route_unavailable' => 'Messenger-rute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['messenger_index_route_unavailable' => 'Messenger-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['messenger_index_route_unavailable' => 'Messenger route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['messenger_index_route_unavailable' => 'La ruta de mensajería no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['messenger_index_route_unavailable' => 'La route du messager n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['messenger_index_route_unavailable' => 'נתיב שליח אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['messenger_index_route_unavailable' => 'La rotta del messenger non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['messenger_index_route_unavailable' => 'メッセンジャールートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['messenger_index_route_unavailable' => 'Messengerroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['messenger_index_route_unavailable' => 'Trasa messengera jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['messenger_index_route_unavailable' => 'A rota do messenger não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['messenger_index_route_unavailable' => 'A rota do messenger não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['messenger_index_route_unavailable' => 'Маршрут мессенджера недоступен. ' . self::DELEGATION_RU],
			'tr' => ['messenger_index_route_unavailable' => 'Mesajlaşma rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['messenger_index_route_unavailable' => '消息路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::CLT => [
			'ar' => [
				'client_index_route_unavailable' => 'مسار العملاء غير متاح. ' . self::DELEGATION_AR,
				'client_dashboard_view_route_unavailable' => 'مسار لوحة تحكم العميل غير متاح. ' . self::DELEGATION_AR,
				'client_create_route_unavailable' => 'مسار إنشاء عميل غير متاح. ' . self::DELEGATION_AR,
				'client_edit_route_unavailable' => 'مسار تعديل عميل غير متاح. ' . self::DELEGATION_AR,
				'client_destroy_route_unavailable' => 'مسار حذف عميل غير متاح. ' . self::DELEGATION_AR,
				'client_reset_route_unavailable' => 'مسار إعادة تعيين كلمة المرور غير متاح. ' . self::DELEGATION_AR,
				'client_password_update_route_unavailable' => 'مسار تحديث كلمة مرور العميل غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'client_index_route_unavailable' => 'Klientrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'client_dashboard_view_route_unavailable' => 'Klientdashboard-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'client_create_route_unavailable' => 'Oprettelsesrute for klient er ikke tilgængelig. ' . self::DELEGATION_DA,
				'client_edit_route_unavailable' => 'Redigeringsrute for klient er ikke tilgængelig. ' . self::DELEGATION_DA,
				'client_destroy_route_unavailable' => 'Sletningsrute for klient er ikke tilgængelig. ' . self::DELEGATION_DA,
				'client_reset_route_unavailable' => 'Nulstilling af adgangskoderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'client_password_update_route_unavailable' => 'Klientadgangskodeopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'client_index_route_unavailable' => 'Kunden-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'client_dashboard_view_route_unavailable' => 'Kunden-Dashboard-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'client_create_route_unavailable' => 'Kunden-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'client_edit_route_unavailable' => 'Kunden-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'client_destroy_route_unavailable' => 'Kunden-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'client_reset_route_unavailable' => 'Passwort-Zurücksetzungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'client_password_update_route_unavailable' => 'Kunden-Passwort-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'client_index_route_unavailable' => 'Clients route is unavailable. ' . self::DELEGATION_EN,
				'client_dashboard_view_route_unavailable' => 'Client Dashboard route is unavailable. ' . self::DELEGATION_EN,
				'client_create_route_unavailable' => 'Create Client route is unavailable. ' . self::DELEGATION_EN,
				'client_edit_route_unavailable' => 'Edit Client route is unavailable. ' . self::DELEGATION_EN,
				'client_destroy_route_unavailable' => 'Delete Client route is unavailable. ' . self::DELEGATION_EN,
				'client_reset_route_unavailable' => 'Reset Password route is unavailable. ' . self::DELEGATION_EN,
				'client_password_update_route_unavailable' => 'Client password update route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'client_index_route_unavailable' => 'La ruta de clientes no está disponible. ' . self::DELEGATION_ES,
				'client_dashboard_view_route_unavailable' => 'La ruta del panel de control del cliente no está disponible. ' . self::DELEGATION_ES,
				'client_create_route_unavailable' => 'La ruta de creación de cliente no está disponible. ' . self::DELEGATION_ES,
				'client_edit_route_unavailable' => 'La ruta de edición de cliente no está disponible. ' . self::DELEGATION_ES,
				'client_destroy_route_unavailable' => 'La ruta de eliminación de cliente no está disponible. ' . self::DELEGATION_ES,
				'client_reset_route_unavailable' => 'La ruta de restablecimiento de contraseña no está disponible. ' . self::DELEGATION_ES,
				'client_password_update_route_unavailable' => 'La ruta de actualización de contraseña del cliente no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'client_index_route_unavailable' => 'La route des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'client_dashboard_view_route_unavailable' => 'La route du tableau de bord client n\'est pas disponible. ' . self::DELEGATION_FR,
				'client_create_route_unavailable' => 'La route de création de client n\'est pas disponible. ' . self::DELEGATION_FR,
				'client_edit_route_unavailable' => 'La route d\'édition de client n\'est pas disponible. ' . self::DELEGATION_FR,
				'client_destroy_route_unavailable' => 'La route de suppression de client n\'est pas disponible. ' . self::DELEGATION_FR,
				'client_reset_route_unavailable' => 'La route de réinitialisation du mot de passe n\'est pas disponible. ' . self::DELEGATION_FR,
				'client_password_update_route_unavailable' => 'La route de mise à jour du mot de passe client n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'client_index_route_unavailable' => 'נתיב לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'client_dashboard_view_route_unavailable' => 'נתיב לוח מחוונים של לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'client_create_route_unavailable' => 'נתיב יצירת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'client_edit_route_unavailable' => 'נתיב עריכת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'client_destroy_route_unavailable' => 'נתיב מחיקת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'client_reset_route_unavailable' => 'נתיב איפוס סיסמה אינו זמין. ' . self::DELEGATION_HE,
				'client_password_update_route_unavailable' => 'נתיב עדכון סיסמת הלקוח אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'client_index_route_unavailable' => 'La rotta dei clienti non è disponibile. ' . self::DELEGATION_IT,
				'client_dashboard_view_route_unavailable' => 'La rotta della dashboard del cliente non è disponibile. ' . self::DELEGATION_IT,
				'client_create_route_unavailable' => 'La rotta di creazione del cliente non è disponibile. ' . self::DELEGATION_IT,
				'client_edit_route_unavailable' => 'La rotta di modifica del cliente non è disponibile. ' . self::DELEGATION_IT,
				'client_destroy_route_unavailable' => 'La rotta di eliminazione del cliente non è disponibile. ' . self::DELEGATION_IT,
				'client_reset_route_unavailable' => 'La rotta di reimpostazione della password non è disponibile. ' . self::DELEGATION_IT,
				'client_password_update_route_unavailable' => 'La rotta di aggiornamento della password del cliente non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'client_index_route_unavailable' => 'クライアントルートは利用できません。' . self::DELEGATION_JA,
				'client_dashboard_view_route_unavailable' => 'クライアントダッシュボードルートは利用できません。' . self::DELEGATION_JA,
				'client_create_route_unavailable' => 'クライアント作成ルートは利用できません。' . self::DELEGATION_JA,
				'client_edit_route_unavailable' => 'クライアント編集ルートは利用できません。' . self::DELEGATION_JA,
				'client_destroy_route_unavailable' => 'クライアント削除ルートは利用できません。' . self::DELEGATION_JA,
				'client_reset_route_unavailable' => 'パスワードリセットルートは利用できません。' . self::DELEGATION_JA,
				'client_password_update_route_unavailable' => 'クライアントパスワード更新ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'client_index_route_unavailable' => 'Klantenroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'client_dashboard_view_route_unavailable' => 'Klantendashboardroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'client_create_route_unavailable' => 'Klant aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'client_edit_route_unavailable' => 'Klant bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'client_destroy_route_unavailable' => 'Klant verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'client_reset_route_unavailable' => 'Wachtwoord resetroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'client_password_update_route_unavailable' => 'Klant wachtwoord updateroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'client_index_route_unavailable' => 'Trasa klientów jest niedostępna. ' . self::DELEGATION_PL,
				'client_dashboard_view_route_unavailable' => 'Trasa pulpitu nawigacyjnego klienta jest niedostępna. ' . self::DELEGATION_PL,
				'client_create_route_unavailable' => 'Trasa tworzenia klienta jest niedostępna. ' . self::DELEGATION_PL,
				'client_edit_route_unavailable' => 'Trasa edycji klienta jest niedostępna. ' . self::DELEGATION_PL,
				'client_destroy_route_unavailable' => 'Trasa usuwania klienta jest niedostępna. ' . self::DELEGATION_PL,
				'client_reset_route_unavailable' => 'Trasa resetowania hasła jest niedostępna. ' . self::DELEGATION_PL,
				'client_password_update_route_unavailable' => 'Trasa aktualizacji hasła klienta jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'client_index_route_unavailable' => 'A rota de clientes não está disponível. ' . self::DELEGATION_PT,
				'client_dashboard_view_route_unavailable' => 'A rota do painel do cliente não está disponível. ' . self::DELEGATION_PT,
				'client_create_route_unavailable' => 'A rota de criação de cliente não está disponível. ' . self::DELEGATION_PT,
				'client_edit_route_unavailable' => 'A rota de edição de cliente não está disponível. ' . self::DELEGATION_PT,
				'client_destroy_route_unavailable' => 'A rota de exclusão de cliente não está disponível. ' . self::DELEGATION_PT,
				'client_reset_route_unavailable' => 'A rota de redefinição de senha não está disponível. ' . self::DELEGATION_PT,
				'client_password_update_route_unavailable' => 'A rota de atualização de senha do cliente não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'client_index_route_unavailable' => 'A rota de clientes não está disponível. ' . self::DELEGATION_PTBR,
				'client_dashboard_view_route_unavailable' => 'A rota do painel do cliente não está disponível. ' . self::DELEGATION_PTBR,
				'client_create_route_unavailable' => 'A rota de criação de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'client_edit_route_unavailable' => 'A rota de edição de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'client_destroy_route_unavailable' => 'A rota de exclusão de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'client_reset_route_unavailable' => 'A rota de redefinição de senha não está disponível. ' . self::DELEGATION_PTBR,
				'client_password_update_route_unavailable' => 'A rota de atualização de senha do cliente não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'client_index_route_unavailable' => 'Маршрут клиентов недоступен. ' . self::DELEGATION_RU,
				'client_dashboard_view_route_unavailable' => 'Маршрут клиентской панели недоступен. ' . self::DELEGATION_RU,
				'client_create_route_unavailable' => 'Маршрут создания клиента недоступен. ' . self::DELEGATION_RU,
				'client_edit_route_unavailable' => 'Маршрут редактирования клиента недоступен. ' . self::DELEGATION_RU,
				'client_destroy_route_unavailable' => 'Маршрут удаления клиента недоступен. ' . self::DELEGATION_RU,
				'client_reset_route_unavailable' => 'Маршрут сброса пароля недоступен. ' . self::DELEGATION_RU,
				'client_password_update_route_unavailable' => 'Маршрут обновления пароля клиента недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'client_index_route_unavailable' => 'Müşteri rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'client_dashboard_view_route_unavailable' => 'Müşteri Panosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'client_create_route_unavailable' => 'Müşteri oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'client_edit_route_unavailable' => 'Müşteri düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'client_destroy_route_unavailable' => 'Müşteri silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'client_reset_route_unavailable' => 'Parola sıfırlama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'client_password_update_route_unavailable' => 'Müşteri parola güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'client_index_route_unavailable' => '客户路由不可用。' . self::DELEGATION_ZH,
				'client_dashboard_view_route_unavailable' => '客户仪表板路由不可用。' . self::DELEGATION_ZH,
				'client_create_route_unavailable' => '创建客户路由不可用。' . self::DELEGATION_ZH,
				'client_edit_route_unavailable' => '编辑客户路由不可用。' . self::DELEGATION_ZH,
				'client_destroy_route_unavailable' => '删除客户路由不可用。' . self::DELEGATION_ZH,
				'client_reset_route_unavailable' => '密码重置路由不可用。' . self::DELEGATION_ZH,
				'client_password_update_route_unavailable' => '客户密码更新路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::COA => [
			'ar' => [
				'coa_index_route_unavailable' => 'مسار مخطط الحسابات غير متاح. ' . self::DELEGATION_AR,
				'chart_of_account_store_route_unavailable' => 'مسار تخزين مخطط  ' . self::DELEGATION_AR,
				'chart_of_account_update_route_unavailable' => 'مسار تحديث مخطط  ' . self::DELEGATION_AR,
				'chart_of_account_destroy_route_unavailable' => 'مسار حذف الحساب غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'chart_of_account_edit_route_unavailable' => 'مسار تعديل الحساب غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'chart_of_account_show_route_unavailable' => 'مسار عرض مخطط  ' . self::DELEGATION_AR
			],
			'da' => [
				'coa_index_route_unavailable' => 'Kontoplansrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_store_route_unavailable' => 'Kontoplan lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_update_route_unavailable' => 'Kontoplan opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_destroy_route_unavailable' => 'Sletning af konto rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_edit_route_unavailable' => 'Redigeringsrute for konto er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_show_route_unavailable' => 'Kontoplan visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'coa_index_route_unavailable' => 'Kontenplanroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_store_route_unavailable' => 'Kontenplan-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_update_route_unavailable' => 'Kontenplan-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_destroy_route_unavailable' => 'Konto-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_edit_route_unavailable' => 'Konto-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_show_route_unavailable' => 'Kontenplan-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'coa_index_route_unavailable' => 'Chart of Accounts route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_store_route_unavailable' => 'Chart of Account store route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_update_route_unavailable' => 'Chart of Account update route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_destroy_route_unavailable' => 'Delete Account route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_edit_route_unavailable' => 'Edit Account route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_show_route_unavailable' => 'Chart of Account show route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'coa_index_route_unavailable' => 'La ruta del Plan de Cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_store_route_unavailable' => 'La ruta de almacenamiento del Plan de Cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_update_route_unavailable' => 'La ruta de actualización del Plan de Cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_destroy_route_unavailable' => 'La ruta de eliminación de cuenta no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_edit_route_unavailable' => 'La ruta de edición de cuenta no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_show_route_unavailable' => 'La ruta de visualización del Plan de Cuentas no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'coa_index_route_unavailable' => 'La route du Plan Comptable n\'est pas disponible. ' . self::DELEGATION_FR,
				'chart_of_account_store_route_unavailable' => 'La route de stockage du Plan Comptable est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_update_route_unavailable' => 'La route de mise à jour du Plan Comptable est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_destroy_route_unavailable' => 'La route de suppression de compte est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_edit_route_unavailable' => 'La route d\'édition de compte est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_show_route_unavailable' => 'La route d\'affichage du Plan Comptable est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'coa_index_route_unavailable' => 'נתיב תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_store_route_unavailable' => 'נתיב אחסון תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_update_route_unavailable' => 'נתיב עדכון תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_destroy_route_unavailable' => 'נתיב מחיקת חשבון אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_edit_route_unavailable' => 'נתיב עריכת חשבון אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_show_route_unavailable' => 'נתיב הצגת תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'coa_index_route_unavailable' => 'Il percorso del Piano dei Conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_store_route_unavailable' => 'La rotta di archiviazione del Piano dei Conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_update_route_unavailable' => 'La rotta di aggiornamento del Piano dei Conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_destroy_route_unavailable' => 'La rotta di eliminazione del conto non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_edit_route_unavailable' => 'La rotta di modifica del conto non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_show_route_unavailable' => 'La rotta di visualizzazione del Piano dei Conti non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'coa_index_route_unavailable' => '勘定科目表ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_store_route_unavailable' => '勘定科目表保存ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_update_route_unavailable' => '勘定科目表更新ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_destroy_route_unavailable' => '勘定科目削除ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_edit_route_unavailable' => '勘定科目編集ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_show_route_unavailable' => '勘定科目表示ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'coa_index_route_unavailable' => 'Rekeningenschemaroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_store_route_unavailable' => 'Rekeningenschema opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_update_route_unavailable' => 'Rekeningenschema updateroute is niet beschikbaar. Neem contact op met technische ondersteuning or uw domeinbeheerder. ' . self::DELEGATION_NL,
				'chart_of_account_destroy_route_unavailable' => 'Rekening verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_edit_route_unavailable' => 'Rekening bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_show_route_unavailable' => 'Rekeningenschema weergaveroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'coa_index_route_unavailable' => 'Trasa wykazu kont jest niedostępna. ' . self::DELEGATION_PL,
				'chart_of_account_store_route_unavailable' => 'Trasa przechowywania wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_update_route_unavailable' => 'Trasa aktualizacji wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_destroy_route_unavailable' => 'Trasa usuwania konta jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_edit_route_unavailable' => 'Trasa edycji konta jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_show_route_unavailable' => 'Trasa wyświetlania wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL
			],
			'pt' => [
				'coa_index_route_unavailable' => 'A rota do Plano de Contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_store_route_unavailable' => 'A rota de armazenamento do Plano de Contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_update_route_unavailable' => 'A rota de atualização do Plano de Contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_destroy_route_unavailable' => 'A rota de exclusão de conta não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_edit_route_unavailable' => 'A rota de edição de conta não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_show_route_unavailable' => 'A rota de exibição do Plano de Contas não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'coa_index_route_unavailable' => 'A rota do Plano de Contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_store_route_unavailable' => 'A rota de armazenamento do Plano de Contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_update_route_unavailable' => 'A rota de atualização do Plano de Contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_destroy_route_unavailable' => 'A rota de exclusão de conta não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_edit_route_unavailable' => 'A rota de edição de conta não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_show_route_unavailable' => 'A rota de exibição do Plano de Contas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'coa_index_route_unavailable' => 'Маршрут Плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_store_route_unavailable' => 'Маршрут сохранения Плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_update_route_unavailable' => 'Маршрут обновления Плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_destroy_route_unavailable' => 'Маршрут удаления счета недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_edit_route_unavailable' => 'Маршрут редактирования счета недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_show_route_unavailable' => 'Маршрут отображения Плана счетов недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'coa_index_route_unavailable' => 'Hesap Planı rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_store_route_unavailable' => 'Hesap Planı depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_update_route_unavailable' => 'Hesap Planı güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_destroy_route_unavailable' => 'Hesap silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_edit_route_unavailable' => 'Hesap düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_show_route_unavailable' => 'Hesap Planı görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'coa_index_route_unavailable' => '会计科目表路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_store_route_unavailable' => '会计科目表存储路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_update_route_unavailable' => '会计科目表更新路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_destroy_route_unavailable' => '账户删除路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_edit_route_unavailable' => '账户编辑路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_show_route_unavailable' => '会计科目表显示路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::COA_TP => [
			'ar' => [
				'chart_of_account_type_store_route_unavailable' => 'مسار تخزين نوع مخطط  ' . self::DELEGATION_AR,
				'chart_of_account_type_update_route_unavailable' => 'مسار تحديث نوع مخطط  ' . self::DELEGATION_AR,
				'chart_of_account_type_edit_route_unavailable' => 'مسار تعديل نوع مخطط  ' . self::DELEGATION_AR,
				'chart_of_account_type_create_route_unavailable' => 'مسار إنشاء نوع مخطط  ' . self::DELEGATION_AR,
				'chart_of_account_type_destroy_route_unavailable' => 'مسار حذف نوع مخطط  ' . self::DELEGATION_AR
			],
			'da' => [
				'chart_of_account_type_store_route_unavailable' => 'Kontoplantype lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_type_update_route_unavailable' => 'Kontoplantype opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_type_edit_route_unavailable' => 'Redigeringsrute for kontoplantype er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_type_create_route_unavailable' => 'Oprettelsesrute for kontoplantype er ikke tilgængelig. ' . self::DELEGATION_DA,
				'chart_of_account_type_destroy_route_unavailable' => 'Sletningsrute for kontoplantype er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'chart_of_account_type_store_route_unavailable' => 'Kontenplan-Typ-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_type_update_route_unavailable' => 'Kontenplan-Typ-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_type_edit_route_unavailable' => 'Kontenplan-Typ-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_type_create_route_unavailable' => 'Kontenplan-Typ-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'chart_of_account_type_destroy_route_unavailable' => 'Kontenplan-Typ-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'chart_of_account_type_store_route_unavailable' => 'Chart of Account Type store route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_type_update_route_unavailable' => 'Chart of Account Type update route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_type_edit_route_unavailable' => 'Edit Chart of Account Type route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_type_create_route_unavailable' => 'Create Chart of Account Type route is unavailable. ' . self::DELEGATION_EN,
				'chart_of_account_type_destroy_route_unavailable' => 'Delete Chart of Account Type route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'chart_of_account_type_store_route_unavailable' => 'La ruta de almacenamiento de tipo de plan de cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_type_update_route_unavailable' => 'La ruta de actualización de tipo de plan de cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_type_edit_route_unavailable' => 'La ruta de edición de tipo de plan de cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_type_create_route_unavailable' => 'La ruta de creación de tipo de plan de cuentas no está disponible. ' . self::DELEGATION_ES,
				'chart_of_account_type_destroy_route_unavailable' => 'La ruta de eliminación de tipo de plan de cuentas no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'chart_of_account_type_store_route_unavailable' => 'La route de stockage du type de plan comptable est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_type_update_route_unavailable' => 'La route de mise à jour du type de plan comptable est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_type_edit_route_unavailable' => 'La route d\'édition du type de plan comptable est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_type_create_route_unavailable' => 'La route de création du type de plan comptable est indisponible. ' . self::DELEGATION_FR,
				'chart_of_account_type_destroy_route_unavailable' => 'La route de suppression du type de plan comptable est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'chart_of_account_type_store_route_unavailable' => 'נתיב אחסון סוג תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_type_update_route_unavailable' => 'נתיב עדכון סוג תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_type_edit_route_unavailable' => 'נתיב עריכת סוג תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_type_create_route_unavailable' => 'נתיב יצירת סוג תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'chart_of_account_type_destroy_route_unavailable' => 'נתיב מחיקת סוג תרשים החשבונות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'chart_of_account_type_store_route_unavailable' => 'La rotta di archiviazione del tipo di piano dei conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_type_update_route_unavailable' => 'La rotta di aggiornamento del tipo di piano dei conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_type_edit_route_unavailable' => 'La rotta di modifica del tipo di piano dei conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_type_create_route_unavailable' => 'La rotta di creazione del tipo di piano dei conti non è disponibile. ' . self::DELEGATION_IT,
				'chart_of_account_type_destroy_route_unavailable' => 'La rotta di eliminazione del tipo di piano dei conti non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'chart_of_account_type_store_route_unavailable' => '勘定科目表タイプ保存ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_type_update_route_unavailable' => '勘定科目表タイプ更新ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_type_edit_route_unavailable' => '勘定科目表タイプ編集ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_type_create_route_unavailable' => '勘定科目表タイプ作成ルートは利用できません。' . self::DELEGATION_JA,
				'chart_of_account_type_destroy_route_unavailable' => '勘定科目表タイプ削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'chart_of_account_type_store_route_unavailable' => 'Rekeningenschematype opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_type_update_route_unavailable' => 'Rekeningenschematype updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_type_edit_route_unavailable' => 'Rekeningenschematype bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_type_create_route_unavailable' => 'Rekeningenschematype aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'chart_of_account_type_destroy_route_unavailable' => 'Rekeningenschematype verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'chart_of_account_type_store_route_unavailable' => 'Trasa przechowywania typu wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_type_update_route_unavailable' => 'Trasa aktualizacji typu wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_type_edit_route_unavailable' => 'Trasa edycji typu wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_type_create_route_unavailable' => 'Trasa tworzenia typu wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'chart_of_account_type_destroy_route_unavailable' => 'Trasa usuwania typu wykazu kont jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL
			],
			'pt' => [
				'chart_of_account_type_store_route_unavailable' => 'A rota de armazenamento de tipo de plano de contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_type_update_route_unavailable' => 'A rota de atualização de tipo de plano de contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_type_edit_route_unavailable' => 'A rota de edição de tipo de plano de contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_type_create_route_unavailable' => 'A rota de criação de tipo de plano de contas não está disponível. ' . self::DELEGATION_PT,
				'chart_of_account_type_destroy_route_unavailable' => 'A rota de exclusão de tipo de plano de contas não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'chart_of_account_type_store_route_unavailable' => 'A rota de armazenamento de tipo de plano de contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_type_update_route_unavailable' => 'A rota de atualização de tipo de plano de contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_type_edit_route_unavailable' => 'A rota de edição de tipo de plano de contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_type_create_route_unavailable' => 'A rota de criação de tipo de plano de contas não está disponível. ' . self::DELEGATION_PTBR,
				'chart_of_account_type_destroy_route_unavailable' => 'A rota de exclusão de tipo de plano de contas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'chart_of_account_type_store_route_unavailable' => 'Маршрут сохранения типа плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_type_update_route_unavailable' => 'Маршрут обновления типа плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_type_edit_route_unavailable' => 'Маршрут редактирования типа плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_type_create_route_unavailable' => 'Маршрут создания типа плана счетов недоступен. ' . self::DELEGATION_RU,
				'chart_of_account_type_destroy_route_unavailable' => 'Маршрут удаления типа плана счетов недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'chart_of_account_type_store_route_unavailable' => 'Hesap Planı Türü depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_type_update_route_unavailable' => 'Hesap Planı Türü güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_type_edit_route_unavailable' => 'Hesap Planı Türü düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_type_create_route_unavailable' => 'Hesap Planı Türü oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'chart_of_account_type_destroy_route_unavailable' => 'Hesap Planı Türü silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'chart_of_account_type_store_route_unavailable' => '会计科目表类型存储路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_type_update_route_unavailable' => '会计科目表类型更新路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_type_edit_route_unavailable' => '会计科目表类型编辑路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_type_create_route_unavailable' => '会计科目表类型创建路由不可用。' . self::DELEGATION_ZH,
				'chart_of_account_type_destroy_route_unavailable' => '会计科目表类型删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::COM => [
			'ar' => [
				'commission_store_route_unavailable' => 'مسار تخزين العمولة غير متاح. ' . self::DELEGATION_AR,
				'commission_create_route_unavailable' => 'مسار إنشاء العمولة غير متاح. ' . self::DELEGATION_AR,
				'commission_edit_route_unavailable' => 'مسار تعديل العمولة غير متاح. ' . self::DELEGATION_AR,
				'commission_destroy_route_unavailable' => 'مسار حذف العمولة غير متاح. ' . self::DELEGATION_AR,
				'commission_update_route_unavailable' => 'مسار تحديث العمولة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'commission_store_route_unavailable' => 'Kommission lagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'commission_create_route_unavailable' => 'Kommission oprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'commission_edit_route_unavailable' => 'Kommission redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'commission_destroy_route_unavailable' => 'Kommission sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'commission_update_route_unavailable' => 'Kommission opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'commission_store_route_unavailable' => 'Provision-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'commission_create_route_unavailable' => 'Provision-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'commission_edit_route_unavailable' => 'Provision-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'commission_destroy_route_unavailable' => 'Provision-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'commission_update_route_unavailable' => 'Provision-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'commission_store_route_unavailable' => 'Commission store route is unavailable. ' . self::DELEGATION_EN,
				'commission_create_route_unavailable' => 'Commission create route is unavailable. ' . self::DELEGATION_EN,
				'commission_edit_route_unavailable' => 'Commission edit route is unavailable. ' . self::DELEGATION_EN,
				'commission_destroy_route_unavailable' => 'Commission destroy route is unavailable. ' . self::DELEGATION_EN,
				'commission_update_route_unavailable' => 'Commission update route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'commission_store_route_unavailable' => 'La ruta de almacenamiento de comisión no está disponible. ' . self::DELEGATION_ES,
				'commission_create_route_unavailable' => 'La ruta de creación de comisión no está disponible. ' . self::DELEGATION_ES,
				'commission_edit_route_unavailable' => 'La ruta de edición de comisión no está disponible. ' . self::DELEGATION_ES,
				'commission_destroy_route_unavailable' => 'La ruta de eliminación de comisión no está disponible. ' . self::DELEGATION_ES,
				'commission_update_route_unavailable' => 'La ruta de actualización de comisión no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'commission_store_route_unavailable' => 'La route de stockage des commissions est indisponible. ' . self::DELEGATION_FR,
				'commission_create_route_unavailable' => 'La route de création de commission est indisponible. ' . self::DELEGATION_FR,
				'commission_edit_route_unavailable' => 'La route d\'édition de commission est indisponible. ' . self::DELEGATION_FR,
				'commission_destroy_route_unavailable' => 'La route de suppression de commission est indisponible. ' . self::DELEGATION_FR,
				'commission_update_route_unavailable' => 'La route de mise à jour de commission est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'commission_store_route_unavailable' => 'נתיב אחסון עמלה אינו זמין. ' . self::DELEGATION_HE,
				'commission_create_route_unavailable' => 'נתיב יצירת עמלה אינו זמין. ' . self::DELEGATION_HE,
				'commission_edit_route_unavailable' => 'נתיב עריכת עמלה אינו זמין. ' . self::DELEGATION_HE,
				'commission_destroy_route_unavailable' => 'נתיב מחיקת עמלה אינו זמין. ' . self::DELEGATION_HE,
				'commission_update_route_unavailable' => 'נתיב עדכון עמלה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'commission_store_route_unavailable' => 'La rotta di memorizzazione della commissione non è disponibile. ' . self::DELEGATION_IT,
				'commission_create_route_unavailable' => 'La rotta di creazione della commissione non è disponibile. ' . self::DELEGATION_IT,
				'commission_edit_route_unavailable' => 'La rotta di modifica della commissione non è disponibile. ' . self::DELEGATION_IT,
				'commission_destroy_route_unavailable' => 'La rotta di eliminazione della commissione non è disponibile. ' . self::DELEGATION_IT,
				'commission_update_route_unavailable' => 'La rotta di aggiornamento della commissione non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'commission_store_route_unavailable' => '手数料保存ルートは利用できません。' . self::DELEGATION_JA,
				'commission_create_route_unavailable' => '手数料作成ルートは利用できません。' . self::DELEGATION_JA,
				'commission_edit_route_unavailable' => '手数料編集ルートは利用できません。' . self::DELEGATION_JA,
				'commission_destroy_route_unavailable' => '手数料削除ルートは利用できません。' . self::DELEGATION_JA,
				'commission_update_route_unavailable' => '手数料更新ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'commission_store_route_unavailable' => 'Commissie opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'commission_create_route_unavailable' => 'Commissie aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'commission_edit_route_unavailable' => 'Commissie bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'commission_destroy_route_unavailable' => 'Commissie verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'commission_update_route_unavailable' => 'Commissie updateroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'commission_store_route_unavailable' => 'Trasa przechowywania prowizji jest niedostępna. ' . self::DELEGATION_PL,
				'commission_create_route_unavailable' => 'Trasa tworzenia prowizji jest niedostępna. ' . self::DELEGATION_PL,
				'commission_edit_route_unavailable' => 'Trasa edycji prowizji jest niedostępna. ' . self::DELEGATION_PL,
				'commission_destroy_route_unavailable' => 'Trasa usuwania prowizji jest niedostępna. ' . self::DELEGATION_PL,
				'commission_update_route_unavailable' => 'Trasa aktualizacji prowizji jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'commission_store_route_unavailable' => 'A rota de armazenamento de comissão não está disponível. ' . self::DELEGATION_PT,
				'commission_create_route_unavailable' => 'A rota de criação de comissão não está disponível. ' . self::DELEGATION_PT,
				'commission_edit_route_unavailable' => 'A rota de edição de comissão não está disponível. ' . self::DELEGATION_PT,
				'commission_destroy_route_unavailable' => 'A rota de exclusão de comissão não está disponível. ' . self::DELEGATION_PT,
				'commission_update_route_unavailable' => 'A rota de atualização de comissão não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'commission_store_route_unavailable' => 'A rota de armazenamento de comissão não está disponível. ' . self::DELEGATION_PTBR,
				'commission_create_route_unavailable' => 'A rota de criação de comissão não está disponível. ' . self::DELEGATION_PTBR,
				'commission_edit_route_unavailable' => 'A rota de edição de comissão não está disponível. ' . self::DELEGATION_PTBR,
				'commission_destroy_route_unavailable' => 'A rota de exclusão de comissão não está disponível. ' . self::DELEGATION_PTBR,
				'commission_update_route_unavailable' => 'A rota de atualização de comissão não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'commission_store_route_unavailable' => 'Маршрут хранения комиссии недоступен. ' . self::DELEGATION_RU,
				'commission_create_route_unavailable' => 'Маршрут создания комиссии недоступен. ' . self::DELEGATION_RU,
				'commission_edit_route_unavailable' => 'Маршрут редактирования комиссии недоступен. ' . self::DELEGATION_RU,
				'commission_destroy_route_unavailable' => 'Маршрут удаления комиссии недоступен. ' . self::DELEGATION_RU,
				'commission_update_route_unavailable' => 'Маршрут обновления комиссии недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'commission_store_route_unavailable' => 'Komisyon depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'commission_create_route_unavailable' => 'Komisyon oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'commission_edit_route_unavailable' => 'Komisyon düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'commission_destroy_route_unavailable' => 'Komisyon silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'commission_update_route_unavailable' => 'Komisyon güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'commission_store_route_unavailable' => '佣金存储路由不可用。' . self::DELEGATION_ZH,
				'commission_create_route_unavailable' => '佣金创建路由不可用。' . self::DELEGATION_ZH,
				'commission_edit_route_unavailable' => '佣金编辑路由不可用。' . self::DELEGATION_ZH,
				'commission_destroy_route_unavailable' => '佣金删除路由不可用。' . self::DELEGATION_ZH,
				'commission_update_route_unavailable' => '佣金更新路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CPL => [
			'ar' => ['complaint_index_route_unavailable' => 'مسار فهرس الشكاوى غير متاح. ' . self::DELEGATION_AR],
			'da' => ['complaint_index_route_unavailable' => 'Klageindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['complaint_index_route_unavailable' => 'Beschwerdeindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['complaint_index_route_unavailable' => 'Complaints index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['complaint_index_route_unavailable' => 'La ruta del índice de quejas no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['complaint_index_route_unavailable' => 'La route de l\'index des réclamations est indisponible. ' . self::DELEGATION_FR],
			'he' => ['complaint_index_route_unavailable' => 'נתיב אינדקס תלונות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['complaint_index_route_unavailable' => 'La rotta dell\'indice dei reclami non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['complaint_index_route_unavailable' => '苦情インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['complaint_index_route_unavailable' => 'Klachtenindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['complaint_index_route_unavailable' => 'Trasa indeksu skarg jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['complaint_index_route_unavailable' => 'A rota do índice de reclamações não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['complaint_index_route_unavailable' => 'A rota do índice de reclamações não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['complaint_index_route_unavailable' => 'Маршрут индекса жалоб недоступен. ' . self::DELEGATION_RU],
			'tr' => ['complaint_index_route_unavailable' => 'Şikayet indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['complaint_index_route_unavailable' => '投诉索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::CPN => [
			'ar' => [
				'coupon_index_route_unavailable' => 'مسار القسائم غير متاح. ' . self::DELEGATION_AR,
				'coupon_generate_ai_route_unavailable' => 'مسار إنشاء القسائم بالذكاء الاصطناعي غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'coupon_create_route_unavailable' => 'مسار إنشاء القسائم غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'coupon_edit_route_unavailable' => 'مسار تعديل القسائم غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'coupon_destroy_route_unavailable' => 'مسار حذف القسائم غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR
			],
			'da' => [
				'coupon_index_route_unavailable' => 'Kuponrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'coupon_generate_ai_route_unavailable' => 'AI-genereret kuponrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'coupon_create_route_unavailable' => 'Oprettelse af kuponrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'coupon_edit_route_unavailable' => 'Redigeringsrute for kuponer er ikke tilgængelig. ' . self::DELEGATION_DA,
				'coupon_destroy_route_unavailable' => 'Sletningsrute for kuponer er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'coupon_index_route_unavailable' => 'Gutschein-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'coupon_generate_ai_route_unavailable' => 'KI-Gutschein-Generierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'coupon_create_route_unavailable' => 'Gutschein-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'coupon_edit_route_unavailable' => 'Gutschein-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'coupon_destroy_route_unavailable' => 'Gutschein-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'coupon_index_route_unavailable' => 'Coupon route is unavailable. ' . self::DELEGATION_EN,
				'coupon_generate_ai_route_unavailable' => 'AI generate route is unavailable. ' . self::DELEGATION_EN,
				'coupon_create_route_unavailable' => 'Coupon create route is unavailable. ' . self::DELEGATION_EN,
				'coupon_edit_route_unavailable' => 'Coupon edit route is unavailable. ' . self::DELEGATION_EN,
				'coupon_destroy_route_unavailable' => 'Coupon delete route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'coupon_index_route_unavailable' => 'La ruta de cupones no está disponible. ' . self::DELEGATION_ES,
				'coupon_generate_ai_route_unavailable' => 'La ruta de generación de cupones por IA no está disponible. ' . self::DELEGATION_ES,
				'coupon_create_route_unavailable' => 'La ruta de creación de cupones no está disponible. ' . self::DELEGATION_ES,
				'coupon_edit_route_unavailable' => 'La ruta de edición de cupones no está disponible. ' . self::DELEGATION_ES,
				'coupon_destroy_route_unavailable' => 'La ruta de eliminación de cupones no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'coupon_index_route_unavailable' => 'La route des coupons n\'est pas disponible. ' . self::DELEGATION_FR,
				'coupon_generate_ai_route_unavailable' => 'La route de génération IA de coupons est indisponible. ' . self::DELEGATION_FR,
				'coupon_create_route_unavailable' => 'La route de création de coupons est indisponible. ' . self::DELEGATION_FR,
				'coupon_edit_route_unavailable' => 'La route d\'édition de coupons est indisponible. ' . self::DELEGATION_FR,
				'coupon_destroy_route_unavailable' => 'La route de suppression de coupons est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'coupon_index_route_unavailable' => 'נתיב קופונים אינו זמין. ' . self::DELEGATION_HE,
				'coupon_generate_ai_route_unavailable' => 'נתיב יצירת קופונים באמצעות בינה מלאכותית אינו זמין. ' . self::DELEGATION_HE,
				'coupon_create_route_unavailable' => 'נתיב יצירת קופונים אינו זמין. ' . self::DELEGATION_HE,
				'coupon_edit_route_unavailable' => 'נתיב עריכת קופונים אינו זמין. ' . self::DELEGATION_HE,
				'coupon_destroy_route_unavailable' => 'נתיב מחיקת קופונים אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'coupon_index_route_unavailable' => 'La rotta dei coupon non è disponibile. ' . self::DELEGATION_IT,
				'coupon_generate_ai_route_unavailable' => 'La rotta di generazione IA dei coupon non è disponibile. ' . self::DELEGATION_IT,
				'coupon_create_route_unavailable' => 'La rotta di creazione dei coupon non è disponibile. ' . self::DELEGATION_IT,
				'coupon_edit_route_unavailable' => 'La rotta di modifica dei coupon non è disponibile. ' . self::DELEGATION_IT,
				'coupon_destroy_route_unavailable' => 'La rotta di eliminazione dei coupon non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'coupon_index_route_unavailable' => 'クーポンルートは利用できません。' . self::DELEGATION_JA,
				'coupon_generate_ai_route_unavailable' => 'AI生成クーポンルートは利用できません。' . self::DELEGATION_JA,
				'coupon_create_route_unavailable' => 'クーポン作成ルートは利用できません。' . self::DELEGATION_JA,
				'coupon_edit_route_unavailable' => 'クーポン編集ルートは利用できません。' . self::DELEGATION_JA,
				'coupon_destroy_route_unavailable' => 'クーポン削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'coupon_index_route_unavailable' => 'Couponroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'coupon_generate_ai_route_unavailable' => 'AI-generatie couponroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'coupon_create_route_unavailable' => 'Coupon aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'coupon_edit_route_unavailable' => 'Coupon bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'coupon_destroy_route_unavailable' => 'Coupon verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'coupon_index_route_unavailable' => 'Trasa kuponów jest niedostępna. ' . self::DELEGATION_PL,
				'coupon_generate_ai_route_unavailable' => 'Trasa generowania kuponów AI jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'coupon_create_route_unavailable' => 'Trasa tworzenia kuponów jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'coupon_edit_route_unavailable' => 'Trasa edycji kuponów jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'coupon_destroy_route_unavailable' => 'Trasa usuwania kuponów jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL
			],
			'pt' => [
				'coupon_index_route_unavailable' => 'A rota de cupons não está disponível. ' . self::DELEGATION_PT,
				'coupon_generate_ai_route_unavailable' => 'A rota de geração de cupons por IA não está disponível. ' . self::DELEGATION_PT,
				'coupon_create_route_unavailable' => 'A rota de criação de cupons não está disponível. ' . self::DELEGATION_PT,
				'coupon_edit_route_unavailable' => 'A rota de edição de cupons não está disponível. ' . self::DELEGATION_PT,
				'coupon_destroy_route_unavailable' => 'A rota de exclusão de cupons não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'coupon_index_route_unavailable' => 'A rota de cupons não está disponível. ' . self::DELEGATION_PTBR,
				'coupon_generate_ai_route_unavailable' => 'A rota de geração de cupons por IA não está disponível. ' . self::DELEGATION_PTBR,
				'coupon_create_route_unavailable' => 'A rota de criação de cupons não está disponível. ' . self::DELEGATION_PTBR,
				'coupon_edit_route_unavailable' => 'A rota de edição de cupons não está disponível. ' . self::DELEGATION_PTBR,
				'coupon_destroy_route_unavailable' => 'A rota de exclusão de cupons não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'coupon_index_route_unavailable' => 'Маршрут купонов недоступен. ' . self::DELEGATION_RU,
				'coupon_generate_ai_route_unavailable' => 'Маршрут генерации купонов ИИ недоступен. ' . self::DELEGATION_RU,
				'coupon_create_route_unavailable' => 'Маршрут создания купонов недоступен. ' . self::DELEGATION_RU,
				'coupon_edit_route_unavailable' => 'Маршрут редактирования купонов недоступен. ' . self::DELEGATION_RU,
				'coupon_destroy_route_unavailable' => 'Маршрут удаления купонов недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'coupon_index_route_unavailable' => 'Kupon rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'coupon_generate_ai_route_unavailable' => 'AI kupon oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'coupon_create_route_unavailable' => 'Kupon oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'coupon_edit_route_unavailable' => 'Kupon düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'coupon_destroy_route_unavailable' => 'Kupon silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'coupon_index_route_unavailable' => '优惠券路由不可用。' . self::DELEGATION_ZH,
				'coupon_generate_ai_route_unavailable' => 'AI生成优惠券路由不可用。' . self::DELEGATION_ZH,
				'coupon_create_route_unavailable' => '优惠券创建路由不可用。' . self::DELEGATION_ZH,
				'coupon_edit_route_unavailable' => '优惠券编辑路由不可用。' . self::DELEGATION_ZH,
				'coupon_destroy_route_unavailable' => '优惠券删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CPN_PL => [
			'ar' => [
				'company_policy_index_unavailable' => 'مسار سياسة الشركة غير متاح. ' . self::DELEGATION_AR,
				'company_policy_store_route_unavailable' => 'مسار تخزين سياسة الشركة غير متاح. ' . self::DELEGATION_AR,
				'company_policy_update_route_unavailable' => 'مسار تحديث سياسة الشركة غير متاح. ' . self::DELEGATION_AR,
				'company_policy_create_route_unavailable' => 'مسار إنشاء سياسة الشركة غير متاح. ' . self::DELEGATION_AR,
				'company_policy_edit_route_unavailable' => 'مسار تعديل سياسة الشركة غير متاح. ' . self::DELEGATION_AR,
				'company_policy_destroy_route_unavailable' => 'مسار حذف سياسة الشركة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'company_policy_index_unavailable' => 'Virksomhedspolitik rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'company_policy_store_route_unavailable' => 'Virksomhedspolitik lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'company_policy_update_route_unavailable' => 'Virksomhedspolitik opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'company_policy_create_route_unavailable' => 'Opret virksomhedspolitik rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'company_policy_edit_route_unavailable' => 'Virksomhedspolitik redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'company_policy_destroy_route_unavailable' => 'Virksomhedspolitik sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'company_policy_index_unavailable' => 'Firmenrichtlinien-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'company_policy_store_route_unavailable' => 'Firmenrichtlinien-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'company_policy_update_route_unavailable' => 'Firmenrichtlinien-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'company_policy_create_route_unavailable' => 'Firmenrichtlinien-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'company_policy_edit_route_unavailable' => 'Firmenrichtlinien-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'company_policy_destroy_route_unavailable' => 'Firmenrichtlinien-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'company_policy_index_unavailable' => 'Company policy route is unavailable. ' . self::DELEGATION_EN,
				'company_policy_store_route_unavailable' => 'Company Policy store route is unavailable. ' . self::DELEGATION_EN,
				'company_policy_update_route_unavailable' => 'Company Policy update route is unavailable. ' . self::DELEGATION_EN,
				'company_policy_create_route_unavailable' => 'Create Company Policy route is unavailable. ' . self::DELEGATION_EN,
				'company_policy_edit_route_unavailable' => 'Edit Company Policy route is unavailable. ' . self::DELEGATION_EN,
				'company_policy_destroy_route_unavailable' => 'Delete Company Policy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'company_policy_index_unavailable' => 'La ruta de política de la empresa no está disponible. ' . self::DELEGATION_ES,
				'company_policy_store_route_unavailable' => 'La ruta de almacenamiento de política de empresa no está disponible. ' . self::DELEGATION_ES,
				'company_policy_update_route_unavailable' => 'La ruta de actualización de política de empresa no está disponible. ' . self::DELEGATION_ES,
				'company_policy_create_route_unavailable' => 'La ruta de creación de política de empresa no está disponible. ' . self::DELEGATION_ES,
				'company_policy_edit_route_unavailable' => 'La ruta de edición de política de empresa no está disponible. ' . self::DELEGATION_ES,
				'company_policy_destroy_route_unavailable' => 'La ruta de eliminación de política de empresa no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'company_policy_index_unavailable' => 'La route de la politique de l\'entreprise est indisponible. ' . self::DELEGATION_FR,
				'company_policy_store_route_unavailable' => 'La route de stockage de la politique d\'entreprise est indisponible. ' . self::DELEGATION_FR,
				'company_policy_update_route_unavailable' => 'La route de mise à jour de la politique d\'entreprise est indisponible. ' . self::DELEGATION_FR,
				'company_policy_create_route_unavailable' => 'La route de création de politique d\'entreprise est indisponible. ' . self::DELEGATION_FR,
				'company_policy_edit_route_unavailable' => 'La route d\'édition de la politique d\'entreprise est indisponible. ' . self::DELEGATION_FR,
				'company_policy_destroy_route_unavailable' => 'La route de suppression de la politique d\'entreprise est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'company_policy_index_unavailable' => 'נתיב מדיניות החברה אינו זמין. ' . self::DELEGATION_HE,
				'company_policy_store_route_unavailable' => 'נתיב אחסון מדיניות החברה אינו זמין. ' . self::DELEGATION_HE,
				'company_policy_update_route_unavailable' => 'נתיב עדכון מדיניות החברה אינו זמין. ' . self::DELEGATION_HE,
				'company_policy_create_route_unavailable' => 'נתיב יצירת מדיניות החברה אינו זמין. ' . self::DELEGATION_HE,
				'company_policy_edit_route_unavailable' => 'נתיב עריכת מדיניות החברה אינו זמין. ' . self::DELEGATION_HE,
				'company_policy_destroy_route_unavailable' => 'נתיב מחיקת מדיניות החברה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'company_policy_index_unavailable' => 'La rotta della politica aziendale non è disponibile. ' . self::DELEGATION_IT,
				'company_policy_store_route_unavailable' => 'La rotta di archiviazione della politica aziendale non è disponibile. ' . self::DELEGATION_IT,
				'company_policy_update_route_unavailable' => 'La rotta di aggiornamento della politica aziendale non è disponibile. ' . self::DELEGATION_IT,
				'company_policy_create_route_unavailable' => 'La rotta di creazione della politica aziendale non è disponibile. ' . self::DELEGATION_IT,
				'company_policy_edit_route_unavailable' => 'La rotta di modifica della politica aziendale non è disponibile. ' . self::DELEGATION_IT,
				'company_policy_destroy_route_unavailable' => 'La rotta di eliminazione della politica aziendale non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'company_policy_index_unavailable' => '会社ポリシールートは利用できません。' . self::DELEGATION_JA,
				'company_policy_store_route_unavailable' => '会社ポリシー保存ルートは利用できません。' . self::DELEGATION_JA,
				'company_policy_update_route_unavailable' => '会社ポリシー更新ルートは利用できません。' . self::DELEGATION_JA,
				'company_policy_create_route_unavailable' => '会社ポリシー作成ルートは利用できません。' . self::DELEGATION_JA,
				'company_policy_edit_route_unavailable' => '会社ポリシー編集ルートは利用できません。' . self::DELEGATION_JA,
				'company_policy_destroy_route_unavailable' => '会社ポリシー削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'company_policy_index_unavailable' => 'Bedrijfsbeleidroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'company_policy_store_route_unavailable' => 'Bedrijfsbeleid opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'company_policy_update_route_unavailable' => 'Bedrijfsbeleid updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'company_policy_create_route_unavailable' => 'Bedrijfsbeleid aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'company_policy_edit_route_unavailable' => 'Bedrijfsbeleid bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'company_policy_destroy_route_unavailable' => 'Bedrijfsbeleid verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'company_policy_index_unavailable' => 'Trasa polityki firmowej jest niedostępna. ' . self::DELEGATION_PL,
				'company_policy_store_route_unavailable' => 'Trasa przechowywania polityki firmowej jest niedostępna. ' . self::DELEGATION_PL,
				'company_policy_update_route_unavailable' => 'Trasa aktualizacji polityki firmowej jest niedostępna. ' . self::DELEGATION_PL,
				'company_policy_create_route_unavailable' => 'Trasa tworzenia polityki firmowej jest niedostępna. ' . self::DELEGATION_PL,
				'company_policy_edit_route_unavailable' => 'Trasa edycji polityki firmowej jest niedostępna. ' . self::DELEGATION_PL,
				'company_policy_destroy_route_unavailable' => 'Trasa usuwania polityki firmowej jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'company_policy_index_unavailable' => 'A rota da política da empresa não está disponível. ' . self::DELEGATION_PT,
				'company_policy_store_route_unavailable' => 'A rota de armazenamento de política da empresa não está disponível. ' . self::DELEGATION_PT,
				'company_policy_update_route_unavailable' => 'A rota de atualização de política da empresa não está disponível. ' . self::DELEGATION_PT,
				'company_policy_create_route_unavailable' => 'A rota de criação de política da empresa não está disponível. ' . self::DELEGATION_PT,
				'company_policy_edit_route_unavailable' => 'A rota de edição de política da empresa não está disponível. ' . self::DELEGATION_PT,
				'company_policy_destroy_route_unavailable' => 'A rota de exclusão de política da empresa não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'company_policy_index_unavailable' => 'A rota de política da empresa não está disponível. ' . self::DELEGATION_PTBR,
				'company_policy_store_route_unavailable' => 'A rota de armazenamento de política da empresa não está disponível. ' . self::DELEGATION_PTBR,
				'company_policy_update_route_unavailable' => 'A rota de atualização de política da empresa não está disponível. ' . self::DELEGATION_PTBR,
				'company_policy_create_route_unavailable' => 'A rota de criação de política da empresa não está disponível. ' . self::DELEGATION_PTBR,
				'company_policy_edit_route_unavailable' => 'A rota de edição de política da empresa não está disponível. ' . self::DELEGATION_PTBR,
				'company_policy_destroy_route_unavailable' => 'A rota de exclusão de política da empresa não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'company_policy_index_unavailable' => 'Маршрут политики компании недоступен. ' . self::DELEGATION_RU,
				'company_policy_store_route_unavailable' => 'Маршрут сохранения политики компании недоступен. ' . self::DELEGATION_RU,
				'company_policy_update_route_unavailable' => 'Маршрут обновления политики компании недоступен. ' . self::DELEGATION_RU,
				'company_policy_create_route_unavailable' => 'Маршрут создания политики компании недоступен. ' . self::DELEGATION_RU,
				'company_policy_edit_route_unavailable' => 'Маршрут редактирования политики компании недоступен. ' . self::DELEGATION_RU,
				'company_policy_destroy_route_unavailable' => 'Маршрут удаления политики компании недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'company_policy_index_unavailable' => 'Şirket politikası rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'company_policy_store_route_unavailable' => 'Şirket politikası depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'company_policy_update_route_unavailable' => 'Şirket politikası güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'company_policy_create_route_unavailable' => 'Şirket politikası oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'company_policy_edit_route_unavailable' => 'Şirket politikası düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'company_policy_destroy_route_unavailable' => 'Şirket politikası silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'company_policy_index_unavailable' => '公司政策路由不可用。' . self::DELEGATION_ZH,
				'company_policy_store_route_unavailable' => '公司政策存储路由不可用。' . self::DELEGATION_ZH,
				'company_policy_update_route_unavailable' => '公司政策更新路由不可用。' . self::DELEGATION_ZH,
				'company_policy_create_route_unavailable' => '公司政策创建路由不可用。' . self::DELEGATION_ZH,
				'company_policy_edit_route_unavailable' => '公司政策编辑路由不可用。' . self::DELEGATION_ZH,
				'company_policy_destroy_route_unavailable' => '公司政策删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CPT => [
			'ar' => [
				'competency_store_route_unavailable' => 'مسار تخزين الكفاءة غير متاح. ' . self::DELEGATION_AR,
				'competency_update_route_unavailable' => 'مسار تحديث الكفاءة غير متاح. ' . self::DELEGATION_AR,
				'competency_create_route_unavailable' => 'مسار إنشاء الكفاءة غير متاح. ' . self::DELEGATION_AR,
				'competency_edit_route_unavailable' => 'مسار تعديل الكفاءة غير متاح. ' . self::DELEGATION_AR,
				'competency_destroy_route_unavailable' => 'مسار حذف الكفاءة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'competency_store_route_unavailable' => 'Kompetence lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'competency_update_route_unavailable' => 'Kompetence opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'competency_create_route_unavailable' => 'Kompetence oprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'competency_edit_route_unavailable' => 'Kompetence redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'competency_destroy_route_unavailable' => 'Kompetence sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'competency_store_route_unavailable' => 'Kompetenz-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'competency_update_route_unavailable' => 'Kompetenz-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'competency_create_route_unavailable' => 'Kompetenz-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'competency_edit_route_unavailable' => 'Kompetenz-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'competency_destroy_route_unavailable' => 'Kompetenz-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'competency_store_route_unavailable' => 'Competency store route is unavailable. ' . self::DELEGATION_EN,
				'competency_update_route_unavailable' => 'Competency update route is unavailable. ' . self::DELEGATION_EN,
				'competency_create_route_unavailable' => 'Competency create route is unavailable. ' . self::DELEGATION_EN,
				'competency_edit_route_unavailable' => 'Competency edit route is unavailable. ' . self::DELEGATION_EN,
				'competency_destroy_route_unavailable' => 'Competency delete route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'competency_store_route_unavailable' => 'La ruta de almacenamiento de competencia no está disponible. ' . self::DELEGATION_ES,
				'competency_update_route_unavailable' => 'La ruta de actualización de competencia no está disponible. ' . self::DELEGATION_ES,
				'competency_create_route_unavailable' => 'La ruta de creación de competencia no está disponible. ' . self::DELEGATION_ES,
				'competency_edit_route_unavailable' => 'La ruta de edición de competencia no está disponible. ' . self::DELEGATION_ES,
				'competency_destroy_route_unavailable' => 'La ruta de eliminación de competencia no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'competency_store_route_unavailable' => 'La route de stockage de compétence est indisponible. ' . self::DELEGATION_FR,
				'competency_update_route_unavailable' => 'La route de mise à jour de compétence est indisponible. ' . self::DELEGATION_FR,
				'competency_create_route_unavailable' => 'La route de création de compétence est indisponible. ' . self::DELEGATION_FR,
				'competency_edit_route_unavailable' => 'La route d\'édition de compétence est indisponible. ' . self::DELEGATION_FR,
				'competency_destroy_route_unavailable' => 'La route de suppression de compétence est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'competency_store_route_unavailable' => 'נתיב אחסון יכולת אינו זמין. ' . self::DELEGATION_HE,
				'competency_update_route_unavailable' => 'נתיב עדכון יכולת אינו זמין. ' . self::DELEGATION_HE,
				'competency_create_route_unavailable' => 'נתיב יצירת יכולת אינו זמין. ' . self::DELEGATION_HE,
				'competency_edit_route_unavailable' => 'נתיב עריכת יכולת אינו זמין. ' . self::DELEGATION_HE,
				'competency_destroy_route_unavailable' => 'נתיב מחיקת יכולת אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'competency_store_route_unavailable' => 'La rotta di archiviazione delle competenze non è disponibile. ' . self::DELEGATION_IT,
				'competency_update_route_unavailable' => 'La rotta di aggiornamento delle competenze non è disponibile. ' . self::DELEGATION_IT,
				'competency_create_route_unavailable' => 'La rotta di creazione delle competenze non è disponibile. ' . self::DELEGATION_IT,
				'competency_edit_route_unavailable' => 'La rotta di modifica delle competenze non è disponibile. ' . self::DELEGATION_IT,
				'competency_destroy_route_unavailable' => 'La rotta di eliminazione delle competenze non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'competency_store_route_unavailable' => 'コンピテンシー保存ルートは利用できません。' . self::DELEGATION_JA,
				'competency_update_route_unavailable' => 'コンピテンシー更新ルートは利用できません。' . self::DELEGATION_JA,
				'competency_create_route_unavailable' => 'コンピテンシー作成ルートは利用できません。' . self::DELEGATION_JA,
				'competency_edit_route_unavailable' => 'コンピテンシー編集ルートは利用できません。' . self::DELEGATION_JA,
				'competency_destroy_route_unavailable' => 'コンピテンシー削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'competency_store_route_unavailable' => 'Competentie opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'competency_update_route_unavailable' => 'Competentie updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'competency_create_route_unavailable' => 'Competentie aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'competency_edit_route_unavailable' => 'Competentie bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'competency_destroy_route_unavailable' => 'Competentie verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'competency_store_route_unavailable' => 'Trasa przechowywania kompetencji jest niedostępna. ' . self::DELEGATION_PL,
				'competency_update_route_unavailable' => 'Trasa aktualizacji kompetencji jest niedostępna. ' . self::DELEGATION_PL,
				'competency_create_route_unavailable' => 'Trasa tworzenia kompetencji jest niedostępna. ' . self::DELEGATION_PL,
				'competency_edit_route_unavailable' => 'Trasa edycji kompetencji jest niedostępna. ' . self::DELEGATION_PL,
				'competency_destroy_route_unavailable' => 'Trasa usuwania kompetencji jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'competency_store_route_unavailable' => 'A rota de armazenamento de competência não está disponível. ' . self::DELEGATION_PT,
				'competency_update_route_unavailable' => 'A rota de atualização de competência não está disponível. ' . self::DELEGATION_PT,
				'competency_create_route_unavailable' => 'A rota de criação de competência não está disponível. ' . self::DELEGATION_PT,
				'competency_edit_route_unavailable' => 'A rota de edição de competência não está disponível. ' . self::DELEGATION_PT,
				'competency_destroy_route_unavailable' => 'A rota de exclusão de competência não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'competency_store_route_unavailable' => 'A rota de armazenamento de competência não está disponível. ' . self::DELEGATION_PTBR,
				'competency_update_route_unavailable' => 'A rota de atualização de competência não está disponível. ' . self::DELEGATION_PTBR,
				'competency_create_route_unavailable' => 'A rota de criação de competência não está disponível. ' . self::DELEGATION_PTBR,
				'competency_edit_route_unavailable' => 'A rota de edição de competência não está disponível. ' . self::DELEGATION_PTBR,
				'competency_destroy_route_unavailable' => 'A rota de exclusão de competência não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'competency_store_route_unavailable' => 'Маршрут сохранения компетенции недоступен. ' . self::DELEGATION_RU,
				'competency_update_route_unavailable' => 'Маршрут обновления компетенции недоступен. ' . self::DELEGATION_RU,
				'competency_create_route_unavailable' => 'Маршрут создания компетенции недоступен. ' . self::DELEGATION_RU,
				'competency_edit_route_unavailable' => 'Маршрут редактирования компетенции недоступен. ' . self::DELEGATION_RU,
				'competency_destroy_route_unavailable' => 'Маршрут удаления компетенции недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'competency_store_route_unavailable' => 'Yeterlilik depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'competency_update_route_unavailable' => 'Yeterlilik güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'competency_create_route_unavailable' => 'Yeterlilik oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'competency_edit_route_unavailable' => 'Yeterlilik düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'competency_destroy_route_unavailable' => 'Yeterlilik silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'competency_store_route_unavailable' => '能力存储路由不可用。' . self::DELEGATION_ZH,
				'competency_update_route_unavailable' => '能力更新路由不可用。' . self::DELEGATION_ZH,
				'competency_create_route_unavailable' => '能力创建路由不可用。' . self::DELEGATION_ZH,
				'competency_edit_route_unavailable' => '能力编辑路由不可用。' . self::DELEGATION_ZH,
				'competency_destroy_route_unavailable' => '能力删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CPL => [
			'ar' => [
				'complaint_store_route_unavailable' => 'مسار تخزين الشكوى غير متاح. ' . self::DELEGATION_AR,
				'complaint_update_route_unavailable' => 'مسار تحديث الشكوى غير متاح. ' . self::DELEGATION_AR,
				'complaint_create_route_unavailable' => 'مسار إنشاء الشكوى غير متاح. ' . self::DELEGATION_AR,
				'complaint_edit_route_unavailable' => 'مسار تعديل الشكوى غير متاح. ' . self::DELEGATION_AR,
				'complaint_destroy_route_unavailable' => 'مسار حذف الشكوى غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'complaint_store_route_unavailable' => 'Klage lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'complaint_update_route_unavailable' => 'Klage opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'complaint_create_route_unavailable' => 'Opret klage rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'complaint_edit_route_unavailable' => 'Klage redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'complaint_destroy_route_unavailable' => 'Klage sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'complaint_store_route_unavailable' => 'Beschwerde-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'complaint_update_route_unavailable' => 'Beschwerde-Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'complaint_create_route_unavailable' => 'Beschwerde-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'complaint_edit_route_unavailable' => 'Beschwerde-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'complaint_destroy_route_unavailable' => 'Beschwerde-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'complaint_store_route_unavailable' => 'Complaint store route is unavailable. ' . self::DELEGATION_EN,
				'complaint_update_route_unavailable' => 'Complaint update route is unavailable. ' . self::DELEGATION_EN,
				'complaint_create_route_unavailable' => 'Create Complaint route is unavailable. ' . self::DELEGATION_EN,
				'complaint_edit_route_unavailable' => 'Edit Complaint route is unavailable. ' . self::DELEGATION_EN,
				'complaint_destroy_route_unavailable' => 'Delete Complaint route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'complaint_store_route_unavailable' => 'La ruta de almacenamiento de queja no está disponible. ' . self::DELEGATION_ES,
				'complaint_update_route_unavailable' => 'La ruta de actualización de queja no está disponible. ' . self::DELEGATION_ES,
				'complaint_create_route_unavailable' => 'La ruta de creación de queja no está disponible. ' . self::DELEGATION_ES,
				'complaint_edit_route_unavailable' => 'La ruta de edición de queja no está disponible. ' . self::DELEGATION_ES,
				'complaint_destroy_route_unavailable' => 'La ruta de eliminación de queja no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'complaint_store_route_unavailable' => 'La route de stockage de plainte est indisponible. ' . self::DELEGATION_FR,
				'complaint_update_route_unavailable' => 'La route de mise à jour de plainte est indisponible. ' . self::DELEGATION_FR,
				'complaint_create_route_unavailable' => 'La route de création de plainte est indisponible. ' . self::DELEGATION_FR,
				'complaint_edit_route_unavailable' => 'La route d\'édition de plainte est indisponible. ' . self::DELEGATION_FR,
				'complaint_destroy_route_unavailable' => 'La route de suppression de plainte est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'complaint_store_route_unavailable' => 'נתיב אחסון תלונה אינו זמין. ' . self::DELEGATION_HE,
				'complaint_update_route_unavailable' => 'נתיב עדכון תלונה אינו זמין. ' . self::DELEGATION_HE,
				'complaint_create_route_unavailable' => 'נתיב יצירת תלונה אינו זמין. ' . self::DELEGATION_HE,
				'complaint_edit_route_unavailable' => 'נתיב עריכת תלונה אינו זמין. ' . self::DELEGATION_HE,
				'complaint_destroy_route_unavailable' => 'נתיב מחיקת תלונה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'complaint_store_route_unavailable' => 'La rotta di archiviazione del reclamo non è disponibile. ' . self::DELEGATION_IT,
				'complaint_update_route_unavailable' => 'La rotta di aggiornamento del reclamo non è disponibile. ' . self::DELEGATION_IT,
				'complaint_create_route_unavailable' => 'La rotta di creazione del reclamo non è disponibile. ' . self::DELEGATION_IT,
				'complaint_edit_route_unavailable' => 'La rotta di modifica del reclamo non è disponibile. ' . self::DELEGATION_IT,
				'complaint_destroy_route_unavailable' => 'La rotta di eliminazione del reclamo non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'complaint_store_route_unavailable' => '苦情保存ルートは利用できません。' . self::DELEGATION_JA,
				'complaint_update_route_unavailable' => '苦情更新ルートは利用できません。' . self::DELEGATION_JA,
				'complaint_create_route_unavailable' => '苦情作成ルートは利用できません。' . self::DELEGATION_JA,
				'complaint_edit_route_unavailable' => '苦情編集ルートは利用できません。' . self::DELEGATION_JA,
				'complaint_destroy_route_unavailable' => '苦情削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'complaint_store_route_unavailable' => 'Klacht opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'complaint_update_route_unavailable' => 'Klacht updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'complaint_create_route_unavailable' => 'Klacht aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'complaint_edit_route_unavailable' => 'Klacht bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'complaint_destroy_route_unavailable' => 'Klacht verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'complaint_store_route_unavailable' => 'Trasa przechowywania skargi jest niedostępna. ' . self::DELEGATION_PL,
				'complaint_update_route_unavailable' => 'Trasa aktualizacji skargi jest niedostępna. ' . self::DELEGATION_PL,
				'complaint_create_route_unavailable' => 'Trasa tworzenia skargi jest niedostępna. ' . self::DELEGATION_PL,
				'complaint_edit_route_unavailable' => 'Trasa edycji skargi jest niedostępna. ' . self::DELEGATION_PL,
				'complaint_destroy_route_unavailable' => 'Trasa usuwania skargi jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'complaint_store_route_unavailable' => 'A rota de armazenamento de reclamação não está disponível. ' . self::DELEGATION_PT,
				'complaint_update_route_unavailable' => 'A rota de atualização de reclamação não está disponível. ' . self::DELEGATION_PT,
				'complaint_create_route_unavailable' => 'A rota de criação de reclamação não está disponível. ' . self::DELEGATION_PT,
				'complaint_edit_route_unavailable' => 'A rota de edição de reclamação não está disponível. ' . self::DELEGATION_PT,
				'complaint_destroy_route_unavailable' => 'A rota de exclusão de reclamação não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'complaint_store_route_unavailable' => 'A rota de armazenamento de reclamação não está disponível. ' . self::DELEGATION_PTBR,
				'complaint_update_route_unavailable' => 'A rota de atualização de reclamação não está disponível. ' . self::DELEGATION_PTBR,
				'complaint_create_route_unavailable' => 'A rota de criação de reclamação não está disponível. ' . self::DELEGATION_PTBR,
				'complaint_edit_route_unavailable' => 'A rota de edição de reclamação não está disponível. ' . self::DELEGATION_PTBR,
				'complaint_destroy_route_unavailable' => 'A rota de exclusão de reclamação não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'complaint_store_route_unavailable' => 'Маршрут сохранения жалобы недоступен. ' . self::DELEGATION_RU,
				'complaint_update_route_unavailable' => 'Маршрут обновления жалобы недоступен. ' . self::DELEGATION_RU,
				'complaint_create_route_unavailable' => 'Маршрут создания жалобы недоступен. ' . self::DELEGATION_RU,
				'complaint_edit_route_unavailable' => 'Маршрут редактирования жалобы недоступен. ' . self::DELEGATION_RU,
				'complaint_destroy_route_unavailable' => 'Маршрут удаления жалобы недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'complaint_store_route_unavailable' => 'Şikayet depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'complaint_update_route_unavailable' => 'Şikayet güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'complaint_create_route_unavailable' => 'Şikayet oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'complaint_edit_route_unavailable' => 'Şikayet düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'complaint_destroy_route_unavailable' => 'Şikayet silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'complaint_store_route_unavailable' => '投诉存储路由不可用。' . self::DELEGATION_ZH,
				'complaint_update_route_unavailable' => '投诉更新路由不可用。' . self::DELEGATION_ZH,
				'complaint_create_route_unavailable' => '投诉创建路由不可用。' . self::DELEGATION_ZH,
				'complaint_edit_route_unavailable' => '投诉编辑路由不可用。' . self::DELEGATION_ZH,
				'complaint_destroy_route_unavailable' => '投诉删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CRD_NT => [
			'ar' => ['credit_notes_route_unavailable' => 'مسار الإشعارات الدائنة غير متاح. ' . self::DELEGATION_AR],
			'da' => ['credit_notes_route_unavailable' => 'Kreditnotarute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['credit_notes_route_unavailable' => 'Gutschriften-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['credit_notes_route_unavailable' => 'Credit Notes route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['credit_notes_route_unavailable' => 'La ruta de notas de crédito no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['credit_notes_route_unavailable' => 'La route des notes de crédit est indisponible. ' . self::DELEGATION_FR],
			'he' => ['credit_notes_route_unavailable' => 'נתיב חשבוניות זיכוי אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['credit_notes_route_unavailable' => 'La rotta delle note di credito non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['credit_notes_route_unavailable' => 'クレジットノートルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['credit_notes_route_unavailable' => 'Creditnotaroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['credit_notes_route_unavailable' => 'Trasa not kredytowych jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['credit_notes_route_unavailable' => 'A rota de notas de crédito não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['credit_notes_route_unavailable' => 'A rota de notas de crédito não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['credit_notes_route_unavailable' => 'Маршрут кредит-нот недоступен. ' . self::DELEGATION_RU],
			'tr' => ['credit_notes_route_unavailable' => 'Kredi notası rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['credit_notes_route_unavailable' => '信用票据路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::CRR => [
			'ar' => [
				'career_index_route_unavailable' => 'مسار المسارات الوظيفية غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'career_index_route_unavailable' => 'Karriererute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'career_index_route_unavailable' => 'Karriere-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'career_index_route_unavailable' => 'Career route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'career_index_route_unavailable' => 'La ruta de carrera profesional no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'career_index_route_unavailable' => 'La route de carrière est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'career_index_route_unavailable' => 'נתיב קריירה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'career_index_route_unavailable' => 'La rotta della carriera non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'career_index_route_unavailable' => 'キャリアルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'career_index_route_unavailable' => 'Carrièreroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'career_index_route_unavailable' => 'Trasa kariery jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'career_index_route_unavailable' => 'A rota de carreira não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'career_index_route_unavailable' => 'A rota de carreira não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'career_index_route_unavailable' => 'Маршрут карьеры недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'career_index_route_unavailable' => 'Kariyer rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'career_index_route_unavailable' => '职业路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CST => [
			'ar' => [
				'customer_index_route_unavailable' => 'مسار العملاء غير متاح. ' . self::DELEGATION_AR,
				'customers_import_route_unavailable' => 'مسار استيراد CSV للعملاء غير متاح. ' . self::DELEGATION_AR,
				'customers_export_route_unavailable' => 'مسار تصدير العملاء غير متاح. ' . self::DELEGATION_AR,
				'customers_create_route_unavailable' => 'مسار إنشاء العميل غير متاح. ' . self::DELEGATION_AR,
				'customers_show_route_unavailable' => 'مسار عرض العميل غير متاح. ' . self::DELEGATION_AR,
				'customers_edit_route_unavailable' => 'مسار تعديل العميل غير متاح. ' . self::DELEGATION_AR,
				'customers_destroy_route_unavailable' => 'مسار حذف العميل غير متاح. ' . self::DELEGATION_AR,
				'customers_transaction_route_unavailable' => 'مسار معاملة العميل غير متاح. ' . self::DELEGATION_AR,
				'payment_route_unavailable' => 'مسار الدفع غير متاح. ' . self::DELEGATION_AR,
				'pay_with_bank_route_unavailable' => 'مسار الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_with_stripe_route_unavailable' => 'مسار الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_with_paypal_route_unavailable' => 'مسار الدفع عبر PayPal غير متاح. ' . self::DELEGATION_AR,
				'payment_with_paystack_route_unavailable' => 'مسار الدفع عبر Paystack غير متاح. ' . self::DELEGATION_AR,
				'payment_with_flutterwave_route_unavailable' => 'مسار الدفع عبر Flutterwave غير متاح. ' . self::DELEGATION_AR,
				'payment_with_razorpay_route_unavailable' => 'مسار الدفع عبر Razorpay غير متاح. ' . self::DELEGATION_AR,
				'payment_with_mercado_route_unavailable' => 'مسار الدفع عبر Mercado غير متاح. ' . self::DELEGATION_AR,
				'payment_with_paytm_route_unavailable' => 'مسار الدفع عبر Paytm غير متاح. ' . self::DELEGATION_AR,
				'payment_with_mollie_route_unavailable' => 'مسار الدفع عبر Mollie غير متاح. ' . self::DELEGATION_AR,
				'payment_with_skrill_route_unavailable' => 'مسار الدفع عبر Skrill غير متاح. ' . self::DELEGATION_AR,
				'payment_with_coingate_route_unavailable' => 'مسار الدفع عبر Coingate غير متاح. ' . self::DELEGATION_AR,
				'payment_with_paymentwall_route_unavailable' => 'مسار الدفع عبر Paymentwall غير متاح. ' . self::DELEGATION_AR,
				'payment_with_toyyibpay_route_unavailable' => 'مسار الدفع عبر ToyyibPay غير متاح. ' . self::DELEGATION_AR,
				'payment_with_payfast_route_unavailable' => 'مسار الدفع عبر Payfast غير متاح. ' . self::DELEGATION_AR,
				'payment_with_iyzipay_route_unavailable' => 'مسار الدفع عبر Iyzipay غير متاح. ' . self::DELEGATION_AR,
				'payment_with_sspay_route_unavailable' => 'مسار الدفع عبر SSPay غير متاح. ' . self::DELEGATION_AR,
				'payment_with_paytab_route_unavailable' => 'مسار الدفع عبر PayTab غير متاح. ' . self::DELEGATION_AR,
				'payment_with_cashfree_route_unavailable' => 'مسار الدفع عبر Cashfree غير متاح. ' . self::DELEGATION_AR,
				'payment_with_aamarpay_route_unavailable' => 'مسار الدفع عبر Aamarpay غير متاح. ' . self::DELEGATION_AR,
				'payment_with_yookassa_route_unavailable' => 'مسار الدفع عبر YooKassa غير متاح. ' . self::DELEGATION_AR,
				'payment_with_midtrans_route_unavailable' => 'مسار الدفع عبر Midtrans غير متاح. ' . self::DELEGATION_AR,
				'payment_with_xendit_route_unavailable' => 'مسار الدفع عبر Xendit غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'customer_index_route_unavailable' => 'Kunderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_import_route_unavailable' => 'Kunde CSV-importrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_export_route_unavailable' => 'Kunde eksportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_create_route_unavailable' => 'Kundeoprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_show_route_unavailable' => 'Kundevisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_edit_route_unavailable' => 'Kunderedigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_destroy_route_unavailable' => 'Kundesletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'customers_transaction_route_unavailable' => 'Kundetransaktionsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_route_unavailable' => 'Betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pay_with_bank_route_unavailable' => 'Betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_stripe_route_unavailable' => 'Betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_paypal_route_unavailable' => 'Betaling med PayPal-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_paystack_route_unavailable' => 'Betaling med Paystack-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_flutterwave_route_unavailable' => 'Betaling med Flutterwave-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_razorpay_route_unavailable' => 'Betaling med Razorpay-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_mercado_route_unavailable' => 'Betaling med Mercado-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_paytm_route_unavailable' => 'Betaling med Paytm-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_mollie_route_unavailable' => 'Betaling med Mollie-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_skrill_route_unavailable' => 'Betaling med Skrill-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_coingate_route_unavailable' => 'Betaling med Coingate-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_paymentwall_route_unavailable' => 'Betaling med Paymentwall-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_toyyibpay_route_unavailable' => 'Betaling med ToyyibPay-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_payfast_route_unavailable' => 'Betaling med Payfast-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_iyzipay_route_unavailable' => 'Betaling med Iyzipay-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_sspay_route_unavailable' => 'Betaling med SSPay-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_paytab_route_unavailable' => 'Betaling med PayTab-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_cashfree_route_unavailable' => 'Betaling med Cashfree-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_aamarpay_route_unavailable' => 'Betaling med Aamarpay-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_yookassa_route_unavailable' => 'Betaling med YooKassa-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_midtrans_route_unavailable' => 'Betaling med Midtrans-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_xendit_route_unavailable' => 'Betaling med Xendit-rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'customer_index_route_unavailable' => 'Kunden-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_import_route_unavailable' => 'Kunden-CSV-Import-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_export_route_unavailable' => 'Kundenexport-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_create_route_unavailable' => 'Kundenerstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_show_route_unavailable' => 'Kundenanzeige-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_edit_route_unavailable' => 'Kundenbearbeitungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_destroy_route_unavailable' => 'Kundenlöschungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'customers_transaction_route_unavailable' => 'Kundentransaktions-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_route_unavailable' => 'Zahlungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pay_with_bank_route_unavailable' => 'Zahlungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_stripe_route_unavailable' => 'Zahlungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_paypal_route_unavailable' => 'Zahlung mit PayPal-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_paystack_route_unavailable' => 'Zahlung mit Paystack-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_flutterwave_route_unavailable' => 'Zahlung mit Flutterwave-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_razorpay_route_unavailable' => 'Zahlung mit Razorpay-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_mercado_route_unavailable' => 'Zahlung mit Mercado-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_paytm_route_unavailable' => 'Zahlung mit Paytm-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_mollie_route_unavailable' => 'Zahlung mit Mollie-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_skrill_route_unavailable' => 'Zahlung mit Skrill-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_coingate_route_unavailable' => 'Zahlung mit Coingate-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_paymentwall_route_unavailable' => 'Zahlung mit Paymentwall-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_toyyibpay_route_unavailable' => 'Zahlung mit ToyyibPay-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_payfast_route_unavailable' => 'Zahlung mit Payfast-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_iyzipay_route_unavailable' => 'Zahlung mit Iyzipay-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_sspay_route_unavailable' => 'Zahlung mit SSPay-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_paytab_route_unavailable' => 'Zahlung mit PayTab-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_cashfree_route_unavailable' => 'Zahlung mit Cashfree-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_aamarpay_route_unavailable' => 'Zahlung mit Aamarpay-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_yookassa_route_unavailable' => 'Zahlung mit YooKassa-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_midtrans_route_unavailable' => 'Zahlung mit Midtrans-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_xendit_route_unavailable' => 'Zahlung mit Xendit-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'customer_index_route_unavailable' => 'Customer route is unavailable. ' . self::DELEGATION_EN,
				'customers_import_route_unavailable' => 'Customer CSV import route is unavailable. ' . self::DELEGATION_EN,
				'customers_export_route_unavailable' => 'Customer export route is unavailable. ' . self::DELEGATION_EN,
				'customers_create_route_unavailable' => 'Customer create route is unavailable. ' . self::DELEGATION_EN,
				'customers_show_route_unavailable' => 'Customer show route is unavailable. ' . self::DELEGATION_EN,
				'customers_edit_route_unavailable' => 'Customer edit route is unavailable. ' . self::DELEGATION_EN,
				'customers_destroy_route_unavailable' => 'Delete customer route is unavailable. ' . self::DELEGATION_EN,
				'customers_transaction_route_unavailable' => 'Customer transaction route is unavailable. ' . self::DELEGATION_EN,
				'payment_route_unavailable' => 'Payment route is unavailable. ' . self::DELEGATION_EN,
				'pay_with_bank_route_unavailable' => 'Payment route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_stripe_route_unavailable' => 'Payment route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_paypal_route_unavailable' => 'Payment with PayPal route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_paystack_route_unavailable' => 'Payment with Paystack route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_flutterwave_route_unavailable' => 'Payment with Flutterwave route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_razorpay_route_unavailable' => 'Payment with Razorpay route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_mercado_route_unavailable' => 'Payment with Mercado route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_paytm_route_unavailable' => 'Payment with Paytm route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_mollie_route_unavailable' => 'Payment with Mollie route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_skrill_route_unavailable' => 'Payment with Skrill route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_coingate_route_unavailable' => 'Payment with Coingate route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_paymentwall_route_unavailable' => 'Payment with Paymentwall route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_toyyibpay_route_unavailable' => 'Payment with ToyyibPay route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_payfast_route_unavailable' => 'Payment with Payfast route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_iyzipay_route_unavailable' => 'Payment with Iyzipay route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_sspay_route_unavailable' => 'Payment with SSPay route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_paytab_route_unavailable' => 'Payment with PayTab route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_cashfree_route_unavailable' => 'Payment with Cashfree route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_aamarpay_route_unavailable' => 'Payment with Aamarpay route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_yookassa_route_unavailable' => 'Payment with YooKassa route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_midtrans_route_unavailable' => 'Payment with Midtrans route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_xendit_route_unavailable' => 'Payment with Xendit route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'customer_index_route_unavailable' => 'La ruta de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_import_route_unavailable' => 'La ruta de importación CSV de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_export_route_unavailable' => 'La ruta de exportación de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_create_route_unavailable' => 'La ruta de creación de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_show_route_unavailable' => 'La ruta de visualización de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_edit_route_unavailable' => 'La ruta de edición de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_destroy_route_unavailable' => 'La ruta de eliminación de clientes no está disponible. ' . self::DELEGATION_ES,
				'customers_transaction_route_unavailable' => 'La ruta de transacción de clientes no está disponible. ' . self::DELEGATION_ES,
				'payment_route_unavailable' => 'La ruta de pago no está disponible. ' . self::DELEGATION_ES,
				'pay_with_bank_route_unavailable' => 'La ruta de pago no está disponible. ' . self::DELEGATION_ES,
				'payment_with_stripe_route_unavailable' => 'La ruta de pago no está disponible. ' . self::DELEGATION_ES,
				'payment_with_paypal_route_unavailable' => 'La ruta de pago con PayPal no está disponible. ' . self::DELEGATION_ES,
				'payment_with_paystack_route_unavailable' => 'La ruta de pago con Paystack no está disponible. ' . self::DELEGATION_ES,
				'payment_with_flutterwave_route_unavailable' => 'La ruta de pago con Flutterwave no está disponible. ' . self::DELEGATION_ES,
				'payment_with_razorpay_route_unavailable' => 'La ruta de pago con Razorpay no está disponible. ' . self::DELEGATION_ES,
				'payment_with_mercado_route_unavailable' => 'La ruta de pago con Mercado no está disponible. ' . self::DELEGATION_ES,
				'payment_with_paytm_route_unavailable' => 'La ruta de pago con Paytm no está disponible. ' . self::DELEGATION_ES,
				'payment_with_mollie_route_unavailable' => 'La ruta de pago con Mollie no está disponible. ' . self::DELEGATION_ES,
				'payment_with_skrill_route_unavailable' => 'La ruta de pago con Skrill no está disponible. ' . self::DELEGATION_ES,
				'payment_with_coingate_route_unavailable' => 'La ruta de pago con Coingate no está disponible. ' . self::DELEGATION_ES,
				'payment_with_paymentwall_route_unavailable' => 'La ruta de pago con Paymentwall no está disponible. ' . self::DELEGATION_ES,
				'payment_with_toyyibpay_route_unavailable' => 'La ruta de pago con ToyyibPay no está disponible. ' . self::DELEGATION_ES,
				'payment_with_payfast_route_unavailable' => 'La ruta de pago con Payfast no está disponible. ' . self::DELEGATION_ES,
				'payment_with_iyzipay_route_unavailable' => 'La ruta de pago con Iyzipay no está disponible. ' . self::DELEGATION_ES,
				'payment_with_sspay_route_unavailable' => 'La ruta de pago con SSPay no está disponible. ' . self::DELEGATION_ES,
				'payment_with_paytab_route_unavailable' => 'La ruta de pago con PayTab no está disponible. ' . self::DELEGATION_ES,
				'payment_with_cashfree_route_unavailable' => 'La ruta de pago con Cashfree no está disponible. ' . self::DELEGATION_ES,
				'payment_with_aamarpay_route_unavailable' => 'La ruta de pago con Aamarpay no está disponible. ' . self::DELEGATION_ES,
				'payment_with_yookassa_route_unavailable' => 'La ruta de pago con YooKassa no está disponible. ' . self::DELEGATION_ES,
				'payment_with_midtrans_route_unavailable' => 'La ruta de pago con Midtrans no está disponible. ' . self::DELEGATION_ES,
				'payment_with_xendit_route_unavailable' => 'La ruta de pago con Xendit no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'customer_index_route_unavailable' => 'La route des clients est indisponible. ' . self::DELEGATION_FR,
				'customers_import_route_unavailable' => 'La route d\'importation CSV des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'customers_export_route_unavailable' => 'La route d\'exportation des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'customers_create_route_unavailable' => 'La route de création de client n\'est pas disponible. ' . self::DELEGATION_FR,
				'customers_show_route_unavailable' => 'La route d\'affichage des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'customers_edit_route_unavailable' => 'La route d\'édition des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'customers_destroy_route_unavailable' => 'La route de suppression des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'customers_transaction_route_unavailable' => 'La route de transaction des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'payment_route_unavailable' => 'La route de paiement n\'est pas disponible. ' . self::DELEGATION_FR,
				'pay_with_bank_route_unavailable' => 'La route de paiement est indisponible. ' . self::DELEGATION_FR,
				'payment_with_stripe_route_unavailable' => 'La route de paiement est indisponible. ' . self::DELEGATION_FR,
				'payment_with_paypal_route_unavailable' => 'La route de paiement avec PayPal est indisponible. ' . self::DELEGATION_FR,
				'payment_with_paystack_route_unavailable' => 'La route de paiement avec Paystack est indisponible. ' . self::DELEGATION_FR,
				'payment_with_flutterwave_route_unavailable' => 'La route de paiement avec Flutterwave est indisponible. ' . self::DELEGATION_FR,
				'payment_with_razorpay_route_unavailable' => 'La route de paiement avec Razorpay est indisponible. ' . self::DELEGATION_FR,
				'payment_with_mercado_route_unavailable' => 'La route de paiement avec Mercado est indisponible. ' . self::DELEGATION_FR,
				'payment_with_paytm_route_unavailable' => 'La route de paiement avec Paytm est indisponible. ' . self::DELEGATION_FR,
				'payment_with_mollie_route_unavailable' => 'La route de paiement avec Mollie est indisponible. ' . self::DELEGATION_FR,
				'payment_with_skrill_route_unavailable' => 'La route de paiement avec Skrill est indisponible. ' . self::DELEGATION_FR,
				'payment_with_coingate_route_unavailable' => 'La route de paiement avec Coingate est indisponible. ' . self::DELEGATION_FR,
				'payment_with_paymentwall_route_unavailable' => 'La route de paiement avec Paymentwall est indisponible. ' . self::DELEGATION_FR,
				'payment_with_toyyibpay_route_unavailable' => 'La route de paiement avec ToyyibPay est indisponible. ' . self::DELEGATION_FR,
				'payment_with_payfast_route_unavailable' => 'La route de paiement avec Payfast est indisponible. ' . self::DELEGATION_FR,
				'payment_with_iyzipay_route_unavailable' => 'La route de paiement avec Iyzipay est indisponible. ' . self::DELEGATION_FR,
				'payment_with_sspay_route_unavailable' => 'La route de paiement avec SSPay est indisponible. ' . self::DELEGATION_FR,
				'payment_with_paytab_route_unavailable' => 'La route de paiement avec PayTab est indisponible. ' . self::DELEGATION_FR,
				'payment_with_cashfree_route_unavailable' => 'La route de paiement avec Cashfree est indisponible. ' . self::DELEGATION_FR,
				'payment_with_aamarpay_route_unavailable' => 'La route de paiement avec Aamarpay est indisponible. ' . self::DELEGATION_FR,
				'payment_with_yookassa_route_unavailable' => 'La route de paiement avec YooKassa est indisponible. ' . self::DELEGATION_FR,
				'payment_with_midtrans_route_unavailable' => 'La route de paiement avec Midtrans est indisponible. ' . self::DELEGATION_FR,
				'payment_with_xendit_route_unavailable' => 'La route de paiement avec Xendit est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'customer_index_route_unavailable' => 'נתיב לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'customers_import_route_unavailable' => 'נתיב ייבוא CSV של לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'customers_export_route_unavailable' => 'נתיב ייצוא לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'customers_create_route_unavailable' => 'נתיב יצירת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'customers_show_route_unavailable' => 'נתיב הצגת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'customers_edit_route_unavailable' => 'נתיב עריכת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'customers_destroy_route_unavailable' => 'נתיב מחיקת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'customers_transaction_route_unavailable' => 'נתיב עסקת לקוח אינו זמין. ' . self::DELEGATION_HE,
				'payment_route_unavailable' => 'נתיב תשלום אינו זמין. ' . self::DELEGATION_HE,
				'pay_with_bank_route_unavailable' => 'מסלול תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_stripe_route_unavailable' => 'מסלול תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_paypal_route_unavailable' => 'מסלול תשלום עם PayPal אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_paystack_route_unavailable' => 'מסלול תשלום עם Paystack אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_flutterwave_route_unavailable' => 'מסלול תשלום עם Flutterwave אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_razorpay_route_unavailable' => 'מסלול תשלום עם Razorpay אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_mercado_route_unavailable' => 'מסלול תשלום עם Mercado אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_paytm_route_unavailable' => 'מסלול תשלום עם Paytm אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_mollie_route_unavailable' => 'מסלול תשלום עם Mollie אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_skrill_route_unavailable' => 'מסלול תשלום עם Skrill אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_coingate_route_unavailable' => 'מסלול תשלום עם Coingate אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_paymentwall_route_unavailable' => 'מסלול תשלום עם Paymentwall אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_toyyibpay_route_unavailable' => 'מסלול תשלום עם ToyyibPay אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_payfast_route_unavailable' => 'מסלול תשלום עם Payfast אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_iyzipay_route_unavailable' => 'מסלול תשלום עם Iyzipay אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_sspay_route_unavailable' => 'מסלול תשלום עם SSPay אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_paytab_route_unavailable' => 'מסלול תשלום עם PayTab אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_cashfree_route_unavailable' => 'מסלול תשלום עם Cashfree אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_aamarpay_route_unavailable' => 'מסלול תשלום עם Aamarpay אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_yookassa_route_unavailable' => 'מסלול תשלום עם YooKassa אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_midtrans_route_unavailable' => 'מסלול תשלום עם Midtrans אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_xendit_route_unavailable' => 'מסלול תשלום עם Xendit אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'customer_index_route_unavailable' => 'La rotta dei clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_import_route_unavailable' => 'La rotta di importazione CSV clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_export_route_unavailable' => 'La rotta di esportazione clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_create_route_unavailable' => 'La rotta di creazione clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_show_route_unavailable' => 'La rotta di visualizzazione clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_edit_route_unavailable' => 'La rotta di modifica clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_destroy_route_unavailable' => 'La rotta di eliminazione clienti non è disponibile. ' . self::DELEGATION_IT,
				'customers_transaction_route_unavailable' => 'La rotta di transazione clienti non è disponibile. ' . self::DELEGATION_IT,
				'payment_route_unavailable' => 'La rotta di pagamento non è disponibile. ' . self::DELEGATION_IT,
				'pay_with_bank_route_unavailable' => 'La rotta di pagamento non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_stripe_route_unavailable' => 'La rotta di pagamento non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_paypal_route_unavailable' => 'La rotta di pagamento con PayPal non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_paystack_route_unavailable' => 'La rotta di pagamento con Paystack non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_flutterwave_route_unavailable' => 'La rotta di pagamento con Flutterwave non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_razorpay_route_unavailable' => 'La rotta di pagamento con Razorpay non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_mercado_route_unavailable' => 'La rotta di pagamento con Mercado non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_paytm_route_unavailable' => 'La rotta di pagamento con Paytm non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_mollie_route_unavailable' => 'La rotta di pagamento con Mollie non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_skrill_route_unavailable' => 'La rotta di pagamento con Skrill non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_coingate_route_unavailable' => 'La rotta di pagamento con Coingate non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_paymentwall_route_unavailable' => 'La rotta di pagamento con Paymentwall non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_toyyibpay_route_unavailable' => 'La rotta di pagamento con ToyyibPay non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_payfast_route_unavailable' => 'La rotta di pagamento con Payfast non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_iyzipay_route_unavailable' => 'La rotta di pagamento con Iyzipay non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_sspay_route_unavailable' => 'La rotta di pagamento con SSPay non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_paytab_route_unavailable' => 'La rotta di pagamento con PayTab non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_cashfree_route_unavailable' => 'La rotta di pagamento con Cashfree non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_aamarpay_route_unavailable' => 'La rotta di pagamento con Aamarpay non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_yookassa_route_unavailable' => 'La rotta di pagamento con YooKassa non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_midtrans_route_unavailable' => 'La rotta di pagamento con Midtrans non è disponibile. ' . self::DELEGATION_IT,
				'payment_with_xendit_route_unavailable' => 'La rotta di pagamento con Xendit non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'customer_index_route_unavailable' => '顧客ルートは利用できません。' . self::DELEGATION_JA,
				'customers_import_route_unavailable' => '顧客CSVインポートルートは利用できません。' . self::DELEGATION_JA,
				'customers_export_route_unavailable' => '顧客エクスポートルートは利用できません。' . self::DELEGATION_JA,
				'customers_create_route_unavailable' => '顧客作成ルートは利用できません。' . self::DELEGATION_JA,
				'customers_show_route_unavailable' => '顧客表示ルートは利用できません。' . self::DELEGATION_JA,
				'customers_edit_route_unavailable' => '顧客編集ルートは利用できません。' . self::DELEGATION_JA,
				'customers_destroy_route_unavailable' => '顧客削除ルートは利用できません。' . self::DELEGATION_JA,
				'customers_transaction_route_unavailable' => '顧客取引ルートは利用できません。' . self::DELEGATION_JA,
				'payment_route_unavailable' => '支払いルートは利用できません。' . self::DELEGATION_JA,
				'pay_with_bank_route_unavailable' => '支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_stripe_route_unavailable' => '支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_paypal_route_unavailable' => 'PayPalによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_paystack_route_unavailable' => 'Paystackによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_flutterwave_route_unavailable' => 'Flutterwaveによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_razorpay_route_unavailable' => 'Razorpayによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_mercado_route_unavailable' => 'Mercadoによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_paytm_route_unavailable' => 'Paytmによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_mollie_route_unavailable' => 'Mollieによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_skrill_route_unavailable' => 'Skrillによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_coingate_route_unavailable' => 'Coingateによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_paymentwall_route_unavailable' => 'Paymentwallによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_toyyibpay_route_unavailable' => 'ToyyibPayによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_payfast_route_unavailable' => 'Payfastによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_iyzipay_route_unavailable' => 'Iyzipayによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_sspay_route_unavailable' => 'SSPayによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_paytab_route_unavailable' => 'PayTabによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_cashfree_route_unavailable' => 'Cashfreeによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_aamarpay_route_unavailable' => 'Aamarpayによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_yookassa_route_unavailable' => 'YooKassaによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_midtrans_route_unavailable' => 'Midtransによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_xendit_route_unavailable' => 'Xenditによる支払いルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'customer_index_route_unavailable' => 'Klantenroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_import_route_unavailable' => 'Klant CSV-importroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_export_route_unavailable' => 'Klantexportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_create_route_unavailable' => 'Klantaanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_show_route_unavailable' => 'Klantweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_edit_route_unavailable' => 'Klantbewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_destroy_route_unavailable' => 'Klantverwijderingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'customers_transaction_route_unavailable' => 'Klanttransactieroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_route_unavailable' => 'Betalingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'pay_with_bank_route_unavailable' => 'Betalingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_stripe_route_unavailable' => 'Betalingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_paypal_route_unavailable' => 'Betalingsroute met PayPal is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_paystack_route_unavailable' => 'Betalingsroute met Paystack is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_flutterwave_route_unavailable' => 'Betalingsroute met Flutterwave is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_razorpay_route_unavailable' => 'Betalingsroute met Razorpay is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_mercado_route_unavailable' => 'Betalingsroute met Mercado is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_paytm_route_unavailable' => 'Betalingsroute met Paytm is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_mollie_route_unavailable' => 'Betalingsroute met Mollie is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_skrill_route_unavailable' => 'Betalingsroute met Skrill is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_coingate_route_unavailable' => 'Betalingsroute met Coingate is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_paymentwall_route_unavailable' => 'Betalingsroute met Paymentwall is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_toyyibpay_route_unavailable' => 'Betalingsroute met ToyyibPay is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_payfast_route_unavailable' => 'Betalingsroute met Payfast is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_iyzipay_route_unavailable' => 'Betalingsroute met Iyzipay is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_sspay_route_unavailable' => 'Betalingsroute met SSPay is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_paytab_route_unavailable' => 'Betalingsroute met PayTab is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_cashfree_route_unavailable' => 'Betalingsroute met Cashfree is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_aamarpay_route_unavailable' => 'Betalingsroute met Aamarpay is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_yookassa_route_unavailable' => 'Betalingsroute met YooKassa is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_midtrans_route_unavailable' => 'Betalingsroute met Midtrans is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_xendit_route_unavailable' => 'Betalingsroute met Xendit is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'customer_index_route_unavailable' => 'Trasa klientów jest niedostępna. ' . self::DELEGATION_PL,
				'customers_import_route_unavailable' => 'Trasa importu CSV klientów jest niedostępna. ' . self::DELEGATION_PL,
				'customers_export_route_unavailable' => 'Trasa eksportu klientów jest niedostępna. ' . self::DELEGATION_PL,
				'customers_create_route_unavailable' => 'Trasa tworzenia klienta jest niedostępna. ' . self::DELEGATION_PL,
				'customers_show_route_unavailable' => 'Trasa wyświetlania klienta jest niedostępna. ' . self::DELEGATION_PL,
				'customers_edit_route_unavailable' => 'Trasa edycji klienta jest niedostępna. ' . self::DELEGATION_PL,
				'customers_destroy_route_unavailable' => 'Trasa usuwania klienta jest niedostępna. ' . self::DELEGATION_PL,
				'customers_transaction_route_unavailable' => 'Trasa transakcji klienta jest niedostępna. ' . self::DELEGATION_PL,
				'payment_route_unavailable' => 'Trasa płatności jest niedostępna. ' . self::DELEGATION_PL,
				'pay_with_bank_route_unavailable' => 'Trasa płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_stripe_route_unavailable' => 'Trasa płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_paypal_route_unavailable' => 'Trasa płatności z PayPal jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_paystack_route_unavailable' => 'Trasa płatności z Paystack jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_flutterwave_route_unavailable' => 'Trasa płatności z Flutterwave jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_razorpay_route_unavailable' => 'Trasa płatności z Razorpay jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_mercado_route_unavailable' => 'Trasa płatności z Mercado jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_paytm_route_unavailable' => 'Trasa płatności z Paytm jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_mollie_route_unavailable' => 'Trasa płatności z Mollie jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_skrill_route_unavailable' => 'Trasa płatności z Skrill jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_coingate_route_unavailable' => 'Trasa płatności z Coingate jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_paymentwall_route_unavailable' => 'Trasa płatności z Paymentwall jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_toyyibpay_route_unavailable' => 'Trasa płatności z ToyyibPay jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_payfast_route_unavailable' => 'Trasa płatności z Payfast jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_iyzipay_route_unavailable' => 'Trasa płatności z Iyzipay jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_sspay_route_unavailable' => 'Trasa płatności z SSPay jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_paytab_route_unavailable' => 'Trasa płatności z PayTab jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_cashfree_route_unavailable' => 'Trasa płatności z Cashfree jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_aamarpay_route_unavailable' => 'Trasa płatności z Aamarpay jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_yookassa_route_unavailable' => 'Trasa płatności z YooKassa jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_midtrans_route_unavailable' => 'Trasa płatności z Midtrans jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_xendit_route_unavailable' => 'Trasa płatności z Xendit jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'customer_index_route_unavailable' => 'A rota de clientes não está disponível. ' . self::DELEGATION_PT,
				'customers_import_route_unavailable' => 'A rota de importação CSV de clientes não está disponível. ' . self::DELEGATION_PT,
				'customers_export_route_unavailable' => 'A rota de exportação de clientes não está disponível. ' . self::DELEGATION_PT,
				'customers_create_route_unavailable' => 'A rota de criação de cliente não está disponível. ' . self::DELEGATION_PT,
				'customers_show_route_unavailable' => 'A rota de exibição de cliente não está disponível. ' . self::DELEGATION_PT,
				'customers_edit_route_unavailable' => 'A rota de edição de cliente não está disponível. ' . self::DELEGATION_PT,
				'customers_destroy_route_unavailable' => 'A rota de exclusão de cliente não está disponível. ' . self::DELEGATION_PT,
				'customers_transaction_route_unavailable' => 'A rota de transação do cliente não está disponível. ' . self::DELEGATION_PT,
				'payment_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PT,
				'pay_with_bank_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PT,
				'payment_with_stripe_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PT,
				'payment_with_paypal_route_unavailable' => 'A rota de pagamento com PayPal não está disponível. ' . self::DELEGATION_PT,
				'payment_with_paystack_route_unavailable' => 'A rota de pagamento com Paystack não está disponível. ' . self::DELEGATION_PT,
				'payment_with_flutterwave_route_unavailable' => 'A rota de pagamento com Flutterwave não está disponível. ' . self::DELEGATION_PT,
				'payment_with_razorpay_route_unavailable' => 'A rota de pagamento com Razorpay não está disponível. ' . self::DELEGATION_PT,
				'payment_with_mercado_route_unavailable' => 'A rota de pagamento com Mercado não está disponível. ' . self::DELEGATION_PT,
				'payment_with_paytm_route_unavailable' => 'A rota de pagamento com Paytm não está disponível. ' . self::DELEGATION_PT,
				'payment_with_mollie_route_unavailable' => 'A rota de pagamento com Mollie não está disponível. ' . self::DELEGATION_PT,
				'payment_with_skrill_route_unavailable' => 'A rota de pagamento com Skrill não está disponível. ' . self::DELEGATION_PT,
				'payment_with_coingate_route_unavailable' => 'A rota de pagamento com Coingate não está disponível. ' . self::DELEGATION_PT,
				'payment_with_paymentwall_route_unavailable' => 'A rota de pagamento com Paymentwall não está disponível. ' . self::DELEGATION_PT,
				'payment_with_toyyibpay_route_unavailable' => 'A rota de pagamento com ToyyibPay não está disponível. ' . self::DELEGATION_PT,
				'payment_with_payfast_route_unavailable' => 'A rota de pagamento com Payfast não está disponível. ' . self::DELEGATION_PT,
				'payment_with_iyzipay_route_unavailable' => 'A rota de pagamento com Iyzipay não está disponível. ' . self::DELEGATION_PT,
				'payment_with_sspay_route_unavailable' => 'A rota de pagamento com SSPay não está disponível. ' . self::DELEGATION_PT,
				'payment_with_paytab_route_unavailable' => 'A rota de pagamento com PayTab não está disponível. ' . self::DELEGATION_PT,
				'payment_with_cashfree_route_unavailable' => 'A rota de pagamento com Cashfree não está disponível. ' . self::DELEGATION_PT,
				'payment_with_aamarpay_route_unavailable' => 'A rota de pagamento com Aamarpay não está disponível. ' . self::DELEGATION_PT,
				'payment_with_yookassa_route_unavailable' => 'A rota de pagamento com YooKassa não está disponível. ' . self::DELEGATION_PT,
				'payment_with_midtrans_route_unavailable' => 'A rota de pagamento com Midtrans não está disponível. ' . self::DELEGATION_PT,
				'payment_with_xendit_route_unavailable' => 'A rota de pagamento com Xendit não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'customer_index_route_unavailable' => 'A rota de clientes não está disponível. ' . self::DELEGATION_PTBR,
				'customers_import_route_unavailable' => 'A rota de importação CSV de clientes não está disponível. ' . self::DELEGATION_PTBR,
				'customers_export_route_unavailable' => 'A rota de exportação de clientes não está disponível. ' . self::DELEGATION_PTBR,
				'customers_create_route_unavailable' => 'A rota de criação de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'customers_show_route_unavailable' => 'A rota de exibição de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'customers_edit_route_unavailable' => 'A rota de edição de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'customers_destroy_route_unavailable' => 'A rota de exclusão de cliente não está disponível. ' . self::DELEGATION_PTBR,
				'customers_transaction_route_unavailable' => 'A rota de transação do cliente não está disponível. ' . self::DELEGATION_PTBR,
				'payment_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'pay_with_bank_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_stripe_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_paypal_route_unavailable' => 'A rota de pagamento com PayPal não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_paystack_route_unavailable' => 'A rota de pagamento com Paystack não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_flutterwave_route_unavailable' => 'A rota de pagamento com Flutterwave não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_razorpay_route_unavailable' => 'A rota de pagamento com Razorpay não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_mercado_route_unavailable' => 'A rota de pagamento com Mercado não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_paytm_route_unavailable' => 'A rota de pagamento com Paytm não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_mollie_route_unavailable' => 'A rota de pagamento com Mollie não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_skrill_route_unavailable' => 'A rota de pagamento com Skrill não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_coingate_route_unavailable' => 'A rota de pagamento com Coingate não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_paymentwall_route_unavailable' => 'A rota de pagamento com Paymentwall não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_toyyibpay_route_unavailable' => 'A rota de pagamento com ToyyibPay não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_payfast_route_unavailable' => 'A rota de pagamento com Payfast não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_iyzipay_route_unavailable' => 'A rota de pagamento com Iyzipay não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_sspay_route_unavailable' => 'A rota de pagamento com SSPay não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_paytab_route_unavailable' => 'A rota de pagamento com PayTab não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_cashfree_route_unavailable' => 'A rota de pagamento com Cashfree não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_aamarpay_route_unavailable' => 'A rota de pagamento com Aamarpay não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_yookassa_route_unavailable' => 'A rota de pagamento com YooKassa não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_midtrans_route_unavailable' => 'A rota de pagamento com Midtrans não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_xendit_route_unavailable' => 'A rota de pagamento com Xendit não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'customer_index_route_unavailable' => 'Маршрут клиентов недоступен. ' . self::DELEGATION_RU,
				'customers_import_route_unavailable' => 'Маршрут импорта CSV клиентов недоступен. ' . self::DELEGATION_RU,
				'customers_export_route_unavailable' => 'Маршрут экспорта клиентов недоступен. ' . self::DELEGATION_RU,
				'customers_create_route_unavailable' => 'Маршрут создания клиента недоступен. ' . self::DELEGATION_RU,
				'customers_show_route_unavailable' => 'Маршрут просмотра клиента недоступен. ' . self::DELEGATION_RU,
				'customers_edit_route_unavailable' => 'Маршрут редактирования клиента недоступен. ' . self::DELEGATION_RU,
				'customers_destroy_route_unavailable' => 'Маршрут удаления клиента недоступен. ' . self::DELEGATION_RU,
				'customers_transaction_route_unavailable' => 'Маршрут транзакции клиента недоступен. ' . self::DELEGATION_RU,
				'payment_route_unavailable' => 'Платежный маршрут недоступен. ' . self::DELEGATION_RU,
				'pay_with_bank_route_unavailable' => 'Платежный маршрут недоступен. ' . self::DELEGATION_RU,
				'payment_with_stripe_route_unavailable' => 'Платежный маршрут недоступен. ' . self::DELEGATION_RU,
				'payment_with_paypal_route_unavailable' => 'Платежный маршрут с PayPal недоступен. ' . self::DELEGATION_RU,
				'payment_with_paystack_route_unavailable' => 'Платежный маршрут с Paystack недоступен. ' . self::DELEGATION_RU,
				'payment_with_flutterwave_route_unavailable' => 'Платежный маршрут с Flutterwave недоступен. ' . self::DELEGATION_RU,
				'payment_with_razorpay_route_unavailable' => 'Платежный маршрут с Razorpay недоступен. ' . self::DELEGATION_RU,
				'payment_with_mercado_route_unavailable' => 'Платежный маршрут с Mercado недоступен. ' . self::DELEGATION_RU,
				'payment_with_paytm_route_unavailable' => 'Платежный маршрут с Paytm недоступен. ' . self::DELEGATION_RU,
				'payment_with_mollie_route_unavailable' => 'Платежный маршрут с Mollie недоступен. ' . self::DELEGATION_RU,
				'payment_with_skrill_route_unavailable' => 'Платежный маршрут с Skrill недоступен. ' . self::DELEGATION_RU,
				'payment_with_coingate_route_unavailable' => 'Платежный маршрут с Coingate недоступен. ' . self::DELEGATION_RU,
				'payment_with_paymentwall_route_unavailable' => 'Платежный маршрут с Paymentwall недоступен. ' . self::DELEGATION_RU,
				'payment_with_toyyibpay_route_unavailable' => 'Платежный маршрут с ToyyibPay недоступен. ' . self::DELEGATION_RU,
				'payment_with_payfast_route_unavailable' => 'Платежный маршрут с Payfast недоступен. ' . self::DELEGATION_RU,
				'payment_with_iyzipay_route_unavailable' => 'Платежный маршрут с Iyzipay недоступен. ' . self::DELEGATION_RU,
				'payment_with_sspay_route_unavailable' => 'Платежный маршрут с SSPay недоступен. ' . self::DELEGATION_RU,
				'payment_with_paytab_route_unavailable' => 'Платежный маршрут с PayTab недоступен. ' . self::DELEGATION_RU,
				'payment_with_cashfree_route_unavailable' => 'Платежный маршрут с Cashfree недоступен. ' . self::DELEGATION_RU,
				'payment_with_aamarpay_route_unavailable' => 'Платежный маршрут с Aamarpay недоступен. ' . self::DELEGATION_RU,
				'payment_with_yookassa_route_unavailable' => 'Платежный маршрут с YooKassa недоступен. ' . self::DELEGATION_RU,
				'payment_with_midtrans_route_unavailable' => 'Платежный маршрут с Midtrans недоступен. ' . self::DELEGATION_RU,
				'payment_with_xendit_route_unavailable' => 'Платежный маршрут с Xendit недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'customer_index_route_unavailable' => 'Müşteri rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_import_route_unavailable' => 'Müşteri CSV içe aktarma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_export_route_unavailable' => 'Müşteri dışa aktarma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_create_route_unavailable' => 'Müşteri oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_show_route_unavailable' => 'Müşteri görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_edit_route_unavailable' => 'Müşteri düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_destroy_route_unavailable' => 'Müşteri silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'customers_transaction_route_unavailable' => 'Müşteri işlem rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_route_unavailable' => 'Ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pay_with_bank_route_unavailable' => 'Ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_stripe_route_unavailable' => 'Ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_paypal_route_unavailable' => 'PayPal ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_paystack_route_unavailable' => 'Paystack ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_flutterwave_route_unavailable' => 'Flutterwave ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_razorpay_route_unavailable' => 'Razorpay ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_mercado_route_unavailable' => 'Mercado ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_paytm_route_unavailable' => 'Paytm ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_mollie_route_unavailable' => 'Mollie ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_skrill_route_unavailable' => 'Skrill ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_coingate_route_unavailable' => 'Coingate ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_paymentwall_route_unavailable' => 'Paymentwall ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_toyyibpay_route_unavailable' => 'ToyyibPay ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_payfast_route_unavailable' => 'Payfast ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_iyzipay_route_unavailable' => 'Iyzipay ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_sspay_route_unavailable' => 'SSPay ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_paytab_route_unavailable' => 'PayTab ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_cashfree_route_unavailable' => 'Cashfree ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_aamarpay_route_unavailable' => 'Aamarpay ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_yookassa_route_unavailable' => 'YooKassa ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_midtrans_route_unavailable' => 'Midtrans ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_xendit_route_unavailable' => 'Xendit ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'customer_index_route_unavailable' => '客户路由不可用。' . self::DELEGATION_ZH,
				'customers_import_route_unavailable' => '客户CSV导入路由不可用。' . self::DELEGATION_ZH,
				'customers_export_route_unavailable' => '客户导出路由不可用。' . self::DELEGATION_ZH,
				'customers_create_route_unavailable' => '客户创建路由不可用。' . self::DELEGATION_ZH,
				'customers_show_route_unavailable' => '客户查看路由不可用。' . self::DELEGATION_ZH,
				'customers_edit_route_unavailable' => '客户编辑路由不可用。' . self::DELEGATION_ZH,
				'customers_destroy_route_unavailable' => '客户删除路由不可用。' . self::DELEGATION_ZH,
				'customers_transaction_route_unavailable' => '客户交易路由不可用。' . self::DELEGATION_ZH,
				'payment_route_unavailable' => '支付路由不可用。' . self::DELEGATION_ZH,
				'pay_with_bank_route_unavailable' => '支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_stripe_route_unavailable' => '支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_paypal_route_unavailable' => 'PayPal支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_paystack_route_unavailable' => 'Paystack支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_flutterwave_route_unavailable' => 'Flutterwave支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_razorpay_route_unavailable' => 'Razorpay支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_mercado_route_unavailable' => 'Mercado支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_paytm_route_unavailable' => 'Paytm支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_mollie_route_unavailable' => 'Mollie支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_skrill_route_unavailable' => 'Skrill支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_coingate_route_unavailable' => 'Coingate支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_paymentwall_route_unavailable' => 'Paymentwall支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_toyyibpay_route_unavailable' => 'ToyyibPay支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_payfast_route_unavailable' => 'Payfast支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_iyzipay_route_unavailable' => 'Iyzipay支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_sspay_route_unavailable' => 'SSPay支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_paytab_route_unavailable' => 'PayTab支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_cashfree_route_unavailable' => 'Cashfree支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_aamarpay_route_unavailable' => 'Aamarpay支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_yookassa_route_unavailable' => 'YooKassa支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_midtrans_route_unavailable' => 'Midtrans支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_xendit_route_unavailable' => 'Xendit支付路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CST_FD => [
			'ar' => [
				'custom_field_index_route_unavailable' => 'مسار فهرس الحقول المخصصة غير متاح. ' . self::DELEGATION_AR,
				'custom_field_create_route_unavailable' => 'مسار إنشاء الحقل المخصص غير متاح. ' . self::DELEGATION_AR,
				'custom_field_update_route_unavailable' => 'مسار تحديث الحقل المخصص غير متاح. ' . self::DELEGATION_AR,
				'custom_field_edit_route_unavailable' => 'مسار تعديل الحقل المخصص غير متاح. ' . self::DELEGATION_AR,
				'custom_field_delete_unavailable' => 'مسار حذف الحقل المخصص غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'custom_field_index_route_unavailable' => 'Tilpasset felt-indeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_field_create_route_unavailable' => 'Oprettelsesrute for tilpasset felt er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_field_update_route_unavailable' => 'Opdateringsrute for tilpasset felt er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_field_edit_route_unavailable' => 'Redigeringsrute for tilpasset felt er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_field_delete_unavailable' => 'Sletningsrute for tilpasset felt er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'custom_field_index_route_unavailable' => 'Benutzerdefinierte Feld-Index-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_field_create_route_unavailable' => 'Erstellungsroute für benutzerdefinierte Felder ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_field_update_route_unavailable' => 'Aktualisierungsroute für benutzerdefinierte Felder ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_field_edit_route_unavailable' => 'Bearbeitungsroute für benutzerdefinierte Felder ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_field_delete_unavailable' => 'Löschroute für benutzerdefinierte Felder ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'custom_field_index_route_unavailable' => 'Custom Field index route is unavailable. ' . self::DELEGATION_EN,
				'custom_field_create_route_unavailable' => 'Custom Field creation route is unavailable. ' . self::DELEGATION_EN,
				'custom_field_update_route_unavailable' => 'Custom Field update route is unavailable. ' . self::DELEGATION_EN,
				'custom_field_edit_route_unavailable' => 'Edit Custom Field route is unavailable. ' . self::DELEGATION_EN,
				'custom_field_delete_unavailable' => 'Delete Custom Field route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'custom_field_index_route_unavailable' => 'La ruta de índice de campos personalizados no está disponible. ' . self::DELEGATION_ES,
				'custom_field_create_route_unavailable' => 'La ruta de creación de campos personalizados no está disponible. ' . self::DELEGATION_ES,
				'custom_field_update_route_unavailable' => 'La ruta de actualización de campos personalizados no está disponible. ' . self::DELEGATION_ES,
				'custom_field_edit_route_unavailable' => 'La ruta de edición de campos personalizados no está disponible. ' . self::DELEGATION_ES,
				'custom_field_delete_unavailable' => 'La ruta de eliminación de campos personalizados no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'custom_field_index_route_unavailable' => 'La route d\'index des champs personnalisés n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_field_create_route_unavailable' => 'La route de création de champ personnalisé n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_field_update_route_unavailable' => 'La route de mise à jour des champs personnalisés n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_field_edit_route_unavailable' => 'La route d\'édition des champs personnalisés n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_field_delete_unavailable' => 'La route de suppression des champs personnalisés n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'custom_field_index_route_unavailable' => 'נתיב אינדקס השדות המותאם אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_field_create_route_unavailable' => 'נתיב יצירת השדה המותאם אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_field_update_route_unavailable' => 'נתיב עדכון השדה המותאם אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_field_edit_route_unavailable' => 'נתיב עריכת השדה המותאם אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_field_delete_unavailable' => 'נתיב מחיקת השדה המותאם אישית אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'custom_field_index_route_unavailable' => 'La rotta dell\'indice dei campi personalizzati non è disponibile. ' . self::DELEGATION_IT,
				'custom_field_create_route_unavailable' => 'La rotta di creazione del campo personalizzato non è disponibile. ' . self::DELEGATION_IT,
				'custom_field_update_route_unavailable' => 'La rotta di aggiornamento del campo personalizzato non è disponibile. ' . self::DELEGATION_IT,
				'custom_field_edit_route_unavailable' => 'La rotta di modifica del campo personalizzato non è disponibile. ' . self::DELEGATION_IT,
				'custom_field_delete_unavailable' => 'La rotta di eliminazione del campo personalizzato non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'custom_field_index_route_unavailable' => 'カスタムフィールドのインデックスルートは利用できません。' . self::DELEGATION_JA,
				'custom_field_create_route_unavailable' => 'カスタムフィールド作成ルートは利用できません。' . self::DELEGATION_JA,
				'custom_field_update_route_unavailable' => 'カスタムフィールド更新ルートは利用できません。' . self::DELEGATION_JA,
				'custom_field_edit_route_unavailable' => 'カスタムフィールド編集ルートは利用できません。' . self::DELEGATION_JA,
				'custom_field_delete_unavailable' => 'カスタムフィールド削除ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'custom_field_index_route_unavailable' => 'Aangepast veldindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_field_create_route_unavailable' => 'Aangepaste veldaanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_field_update_route_unavailable' => 'Aangepaste veldupdate route is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_field_edit_route_unavailable' => 'Aangepaste veldbewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_field_delete_unavailable' => 'Aangepaste veldverwijderingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'custom_field_index_route_unavailable' => 'Trasa indeksu pól niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_field_create_route_unavailable' => 'Trasa tworzenia pól niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_field_update_route_unavailable' => 'Trasa aktualizacji pól niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_field_edit_route_unavailable' => 'Trasa edycji pól niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_field_delete_unavailable' => 'Trasa usuwania pól niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'custom_field_index_route_unavailable' => 'A rota de índice de campos personalizados não está disponível. ' . self::DELEGATION_PT,
				'custom_field_create_route_unavailable' => 'A rota de criação de campos personalizados não está disponível. ' . self::DELEGATION_PT,
				'custom_field_update_route_unavailable' => 'A rota de atualização de campos personalizados não está disponível. ' . self::DELEGATION_PT,
				'custom_field_edit_route_unavailable' => 'A rota de edição de campos personalizados não está disponível. ' . self::DELEGATION_PT,
				'custom_field_delete_unavailable' => 'A rota de exclusão de campos personalizados não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'custom_field_index_route_unavailable' => 'A rota de índice de campos personalizados não está disponível. ' . self::DELEGATION_PTBR,
				'custom_field_create_route_unavailable' => 'A rota de criação de campos personalizados não está disponível. ' . self::DELEGATION_PTBR,
				'custom_field_update_route_unavailable' => 'A rota de atualização de campos personalizados não está disponível. ' . self::DELEGATION_PTBR,
				'custom_field_edit_route_unavailable' => 'A rota de edição de campos personalizados não está disponível. ' . self::DELEGATION_PTBR,
				'custom_field_delete_unavailable' => 'A rota de exclusão de campos personalizados não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'custom_field_index_route_unavailable' => 'Маршрут индекса настраиваемых полей недоступен. ' . self::DELEGATION_RU,
				'custom_field_create_route_unavailable' => 'Маршрут создания настраиваемого поля недоступен. ' . self::DELEGATION_RU,
				'custom_field_update_route_unavailable' => 'Маршрут обновления настраиваемого поля недоступен. ' . self::DELEGATION_RU,
				'custom_field_edit_route_unavailable' => 'Маршрут редактирования настраиваемого поля недоступен. ' . self::DELEGATION_RU,
				'custom_field_delete_unavailable' => 'Маршрут удаления настраиваемого поля недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'custom_field_index_route_unavailable' => 'Özel Alan dizin rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_field_create_route_unavailable' => 'Özel Alan oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_field_update_route_unavailable' => 'Özel Alan güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_field_edit_route_unavailable' => 'Özel Alan düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_field_delete_unavailable' => 'Özel Alan silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'custom_field_index_route_unavailable' => '自定义字段索引路由不可用。' . self::DELEGATION_ZH,
				'custom_field_create_route_unavailable' => '自定义字段创建路由不可用。' . self::DELEGATION_ZH,
				'custom_field_update_route_unavailable' => '自定义字段更新路由不可用。' . self::DELEGATION_ZH,
				'custom_field_edit_route_unavailable' => '编辑自定义字段路由不可用。' . self::DELEGATION_ZH,
				'custom_field_delete_unavailable' => '删除自定义字段路由不可用。' . self::DELEGATION_ZH,
			],
		],
		ViewsConstants::CST_QT => [
			'ar' => [
				'custom_question_index_route_unavailable' => 'مسار فهرس الأسئلة المخصصة غير متاح. ' . self::DELEGATION_AR,
				'custom_question_create_route_unavailable' => 'مسار إنشاء السؤال المخصص غير متاح. ' . self::DELEGATION_AR,
				'custom_question_edit_route_unavailable' => 'مسار تعديل السؤال المخصص غير متاح. ' . self::DELEGATION_AR,
				'custom_question_destroy_route_unavailable' => 'مسار حذف السؤال المخصص غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'custom_question_index_route_unavailable' => 'Brugerdefineret spørgsmål indeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_question_create_route_unavailable' => 'Oprettelsesrute for brugerdefinerede spørgsmål er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_question_edit_route_unavailable' => 'Redigeringsrute for brugerdefinerede spørgsmål er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_question_destroy_route_unavailable' => 'Sletningsrute for brugerdefinerede spørgsmål er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'custom_question_index_route_unavailable' => 'Benutzerdefinierte Fragen-Index-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_question_create_route_unavailable' => 'Erstellungsroute für benutzerdefinierte Fragen ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_question_edit_route_unavailable' => 'Bearbeitungsroute für benutzerdefinierte Fragen ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_question_destroy_route_unavailable' => 'Löschroute für benutzerdefinierte Fragen ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'custom_question_index_route_unavailable' => 'Custom Question index route is unavailable. ' . self::DELEGATION_EN,
				'custom_question_create_route_unavailable' => 'Create Custom Question route is unavailable. ' . self::DELEGATION_EN,
				'custom_question_edit_route_unavailable' => 'Edit Custom Question route is unavailable. ' . self::DELEGATION_EN,
				'custom_question_destroy_route_unavailable' => 'Delete Custom Question route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'custom_question_index_route_unavailable' => 'La ruta de índice de preguntas personalizadas no está disponible. ' . self::DELEGATION_ES,
				'custom_question_create_route_unavailable' => 'La ruta de creación de preguntas personalizadas no está disponible. ' . self::DELEGATION_ES,
				'custom_question_edit_route_unavailable' => 'La ruta de edición de preguntas personalizadas no está disponible. ' . self::DELEGATION_ES,
				'custom_question_destroy_route_unavailable' => 'La ruta de eliminación de preguntas personalizadas no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'custom_question_index_route_unavailable' => 'La route d\'index des questions personnalisées n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_question_create_route_unavailable' => 'La route de création de questions personnalisées n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_question_edit_route_unavailable' => 'La route d\'édition des questions personnalisées n\'est pas disponible. ' . self::DELEGATION_FR,
				'custom_question_destroy_route_unavailable' => 'La route de suppression des questions personnalisées n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'custom_question_index_route_unavailable' => 'נתיב אינדקס השאלות המותאמות אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_question_create_route_unavailable' => 'נתיב יצירת השאלה המותאמת אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_question_edit_route_unavailable' => 'נתיב עריכת השאלה המותאמת אישית אינו זמין. ' . self::DELEGATION_HE,
				'custom_question_destroy_route_unavailable' => 'נתיב מחיקת השאלה המותאמת אישית אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'custom_question_index_route_unavailable' => 'La rotta dell\'indice delle domande personalizzate non è disponibile. ' . self::DELEGATION_IT,
				'custom_question_create_route_unavailable' => 'La rotta di creazione delle domande personalizzate non è disponibile. ' . self::DELEGATION_IT,
				'custom_question_edit_route_unavailable' => 'La rotta di modifica delle domande personalizzate non è disponibile. ' . self::DELEGATION_IT,
				'custom_question_destroy_route_unavailable' => 'La rotta di eliminazione delle domande personalizzate non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'custom_question_index_route_unavailable' => 'カスタム質問のインデックスルートは利用できません。' . self::DELEGATION_JA,
				'custom_question_create_route_unavailable' => 'カスタム質問作成ルートは利用できません。' . self::DELEGATION_JA,
				'custom_question_edit_route_unavailable' => 'カスタム質問編集ルートは利用できません。' . self::DELEGATION_JA,
				'custom_question_destroy_route_unavailable' => 'カスタム質問削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'custom_question_index_route_unavailable' => 'Aangepaste vragenindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_question_create_route_unavailable' => 'Aanmaakroute voor aangepaste vragen is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_question_edit_route_unavailable' => 'Bewerkingsroute voor aangepaste vragen is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_question_destroy_route_unavailable' => 'Verwijderingsroute voor aangepaste vragen is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'custom_question_index_route_unavailable' => 'Trasa indeksu pytań niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_question_create_route_unavailable' => 'Trasa tworzenia pytań niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_question_edit_route_unavailable' => 'Trasa edycji pytań niestandardowych jest niedostępna. ' . self::DELEGATION_PL,
				'custom_question_destroy_route_unavailable' => 'Trasa usuwania pytań niestandardowych jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'custom_question_index_route_unavailable' => 'A rota de índice de perguntas personalizadas não está disponível. ' . self::DELEGATION_PT,
				'custom_question_create_route_unavailable' => 'A rota de criação de perguntas personalizadas não está disponível. ' . self::DELEGATION_PT,
				'custom_question_edit_route_unavailable' => 'A rota de edição de perguntas personalizadas não está disponível. ' . self::DELEGATION_PT,
				'custom_question_destroy_route_unavailable' => 'A rota de exclusão de perguntas personalizadas não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'custom_question_index_route_unavailable' => 'A rota de índice de perguntas personalizadas não está disponível. ' . self::DELEGATION_PTBR,
				'custom_question_create_route_unavailable' => 'A rota de criação de perguntas personalizadas não está disponível. ' . self::DELEGATION_PTBR,
				'custom_question_edit_route_unavailable' => 'A rota de edição de perguntas personalizadas não está disponível. ' . self::DELEGATION_PTBR,
				'custom_question_destroy_route_unavailable' => 'A rota de exclusão de perguntas personalizadas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'custom_question_index_route_unavailable' => 'Маршрут индекса настраиваемых вопросов недоступен. ' . self::DELEGATION_RU,
				'custom_question_create_route_unavailable' => 'Маршрут создания настраиваемого вопроса недоступен. ' . self::DELEGATION_RU,
				'custom_question_edit_route_unavailable' => 'Маршрут редактирования настраиваемого вопроса недоступен. ' . self::DELEGATION_RU,
				'custom_question_destroy_route_unavailable' => 'Маршрут удаления настраиваемого вопроса недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'custom_question_index_route_unavailable' => 'Özel Soru dizin rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_question_create_route_unavailable' => 'Özel Soru oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_question_edit_route_unavailable' => 'Özel Soru düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_question_destroy_route_unavailable' => 'Özel Soru silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'custom_question_index_route_unavailable' => '自定义问题索引路由不可用。' . self::DELEGATION_ZH,
				'custom_question_create_route_unavailable' => '创建自定义问题路由不可用。' . self::DELEGATION_ZH,
				'custom_question_edit_route_unavailable' => '编辑自定义问题路由不可用。' . self::DELEGATION_ZH,
				'custom_question_destroy_route_unavailable' => '删除自定义问题路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::CTC => [
			'ar' => [
				'copy_store_route_unavailable' => 'مسار نسخ العقد غير متاح. ' . self::DELEGATION_AR,
				'create_contract_route_unavailable' => 'مسار الإنشاء غير متاح. ' . self::DELEGATION_AR,
				'contract_download_route_unavailable' => 'مسار تنزيل العقد غير متاح. ' . self::DELEGATION_AR,
				'contract_download_pdf_route_unavailable' => 'مسار تنزيل PDF العقد غير متاح. ' . self::DELEGATION_AR,
				'contract_signature_route_unavailable' => 'مسار التوقيع للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_generate_route_unavailable' => 'مسار التوليد بالذكاء الاصطناعي غير متاح. ' . self::DELEGATION_AR,
				'contract_index_route_unavailable' => 'مسار عرض القائمة للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_grid_route_unavailable' => 'مسار عرض الشبكة للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_show_route_unavailable' => 'مسار عرض العقد غير متاح. ' . self::DELEGATION_AR,
				'contract_edit_route_unavailable' => 'مسار التعديل للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_destroy_route_unavailable' => 'مسار الحذف للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_copy_route_unavailable' => 'مسار نسخ العقد غير متاح. ' . self::DELEGATION_AR,
				'contract_preview_route_unavailable' => 'مسار معاينة العقد غير متاح. ' . self::DELEGATION_AR,
				'contract_send_mail_route_unavailable' => 'مسار إرسال البريد الإلكتروني للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_status_route_unavailable' => 'مسار تحديث حالة العقد غير متاح. ' . self::DELEGATION_AR,
				'contract_file_delete_route_unavailable' => 'مسار حذف ملف العقد غير متاح. ' . self::DELEGATION_AR,
				'comment_store_route_unavailable' => 'مسار إضافة تعليق غير متاح. ' . self::DELEGATION_AR,
				'contract_comment_destroy_route_unavailable' => 'مسار حذف التعليق للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_note_store_route_unavailable' => 'مسار إضافة ملاحظة للعقود غير متاح. ' . self::DELEGATION_AR,
				'contract_note_destroy_route_unavailable' => 'مسار حذف التعليق للعقود غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'copy_store_route_unavailable' => 'Kontrakt kopi-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'create_contract_route_unavailable' => 'Opret rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_download_route_unavailable' => 'Kontrakt download-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_download_pdf_route_unavailable' => 'Kontrakt PDF-download-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_signature_route_unavailable' => 'Signaturrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_generate_route_unavailable' => 'Generer med AI-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_index_route_unavailable' => 'Listevisningsrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_grid_route_unavailable' => 'Gittervisningsrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_show_route_unavailable' => 'Kontraktvisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_edit_route_unavailable' => 'Redigeringsrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_destroy_route_unavailable' => 'Sletningsrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_copy_route_unavailable' => 'Kopier kontrakt-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_preview_route_unavailable' => 'Kontrakt forhåndsvisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_send_mail_route_unavailable' => 'Send e-mail-rute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_status_route_unavailable' => 'Kontraktstatusopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_file_delete_route_unavailable' => 'Kontraktfilsletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'comment_store_route_unavailable' => 'Tilføj kommentar-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_comment_destroy_route_unavailable' => 'Kommentarsletningsrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_note_store_route_unavailable' => 'Tilføj note-rute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_note_destroy_route_unavailable' => 'Kommentarsletningsrute for Kontrakter er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'copy_store_route_unavailable' => 'Vertragskopierroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'create_contract_route_unavailable' => 'Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_download_route_unavailable' => 'Vertrag-Download-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_download_pdf_route_unavailable' => 'Vertrag-PDF-Download-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_signature_route_unavailable' => 'Signaturroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_generate_route_unavailable' => 'Generieren-mit-KI-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_index_route_unavailable' => 'Listenansichtsroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_grid_route_unavailable' => 'Rasteransichtsroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_show_route_unavailable' => 'Vertragsansichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_edit_route_unavailable' => 'Bearbeitungsroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_destroy_route_unavailable' => 'Löschroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_copy_route_unavailable' => 'Vertragskopierroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_preview_route_unavailable' => 'Vertragsvorschau-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_send_mail_route_unavailable' => 'E-Mail-Sende-Route für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_status_route_unavailable' => 'Vertragsstatusaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_file_delete_route_unavailable' => 'Vertragsdateilöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'comment_store_route_unavailable' => 'Kommentarhinzufügungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_comment_destroy_route_unavailable' => 'Kommentarlöschroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_note_store_route_unavailable' => 'Notizhinzufügungsroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_note_destroy_route_unavailable' => 'Kommentarlöschroute für Verträge ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'copy_store_route_unavailable' => 'Contract copy route is unavailable. ' . self::DELEGATION_EN,
				'create_contract_route_unavailable' => 'Create route is unavailable. ' . self::DELEGATION_EN,
				'contract_download_route_unavailable' => 'Contract download route is unavailable. ' . self::DELEGATION_EN,
				'contract_download_pdf_route_unavailable' => 'Contract PDF download route is unavailable. ' . self::DELEGATION_EN,
				'contract_signature_route_unavailable' => 'Signature route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_generate_route_unavailable' => 'Generate with AI route is unavailable. ' . self::DELEGATION_EN,
				'contract_index_route_unavailable' => 'List view route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_grid_route_unavailable' => 'Grid view route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_show_route_unavailable' => 'Contract view route is unavailable. ' . self::DELEGATION_EN,
				'contract_edit_route_unavailable' => 'Edit route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_destroy_route_unavailable' => 'Delete route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_copy_route_unavailable' => 'Copy Contract route is unavailable. ' . self::DELEGATION_EN,
				'contract_preview_route_unavailable' => 'Contract preview route is unavailable. ' . self::DELEGATION_EN,
				'contract_send_mail_route_unavailable' => 'Send email route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_status_route_unavailable' => 'Contract status update route is unavailable. ' . self::DELEGATION_EN,
				'contract_file_delete_route_unavailable' => 'Contract file delete route is unavailable. ' . self::DELEGATION_EN,
				'comment_store_route_unavailable' => 'Add comment route is unavailable. ' . self::DELEGATION_EN,
				'contract_comment_destroy_route_unavailable' => 'Comment delete route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_note_store_route_unavailable' => 'Add note route for Contracts is unavailable. ' . self::DELEGATION_EN,
				'contract_note_destroy_route_unavailable' => 'Comment delete route for Contracts is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'copy_store_route_unavailable' => 'La ruta de copia de contrato no está disponible. ' . self::DELEGATION_ES,
				'create_contract_route_unavailable' => 'La ruta de creación no está disponible. ' . self::DELEGATION_ES,
				'contract_download_route_unavailable' => 'La ruta de descarga de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_download_pdf_route_unavailable' => 'La ruta de descarga de PDF de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_signature_route_unavailable' => 'La ruta de firma para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_generate_route_unavailable' => 'La ruta de generación con IA no está disponible. ' . self::DELEGATION_ES,
				'contract_index_route_unavailable' => 'La ruta de vista de lista para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_grid_route_unavailable' => 'La ruta de vista de cuadrícula para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_show_route_unavailable' => 'La ruta de visualización de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_edit_route_unavailable' => 'La ruta de edición para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_destroy_route_unavailable' => 'La ruta de eliminación para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_copy_route_unavailable' => 'La ruta de copia de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_preview_route_unavailable' => 'La ruta de vista previa de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_send_mail_route_unavailable' => 'La ruta de envío de correo para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_status_route_unavailable' => 'La ruta de actualización de estado de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_file_delete_route_unavailable' => 'La ruta de eliminación de archivo de contrato no está disponible. ' . self::DELEGATION_ES,
				'comment_store_route_unavailable' => 'La ruta para agregar comentario no está disponible. ' . self::DELEGATION_ES,
				'contract_comment_destroy_route_unavailable' => 'La ruta de eliminación de comentarios para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_note_store_route_unavailable' => 'La ruta para agregar nota para Contratos no está disponible. ' . self::DELEGATION_ES,
				'contract_note_destroy_route_unavailable' => 'La ruta de eliminación de comentarios para Contratos no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'copy_store_route_unavailable' => 'La route de copie de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'create_contract_route_unavailable' => 'La route de création n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_download_route_unavailable' => 'La route de téléchargement de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_download_pdf_route_unavailable' => 'La route de téléchargement PDF de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_signature_route_unavailable' => 'La route de signature pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_generate_route_unavailable' => 'La route de génération avec IA n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_index_route_unavailable' => 'La route d\'affichage en liste pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_grid_route_unavailable' => 'La route d\'affichage en grille pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_show_route_unavailable' => 'La route de visualisation de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_edit_route_unavailable' => 'La route d\'édition pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_destroy_route_unavailable' => 'La route de suppression pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_copy_route_unavailable' => 'La route de copie de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_preview_route_unavailable' => 'La route d\'aperçu de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_send_mail_route_unavailable' => 'La route d\'envoi d\'e-mail pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_status_route_unavailable' => 'La route de mise à jour du statut du contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_file_delete_route_unavailable' => 'La route de suppression de fichier de contrat n\'est pas disponible. ' . self::DELEGATION_FR,
				'comment_store_route_unavailable' => 'La route d\'ajout de commentaire n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_comment_destroy_route_unavailable' => 'La route de suppression de commentaire pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_note_store_route_unavailable' => 'La route d\'ajout de note pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
				'contract_note_destroy_route_unavailable' => 'La route de suppression de commentaire pour les Contrats n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'copy_store_route_unavailable' => 'נתיב העתקת החוזה אינו זמין. ' . self::DELEGATION_HE,
				'create_contract_route_unavailable' => 'נתיב היצירה אינו זמין. ' . self::DELEGATION_HE,
				'contract_download_route_unavailable' => 'נתיב הורדת החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_download_pdf_route_unavailable' => 'נתיב הורדת PDF החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_signature_route_unavailable' => 'נתיב החתימה לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_generate_route_unavailable' => 'נתיב יצירה עם בינה מלאכותית אינו זמין. ' . self::DELEGATION_HE,
				'contract_index_route_unavailable' => 'נתיב תצוגת הרשימה לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_grid_route_unavailable' => 'נתיב תצוגת הרשת לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_show_route_unavailable' => 'נתיב צפייה בחוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_edit_route_unavailable' => 'נתיב העריכה לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_destroy_route_unavailable' => 'נתיב המחיקה לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_copy_route_unavailable' => 'נתיב העתקת החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_preview_route_unavailable' => 'נתיב תצוגה מקדימה של החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_send_mail_route_unavailable' => 'נתיב שליחת דוא"ל לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_status_route_unavailable' => 'נתיב עדכון סטטוס החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_file_delete_route_unavailable' => 'נתיב מחיקת קובץ החוזה אינו זמין. ' . self::DELEGATION_HE,
				'comment_store_route_unavailable' => 'נתיב הוספת תגובה אינו זמין. ' . self::DELEGATION_HE,
				'contract_comment_destroy_route_unavailable' => 'נתיב מחיקת תגובה לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_note_store_route_unavailable' => 'נתיב הוספת הערה לחוזים אינו זמין. ' . self::DELEGATION_HE,
				'contract_note_destroy_route_unavailable' => 'נתיב מחיקת תגובה לחוזים אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'copy_store_route_unavailable' => 'La rotta di copia del contratto non è disponibile. ' . self::DELEGATION_IT,
				'create_contract_route_unavailable' => 'La rotta di creazione non è disponibile. ' . self::DELEGATION_IT,
				'contract_download_route_unavailable' => 'La rotta di download del contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_download_pdf_route_unavailable' => 'La rotta di download PDF del contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_signature_route_unavailable' => 'La rotta della firma per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_generate_route_unavailable' => 'La rotta di generazione con IA non è disponibile. ' . self::DELEGATION_IT,
				'contract_index_route_unavailable' => 'La rotta della vista a elenco per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_grid_route_unavailable' => 'La rotta della vista a griglia per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_show_route_unavailable' => 'La rotta di visualizzazione del contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_edit_route_unavailable' => 'La rotta di modifica per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_destroy_route_unavailable' => 'La rotta di eliminazione per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_copy_route_unavailable' => 'La rotta di copia del contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_preview_route_unavailable' => 'La rotta di anteprima del contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_send_mail_route_unavailable' => 'La rotta di invio email per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_status_route_unavailable' => 'La rotta di aggiornamento dello stato del contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_file_delete_route_unavailable' => 'La rotta di eliminazione del file del contratto non è disponibile. ' . self::DELEGATION_IT,
				'comment_store_route_unavailable' => 'La rotta per aggiungere commenti non è disponibile. ' . self::DELEGATION_IT,
				'contract_comment_destroy_route_unavailable' => 'La rotta di eliminazione commenti per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_note_store_route_unavailable' => 'La rotta per aggiungere note per i Contratti non è disponibile. ' . self::DELEGATION_IT,
				'contract_note_destroy_route_unavailable' => 'La rotta di eliminazione commenti per i Contratti non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'copy_store_route_unavailable' => '契約コピールートは利用できません。' . self::DELEGATION_JA,
				'create_contract_route_unavailable' => '作成ルートは利用できません。' . self::DELEGATION_JA,
				'contract_download_route_unavailable' => '契約ダウンロードルートは利用できません。' . self::DELEGATION_JA,
				'contract_download_pdf_route_unavailable' => '契約PDFダウンロードルートは利用できません。' . self::DELEGATION_JA,
				'contract_signature_route_unavailable' => '契約の署名ルートは利用できません。' . self::DELEGATION_JA,
				'contract_generate_route_unavailable' => 'AIによる生成ルートは利用できません。' . self::DELEGATION_JA,
				'contract_index_route_unavailable' => '契約のリスト表示ルートは利用できません。' . self::DELEGATION_JA,
				'contract_grid_route_unavailable' => '契約のグリッド表示ルートは利用できません。' . self::DELEGATION_JA,
				'contract_show_route_unavailable' => '契約表示ルートは利用できません。' . self::DELEGATION_JA,
				'contract_edit_route_unavailable' => '契約の編集ルートは利用できません。' . self::DELEGATION_JA,
				'contract_destroy_route_unavailable' => '契約の削除ルートは利用できません。' . self::DELEGATION_JA,
				'contract_copy_route_unavailable' => '契約コピールートは利用できません。' . self::DELEGATION_JA,
				'contract_preview_route_unavailable' => '契約プレビュールートは利用できません。' . self::DELEGATION_JA,
				'contract_send_mail_route_unavailable' => '契約のメール送信ルートは利用できません。' . self::DELEGATION_JA,
				'contract_status_route_unavailable' => '契約ステータス更新ルートは利用できません。' . self::DELEGATION_JA,
				'contract_file_delete_route_unavailable' => '契約ファイル削除ルートは利用できません。' . self::DELEGATION_JA,
				'comment_store_route_unavailable' => 'コメント追加ルートは利用できません。' . self::DELEGATION_JA,
				'contract_comment_destroy_route_unavailable' => '契約のコメント削除ルートは利用できません。' . self::DELEGATION_JA,
				'contract_note_store_route_unavailable' => '契約のメモ追加ルートは利用できません。' . self::DELEGATION_JA,
				'contract_note_destroy_route_unavailable' => '契約のコメント削除ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'copy_store_route_unavailable' => 'Contract kopieerroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'create_contract_route_unavailable' => 'Aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_download_route_unavailable' => 'Contract downloadroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_download_pdf_route_unavailable' => 'Contract PDF-downloadroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_signature_route_unavailable' => 'Handtekeningroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_generate_route_unavailable' => 'Genereren met AI-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_index_route_unavailable' => 'Lijstweergaveroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_grid_route_unavailable' => 'Rasterweergaveroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_show_route_unavailable' => 'Contractweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_edit_route_unavailable' => 'Bewerkingsroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_destroy_route_unavailable' => 'Verwijderingsroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_copy_route_unavailable' => 'Contract kopieerroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_preview_route_unavailable' => 'Contract voorbeeldroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_send_mail_route_unavailable' => 'E-mailverzendroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_status_route_unavailable' => 'Contractstatusupdateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_file_delete_route_unavailable' => 'Contractbestandverwijderingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'comment_store_route_unavailable' => 'Opmerkingtoevoegingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_comment_destroy_route_unavailable' => 'Opmerkingverwijderingsroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_note_store_route_unavailable' => 'Notitietoevoegingsroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_note_destroy_route_unavailable' => 'Opmerkingverwijderingsroute voor Contracten is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'copy_store_route_unavailable' => 'Trasa kopiowania umowy jest niedostępna. ' . self::DELEGATION_PL,
				'create_contract_route_unavailable' => 'Trasa tworzenia jest niedostępna. ' . self::DELEGATION_PL,
				'contract_download_route_unavailable' => 'Trasa pobierania umowy jest niedostępna. ' . self::DELEGATION_PL,
				'contract_download_pdf_route_unavailable' => 'Trasa pobierania PDF umowy jest niedostępna. ' . self::DELEGATION_PL,
				'contract_signature_route_unavailable' => 'Trasa podpisu dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_generate_route_unavailable' => 'Trasa generowania za pomocą AI jest niedostępna. ' . self::DELEGATION_PL,
				'contract_index_route_unavailable' => 'Trasa widoku listy dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_grid_route_unavailable' => 'Trasa widoku siatki dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_show_route_unavailable' => 'Trasa podglądu umowy jest niedostępna. ' . self::DELEGATION_PL,
				'contract_edit_route_unavailable' => 'Trasa edycji dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_destroy_route_unavailable' => 'Trasa usuwania dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_copy_route_unavailable' => 'Trasa kopiowania umowy jest niedostępna. ' . self::DELEGATION_PL,
				'contract_preview_route_unavailable' => 'Trasa podglądu umowy jest niedostępna. ' . self::DELEGATION_PL,
				'contract_send_mail_route_unavailable' => 'Trasa wysyłania e-maili dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_status_route_unavailable' => 'Trasa aktualizacji statusu umowy jest niedostępna. ' . self::DELEGATION_PL,
				'contract_file_delete_route_unavailable' => 'Trasa usuwania pliku umowy jest niedostępna. ' . self::DELEGATION_PL,
				'comment_store_route_unavailable' => 'Trasa dodawania komentarza jest niedostępna. ' . self::DELEGATION_PL,
				'contract_comment_destroy_route_unavailable' => 'Trasa usuwania komentarza dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_note_store_route_unavailable' => 'Trasa dodawania notatki dla Umów jest niedostępna. ' . self::DELEGATION_PL,
				'contract_note_destroy_route_unavailable' => 'Trasa usuwania komentarza dla Umów jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'copy_store_route_unavailable' => 'A rota de cópia de contrato não está disponível. ' . self::DELEGATION_PT,
				'create_contract_route_unavailable' => 'A rota de criação não está disponível. ' . self::DELEGATION_PT,
				'contract_download_route_unavailable' => 'A rota de download de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_download_pdf_route_unavailable' => 'A rota de download de PDF de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_signature_route_unavailable' => 'A rota de assinatura para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_generate_route_unavailable' => 'A rota de geração com IA não está disponível. ' . self::DELEGATION_PT,
				'contract_index_route_unavailable' => 'A rota de visualização de lista para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_grid_route_unavailable' => 'A rota de visualização de grelha para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_show_route_unavailable' => 'A rota de visualização de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_edit_route_unavailable' => 'A rota de edição para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_destroy_route_unavailable' => 'A rota de eliminação para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_copy_route_unavailable' => 'A rota de cópia de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_preview_route_unavailable' => 'A rota de pré-visualização de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_send_mail_route_unavailable' => 'A rota de envio de e-mail para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_status_route_unavailable' => 'A rota de atualização de estado de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_file_delete_route_unavailable' => 'A rota de eliminação de ficheiro de contrato não está disponível. ' . self::DELEGATION_PT,
				'comment_store_route_unavailable' => 'A rota para adicionar comentário não está disponível. ' . self::DELEGATION_PT,
				'contract_comment_destroy_route_unavailable' => 'A rota de eliminação de comentário para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_note_store_route_unavailable' => 'A rota para adicionar nota para Contratos não está disponível. ' . self::DELEGATION_PT,
				'contract_note_destroy_route_unavailable' => 'A rota de eliminação de comentário para Contratos não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'copy_store_route_unavailable' => 'A rota de cópia de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'create_contract_route_unavailable' => 'A rota de criação não está disponível. ' . self::DELEGATION_PTBR,
				'contract_download_route_unavailable' => 'A rota de download de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_download_pdf_route_unavailable' => 'A rota de download de PDF de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_signature_route_unavailable' => 'A rota de assinatura para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_generate_route_unavailable' => 'A rota de geração com IA não está disponível. ' . self::DELEGATION_PTBR,
				'contract_index_route_unavailable' => 'A rota de visualização de lista para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_grid_route_unavailable' => 'A rota de visualização de grade para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_show_route_unavailable' => 'A rota de visualização de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_edit_route_unavailable' => 'A rota de edição para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_destroy_route_unavailable' => 'A rota de exclusão para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_copy_route_unavailable' => 'A rota de cópia de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_preview_route_unavailable' => 'A rota de pré-visualização de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_send_mail_route_unavailable' => 'A rota de envio de e-mail para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_status_route_unavailable' => 'A rota de atualização de status de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_file_delete_route_unavailable' => 'A rota de exclusão de arquivo de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'comment_store_route_unavailable' => 'A rota para adicionar comentário não está disponível. ' . self::DELEGATION_PTBR,
				'contract_comment_destroy_route_unavailable' => 'A rota de exclusão de comentário para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_note_store_route_unavailable' => 'A rota para adicionar nota para Contratos não está disponível. ' . self::DELEGATION_PTBR,
				'contract_note_destroy_route_unavailable' => 'A rota de exclusão de comentário para Contratos não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'copy_store_route_unavailable' => 'Маршрут копирования контракта недоступен. ' . self::DELEGATION_RU,
				'create_contract_route_unavailable' => 'Маршрут создания недоступен. ' . self::DELEGATION_RU,
				'contract_download_route_unavailable' => 'Маршрут загрузки контракта недоступен. ' . self::DELEGATION_RU,
				'contract_download_pdf_route_unavailable' => 'Маршрут загрузки PDF контракта недоступен. ' . self::DELEGATION_RU,
				'contract_signature_route_unavailable' => 'Маршрут подписи для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_generate_route_unavailable' => 'Маршрут генерации с ИИ недоступен. ' . self::DELEGATION_RU,
				'contract_index_route_unavailable' => 'Маршрут просмотра списка для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_grid_route_unavailable' => 'Маршрут просмотра сетки для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_show_route_unavailable' => 'Маршрут просмотра контракта недоступен. ' . self::DELEGATION_RU,
				'contract_edit_route_unavailable' => 'Маршрут редактирования для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_destroy_route_unavailable' => 'Маршрут удаления для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_copy_route_unavailable' => 'Маршрут копирования контракта недоступен. ' . self::DELEGATION_RU,
				'contract_preview_route_unavailable' => 'Маршрут предпросмотра контракта недоступен. ' . self::DELEGATION_RU,
				'contract_send_mail_route_unavailable' => 'Маршрут отправки электронной почты для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_status_route_unavailable' => 'Маршрут обновления статуса контракта недоступен. ' . self::DELEGATION_RU,
				'contract_file_delete_route_unavailable' => 'Маршрут удаления файла контракта недоступен. ' . self::DELEGATION_RU,
				'comment_store_route_unavailable' => 'Маршрут добавления комментария недоступен. ' . self::DELEGATION_RU,
				'contract_comment_destroy_route_unavailable' => 'Маршрут удаления комментария для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_note_store_route_unavailable' => 'Маршрут добавления заметки для Контрактов недоступен. ' . self::DELEGATION_RU,
				'contract_note_destroy_route_unavailable' => 'Маршрут удаления комментария для Контрактов недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'copy_store_route_unavailable' => 'Sözleşme kopyalama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'create_contract_route_unavailable' => 'Oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_download_route_unavailable' => 'Sözleşme indirme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_download_pdf_route_unavailable' => 'Sözleşme PDF indirme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_signature_route_unavailable' => 'Sözleşmeler için imza rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_generate_route_unavailable' => 'AI ile oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_index_route_unavailable' => 'Sözleşmeler için liste görünümü rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_grid_route_unavailable' => 'Sözleşmeler için ızgara görünümü rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_show_route_unavailable' => 'Sözleşme görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_edit_route_unavailable' => 'Sözleşmeler için düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_destroy_route_unavailable' => 'Sözleşmeler için silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_copy_route_unavailable' => 'Sözleşme kopyalama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_preview_route_unavailable' => 'Sözleşme ön izleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_send_mail_route_unavailable' => 'Sözleşmeler için e-posta gönderme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_status_route_unavailable' => 'Sözleşme durumu güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_file_delete_route_unavailable' => 'Sözleşme dosyası silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'comment_store_route_unavailable' => 'Yorum ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_comment_destroy_route_unavailable' => 'Sözleşmeler için yorum silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_note_store_route_unavailable' => 'Sözleşmeler için not ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_note_destroy_route_unavailable' => 'Sözleşmeler için yorum silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'copy_store_route_unavailable' => '合同复制路由不可用。' . self::DELEGATION_ZH,
				'create_contract_route_unavailable' => '创建路由不可用。' . self::DELEGATION_ZH,
				'contract_download_route_unavailable' => '合同下载路由不可用。' . self::DELEGATION_ZH,
				'contract_download_pdf_route_unavailable' => '合同PDF下载路由不可用。' . self::DELEGATION_ZH,
				'contract_signature_route_unavailable' => '合同签名路由不可用。' . self::DELEGATION_ZH,
				'contract_generate_route_unavailable' => 'AI生成路由不可用。' . self::DELEGATION_ZH,
				'contract_index_route_unavailable' => '合同列表视图路由不可用。' . self::DELEGATION_ZH,
				'contract_grid_route_unavailable' => '合同网格视图路由不可用。' . self::DELEGATION_ZH,
				'contract_show_route_unavailable' => '合同查看路由不可用。' . self::DELEGATION_ZH,
				'contract_edit_route_unavailable' => '合同编辑路由不可用。' . self::DELEGATION_ZH,
				'contract_destroy_route_unavailable' => '合同删除路由不可用。' . self::DELEGATION_ZH,
				'contract_copy_route_unavailable' => '合同复制路由不可用。' . self::DELEGATION_ZH,
				'contract_preview_route_unavailable' => '合同预览路由不可用。' . self::DELEGATION_ZH,
				'contract_send_mail_route_unavailable' => '合同发送邮件路由不可用。' . self::DELEGATION_ZH,
				'contract_status_route_unavailable' => '合同状态更新路由不可用。' . self::DELEGATION_ZH,
				'contract_file_delete_route_unavailable' => '合同文件删除路由不可用。' . self::DELEGATION_ZH,
				'comment_store_route_unavailable' => '添加评论路由不可用。' . self::DELEGATION_ZH,
				'contract_comment_destroy_route_unavailable' => '合同评论删除路由不可用。' . self::DELEGATION_ZH,
				'contract_note_store_route_unavailable' => '合同笔记添加路由不可用。' . self::DELEGATION_ZH,
				'contract_note_destroy_route_unavailable' => '合同评论删除路由不可用。' . self::DELEGATION_ZH,
			],
		],
		ViewsConstants::CTC_TP => [
			'ar' => [
				'contract_type_store_route_unavailable' => 'مسار تخزين نوع العقد غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'contract_type_update_route_unavailable' => 'مسار تحديث نوع العقد غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'contract_type_edit_route_unavailable' => 'مسار تعديل نوع العقد غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'contract_type_destroy_route_unavailable' => 'مسار حذف نوع العقد غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR,
				'contract_type_create_route_unavailable' => 'مسار إنشاء نوع العقد غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول المجال. ' . self::DELEGATION_AR
			],
			'da' => [
				'contract_type_store_route_unavailable' => 'Kontrakttype lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_type_update_route_unavailable' => 'Kontrakttype opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_type_edit_route_unavailable' => 'Kontrakttype redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_type_destroy_route_unavailable' => 'Kontrakttype sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'contract_type_create_route_unavailable' => 'Kontrakttype oprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'contract_type_store_route_unavailable' => 'Contract Type Store-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_type_update_route_unavailable' => 'Contract Type Update-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_type_edit_route_unavailable' => 'Contract Type Edit-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_type_destroy_route_unavailable' => 'Contract Type Delete-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'contract_type_create_route_unavailable' => 'Contract Type Create-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'contract_type_store_route_unavailable' => 'Contract Type store route is unavailable. ' . self::DELEGATION_EN,
				'contract_type_update_route_unavailable' => 'Contract Type update route is unavailable. ' . self::DELEGATION_EN,
				'contract_type_edit_route_unavailable' => 'Contract Type edit route is unavailable. ' . self::DELEGATION_EN,
				'contract_type_destroy_route_unavailable' => 'Contract Type delete route is unavailable. ' . self::DELEGATION_EN,
				'contract_type_create_route_unavailable' => 'Contract Type create route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'contract_type_store_route_unavailable' => 'La ruta de almacenamiento de tipo de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_type_update_route_unavailable' => 'La ruta de actualización de tipo de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_type_edit_route_unavailable' => 'La ruta de edición de tipo de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_type_destroy_route_unavailable' => 'La ruta de eliminación de tipo de contrato no está disponible. ' . self::DELEGATION_ES,
				'contract_type_create_route_unavailable' => 'La ruta de creación de tipo de contrato no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'contract_type_store_route_unavailable' => 'La route de stockage du type de contrat est indisponible. ' . self::DELEGATION_FR,
				'contract_type_update_route_unavailable' => 'La route de mise à jour du type de contrat est indisponible. ' . self::DELEGATION_FR,
				'contract_type_edit_route_unavailable' => 'La route d\'édition du type de contrat est indisponible. ' . self::DELEGATION_FR,
				'contract_type_destroy_route_unavailable' => 'La route de suppression du type de contrat est indisponible. ' . self::DELEGATION_FR,
				'contract_type_create_route_unavailable' => 'La route de création du type de contrat est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'contract_type_store_route_unavailable' => 'נתיב אחסון סוג החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_type_update_route_unavailable' => 'נתיב עדכון סוג החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_type_edit_route_unavailable' => 'נתיב עריכת סוג החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_type_destroy_route_unavailable' => 'נתיב מחיקת סוג החוזה אינו זמין. ' . self::DELEGATION_HE,
				'contract_type_create_route_unavailable' => 'נתיב יצירת סוג החוזה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'contract_type_store_route_unavailable' => 'La rotta di archiviazione del tipo di contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_type_update_route_unavailable' => 'La rotta di aggiornamento del tipo di contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_type_edit_route_unavailable' => 'La rotta di modifica del tipo di contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_type_destroy_route_unavailable' => 'La rotta di eliminazione del tipo di contratto non è disponibile. ' . self::DELEGATION_IT,
				'contract_type_create_route_unavailable' => 'La rotta di creazione del tipo di contratto non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'contract_type_store_route_unavailable' => '契約タイプ保存ルートは利用できません。' . self::DELEGATION_JA,
				'contract_type_update_route_unavailable' => '契約タイプ更新ルートは利用できません。' . self::DELEGATION_JA,
				'contract_type_edit_route_unavailable' => '契約タイプ編集ルートは利用できません。' . self::DELEGATION_JA,
				'contract_type_destroy_route_unavailable' => '契約タイプ削除ルートは利用できません。' . self::DELEGATION_JA,
				'contract_type_create_route_unavailable' => '契約タイプ作成ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'contract_type_store_route_unavailable' => 'Contracttype opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_type_update_route_unavailable' => 'Contracttype updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_type_edit_route_unavailable' => 'Contracttype bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_type_destroy_route_unavailable' => 'Contracttype verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'contract_type_create_route_unavailable' => 'Contracttype aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'contract_type_store_route_unavailable' => 'Trasa przechowywania typu kontraktu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'contract_type_update_route_unavailable' => 'Trasa aktualizacji typu kontraktu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'contract_type_edit_route_unavailable' => 'Trasa edycji typu kontraktu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'contract_type_destroy_route_unavailable' => 'Trasa usuwania typu kontraktu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL,
				'contract_type_create_route_unavailable' => 'Trasa tworzenia typu kontraktu jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny. ' . self::DELEGATION_PL
			],
			'pt' => [
				'contract_type_store_route_unavailable' => 'A rota de armazenamento de tipo de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_type_update_route_unavailable' => 'A rota de atualização de tipo de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_type_edit_route_unavailable' => 'A rota de edição de tipo de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_type_destroy_route_unavailable' => 'A rota de exclusão de tipo de contrato não está disponível. ' . self::DELEGATION_PT,
				'contract_type_create_route_unavailable' => 'A rota de criação de tipo de contrato não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'contract_type_store_route_unavailable' => 'A rota de armazenamento do tipo de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_type_update_route_unavailable' => 'A rota de atualização do tipo de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_type_edit_route_unavailable' => 'A rota de edição do tipo de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_type_destroy_route_unavailable' => 'A rota de exclusão do tipo de contrato não está disponível. ' . self::DELEGATION_PTBR,
				'contract_type_create_route_unavailable' => 'A rota de criação do tipo de contrato não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'contract_type_store_route_unavailable' => 'Маршрут сохранения типа контракта недоступен. ' . self::DELEGATION_RU,
				'contract_type_update_route_unavailable' => 'Маршрут обновления типа контракта недоступен. ' . self::DELEGATION_RU,
				'contract_type_edit_route_unavailable' => 'Маршрут редактирования типа контракта недоступен. ' . self::DELEGATION_RU,
				'contract_type_destroy_route_unavailable' => 'Маршрут удаления типа контракта недоступен. ' . self::DELEGATION_RU,
				'contract_type_create_route_unavailable' => 'Маршрут создания типа контракта недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'contract_type_store_route_unavailable' => 'Sözleşme Tipi depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_type_update_route_unavailable' => 'Sözleşme Tipi güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_type_edit_route_unavailable' => 'Sözleşme Tipi düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_type_destroy_route_unavailable' => 'Sözleşme Tipi silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'contract_type_create_route_unavailable' => 'Sözleşme Tipi oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'contract_type_store_route_unavailable' => '合同类型存储路由不可用。' . self::DELEGATION_ZH,
				'contract_type_update_route_unavailable' => '合同类型更新路由不可用。' . self::DELEGATION_ZH,
				'contract_type_edit_route_unavailable' => '合同类型编辑路由不可用。' . self::DELEGATION_ZH,
				'contract_type_destroy_route_unavailable' => '合同类型删除路由不可用。' . self::DELEGATION_ZH,
				'contract_type_create_route_unavailable' => '合同类型创建路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::DBT_NT => [
			'ar' => ['debit_note_route_unavailable' => 'مسار إشعارات المدين غير متاح. ' . self::DELEGATION_AR],
			'da' => ['debit_note_route_unavailable' => 'Debitnotarute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['debit_note_route_unavailable' => 'Debitoren-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['debit_note_route_unavailable' => 'Debit Note route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['debit_note_route_unavailable' => 'La ruta de notas de débito no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['debit_note_route_unavailable' => 'La route des notes de débit est indisponible. ' . self::DELEGATION_FR],
			'he' => ['debit_note_route_unavailable' => 'נתיב חשבוניות חיוב אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['debit_note_route_unavailable' => 'La rotta delle note di addebito non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['debit_note_route_unavailable' => '借方通知ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['debit_note_route_unavailable' => 'Debitnotaroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['debit_note_route_unavailable' => 'Trasa not obciążeniowych jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['debit_note_route_unavailable' => 'A rota de notas de débito não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['debit_note_route_unavailable' => 'A rota de notas de débito não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['debit_note_route_unavailable' => 'Маршрут дебет-нот недоступен. ' . self::DELEGATION_RU],
			'tr' => ['debit_note_route_unavailable' => 'Borç dekontu rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['debit_note_route_unavailable' => '借方票据路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::DL => [
			'ar' => [
				'calls_update_route_unavailable' => 'مسار تحديث المكالمة غير متاح. ' . self::DELEGATION_AR,
				'calls_store_route_unavailable' => 'مسار تخزين المكالمة غير متاح. ' . self::DELEGATION_AR,
				'generate_route_unavailable' => 'مسار إنشاء المحتوى للصفقات باستخدام الذكاء الاصطناعي غير متاح. ' . self::DELEGATION_AR,
				'grammar_route_unavailable' => 'مسار التدقيق النحوي باستخدام الذكاء الاصطناعي غير متاح. ' . self::DELEGATION_AR,
				'clients_update_route_unavailable' => 'مسار تحديث الصفقات مع العملاء غير متاح. ' . self::DELEGATION_AR,
				'clients_index_route_unavailable' => 'مسار فهرس العملاء غير متاح. ' . self::DELEGATION_AR,
				'discussion_store_route_unavailable' => 'مسار تخزين المناقشة غير متاح. ' . self::DELEGATION_AR,
				'discussions_create_route_unavailable' => 'مسار إنشاء مناقشات الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_update_route_unavailable' => 'مسار التحديث غير متاح. ' . self::DELEGATION_AR,
				'change_pipeline_deal_route_unavailable' => 'مسار تغيير خط أنابيب الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deals_list_route_unavailable' => 'مسار قائمة الصفقات غير متاح. ' . self::DELEGATION_AR,
				'deals_create_route_unavailable' => 'مسار إنشاء الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_emails_store_route_unavailable' => 'مسار تخزين رسائل البريد الإلكتروني للصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_show_route_unavailable' => 'مسار عرض الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deals_edit_route_unavailable' => 'مسار تعديل الصفقة غير متاح. ' . self::DELEGATION_AR,
				'calls_edit_route_unavailable' => 'مسار تعديل مكالمة الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deals_labels_route_unavailable' => 'مسار تسميات الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_destroy_route_unavailable' => 'مسار حذف الصفقة غير متاح. ' . self::DELEGATION_AR,
				'labels_store_route_unavailable' => 'مسار تخزين التسميات غير متاح. ' . self::DELEGATION_AR,
				'deal_client_permissions_store_route_unavailable' => 'مسار تخزين أذونات عميل الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_products_update_route_unavailable' => 'مسار تحديث منتجات الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_sources_update_route_unavailable' => 'مسار تحديث مصادر الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deals_index_route_unavailable' => 'مسار فهرس الصفقات غير متاح. ' . self::DELEGATION_AR,
				'deal_tasks_update_route_unavailable' => 'مسار تحديث مهمة الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_tasks_store_route_unavailable' => 'مسار إنشاء مهمة الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_users_update_route_unavailable' => 'مسار تحديث مستخدمي الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_show_convert_route_unavailable' => 'مسار تحويل الصفقة غير متاح. ' . self::DELEGATION_AR,
				'section_anchor_deal_unavailable' => 'مرساة قسم الصفقة غير متاحة. ' . self::DELEGATION_AR,
				'deal_users_edit_route_unavailable' => 'مسار إضافة مستخدم في الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_products_edit_route_unavailable' => 'مسار إضافة منتج غير متاح. ' . self::DELEGATION_AR,
				'users_destroy_route_unavailable' => 'مسار حذف مستخدم الصفقة غير متاح. ' . self::DELEGATION_AR,
				'products_destroy_route_unavailable' => 'مسار حذف منتج الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_sources_edit_route_unavailable' => 'مسار إضافة مصدر غير متاح. ' . self::DELEGATION_AR,
				'deal_sources_destroy_route_unavailable' => 'مسار حذف مصدر الصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_calls_create_route_unavailable' => 'مسار إضافة مكالمة للصفقة غير متاح. ' . self::DELEGATION_AR,
				'deal_calls_destroy_route_unavailable' => 'مسار حذف المكالمة غير متاح. ' . self::DELEGATION_AR,
				'emails_create_route_unavailable' => 'مسار إنشاء بريد إلكتروني للصفقة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'calls_update_route_unavailable' => 'Opdater opkaldsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'calls_store_route_unavailable' => 'Opkaldslagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'generate_route_unavailable' => 'Generer indhold til aftaler med AI-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'grammar_route_unavailable' => 'Grammatiktjek med AI-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'clients_update_route_unavailable' => 'Opdater aftaler med klientrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'clients_index_route_unavailable' => 'Klientindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'discussion_store_route_unavailable' => 'Diskussionslagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'discussions_create_route_unavailable' => 'Opret aftalediskussionsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_update_route_unavailable' => 'Opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'change_pipeline_deal_route_unavailable' => 'Aftaleændrings pipeline-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deals_list_route_unavailable' => 'Aftalelisterute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deals_create_route_unavailable' => 'Opret aftalerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_emails_store_route_unavailable' => 'Aftale e-mails lagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_show_route_unavailable' => 'Aftalevisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deals_edit_route_unavailable' => 'Aftaleredigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'calls_edit_route_unavailable' => 'Aftaleopkaldsredigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deals_labels_route_unavailable' => 'Aftaleetiketterrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_destroy_route_unavailable' => 'Slet aftalerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'labels_store_route_unavailable' => 'Etiketlagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_client_permissions_store_route_unavailable' => 'Aftale klienttilladelseslagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_products_update_route_unavailable' => 'Aftaleproduktopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_sources_update_route_unavailable' => 'Aftalekildeopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deals_index_route_unavailable' => 'Aftaleindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_tasks_update_route_unavailable' => 'Opdater aftaleopgaverute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_tasks_store_route_unavailable' => 'Opret aftaleopgaverute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_users_update_route_unavailable' => 'Aftale brugere opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_show_convert_route_unavailable' => 'Konverter aftalerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'section_anchor_deal_unavailable' => 'Aftalesektionsanker er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_users_edit_route_unavailable' => 'Tilføj bruger i aftalerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_products_edit_route_unavailable' => 'Tilføj produktroute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'users_destroy_route_unavailable' => 'Slet aftale brugerrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'products_destroy_route_unavailable' => 'Slet aftaleproduktrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_sources_edit_route_unavailable' => 'Tilføj kilde rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_sources_destroy_route_unavailable' => 'Slet aftalekilderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_calls_create_route_unavailable' => 'Tilføj opkaldsrute for aftale er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_calls_destroy_route_unavailable' => 'Slet opkaldsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'emails_create_route_unavailable' => 'Opret aftale e-mail rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'calls_update_route_unavailable' => 'Anruf-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'calls_store_route_unavailable' => 'Anruf-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'generate_route_unavailable' => 'Inhaltsgenerierung für Deals mit KI-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'grammar_route_unavailable' => 'Grammatikprüfung mit KI-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'clients_update_route_unavailable' => 'Deal-Aktualisierung mit Kundenroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'clients_index_route_unavailable' => 'Kundenindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'discussion_store_route_unavailable' => 'Diskussions-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'discussions_create_route_unavailable' => 'Deal-Diskussionserstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_update_route_unavailable' => 'Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'change_pipeline_deal_route_unavailable' => 'Deal-Pipeline-Änderungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deals_list_route_unavailable' => 'Deal-Listenroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deals_create_route_unavailable' => 'Deal-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_emails_store_route_unavailable' => 'Deal-E-Mail-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_show_route_unavailable' => 'Deal-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deals_edit_route_unavailable' => 'Deal-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'calls_edit_route_unavailable' => 'Deal-Anrufbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deals_labels_route_unavailable' => 'Deal-Labelroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_destroy_route_unavailable' => 'Deal-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'labels_store_route_unavailable' => 'Label-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_client_permissions_store_route_unavailable' => 'Deal-Kundenberechtigungs-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_products_update_route_unavailable' => 'Deal-Produktaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_sources_update_route_unavailable' => 'Deal-Quellenaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deals_index_route_unavailable' => 'Deal-Indexroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_tasks_update_route_unavailable' => 'Deal-Aufgabenaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_tasks_store_route_unavailable' => 'Deal-Aufgabenerstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_users_update_route_unavailable' => 'Deal-Benutzeraktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_show_convert_route_unavailable' => 'Deal-Konvertierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'section_anchor_deal_unavailable' => 'Deal-Bereichsanker ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_users_edit_route_unavailable' => 'Benutzerhinzufügungsroute für Deal ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_products_edit_route_unavailable' => 'Produkthinzufügungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'users_destroy_route_unavailable' => 'Deal-Benutzerlöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'products_destroy_route_unavailable' => 'Deal-Produktlöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_sources_edit_route_unavailable' => 'Quellenhinzufügungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_sources_destroy_route_unavailable' => 'Deal-Quellenlöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_calls_create_route_unavailable' => 'Anrufhinzufügungsroute für Deal ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_calls_destroy_route_unavailable' => 'Anruflöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'emails_create_route_unavailable' => 'Deal-E-Mail-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'calls_update_route_unavailable' => 'Update call route is unavailable. ' . self::DELEGATION_EN,
				'calls_store_route_unavailable' => 'Call store route is unavailable. ' . self::DELEGATION_EN,
				'generate_route_unavailable' => 'Generate content for deals with AI route is unavailable. ' . self::DELEGATION_EN,
				'grammar_route_unavailable' => 'Grammar check with AI route is unavailable. ' . self::DELEGATION_EN,
				'clients_update_route_unavailable' => 'Update deals with clients route is unavailable. ' . self::DELEGATION_EN,
				'clients_index_route_unavailable' => 'Clients index route is unavailable. ' . self::DELEGATION_EN,
				'discussion_store_route_unavailable' => 'Discussion store route is unavailable. ' . self::DELEGATION_EN,
				'discussions_create_route_unavailable' => 'Deal discussions create route is unavailable. ' . self::DELEGATION_EN,
				'deal_update_route_unavailable' => 'Update route is unavailable. ' . self::DELEGATION_EN,
				'change_pipeline_deal_route_unavailable' => 'Deal change pipeline route is unavailable. ' . self::DELEGATION_EN,
				'deals_list_route_unavailable' => 'Deal list route is unavailable. ' . self::DELEGATION_EN,
				'deals_create_route_unavailable' => 'Deal create route is unavailable. ' . self::DELEGATION_EN,
				'deal_emails_store_route_unavailable' => 'Deal emails store route is unavailable. ' . self::DELEGATION_EN,
				'deal_show_route_unavailable' => 'Deal show route is unavailable. ' . self::DELEGATION_EN,
				'deals_edit_route_unavailable' => 'Deal edit route is unavailable. ' . self::DELEGATION_EN,
				'calls_edit_route_unavailable' => 'Deal call edit route is unavailable. ' . self::DELEGATION_EN,
				'deals_labels_route_unavailable' => 'Deal labels route is unavailable. ' . self::DELEGATION_EN,
				'deal_destroy_route_unavailable' => 'Delete deal route is unavailable. ' . self::DELEGATION_EN,
				'labels_store_route_unavailable' => 'Labels store route is unavailable. ' . self::DELEGATION_EN,
				'deal_client_permissions_store_route_unavailable' => 'Deal client permissions store route is unavailable. ' . self::DELEGATION_EN,
				'deal_products_update_route_unavailable' => 'Deal products update route is unavailable. ' . self::DELEGATION_EN,
				'deal_sources_update_route_unavailable' => 'Deal sources update route is unavailable. ' . self::DELEGATION_EN,
				'deals_index_route_unavailable' => 'Deal index route is unavailable. ' . self::DELEGATION_EN,
				'deal_tasks_update_route_unavailable' => 'Update deal task route is unavailable. ' . self::DELEGATION_EN,
				'deal_tasks_store_route_unavailable' => 'Create deal task route is unavailable. ' . self::DELEGATION_EN,
				'deal_users_update_route_unavailable' => 'Deal users update route is unavailable. ' . self::DELEGATION_EN,
				'deal_show_convert_route_unavailable' => 'Convert deal route is unavailable. ' . self::DELEGATION_EN,
				'section_anchor_deal_unavailable' => 'Deal section anchor is unavailable. ' . self::DELEGATION_EN,
				'deal_users_edit_route_unavailable' => 'Add user in deal route is unavailable. ' . self::DELEGATION_EN,
				'deal_products_edit_route_unavailable' => 'Add product route is unavailable. ' . self::DELEGATION_EN,
				'users_destroy_route_unavailable' => 'Deal user delete route is unavailable. ' . self::DELEGATION_EN,
				'products_destroy_route_unavailable' => 'Deal product delete route is unavailable. ' . self::DELEGATION_EN,
				'deal_sources_edit_route_unavailable' => 'Add source route is unavailable. ' . self::DELEGATION_EN,
				'deal_sources_destroy_route_unavailable' => 'Delete deal source route is unavailable. ' . self::DELEGATION_EN,
				'deal_calls_create_route_unavailable' => 'Add call route for deal is unavailable. ' . self::DELEGATION_EN,
				'deal_calls_destroy_route_unavailable' => 'Delete call route is unavailable. ' . self::DELEGATION_EN,
				'emails_create_route_unavailable' => 'Deal email create route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'calls_update_route_unavailable' => 'La ruta de actualización de llamadas no está disponible. ' . self::DELEGATION_ES,
				'calls_store_route_unavailable' => 'La ruta de almacenamiento de llamadas no está disponible. ' . self::DELEGATION_ES,
				'generate_route_unavailable' => 'La ruta de generación de contenido para tratos con IA no está disponible. ' . self::DELEGATION_ES,
				'grammar_route_unavailable' => 'La ruta de verificación gramatical con IA no está disponible. ' . self::DELEGATION_ES,
				'clients_update_route_unavailable' => 'La ruta de actualización de tratos con clientes no está disponible. ' . self::DELEGATION_ES,
				'clients_index_route_unavailable' => 'La ruta de índice de clientes no está disponible. ' . self::DELEGATION_ES,
				'discussion_store_route_unavailable' => 'La ruta de almacenamiento de discusión no está disponible. ' . self::DELEGATION_ES,
				'discussions_create_route_unavailable' => 'La ruta de creación de discusiones de tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_update_route_unavailable' => 'La ruta de actualización no está disponible. ' . self::DELEGATION_ES,
				'change_pipeline_deal_route_unavailable' => 'La ruta de cambio de pipeline para tratos no está disponible. ' . self::DELEGATION_ES,
				'deals_list_route_unavailable' => 'La ruta de lista de tratos no está disponible. ' . self::DELEGATION_ES,
				'deals_create_route_unavailable' => 'La ruta de creación de tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_emails_store_route_unavailable' => 'La ruta de almacenamiento de correos de tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_show_route_unavailable' => 'La ruta de visualización de tratos no está disponible. ' . self::DELEGATION_ES,
				'deals_edit_route_unavailable' => 'La ruta de edición de tratos no está disponible. ' . self::DELEGATION_ES,
				'calls_edit_route_unavailable' => 'La ruta de edición de llamadas para tratos no está disponible. ' . self::DELEGATION_ES,
				'deals_labels_route_unavailable' => 'La ruta de etiquetas de tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_destroy_route_unavailable' => 'La ruta de eliminación de tratos no está disponible. ' . self::DELEGATION_ES,
				'labels_store_route_unavailable' => 'La ruta de almacenamiento de etiquetas no está disponible. ' . self::DELEGATION_ES,
				'deal_client_permissions_store_route_unavailable' => 'La ruta de almacenamiento de permisos de clientes para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_products_update_route_unavailable' => 'La ruta de actualización de productos para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_sources_update_route_unavailable' => 'La ruta de actualización de fuentes para tratos no está disponible. ' . self::DELEGATION_ES,
				'deals_index_route_unavailable' => 'La ruta de índice de tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_tasks_update_route_unavailable' => 'La ruta de actualización de tareas para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_tasks_store_route_unavailable' => 'La ruta de creación de tareas para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_users_update_route_unavailable' => 'La ruta de actualización de usuarios para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_show_convert_route_unavailable' => 'La ruta de conversión de tratos no está disponible. ' . self::DELEGATION_ES,
				'section_anchor_deal_unavailable' => 'El anclaje de sección para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_users_edit_route_unavailable' => 'La ruta para agregar usuarios en tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_products_edit_route_unavailable' => 'La ruta para agregar productos no está disponible. ' . self::DELEGATION_ES,
				'users_destroy_route_unavailable' => 'La ruta de eliminación de usuarios para tratos no está disponible. ' . self::DELEGATION_ES,
				'products_destroy_route_unavailable' => 'La ruta de eliminación de productos para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_sources_edit_route_unavailable' => 'La ruta para agregar fuentes no está disponible. ' . self::DELEGATION_ES,
				'deal_sources_destroy_route_unavailable' => 'La ruta de eliminación de fuentes para tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_calls_create_route_unavailable' => 'La ruta para agregar llamadas a tratos no está disponible. ' . self::DELEGATION_ES,
				'deal_calls_destroy_route_unavailable' => 'La ruta de eliminación de llamadas no está disponible. ' . self::DELEGATION_ES,
				'emails_create_route_unavailable' => 'La ruta de creación de correos para tratos no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'calls_update_route_unavailable' => 'La route de mise à jour des appels n\'est pas disponible. ' . self::DELEGATION_FR,
				'calls_store_route_unavailable' => 'La route de stockage des appels n\'est pas disponible. ' . self::DELEGATION_FR,
				'generate_route_unavailable' => 'La route de génération de contenu pour les deals avec IA n\'est pas disponible. ' . self::DELEGATION_FR,
				'grammar_route_unavailable' => 'La route de vérification grammaticale avec IA n\'est pas disponible. ' . self::DELEGATION_FR,
				'clients_update_route_unavailable' => 'La route de mise à jour des deals avec clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'clients_index_route_unavailable' => 'La route d\'index des clients n\'est pas disponible. ' . self::DELEGATION_FR,
				'discussion_store_route_unavailable' => 'La route de stockage des discussions n\'est pas disponible. ' . self::DELEGATION_FR,
				'discussions_create_route_unavailable' => 'La route de création des discussions de deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_update_route_unavailable' => 'La route de mise à jour n\'est pas disponible. ' . self::DELEGATION_FR,
				'change_pipeline_deal_route_unavailable' => 'La route de changement de pipeline pour les deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deals_list_route_unavailable' => 'La route de liste des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deals_create_route_unavailable' => 'La route de création des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_emails_store_route_unavailable' => 'La route de stockage des emails des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_show_route_unavailable' => 'La route d\'affichage des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deals_edit_route_unavailable' => 'La route d\'édition des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'calls_edit_route_unavailable' => 'La route d\'édition des appels pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deals_labels_route_unavailable' => 'La route des étiquettes des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_destroy_route_unavailable' => 'La route de suppression des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'labels_store_route_unavailable' => 'La route de stockage des étiquettes n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_client_permissions_store_route_unavailable' => 'La route de stockage des permissions clients pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_products_update_route_unavailable' => 'La route de mise à jour des produits pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_sources_update_route_unavailable' => 'La route de mise à jour des sources pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deals_index_route_unavailable' => 'La route d\'index des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_tasks_update_route_unavailable' => 'La route de mise à jour des tâches pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_tasks_store_route_unavailable' => 'La route de création des tâches pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_users_update_route_unavailable' => 'La route de mise à jour des utilisateurs pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_show_convert_route_unavailable' => 'La route de conversion des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'section_anchor_deal_unavailable' => 'L\'ancre de section des deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_users_edit_route_unavailable' => 'La route d\'ajout d\'utilisateur dans les deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_products_edit_route_unavailable' => 'La route d\'ajout de produit n\'est pas disponible. ' . self::DELEGATION_FR,
				'users_destroy_route_unavailable' => 'La route de suppression d\'utilisateur pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'products_destroy_route_unavailable' => 'La route de suppression de produit pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_sources_edit_route_unavailable' => 'La route d\'ajout de source n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_sources_destroy_route_unavailable' => 'La route de suppression de source pour deals n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_calls_create_route_unavailable' => 'La route d\'ajout d\'appel pour deal n\'est pas disponible. ' . self::DELEGATION_FR,
				'deal_calls_destroy_route_unavailable' => 'La route de suppression d\'appel n\'est pas disponible. ' . self::DELEGATION_FR,
				'emails_create_route_unavailable' => 'La route de création d\'email pour deals n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'calls_update_route_unavailable' => 'נתיב עדכון השיחה אינו זמין. ' . self::DELEGATION_HE,
				'calls_store_route_unavailable' => 'נתיב אחסון השיחה אינו זמין. ' . self::DELEGATION_HE,
				'generate_route_unavailable' => 'נתיב יצירת תוכן לעסקאות באמצעות בינה מלאכותית אינו זמין. ' . self::DELEGATION_HE,
				'grammar_route_unavailable' => 'נתיב בדיקת דקדוק באמצעות בינה מלאכותית אינו זמין. ' . self::DELEGATION_HE,
				'clients_update_route_unavailable' => 'נתיב עדכון עסקאות עם לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'clients_index_route_unavailable' => 'נתיב אינדקס הלקוחות אינו זמין. ' . self::DELEGATION_HE,
				'discussion_store_route_unavailable' => 'נתיב אחסון הדיון אינו זמין. ' . self::DELEGATION_HE,
				'discussions_create_route_unavailable' => 'נתיב יצירת דיוני עסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_update_route_unavailable' => 'נתיב העדכון אינו זמין. ' . self::DELEGATION_HE,
				'change_pipeline_deal_route_unavailable' => 'נתיב שינוי צינור העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deals_list_route_unavailable' => 'נתיב רשימת העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deals_create_route_unavailable' => 'נתיב יצירת עסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_emails_store_route_unavailable' => 'נתיב אחסון אימיילים לעסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_show_route_unavailable' => 'נתיב הצגת העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deals_edit_route_unavailable' => 'נתיב עריכת העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'calls_edit_route_unavailable' => 'נתיב עריכת שיחות לעסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deals_labels_route_unavailable' => 'נתיב תוויות העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_destroy_route_unavailable' => 'נתיב מחיקת העסקה אינו זמין. ' . self::DELEGATION_HE,
				'labels_store_route_unavailable' => 'נתיב אחסון תוויות אינו זמין. ' . self::DELEGATION_HE,
				'deal_client_permissions_store_route_unavailable' => 'נתיב אחסון הרשאות לקוח לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deal_products_update_route_unavailable' => 'נתיב עדכון מוצרים לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deal_sources_update_route_unavailable' => 'נתיב עדכון מקורות לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deals_index_route_unavailable' => 'נתיב אינדקס העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_tasks_update_route_unavailable' => 'נתיב עדכון משימות לעסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_tasks_store_route_unavailable' => 'נתיב יצירת משימות לעסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_users_update_route_unavailable' => 'נתיב עדכון משתמשים לעסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_show_convert_route_unavailable' => 'נתיב המרת העסקה אינו זמין. ' . self::DELEGATION_HE,
				'section_anchor_deal_unavailable' => 'עוגן קטע העסקאות אינו זמין. ' . self::DELEGATION_HE,
				'deal_users_edit_route_unavailable' => 'נתיב הוספת משתמש לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deal_products_edit_route_unavailable' => 'נתיב הוספת מוצר אינו זמין. ' . self::DELEGATION_HE,
				'users_destroy_route_unavailable' => 'נתיב מחיקת משתמש לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'products_destroy_route_unavailable' => 'נתיב מחיקת מוצר לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deal_sources_edit_route_unavailable' => 'נתיב הוספת מקור אינו זמין. ' . self::DELEGATION_HE,
				'deal_sources_destroy_route_unavailable' => 'נתיב מחיקת מקור לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deal_calls_create_route_unavailable' => 'נתיב הוספת שיחה לעסקה אינו זמין. ' . self::DELEGATION_HE,
				'deal_calls_destroy_route_unavailable' => 'נתיב מחיקת שיחה אינו זמין. ' . self::DELEGATION_HE,
				'emails_create_route_unavailable' => 'נתיב יצירת אימייל לעסקה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'calls_update_route_unavailable' => 'La rotta di aggiornamento delle chiamate non è disponibile. ' . self::DELEGATION_IT,
				'calls_store_route_unavailable' => 'La rotta di memorizzazione delle chiamate non è disponibile. ' . self::DELEGATION_IT,
				'generate_route_unavailable' => 'La rotta di generazione contenuti per affari con IA non è disponibile. ' . self::DELEGATION_IT,
				'grammar_route_unavailable' => 'La rotta di controllo grammaticale con IA non è disponibile. ' . self::DELEGATION_IT,
				'clients_update_route_unavailable' => 'La rotta di aggiornamento affari con clienti non è disponibile. ' . self::DELEGATION_IT,
				'clients_index_route_unavailable' => 'La rotta dell\'indice clienti non è disponibile. ' . self::DELEGATION_IT,
				'discussion_store_route_unavailable' => 'La rotta di memorizzazione discussioni non è disponibile. ' . self::DELEGATION_IT,
				'discussions_create_route_unavailable' => 'La rotta di creazione discussioni per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_update_route_unavailable' => 'La rotta di aggiornamento non è disponibile. ' . self::DELEGATION_IT,
				'change_pipeline_deal_route_unavailable' => 'La rotta di cambio pipeline per affari non è disponibile. ' . self::DELEGATION_IT,
				'deals_list_route_unavailable' => 'La rotta dell\'elenco affari non è disponibile. ' . self::DELEGATION_IT,
				'deals_create_route_unavailable' => 'La rotta di creazione affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_emails_store_route_unavailable' => 'La rotta di memorizzazione email per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_show_route_unavailable' => 'La rotta di visualizzazione affari non è disponibile. ' . self::DELEGATION_IT,
				'deals_edit_route_unavailable' => 'La rotta di modifica affari non è disponibile. ' . self::DELEGATION_IT,
				'calls_edit_route_unavailable' => 'La rotta di modifica chiamate per affari non è disponibile. ' . self::DELEGATION_IT,
				'deals_labels_route_unavailable' => 'La rotta delle etichette per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_destroy_route_unavailable' => 'La rotta di eliminazione affari non è disponibile. ' . self::DELEGATION_IT,
				'labels_store_route_unavailable' => 'La rotta di memorizzazione etichette non è disponibile. ' . self::DELEGATION_IT,
				'deal_client_permissions_store_route_unavailable' => 'La rotta di memorizzazione permessi clienti per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_products_update_route_unavailable' => 'La rotta di aggiornamento prodotti per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_sources_update_route_unavailable' => 'La rotta di aggiornamento fonti per affari non è disponibile. ' . self::DELEGATION_IT,
				'deals_index_route_unavailable' => 'La rotta dell\'indice affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_tasks_update_route_unavailable' => 'La rotta di aggiornamento attività per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_tasks_store_route_unavailable' => 'La rotta di creazione attività per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_users_update_route_unavailable' => 'La rotta di aggiornamento utenti per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_show_convert_route_unavailable' => 'La rotta di conversione affari non è disponibile. ' . self::DELEGATION_IT,
				'section_anchor_deal_unavailable' => 'L\'ancora di sezione per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_users_edit_route_unavailable' => 'La rotta per aggiungere utenti negli affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_products_edit_route_unavailable' => 'La rotta per aggiungere prodotti non è disponibile. ' . self::DELEGATION_IT,
				'users_destroy_route_unavailable' => 'La rotta di eliminazione utenti per affari non è disponibile. ' . self::DELEGATION_IT,
				'products_destroy_route_unavailable' => 'La rotta di eliminazione prodotti per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_sources_edit_route_unavailable' => 'La rotta per aggiungere fonti non è disponibile. ' . self::DELEGATION_IT,
				'deal_sources_destroy_route_unavailable' => 'La rotta di eliminazione fonti per affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_calls_create_route_unavailable' => 'La rotta per aggiungere chiamate agli affari non è disponibile. ' . self::DELEGATION_IT,
				'deal_calls_destroy_route_unavailable' => 'La rotta di eliminazione chiamate non è disponibile. ' . self::DELEGATION_IT,
				'emails_create_route_unavailable' => 'La rotta di creazione email per affari non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'calls_update_route_unavailable' => '通話更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'calls_store_route_unavailable' => '通話保存ルートは利用できません。 ' . self::DELEGATION_JA,
				'generate_route_unavailable' => 'AIによるディール向けコンテンツ生成ルートは利用できません。 ' . self::DELEGATION_JA,
				'grammar_route_unavailable' => 'AIによる文法チェックルートは利用できません。 ' . self::DELEGATION_JA,
				'clients_update_route_unavailable' => 'クライアントとのディール更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'clients_index_route_unavailable' => 'クライアント索引ルートは利用できません。 ' . self::DELEGATION_JA,
				'discussion_store_route_unavailable' => 'ディスカッション保存ルートは利用できません。 ' . self::DELEGATION_JA,
				'discussions_create_route_unavailable' => 'ディールディスカッション作成ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_update_route_unavailable' => '更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'change_pipeline_deal_route_unavailable' => 'ディールパイプライン変更ルートは利用できません。 ' . self::DELEGATION_JA,
				'deals_list_route_unavailable' => 'ディール一覧ルートは利用できません。 ' . self::DELEGATION_JA,
				'deals_create_route_unavailable' => 'ディール作成ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_emails_store_route_unavailable' => 'ディールメール保存ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_show_route_unavailable' => 'ディール表示ルートは利用できません。 ' . self::DELEGATION_JA,
				'deals_edit_route_unavailable' => 'ディール編集ルートは利用できません。 ' . self::DELEGATION_JA,
				'calls_edit_route_unavailable' => 'ディール通話編集ルートは利用できません。 ' . self::DELEGATION_JA,
				'deals_labels_route_unavailable' => 'ディールラベルルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_destroy_route_unavailable' => 'ディール削除ルートは利用できません。 ' . self::DELEGATION_JA,
				'labels_store_route_unavailable' => 'ラベル保存ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_client_permissions_store_route_unavailable' => 'ディールクライアント権限保存ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_products_update_route_unavailable' => 'ディール商品更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_sources_update_route_unavailable' => 'ディールソース更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'deals_index_route_unavailable' => 'ディール索引ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_tasks_update_route_unavailable' => 'ディールタスク更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_tasks_store_route_unavailable' => 'ディールタスク作成ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_users_update_route_unavailable' => 'ディールユーザー更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_show_convert_route_unavailable' => 'ディール変換ルートは利用できません。 ' . self::DELEGATION_JA,
				'section_anchor_deal_unavailable' => 'ディールセクションアンカーは利用できません。 ' . self::DELEGATION_JA,
				'deal_users_edit_route_unavailable' => 'ディールへのユーザー追加ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_products_edit_route_unavailable' => '商品追加ルートは利用できません。 ' . self::DELEGATION_JA,
				'users_destroy_route_unavailable' => 'ディールユーザー削除ルートは利用できません。 ' . self::DELEGATION_JA,
				'products_destroy_route_unavailable' => 'ディール商品削除ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_sources_edit_route_unavailable' => 'ソース追加ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_sources_destroy_route_unavailable' => 'ディールソース削除ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_calls_create_route_unavailable' => 'ディールへの通話追加ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_calls_destroy_route_unavailable' => '通話削除ルートは利用できません。 ' . self::DELEGATION_JA,
				'emails_create_route_unavailable' => 'ディールメール作成ルートは利用できません。 ' . self::DELEGATION_JA,
				'deal_index_route_unavailable' => 'ディール設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'calls_update_route_unavailable' => 'Oproepupdate route is niet beschikbaar. ' . self::DELEGATION_NL,
				'calls_store_route_unavailable' => 'Oproepopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'generate_route_unavailable' => 'Inhoud genereren voor deals met AI-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'grammar_route_unavailable' => 'Grammaticacontrole met AI-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'clients_update_route_unavailable' => 'Deals bijwerken met klantroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'clients_index_route_unavailable' => 'Klantenindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'discussion_store_route_unavailable' => 'Discussieopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'discussions_create_route_unavailable' => 'Dealdiscussie aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_update_route_unavailable' => 'Updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'change_pipeline_deal_route_unavailable' => 'Deal pijplijnwijzigingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deals_list_route_unavailable' => 'Deallijstroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deals_create_route_unavailable' => 'Deal aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_emails_store_route_unavailable' => 'Deal e-mail opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_show_route_unavailable' => 'Deal weergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deals_edit_route_unavailable' => 'Deal bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'calls_edit_route_unavailable' => 'Deal oproep bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deals_labels_route_unavailable' => 'Deal labelsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_destroy_route_unavailable' => 'Deal verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'labels_store_route_unavailable' => 'Label opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_client_permissions_store_route_unavailable' => 'Deal klantmachtigingsopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_products_update_route_unavailable' => 'Deal productupdate route is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_sources_update_route_unavailable' => 'Deal bronupdate route is niet beschikbaar. ' . self::DELEGATION_NL,
				'deals_index_route_unavailable' => 'Deal indexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_tasks_update_route_unavailable' => 'Deal taakupdate route is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_tasks_store_route_unavailable' => 'Deal taak aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_users_update_route_unavailable' => 'Deal gebruikersupdate route is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_show_convert_route_unavailable' => 'Deal conversieroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'section_anchor_deal_unavailable' => 'Deal sectie-ananker is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_users_edit_route_unavailable' => 'Gebruiker toevoegen in dealroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_products_edit_route_unavailable' => 'Product toevoegroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'users_destroy_route_unavailable' => 'Deal gebruikerverwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'products_destroy_route_unavailable' => 'Deal productverwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_sources_edit_route_unavailable' => 'Bron toevoegroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_sources_destroy_route_unavailable' => 'Deal bronverwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_calls_create_route_unavailable' => 'Oproep toevoegroute voor deal is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_calls_destroy_route_unavailable' => 'Oproep verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'emails_create_route_unavailable' => 'Deal e-mail aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_index_route_unavailable' => 'Deal-installatieroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'calls_update_route_unavailable' => 'Trasa aktualizacji połączeń jest niedostępna. ' . self::DELEGATION_PL,
				'calls_store_route_unavailable' => 'Trasa zapisu połączeń jest niedostępna. ' . self::DELEGATION_PL,
				'generate_route_unavailable' => 'Trasa generowania treści dla transakcji z użyciem AI jest niedostępna. ' . self::DELEGATION_PL,
				'grammar_route_unavailable' => 'Trasa sprawdzania gramatyki z użyciem AI jest niedostępna. ' . self::DELEGATION_PL,
				'clients_update_route_unavailable' => 'Trasa aktualizacji transakcji z klientami jest niedostępna. ' . self::DELEGATION_PL,
				'clients_index_route_unavailable' => 'Trasa indeksu klientów jest niedostępna. ' . self::DELEGATION_PL,
				'discussion_store_route_unavailable' => 'Trasa zapisu dyskusji jest niedostępna. ' . self::DELEGATION_PL,
				'discussions_create_route_unavailable' => 'Trasa tworzenia dyskusji transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_update_route_unavailable' => 'Trasa aktualizacji jest niedostępna. ' . self::DELEGATION_PL,
				'change_pipeline_deal_route_unavailable' => 'Trasa zmiany procesu transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deals_list_route_unavailable' => 'Trasa listy transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deals_create_route_unavailable' => 'Trasa tworzenia transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_emails_store_route_unavailable' => 'Trasa zapisu e-maili transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_show_route_unavailable' => 'Trasa wyświetlania transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deals_edit_route_unavailable' => 'Trasa edycji transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'calls_edit_route_unavailable' => 'Trasa edycji połączeń transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deals_labels_route_unavailable' => 'Trasa etykiet transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_destroy_route_unavailable' => 'Trasa usuwania transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'labels_store_route_unavailable' => 'Trasa zapisu etykiet jest niedostępna. ' . self::DELEGATION_PL,
				'deal_client_permissions_store_route_unavailable' => 'Trasa zapisu uprawnień klienta transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_products_update_route_unavailable' => 'Trasa aktualizacji produktów transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_sources_update_route_unavailable' => 'Trasa aktualizacji źródeł transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deals_index_route_unavailable' => 'Trasa indeksu transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_tasks_update_route_unavailable' => 'Trasa aktualizacji zadań transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_tasks_store_route_unavailable' => 'Trasa tworzenia zadań transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_users_update_route_unavailable' => 'Trasa aktualizacji użytkowników transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_show_convert_route_unavailable' => 'Trasa konwersji transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'section_anchor_deal_unavailable' => 'Kotwicza sekcja transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_users_edit_route_unavailable' => 'Trasa dodawania użytkownika w transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_products_edit_route_unavailable' => 'Trasa dodawania produktu jest niedostępna. ' . self::DELEGATION_PL,
				'users_destroy_route_unavailable' => 'Trasa usuwania użytkownika transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'products_destroy_route_unavailable' => 'Trasa usuwania produktu transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_sources_edit_route_unavailable' => 'Trasa dodawania źródła jest niedostępna. ' . self::DELEGATION_PL,
				'deal_sources_destroy_route_unavailable' => 'Trasa usuwania źródła transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_calls_create_route_unavailable' => 'Trasa dodawania połączenia do transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_calls_destroy_route_unavailable' => 'Trasa usuwania połączenia jest niedostępna. ' . self::DELEGATION_PL,
				'emails_create_route_unavailable' => 'Trasa tworzenia e-maila transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'deal_index_route_unavailable' => 'Trasa konfiguracji transakcji jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'calls_update_route_unavailable' => 'A rota de atualização de chamadas não está disponível. ' . self::DELEGATION_PT,
				'calls_store_route_unavailable' => 'A rota de armazenamento de chamadas não está disponível. ' . self::DELEGATION_PT,
				'generate_route_unavailable' => 'A rota de geração de conteúdo para negócios com IA não está disponível. ' . self::DELEGATION_PT,
				'grammar_route_unavailable' => 'A rota de verificação gramatical com IA não está disponível. ' . self::DELEGATION_PT,
				'clients_update_route_unavailable' => 'A rota de atualização de negócios com clientes não está disponível. ' . self::DELEGATION_PT,
				'clients_index_route_unavailable' => 'A rota de índice de clientes não está disponível. ' . self::DELEGATION_PT,
				'discussion_store_route_unavailable' => 'A rota de armazenamento de discussão não está disponível. ' . self::DELEGATION_PT,
				'discussions_create_route_unavailable' => 'A rota de criação de discussões de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_update_route_unavailable' => 'A rota de atualização não está disponível. ' . self::DELEGATION_PT,
				'change_pipeline_deal_route_unavailable' => 'A rota de alteração de pipeline de negócios não está disponível. ' . self::DELEGATION_PT,
				'deals_list_route_unavailable' => 'A rota de lista de negócios não está disponível. ' . self::DELEGATION_PT,
				'deals_create_route_unavailable' => 'A rota de criação de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_emails_store_route_unavailable' => 'A rota de armazenamento de e-mails de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_show_route_unavailable' => 'A rota de exibição de negócios não está disponível. ' . self::DELEGATION_PT,
				'deals_edit_route_unavailable' => 'A rota de edição de negócios não está disponível. ' . self::DELEGATION_PT,
				'calls_edit_route_unavailable' => 'A rota de edição de chamadas de negócios não está disponível. ' . self::DELEGATION_PT,
				'deals_labels_route_unavailable' => 'A rota de etiquetas de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_destroy_route_unavailable' => 'A rota de exclusão de negócios não está disponível. ' . self::DELEGATION_PT,
				'labels_store_route_unavailable' => 'A rota de armazenamento de etiquetas não está disponível. ' . self::DELEGATION_PT,
				'deal_client_permissions_store_route_unavailable' => 'A rota de armazenamento de permissões de cliente de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_products_update_route_unavailable' => 'A rota de atualização de produtos de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_sources_update_route_unavailable' => 'A rota de atualização de fontes de negócios não está disponível. ' . self::DELEGATION_PT,
				'deals_index_route_unavailable' => 'A rota de índice de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_tasks_update_route_unavailable' => 'A rota de atualização de tarefas de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_tasks_store_route_unavailable' => 'A rota de criação de tarefas de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_users_update_route_unavailable' => 'A rota de atualização de usuários de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_show_convert_route_unavailable' => 'A rota de conversão de negócios não está disponível. ' . self::DELEGATION_PT,
				'section_anchor_deal_unavailable' => 'A âncora de seção de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_users_edit_route_unavailable' => 'A rota de adição de usuário em negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_products_edit_route_unavailable' => 'A rota de adição de produto não está disponível. ' . self::DELEGATION_PT,
				'users_destroy_route_unavailable' => 'A rota de exclusão de usuário de negócios não está disponível. ' . self::DELEGATION_PT,
				'products_destroy_route_unavailable' => 'A rota de exclusão de produto de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_sources_edit_route_unavailable' => 'A rota de adição de fonte não está disponível. ' . self::DELEGATION_PT,
				'deal_sources_destroy_route_unavailable' => 'A rota de exclusão de fonte de negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_calls_create_route_unavailable' => 'A rota de adição de chamada para negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_calls_destroy_route_unavailable' => 'A rota de exclusão de chamada não está disponível. ' . self::DELEGATION_PT,
				'emails_create_route_unavailable' => 'A rota de criação de e-mail para negócios não está disponível. ' . self::DELEGATION_PT,
				'deal_index_route_unavailable' => 'A rota de configuração de negócios não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'calls_update_route_unavailable' => 'A rota de atualização de chamadas não está disponível. ' . self::DELEGATION_PTBR,
				'calls_store_route_unavailable' => 'A rota de armazenamento de chamadas não está disponível. ' . self::DELEGATION_PTBR,
				'generate_route_unavailable' => 'A rota de geração de conteúdo para negócios com IA não está disponível. ' . self::DELEGATION_PTBR,
				'grammar_route_unavailable' => 'A rota de verificação gramatical com IA não está disponível. ' . self::DELEGATION_PTBR,
				'clients_update_route_unavailable' => 'A rota de atualização de negócios com clientes não está disponível. ' . self::DELEGATION_PTBR,
				'clients_index_route_unavailable' => 'A rota de índice de clientes não está disponível. ' . self::DELEGATION_PTBR,
				'discussion_store_route_unavailable' => 'A rota de armazenamento de discussão não está disponível. ' . self::DELEGATION_PTBR,
				'discussions_create_route_unavailable' => 'A rota de criação de discussões de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_update_route_unavailable' => 'A rota de atualização não está disponível. ' . self::DELEGATION_PTBR,
				'change_pipeline_deal_route_unavailable' => 'A rota de alteração de pipeline de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deals_list_route_unavailable' => 'A rota de lista de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deals_create_route_unavailable' => 'A rota de criação de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_emails_store_route_unavailable' => 'A rota de armazenamento de e-mails de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_show_route_unavailable' => 'A rota de exibição de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deals_edit_route_unavailable' => 'A rota de edição de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'calls_edit_route_unavailable' => 'A rota de edição de chamadas de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deals_labels_route_unavailable' => 'A rota de etiquetas de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_destroy_route_unavailable' => 'A rota de exclusão de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'labels_store_route_unavailable' => 'A rota de armazenamento de etiquetas não está disponível. ' . self::DELEGATION_PTBR,
				'deal_client_permissions_store_route_unavailable' => 'A rota de armazenamento de permissões de cliente de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_products_update_route_unavailable' => 'A rota de atualização de produtos de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_sources_update_route_unavailable' => 'A rota de atualização de fontes de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deals_index_route_unavailable' => 'A rota de índice de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_tasks_update_route_unavailable' => 'A rota de atualização de tarefas de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_tasks_store_route_unavailable' => 'A rota de criação de tarefas de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_users_update_route_unavailable' => 'A rota de atualização de usuários de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_show_convert_route_unavailable' => 'A rota de conversão de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'section_anchor_deal_unavailable' => 'A âncora de seção de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_users_edit_route_unavailable' => 'A rota de adição de usuário em negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_products_edit_route_unavailable' => 'A rota de adição de produto não está disponível. ' . self::DELEGATION_PTBR,
				'users_destroy_route_unavailable' => 'A rota de exclusão de usuário de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'products_destroy_route_unavailable' => 'A rota de exclusão de produto de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_sources_edit_route_unavailable' => 'A rota de adição de fonte não está disponível. ' . self::DELEGATION_PTBR,
				'deal_sources_destroy_route_unavailable' => 'A rota de exclusão de fonte de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_calls_create_route_unavailable' => 'A rota de adição de chamada para negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_calls_destroy_route_unavailable' => 'A rota de exclusão de chamada não está disponível. ' . self::DELEGATION_PTBR,
				'emails_create_route_unavailable' => 'A rota de criação de e-mail para negócios não está disponível. ' . self::DELEGATION_PTBR,
				'deal_index_route_unavailable' => 'A rota de configuração de negócios não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'calls_update_route_unavailable' => 'Маршрут обновления звонков недоступен. ' . self::DELEGATION_RU,
				'calls_store_route_unavailable' => 'Маршрут сохранения звонков недоступен. ' . self::DELEGATION_RU,
				'generate_route_unavailable' => 'Маршрут генерации контента для сделок с ИИ недоступен. ' . self::DELEGATION_RU,
				'grammar_route_unavailable' => 'Маршрут проверки грамматики с ИИ недоступен. ' . self::DELEGATION_RU,
				'clients_update_route_unavailable' => 'Маршрут обновления сделок с клиентами недоступен. ' . self::DELEGATION_RU,
				'clients_index_route_unavailable' => 'Маршрут индекса клиентов недоступен. ' . self::DELEGATION_RU,
				'discussion_store_route_unavailable' => 'Маршрут сохранения обсуждения недоступен. ' . self::DELEGATION_RU,
				'discussions_create_route_unavailable' => 'Маршрут создания обсуждений сделок недоступен. ' . self::DELEGATION_RU,
				'deal_update_route_unavailable' => 'Маршрут обновления недоступен. ' . self::DELEGATION_RU,
				'change_pipeline_deal_route_unavailable' => 'Маршрут изменения воронки сделок недоступен. ' . self::DELEGATION_RU,
				'deals_list_route_unavailable' => 'Маршрут списка сделок недоступен. ' . self::DELEGATION_RU,
				'deals_create_route_unavailable' => 'Маршрут создания сделок недоступен. ' . self::DELEGATION_RU,
				'deal_emails_store_route_unavailable' => 'Маршрут сохранения писем сделки недоступен. ' . self::DELEGATION_RU,
				'deal_show_route_unavailable' => 'Маршрут просмотра сделки недоступен. ' . self::DELEGATION_RU,
				'deals_edit_route_unavailable' => 'Маршрут редактирования сделок недоступен. ' . self::DELEGATION_RU,
				'calls_edit_route_unavailable' => 'Маршрут редактирования звонков сделки недоступен. ' . self::DELEGATION_RU,
				'deals_labels_route_unavailable' => 'Маршрут меток сделок недоступен. ' . self::DELEGATION_RU,
				'deal_destroy_route_unavailable' => 'Маршрут удаления сделки недоступен. ' . self::DELEGATION_RU,
				'labels_store_route_unavailable' => 'Маршрут сохранения меток недоступен. ' . self::DELEGATION_RU,
				'deal_client_permissions_store_route_unavailable' => 'Маршрут сохранения прав клиента сделки недоступен. ' . self::DELEGATION_RU,
				'deal_products_update_route_unavailable' => 'Маршрут обновления продуктов сделки недоступен. ' . self::DELEGATION_RU,
				'deal_sources_update_route_unavailable' => 'Маршрут обновления источников сделки недоступен. ' . self::DELEGATION_RU,
				'deals_index_route_unavailable' => 'Маршрут индекса сделок недоступен. ' . self::DELEGATION_RU,
				'deal_tasks_update_route_unavailable' => 'Маршрут обновления задач сделки недоступен. ' . self::DELEGATION_RU,
				'deal_tasks_store_route_unavailable' => 'Маршрут создания задач сделки недоступен. ' . self::DELEGATION_RU,
				'deal_users_update_route_unavailable' => 'Маршрут обновления пользователей сделки недоступен. ' . self::DELEGATION_RU,
				'deal_show_convert_route_unavailable' => 'Маршрут конвертации сделки недоступен. ' . self::DELEGATION_RU,
				'section_anchor_deal_unavailable' => 'Якорь раздела сделки недоступен. ' . self::DELEGATION_RU,
				'deal_users_edit_route_unavailable' => 'Маршрут добавления пользователя в сделку недоступен. ' . self::DELEGATION_RU,
				'deal_products_edit_route_unavailable' => 'Маршрут добавления продукта недоступен. ' . self::DELEGATION_RU,
				'users_destroy_route_unavailable' => 'Маршрут удаления пользователя сделки недоступен. ' . self::DELEGATION_RU,
				'products_destroy_route_unavailable' => 'Маршрут удаления продукта сделки недоступен. ' . self::DELEGATION_RU,
				'deal_sources_edit_route_unavailable' => 'Маршрут добавления источника недоступен. ' . self::DELEGATION_RU,
				'deal_sources_destroy_route_unavailable' => 'Маршрут удаления источника сделки недоступен. ' . self::DELEGATION_RU,
				'deal_calls_create_route_unavailable' => 'Маршрут добавления звонка для сделки недоступен. ' . self::DELEGATION_RU,
				'deal_calls_destroy_route_unavailable' => 'Маршрут удаления звонка недоступен. ' . self::DELEGATION_RU,
				'emails_create_route_unavailable' => 'Маршрут создания письма для сделки недоступен. ' . self::DELEGATION_RU,
				'deal_index_route_unavailable' => 'Маршрут настройки сделок недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'calls_update_route_unavailable' => 'Çağrı güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'calls_store_route_unavailable' => 'Çağrı depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'generate_route_unavailable' => 'Yapay zeka ile anlaşmalar için içerik oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'grammar_route_unavailable' => 'Yapay zeka ile dilbilgisi denetleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'clients_update_route_unavailable' => 'Müşterilerle anlaşmaları güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'clients_index_route_unavailable' => 'Müşteri indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'discussion_store_route_unavailable' => 'Tartışma depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'discussions_create_route_unavailable' => 'Anlaşma tartışmaları oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_update_route_unavailable' => 'Güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'change_pipeline_deal_route_unavailable' => 'Anlaşma boru hattı değiştirme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deals_list_route_unavailable' => 'Anlaşma listesi rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deals_create_route_unavailable' => 'Anlaşma oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_emails_store_route_unavailable' => 'Anlaşma e-postaları depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_show_route_unavailable' => 'Anlaşma görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deals_edit_route_unavailable' => 'Anlaşma düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'calls_edit_route_unavailable' => 'Anlaşma çağrısı düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deals_labels_route_unavailable' => 'Anlaşma etiketleri rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_destroy_route_unavailable' => 'Anlaşma silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'labels_store_route_unavailable' => 'Etiket depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_client_permissions_store_route_unavailable' => 'Anlaşma müşteri izinleri depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_products_update_route_unavailable' => 'Anlaşma ürünleri güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_sources_update_route_unavailable' => 'Anlaşma kaynakları güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deals_index_route_unavailable' => 'Anlaşma indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_tasks_update_route_unavailable' => 'Anlaşma görevi güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_tasks_store_route_unavailable' => 'Anlaşma görevi oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_users_update_route_unavailable' => 'Anlaşma kullanıcıları güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_show_convert_route_unavailable' => 'Anlaşma dönüştürme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'section_anchor_deal_unavailable' => 'Anlaşma bölümü çapası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_users_edit_route_unavailable' => 'Anlaşmada kullanıcı ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_products_edit_route_unavailable' => 'Ürün ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'users_destroy_route_unavailable' => 'Anlaşma kullanıcısı silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'products_destroy_route_unavailable' => 'Anlaşma ürünü silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_sources_edit_route_unavailable' => 'Kaynak ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_sources_destroy_route_unavailable' => 'Anlaşma kaynağı silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_calls_create_route_unavailable' => 'Anlaşma için çağrı ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_calls_destroy_route_unavailable' => 'Çağrı silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'emails_create_route_unavailable' => 'Anlaşma e-postası oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_index_route_unavailable' => 'Anlaşma kurulum rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'calls_update_route_unavailable' => '呼叫更新路由不可用。 ' . self::DELEGATION_ZH,
				'calls_store_route_unavailable' => '呼叫存储路由不可用。 ' . self::DELEGATION_ZH,
				'generate_route_unavailable' => '使用AI生成交易内容的路线不可用。 ' . self::DELEGATION_ZH,
				'grammar_route_unavailable' => '使用AI进行语法检查的路线不可用。 ' . self::DELEGATION_ZH,
				'clients_update_route_unavailable' => '与客户更新交易的路由不可用。 ' . self::DELEGATION_ZH,
				'clients_index_route_unavailable' => '客户索引路由不可用。 ' . self::DELEGATION_ZH,
				'discussion_store_route_unavailable' => '讨论存储路由不可用。 ' . self::DELEGATION_ZH,
				'discussions_create_route_unavailable' => '创建交易讨论的路由不可用。 ' . self::DELEGATION_ZH,
				'deal_update_route_unavailable' => '更新路由不可用。 ' . self::DELEGATION_ZH,
				'change_pipeline_deal_route_unavailable' => '交易流程更改路由不可用。 ' . self::DELEGATION_ZH,
				'deals_list_route_unavailable' => '交易列表路由不可用。 ' . self::DELEGATION_ZH,
				'deals_create_route_unavailable' => '交易创建路由不可用。 ' . self::DELEGATION_ZH,
				'deal_emails_store_route_unavailable' => '交易邮件存储路由不可用。 ' . self::DELEGATION_ZH,
				'deal_show_route_unavailable' => '交易显示路由不可用。 ' . self::DELEGATION_ZH,
				'deals_edit_route_unavailable' => '交易编辑路由不可用。 ' . self::DELEGATION_ZH,
				'calls_edit_route_unavailable' => '交易呼叫编辑路由不可用。 ' . self::DELEGATION_ZH,
				'deals_labels_route_unavailable' => '交易标签路由不可用。 ' . self::DELEGATION_ZH,
				'deal_destroy_route_unavailable' => '删除交易路由不可用。 ' . self::DELEGATION_ZH,
				'labels_store_route_unavailable' => '标签存储路由不可用。 ' . self::DELEGATION_ZH,
				'deal_client_permissions_store_route_unavailable' => '交易客户权限存储路由不可用。 ' . self::DELEGATION_ZH,
				'deal_products_update_route_unavailable' => '交易产品更新路由不可用。 ' . self::DELEGATION_ZH,
				'deal_sources_update_route_unavailable' => '交易来源更新路由不可用。 ' . self::DELEGATION_ZH,
				'deals_index_route_unavailable' => '交易索引路由不可用。 ' . self::DELEGATION_ZH,
				'deal_tasks_update_route_unavailable' => '更新交易任务路由不可用。 ' . self::DELEGATION_ZH,
				'deal_tasks_store_route_unavailable' => '创建交易任务路由不可用。 ' . self::DELEGATION_ZH,
				'deal_users_update_route_unavailable' => '交易用户更新路由不可用。 ' . self::DELEGATION_ZH,
				'deal_show_convert_route_unavailable' => '交易转换路由不可用。 ' . self::DELEGATION_ZH,
				'section_anchor_deal_unavailable' => '交易部分锚点不可用。 ' . self::DELEGATION_ZH,
				'deal_users_edit_route_unavailable' => '在交易中添加用户的路由不可用。 ' . self::DELEGATION_ZH,
				'deal_products_edit_route_unavailable' => '添加产品路由不可用。 ' . self::DELEGATION_ZH,
				'users_destroy_route_unavailable' => '交易用户删除路由不可用。 ' . self::DELEGATION_ZH,
				'products_destroy_route_unavailable' => '交易产品删除路由不可用。 ' . self::DELEGATION_ZH,
				'deal_sources_edit_route_unavailable' => '添加来源路由不可用。 ' . self::DELEGATION_ZH,
				'deal_sources_destroy_route_unavailable' => '删除交易来源路由不可用。 ' . self::DELEGATION_ZH,
				'deal_calls_create_route_unavailable' => '为交易添加呼叫的路由不可用。 ' . self::DELEGATION_ZH,
				'deal_calls_destroy_route_unavailable' => '删除呼叫路由不可用。 ' . self::DELEGATION_ZH,
				'emails_create_route_unavailable' => '交易邮件创建路由不可用。 ' . self::DELEGATION_ZH,
				'deal_index_route_unavailable' => '交易设置路由不可用。' . self::DELEGATION_ZH,
			],
		],
		ViewsConstants::DOC => [
			'ar' => [
				'document_index_route_unavailable' => 'مسار إعداد المستند غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'document_index_route_unavailable' => 'Dokumentopsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'document_index_route_unavailable' => 'Dokumenteinrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'document_index_route_unavailable' => 'Document setup route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'document_index_route_unavailable' => 'La ruta de configuración de documentos no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'document_index_route_unavailable' => 'La route de configuration des documents est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'document_index_route_unavailable' => 'נתיב הגדרת מסמך אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'document_index_route_unavailable' => 'La rotta di configurazione del documento non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'document_index_route_unavailable' => 'ドキュメント設定ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'document_index_route_unavailable' => 'Documentinstellingsroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'document_index_route_unavailable' => 'Trasa konfiguracji dokumentu jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'document_index_route_unavailable' => 'A rota de configuração de documentos não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'document_index_route_unavailable' => 'A rota de configuração de documentos não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'document_index_route_unavailable' => 'Маршрут настройки документов недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'document_index_route_unavailable' => 'Belge ayarlama rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'document_index_route_unavailable' => '文档设置路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::EMLS => [
			'ar' => ['email_template_route_unavailable' => 'مسار قالب البريد الإلكتروني غير متاح. ' . self::DELEGATION_AR],
			'da' => ['email_template_route_unavailable' => 'E-mail-skabelonrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['email_template_route_unavailable' => 'E-Mail-Vorlagen-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['email_template_route_unavailable' => 'Email Template route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['email_template_route_unavailable' => 'La ruta de plantilla de correo electrónico no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['email_template_route_unavailable' => 'La route du modèle d\'e-mail n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['email_template_route_unavailable' => 'נתיב תבנית דוא"ל אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['email_template_route_unavailable' => 'La rotta del modello e-mail non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['email_template_route_unavailable' => 'メールテンプレートルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['email_template_route_unavailable' => 'E-mailsjabloonroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['email_template_route_unavailable' => 'Trasa szablonu e-mail jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['email_template_route_unavailable' => 'A rota de modelo de e-mail não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['email_template_route_unavailable' => 'A rota de modelo de e-mail não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['email_template_route_unavailable' => 'Маршрут шаблона электронной почты недоступен. ' . self::DELEGATION_RU],
			'tr' => ['email_template_route_unavailable' => 'E-posta Şablonu rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['email_template_route_unavailable' => '电子邮件模板路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::EMP => [
			'ar' => [
				'show_employee_route_unavailable' => 'مسار عرض الموظف غير متاح. ' . self::DELEGATION_AR,
				'employee_setup_route_unavailable' => 'مسار إعداد الموظف غير متاح. ' . self::DELEGATION_AR,
				'salary_update_route_unavailable' => 'مسار تحديث الراتب غير متاح. ' . self::DELEGATION_AR,
				'salary_basic_route_unavailable' => 'مسار الراتب الأساسي غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'show_employee_route_unavailable' => 'Medarbejder visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'employee_setup_route_unavailable' => 'Medarbejder opsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'salary_update_route_unavailable' => 'Lønopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'salary_basic_route_unavailable' => 'Grundlønsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'show_employee_route_unavailable' => 'Mitarbeiter-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'employee_setup_route_unavailable' => 'Mitarbeiter-Einrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'salary_update_route_unavailable' => 'Gehaltsaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'salary_basic_route_unavailable' => 'Grundgehaltsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'show_employee_route_unavailable' => 'Employee view route is unavailable. ' . self::DELEGATION_EN,
				'employee_setup_route_unavailable' => 'Employee setup route is unavailable. ' . self::DELEGATION_EN,
				'salary_update_route_unavailable' => 'Salary update route is unavailable. ' . self::DELEGATION_EN,
				'salary_basic_route_unavailable' => 'Basic salary route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'show_employee_route_unavailable' => 'La ruta de visualización de empleados no está disponible. ' . self::DELEGATION_ES,
				'employee_setup_route_unavailable' => 'La ruta de configuración de empleados no está disponible. ' . self::DELEGATION_ES,
				'salary_update_route_unavailable' => 'La ruta de actualización de salario no está disponible. ' . self::DELEGATION_ES,
				'salary_basic_route_unavailable' => 'La ruta del salario básico no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'show_employee_route_unavailable' => 'La route de visualisation des employés est indisponible. ' . self::DELEGATION_FR,
				'employee_setup_route_unavailable' => 'La route de configuration des employés est indisponible. ' . self::DELEGATION_FR,
				'salary_update_route_unavailable' => 'La route de mise à jour du salaire est indisponible. ' . self::DELEGATION_FR,
				'salary_basic_route_unavailable' => 'La route du salaire de base est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'show_employee_route_unavailable' => 'נתיב תצוגת עובד אינו זמין. ' . self::DELEGATION_HE,
				'employee_setup_route_unavailable' => 'נתיב הגדרת עובד אינו זמין. ' . self::DELEGATION_HE,
				'salary_update_route_unavailable' => 'נתיב עדכון משכורת אינו זמין. ' . self::DELEGATION_HE,
				'salary_basic_route_unavailable' => 'נתיב משכורת בסיסית אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'show_employee_route_unavailable' => 'La rotta di visualizzazione del dipendente non è disponibile. ' . self::DELEGATION_IT,
				'employee_setup_route_unavailable' => 'La rotta di configurazione del dipendente non è disponibile. ' . self::DELEGATION_IT,
				'salary_update_route_unavailable' => 'La rotta di aggiornamento dello stipendio non è disponibile. ' . self::DELEGATION_IT,
				'salary_basic_route_unavailable' => 'La rotta dello stipendio base non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'show_employee_route_unavailable' => '従業員表示ルートは利用できません。' . self::DELEGATION_JA,
				'employee_setup_route_unavailable' => '従業員設定ルートは利用できません。' . self::DELEGATION_JA,
				'salary_update_route_unavailable' => '給与更新ルートは利用できません。' . self::DELEGATION_JA,
				'salary_basic_route_unavailable' => '基本給与ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'show_employee_route_unavailable' => 'Medewerkerweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'employee_setup_route_unavailable' => 'Medewerkerinstellingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'salary_update_route_unavailable' => 'Salarisupdateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'salary_basic_route_unavailable' => 'Basis salarisroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'show_employee_route_unavailable' => 'Trasa podglądu pracownika jest niedostępna. ' . self::DELEGATION_PL,
				'employee_setup_route_unavailable' => 'Trasa konfiguracji pracownika jest niedostępna. ' . self::DELEGATION_PL,
				'salary_update_route_unavailable' => 'Trasa aktualizacji wynagrodzenia jest niedostępna. ' . self::DELEGATION_PL,
				'salary_basic_route_unavailable' => 'Trasa wynagrodzenia podstawowego jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'show_employee_route_unavailable' => 'A rota de visualização de funcionários não está disponível. ' . self::DELEGATION_PT,
				'employee_setup_route_unavailable' => 'A rota de configuração de funcionários não está disponível. ' . self::DELEGATION_PT,
				'salary_update_route_unavailable' => 'A rota de atualização de salário não está disponível. ' . self::DELEGATION_PT,
				'salary_basic_route_unavailable' => 'A rota do salário básico não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'show_employee_route_unavailable' => 'A rota de visualização de funcionários não está disponível. ' . self::DELEGATION_PTBR,
				'employee_setup_route_unavailable' => 'A rota de configuração de funcionários não está disponível. ' . self::DELEGATION_PTBR,
				'salary_update_route_unavailable' => 'A rota de atualização de salário não está disponível. ' . self::DELEGATION_PTBR,
				'salary_basic_route_unavailable' => 'A rota do salário básico não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'show_employee_route_unavailable' => 'Маршрут просмотра сотрудника недоступен. ' . self::DELEGATION_RU,
				'employee_setup_route_unavailable' => 'Маршрут настройки сотрудника недоступен. ' . self::DELEGATION_RU,
				'salary_update_route_unavailable' => 'Маршрут обновления зарплаты недоступен. ' . self::DELEGATION_RU,
				'salary_basic_route_unavailable' => 'Маршрут базовой зарплаты недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'show_employee_route_unavailable' => 'Çalışan görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'employee_setup_route_unavailable' => 'Çalışan ayarlama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'salary_update_route_unavailable' => 'Maaş güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'salary_basic_route_unavailable' => 'Temel maaş rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'show_employee_route_unavailable' => '员工查看路由不可用。' . self::DELEGATION_ZH,
				'employee_setup_route_unavailable' => '员工设置路由不可用。' . self::DELEGATION_ZH,
				'salary_update_route_unavailable' => '薪资更新路由不可用。' . self::DELEGATION_ZH,
				'salary_basic_route_unavailable' => '基本薪资路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::EMP_ATD => [
			'ar' => [
				'attendance_index_route_unavailable' => 'مسار تسجيل الحضور غير متاح. ' . self::DELEGATION_AR,
				'bulk_attendance_route_unavailable' => 'مسار الحضور الجماعي غير متاح. ' . self::DELEGATION_AR,
				'employee_attendance_out_route_unavailable' => 'مسار تسجيل الخروج غير متاح. ' . self::DELEGATION_AR,
				'employee_attendance_in_route_unavailable' => 'مسار تسجيل الدخول غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'attendance_index_route_unavailable' => 'Registrer fremmøde rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bulk_attendance_route_unavailable' => 'Massefremmøde rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'employee_attendance_out_route_unavailable' => 'Udklaekningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'employee_attendance_in_route_unavailable' => 'Indklaekningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'attendance_index_route_unavailable' => 'Anwesenheitserfassungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bulk_attendance_route_unavailable' => 'Massenanwesenheitsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'employee_attendance_out_route_unavailable' => 'Stempeluhr-Ausgangsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'employee_attendance_in_route_unavailable' => 'Stempeluhr-Eingangsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'attendance_index_route_unavailable' => 'Mark attendance route is unavailable. ' . self::DELEGATION_EN,
				'bulk_attendance_route_unavailable' => 'Bulk attendance route is unavailable. ' . self::DELEGATION_EN,
				'employee_attendance_out_route_unavailable' => 'Clock out route is unavailable. ' . self::DELEGATION_EN,
				'employee_attendance_in_route_unavailable' => 'Clock in route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'attendance_index_route_unavailable' => 'La ruta de registro de asistencia no está disponible. ' . self::DELEGATION_ES,
				'bulk_attendance_route_unavailable' => 'La ruta de asistencia masiva no está disponible. ' . self::DELEGATION_ES,
				'employee_attendance_out_route_unavailable' => 'La ruta de registro de salida no está disponible. ' . self::DELEGATION_ES,
				'employee_attendance_in_route_unavailable' => 'La ruta de registro de entrada no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'attendance_index_route_unavailable' => 'La route d\'enregistrement de présence est indisponible. ' . self::DELEGATION_FR,
				'bulk_attendance_route_unavailable' => 'La route de présence groupée est indisponible. ' . self::DELEGATION_FR,
				'employee_attendance_out_route_unavailable' => 'La route de pointage de sortie n\'est pas disponible. ' . self::DELEGATION_FR,
				'employee_attendance_in_route_unavailable' => 'La route de pointage d\'entrée n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'attendance_index_route_unavailable' => 'נתיב סימון נוכחות אינו זמין. ' . self::DELEGATION_HE,
				'bulk_attendance_route_unavailable' => 'נתיב נוכחות קבוצתית אינו זמין. ' . self::DELEGATION_HE,
				'employee_attendance_out_route_unavailable' => 'נתיב יציאה אינו זמין. ' . self::DELEGATION_HE,
				'employee_attendance_in_route_unavailable' => 'נתיב כניסה אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'attendance_index_route_unavailable' => 'La rotta di registrazione presenza non è disponibile. ' . self::DELEGATION_IT,
				'bulk_attendance_route_unavailable' => 'La rotta di presenza collettiva non è disponibile. ' . self::DELEGATION_IT,
				'employee_attendance_out_route_unavailable' => 'La rotta di timbratura di uscita non è disponibile. ' . self::DELEGATION_IT,
				'employee_attendance_in_route_unavailable' => 'La rotta di timbratura di entrata non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'attendance_index_route_unavailable' => '出勤記録ルートは利用できません。' . self::DELEGATION_JA,
				'bulk_attendance_route_unavailable' => '一括出勤ルートは利用できません。' . self::DELEGATION_JA,
				'employee_attendance_out_route_unavailable' => '退勤ルートは利用できません。' . self::DELEGATION_JA,
				'employee_attendance_in_route_unavailable' => '出勤ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'attendance_index_route_unavailable' => 'Aanwezigheidsregistratieroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bulk_attendance_route_unavailable' => 'Bulk aanwezigheidsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'employee_attendance_out_route_unavailable' => 'Uitklokroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'employee_attendance_in_route_unavailable' => 'Inklokroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'attendance_index_route_unavailable' => 'Trasa rejestracji obecności jest niedostępna. ' . self::DELEGATION_PL,
				'bulk_attendance_route_unavailable' => 'Trasa zbiorczej obecności jest niedostępna. ' . self::DELEGATION_PL,
				'employee_attendance_out_route_unavailable' => 'Trasa rejestracji wyjścia jest niedostępna. ' . self::DELEGATION_PL,
				'employee_attendance_in_route_unavailable' => 'Trasa rejestracji wejścia jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'attendance_index_route_unavailable' => 'A rota de marcação de presenças não está disponível. ' . self::DELEGATION_PT,
				'bulk_attendance_route_unavailable' => 'A rota de presenças em massa não está disponível. ' . self::DELEGATION_PT,
				'employee_attendance_out_route_unavailable' => 'A rota de registro de saída não está disponível. ' . self::DELEGATION_PT,
				'employee_attendance_in_route_unavailable' => 'A rota de registro de entrada não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'attendance_index_route_unavailable' => 'A rota de marcação de presença não está disponível. ' . self::DELEGATION_PTBR,
				'bulk_attendance_route_unavailable' => 'A rota de presença em massa não está disponível. ' . self::DELEGATION_PTBR,
				'employee_attendance_out_route_unavailable' => 'A rota de registro de saída não está disponível. ' . self::DELEGATION_PTBR,
				'employee_attendance_in_route_unavailable' => 'A rota de registro de entrada não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'attendance_index_route_unavailable' => 'Маршрут отметки посещаемости недоступен. ' . self::DELEGATION_RU,
				'bulk_attendance_route_unavailable' => 'Маршрут массовой посещаемости недоступен. ' . self::DELEGATION_RU,
				'employee_attendance_out_route_unavailable' => 'Маршрут выхода недоступен. ' . self::DELEGATION_RU,
				'employee_attendance_in_route_unavailable' => 'Маршрут входа недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'attendance_index_route_unavailable' => 'Devam kayıt rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bulk_attendance_route_unavailable' => 'Toplu devam rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'employee_attendance_out_route_unavailable' => 'Çıkış kayıt rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'employee_attendance_in_route_unavailable' => 'Giriş kayıt rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'attendance_index_route_unavailable' => '标记考勤路由不可用。' . self::DELEGATION_ZH,
				'bulk_attendance_route_unavailable' => '批量考勤路由不可用。' . self::DELEGATION_ZH,
				'employee_attendance_out_route_unavailable' => '下班打卡路由不可用。' . self::DELEGATION_ZH,
				'employee_attendance_in_route_unavailable' => '上班打卡路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::EST => [
			'ar' => [
				'estimation_show_route_unavailable' => 'مسار عرض التقدير غير متاح. ' . self::DELEGATION_AR,
				'estimation_edit_route_unavailable' => 'مسار تعديل التقدير غير متاح. ' . self::DELEGATION_AR,
				'estimation_destroy_route_unavailable' => 'مسار حذف التقدير غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'estimation_show_route_unavailable' => 'Estimering visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'estimation_edit_route_unavailable' => 'Estimering redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'estimation_destroy_route_unavailable' => 'Estimering sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'estimation_show_route_unavailable' => 'Kostenvoranschlag-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'estimation_edit_route_unavailable' => 'Kostenvoranschlag-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'estimation_destroy_route_unavailable' => 'Kostenvoranschlag-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'estimation_show_route_unavailable' => 'Estimate view route is unavailable. ' . self::DELEGATION_EN,
				'estimation_edit_route_unavailable' => 'Estimate edit route is unavailable. ' . self::DELEGATION_EN,
				'estimation_destroy_route_unavailable' => 'Estimate delete route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'estimation_show_route_unavailable' => 'La ruta de visualización de estimación no está disponible. ' . self::DELEGATION_ES,
				'estimation_edit_route_unavailable' => 'La ruta de edición de estimación no está disponible. ' . self::DELEGATION_ES,
				'estimation_destroy_route_unavailable' => 'La ruta de eliminación de estimación no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'estimation_show_route_unavailable' => 'La route de visualisation de devis n\'est pas disponible. ' . self::DELEGATION_FR,
				'estimation_edit_route_unavailable' => 'La route d\'édition de devis n\'est pas disponible. ' . self::DELEGATION_FR,
				'estimation_destroy_route_unavailable' => 'La route de suppression de devis n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'estimation_show_route_unavailable' => 'נתיב הצגת אומדן אינו זמין. ' . self::DELEGATION_HE,
				'estimation_edit_route_unavailable' => 'נתיב עריכת אומדן אינו זמין. ' . self::DELEGATION_HE,
				'estimation_destroy_route_unavailable' => 'נתיב מחיקת אומדן אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'estimation_show_route_unavailable' => 'La rotta di visualizzazione preventivo non è disponibile. ' . self::DELEGATION_IT,
				'estimation_edit_route_unavailable' => 'La rotta di modifica preventivo non è disponibile. ' . self::DELEGATION_IT,
				'estimation_destroy_route_unavailable' => 'La rotta di eliminazione preventivo non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'estimation_show_route_unavailable' => '見積表示ルートは利用できません。' . self::DELEGATION_JA,
				'estimation_edit_route_unavailable' => '見積編集ルートは利用できません。' . self::DELEGATION_JA,
				'estimation_destroy_route_unavailable' => '見積削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'estimation_show_route_unavailable' => 'Offerte weergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'estimation_edit_route_unavailable' => 'Offerte bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'estimation_destroy_route_unavailable' => 'Offerte verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'estimation_show_route_unavailable' => 'Trasa wyświetlania wyceny jest niedostępna. ' . self::DELEGATION_PL,
				'estimation_edit_route_unavailable' => 'Trasa edycji wyceny jest niedostępna. ' . self::DELEGATION_PL,
				'estimation_destroy_route_unavailable' => 'Trasa usuwania wyceny jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'estimation_show_route_unavailable' => 'A rota de visualização de estimativa não está disponível. ' . self::DELEGATION_PT,
				'estimation_edit_route_unavailable' => 'A rota de edição de estimativa não está disponível. ' . self::DELEGATION_PT,
				'estimation_destroy_route_unavailable' => 'A rota de exclusão de estimativa não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'estimation_show_route_unavailable' => 'A rota de visualização de estimativa não está disponível. ' . self::DELEGATION_PTBR,
				'estimation_edit_route_unavailable' => 'A rota de edição de estimativa não está disponível. ' . self::DELEGATION_PTBR,
				'estimation_destroy_route_unavailable' => 'A rota de exclusão de estimativa não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'estimation_show_route_unavailable' => 'Маршрут просмотра сметы недоступен. ' . self::DELEGATION_RU,
				'estimation_edit_route_unavailable' => 'Маршрут редактирования сметы недоступен. ' . self::DELEGATION_RU,
				'estimation_destroy_route_unavailable' => 'Маршрут удаления сметы недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'estimation_show_route_unavailable' => 'Tahmin görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'estimation_edit_route_unavailable' => 'Tahmin düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'estimation_destroy_route_unavailable' => 'Tahmin silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'estimation_show_route_unavailable' => '估算查看路由不可用。' . self::DELEGATION_ZH,
				'estimation_edit_route_unavailable' => '估算编辑路由不可用。' . self::DELEGATION_ZH,
				'estimation_destroy_route_unavailable' => '估算删除路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::EVT => [
			'ar' => [
				'event_index_route_unavailable' => 'مسار إعداد الأحداث غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'event_index_route_unavailable' => 'Begivenhedsopsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'event_index_route_unavailable' => 'Ereigniseinrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'event_index_route_unavailable' => 'Event setup route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'event_index_route_unavailable' => 'La ruta de configuración de eventos no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'event_index_route_unavailable' => 'La route de configuration des événements est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'event_index_route_unavailable' => 'נתיב הגדרת אירוע אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'event_index_route_unavailable' => 'La rotta di configurazione dell\'evento non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'event_index_route_unavailable' => 'イベント設定ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'event_index_route_unavailable' => 'Evenementinstellingsroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'event_index_route_unavailable' => 'Trasa konfiguracji wydarzeń jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'event_index_route_unavailable' => 'A rota de configuração de eventos não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'event_index_route_unavailable' => 'A rota de configuração de eventos não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'event_index_route_unavailable' => 'Маршрут настройки событий недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'event_index_route_unavailable' => 'Etkinlik ayarlama rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'event_index_route_unavailable' => '事件设置路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::EXP => [
			'ar' => ['expense_index_route_unavailable' => 'مسار المصروفات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['expense_index_route_unavailable' => 'Udgiftsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['expense_index_route_unavailable' => 'Ausgaben-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['expense_index_route_unavailable' => 'Expense route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['expense_index_route_unavailable' => 'La ruta de gastos no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['expense_index_route_unavailable' => 'La route des dépenses est indisponible. ' . self::DELEGATION_FR],
			'he' => ['expense_index_route_unavailable' => 'נתיב הוצאות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['expense_index_route_unavailable' => 'La rotta delle spese non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['expense_index_route_unavailable' => '経費ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['expense_index_route_unavailable' => 'Uitgavenroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['expense_index_route_unavailable' => 'Trasa wydatków jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['expense_index_route_unavailable' => 'A rota de despesas não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['expense_index_route_unavailable' => 'A rota de despesas não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['expense_index_route_unavailable' => 'Маршрут расходов недоступен. ' . self::DELEGATION_RU],
			'tr' => ['expense_index_route_unavailable' => 'Gider rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['expense_index_route_unavailable' => '费用路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::FM_BD => [
			'ar' => [
				'form_builder_index_route_unavailable' => 'مسار منشئ النماذج غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'form_builder_index_route_unavailable' => 'Formularbyggerrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'form_builder_index_route_unavailable' => 'Formularbuilder-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'form_builder_index_route_unavailable' => 'Form Builder route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'form_builder_index_route_unavailable' => 'La ruta del creador de formularios no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'form_builder_index_route_unavailable' => 'La route du générateur de formulaires n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'form_builder_index_route_unavailable' => 'נתיב בונה הטופס אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'form_builder_index_route_unavailable' => 'La rotta del generatore di moduli non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'form_builder_index_route_unavailable' => 'フォームビルダールートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'form_builder_index_route_unavailable' => 'Formulierbouwerroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'form_builder_index_route_unavailable' => 'Trasa konstruktora formularzy jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'form_builder_index_route_unavailable' => 'A rota do Construtor de Formulários não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'form_builder_index_route_unavailable' => 'A rota do Construtor de Formulários não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'form_builder_index_route_unavailable' => 'Маршрут конструктора форм недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'form_builder_index_route_unavailable' => 'Form Oluşturucu rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'form_builder_index_route_unavailable' => '表单生成器路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::GL => [
			'ar' => [
				'financial_goal_index_route_unavailable' => 'مسار الهدف المالي غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'financial_goal_index_route_unavailable' => 'Finansiel målrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'financial_goal_index_route_unavailable' => 'Finanzziel-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'financial_goal_index_route_unavailable' => 'Financial Goal route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'financial_goal_index_route_unavailable' => 'La ruta de Objetivo financiero no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'financial_goal_index_route_unavailable' => 'La route de l\'objectif financier n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'financial_goal_index_route_unavailable' => 'נתיב היעד הפיננסי אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'financial_goal_index_route_unavailable' => 'Il percorso dell\'obiettivo finanziario non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'financial_goal_index_route_unavailable' => '財務目標ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'financial_goal_index_route_unavailable' => 'Financieel doelroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'financial_goal_index_route_unavailable' => 'Trasa celu finansowego jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'financial_goal_index_route_unavailable' => 'A rota de Objetivo Financeiro não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'financial_goal_index_route_unavailable' => 'A rota de Objetivo Financeiro não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'financial_goal_index_route_unavailable' => 'Маршрут финансовой цели недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'financial_goal_index_route_unavailable' => 'Finansal Hedef rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'financial_goal_index_route_unavailable' => '财务目标路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::GL_TRC => [
			'ar' => [
				'goal_tracking_index_route_unavailable' => 'مسار تتبع الهدف غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'goal_tracking_index_route_unavailable' => 'Målsøgningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'goal_tracking_index_route_unavailable' => 'Zielverfolgungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'goal_tracking_index_route_unavailable' => 'Goal Tracking route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'goal_tracking_index_route_unavailable' => 'La ruta de seguimiento de objetivos no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'goal_tracking_index_route_unavailable' => 'La route de suivi des objectifs est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'goal_tracking_index_route_unavailable' => 'נתיב מעקב מטרות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'goal_tracking_index_route_unavailable' => 'La rotta di tracciamento degli obiettivi non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'goal_tracking_index_route_unavailable' => '目標追跡ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'goal_tracking_index_route_unavailable' => 'Doelvolgroutes is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'goal_tracking_index_route_unavailable' => 'Trasa śledzenia celów jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'goal_tracking_index_route_unavailable' => 'A rota de acompanhamento de objetivos não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'goal_tracking_index_route_unavailable' => 'A rota de acompanhamento de metas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'goal_tracking_index_route_unavailable' => 'Маршрут отслеживания целей недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'goal_tracking_index_route_unavailable' => 'Hedef takip rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'goal_tracking_index_route_unavailable' => '目标跟踪路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::HLD => [
			'ar' => [
				'holidays_index_route_unavailable' => 'مسار فهرس العطلات غير متاح. ' . self::DELEGATION_AR,
				'holiday_calendar_route_unavailable' => 'مسار عرض التقويم غير متاح. ' . self::DELEGATION_AR,
				'holiday_create_route_unavailable' => 'مسار إنشاء العطلة غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'holidays_index_route_unavailable' => 'Ferieindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'holiday_calendar_route_unavailable' => 'Kalender visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'holiday_create_route_unavailable' => 'Opret ferie-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'holidays_index_route_unavailable' => 'Feiertagsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'holiday_calendar_route_unavailable' => 'Kalenderansicht-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'holiday_create_route_unavailable' => 'Feiertagserstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'holidays_index_route_unavailable' => 'Holidays index route is unavailable. ' . self::DELEGATION_EN,
				'holiday_calendar_route_unavailable' => 'Calendar view route is unavailable. ' . self::DELEGATION_EN,
				'holiday_create_route_unavailable' => 'Create holiday route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'holidays_index_route_unavailable' => 'La ruta del índice de vacaciones no está disponible. ' . self::DELEGATION_ES,
				'holiday_calendar_route_unavailable' => 'La ruta de vista de calendario no está disponible. ' . self::DELEGATION_ES,
				'holiday_create_route_unavailable' => 'La ruta de creación de vacaciones no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'holidays_index_route_unavailable' => 'La route de l\'index des vacances est indisponible. ' . self::DELEGATION_FR,
				'holiday_calendar_route_unavailable' => 'La route de la vue calendrier n\'est pas disponible. ' . self::DELEGATION_FR,
				'holiday_create_route_unavailable' => 'La route de création de vacances n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'holidays_index_route_unavailable' => 'נתיב אינדקס חגים אינו זמין. ' . self::DELEGATION_HE,
				'holiday_calendar_route_unavailable' => 'נתיב תצוגת לוח שנה אינו זמין. ' . self::DELEGATION_HE,
				'holiday_create_route_unavailable' => 'נתיב יצירת חג אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'holidays_index_route_unavailable' => 'La rotta dell\'indice delle festività non è disponibile. ' . self::DELEGATION_IT,
				'holiday_calendar_route_unavailable' => 'La rotta della vista calendario non è disponibile. ' . self::DELEGATION_IT,
				'holiday_create_route_unavailable' => 'La rotta di creazione della festività non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'holidays_index_route_unavailable' => '休日インデックスルートは利用できません。' . self::DELEGATION_JA,
				'holiday_calendar_route_unavailable' => 'カレンダービュールートは利用できません。' . self::DELEGATION_JA,
				'holiday_create_route_unavailable' => '休日作成ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'holidays_index_route_unavailable' => 'Feestdagenindexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'holiday_calendar_route_unavailable' => 'Kalenderweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'holiday_create_route_unavailable' => 'Feestdagen aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'holidays_index_route_unavailable' => 'Trasa indeksu świąt jest niedostępna. ' . self::DELEGATION_PL,
				'holiday_calendar_route_unavailable' => 'Trasa widoku kalendarza jest niedostępna. ' . self::DELEGATION_PL,
				'holiday_create_route_unavailable' => 'Trasa tworzenia święta jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'holidays_index_route_unavailable' => 'A rota do índice de feriados não está disponível. ' . self::DELEGATION_PT,
				'holiday_calendar_route_unavailable' => 'A rota da visualização do calendário não está disponível. ' . self::DELEGATION_PT,
				'holiday_create_route_unavailable' => 'A rota de criação de feriado não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'holidays_index_route_unavailable' => 'A rota do índice de feriados não está disponível. ' . self::DELEGATION_PTBR,
				'holiday_calendar_route_unavailable' => 'A rota de visualização do calendário não está disponível. ' . self::DELEGATION_PTBR,
				'holiday_create_route_unavailable' => 'A rota de criação de feriado não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'holidays_index_route_unavailable' => 'Маршрут индекса праздников недоступен. ' . self::DELEGATION_RU,
				'holiday_calendar_route_unavailable' => 'Маршрут просмотра календаря недоступен. ' . self::DELEGATION_RU,
				'holiday_create_route_unavailable' => 'Маршрут создания праздника недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'holidays_index_route_unavailable' => 'Tatil indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'holiday_calendar_route_unavailable' => 'Takvim görünümü rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'holiday_create_route_unavailable' => 'Tatil oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'holidays_index_route_unavailable' => '假期索引路由不可用。' . self::DELEGATION_ZH,
				'holiday_calendar_route_unavailable' => '日历视图路由不可用。' . self::DELEGATION_ZH,
				'holiday_create_route_unavailable' => '创建假期路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::IND => [
			'ar' => [
				'indicator_index_route_unavailable' => 'مسار فهرس المؤشرات غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'indicator_index_route_unavailable' => 'Indikatorindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'indicator_index_route_unavailable' => 'Indikatorindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'indicator_index_route_unavailable' => 'Indicator index route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'indicator_index_route_unavailable' => 'La ruta del índice de indicadores no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'indicator_index_route_unavailable' => 'La route de l\'index des indicateurs est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'indicator_index_route_unavailable' => 'נתיב אינדקס אינדיקטורים אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'indicator_index_route_unavailable' => 'La rotta dell\'indice degli indicatori non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'indicator_index_route_unavailable' => '指標インデックスルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'indicator_index_route_unavailable' => 'Indicatorindexroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'indicator_index_route_unavailable' => 'Trasa indeksu wskaźników jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'indicator_index_route_unavailable' => 'A rota do índice de indicadores não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'indicator_index_route_unavailable' => 'A rota do índice de indicadores não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'indicator_index_route_unavailable' => 'Маршрут индекса показателей недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'indicator_index_route_unavailable' => 'Gösterge indeks rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'indicator_index_route_unavailable' => '指标索引路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::INV => [
			'ar' => [
				'invoice_index_route_unavailable' => 'مسار الفواتير غير متاح. ' . self::DELEGATION_AR,
				'invoice_export_route_unavailable' => 'مسار التصدير غير متاح. ' . self::DELEGATION_AR,
				'invoice_create_route_unavailable' => 'مسار إنشاء الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_copy_route_unavailable' => 'مسار نسخ الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_duplicate_route_unavailable' => 'مسار تكرار الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_show_route_unavailable' => 'مسار عرض الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_edit_route_unavailable' => 'مسار تعديل الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_delete_route_unavailablee' => 'مسار حذف الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'credit_note_route_unavailable' => 'مسار إضافة إشعار دائن غير متاح. ' . self::DELEGATION_AR,
				'custom_credit_note_route_unavailable' => 'مسار إضافة إشعار دائن مخصص غير متاح. ' . self::DELEGATION_AR,
				'edit_credit_note_route_unavailable' => 'مسار تعديل إشعار دائن غير متاح. ' . self::DELEGATION_AR,
				'delete_credit_note_route_unavailable' => 'مسار حذف إشعار دائن غير متاح. ' . self::DELEGATION_AR,
				'change_status_route_unavailable' => 'مسار تغيير الحالة غير متاح. ' . self::DELEGATION_AR,
				'invoice_store_route_unavailable' => 'مسار إنشاء الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_update_route_unavailable' => 'مسار تحديث الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_customer_route_unavailable' => 'مسار عميل الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_payment_route_unavailable' => 'مسار دفع الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_product_route_unavailable' => 'مسار منتج الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_resend_route_unavailable' => 'مسار إعادة إرسال الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_pdf_route_unavailable' => 'مسار تنزيل PDF الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'invoice_mark_sent_route_unavailable' => 'مسار إرسال الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'link_copy_route_unavailable' => 'مسار نسخ رابط الفاتورة غير متاح. ' . self::DELEGATION_AR,
				'payment_receipt_route_unavailable' => 'مسار إيصال الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_add_receipt_download_route_unavailable' => 'مسار تنزيل إيصال الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_receipt_view_unavailable' => 'مسار عرض الإيصال غير متاح. ' . self::DELEGATION_AR,
				'payment_add_receipt_view_unavailable' => 'مسار عرض الإيصال غير متاح. ' . self::DELEGATION_AR,
				'payment_add_receipt_route_unavailable' => 'مسار URL إيصال الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_delete_route_unavailable' => 'مسار حذف الدفع غير متاح. ' . self::DELEGATION_AR,
				'bank_payment_receipt_route_unavailable' => 'مسار إيصال الدفع المصرفي غير متاح. ' . self::DELEGATION_AR,
				'payment_status_route_unavailable' => 'مسار حالة الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_status_action_route_unavailable' => 'مسار إجراء حالة الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_destroy_route_unavailable' => 'مسار حذف الدفع غير متاح. ' . self::DELEGATION_AR,
				'pay_with_bank_route_unavailable' => 'مسار الدفع غير متاح. ' . self::DELEGATION_AR,
				'payment_with_benefit_route_unavailable' => 'مسار الدفع باستخدام بنفت غير متاح. ' . self::DELEGATION_AR,
				'payment_reminder_route_unavailable' => 'مسار تذكير الإيصال غير متاح. ' . self::DELEGATION_AR,
				'bankpayment_receipt_route_unavailable' => 'مسار إيصال الدفع المصرفي غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'invoice_index_route_unavailable' => 'Fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_export_route_unavailable' => 'Eksportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_create_route_unavailable' => 'Opret fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_copy_route_unavailable' => 'Kopier fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_duplicate_route_unavailable' => 'Dupliker fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_show_route_unavailable' => 'Vis fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_edit_route_unavailable' => 'Rediger fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_delete_route_unavailablee' => 'Slet fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'credit_note_route_unavailable' => 'Tilføj kreditnotarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'custom_credit_note_route_unavailable' => 'Tilføj brugerdefineret kreditnotarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'edit_credit_note_route_unavailable' => 'Rediger kreditnotarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'delete_credit_note_route_unavailable' => 'Slet kreditnotarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'change_status_route_unavailable' => 'Skift statusrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_store_route_unavailable' => 'Opret fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_update_route_unavailable' => 'Opdater fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_customer_route_unavailable' => 'Faktura kunderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_payment_route_unavailable' => 'Faktura betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_product_route_unavailable' => 'Faktura produktroute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_resend_route_unavailable' => 'Gensend fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_pdf_route_unavailable' => 'Download faktura PDF-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_mark_sent_route_unavailable' => 'Send fakturarute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'link_copy_route_unavailable' => 'Kopier fakturalinkrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_receipt_route_unavailable' => 'Betalingskvitteringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_add_receipt_download_route_unavailable' => 'Download betalingskvitteringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_receipt_view_unavailable' => 'Vis kvitteringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_add_receipt_view_unavailable' => 'Vis kvitteringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_add_receipt_route_unavailable' => 'Betalingskvitterings URL er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_delete_route_unavailable' => 'Slet betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bank_payment_receipt_route_unavailable' => 'Bankbetalingskvitteringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_status_route_unavailable' => 'Betalingsstatusrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_status_action_route_unavailable' => 'Betalingsstatus handlingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_destroy_route_unavailable' => 'Slet betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pay_with_bank_route_unavailable' => 'Betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_with_benefit_route_unavailable' => 'Betaling med Benefit-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_reminder_route_unavailable' => 'Kvitteringspåmindelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bankpayment_receipt_route_unavailable' => 'Bankbetalingskvitteringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'invoice_index_route_unavailable' => 'Rechnungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_export_route_unavailable' => 'Exportroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_create_route_unavailable' => 'Rechnungserstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_copy_route_unavailable' => 'Rechnungskopierroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_duplicate_route_unavailable' => 'Rechnungsduplizierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_show_route_unavailable' => 'Rechnungsanzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_edit_route_unavailable' => 'Rechnungsbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_delete_route_unavailablee' => 'Rechnungslöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'credit_note_route_unavailable' => 'Gutschrift hinzufügen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'custom_credit_note_route_unavailable' => 'Benutzerdefinierte Gutschrift hinzufügen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'edit_credit_note_route_unavailable' => 'Gutschrift bearbeiten-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'delete_credit_note_route_unavailable' => 'Gutschrift löschen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'change_status_route_unavailable' => 'Status ändern-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_store_route_unavailable' => 'Rechnung erstellen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_update_route_unavailable' => 'Rechnung aktualisieren-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_customer_route_unavailable' => 'Rechnungskunden-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_payment_route_unavailable' => 'Rechnungszahlung-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_product_route_unavailable' => 'Rechnungsprodukt-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_resend_route_unavailable' => 'Rechnung erneut senden-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_pdf_route_unavailable' => 'Rechnung PDF herunterladen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_mark_sent_route_unavailable' => 'Rechnung senden-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'link_copy_route_unavailable' => 'Rechnungslink kopieren-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_receipt_route_unavailable' => 'Zahlungsbeleg-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_add_receipt_download_route_unavailable' => 'Zahlungsbeleg herunterladen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_receipt_view_unavailable' => 'Belegansicht-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_add_receipt_view_unavailable' => 'Belegansicht-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_add_receipt_route_unavailable' => 'Zahlungsbeleg-URL ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_delete_route_unavailable' => 'Zahlung löschen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bank_payment_receipt_route_unavailable' => 'Bankzahlungsbeleg-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_status_route_unavailable' => 'Zahlungsstatus-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_status_action_route_unavailable' => 'Zahlungsstatus-Aktion-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_destroy_route_unavailable' => 'Zahlung löschen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pay_with_bank_route_unavailable' => 'Zahlungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_with_benefit_route_unavailable' => 'Zahlung mit Benefit-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_reminder_route_unavailable' => 'Beleg-Erinnerungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bankpayment_receipt_route_unavailable' => 'Bankzahlungsbeleg-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'invoice_index_route_unavailable' => 'Invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_export_route_unavailable' => 'Export route is unavailable. ' . self::DELEGATION_EN,
				'invoice_create_route_unavailable' => 'Create invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_copy_route_unavailable' => 'Copy invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_duplicate_route_unavailable' => 'Duplicate invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_show_route_unavailable' => 'Show invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_edit_route_unavailable' => 'Edit invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_delete_route_unavailablee' => 'Delete invoice route is unavailable. ' . self::DELEGATION_EN,
				'credit_note_route_unavailable' => 'Add credit note route is unavailable. ' . self::DELEGATION_EN,
				'custom_credit_note_route_unavailable' => 'Add custom credit note route is unavailable. ' . self::DELEGATION_EN,
				'edit_credit_note_route_unavailable' => 'Edit credit note route is unavailable. ' . self::DELEGATION_EN,
				'delete_credit_note_route_unavailable' => 'Delete credit note route is unavailable. ' . self::DELEGATION_EN,
				'change_status_route_unavailable' => 'Change status route is unavailable. ' . self::DELEGATION_EN,
				'invoice_store_route_unavailable' => 'Invoice create route is unavailable. ' . self::DELEGATION_EN,
				'invoice_update_route_unavailable' => 'Invoice update route is unavailable. ' . self::DELEGATION_EN,
				'invoice_customer_route_unavailable' => 'Invoice customer route is unavailable. ' . self::DELEGATION_EN,
				'invoice_payment_route_unavailable' => 'Invoice payment route is unavailable. ' . self::DELEGATION_EN,
				'invoice_product_route_unavailable' => 'Invoice product route is unavailable. ' . self::DELEGATION_EN,
				'invoice_resend_route_unavailable' => 'Resend invoice route is unavailable. ' . self::DELEGATION_EN,
				'invoice_pdf_route_unavailable' => 'Download invoice PDF route is unavailable. ' . self::DELEGATION_EN,
				'invoice_mark_sent_route_unavailable' => 'Send invoice route is unavailable. ' . self::DELEGATION_EN,
				'link_copy_route_unavailable' => 'Invoice link copy route is unavailable. ' . self::DELEGATION_EN,
				'payment_receipt_route_unavailable' => 'Payment receipt route is unavailable. ' . self::DELEGATION_EN,
				'payment_add_receipt_download_route_unavailable' => 'Payment receipt download route is unavailable. ' . self::DELEGATION_EN,
				'payment_receipt_view_unavailable' => 'Receipt view route is unavailable. ' . self::DELEGATION_EN,
				'payment_add_receipt_view_unavailable' => 'Receipt view route is unavailable. ' . self::DELEGATION_EN,
				'payment_add_receipt_route_unavailable' => 'Payment receipt URL is unavailable. ' . self::DELEGATION_EN,
				'payment_delete_route_unavailable' => 'Payment delete route is unavailable. ' . self::DELEGATION_EN,
				'bank_payment_receipt_route_unavailable' => 'Bank payment receipt route is unavailable. ' . self::DELEGATION_EN,
				'payment_status_route_unavailable' => 'Payment status route is unavailable. ' . self::DELEGATION_EN,
				'payment_status_action_route_unavailable' => 'Payment status action route is unavailable. ' . self::DELEGATION_EN,
				'payment_destroy_route_unavailable' => 'Payment delete route is unavailable. ' . self::DELEGATION_EN,
				'pay_with_bank_route_unavailable' => 'Payment route is unavailable. ' . self::DELEGATION_EN,
				'payment_with_benefit_route_unavailable' => 'Payment with Benefit route is unavailable. ' . self::DELEGATION_EN,
				'payment_reminder_route_unavailable' => 'Receipt reminder route is unavailable. ' . self::DELEGATION_EN,
				'bankpayment_receipt_route_unavailable' => 'Bank payment receipt route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'invoice_index_route_unavailable' => 'La ruta de facturas no está disponible. ' . self::DELEGATION_ES,
				'invoice_export_route_unavailable' => 'La ruta de exportación no está disponible.' . self::DELEGATION_ES,
				'invoice_create_route_unavailable' => 'La ruta de creación de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_copy_route_unavailable' => 'La ruta de copia de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_duplicate_route_unavailable' => 'La ruta de duplicación de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_show_route_unavailable' => 'La ruta de visualización de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_edit_route_unavailable' => 'La ruta de edición de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_delete_route_unavailablee' => 'La ruta de eliminación de factura no está disponible.' . self::DELEGATION_ES,
				'credit_note_route_unavailable' => 'La ruta para añadir nota de crédito no está disponible.' . self::DELEGATION_ES,
				'custom_credit_note_route_unavailable' => 'La ruta para añadir nota de crédito personalizada no está disponible.' . self::DELEGATION_ES,
				'edit_credit_note_route_unavailable' => 'La ruta para editar nota de crédito no está disponible.' . self::DELEGATION_ES,
				'delete_credit_note_route_unavailable' => 'La ruta para eliminar nota de crédito no está disponible.' . self::DELEGATION_ES,
				'change_status_route_unavailable' => 'La ruta para cambiar el estado no está disponible.' . self::DELEGATION_ES,
				'invoice_store_route_unavailable' => 'La ruta de creación de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_update_route_unavailable' => 'La ruta de actualización de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_customer_route_unavailable' => 'La ruta de cliente de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_payment_route_unavailable' => 'La ruta de pago de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_product_route_unavailable' => 'La ruta de producto de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_resend_route_unavailable' => 'La ruta para reenviar factura no está disponible.' . self::DELEGATION_ES,
				'invoice_pdf_route_unavailable' => 'La ruta para descargar PDF de factura no está disponible.' . self::DELEGATION_ES,
				'invoice_mark_sent_route_unavailable' => 'La ruta para enviar factura no está disponible.' . self::DELEGATION_ES,
				'link_copy_route_unavailable' => 'La ruta para copiar enlace de factura no está disponible.' . self::DELEGATION_ES,
				'payment_receipt_route_unavailable' => 'La ruta de recibo de pago no está disponible.' . self::DELEGATION_ES,
				'payment_add_receipt_download_route_unavailable' => 'La ruta para descargar recibo de pago no está disponible.' . self::DELEGATION_ES,
				'payment_receipt_view_unavailable' => 'La ruta de visualización de recibo no está disponible.' . self::DELEGATION_ES,
				'payment_add_receipt_view_unavailable' => 'La ruta de visualización de recibo no está disponible.' . self::DELEGATION_ES,
				'payment_add_receipt_route_unavailable' => 'La URL de recibo de pago no está disponible.' . self::DELEGATION_ES,
				'payment_delete_route_unavailable' => 'La ruta para eliminar pago no está disponible.' . self::DELEGATION_ES,
				'bank_payment_receipt_route_unavailable' => 'La ruta de recibo de pago bancario no está disponible.' . self::DELEGATION_ES,
				'payment_status_route_unavailable' => 'La ruta de estado de pago no está disponible.' . self::DELEGATION_ES,
				'payment_status_action_route_unavailable' => 'La ruta de acción de estado de pago no está disponible.' . self::DELEGATION_ES,
				'payment_destroy_route_unavailable' => 'La ruta para eliminar pago no está disponible.' . self::DELEGATION_ES,
				'pay_with_bank_route_unavailable' => 'La ruta de pago no está disponible.' . self::DELEGATION_ES,
				'payment_with_benefit_route_unavailable' => 'La ruta de pago con Benefit no está disponible.' . self::DELEGATION_ES,
				'payment_reminder_route_unavailable' => 'La ruta de recordatorio de recibo no está disponible.' . self::DELEGATION_ES,
				'bankpayment_receipt_route_unavailable' => 'La ruta de recibo de pago bancario no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'invoice_index_route_unavailable' => 'La route des factures est indisponible. ' . self::DELEGATION_FR,
				'invoice_export_route_unavailable' => 'La route d\'exportation est indisponible.' . self::DELEGATION_FR,
				'invoice_create_route_unavailable' => 'La route de création de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_copy_route_unavailable' => 'La route de copie de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_duplicate_route_unavailable' => 'La route de duplication de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_show_route_unavailable' => 'La route d\'affichage de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_edit_route_unavailable' => 'La route de modification de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_delete_route_unavailablee' => 'La route de suppression de facture est indisponible.' . self::DELEGATION_FR,
				'credit_note_route_unavailable' => 'La route d\'ajout d\'avoir est indisponible.' . self::DELEGATION_FR,
				'custom_credit_note_route_unavailable' => 'La route d\'ajout d\'avoir personnalisé est indisponible.' . self::DELEGATION_FR,
				'edit_credit_note_route_unavailable' => 'La route de modification d\'avoir est indisponible.' . self::DELEGATION_FR,
				'delete_credit_note_route_unavailable' => 'La route de suppression d\'avoir est indisponible.' . self::DELEGATION_FR,
				'change_status_route_unavailable' => 'La route de changement de statut est indisponible.' . self::DELEGATION_FR,
				'invoice_store_route_unavailable' => 'La route de création de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_update_route_unavailable' => 'La route de mise à jour de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_customer_route_unavailable' => 'La route de client de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_payment_route_unavailable' => 'La route de paiement de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_product_route_unavailable' => 'La route de produit de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_resend_route_unavailable' => 'La route de renvoi de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_pdf_route_unavailable' => 'La route de téléchargement PDF de facture est indisponible.' . self::DELEGATION_FR,
				'invoice_mark_sent_route_unavailable' => 'La route d\'envoi de facture est indisponible.' . self::DELEGATION_FR,
				'link_copy_route_unavailable' => 'La route de copie de lien de facture est indisponible.' . self::DELEGATION_FR,
				'payment_receipt_route_unavailable' => 'La route de reçu de paiement est indisponible.' . self::DELEGATION_FR,
				'payment_add_receipt_download_route_unavailable' => 'La route de téléchargement de reçu de paiement est indisponible.' . self::DELEGATION_FR,
				'payment_receipt_view_unavailable' => 'La route de visualisation de reçu est indisponible.' . self::DELEGATION_FR,
				'payment_add_receipt_view_unavailable' => 'La route de visualisation de reçu est indisponible.' . self::DELEGATION_FR,
				'payment_add_receipt_route_unavailable' => 'L\'URL de reçu de paiement est indisponible.' . self::DELEGATION_FR,
				'payment_delete_route_unavailable' => 'La route de suppression de paiement est indisponible.' . self::DELEGATION_FR,
				'bank_payment_receipt_route_unavailable' => 'La route de reçu de paiement bancaire est indisponible.' . self::DELEGATION_FR,
				'payment_status_route_unavailable' => 'La route de statut de paiement est indisponible.' . self::DELEGATION_FR,
				'payment_status_action_route_unavailable' => 'La route d\'action de statut de paiement est indisponible.' . self::DELEGATION_FR,
				'payment_destroy_route_unavailable' => 'La route de suppression de paiement est indisponible.' . self::DELEGATION_FR,
				'pay_with_bank_route_unavailable' => 'La route de paiement est indisponible.' . self::DELEGATION_FR,
				'payment_with_benefit_route_unavailable' => 'La route de paiement avec Benefit est indisponible.' . self::DELEGATION_FR,
				'payment_reminder_route_unavailable' => 'La route de rappel de reçu est indisponible.' . self::DELEGATION_FR,
				'bankpayment_receipt_route_unavailable' => 'La route de reçu de paiement bancaire est indisponible.' . self::DELEGATION_FR
			],
			'he' => [
				'invoice_index_route_unavailable' => 'נתיב חשבוניות אינו זמין. ' . self::DELEGATION_HE,
				'invoice_export_route_unavailable' => 'מסלול ייצוא אינו זמין. ' . self::DELEGATION_HE,
				'invoice_create_route_unavailable' => 'מסלול יצירת חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_copy_route_unavailable' => 'מסלול העתקת חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_duplicate_route_unavailable' => 'מסלול שכפול חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_show_route_unavailable' => 'מסלול הצגת חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_edit_route_unavailable' => 'מסלול עריכת חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_delete_route_unavailablee' => 'מסלול מחיקת חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'credit_note_route_unavailable' => 'מסלול הוספת חשבונית זיכוי אינו זמין. ' . self::DELEGATION_HE,
				'custom_credit_note_route_unavailable' => 'מסלול הוספת חשבונית זיכוי מותאמת אינו זמין. ' . self::DELEGATION_HE,
				'edit_credit_note_route_unavailable' => 'מסלול עריכת חשבונית זיכוי אינו זמין. ' . self::DELEGATION_HE,
				'delete_credit_note_route_unavailable' => 'מסלול מחיקת חשבונית זיכוי אינו זמין. ' . self::DELEGATION_HE,
				'change_status_route_unavailable' => 'מסלול שינוי סטטוס אינו זמין. ' . self::DELEGATION_HE,
				'invoice_store_route_unavailable' => 'מסלול יצירת חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_update_route_unavailable' => 'מסלול עדכון חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_customer_route_unavailable' => 'מסלול לקוח חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_payment_route_unavailable' => 'מסלול תשלום חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_product_route_unavailable' => 'מסלול מוצר חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_resend_route_unavailable' => 'מסלול שיחה חוזרת של חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_pdf_route_unavailable' => 'מסלול הורדת PDF חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'invoice_mark_sent_route_unavailable' => 'מסלול סימון חשבונית כנשלחת אינו זמין. ' . self::DELEGATION_HE,
				'link_copy_route_unavailable' => 'מסלול העתקת קישור חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'payment_receipt_route_unavailable' => 'מסלול קבלת תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_add_receipt_download_route_unavailable' => 'מסלול הורדת קבלת תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_receipt_view_unavailable' => 'מסלול צפייה בקבלה אינו זמין. ' . self::DELEGATION_HE,
				'payment_add_receipt_view_unavailable' => 'מסלול צפייה בקבלה אינו זמין. ' . self::DELEGATION_HE,
				'payment_add_receipt_route_unavailable' => 'כתובת URL לקבלת תשלום אינה זמינה. ' . self::DELEGATION_HE,
				'payment_delete_route_unavailable' => 'מסלול מחיקת תשלום אינו זמין. ' . self::DELEGATION_HE,
				'bank_payment_receipt_route_unavailable' => 'מסלול קבלת תשלום בנקאי אינו זמין. ' . self::DELEGATION_HE,
				'payment_status_route_unavailable' => 'מסלול סטטוס תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_status_action_route_unavailable' => 'מסלול פעולת סטטוס תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_destroy_route_unavailable' => 'מסלול מחיקת תשלום אינו זמין. ' . self::DELEGATION_HE,
				'pay_with_bank_route_unavailable' => 'מסלול תשלום אינו זמין. ' . self::DELEGATION_HE,
				'payment_with_benefit_route_unavailable' => 'מסלול תשלום עם Benefit אינו זמין. ' . self::DELEGATION_HE,
				'payment_reminder_route_unavailable' => 'מסלול תזכורת קבלה אינו זמין. ' . self::DELEGATION_HE,
				'bankpayment_receipt_route_unavailable' => 'מסלול קבלת תשלום בנקאי אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'invoice_index_route_unavailable' => 'La rotta delle fatture non è disponibile. ' . self::DELEGATION_IT,
				'invoice_export_route_unavailable' => 'La rotta di esportazione non è disponibile. ' . self::DELEGATION_IT,
				'invoice_create_route_unavailable' => 'La rotta di creazione della fattura non è disponibile. ' . self::DELEGATION_IT,
				'invoice_copy_route_unavailable' => 'La rotta di copia della fattura non è disponibile. ' . self::DELEGATION_IT,
				'invoice_duplicate_route_unavailable' => 'La rotta di duplicazione della fattura non è disponibile. ' . self::DELEGATION_IT,
				'invoice_show_route_unavailable' => 'La rotta di visualizzazione della fattura non è disponibile. ' . self::DELEGATION_IT,
				'invoice_edit_route_unavailable' => 'La rotta di modifica della fattura non è disponibile. ' . self::DELEGATION_IT,
				'invoice_delete_route_unavailablee' => 'La rotta di eliminazione della fattura non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'invoice_index_route_unavailable' => '請求書ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_export_route_unavailable' => 'エクスポートルートは利用できません。' . self::DELEGATION_JA,
				'invoice_create_route_unavailable' => '請求書作成ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_copy_route_unavailable' => '請求書コピールートは利用できません。' . self::DELEGATION_JA,
				'invoice_duplicate_route_unavailable' => '請求書複製ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_show_route_unavailable' => '請求書表示ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_edit_route_unavailable' => '請求書編集ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_delete_route_unavailablee' => '請求書削除ルートは利用できません。' . self::DELEGATION_JA,
				'credit_note_route_unavailable' => 'クレジットノート追加ルートは利用できません。' . self::DELEGATION_JA,
				'custom_credit_note_route_unavailable' => 'カスタムクレジットノート追加ルートは利用できません。' . self::DELEGATION_JA,
				'edit_credit_note_route_unavailable' => 'クレジットノート編集ルートは利用できません。' . self::DELEGATION_JA,
				'delete_credit_note_route_unavailable' => 'クレジットノート削除ルートは利用できません。' . self::DELEGATION_JA,
				'change_status_route_unavailable' => 'ステータス変更ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_store_route_unavailable' => '請求書作成ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_update_route_unavailable' => '請求書更新ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_customer_route_unavailable' => '請求書顧客ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_payment_route_unavailable' => '請求書支払いルートは利用できません。' . self::DELEGATION_JA,
				'invoice_product_route_unavailable' => '請求書製品ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_resend_route_unavailable' => '請求書再送ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_pdf_route_unavailable' => '請求書PDFダウンロードルートは利用できません。' . self::DELEGATION_JA,
				'invoice_mark_sent_route_unavailable' => '請求書送信ルートは利用できません。' . self::DELEGATION_JA,
				'link_copy_route_unavailable' => '請求書リンクコピールートは利用できません。' . self::DELEGATION_JA,
				'payment_receipt_route_unavailable' => '支払い領収書ルートは利用できません。' . self::DELEGATION_JA,
				'payment_add_receipt_download_route_unavailable' => '支払い領収書ダウンロードルートは利用できません。' . self::DELEGATION_JA,
				'payment_receipt_view_unavailable' => '領収書表示ルートは利用できません。' . self::DELEGATION_JA,
				'payment_add_receipt_view_unavailable' => '領収書表示ルートは利用できません。' . self::DELEGATION_JA,
				'payment_add_receipt_route_unavailable' => '支払い領収書URLは利用できません。' . self::DELEGATION_JA,
				'payment_delete_route_unavailable' => '支払い削除ルートは利用できません。' . self::DELEGATION_JA,
				'bank_payment_receipt_route_unavailable' => '銀行支払い領収書ルートは利用できません。' . self::DELEGATION_JA,
				'payment_status_route_unavailable' => '支払いステータスルートは利用できません。' . self::DELEGATION_JA,
				'payment_status_action_route_unavailable' => '支払いステータスアクションルートは利用できません。' . self::DELEGATION_JA,
				'payment_destroy_route_unavailable' => '支払い削除ルートは利用できません。' . self::DELEGATION_JA,
				'pay_with_bank_route_unavailable' => '支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_with_benefit_route_unavailable' => 'Benefitによる支払いルートは利用できません。' . self::DELEGATION_JA,
				'payment_reminder_route_unavailable' => '領収書リマインダールートは利用できません。' . self::DELEGATION_JA,
				'bankpayment_receipt_route_unavailable' => '銀行支払い領収書ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'invoice_index_route_unavailable' => 'Factuurroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_export_route_unavailable' => 'Exportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_create_route_unavailable' => 'Route voor het maken van facturen is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_copy_route_unavailable' => 'Route voor het kopiëren van facturen is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_duplicate_route_unavailable' => 'Route voor het dupliceren van facturen is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_show_route_unavailable' => 'Route voor het tonen van facturen is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_edit_route_unavailable' => 'Route voor het bewerken van facturen is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_delete_route_unavailablee' => 'Route voor het verwijderen van facturen is niet beschikbaar. ' . self::DELEGATION_NL,
				'credit_note_route_unavailable' => 'Route voor het toevoegen van creditnota is niet beschikbaar. ' . self::DELEGATION_NL,
				'custom_credit_note_route_unavailable' => 'Route voor het toevoegen van aangepaste creditnota is niet beschikbaar. ' . self::DELEGATION_NL,
				'edit_credit_note_route_unavailable' => 'Route voor het bewerken van creditnota is niet beschikbaar. ' . self::DELEGATION_NL,
				'delete_credit_note_route_unavailable' => 'Route voor het verwijderen van creditnota is niet beschikbaar. ' . self::DELEGATION_NL,
				'change_status_route_unavailable' => 'Route voor het wijzigen van status is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_store_route_unavailable' => 'Route voor het aanmaken van factuur is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_update_route_unavailable' => 'Route voor het bijwerken van factuur is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_customer_route_unavailable' => 'Route voor factuurklant is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_payment_route_unavailable' => 'Route voor factuurbetaling is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_product_route_unavailable' => 'Route voor factuurproduct is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_resend_route_unavailable' => 'Route voor het opnieuw verzenden van factuur is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_pdf_route_unavailable' => 'Route voor het downloaden van factuur PDF is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_mark_sent_route_unavailable' => 'Route voor het verzenden van factuur is niet beschikbaar. ' . self::DELEGATION_NL,
				'link_copy_route_unavailable' => 'Route voor het kopiëren van factuurlink is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_receipt_route_unavailable' => 'Route voor betalingsbewijs is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_add_receipt_download_route_unavailable' => 'Route voor het downloaden van betalingsbewijs is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_receipt_view_unavailable' => 'Route voor het bekijken van bewijs is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_add_receipt_view_unavailable' => 'Route voor het bekijken van bewijs is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_add_receipt_route_unavailable' => 'URL voor betalingsbewijs is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_delete_route_unavailable' => 'Route voor het verwijderen van betaling is niet beschikbaar. ' . self::DELEGATION_NL,
				'bank_payment_receipt_route_unavailable' => 'Route voor bankbetalingsbewijs is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_status_route_unavailable' => 'Route voor betalingsstatus is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_status_action_route_unavailable' => 'Route voor betalingsstatusactie is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_destroy_route_unavailable' => 'Route voor het verwijderen van betaling is niet beschikbaar. ' . self::DELEGATION_NL,
				'pay_with_bank_route_unavailable' => 'Betalingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_with_benefit_route_unavailable' => 'Betalingsroute met Benefit is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_reminder_route_unavailable' => 'Route voor bewijsherinnering is niet beschikbaar. ' . self::DELEGATION_NL,
				'bankpayment_receipt_route_unavailable' => 'Route voor bankbetalingsbewijs is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'invoice_index_route_unavailable' => 'Trasa faktur jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_export_route_unavailable' => 'Trasa eksportu jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_create_route_unavailable' => 'Trasa tworzenia faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_copy_route_unavailable' => 'Trasa kopiowania faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_duplicate_route_unavailable' => 'Trasa duplikowania faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_show_route_unavailable' => 'Trasa wyświetlania faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_edit_route_unavailable' => 'Trasa edycji faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_delete_route_unavailablee' => 'Trasa usuwania faktury jest niedostępna. ' . self::DELEGATION_PL,
				'credit_note_route_unavailable' => 'Trasa dodawania nota kredytowego jest niedostępna. ' . self::DELEGATION_PL,
				'custom_credit_note_route_unavailable' => 'Trasa dodawania niestandardowego nota kredytowego jest niedostępna. ' . self::DELEGATION_PL,
				'edit_credit_note_route_unavailable' => 'Trasa edycji nota kredytowego jest niedostępna. ' . self::DELEGATION_PL,
				'delete_credit_note_route_unavailable' => 'Trasa usuwania nota kredytowego jest niedostępna. ' . self::DELEGATION_PL,
				'change_status_route_unavailable' => 'Trasa zmiany statusu jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_store_route_unavailable' => 'Trasa tworzenia faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_update_route_unavailable' => 'Trasa aktualizacji faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_customer_route_unavailable' => 'Trasa klienta faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_payment_route_unavailable' => 'Trasa płatności faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_product_route_unavailable' => 'Trasa produktu faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_resend_route_unavailable' => 'Trasa ponownego wysyłania faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_pdf_route_unavailable' => 'Trasa pobierania PDF faktury jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_mark_sent_route_unavailable' => 'Trasa wysyłania faktury jest niedostępna. ' . self::DELEGATION_PL,
				'link_copy_route_unavailable' => 'Trasa kopiowania linku do faktury jest niedostępna. ' . self::DELEGATION_PL,
				'payment_receipt_route_unavailable' => 'Trasa potwierdzenia płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_add_receipt_download_route_unavailable' => 'Trasa pobierania potwierdzenia płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_receipt_view_unavailable' => 'Trasa przeglądania potwierdzenia jest niedostępna. ' . self::DELEGATION_PL,
				'payment_add_receipt_view_unavailable' => 'Trasa przeglądania potwierdzenia jest niedostępna. ' . self::DELEGATION_PL,
				'payment_add_receipt_route_unavailable' => 'URL potwierdzenia płatności jest niedostępny. ' . self::DELEGATION_PL,
				'payment_delete_route_unavailable' => 'Trasa usuwania płatności jest niedostępna. ' . self::DELEGATION_PL,
				'bank_payment_receipt_route_unavailable' => 'Trasa potwierdzenia płatności bankowej jest niedostępna. ' . self::DELEGATION_PL,
				'payment_status_route_unavailable' => 'Trasa statusu płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_status_action_route_unavailable' => 'Trasa akcji statusu płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_destroy_route_unavailable' => 'Trasa usuwania płatności jest niedostępna. ' . self::DELEGATION_PL,
				'pay_with_bank_route_unavailable' => 'Trasa płatności jest niedostępna. ' . self::DELEGATION_PL,
				'payment_with_benefit_route_unavailable' => 'Trasa płatności z Benefit jest niedostępna. ' . self::DELEGATION_PL,
				'payment_reminder_route_unavailable' => 'Trasa przypomnienia o potwierdzeniu jest niedostępna. ' . self::DELEGATION_PL,
				'bankpayment_receipt_route_unavailable' => 'Trasa potwierdzenia płatności bankowej jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'invoice_index_route_unavailable' => 'A rota de faturas não está disponível. ' . self::DELEGATION_PT,
				'invoice_export_route_unavailable' => 'A rota de exportação não está disponível.' . self::DELEGATION_PT,
				'invoice_create_route_unavailable' => 'A rota de criação de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_copy_route_unavailable' => 'A rota de cópia de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_duplicate_route_unavailable' => 'A rota de duplicação de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_show_route_unavailable' => 'A rota de exibição de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_edit_route_unavailable' => 'A rota de edição de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_delete_route_unavailablee' => 'A rota de eliminação de fatura não está disponível.' . self::DELEGATION_PT,
				'credit_note_route_unavailable' => 'A rota para adicionar nota de crédito não está disponível.' . self::DELEGATION_PT,
				'custom_credit_note_route_unavailable' => 'A rota para adicionar nota de crédito personalizada não está disponível.' . self::DELEGATION_PT,
				'edit_credit_note_route_unavailable' => 'A rota para editar nota de crédito não está disponível.' . self::DELEGATION_PT,
				'delete_credit_note_route_unavailable' => 'A rota para eliminar nota de crédito não está disponível.' . self::DELEGATION_PT,
				'change_status_route_unavailable' => 'A rota para alterar estado não está disponível.' . self::DELEGATION_PT,
				'invoice_store_route_unavailable' => 'A rota de criação de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_update_route_unavailable' => 'A rota de atualização de fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_customer_route_unavailable' => 'A rota de cliente da fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_payment_route_unavailable' => 'A rota de pagamento da fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_product_route_unavailable' => 'A rota de produto da fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_resend_route_unavailable' => 'A rota para reenviar fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_pdf_route_unavailable' => 'A rota para descarregar PDF da fatura não está disponível.' . self::DELEGATION_PT,
				'invoice_mark_sent_route_unavailable' => 'A rota para enviar fatura não está disponível.' . self::DELEGATION_PT,
				'link_copy_route_unavailable' => 'A rota para copiar link da fatura não está disponível.' . self::DELEGATION_PT,
				'payment_receipt_route_unavailable' => 'A rota de recibo de pagamento não está disponível.' . self::DELEGATION_PT,
				'payment_add_receipt_download_route_unavailable' => 'A rota para descarregar recibo de pagamento não está disponível.' . self::DELEGATION_PT,
				'payment_receipt_view_unavailable' => 'A rota de visualização de recibo não está disponível.' . self::DELEGATION_PT,
				'payment_add_receipt_view_unavailable' => 'A rota de visualização de recibo não está disponível.' . self::DELEGATION_PT,
				'payment_add_receipt_route_unavailable' => 'O URL do recibo de pagamento não está disponível.' . self::DELEGATION_PT,
				'payment_delete_route_unavailable' => 'A rota para eliminar pagamento não está disponível.' . self::DELEGATION_PT,
				'bank_payment_receipt_route_unavailable' => 'A rota de recibo de pagamento bancário não está disponível.' . self::DELEGATION_PT,
				'payment_status_route_unavailable' => 'A rota de estado de pagamento não está disponível.' . self::DELEGATION_PT,
				'payment_status_action_route_unavailable' => 'A rota de ação de estado de pagamento não está disponível.' . self::DELEGATION_PT,
				'payment_destroy_route_unavailable' => 'A rota para eliminar pagamento não está disponível.' . self::DELEGATION_PT,
				'pay_with_bank_route_unavailable' => 'A rota de pagamento não está disponível.' . self::DELEGATION_PT,
				'payment_with_benefit_route_unavailable' => 'A rota de pagamento com Benefit não está disponível.' . self::DELEGATION_PT,
				'payment_reminder_route_unavailable' => 'A rota de lembrete de recibo não está disponível.' . self::DELEGATION_PT,
				'bankpayment_receipt_route_unavailable' => 'A rota de recibo de pagamento bancário não está disponível.' . self::DELEGATION_PT
			],
			'pt-br' => [
				'invoice_index_route_unavailable' => 'A rota de faturas não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_export_route_unavailable' => 'A rota de exportação não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_create_route_unavailable' => 'A rota de criação de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_copy_route_unavailable' => 'A rota de cópia de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_duplicate_route_unavailable' => 'A rota de duplicação de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_show_route_unavailable' => 'A rota de exibição de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_edit_route_unavailable' => 'A rota de edição de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_delete_route_unavailablee' => 'A rota de exclusão de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'credit_note_route_unavailable' => 'A rota para adicionar nota de crédito não está disponível. ' . self::DELEGATION_PTBR,
				'custom_credit_note_route_unavailable' => 'A rota para adicionar nota de crédito personalizada não está disponível. ' . self::DELEGATION_PTBR,
				'edit_credit_note_route_unavailable' => 'A rota para editar nota de crédito não está disponível. ' . self::DELEGATION_PTBR,
				'delete_credit_note_route_unavailable' => 'A rota para excluir nota de crédito não está disponível. ' . self::DELEGATION_PTBR,
				'change_status_route_unavailable' => 'A rota para alterar status não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_store_route_unavailable' => 'A rota de criação de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_update_route_unavailable' => 'A rota de atualização de fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_customer_route_unavailable' => 'A rota de cliente da fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_payment_route_unavailable' => 'A rota de pagamento da fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_product_route_unavailable' => 'A rota de produto da fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_resend_route_unavailable' => 'A rota para reenviar fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_pdf_route_unavailable' => 'A rota para baixar PDF da fatura não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_mark_sent_route_unavailable' => 'A rota para enviar fatura não está disponível. ' . self::DELEGATION_PTBR,
				'link_copy_route_unavailable' => 'A rota para copiar link da fatura não está disponível. ' . self::DELEGATION_PTBR,
				'payment_receipt_route_unavailable' => 'A rota de recibo de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_add_receipt_download_route_unavailable' => 'A rota para baixar recibo de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_receipt_view_unavailable' => 'A rota de visualização de recibo não está disponível. ' . self::DELEGATION_PTBR,
				'payment_add_receipt_view_unavailable' => 'A rota de visualização de recibo não está disponível. ' . self::DELEGATION_PTBR,
				'payment_add_receipt_route_unavailable' => 'A URL do recibo de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_delete_route_unavailable' => 'A rota para excluir pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'bank_payment_receipt_route_unavailable' => 'A rota de recibo de pagamento bancário não está disponível. ' . self::DELEGATION_PTBR,
				'payment_status_route_unavailable' => 'A rota de status de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_status_action_route_unavailable' => 'A rota de ação de status de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_destroy_route_unavailable' => 'A rota para excluir pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'pay_with_bank_route_unavailable' => 'A rota de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'payment_with_benefit_route_unavailable' => 'A rota de pagamento com Benefit não está disponível. ' . self::DELEGATION_PTBR,
				'payment_reminder_route_unavailable' => 'A rota de lembrete de recibo não está disponível. ' . self::DELEGATION_PTBR,
				'bankpayment_receipt_route_unavailable' => 'A rota de recibo de pagamento bancário não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'invoice_index_route_unavailable' => 'Маршрут счетов недоступен. ' . self::DELEGATION_RU,
				'invoice_export_route_unavailable' => 'Маршрут экспорта недоступен. ' . self::DELEGATION_RU,
				'invoice_create_route_unavailable' => 'Маршрут создания счета недоступен. ' . self::DELEGATION_RU,
				'invoice_copy_route_unavailable' => 'Маршрут копирования счета недоступен. ' . self::DELEGATION_RU,
				'invoice_duplicate_route_unavailable' => 'Маршрут дублирования счета недоступен. ' . self::DELEGATION_RU,
				'invoice_show_route_unavailable' => 'Маршрут отображения счета недоступен. ' . self::DELEGATION_RU,
				'invoice_edit_route_unavailable' => 'Маршрут редактирования счета недоступен. ' . self::DELEGATION_RU,
				'invoice_delete_route_unavailablee' => 'Маршрут удаления счета недоступен. ' . self::DELEGATION_RU,
				'credit_note_route_unavailable' => 'Маршрут добавления кредитной ноты недоступен. ' . self::DELEGATION_RU,
				'custom_credit_note_route_unavailable' => 'Маршрут добавления пользовательской кредитной ноты недоступен. ' . self::DELEGATION_RU,
				'edit_credit_note_route_unavailable' => 'Маршрут редактирования кредитной ноты недоступен. ' . self::DELEGATION_RU,
				'delete_credit_note_route_unavailable' => 'Маршрут удаления кредитной ноты недоступен. ' . self::DELEGATION_RU,
				'change_status_route_unavailable' => 'Маршрут изменения статуса недоступен. ' . self::DELEGATION_RU,
				'invoice_store_route_unavailable' => 'Маршрут создания счета недоступен. ' . self::DELEGATION_RU,
				'invoice_update_route_unavailable' => 'Маршрут обновления счета недоступен. ' . self::DELEGATION_RU,
				'invoice_customer_route_unavailable' => 'Маршрут клиента счета недоступен. ' . self::DELEGATION_RU,
				'invoice_payment_route_unavailable' => 'Маршрут оплаты счета недоступен. ' . self::DELEGATION_RU,
				'invoice_product_route_unavailable' => 'Маршрут товара счета недоступен. ' . self::DELEGATION_RU,
				'invoice_resend_route_unavailable' => 'Маршрут повторной отправки счета недоступен. ' . self::DELEGATION_RU,
				'invoice_pdf_route_unavailable' => 'Маршрут загрузки PDF счета недоступен. ' . self::DELEGATION_RU,
				'invoice_mark_sent_route_unavailable' => 'Маршрут отправки счета недоступен. ' . self::DELEGATION_RU,
				'link_copy_route_unavailable' => 'Маршрут копирования ссылки на счет недоступен. ' . self::DELEGATION_RU,
				'payment_receipt_route_unavailable' => 'Маршрут квитанции об оплате недоступен. ' . self::DELEGATION_RU,
				'payment_add_receipt_download_route_unavailable' => 'Маршрут загрузки квитанции об оплате недоступен. ' . self::DELEGATION_RU,
				'payment_receipt_view_unavailable' => 'Маршрут просмотра квитанции недоступен. ' . self::DELEGATION_RU,
				'payment_add_receipt_view_unavailable' => 'Маршрут просмотра квитанции недоступен. ' . self::DELEGATION_RU,
				'payment_add_receipt_route_unavailable' => 'URL квитанции об оплате недоступен. ' . self::DELEGATION_RU,
				'payment_delete_route_unavailable' => 'Маршрут удаления платежа недоступен. ' . self::DELEGATION_RU,
				'bank_payment_receipt_route_unavailable' => 'Маршрут квитанции банковского платежа недоступен. ' . self::DELEGATION_RU,
				'payment_status_route_unavailable' => 'Маршрут статуса платежа недоступен. ' . self::DELEGATION_RU,
				'payment_status_action_route_unavailable' => 'Маршрут действия статуса платежа недоступен. ' . self::DELEGATION_RU,
				'payment_destroy_route_unavailable' => 'Маршрут удаления платежа недоступен. ' . self::DELEGATION_RU,
				'pay_with_bank_route_unavailable' => 'Маршрут оплаты недоступен. ' . self::DELEGATION_RU,
				'payment_with_benefit_route_unavailable' => 'Маршрут оплаты с Benefit недоступен. ' . self::DELEGATION_RU,
				'payment_reminder_route_unavailable' => 'Маршрут напоминания о квитанции недоступен. ' . self::DELEGATION_RU,
				'bankpayment_receipt_route_unavailable' => 'Маршрут квитанции банковского платежа недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'invoice_index_route_unavailable' => 'Fatura rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_export_route_unavailable' => 'Dışa aktarma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_create_route_unavailable' => 'Fatura oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_copy_route_unavailable' => 'Fatura kopyalama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_duplicate_route_unavailable' => 'Fatura çoğaltma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_show_route_unavailable' => 'Fatura görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_edit_route_unavailable' => 'Fatura düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_delete_route_unavailablee' => 'Fatura silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'credit_note_route_unavailable' => 'Kredi notu ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'custom_credit_note_route_unavailable' => 'Özel kredi notu ekleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'edit_credit_note_route_unavailable' => 'Kredi notu düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'delete_credit_note_route_unavailable' => 'Kredi notu silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'change_status_route_unavailable' => 'Durum değiştirme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_store_route_unavailable' => 'Fatura oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_update_route_unavailable' => 'Fatura güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_customer_route_unavailable' => 'Fatura müşteri rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_payment_route_unavailable' => 'Fatura ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_product_route_unavailable' => 'Fatura ürün rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_resend_route_unavailable' => 'Fatura yeniden gönderme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_pdf_route_unavailable' => 'Fatura PDF indirme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_mark_sent_route_unavailable' => 'Fatura gönderme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'link_copy_route_unavailable' => 'Fatura bağlantısı kopyalama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_receipt_route_unavailable' => 'Ödeme makbuzu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_add_receipt_download_route_unavailable' => 'Ödeme makbuzu indirme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_receipt_view_unavailable' => 'Makbuz görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_add_receipt_view_unavailable' => 'Makbuz görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_add_receipt_route_unavailable' => 'Ödeme makbuzu URL\'si kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_delete_route_unavailable' => 'Ödeme silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bank_payment_receipt_route_unavailable' => 'Banka ödeme makbuzu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_status_route_unavailable' => 'Ödeme durumu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_status_action_route_unavailable' => 'Ödeme durumu eylemi rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_destroy_route_unavailable' => 'Ödeme silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pay_with_bank_route_unavailable' => 'Ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_with_benefit_route_unavailable' => 'Benefit ile ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_reminder_route_unavailable' => 'Makbuz hatırlatma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bankpayment_receipt_route_unavailable' => 'Banka ödeme makbuzu rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'invoice_index_route_unavailable' => '发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_export_route_unavailable' => '导出路由不可用。' . self::DELEGATION_ZH,
				'invoice_create_route_unavailable' => '创建发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_copy_route_unavailable' => '复制发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_duplicate_route_unavailable' => '复制发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_show_route_unavailable' => '显示发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_edit_route_unavailable' => '编辑发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_delete_route_unavailablee' => '删除发票路由不可用。' . self::DELEGATION_ZH,
				'credit_note_route_unavailable' => '添加信用票据路由不可用。' . self::DELEGATION_ZH,
				'custom_credit_note_route_unavailable' => '添加自定义信用票据路由不可用。' . self::DELEGATION_ZH,
				'edit_credit_note_route_unavailable' => '编辑信用票据路由不可用。' . self::DELEGATION_ZH,
				'delete_credit_note_route_unavailable' => '删除信用票据路由不可用。' . self::DELEGATION_ZH,
				'change_status_route_unavailable' => '更改状态路由不可用。' . self::DELEGATION_ZH,
				'invoice_store_route_unavailable' => '创建发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_update_route_unavailable' => '更新发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_customer_route_unavailable' => '发票客户路由不可用。' . self::DELEGATION_ZH,
				'invoice_payment_route_unavailable' => '发票支付路由不可用。' . self::DELEGATION_ZH,
				'invoice_product_route_unavailable' => '发票产品路由不可用。' . self::DELEGATION_ZH,
				'invoice_resend_route_unavailable' => '重新发送发票路由不可用。' . self::DELEGATION_ZH,
				'invoice_pdf_route_unavailable' => '下载发票PDF路由不可用。' . self::DELEGATION_ZH,
				'invoice_mark_sent_route_unavailable' => '发送发票路由不可用。' . self::DELEGATION_ZH,
				'link_copy_route_unavailable' => '发票链接复制路由不可用。' . self::DELEGATION_ZH,
				'payment_receipt_route_unavailable' => '支付收据路由不可用。' . self::DELEGATION_ZH,
				'payment_add_receipt_download_route_unavailable' => '支付收据下载路由不可用。' . self::DELEGATION_ZH,
				'payment_receipt_view_unavailable' => '收据查看路由不可用。' . self::DELEGATION_ZH,
				'payment_add_receipt_view_unavailable' => '收据查看路由不可用。' . self::DELEGATION_ZH,
				'payment_add_receipt_route_unavailable' => '支付收据URL不可用。' . self::DELEGATION_ZH,
				'payment_delete_route_unavailable' => '删除支付路由不可用。' . self::DELEGATION_ZH,
				'bank_payment_receipt_route_unavailable' => '银行支付收据路由不可用。' . self::DELEGATION_ZH,
				'payment_status_route_unavailable' => '支付状态路由不可用。' . self::DELEGATION_ZH,
				'payment_status_action_route_unavailable' => '支付状态操作路由不可用。' . self::DELEGATION_ZH,
				'payment_destroy_route_unavailable' => '删除支付路由不可用。' . self::DELEGATION_ZH,
				'pay_with_bank_route_unavailable' => '支付路由不可用。' . self::DELEGATION_ZH,
				'payment_with_benefit_route_unavailable' => '使用Benefit支付路由不可用。' . self::DELEGATION_ZH,
				'payment_reminder_route_unavailable' => '收据提醒路由不可用。' . self::DELEGATION_ZH,
				'bankpayment_receipt_route_unavailable' => '银行支付收据路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::ITV_SCD => [
			'ar' => [
				'interview_schedule_index_route_unavailable' => 'مسار جدولة المقابلات غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'interview_schedule_index_route_unavailable' => 'Interviewplanlægningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'interview_schedule_index_route_unavailable' => 'Interviewplanungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'interview_schedule_index_route_unavailable' => 'Interview Schedule route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'interview_schedule_index_route_unavailable' => 'La ruta de programación de entrevistas no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'interview_schedule_index_route_unavailable' => 'La route de planification d\'entretien est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'interview_schedule_index_route_unavailable' => 'נתיב לוח זמנים לראיונות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'interview_schedule_index_route_unavailable' => 'La rotta della pianificazione dei colloqui non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'interview_schedule_index_route_unavailable' => '面接スケジュールルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'interview_schedule_index_route_unavailable' => 'Interviewplanningsroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'interview_schedule_index_route_unavailable' => 'Trasa harmonogramu rozmów kwalifikacyjnych jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'interview_schedule_index_route_unavailable' => 'A rota de agendamento de entrevistas não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'interview_schedule_index_route_unavailable' => 'A rota de agendamento de entrevistas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'interview_schedule_index_route_unavailable' => 'Маршрут расписания собеседований недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'interview_schedule_index_route_unavailable' => 'Mülakat programlama rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'interview_schedule_index_route_unavailable' => '面试安排路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::JB => [
			'ar' => [
				'job_index_route_unavailable' => 'مسار الوظائف غير متاح. ' . self::DELEGATION_AR,
				'job_create_route_unavailable' => 'مسار إنشاء الوظائف غير متاح. ' . self::DELEGATION_AR,
				'job_application_index_route_unavailable' => 'مسار التقدم للوظائف غير متاح. ' . self::DELEGATION_AR,
				'job_candidate_route_unavailable' => 'مسار مرشحي الوظائف غير متاح. ' . self::DELEGATION_AR,
				'job_on_board_route_unavailable' => 'مسار التعيين في الوظائف غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'job_index_route_unavailable' => 'Job-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'job_create_route_unavailable' => 'Joboprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'job_application_index_route_unavailable' => 'Jobansøgningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'job_candidate_route_unavailable' => 'Jobkandidatrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'job_on_board_route_unavailable' => 'Job onboarding-rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'job_index_route_unavailable' => 'Stellen-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'job_create_route_unavailable' => 'Stellenerstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'job_application_index_route_unavailable' => 'Stellenbewerbungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'job_candidate_route_unavailable' => 'Stellenkandidaten-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'job_on_board_route_unavailable' => 'Stellen-Onboarding-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'job_index_route_unavailable' => 'Jobs route is unavailable. ' . self::DELEGATION_EN,
				'job_create_route_unavailable' => 'Job Create route is unavailable. ' . self::DELEGATION_EN,
				'job_application_index_route_unavailable' => 'Job Application route is unavailable. ' . self::DELEGATION_EN,
				'job_candidate_route_unavailable' => 'Job Candidate route is unavailable. ' . self::DELEGATION_EN,
				'job_on_board_route_unavailable' => 'Job On-boarding route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'job_index_route_unavailable' => 'La ruta de trabajos no está disponible. ' . self::DELEGATION_ES,
				'job_create_route_unavailable' => 'La ruta de creación de trabajos no está disponible. ' . self::DELEGATION_ES,
				'job_application_index_route_unavailable' => 'La ruta de solicitud de empleo no está disponible. ' . self::DELEGATION_ES,
				'job_candidate_route_unavailable' => 'La ruta de candidatos a empleo no está disponible. ' . self::DELEGATION_ES,
				'job_on_board_route_unavailable' => 'La ruta de incorporación laboral no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'job_index_route_unavailable' => 'La route des emplois est indisponible. ' . self::DELEGATION_FR,
				'job_create_route_unavailable' => 'La route de création d\'emploi est indisponible. ' . self::DELEGATION_FR,
				'job_application_index_route_unavailable' => 'La route de candidature est indisponible. ' . self::DELEGATION_FR,
				'job_candidate_route_unavailable' => 'La route des candidats à l\'emploi est indisponible. ' . self::DELEGATION_FR,
				'job_on_board_route_unavailable' => 'La route d\'intégration professionnelle est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'job_index_route_unavailable' => 'נתיב המשרות אינו זמין. ' . self::DELEGATION_HE,
				'job_create_route_unavailable' => 'נתיב יצירת משרה אינו זמין. ' . self::DELEGATION_HE,
				'job_application_index_route_unavailable' => 'נתיב הגשת מועמדות למשרה אינו זמין. ' . self::DELEGATION_HE,
				'job_candidate_route_unavailable' => 'נתיב מועמדים למשרה אינו זמין. ' . self::DELEGATION_HE,
				'job_on_board_route_unavailable' => 'נתיב הצטרפות למשרה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'job_index_route_unavailable' => 'La rotta dei lavori non è disponibile. ' . self::DELEGATION_IT,
				'job_create_route_unavailable' => 'La rotta di creazione lavoro non è disponibile. ' . self::DELEGATION_IT,
				'job_application_index_route_unavailable' => 'La rotta delle candidature non è disponibile. ' . self::DELEGATION_IT,
				'job_candidate_route_unavailable' => 'La rotta dei candidati al lavoro non è disponibile. ' . self::DELEGATION_IT,
				'job_on_board_route_unavailable' => 'La rotta di onboarding lavoro non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'job_index_route_unavailable' => '求人ルートは利用できません。' . self::DELEGATION_JA,
				'job_create_route_unavailable' => '求人作成ルートは利用できません。' . self::DELEGATION_JA,
				'job_application_index_route_unavailable' => '求人応募ルートは利用できません。' . self::DELEGATION_JA,
				'job_candidate_route_unavailable' => '求人候補者ルートは利用できません。' . self::DELEGATION_JA,
				'job_on_board_route_unavailable' => '求人オンボーディングルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'job_index_route_unavailable' => 'Banenroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'job_create_route_unavailable' => 'Banenaanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'job_application_index_route_unavailable' => 'Baan sollicitatieroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'job_candidate_route_unavailable' => 'Banenkandidaatroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'job_on_board_route_unavailable' => 'Baan onboardingroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'job_index_route_unavailable' => 'Trasa stanowisk jest niedostępna. ' . self::DELEGATION_PL,
				'job_create_route_unavailable' => 'Trasa tworzenia stanowiska jest niedostępna. ' . self::DELEGATION_PL,
				'job_application_index_route_unavailable' => 'Trasa aplikacji o pracę jest niedostępna. ' . self::DELEGATION_PL,
				'job_candidate_route_unavailable' => 'Trasa kandydatów do pracy jest niedostępna. ' . self::DELEGATION_PL,
				'job_on_board_route_unavailable' => 'Trasa onboardingu stanowiska jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'job_index_route_unavailable' => 'A rota de trabalhos não está disponível. ' . self::DELEGATION_PT,
				'job_create_route_unavailable' => 'A rota de criação de trabalho não está disponível. ' . self::DELEGATION_PT,
				'job_application_index_route_unavailable' => 'A rota de candidatura a emprego não está disponível. ' . self::DELEGATION_PT,
				'job_candidate_route_unavailable' => 'A rota de candidatos a emprego não está disponível. ' . self::DELEGATION_PT,
				'job_on_board_route_unavailable' => 'A rota de integração profissional não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'job_index_route_unavailable' => 'A rota de empregos não está disponível. ' . self::DELEGATION_PTBR,
				'job_create_route_unavailable' => 'A rota de criação de emprego não está disponível. ' . self::DELEGATION_PTBR,
				'job_application_index_route_unavailable' => 'A rota de candidatura a emprego não está disponível. ' . self::DELEGATION_PTBR,
				'job_candidate_route_unavailable' => 'A rota de candidatos a emprego não está disponível. ' . self::DELEGATION_PTBR,
				'job_on_board_route_unavailable' => 'A rota de integração profissional não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'job_index_route_unavailable' => 'Маршрут вакансий недоступен. ' . self::DELEGATION_RU,
				'job_create_route_unavailable' => 'Маршрут создания вакансий недоступен. ' . self::DELEGATION_RU,
				'job_application_index_route_unavailable' => 'Маршрут подачи заявок недоступен. ' . self::DELEGATION_RU,
				'job_candidate_route_unavailable' => 'Маршрут кандидатов на вакансию недоступен. ' . self::DELEGATION_RU,
				'job_on_board_route_unavailable' => 'Маршрут адаптации сотрудников недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'job_index_route_unavailable' => 'İş rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'job_create_route_unavailable' => 'İş oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'job_application_index_route_unavailable' => 'İş başvurusu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'job_candidate_route_unavailable' => 'İş adayı rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'job_on_board_route_unavailable' => 'İşe alıştırma rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'job_index_route_unavailable' => '工作路由不可用。' . self::DELEGATION_ZH,
				'job_create_route_unavailable' => '工作创建路由不可用。' . self::DELEGATION_ZH,
				'job_application_index_route_unavailable' => '工作申请路由不可用。' . self::DELEGATION_ZH,
				'job_candidate_route_unavailable' => '工作候选人路由不可用。' . self::DELEGATION_ZH,
				'job_on_board_route_unavailable' => '工作入职路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::JB_STG => [
			'edit_job_stage_unavailable' => [
				'ar' => 'مسار تعديل مرحلة الوظيفة غير متاح. ' . self::DELEGATION_AR,
				'da' => 'Ruten til redigering af jobstadie er ikke tilgængelig. ' . self::DELEGATION_DA,
				'de' => 'Die Route zum Bearbeiten der Jobphase ist nicht verfügbar. ' . self::DELEGATION_DE,
				'en' => 'Edit job stage route is unavailable. ' . self::DELEGATION_EN,
				'es' => 'La ruta de edición de etapa de trabajo no está disponible. ' . self::DELEGATION_ES,
				'fr' => 'La route d\'édition des étapes du job n\'est pas disponible. ' . self::DELEGATION_FR,
				'he' => 'נתיב עריכת שלב העבודה אינו זמין. ' . self::DELEGATION_HE,
				'it' => 'La rotta di modifica della fase di lavoro non è disponibile. ' . self::DELEGATION_IT,
				'ja' => '求人ステージ編集ルートが利用できません。' . self::DELEGATION_JA,
				'nl' => 'Bewerkingsroute voor jobfase is niet beschikbaar. ' . self::DELEGATION_NL,
				'pl' => 'Trasa edycji etapu pracy jest niedostępna. ' . self::DELEGATION_PL,
				'pt' => 'A rota de edição da fase de trabalho não está disponível. ' . self::DELEGATION_PT,
				'pt-br' => 'A rota para editar estágio de trabalho não está disponível. ' . self::DELEGATION_PTBR,
				'ru' => 'Маршрут редактирования этапа вакансии недоступен. ' . self::DELEGATION_RU,
				'tr' => 'İş aşaması düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'zh' => '编辑工作阶段路由不可用。' . self::DELEGATION_ZH,
			],
			'delete_job_stage_unavailable' => [
				'ar' => 'مسار حذف مرحلة الوظيفة غير متاح. ' . self::DELEGATION_AR,
				'da' => 'Ruten til sletning af jobstadie er ikke tilgængelig. ' . self::DELEGATION_DA,
				'de' => 'Die Route zum Löschen der Jobphase ist nicht verfügbar. ' . self::DELEGATION_DE,
				'en' => 'Delete job stage route is unavailable. ' . self::DELEGATION_EN,
				'es' => 'La ruta de eliminación de etapa de trabajo no está disponible. ' . self::DELEGATION_ES,
				'fr' => 'La route de suppression des étapes du job n\'est pas disponible. ' . self::DELEGATION_FR,
				'he' => 'נתיב מחיקת שלב העבודה אינו זמין. ' . self::DELEGATION_HE,
				'it' => 'La rotta di eliminazione della fase di lavoro non è disponibile. ' . self::DELEGATION_IT,
				'ja' => '求人ステージ削除ルートが利用できません。' . self::DELEGATION_JA,
				'nl' => 'Verwijderingsroute voor jobfase is niet beschikbaar. ' . self::DELEGATION_NL,
				'pl' => 'Trasa usuwania etapu pracy jest niedostępna. ' . self::DELEGATION_PL,
				'pt' => 'A rota de eliminação da fase de trabalho não está disponível. ' . self::DELEGATION_PT,
				'pt-br' => 'A rota para excluir estágio de trabalho não está disponível. ' . self::DELEGATION_PTBR,
				'ru' => 'Маршрут удаления этапа вакансии недоступен. ' . self::DELEGATION_RU,
				'tr' => 'İş aşaması silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'zh' => '删除工作阶段路由不可用。' . self::DELEGATION_ZH,
			],
		],
		ViewsConstants::JRN_IT => [
			'ar' => [
				'edit_job_stage_unavailable' => 'مسار تعديل مرحلة الوظيفة غير متاح. ' . self::DELEGATION_AR,
				'delete_job_stage_unavailable' => 'مسار حذف مرحلة الوظيفة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'edit_job_stage_unavailable' => 'Ruten til redigering af jobstadie er ikke tilgængelig. ' . self::DELEGATION_DA,
				'delete_job_stage_unavailable' => 'Ruten til sletning af jobstadie er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'edit_job_stage_unavailable' => 'Die Route zum Bearbeiten der Jobphase ist nicht verfügbar. ' . self::DELEGATION_DE,
				'delete_job_stage_unavailable' => 'Die Route zum Löschen der Jobphase ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'edit_job_stage_unavailable' => 'Edit job stage route is unavailable. ' . self::DELEGATION_EN,
				'delete_job_stage_unavailable' => 'Delete job stage route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'edit_job_stage_unavailable' => 'La ruta de edición de etapa de trabajo no está disponible.' . self::DELEGATION_ES,
				'delete_job_stage_unavailable' => 'La ruta de eliminación de etapa de trabajo no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'edit_job_stage_unavailable' => 'La route d\'édition des étapes du job n\'est pas disponible. ' . self::DELEGATION_FR,
				'delete_job_stage_unavailable' => 'La route de suppression des étapes du job n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'edit_job_stage_unavailable' => 'נתיב עריכת שלב העבודה אינו זמין. ' . self::DELEGATION_HE,
				'delete_job_stage_unavailable' => 'נתיב מחיקת שלב העבודה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'edit_job_stage_unavailable' => 'La rotta di modifica della fase di lavoro non è disponibile. ' . self::DELEGATION_IT,
				'delete_job_stage_unavailable' => 'La rotta di eliminazione della fase di lavoro non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'edit_job_stage_unavailable' => '求人ステージ編集ルートが利用できません。' . self::DELEGATION_JA,
				'delete_job_stage_unavailable' => '求人ステージ削除ルートが利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'edit_job_stage_unavailable' => 'Bewerkingsroute voor jobfase is niet beschikbaar. ' . self::DELEGATION_NL,
				'delete_job_stage_unavailable' => 'Verwijderingsroute voor jobfase is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'edit_job_stage_unavailable' => 'Trasa edycji etapu pracy jest niedostępna. ' . self::DELEGATION_PL,
				'delete_job_stage_unavailable' => 'Trasa usuwania etapu pracy jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'edit_job_stage_unavailable' => 'A rota de edição da fase de trabalho não está disponível. ' . self::DELEGATION_PT,
				'delete_job_stage_unavailable' => 'A rota de eliminação da fase de trabalho não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'edit_job_stage_unavailable' => 'A rota para editar estágio de trabalho não está disponível. ' . self::DELEGATION_PTBR,
				'delete_job_stage_unavailable' => 'A rota para excluir estágio de trabalho não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'edit_job_stage_unavailable' => 'Маршрут редактирования этапа вакансии недоступен. ' . self::DELEGATION_RU,
				'delete_job_stage_unavailable' => 'Маршрут удаления этапа вакансии недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'edit_job_stage_unavailable' => 'İş aşaması düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'delete_job_stage_unavailable' => 'İş aşaması silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'edit_job_stage_unavailable' => '编辑工作阶段路由不可用。' . self::DELEGATION_ZH,
				'delete_job_stage_unavailable' => '删除工作阶段路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::JRN_ET => [
			'ar' => [
				'create_journal_entry_unavailable' => 'مسار إنشاء دفتر جديد غير متاح. ' . self::DELEGATION_AR,
				'show_journal_entry_unavailable' => 'مسار عرض إدخال دفتر اليومية غير متاح. ' . self::DELEGATION_AR,
				'jrn_et_index_route_unavailable' => 'مسار قيود اليومية غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'create_journal_entry_unavailable' => 'Oprettelsesruten for ny journal er ikke tilgængelig. ' . self::DELEGATION_DA,
				'show_journal_entry_unavailable' => 'Visningsruten for journalpost er ikke tilgængelig. ' . self::DELEGATION_DA,
				'jrn_et_index_route_unavailable' => 'Journalpostrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'create_journal_entry_unavailable' => 'Erstellungsroute für neues Journal nicht verfügbar. ' . self::DELEGATION_DE,
				'show_journal_entry_unavailable' => 'Anzeigeroute für Journaleintrag nicht verfügbar. ' . self::DELEGATION_DE,
				'jrn_et_index_route_unavailable' => 'Journalbuchungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'create_journal_entry_unavailable' => 'Create New Journal route is unavailable. ' . self::DELEGATION_EN,
				'show_journal_entry_unavailable' => 'Show Journal Entry route is unavailable. ' . self::DELEGATION_EN,
				'jrn_et_index_route_unavailable' => 'Journal Account route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'create_journal_entry_unavailable' => 'La ruta de creación de nuevo diario no está disponible.' . self::DELEGATION_ES,
				'show_journal_entry_unavailable' => 'La ruta para mostrar la entrada del diario no está disponible.' . self::DELEGATION_ES,
				'jrn_et_index_route_unavailable' => 'La ruta de asientos contables no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'create_journal_entry_unavailable' => 'La route de création d\'un nouveau journal n\'est pas disponible.' . self::DELEGATION_FR,
				'show_journal_entry_unavailable' => 'La route d\'affichage de l\'entrée du journal n\'est pas disponible.' . self::DELEGATION_FR,
				'jrn_et_index_route_unavailable' => 'La route des écritures comptables n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'create_journal_entry_unavailable' => 'נתיב יצירת יומן חדש אינו זמין. ' . self::DELEGATION_HE,
				'show_journal_entry_unavailable' => 'נתיב הצגת רשומת יומן אינו זמין. ' . self::DELEGATION_HE,
				'jrn_et_index_route_unavailable' => 'נתיב רשומות היומן אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'create_journal_entry_unavailable' => 'La rotta per creare un nuovo giornale non è disponibile. ' . self::DELEGATION_IT,
				'show_journal_entry_unavailable' => 'La rotta per visualizzare la voce del giornale non è disponibile. ' . self::DELEGATION_IT,
				'jrn_et_index_route_unavailable' => 'Il percorso delle registrazioni contabili non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'create_journal_entry_unavailable' => '新しいジャーナルの作成ルートが利用できません。' . self::DELEGATION_JA,
				'show_journal_entry_unavailable' => '仕訳帳エントリを表示するルートが利用できません。' . self::DELEGATION_JA,
				'jrn_et_index_route_unavailable' => '仕訳帳ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'create_journal_entry_unavailable' => 'Route voor het maken van een nieuw dagboek is niet beschikbaar. ' . self::DELEGATION_NL,
				'show_journal_entry_unavailable' => 'Route voor het weergeven van dagboekposten is niet beschikbaar. ' . self::DELEGATION_NL,
				'jrn_et_index_route_unavailable' => 'Journaalboekingenroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'create_journal_entry_unavailable' => 'Trasa tworzenia nowego dziennika jest niedostępna. ' . self::DELEGATION_PL,
				'show_journal_entry_unavailable' => 'Trasa wyświetlania wpisu dziennika jest niedostępna. ' . self::DELEGATION_PL,
				'jrn_et_index_route_unavailable' => 'Trasa zapisów dziennika jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'create_journal_entry_unavailable' => 'A rota de criação de novo diário não está disponível. ' . self::DELEGATION_PT,
				'show_journal_entry_unavailable' => 'A rota de exibição de entrada de diário não está disponível. ' . self::DELEGATION_PT,
				'jrn_et_index_route_unavailable' => 'A rota de lançamentos contábeis não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'create_journal_entry_unavailable' => 'A rota de criação de novo diário não está disponível. ' . self::DELEGATION_PTBR,
				'show_journal_entry_unavailable' => 'A rota de exibição de entrada de diário não está disponível. ' . self::DELEGATION_PTBR,
				'jrn_et_index_route_unavailable' => 'A rota de lançamentos contábeis não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'create_journal_entry_unavailable' => 'Маршрут создания нового журнала недоступен. ' . self::DELEGATION_RU,
				'show_journal_entry_unavailable' => 'Маршрут показа записи журнала недоступен. ' . self::DELEGATION_RU,
				'jrn_et_index_route_unavailable' => 'Маршрут журнальных записей недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'create_journal_entry_unavailable' => 'Yeni Günlük Oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'show_journal_entry_unavailable' => 'Günlük Girişini Göster rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'jrn_et_index_route_unavailable' => 'Yevmiye Kaydı rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'create_journal_entry_unavailable' => '创建新日志路由不可用。' . self::DELEGATION_ZH,
				'show_journal_entry_unavailable' => '显示日志条目路由不可用。' . self::DELEGATION_ZH,
				'jrn_et_index_route_unavailable' => '日记账路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::LD => [
			'ar' => [
				'lead_index_route_unavailable' => 'مسار إعدادات العملاء المحتملين غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'lead_index_route_unavailable' => 'Lead opsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'lead_index_route_unavailable' => 'Lead-Einrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'lead_index_route_unavailable' => 'Lead setup route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'lead_index_route_unavailable' => 'La ruta de configuración de leads no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'lead_index_route_unavailable' => 'La route de configuration des leads n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'lead_index_route_unavailable' => 'נתיב הגדרת לידים אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'lead_index_route_unavailable' => 'La rotta di configurazione dei lead non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'lead_index_route_unavailable' => 'リード設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'lead_index_route_unavailable' => 'Lead-installatieroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'lead_index_route_unavailable' => 'Trasa konfiguracji leadów jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'lead_index_route_unavailable' => 'A rota de configuração de leads não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'lead_index_route_unavailable' => 'A rota de configuração de leads não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'lead_index_route_unavailable' => 'Маршрут настройки лидов недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'lead_index_route_unavailable' => 'Lead kurulum rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'lead_index_route_unavailable' => '潜在客户设置路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::LN => [
			'ar' => [
				'loan_store_route_unavailable' => 'مسار تخزين القرض غير متاح. ' . self::DELEGATION_AR,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_AR,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_AR,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'loan_store_route_unavailable' => 'Lånelagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_DA,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_DA,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'loan_store_route_unavailable' => 'Darlehen-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_DE,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_DE,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'loan_store_route_unavailable' => 'Loan store route is unavailable. ' . self::DELEGATION_EN,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_EN,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_EN,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'loan_store_route_unavailable' => 'La ruta de almacenamiento de préstamo no está disponible. ' . self::DELEGATION_ES,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_ES,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_ES,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'loan_store_route_unavailable' => 'La route de stockage des prêts est indisponible. ' . self::DELEGATION_FR,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_FR,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_FR,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'loan_store_route_unavailable' => 'נתיב אחסון הלוואה אינו זמין. ' . self::DELEGATION_HE,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_HE,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_HE,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'loan_store_route_unavailable' => 'La rotta di memorizzazione del prestito non è disponibile. ' . self::DELEGATION_IT,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_IT,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_IT,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'loan_store_route_unavailable' => 'ローン保存ルートは利用できません。' . self::DELEGATION_JA,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_JA,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_JA,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'loan_store_route_unavailable' => 'Lening opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_NL,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_NL,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'loan_store_route_unavailable' => 'Trasa przechowywania pożyczki jest niedostępna. ' . self::DELEGATION_PL,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_PL,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_PL,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'loan_store_route_unavailable' => 'A rota de armazenamento de empréstimo não está disponível. ' . self::DELEGATION_PT,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_PT,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_PT,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'loan_store_route_unavailable' => 'A rota de armazenamento de empréstimo não está disponível. ' . self::DELEGATION_PTBR,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_PTBR,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_PTBR,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'loan_store_route_unavailable' => 'Маршрут хранения кредита недоступен. ' . self::DELEGATION_RU,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_RU,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_RU,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'loan_store_route_unavailable' => 'Kredi depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_TR,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_TR,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'loan_store_route_unavailable' => '贷款存储路由不可用。' . self::DELEGATION_ZH,
				'loan_create_route_unavailable' => 'Loan create route is unavailable. ' . self::DELEGATION_ZH,
				'loan_edit_route_unavailable' => 'Loan edit route is unavailable. ' . self::DELEGATION_ZH,
				'loan_destroy_route_unavailable' => 'Loan destroy route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::LNG => [
			'ar' => [
				'language_destroy_route_unavailable' => 'مسار حذف اللغة غير متاح. ' . self::DELEGATION_AR,
				'language_manage_route_unavailable' => 'مسار إدارة اللغة غير متاح. ' . self::DELEGATION_AR,
				'languages_store_data_route_unavailable' => 'مسار تخزين بيانات اللغة غير متاح. ' . self::DELEGATION_AR,
				'language_create_route_unavailable' => 'مسار إنشاء اللغة غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'language_destroy_route_unavailable' => 'Sprog-sletningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'language_manage_route_unavailable' => 'Håndter sprogrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'languages_store_data_route_unavailable' => 'Sprog gem data rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'language_create_route_unavailable' => 'Sprogoprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'language_destroy_route_unavailable' => 'Sprachlöschungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'language_manage_route_unavailable' => 'Sprachverwaltungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'languages_store_data_route_unavailable' => 'Sprachdatenspeicherungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'language_create_route_unavailable' => 'Spracherstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'language_destroy_route_unavailable' => 'Language delete route is unavailable. ' . self::DELEGATION_EN,
				'language_manage_route_unavailable' => 'Manage Language route is unavailable. ' . self::DELEGATION_EN,
				'languages_store_data_route_unavailable' => 'Language store data route is unavailable. ' . self::DELEGATION_EN,
				'language_create_route_unavailable' => 'Create Language route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'language_destroy_route_unavailable' => 'La ruta de eliminación de idioma no está disponible. ' . self::DELEGATION_ES,
				'language_manage_route_unavailable' => 'La ruta de gestión de idiomas no está disponible. ' . self::DELEGATION_ES,
				'languages_store_data_route_unavailable' => 'La ruta de almacenamiento de datos de idioma no está disponible. ' . self::DELEGATION_ES,
				'language_create_route_unavailable' => 'La ruta de creación de idioma no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'language_destroy_route_unavailable' => 'La route de suppression de la langue n\'est pas disponible. ' . self::DELEGATION_FR,
				'language_manage_route_unavailable' => 'La route de gestion de la langue n\'est pas disponible. ' . self::DELEGATION_FR,
				'languages_store_data_route_unavailable' => 'La route de stockage des données linguistiques n\'est pas disponible. ' . self::DELEGATION_FR,
				'language_create_route_unavailable' => 'La route de création de langue n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'language_destroy_route_unavailable' => 'נתיב מחיקת שפה אינו זמין. ' . self::DELEGATION_HE,
				'language_manage_route_unavailable' => 'נתיב ניהול שפה אינו זמין. ' . self::DELEGATION_HE,
				'languages_store_data_route_unavailable' => 'נתיב אחסון נתוני שפה אינו זמין. ' . self::DELEGATION_HE,
				'language_create_route_unavailable' => 'נתיב יצירת שפה אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'language_destroy_route_unavailable' => 'La rotta di eliminazione della lingua non è disponibile. ' . self::DELEGATION_IT,
				'language_manage_route_unavailable' => 'La rotta di gestione della lingua non è disponibile. ' . self::DELEGATION_IT,
				'languages_store_data_route_unavailable' => 'La rotta di archiviazione dati della lingua non è disponibile. ' . self::DELEGATION_IT,
				'language_create_route_unavailable' => 'La rotta di creazione della lingua non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'language_destroy_route_unavailable' => '言語削除ルートは利用できません。' . self::DELEGATION_JA,
				'language_manage_route_unavailable' => '言語管理ルートは利用できません。' . self::DELEGATION_JA,
				'languages_store_data_route_unavailable' => '言語データ保存ルートは利用できません。' . self::DELEGATION_JA,
				'language_create_route_unavailable' => '言語作成ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'language_destroy_route_unavailable' => 'Taal verwijderingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'language_manage_route_unavailable' => 'Taalbeheerroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'languages_store_data_route_unavailable' => 'Taalgegevens opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'language_create_route_unavailable' => 'Taalaanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'language_destroy_route_unavailable' => 'Trasa usuwania języka jest niedostępna. ' . self::DELEGATION_PL,
				'language_manage_route_unavailable' => 'Trasa zarządzania językiem jest niedostępna. ' . self::DELEGATION_PL,
				'languages_store_data_route_unavailable' => 'Trasa przechowywania danych językowych jest niedostępna. ' . self::DELEGATION_PL,
				'language_create_route_unavailable' => 'Trasa tworzenia języka jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'language_destroy_route_unavailable' => 'A rota de eliminação de idioma não está disponível. ' . self::DELEGATION_PT,
				'language_manage_route_unavailable' => 'A rota de gestão de idioma não está disponível. ' . self::DELEGATION_PT,
				'languages_store_data_route_unavailable' => 'A rota de armazenamento de dados de idioma não está disponível. ' . self::DELEGATION_PT,
				'language_create_route_unavailable' => 'A rota de criação de idioma não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'language_destroy_route_unavailable' => 'A rota de exclusão de idioma não está disponível. ' . self::DELEGATION_PTBR,
				'language_manage_route_unavailable' => 'A rota de gerenciamento de idioma não está disponível. ' . self::DELEGATION_PTBR,
				'languages_store_data_route_unavailable' => 'A rota de armazenamento de dados de idioma não está disponível. ' . self::DELEGATION_PTBR,
				'language_create_route_unavailable' => 'A rota de criação de idioma não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'language_destroy_route_unavailable' => 'Маршрут удаления языка недоступен. ' . self::DELEGATION_RU,
				'language_manage_route_unavailable' => 'Маршрут управления языком недоступен. ' . self::DELEGATION_RU,
				'languages_store_data_route_unavailable' => 'Маршрут хранения языковых данных недоступен. ' . self::DELEGATION_RU,
				'language_create_route_unavailable' => 'Маршрут создания языка недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'language_destroy_route_unavailable' => 'Dil silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'language_manage_route_unavailable' => 'Dil Yönetimi rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'languages_store_data_route_unavailable' => 'Dil veri depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'language_create_route_unavailable' => 'Dil Oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'language_destroy_route_unavailable' => '语言删除路由不可用。' . self::DELEGATION_ZH,
				'language_manage_route_unavailable' => '管理语言路由不可用。' . self::DELEGATION_ZH,
				'languages_store_data_route_unavailable' => '语言数据存储路由不可用。' . self::DELEGATION_ZH,
				'language_create_route_unavailable' => '创建语言路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::LV => [
			'ar' => [
				'leave_index_route_unavailable' => 'مسار إدارة الإجازات غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'leave_index_route_unavailable' => 'Administrer orlov rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'leave_index_route_unavailable' => 'Urlaubsverwaltungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'leave_index_route_unavailable' => 'Manage leave route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'leave_index_route_unavailable' => 'La ruta de gestión de permisos no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'leave_index_route_unavailable' => 'La route de gestion des congés est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'leave_index_route_unavailable' => 'נתיב ניהול חופשה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'leave_index_route_unavailable' => 'La rotta di gestione dei permessi non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'leave_index_route_unavailable' => '休暇管理ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'leave_index_route_unavailable' => 'Beheer verlofroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'leave_index_route_unavailable' => 'Trasa zarządzania urlopem jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'leave_index_route_unavailable' => 'A rota de gestão de licenças não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'leave_index_route_unavailable' => 'A rota de gerenciamento de licenças não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'leave_index_route_unavailable' => 'Маршрут управления отпусками недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'leave_index_route_unavailable' => 'İzin yönetim rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'leave_index_route_unavailable' => '管理休假路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::LV_TP => [
			'ar' => [
				'create_leave_type_unavailable' => 'مسار إنشاء نوع الإجازة غير متاح. ' . self::DELEGATION_AR,
				'edit_leave_type_unavailable' => 'مسار تعديل نوع الإجازة غير متاح. ' . self::DELEGATION_AR,
				'delete_leave_type_unavailable' => 'مسار حذف نوع الإجازة غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'create_leave_type_unavailable' => 'Opret afstandstype-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'edit_leave_type_unavailable' => 'Rediger afstandstype-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'delete_leave_type_unavailable' => 'Slet afstandstype-rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'create_leave_type_unavailable' => 'Die Route zum Erstellen von Urlaubstypen ist nicht verfügbar. ' . self::DELEGATION_DE,
				'edit_leave_type_unavailable' => 'Die Route zum Bearbeiten von Urlaubstypen ist nicht verfügbar. ' . self::DELEGATION_DE,
				'delete_leave_type_unavailable' => 'Die Route zum Löschen von Urlaubstypen ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'create_leave_type_unavailable' => 'Create leave type route is unavailable. ' . self::DELEGATION_EN,
				'edit_leave_type_unavailable' => 'Edit leave type route is unavailable. ' . self::DELEGATION_EN,
				'delete_leave_type_unavailable' => 'Delete leave type route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'create_leave_type_unavailable' => 'La ruta de creación de tipo de permiso no está disponible.' . self::DELEGATION_ES,
				'edit_leave_type_unavailable' => 'La ruta de edición de tipo de permiso no está disponible.' . self::DELEGATION_ES,
				'delete_leave_type_unavailable' => 'La ruta de eliminación de tipo de permiso no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'create_leave_type_unavailable' => 'La route de création de type de congé est indisponible.' . self::DELEGATION_FR,
				'edit_leave_type_unavailable' => 'La route de modification de type de congé est indisponible.' . self::DELEGATION_FR,
				'delete_leave_type_unavailable' => 'La route de suppression de type de congé est indisponible.' . self::DELEGATION_FR
			],
			'he' => [
				'create_leave_type_unavailable' => 'מסלול יצירת סוג חופשה אינו זמין. ' . self::DELEGATION_HE,
				'edit_leave_type_unavailable' => 'מסלול עריכת סוג חופשה אינו זמין. ' . self::DELEGATION_HE,
				'delete_leave_type_unavailable' => 'מסלול מחיקת סוג חופשה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'create_leave_type_unavailable' => 'La rotta per la creazione del tipo di permesso non è disponibile. ' . self::DELEGATION_IT,
				'edit_leave_type_unavailable' => 'La rotta per la modifica del tipo di permesso non è disponibile. ' . self::DELEGATION_IT,
				'delete_leave_type_unavailable' => 'La rotta per l\'eliminazione del tipo di permesso non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'create_leave_type_unavailable' => '休暇タイプ作成ルートは利用できません。' . self::DELEGATION_JA,
				'edit_leave_type_unavailable' => '休暇タイプ編集ルートは利用できません。' . self::DELEGATION_JA,
				'delete_leave_type_unavailable' => '休暇タイプ削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'create_leave_type_unavailable' => 'Route voor het aanmaken van verlofstype is niet beschikbaar. ' . self::DELEGATION_NL,
				'edit_leave_type_unavailable' => 'Route voor het bewerken van verlofstype is niet beschikbaar. ' . self::DELEGATION_NL,
				'delete_leave_type_unavailable' => 'Route voor het verwijderen van verlofstype is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'create_leave_type_unavailable' => 'Trasa tworzenia typu urlopu jest niedostępna. ' . self::DELEGATION_PL,
				'edit_leave_type_unavailable' => 'Trasa edycji typu urlopu jest niedostępna. ' . self::DELEGATION_PL,
				'delete_leave_type_unavailable' => 'Trasa usuwania typu urlopu jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'create_leave_type_unavailable' => 'A rota de criação de tipo de licença não está disponível.' . self::DELEGATION_PT,
				'edit_leave_type_unavailable' => 'A rota de edição de tipo de licença não está disponível.' . self::DELEGATION_PT,
				'delete_leave_type_unavailable' => 'A rota de eliminação de tipo de licença não está disponível.' . self::DELEGATION_PT
			],
			'pt-br' => [
				'create_leave_type_unavailable' => 'A rota de criação de tipo de licença não está disponível. ' . self::DELEGATION_PTBR,
				'edit_leave_type_unavailable' => 'A rota de edição de tipo de licença não está disponível. ' . self::DELEGATION_PTBR,
				'delete_leave_type_unavailable' => 'A rota de exclusão de tipo de licença não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'create_leave_type_unavailable' => 'Маршрут создания типа отпуска недоступен. ' . self::DELEGATION_RU,
				'edit_leave_type_unavailable' => 'Маршрут редактирования типа отпуска недоступен. ' . self::DELEGATION_RU,
				'delete_leave_type_unavailable' => 'Маршрут удаления типа отпуска недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'create_leave_type_unavailable' => 'İzin türü oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'edit_leave_type_unavailable' => 'İzin türü düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'delete_leave_type_unavailable' => 'İzin türü silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'create_leave_type_unavailable' => '创建休假类型路由不可用。' . self::DELEGATION_ZH,
				'edit_leave_type_unavailable' => '编辑休假类型路由不可用。' . self::DELEGATION_ZH,
				'delete_leave_type_unavailable' => '删除休假类型路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::MT => [
			'ar' => [
				'meeting_index_route_unavailable' => 'مسار فهرس الاجتماعات غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'meeting_index_route_unavailable' => 'Mødeindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'meeting_index_route_unavailable' => 'Besprechungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'meeting_index_route_unavailable' => 'Meeting index route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'meeting_index_route_unavailable' => 'La ruta del índice de reuniones no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'meeting_index_route_unavailable' => 'La route de l\'index des réunions est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'meeting_index_route_unavailable' => 'נתיב אינדקס פגישות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'meeting_index_route_unavailable' => 'La rotta dell\'indice delle riunioni non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'meeting_index_route_unavailable' => '会議インデックスルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'meeting_index_route_unavailable' => 'Vergaderingindexroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'meeting_index_route_unavailable' => 'Trasa indeksu spotkań jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'meeting_index_route_unavailable' => 'A rota do índice de reuniões não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'meeting_index_route_unavailable' => 'A rota do índice de reuniões não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'meeting_index_route_unavailable' => 'Маршрут индекса встреч недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'meeting_index_route_unavailable' => 'Toplantı indeks rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'meeting_index_route_unavailable' => '会议索引路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::NTF_TMP => [
			'ar' => ['notification_template_index_route_unavailable' => 'مسار قالب الإشعار غير متاح. ' . self::DELEGATION_AR],
			'da' => ['notification_template_index_route_unavailable' => 'Notifikationsskabelonrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['notification_template_index_route_unavailable' => 'Benachrichtigungsvorlagen-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['notification_template_index_route_unavailable' => 'Notification Template route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['notification_template_index_route_unavailable' => 'La ruta de plantilla de notificación no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['notification_template_index_route_unavailable' => 'La route du modèle de notification n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['notification_template_index_route_unavailable' => 'נתיב תבנית התראה אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['notification_template_index_route_unavailable' => 'La rotta del modello di notifica non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['notification_template_index_route_unavailable' => '通知テンプレートルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['notification_template_index_route_unavailable' => 'Notificatiesjabloonroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['notification_template_index_route_unavailable' => 'Trasa szablonu powiadomień jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['notification_template_index_route_unavailable' => 'A rota de modelo de notificação não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['notification_template_index_route_unavailable' => 'A rota de modelo de notificação não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['notification_template_index_route_unavailable' => 'Маршрут шаблона уведомления недоступен. ' . self::DELEGATION_RU],
			'tr' => ['notification_template_index_route_unavailable' => 'Bildirim Şablonu rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['notification_template_index_route_unavailable' => '通知模板路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::OD => [
			'ar' => [
				'order_index_route_unavailable' => 'مسار الطلبات غير متاح. ' . self::DELEGATION_AR,
				'payment_status_unavailable' => 'مسار حالة الدفع غير متاح. ' . self::DELEGATION_AR,
				'delete_order_unavailable' => 'مسار حذف الطلب غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'order_index_route_unavailable' => 'Ordre-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payment_status_unavailable' => 'Betalingsstatus-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'delete_order_unavailable' => 'Slet ordre-rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'order_index_route_unavailable' => 'Bestellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payment_status_unavailable' => 'Die Zahlungsstatus-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'delete_order_unavailable' => 'Die Bestellungs-Löschungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'order_index_route_unavailable' => 'Order route is unavailable. ' . self::DELEGATION_EN,
				'payment_status_unavailable' => 'Payment status route is unavailable. ' . self::DELEGATION_EN,
				'delete_order_unavailable' => 'Delete order route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'order_index_route_unavailable' => 'La ruta de pedidos no está disponible. ' . self::DELEGATION_ES,
				'payment_status_unavailable' => 'La ruta de estado de pago no está disponible.' . self::DELEGATION_ES,
				'delete_order_unavailable' => 'La ruta de eliminación de pedido no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'order_index_route_unavailable' => 'La route des commandes n\'est pas disponible. ' . self::DELEGATION_FR,
				'payment_status_unavailable' => 'La route du statut de paiement est indisponible.' . self::DELEGATION_FR,
				'delete_order_unavailable' => 'La route de suppression de commande est indisponible.' . self::DELEGATION_FR
			],
			'he' => [
				'order_index_route_unavailable' => 'נתיב הזמנות אינו זמין. ' . self::DELEGATION_HE,
				'payment_status_unavailable' => 'מסלול סטטוס תשלום אינו זמין. ' . self::DELEGATION_HE,
				'delete_order_unavailable' => 'מסלול מחיקת הזמנה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'order_index_route_unavailable' => 'La rotta degli ordini non è disponibile. ' . self::DELEGATION_IT,
				'payment_status_unavailable' => 'La rotta dello stato del pagamento non è disponibile. ' . self::DELEGATION_IT,
				'delete_order_unavailable' => 'La rotta di eliminazione dell\'ordine non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'order_index_route_unavailable' => '注文ルートは利用できません。' . self::DELEGATION_JA,
				'payment_status_unavailable' => '支払いステータスルートは利用できません。' . self::DELEGATION_JA,
				'delete_order_unavailable' => '注文削除ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'order_index_route_unavailable' => 'Bestelroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payment_status_unavailable' => 'Route voor betalingsstatus is niet beschikbaar. ' . self::DELEGATION_NL,
				'delete_order_unavailable' => 'Route voor het verwijderen van bestellingen is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'order_index_route_unavailable' => 'Trasa zamówień jest niedostępna. ' . self::DELEGATION_PL,
				'payment_status_unavailable' => 'Trasa statusu płatności jest niedostępna. ' . self::DELEGATION_PL,
				'delete_order_unavailable' => 'Trasa usuwania zamówienia jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'order_index_route_unavailable' => 'A rota de pedidos não está disponível. ' . self::DELEGATION_PT,
				'payment_status_unavailable' => 'A rota de estado de pagamento não está disponível.' . self::DELEGATION_PT,
				'delete_order_unavailable' => 'A rota de eliminação de encomenda não está disponível.' . self::DELEGATION_PT
			],
			'pt-br' => [
				'order_index_route_unavailable' => 'A rota de pedidos não está disponível. ' . self::DELEGATION_PTBR,
				'payment_status_unavailable' => 'A rota de status de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'delete_order_unavailable' => 'A rota de exclusão de pedido não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'order_index_route_unavailable' => 'Маршрут заказов недоступен. ' . self::DELEGATION_RU,
				'payment_status_unavailable' => 'Маршрут статуса платежа недоступен. ' . self::DELEGATION_RU,
				'delete_order_unavailable' => 'Маршрут удаления заказа недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'order_index_route_unavailable' => 'Sipariş rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payment_status_unavailable' => 'Ödeme durumu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'delete_order_unavailable' => 'Sipariş silme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'order_index_route_unavailable' => '订单路由不可用。' . self::DELEGATION_ZH,
				'payment_status_unavailable' => '支付状态路由不可用。' . self::DELEGATION_ZH,
				'delete_order_unavailable' => '删除订单路由不可用。' . self::DELEGATION_ZH
			],
		],
		ViewsConstants::OT_PAY => [
			'ar' => [
				'other_payment_store_route_unavailable' => 'مسار تخزين الدفع الآخر غير متاح. ' . self::DELEGATION_AR,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_AR,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_AR,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'other_payment_store_route_unavailable' => 'Anden betalings lagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_DA,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_DA,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'other_payment_store_route_unavailable' => 'Andere Zahlungs-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_DE,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_DE,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'other_payment_store_route_unavailable' => 'Other payment store route is unavailable. ' . self::DELEGATION_EN,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_EN,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_EN,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'other_payment_store_route_unavailable' => 'La ruta de almacenamiento de otros pagos no está disponible. ' . self::DELEGATION_ES,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_ES,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_ES,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'other_payment_store_route_unavailable' => 'La route de stockage des autres paiements est indisponible. ' . self::DELEGATION_FR,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_FR,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_FR,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'other_payment_store_route_unavailable' => 'נתיב אחסון תשלום אחר אינו זמין. ' . self::DELEGATION_HE,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_HE,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_HE,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'other_payment_store_route_unavailable' => 'La rotta di memorizzazione degli altri pagamenti non è disponibile. ' . self::DELEGATION_IT,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_IT,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_IT,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'other_payment_store_route_unavailable' => 'その他の支払い保存ルートは利用できません。' . self::DELEGATION_JA,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_JA,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_JA,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'other_payment_store_route_unavailable' => 'Andere betalingsopslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_NL,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_NL,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'other_payment_store_route_unavailable' => 'Trasa przechowywania innych płatności jest niedostępna. ' . self::DELEGATION_PL,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_PL,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_PL,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'other_payment_store_route_unavailable' => 'A rota de armazenamento de outros pagamentos não está disponível. ' . self::DELEGATION_PT,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_PT,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_PT,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'other_payment_store_route_unavailable' => 'A rota de armazenamento de outros pagamentos não está disponível. ' . self::DELEGATION_PTBR,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_PTBR,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_PTBR,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'other_payment_store_route_unavailable' => 'Маршрут хранения других платежей недоступен. ' . self::DELEGATION_RU,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_RU,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_RU,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'other_payment_store_route_unavailable' => 'Diğer ödeme depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_TR,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_TR,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'other_payment_store_route_unavailable' => '其他支付存储路由不可用。' . self::DELEGATION_ZH,
				'other_payment_create_route_unavailable' => 'Other payment create route is unavailable. ' . self::DELEGATION_ZH,
				'other_payment_edit_route_unavailable' => 'Other payment edit route is unavailable. ' . self::DELEGATION_ZH,
				'other_payment_destroy_route_unavailable' => 'Other payment destroy route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::OVT => [
			'ar' => [
				'overtime_store_route_unavailable' => 'مسار تخزين العمل الإضافي غير متاح. ' . self::DELEGATION_AR,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_AR,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_AR,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'overtime_store_route_unavailable' => 'Overtids lagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_DA,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_DA,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'overtime_store_route_unavailable' => 'Überstunden-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_DE,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_DE,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'overtime_store_route_unavailable' => 'Overtime store route is unavailable. ' . self::DELEGATION_EN,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_EN,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_EN,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'overtime_store_route_unavailable' => 'La ruta de almacenamiento de horas extras no está disponible. ' . self::DELEGATION_ES,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_ES,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_ES,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'overtime_store_route_unavailable' => 'La route de stockage des heures supplémentaires est indisponible. ' . self::DELEGATION_FR,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_FR,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_FR,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'overtime_store_route_unavailable' => 'נתיב אחסון שעות נוספות אינו זמין. ' . self::DELEGATION_HE,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_HE,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_HE,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'overtime_store_route_unavailable' => 'La rotta di memorizzazione degli straordinari non è disponibile. ' . self::DELEGATION_IT,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_IT,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_IT,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'overtime_store_route_unavailable' => '残業保存ルートは利用できません。' . self::DELEGATION_JA,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_JA,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_JA,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'overtime_store_route_unavailable' => 'Overuren opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_NL,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_NL,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'overtime_store_route_unavailable' => 'Trasa przechowywania nadgodzin jest niedostępna. ' . self::DELEGATION_PL,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_PL,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_PL,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'overtime_store_route_unavailable' => 'A rota de armazenamento de horas extras não está disponível. ' . self::DELEGATION_PT,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_PT,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_PT,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'overtime_store_route_unavailable' => 'A rota de armazenamento de horas extras não está disponível. ' . self::DELEGATION_PTBR,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_PTBR,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_PTBR,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'overtime_store_route_unavailable' => 'Маршрут хранения сверхурочных недоступен. ' . self::DELEGATION_RU,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_RU,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_RU,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'overtime_store_route_unavailable' => 'Fazla mesai depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_TR,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_TR,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'overtime_store_route_unavailable' => '加班存储路由不可用。' . self::DELEGATION_ZH,
				'overtime_create_route_unavailable' => 'Overtime create route is unavailable. ' . self::DELEGATION_ZH,
				'overtime_edit_route_unavailable' => 'Overtime edit route is unavailable. ' . self::DELEGATION_ZH,
				'overtime_destroy_route_unavailable' => 'Overtime destroy route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::PAY => [
			'ar' => ['payment_index_route_unavailable' => 'مسار المدفوعات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['payment_index_route_unavailable' => 'Betalingsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['payment_index_route_unavailable' => 'Zahlungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['payment_index_route_unavailable' => 'Payment route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['payment_index_route_unavailable' => 'La ruta de pagos no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['payment_index_route_unavailable' => 'La route des paiements est indisponible. ' . self::DELEGATION_FR],
			'he' => ['payment_index_route_unavailable' => 'נתיב תשלומים אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['payment_index_route_unavailable' => 'La rotta dei pagamenti non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['payment_index_route_unavailable' => '支払いルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['payment_index_route_unavailable' => 'Betalingsroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['payment_index_route_unavailable' => 'Trasa płatności jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['payment_index_route_unavailable' => 'A rota de pagamentos não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['payment_index_route_unavailable' => 'A rota de pagamentos não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['payment_index_route_unavailable' => 'Маршрут платежей недоступен. ' . self::DELEGATION_RU],
			'tr' => ['payment_index_route_unavailable' => 'Ödeme rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['payment_index_route_unavailable' => '支付路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::PY_SLP => [
			'ar' => [
				'payslip_index_route_unavailable' => 'مسار إيصال الراتب غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'payslip_index_route_unavailable' => 'Lønseddel rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'payslip_index_route_unavailable' => 'Gehaltsabrechnungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'payslip_index_route_unavailable' => 'Payslip route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'payslip_index_route_unavailable' => 'La ruta del recibo de salario no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'payslip_index_route_unavailable' => 'La route de la fiche de paie est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'payslip_index_route_unavailable' => 'נתיב תלוש משכורת אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'payslip_index_route_unavailable' => 'La rotta della busta paga non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'payslip_index_route_unavailable' => '給与明細ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'payslip_index_route_unavailable' => 'Loonstrookroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'payslip_index_route_unavailable' => 'Trasa listy płac jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'payslip_index_route_unavailable' => 'A rota do recibo de vencimento não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'payslip_index_route_unavailable' => 'A rota do holerite não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'payslip_index_route_unavailable' => 'Маршрут расчетного листа недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'payslip_index_route_unavailable' => 'Maaş bordrosu rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'payslip_index_route_unavailable' => '工资单路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::PLN => [
			'ar' => ['plan_index_route_unavailable' => 'مسار الخطط غير متاح. ' . self::DELEGATION_AR],
			'da' => ['plan_index_route_unavailable' => 'Planrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['plan_index_route_unavailable' => 'Plan-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['plan_index_route_unavailable' => 'Plan route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['plan_index_route_unavailable' => 'La ruta de planes no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['plan_index_route_unavailable' => 'La route des plans n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['plan_index_route_unavailable' => 'נתיב תוכניות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['plan_index_route_unavailable' => 'La rotta dei piani non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['plan_index_route_unavailable' => 'プランルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['plan_index_route_unavailable' => 'Planroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['plan_index_route_unavailable' => 'Trasa planów jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['plan_index_route_unavailable' => 'A rota de planos não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['plan_index_route_unavailable' => 'A rota de planos não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['plan_index_route_unavailable' => 'Маршрут планов недоступен. ' . self::DELEGATION_RU],
			'tr' => ['plan_index_route_unavailable' => 'Plan rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['plan_index_route_unavailable' => '计划路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::POS => [
			'ar' => [
				'daily_pos_unavailable' => 'مسار نقاط البيع اليومي غير متاح. ' . self::DELEGATION_AR,
				'monthly_pos_unavailable' => 'مسار نقاط البيع الشهري غير متاح. ' . self::DELEGATION_AR,
				'daily_apply_pos_unavailable' => 'مسار تطبيق نقاط البيع اليومي غير متاح. ' . self::DELEGATION_AR,
				'daily_reset_pos_unavailable' => 'مسار إعادة تعيين نقاط البيع اليومي غير متاح. ' . self::DELEGATION_AR,
				'download_monthly_pos_unavailable' => 'وظيفة تنزيل نقاط البيع الشهرية غير متاحة. ' . self::DELEGATION_AR,
				'download_daily_pos_unavailable' => 'وظيفة تنزيل نقاط البيع اليومية غير متاحة. ' . self::DELEGATION_AR
			],
			'da' => [
				'daily_pos_unavailable' => 'Daglig POS-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'monthly_pos_unavailable' => 'Månedlig rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'daily_apply_pos_unavailable' => 'Daglig POS-anvendelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'daily_reset_pos_unavailable' => 'Daglig POS-nulstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'download_monthly_pos_unavailable' => 'Downloadfunktion for månedlig POS er ikke tilgængelig. ' . self::DELEGATION_DA,
				'download_daily_pos_unavailable' => 'Downloadfunktion for daglig POS er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'daily_pos_unavailable' => 'Tägliche POS-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'monthly_pos_unavailable' => 'Monatliche Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'daily_apply_pos_unavailable' => 'Tägliche POS-Anwendungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'daily_reset_pos_unavailable' => 'Tägliche POS-Reset-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'download_monthly_pos_unavailable' => 'Download-Funktion für monatliche POS ist nicht verfügbar. ' . self::DELEGATION_DE,
				'download_daily_pos_unavailable' => 'Download-Funktion für tägliche POS ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'daily_pos_unavailable' => 'Daily pos route is unavailable. ' . self::DELEGATION_EN,
				'monthly_pos_unavailable' => 'Monthly route is unavailable. ' . self::DELEGATION_EN,
				'daily_apply_pos_unavailable' => 'Daily POS apply route is unavailable. ' . self::DELEGATION_EN,
				'daily_reset_pos_unavailable' => 'Daily POS reset route is unavailable. ' . self::DELEGATION_EN,
				'download_monthly_pos_unavailable' => 'Download function for monthly POS is unavailable. ' . self::DELEGATION_EN,
				'download_daily_pos_unavailable' => 'Download function for daily POS is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'daily_pos_unavailable' => 'La ruta diaria de POS no está disponible.' . self::DELEGATION_ES,
				'monthly_pos_unavailable' => 'La ruta mensual no está disponible.' . self::DELEGATION_ES,
				'daily_apply_pos_unavailable' => 'La ruta de aplicación diaria de POS no está disponible.' . self::DELEGATION_ES,
				'daily_reset_pos_unavailable' => 'La ruta de reinicio diario de POS no está disponible.' . self::DELEGATION_ES,
				'download_monthly_pos_unavailable' => 'La función de descarga para POS mensual no está disponible.' . self::DELEGATION_ES,
				'download_daily_pos_unavailable' => 'La función de descarga para POS diario no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'daily_pos_unavailable' => 'La route quotidienne du POS est indisponible.' . self::DELEGATION_FR,
				'monthly_pos_unavailable' => 'La route mensuelle est indisponible.' . self::DELEGATION_FR,
				'daily_apply_pos_unavailable' => 'La route d\'application quotidienne du POS est indisponible.' . self::DELEGATION_FR,
				'daily_reset_pos_unavailable' => 'La route de réinitialisation quotidienne du POS est indisponible.' . self::DELEGATION_FR,
				'download_monthly_pos_unavailable' => 'La fonction de téléchargement pour le POS mensuel est indisponible.' . self::DELEGATION_FR,
				'download_daily_pos_unavailable' => 'La fonction de téléchargement pour le POS quotidien est indisponible.' . self::DELEGATION_FR
			],
			'he' => [
				'daily_pos_unavailable' => 'מסלול נקודת מכירה יומית אינו זמין. ' . self::DELEGATION_HE,
				'monthly_pos_unavailable' => 'מסלול נקודת מכירה חודשית אינו זמין. ' . self::DELEGATION_HE,
				'daily_apply_pos_unavailable' => 'מסלול הפעלת נקודת מכירה יומית אינו זמין. ' . self::DELEGATION_HE,
				'daily_reset_pos_unavailable' => 'מסלול איפוס נקודת מכירה יומית אינו זמין. ' . self::DELEGATION_HE,
				'download_monthly_pos_unavailable' => 'פונקציית הורדת נקודת מכירה חודשית אינה זמינה. ' . self::DELEGATION_HE,
				'download_daily_pos_unavailable' => 'פונקציית הורדת נקודת מכירה יומית אינה זמינה. ' . self::DELEGATION_HE
			],
			'it' => [
				'daily_pos_unavailable' => 'La rotta giornaliera del POS non è disponibile. ' . self::DELEGATION_IT,
				'monthly_pos_unavailable' => 'La rotta mensile non è disponibile. ' . self::DELEGATION_IT,
				'daily_apply_pos_unavailable' => 'La rotta di applicazione giornaliera del POS non è disponibile. ' . self::DELEGATION_IT,
				'daily_reset_pos_unavailable' => 'La rotta di reset giornaliera del POS non è disponibile. ' . self::DELEGATION_IT,
				'download_monthly_pos_unavailable' => 'La funzione di download per il POS mensile non è disponibile. ' . self::DELEGATION_IT,
				'download_daily_pos_unavailable' => 'La funzione di download per il POS giornaliero non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'daily_pos_unavailable' => '日次POSルートは利用できません。' . self::DELEGATION_JA,
				'monthly_pos_unavailable' => '月次ルートは利用できません。' . self::DELEGATION_JA,
				'daily_apply_pos_unavailable' => '日次POS適用ルートは利用できません。' . self::DELEGATION_JA,
				'daily_reset_pos_unavailable' => '日次POSリセットルートは利用できません。' . self::DELEGATION_JA,
				'download_monthly_pos_unavailable' => '月次POSのダウンロード機能は利用できません。' . self::DELEGATION_JA,
				'download_daily_pos_unavailable' => '日次POSのダウンロード機能は利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'daily_pos_unavailable' => 'Dagelijkse POS-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'monthly_pos_unavailable' => 'Maandelijkse route is niet beschikbaar. ' . self::DELEGATION_NL,
				'daily_apply_pos_unavailable' => 'Dagelijkse POS-toepassingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'daily_reset_pos_unavailable' => 'Dagelijkse POS-resetroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'download_monthly_pos_unavailable' => 'Downloadfunctie voor maandelijkse POS is niet beschikbaar. ' . self::DELEGATION_NL,
				'download_daily_pos_unavailable' => 'Downloadfunctie voor dagelijkse POS is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'daily_pos_unavailable' => 'Dzienna trasa POS jest niedostępna. ' . self::DELEGATION_PL,
				'monthly_pos_unavailable' => 'Trasa miesięczna jest niedostępna. ' . self::DELEGATION_PL,
				'daily_apply_pos_unavailable' => 'Dzienna trasa aplikacji POS jest niedostępna. ' . self::DELEGATION_PL,
				'daily_reset_pos_unavailable' => 'Dzienna trasa resetu POS jest niedostępna. ' . self::DELEGATION_PL,
				'download_monthly_pos_unavailable' => 'Funkcja pobierania miesięcznego POS jest niedostępna. ' . self::DELEGATION_PL,
				'download_daily_pos_unavailable' => 'Funkcja pobierania dziennego POS jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'daily_pos_unavailable' => 'A rota diária de POS não está disponível.' . self::DELEGATION_PT,
				'monthly_pos_unavailable' => 'A rota mensal não está disponível.' . self::DELEGATION_PT,
				'daily_apply_pos_unavailable' => 'A rota de aplicação diária de POS não está disponível.' . self::DELEGATION_PT,
				'daily_reset_pos_unavailable' => 'A rota de redefinição diária de POS não está disponível.' . self::DELEGATION_PT,
				'download_monthly_pos_unavailable' => 'A função de download para POS mensal não está disponível.' . self::DELEGATION_PT,
				'download_daily_pos_unavailable' => 'A função de download para POS diário não está disponível.' . self::DELEGATION_PT
			],
			'pt-br' => [
				'daily_pos_unavailable' => 'A rota diária de POS não está disponível. ' . self::DELEGATION_PTBR,
				'monthly_pos_unavailable' => 'A rota mensal não está disponível. ' . self::DELEGATION_PTBR,
				'daily_apply_pos_unavailable' => 'A rota de aplicação diária de POS não está disponível. ' . self::DELEGATION_PTBR,
				'daily_reset_pos_unavailable' => 'A rota de redefinição diária de POS não está disponível. ' . self::DELEGATION_PTBR,
				'download_monthly_pos_unavailable' => 'A função de download para POS mensal não está disponível. ' . self::DELEGATION_PTBR,
				'download_daily_pos_unavailable' => 'A função de download para POS diário não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'daily_pos_unavailable' => 'Ежедневный маршрут POS недоступен. ' . self::DELEGATION_RU,
				'monthly_pos_unavailable' => 'Ежемесячный маршрут недоступен. ' . self::DELEGATION_RU,
				'daily_apply_pos_unavailable' => 'Маршрут ежедневного применения POS недоступен. ' . self::DELEGATION_RU,
				'daily_reset_pos_unavailable' => 'Маршрут ежедневного сброса POS недоступен. ' . self::DELEGATION_RU,
				'download_monthly_pos_unavailable' => 'Функция загрузки месячного POS недоступна. ' . self::DELEGATION_RU,
				'download_daily_pos_unavailable' => 'Функция загрузки дневного POS недоступна. ' . self::DELEGATION_RU
			],
			'tr' => [
				'daily_pos_unavailable' => 'Günlük POS rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'monthly_pos_unavailable' => 'Aylık rota kullanılamıyor. ' . self::DELEGATION_TR,
				'daily_apply_pos_unavailable' => 'Günlük POS uygulama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'daily_reset_pos_unavailable' => 'Günlük POS sıfırlama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'download_monthly_pos_unavailable' => 'Aylık POS için indirme işlevi kullanılamıyor. ' . self::DELEGATION_TR,
				'download_daily_pos_unavailable' => 'Günlük POS için indirme işlevi kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'daily_pos_unavailable' => '每日POS路由不可用。' . self::DELEGATION_ZH,
				'monthly_pos_unavailable' => '月度路由不可用。' . self::DELEGATION_ZH,
				'daily_apply_pos_unavailable' => '每日POS应用路由不可用。' . self::DELEGATION_ZH,
				'daily_reset_pos_unavailable' => '每日POS重置路由不可用。' . self::DELEGATION_ZH,
				'download_monthly_pos_unavailable' => '月度POS的下载功能不可用。' . self::DELEGATION_ZH,
				'download_daily_pos_unavailable' => '每日POS的下载功能不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::POS => [
			'ar' => [
				'pos_index_route_unavailable' => 'مسار إعدادات نقاط البيع غير متاح. ' . self::DELEGATION_AR,
				'pos_report_route_unavailable' => 'مسار تقارير نقاط البيع غير متاح. ' . self::DELEGATION_AR,
				'pos_barcode_route_unavailable' => 'مسار باركود نقاط البيع غير متاح. ' . self::DELEGATION_AR,
				'pos_print_setting_route_unavailable' => 'مسار إعدادات طباعة نقاط البيع غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'pos_index_route_unavailable' => 'POS-opsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pos_report_route_unavailable' => 'POS-rapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pos_barcode_route_unavailable' => 'POS-stregkoderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pos_print_setting_route_unavailable' => 'POS-udskriftsindstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'pos_index_route_unavailable' => 'POS-Einrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pos_report_route_unavailable' => 'POS-Berichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pos_barcode_route_unavailable' => 'POS-Barcode-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pos_print_setting_route_unavailable' => 'POS-Druckeinstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'pos_index_route_unavailable' => 'POS Setup route is unavailable. ' . self::DELEGATION_EN,
				'pos_report_route_unavailable' => 'POS Report route is unavailable. ' . self::DELEGATION_EN,
				'pos_barcode_route_unavailable' => 'POS Barcode route is unavailable. ' . self::DELEGATION_EN,
				'pos_print_setting_route_unavailable' => 'POS Print Settings route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'pos_index_route_unavailable' => 'La ruta de configuración de POS no está disponible. ' . self::DELEGATION_ES,
				'pos_report_route_unavailable' => 'La ruta de informe de POS no está disponible. ' . self::DELEGATION_ES,
				'pos_barcode_route_unavailable' => 'La ruta de código de barras de POS no está disponible. ' . self::DELEGATION_ES,
				'pos_print_setting_route_unavailable' => 'La ruta de configuración de impresión de POS no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'pos_index_route_unavailable' => 'La route de configuration du PDV n\'est pas disponible. ' . self::DELEGATION_FR,
				'pos_report_route_unavailable' => 'La route de rapport du PDV n\'est pas disponible. ' . self::DELEGATION_FR,
				'pos_barcode_route_unavailable' => 'La route du code-barres PDV n\'est pas disponible. ' . self::DELEGATION_FR,
				'pos_print_setting_route_unavailable' => 'La route des paramètres d\'impression PDV n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'pos_index_route_unavailable' => 'נתיב הגדרת קופה אינו זמין. ' . self::DELEGATION_HE,
				'pos_report_route_unavailable' => 'נתיב דוח קופה אינו זמין. ' . self::DELEGATION_HE,
				'pos_barcode_route_unavailable' => 'נתיב ברקוד קופה אינו זמין. ' . self::DELEGATION_HE,
				'pos_print_setting_route_unavailable' => 'נתיב הגדרות הדפסה בקופה אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'pos_index_route_unavailable' => 'La rotta di configurazione POS non è disponibile. ' . self::DELEGATION_IT,
				'pos_report_route_unavailable' => 'La rotta dei report POS non è disponibile. ' . self::DELEGATION_IT,
				'pos_barcode_route_unavailable' => 'La rotta del codice a barre POS non è disponibile. ' . self::DELEGATION_IT,
				'pos_print_setting_route_unavailable' => 'La rotta delle impostazioni di stampa POS non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'pos_index_route_unavailable' => 'POS設定ルートは利用できません。' . self::DELEGATION_JA,
				'pos_report_route_unavailable' => 'POSレポートルートは利用できません。' . self::DELEGATION_JA,
				'pos_barcode_route_unavailable' => 'POSバーコードルートは利用できません。' . self::DELEGATION_JA,
				'pos_print_setting_route_unavailable' => 'POS印刷設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'pos_index_route_unavailable' => 'POS-installatieroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'pos_report_route_unavailable' => 'POS-rapportageroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'pos_barcode_route_unavailable' => 'POS-barcoderoute is niet beschikbaar. ' . self::DELEGATION_NL,
				'pos_print_setting_route_unavailable' => 'POS-afdrukinstellingenroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'pos_index_route_unavailable' => 'Trasa konfiguracji POS jest niedostępna. ' . self::DELEGATION_PL,
				'pos_report_route_unavailable' => 'Trasa raportu POS jest niedostępna. ' . self::DELEGATION_PL,
				'pos_barcode_route_unavailable' => 'Trasa kodu kreskowego POS jest niedostępna. ' . self::DELEGATION_PL,
				'pos_print_setting_route_unavailable' => 'Trasa ustawień drukowania POS jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'pos_index_route_unavailable' => 'A rota de configuração POS não está disponível. ' . self::DELEGATION_PT,
				'pos_report_route_unavailable' => 'A rota de relatório POS não está disponível. ' . self::DELEGATION_PT,
				'pos_barcode_route_unavailable' => 'A rota de código de barras POS não está disponível. ' . self::DELEGATION_PT,
				'pos_print_setting_route_unavailable' => 'A rota de configurações de impressão POS não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'pos_index_route_unavailable' => 'A rota de configuração POS não está disponível. ' . self::DELEGATION_PTBR,
				'pos_report_route_unavailable' => 'A rota de relatório POS não está disponível. ' . self::DELEGATION_PTBR,
				'pos_barcode_route_unavailable' => 'A rota de código de barras POS não está disponível. ' . self::DELEGATION_PTBR,
				'pos_print_setting_route_unavailable' => 'A rota de configurações de impressão POS não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'pos_index_route_unavailable' => 'Маршрут настройки POS недоступен. ' . self::DELEGATION_RU,
				'pos_report_route_unavailable' => 'Маршрут отчета POS недоступен. ' . self::DELEGATION_RU,
				'pos_barcode_route_unavailable' => 'Маршрут штрих-кода POS недоступен. ' . self::DELEGATION_RU,
				'pos_print_setting_route_unavailable' => 'Маршрут настроек печати POS недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'pos_index_route_unavailable' => 'POS Kurulum rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pos_report_route_unavailable' => 'POS Rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pos_barcode_route_unavailable' => 'POS Barkod rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pos_print_setting_route_unavailable' => 'POS Yazdırma Ayarları rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'pos_index_route_unavailable' => 'POS设置路由不可用。' . self::DELEGATION_ZH,
				'pos_report_route_unavailable' => 'POS报告路由不可用。' . self::DELEGATION_ZH,
				'pos_barcode_route_unavailable' => 'POS条码路由不可用。' . self::DELEGATION_ZH,
				'pos_print_setting_route_unavailable' => 'POS打印设置路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::PPL => [
			'ar' => [
				'pipeline_index_route_unavailable' => 'مسار إعدادات خطوط الأنابيب غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'pipeline_index_route_unavailable' => 'Pipeline opsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'pipeline_index_route_unavailable' => 'Pipeline-Einrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'pipeline_index_route_unavailable' => 'Pipeline setup route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'pipeline_index_route_unavailable' => 'La ruta de configuración de tuberías no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'pipeline_index_route_unavailable' => 'La route de configuration des pipelines n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'pipeline_index_route_unavailable' => 'נתיב הגדרת צינור אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'pipeline_index_route_unavailable' => 'La rotta di configurazione della pipeline non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'pipeline_index_route_unavailable' => 'パイプライン設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'pipeline_index_route_unavailable' => 'Pijpleidinginstallatieroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'pipeline_index_route_unavailable' => 'Trasa konfiguracji potoku jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'pipeline_index_route_unavailable' => 'A rota de configuração do pipeline não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'pipeline_index_route_unavailable' => 'A rota de configuração do pipeline não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'pipeline_index_route_unavailable' => 'Маршрут настройки конвейера недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'pipeline_index_route_unavailable' => 'Pipeline kurulum rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'pipeline_index_route_unavailable' => '管道设置路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::PPS => [
			'ar' => [
				'create_proposal_route_unavailable' => 'مسار إنشاء الاقتراح غير متاح. ' . self::DELEGATION_AR,
				'proposal_index_route_unavailable' => 'مسار فهرس الاقتراحات غير متاح. ' . self::DELEGATION_AR,
				'proposal_update_route_unavailable' => 'مسار تحديث الاقتراح غير متاح. ' . self::DELEGATION_AR,
				'proposal_customer_route_unavailable' => 'مسار عميل الاقتراح غير متاح. ' . self::DELEGATION_AR,
				'proposal_product_route_unavailable' => 'مسار منتج الاقتراح غير متاح. ' . self::DELEGATION_AR,
				'proposal_store_route_unavailable' => 'مسار تخزين الاقتراح غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'create_proposal_route_unavailable' => 'Oprettelsesrute for forslag er ikke tilgængelig. ' . self::DELEGATION_DA,
				'proposal_index_route_unavailable' => 'Forslagsindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'proposal_update_route_unavailable' => 'Forslagsopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'proposal_customer_route_unavailable' => 'Forslagskunderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'proposal_product_route_unavailable' => 'Forslagsproduktrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'proposal_store_route_unavailable' => 'Forslagslagringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'create_proposal_route_unavailable' => 'Erstellungsroute für Vorschläge ist nicht verfügbar. ' . self::DELEGATION_DE,
				'proposal_index_route_unavailable' => 'Vorschlagsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'proposal_update_route_unavailable' => 'Vorschlagsaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'proposal_customer_route_unavailable' => 'Vorschlagskunden-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'proposal_product_route_unavailable' => 'Vorschlagsprodukt-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'proposal_store_route_unavailable' => 'Vorschlags-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'create_proposal_route_unavailable' => 'Create proposal route is unavailable. ' . self::DELEGATION_EN,
				'proposal_index_route_unavailable' => 'Proposal index route is unavailable. ' . self::DELEGATION_EN,
				'proposal_update_route_unavailable' => 'Proposal update route is unavailable. ' . self::DELEGATION_EN,
				'proposal_customer_route_unavailable' => 'Proposal customer route is unavailable. ' . self::DELEGATION_EN,
				'proposal_product_route_unavailable' => 'Proposal product route is unavailable. ' . self::DELEGATION_EN,
				'proposal_store_route_unavailable' => 'Proposal store route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'create_proposal_route_unavailable' => 'La ruta de creación de propuestas no está disponible. ' . self::DELEGATION_ES,
				'proposal_index_route_unavailable' => 'La ruta de índice de propuestas no está disponible. ' . self::DELEGATION_ES,
				'proposal_update_route_unavailable' => 'La ruta de actualización de propuestas no está disponible. ' . self::DELEGATION_ES,
				'proposal_customer_route_unavailable' => 'La ruta de clientes de propuestas no está disponible. ' . self::DELEGATION_ES,
				'proposal_product_route_unavailable' => 'La ruta de productos de propuestas no está disponible. ' . self::DELEGATION_ES,
				'proposal_store_route_unavailable' => 'La ruta de almacenamiento de propuestas no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'create_proposal_route_unavailable' => 'La route de création de propositions n\'est pas disponible. ' . self::DELEGATION_FR,
				'proposal_index_route_unavailable' => 'La route d\'index des propositions n\'est pas disponible. ' . self::DELEGATION_FR,
				'proposal_update_route_unavailable' => 'La route de mise à jour des propositions n\'est pas disponible. ' . self::DELEGATION_FR,
				'proposal_customer_route_unavailable' => 'La route des clients pour les propositions n\'est pas disponible. ' . self::DELEGATION_FR,
				'proposal_product_route_unavailable' => 'La route des produits pour les propositions n\'est pas disponible. ' . self::DELEGATION_FR,
				'proposal_store_route_unavailable' => 'La route de stockage des propositions n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'create_proposal_route_unavailable' => 'נתיב יצירת ההצעה אינו זמין. ' . self::DELEGATION_HE,
				'proposal_index_route_unavailable' => 'נתיב אינדקס ההצעות אינו זמין. ' . self::DELEGATION_HE,
				'proposal_update_route_unavailable' => 'נתיב עדכון ההצעה אינו זמין. ' . self::DELEGATION_HE,
				'proposal_customer_route_unavailable' => 'נתיב הלקוח של ההצעה אינו זמין. ' . self::DELEGATION_HE,
				'proposal_product_route_unavailable' => 'נתיב המוצר של ההצעה אינו זמין. ' . self::DELEGATION_HE,
				'proposal_store_route_unavailable' => 'נתיב אחסון ההצעה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'create_proposal_route_unavailable' => 'La rotta di creazione delle proposte non è disponibile. ' . self::DELEGATION_IT,
				'proposal_index_route_unavailable' => 'La rotta dell\'indice delle proposte non è disponibile. ' . self::DELEGATION_IT,
				'proposal_update_route_unavailable' => 'La rotta di aggiornamento delle proposte non è disponibile. ' . self::DELEGATION_IT,
				'proposal_customer_route_unavailable' => 'La rotta del cliente per le proposte non è disponibile. ' . self::DELEGATION_IT,
				'proposal_product_route_unavailable' => 'La rotta del prodotto per le proposte non è disponibile. ' . self::DELEGATION_IT,
				'proposal_store_route_unavailable' => 'La rotta di memorizzazione delle proposte non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'create_proposal_route_unavailable' => '提案作成ルートは利用できません。' . self::DELEGATION_JA,
				'proposal_index_route_unavailable' => '提案インデックスルートは利用できません。' . self::DELEGATION_JA,
				'proposal_update_route_unavailable' => '提案更新ルートは利用できません。' . self::DELEGATION_JA,
				'proposal_customer_route_unavailable' => '提案顧客ルートは利用できません。' . self::DELEGATION_JA,
				'proposal_product_route_unavailable' => '提案商品ルートは利用できません。' . self::DELEGATION_JA,
				'proposal_store_route_unavailable' => '提案保存ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'create_proposal_route_unavailable' => 'Aanmaakroute voor voorstellen is niet beschikbaar. ' . self::DELEGATION_NL,
				'proposal_index_route_unavailable' => 'Voorstel indexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'proposal_update_route_unavailable' => 'Voorstel updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'proposal_customer_route_unavailable' => 'Voorstel klantenroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'proposal_product_route_unavailable' => 'Voorstel productroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'proposal_store_route_unavailable' => 'Voorstel opslagroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'create_proposal_route_unavailable' => 'Trasa tworzenia propozycji jest niedostępna. ' . self::DELEGATION_PL,
				'proposal_index_route_unavailable' => 'Trasa indeksu propozycji jest niedostępna. ' . self::DELEGATION_PL,
				'proposal_update_route_unavailable' => 'Trasa aktualizacji propozycji jest niedostępna. ' . self::DELEGATION_PL,
				'proposal_customer_route_unavailable' => 'Trasa klienta propozycji jest niedostępna. ' . self::DELEGATION_PL,
				'proposal_product_route_unavailable' => 'Trasa produktu propozycji jest niedostępna. ' . self::DELEGATION_PL,
				'proposal_store_route_unavailable' => 'Trasa przechowywania propozycji jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'create_proposal_route_unavailable' => 'A rota de criação de propostas não está disponível. ' . self::DELEGATION_PT,
				'proposal_index_route_unavailable' => 'A rota de índice de propostas não está disponível. ' . self::DELEGATION_PT,
				'proposal_update_route_unavailable' => 'A rota de atualização de propostas não está disponível. ' . self::DELEGATION_PT,
				'proposal_customer_route_unavailable' => 'A rota de clientes de propostas não está disponível. ' . self::DELEGATION_PT,
				'proposal_product_route_unavailable' => 'A rota de produtos de propostas não está disponível. ' . self::DELEGATION_PT,
				'proposal_store_route_unavailable' => 'A rota de armazenamento de propostas não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'create_proposal_route_unavailable' => 'A rota de criação de propostas não está disponível. ' . self::DELEGATION_PTBR,
				'proposal_index_route_unavailable' => 'A rota de índice de propostas não está disponível. ' . self::DELEGATION_PTBR,
				'proposal_update_route_unavailable' => 'A rota de atualização de propostas não está disponível. ' . self::DELEGATION_PTBR,
				'proposal_customer_route_unavailable' => 'A rota de clientes de propostas não está disponível. ' . self::DELEGATION_PTBR,
				'proposal_product_route_unavailable' => 'A rota de produtos de propostas não está disponível. ' . self::DELEGATION_PTBR,
				'proposal_store_route_unavailable' => 'A rota de armazenamento de propostas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'create_proposal_route_unavailable' => 'Маршрут создания предложения недоступен. ' . self::DELEGATION_RU,
				'proposal_index_route_unavailable' => 'Маршрут индекса предложений недоступен. ' . self::DELEGATION_RU,
				'proposal_update_route_unavailable' => 'Маршрут обновления предложений недоступен. ' . self::DELEGATION_RU,
				'proposal_customer_route_unavailable' => 'Маршрут клиента предложения недоступен. ' . self::DELEGATION_RU,
				'proposal_product_route_unavailable' => 'Маршрут товара предложения недоступен. ' . self::DELEGATION_RU,
				'proposal_store_route_unavailable' => 'Маршрут хранения предложения недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'create_proposal_route_unavailable' => 'Teklif oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'proposal_index_route_unavailable' => 'Teklif indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'proposal_update_route_unavailable' => 'Teklif güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'proposal_customer_route_unavailable' => 'Teklif müşteri rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'proposal_product_route_unavailable' => 'Teklif ürün rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'proposal_store_route_unavailable' => 'Teklif saklama rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'create_proposal_route_unavailable' => '创建提案路由不可用。' . self::DELEGATION_ZH,
				'proposal_index_route_unavailable' => '提案索引路由不可用。' . self::DELEGATION_ZH,
				'proposal_update_route_unavailable' => '提案更新路由不可用。' . self::DELEGATION_ZH,
				'proposal_customer_route_unavailable' => '提案客户路由不可用。' . self::DELEGATION_ZH,
				'proposal_product_route_unavailable' => '提案产品路由不可用。' . self::DELEGATION_ZH,
				'proposal_store_route_unavailable' => '提案存储路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::PRC => [
			'ar' => ['purchase_index_route_unavailable' => 'مسار المشتريات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['purchase_index_route_unavailable' => 'Indkøbsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['purchase_index_route_unavailable' => 'Kauf-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['purchase_index_route_unavailable' => 'Purchase route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['purchase_index_route_unavailable' => 'La ruta de compras no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['purchase_index_route_unavailable' => 'La route des achats n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['purchase_index_route_unavailable' => 'נתיב רכישות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['purchase_index_route_unavailable' => 'La rotta degli acquisti non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['purchase_index_route_unavailable' => '購入ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['purchase_index_route_unavailable' => 'Aankooproute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['purchase_index_route_unavailable' => 'Trasa zakupów jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['purchase_index_route_unavailable' => 'A rota de compras não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['purchase_index_route_unavailable' => 'A rota de compras não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['purchase_index_route_unavailable' => 'Маршрут покупок недоступен. ' . self::DELEGATION_RU],
			'tr' => ['purchase_index_route_unavailable' => 'Satın Alma rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['purchase_index_route_unavailable' => '购买路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::PRJ => [
			'ar' => [
				'project_index_route_unavailable' => 'مسار المشاريع غير متاح. ' . self::DELEGATION_AR,
				'show_project_route_unavailable' => 'مسار عرض المشروع غير متاح. ' . self::DELEGATION_AR,
				'project_task_route_unavailable' => 'مسار مهام المشروع غير متاح. ' . self::DELEGATION_AR,
				'store_todo_route_unavailable' => 'مسار إنشاء المهام غير متاح. ' . self::DELEGATION_AR,
				'update_todo_route_unavailable' => 'مسار تحديث المهام غير متاح. ' . self::DELEGATION_AR,
				'destroy_todo_route_unavailable' => 'مسار حذف المهام غير متاح. ' . self::DELEGATION_AR,
				'project_copy_store_route_unavailable' => 'مسار تخزين نسخة المشروع غير متاح. ' . self::DELEGATION_AR,
				'project_tasks_show_route_unavailable' => 'مسار عرض مهام المشروع غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'project_index_route_unavailable' => 'Projektrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'show_project_route_unavailable' => 'Projektvisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'project_task_route_unavailable' => 'Projektopgave-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'store_todo_route_unavailable' => 'Oprettelsesrute for opgaver er ikke tilgængelig. ' . self::DELEGATION_DA,
				'update_todo_route_unavailable' => 'Opdateringsrute for opgaver er ikke tilgængelig. ' . self::DELEGATION_DA,
				'destroy_todo_route_unavailable' => 'Slettelsesrute for opgaver er ikke tilgængelig. ' . self::DELEGATION_DA,
				'project_copy_store_route_unavailable' => 'Projekt kopi gemmerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'project_tasks_show_route_unavailable' => 'Projektopgave visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'project_index_route_unavailable' => 'Projekt-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'show_project_route_unavailable' => 'Projektansichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'project_task_route_unavailable' => 'Projektaufgaben-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'store_todo_route_unavailable' => 'Aufgabenerstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'update_todo_route_unavailable' => 'Aufgabenaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'destroy_todo_route_unavailable' => 'Aufgabenlöschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'project_copy_store_route_unavailable' => 'Projektkopie-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'project_tasks_show_route_unavailable' => 'Projektaufgaben-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'project_index_route_unavailable' => 'Projects route is unavailable. ' . self::DELEGATION_EN,
				'show_project_route_unavailable' => 'Project view route is unavailable. ' . self::DELEGATION_EN,
				'project_task_route_unavailable' => 'Project task route is unavailable. ' . self::DELEGATION_EN,
				'store_todo_route_unavailable' => 'Todo creation route is unavailable. ' . self::DELEGATION_EN,
				'update_todo_route_unavailable' => 'Todo update route is unavailable. ' . self::DELEGATION_EN,
				'destroy_todo_route_unavailable' => 'Todo delete route is unavailable. ' . self::DELEGATION_EN,
				'project_copy_store_route_unavailable' => 'Project copy store route is unavailable. ' . self::DELEGATION_EN,
				'project_tasks_show_route_unavailable' => 'Project tasks show route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'project_index_route_unavailable' => 'La ruta de proyectos no está disponible. ' . self::DELEGATION_ES,
				'show_project_route_unavailable' => 'La ruta de visualización del proyecto no está disponible. ' . self::DELEGATION_ES,
				'project_task_route_unavailable' => 'La ruta de tareas del proyecto no está disponible. ' . self::DELEGATION_ES,
				'store_todo_route_unavailable' => 'La ruta de creación de tareas no está disponible. ' . self::DELEGATION_ES,
				'update_todo_route_unavailable' => 'La ruta de actualización de tareas no está disponible. ' . self::DELEGATION_ES,
				'destroy_todo_route_unavailable' => 'La ruta de eliminación de tareas no está disponible. ' . self::DELEGATION_ES,
				'project_copy_store_route_unavailable' => 'La ruta de almacenamiento de copia de proyecto no está disponible. ' . self::DELEGATION_ES,
				'project_tasks_show_route_unavailable' => 'La ruta de visualización de tareas del proyecto no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'project_index_route_unavailable' => 'La route des projets n\'est pas disponible. ' . self::DELEGATION_FR,
				'show_project_route_unavailable' => 'La route d\'affichage du projet n\'est pas disponible. ' . self::DELEGATION_FR,
				'project_task_route_unavailable' => 'La route des tâches du projet n\'est pas disponible. ' . self::DELEGATION_FR,
				'store_todo_route_unavailable' => 'La route de création de tâches n\'est pas disponible. ' . self::DELEGATION_FR,
				'update_todo_route_unavailable' => 'La route de mise à jour des tâches n\'est pas disponible. ' . self::DELEGATION_FR,
				'destroy_todo_route_unavailable' => 'La route de suppression des tâches n\'est pas disponible. ' . self::DELEGATION_FR,
				'project_copy_store_route_unavailable' => 'La route de stockage de copie de projet n\'est pas disponible. ' . self::DELEGATION_FR,
				'project_tasks_show_route_unavailable' => 'La route d\'affichage des tâches du projet n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'project_index_route_unavailable' => 'נתיב פרויקטים אינו זמין. ' . self::DELEGATION_HE,
				'show_project_route_unavailable' => 'נתיב תצוגת הפרויקט אינו זמין. ' . self::DELEGATION_HE,
				'project_task_route_unavailable' => 'נתיב משימות הפרויקט אינו זמין. ' . self::DELEGATION_HE,
				'store_todo_route_unavailable' => 'נתיב יצירת משימות אינו זמין. ' . self::DELEGATION_HE,
				'update_todo_route_unavailable' => 'נתיב עדכון משימות אינו זמין. ' . self::DELEGATION_HE,
				'destroy_todo_route_unavailable' => 'נתיב מחיקת משימות אינו זמין. ' . self::DELEGATION_HE,
				'project_copy_store_route_unavailable' => 'נתיב אחסון העתקת הפרויקט אינו זמין. ' . self::DELEGATION_HE,
				'project_tasks_show_route_unavailable' => 'נתיב הצגת משימות הפרויקט אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'project_index_route_unavailable' => 'La rotta dei progetti non è disponibile. ' . self::DELEGATION_IT,
				'show_project_route_unavailable' => 'La rotta di visualizzazione del progetto non è disponibile. ' . self::DELEGATION_IT,
				'project_task_route_unavailable' => 'La rotta delle attività del progetto non è disponibile. ' . self::DELEGATION_IT,
				'store_todo_route_unavailable' => 'La rotta di creazione delle attività non è disponibile. ' . self::DELEGATION_IT,
				'update_todo_route_unavailable' => 'La rotta di aggiornamento delle attività non è disponibile. ' . self::DELEGATION_IT,
				'destroy_todo_route_unavailable' => 'La rotta di eliminazione delle attività non è disponibile. ' . self::DELEGATION_IT,
				'project_copy_store_route_unavailable' => 'La rotta di memorizzazione della copia del progetto non è disponibile. ' . self::DELEGATION_IT,
				'project_tasks_show_route_unavailable' => 'La rotta di visualizzazione delle attività del progetto non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'project_index_route_unavailable' => 'プロジェクトルートは利用できません。' . self::DELEGATION_JA,
				'show_project_route_unavailable' => 'プロジェクト表示ルートは利用できません。' . self::DELEGATION_JA,
				'project_task_route_unavailable' => 'プロジェクトタスクルートは利用できません。' . self::DELEGATION_JA,
				'store_todo_route_unavailable' => 'Todo作成ルートは利用できません。' . self::DELEGATION_JA,
				'update_todo_route_unavailable' => 'Todo更新ルートは利用できません。' . self::DELEGATION_JA,
				'destroy_todo_route_unavailable' => 'Todo削除ルートは利用できません。' . self::DELEGATION_JA,
				'project_copy_store_route_unavailable' => 'プロジェクトコピー保存ルートは利用できません。' . self::DELEGATION_JA,
				'project_tasks_show_route_unavailable' => 'プロジェクトタスク表示ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'project_index_route_unavailable' => 'Projectroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'show_project_route_unavailable' => 'Projectweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'project_task_route_unavailable' => 'Projecttaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'store_todo_route_unavailable' => 'Taakaanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'update_todo_route_unavailable' => 'Taakupdateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'destroy_todo_route_unavailable' => 'Taakverwijderingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'project_copy_store_route_unavailable' => 'Projectkopie opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'project_tasks_show_route_unavailable' => 'Projecttaakweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'project_index_route_unavailable' => 'Trasa projektów jest niedostępna. ' . self::DELEGATION_PL,
				'show_project_route_unavailable' => 'Trasa podglądu projektu jest niedostępna. ' . self::DELEGATION_PL,
				'project_task_route_unavailable' => 'Trasa zadań projektu jest niedostępna. ' . self::DELEGATION_PL,
				'store_todo_route_unavailable' => 'Trasa tworzenia zadań jest niedostępna. ' . self::DELEGATION_PL,
				'update_todo_route_unavailable' => 'Trasa aktualizacji zadań jest niedostępna. ' . self::DELEGATION_PL,
				'destroy_todo_route_unavailable' => 'Trasa usuwania zadań jest niedostępna. ' . self::DELEGATION_PL,
				'project_copy_store_route_unavailable' => 'Trasa przechowywania kopii projektu jest niedostępna. ' . self::DELEGATION_PL,
				'project_tasks_show_route_unavailable' => 'Trasa wyświetlania zadań projektu jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'project_index_route_unavailable' => 'A rota de projetos não está disponível. ' . self::DELEGATION_PT,
				'show_project_route_unavailable' => 'A rota de visualização do projeto não está disponível. ' . self::DELEGATION_PT,
				'project_task_route_unavailable' => 'A rota de tarefas do projeto não está disponível. ' . self::DELEGATION_PT,
				'store_todo_route_unavailable' => 'A rota de criação de tarefas não está disponível. ' . self::DELEGATION_PT,
				'update_todo_route_unavailable' => 'A rota de atualização de tarefas não está disponível. ' . self::DELEGATION_PT,
				'destroy_todo_route_unavailable' => 'A rota de exclusão de tarefas não está disponível. ' . self::DELEGATION_PT,
				'project_copy_store_route_unavailable' => 'A rota de armazenamento de cópia de projeto não está disponível. ' . self::DELEGATION_PT,
				'project_tasks_show_route_unavailable' => 'A rota de visualização de tarefas do projeto não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'project_index_route_unavailable' => 'A rota de projetos não está disponível. ' . self::DELEGATION_PTBR,
				'show_project_route_unavailable' => 'A rota de visualização do projeto não está disponível. ' . self::DELEGATION_PTBR,
				'project_task_route_unavailable' => 'A rota de tarefas do projeto não está disponível. ' . self::DELEGATION_PTBR,
				'store_todo_route_unavailable' => 'A rota de criação de tarefas não está disponível. ' . self::DELEGATION_PTBR,
				'update_todo_route_unavailable' => 'A rota de atualização de tarefas não está disponível. ' . self::DELEGATION_PTBR,
				'destroy_todo_route_unavailable' => 'A rota de exclusão de tarefas não está disponível. ' . self::DELEGATION_PTBR,
				'project_copy_store_route_unavailable' => 'A rota de armazenamento de cópia de projeto não está disponível. ' . self::DELEGATION_PTBR,
				'project_tasks_show_route_unavailable' => 'A rota de visualização de tarefas do projeto não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'project_index_route_unavailable' => 'Маршрут проектов недоступен. ' . self::DELEGATION_RU,
				'show_project_route_unavailable' => 'Маршрут просмотра проекта недоступен. ' . self::DELEGATION_RU,
				'project_task_route_unavailable' => 'Маршрут задач проекта недоступен. ' . self::DELEGATION_RU,
				'store_todo_route_unavailable' => 'Маршрут создания задач недоступен. ' . self::DELEGATION_RU,
				'update_todo_route_unavailable' => 'Маршрут обновления задач недоступен. ' . self::DELEGATION_RU,
				'destroy_todo_route_unavailable' => 'Маршрут удаления задач недоступен. ' . self::DELEGATION_RU,
				'project_copy_store_route_unavailable' => 'Маршрут хранения копии проекта недоступен. ' . self::DELEGATION_RU,
				'project_tasks_show_route_unavailable' => 'Маршрут показа задач проекта недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'project_index_route_unavailable' => 'Proje rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'show_project_route_unavailable' => 'Proje görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'project_task_route_unavailable' => 'Proje görev rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'store_todo_route_unavailable' => 'Görev oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'update_todo_route_unavailable' => 'Görev güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'destroy_todo_route_unavailable' => 'Görev silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'project_copy_store_route_unavailable' => 'Proje kopyası saklama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'project_tasks_show_route_unavailable' => 'Proje görevlerini göster rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'project_index_route_unavailable' => '项目路由不可用。' . self::DELEGATION_ZH,
				'show_project_route_unavailable' => '项目查看路由不可用。' . self::DELEGATION_ZH,
				'project_task_route_unavailable' => '项目任务路由不可用。' . self::DELEGATION_ZH,
				'store_todo_route_unavailable' => '待办事项创建路由不可用。' . self::DELEGATION_ZH,
				'update_todo_route_unavailable' => '待办事项更新路由不可用。' . self::DELEGATION_ZH,
				'destroy_todo_route_unavailable' => '待办事项删除路由不可用。' . self::DELEGATION_ZH,
				'project_copy_store_route_unavailable' => '项目副本存储路由不可用。' . self::DELEGATION_ZH,
				'project_tasks_show_route_unavailable' => '项目任务显示路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::PRJ_RPT => [
			'ar' => [
				'project_report_index_route_unavailable' => 'مسار تقارير المشروع غير متاح. ' . self::DELEGATION_AR,
				'project_report_view_route_unavailable' => 'مسار عرض تقرير المشروع غير متاح. ' . self::DELEGATION_AR,
				'project_report_edit_route_unavailable' => 'مسار تعديل تقرير المشروع غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'project_report_index_route_unavailable' => 'Projektrapportindeksruten er ikke tilgængelig. ' . self::DELEGATION_DA,
				'project_report_view_route_unavailable' => 'Vis projektrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'project_report_edit_route_unavailable' => 'Rediger projektets rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'project_report_index_route_unavailable' => 'Projektbericht-Indexroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'project_report_view_route_unavailable' => 'Projektbericht-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'project_report_edit_route_unavailable' => 'Projektbearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'project_report_index_route_unavailable' => 'Project report index route is unavailable. ' . self::DELEGATION_EN,
				'project_report_view_route_unavailable' => 'View project report route is unavailable. ' . self::DELEGATION_EN,
				'project_report_edit_route_unavailable' => 'Edit project route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'project_report_index_route_unavailable' => 'La ruta del índice de informes del proyecto no está disponible.' . self::DELEGATION_ES,
				'project_report_view_route_unavailable' => 'La ruta de visualización del informe del proyecto no está disponible.' . self::DELEGATION_ES,
				'project_report_edit_route_unavailable' => 'La ruta de edición del proyecto no está disponible.' . self::DELEGATION_ES
			],
			'fr' => [
				'project_report_index_route_unavailable' => 'La route de l\'index des rapports de projet est indisponible.' . self::DELEGATION_FR,
				'project_report_view_route_unavailable' => 'La route de visualisation du rapport de projet est indisponible.' . self::DELEGATION_FR,
				'project_report_edit_route_unavailable' => 'La route de modification du projet est indisponible.' . self::DELEGATION_FR
			],
			'he' => [
				'project_report_index_route_unavailable' => 'מסלול דוחות פרויקט אינו זמין. ' . self::DELEGATION_HE,
				'project_report_view_route_unavailable' => 'מסלול צפייה בדוח פרויקט אינו זמין. ' . self::DELEGATION_HE,
				'project_report_edit_route_unavailable' => 'מסלול עריכת דוח פרויקט אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'project_report_index_route_unavailable' => 'La rotta dell\'indice del report di progetto non è disponibile. ' . self::DELEGATION_IT,
				'project_report_view_route_unavailable' => 'La rotta di visualizzazione del report di progetto non è disponibile. ' . self::DELEGATION_IT,
				'project_report_edit_route_unavailable' => 'La rotta di modifica del progetto non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'project_report_index_route_unavailable' => 'プロジェクトレポートインデックスルートは利用できません。' . self::DELEGATION_JA,
				'project_report_view_route_unavailable' => 'プロジェクトレポート表示ルートは利用できません。' . self::DELEGATION_JA,
				'project_report_edit_route_unavailable' => 'プロジェクト編集ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'project_report_index_route_unavailable' => 'Projectrapport indexroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'project_report_view_route_unavailable' => 'Projectrapport weergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'project_report_edit_route_unavailable' => 'Projectbewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'project_report_index_route_unavailable' => 'Trasa indeksu raportu projektu jest niedostępna. ' . self::DELEGATION_PL,
				'project_report_view_route_unavailable' => 'Trasa podglądu raportu projektu jest niedostępna. ' . self::DELEGATION_PL,
				'project_report_edit_route_unavailable' => 'Trasa edycji projektu jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'project_report_index_route_unavailable' => 'A rota de índice de relatório de projeto não está disponível.' . self::DELEGATION_PT,
				'project_report_view_route_unavailable' => 'A rota de visualização de relatório de projeto não está disponível.' . self::DELEGATION_PT,
				'project_report_edit_route_unavailable' => 'A rota de edição do projeto não está disponível.' . self::DELEGATION_PT
			],
			'pt-br' => [
				'project_report_index_route_unavailable' => 'A rota de índice de relatório de projeto não está disponível. ' . self::DELEGATION_PTBR,
				'project_report_view_route_unavailable' => 'A rota de visualização de relatório de projeto não está disponível. ' . self::DELEGATION_PTBR,
				'project_report_edit_route_unavailable' => 'A rota de edição do projeto não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'project_report_index_route_unavailable' => 'Маршрут индекса отчетов проекта недоступен. ' . self::DELEGATION_RU,
				'project_report_view_route_unavailable' => 'Маршрут просмотра отчетов проекта недоступен. ' . self::DELEGATION_RU,
				'project_report_edit_route_unavailable' => 'Маршрут редактирования проекта недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'project_report_index_route_unavailable' => 'Proje raporu indeks rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'project_report_view_route_unavailable' => 'Proje raporu görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'project_report_edit_route_unavailable' => 'Proje düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'project_report_index_route_unavailable' => '项目报告索引路由不可用。' . self::DELEGATION_ZH,
				'project_report_view_route_unavailable' => '查看项目报告路由不可用。' . self::DELEGATION_ZH,
				'project_report_edit_route_unavailable' => '编辑项目路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::PRD_SV => [
			'ar' => ['product_services_index_route_unavailable' => 'مسار المنتجات والخدمات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['product_services_index_route_unavailable' => 'Produkt- og servicerute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['product_services_index_route_unavailable' => 'Produkt- und Dienstleistungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['product_services_index_route_unavailable' => 'Product & Services route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['product_services_index_route_unavailable' => 'La ruta de productos y servicios no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['product_services_index_route_unavailable' => 'La route des produits et services n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['product_services_index_route_unavailable' => 'נתיב מוצרים ושירותים אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['product_services_index_route_unavailable' => 'La rotta di prodotti e servizi non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['product_services_index_route_unavailable' => '製品＆サービスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['product_services_index_route_unavailable' => 'Product- en serviceroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['product_services_index_route_unavailable' => 'Trasa produktów i usług jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['product_services_index_route_unavailable' => 'A rota de produtos e serviços não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['product_services_index_route_unavailable' => 'A rota de produtos e serviços não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['product_services_index_route_unavailable' => 'Маршрут товаров и услуг недоступен. ' . self::DELEGATION_RU],
			'tr' => ['product_services_index_route_unavailable' => 'Ürün ve Hizmet rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['product_services_index_route_unavailable' => '产品和服务路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::PRD_SV_CAT => [
			'ar' => [
				'product_category_index_route_unavailable' => 'مسار حفظ فئة المنتج غير متاح. ' . self::DELEGATION_AR,
				'product_category_update_route_unavailable' => 'مسار تحديث فئة المنتج غير متاح. ' . self::DELEGATION_AR,
				'product_category_create_route_unavailable' => 'مسار إنشاء فئة المنتج غير متاح. ' . self::DELEGATION_AR,
				'product_category_edit_route_unavailable' => 'مسار تعديل فئة المنتج غير متاح. ' . self::DELEGATION_AR,
				'product_category_destroy_route_unavailable' => 'مسار حذف فئة المنتج غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'product_category_index_route_unavailable' => 'Produktkategori gemmerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_category_update_route_unavailable' => 'Produktkategori opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_category_create_route_unavailable' => 'Produktkategori oprettelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_category_edit_route_unavailable' => 'Produktkategori redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_category_destroy_route_unavailable' => 'Produktkategori sletterute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'product_category_index_route_unavailable' => 'Produktkategorie-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_category_update_route_unavailable' => 'Produktkategorie-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_category_create_route_unavailable' => 'Produktkategorie-Erstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_category_edit_route_unavailable' => 'Produktkategorie-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_category_destroy_route_unavailable' => 'Produktkategorie-Löschroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'product_category_index_route_unavailable' => 'Product category save route is unavailable. ' . self::DELEGATION_EN,
				'product_category_update_route_unavailable' => 'Product category update route is unavailable. ' . self::DELEGATION_EN,
				'product_category_create_route_unavailable' => 'Product category create route is unavailable. ' . self::DELEGATION_EN,
				'product_category_edit_route_unavailable' => 'Product category edit route is unavailable. ' . self::DELEGATION_EN,
				'product_category_destroy_route_unavailable' => 'Product category destroy route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'product_category_index_route_unavailable' => 'La ruta de guardado de categoría de producto no está disponible. ' . self::DELEGATION_ES,
				'product_category_update_route_unavailable' => 'La ruta de actualización de categoría de producto no está disponible. ' . self::DELEGATION_ES,
				'product_category_create_route_unavailable' => 'La ruta de creación de categoría de producto no está disponible. ' . self::DELEGATION_ES,
				'product_category_edit_route_unavailable' => 'La ruta de edición de categoría de producto no está disponible. ' . self::DELEGATION_ES,
				'product_category_destroy_route_unavailable' => 'La ruta de eliminación de categoría de producto no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'product_category_index_route_unavailable' => 'La route de sauvegarde de catégorie de produit n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_category_update_route_unavailable' => 'La route de mise à jour de catégorie de produit n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_category_create_route_unavailable' => 'La route de création de catégorie de produit n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_category_edit_route_unavailable' => 'La route d\'édition de catégorie de produit n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_category_destroy_route_unavailable' => 'La route de suppression de catégorie de produit n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'product_category_index_route_unavailable' => 'נתיב שמירת קטגוריית מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'product_category_update_route_unavailable' => 'נתיב עדכון קטגוריית מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'product_category_create_route_unavailable' => 'נתיב יצירת קטגוריית מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'product_category_edit_route_unavailable' => 'נתיב עריכת קטגוריית מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'product_category_destroy_route_unavailable' => 'נתיב מחיקת קטגוריית מוצרים אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'product_category_index_route_unavailable' => 'La rotta di salvataggio della categoria prodotto non è disponibile. ' . self::DELEGATION_IT,
				'product_category_update_route_unavailable' => 'La rotta di aggiornamento della categoria prodotto non è disponibile. ' . self::DELEGATION_IT,
				'product_category_create_route_unavailable' => 'La rotta di creazione della categoria prodotto non è disponibile. ' . self::DELEGATION_IT,
				'product_category_edit_route_unavailable' => 'La rotta di modifica della categoria prodotto non è disponibile. ' . self::DELEGATION_IT,
				'product_category_destroy_route_unavailable' => 'La rotta di eliminazione della categoria prodotto non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'product_category_index_route_unavailable' => '製品カテゴリ保存ルートは利用できません。 ' . self::DELEGATION_JA,
				'product_category_update_route_unavailable' => '製品カテゴリ更新ルートは利用できません。 ' . self::DELEGATION_JA,
				'product_category_create_route_unavailable' => '製品カテゴリ作成ルートは利用できません。 ' . self::DELEGATION_JA,
				'product_category_edit_route_unavailable' => '製品カテゴリ編集ルートは利用できません。 ' . self::DELEGATION_JA,
				'product_category_destroy_route_unavailable' => '製品カテゴリ削除ルートは利用できません。 ' . self::DELEGATION_JA,
			],
			'nl' => [
				'product_category_index_route_unavailable' => 'Productcategorie opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_category_update_route_unavailable' => 'Productcategorie updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_category_create_route_unavailable' => 'Productcategorie aanmaakroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_category_edit_route_unavailable' => 'Productcategorie bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_category_destroy_route_unavailable' => 'Productcategorie verwijderroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'product_category_index_route_unavailable' => 'Trasa zapisu kategorii produktów jest niedostępna. ' . self::DELEGATION_PL,
				'product_category_update_route_unavailable' => 'Trasa aktualizacji kategorii produktów jest niedostępna. ' . self::DELEGATION_PL,
				'product_category_create_route_unavailable' => 'Trasa tworzenia kategorii produktów jest niedostępna. ' . self::DELEGATION_PL,
				'product_category_edit_route_unavailable' => 'Trasa edycji kategorii produktów jest niedostępna. ' . self::DELEGATION_PL,
				'product_category_destroy_route_unavailable' => 'Trasa usuwania kategorii produktów jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'product_category_index_route_unavailable' => 'A rota de salvamento de categoria de produto não está disponível. ' . self::DELEGATION_PT,
				'product_category_update_route_unavailable' => 'A rota de atualização de categoria de produto não está disponível. ' . self::DELEGATION_PT,
				'product_category_create_route_unavailable' => 'A rota de criação de categoria de produto não está disponível. ' . self::DELEGATION_PT,
				'product_category_edit_route_unavailable' => 'A rota de edição de categoria de produto não está disponível. ' . self::DELEGATION_PT,
				'product_category_destroy_route_unavailable' => 'A rota de destruição de categoria de produto não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'product_category_index_route_unavailable' => 'A rota de salvamento de categoria de produto não está disponível. ' . self::DELEGATION_PTBR,
				'product_category_update_route_unavailable' => 'A rota de atualização de categoria de produto não está disponível. ' . self::DELEGATION_PTBR,
				'product_category_create_route_unavailable' => 'A rota de criação de categoria de produto não está disponível. ' . self::DELEGATION_PTBR,
				'product_category_edit_route_unavailable' => 'A rota de edição de categoria de produto não está disponível. ' . self::DELEGATION_PTBR,
				'product_category_destroy_route_unavailable' => 'A rota de destruição de categoria de produto não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'product_category_index_route_unavailable' => 'Маршрут сохранения категории продуктов недоступен. ' . self::DELEGATION_RU,
				'product_category_update_route_unavailable' => 'Маршрут обновления категории продуктов недоступен. ' . self::DELEGATION_RU,
				'product_category_create_route_unavailable' => 'Маршрут создания категории продуктов недоступен. ' . self::DELEGATION_RU,
				'product_category_edit_route_unavailable' => 'Маршрут редактирования категории продуктов недоступен. ' . self::DELEGATION_RU,
				'product_category_destroy_route_unavailable' => 'Маршрут удаления категории продуктов недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'product_category_index_route_unavailable' => 'Ürün kategorisi kayıt rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_category_update_route_unavailable' => 'Ürün kategorisi güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_category_create_route_unavailable' => 'Ürün kategorisi oluşturma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_category_edit_route_unavailable' => 'Ürün kategorisi düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_category_destroy_route_unavailable' => 'Ürün kategorisi silme rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'product_category_index_route_unavailable' => '产品类别保存路由不可用。 ' . self::DELEGATION_ZH,
				'product_category_update_route_unavailable' => '产品类别更新路由不可用。 ' . self::DELEGATION_ZH,
				'product_category_create_route_unavailable' => '产品类别创建路由不可用。 ' . self::DELEGATION_ZH,
				'product_category_edit_route_unavailable' => '产品类别编辑路由不可用。 ' . self::DELEGATION_ZH,
				'product_category_destroy_route_unavailable' => '产品类别删除路由不可用。 ' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::PRD_STK => [
			'ar' => [
				'product_stock_index_route_unavailable' => 'مسار مخزون المنتجات غير متاح. ' . self::DELEGATION_AR,
				'product_stock_update_route_unavailable' => 'مسار تحديث مخزون المنتجات غير متاح. ' . self::DELEGATION_AR,
				'product_stock_edit_route_unavailable' => 'مسار تعديل مخزون المنتجات غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'product_stock_index_route_unavailable' => 'Produktlager-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_stock_update_route_unavailable' => 'Produktlager opdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_stock_edit_route_unavailable' => 'Produktlager redigeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'product_stock_index_route_unavailable' => 'Produktbestands-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_stock_update_route_unavailable' => 'Produktbestands-Aktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_stock_edit_route_unavailable' => 'Produktbestands-Bearbeitungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'product_stock_index_route_unavailable' => 'Product Stock route is unavailable. ' . self::DELEGATION_EN,
				'product_stock_update_route_unavailable' => 'Product stock update route is unavailable. ' . self::DELEGATION_EN,
				'product_stock_edit_route_unavailable' => 'Product stock edit route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'product_stock_index_route_unavailable' => 'La ruta de stock de productos no está disponible. ' . self::DELEGATION_ES,
				'product_stock_update_route_unavailable' => 'La ruta de actualización de stock de productos no está disponible. ' . self::DELEGATION_ES,
				'product_stock_edit_route_unavailable' => 'La ruta de edición de stock de productos no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'product_stock_index_route_unavailable' => 'La route du stock de produits n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_stock_update_route_unavailable' => 'La route de mise à jour du stock de produits n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_stock_edit_route_unavailable' => 'La route d\'édition du stock de produits n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'product_stock_index_route_unavailable' => 'נתיב מלאי מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'product_stock_update_route_unavailable' => 'נתיב עדכון מלאי מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'product_stock_edit_route_unavailable' => 'נתיב עריכת מלאי מוצרים אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'product_stock_index_route_unavailable' => 'La rotta della scorta di prodotti non è disponibile. ' . self::DELEGATION_IT,
				'product_stock_update_route_unavailable' => 'La rotta di aggiornamento scorta prodotti non è disponibile. ' . self::DELEGATION_IT,
				'product_stock_edit_route_unavailable' => 'La rotta di modifica scorta prodotti non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'product_stock_index_route_unavailable' => '製品在庫ルートは利用できません。' . self::DELEGATION_JA,
				'product_stock_update_route_unavailable' => '製品在庫更新ルートは利用できません。' . self::DELEGATION_JA,
				'product_stock_edit_route_unavailable' => '製品在庫編集ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'product_stock_index_route_unavailable' => 'Productvoorraadroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_stock_update_route_unavailable' => 'Productvoorraad updateroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_stock_edit_route_unavailable' => 'Productvoorraad bewerkingsroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'product_stock_index_route_unavailable' => 'Trasa stanów magazynowych produktów jest niedostępna. ' . self::DELEGATION_PL,
				'product_stock_update_route_unavailable' => 'Trasa aktualizacji stanu magazynowego produktów jest niedostępna. ' . self::DELEGATION_PL,
				'product_stock_edit_route_unavailable' => 'Trasa edycji stanu magazynowego produktów jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'product_stock_index_route_unavailable' => 'A rota de estoque de produtos não está disponível. ' . self::DELEGATION_PT,
				'product_stock_update_route_unavailable' => 'A rota de atualização de estoque de produtos não está disponível. ' . self::DELEGATION_PT,
				'product_stock_edit_route_unavailable' => 'A rota de edição de estoque de produtos não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'product_stock_index_route_unavailable' => 'A rota de estoque de produtos não está disponível. ' . self::DELEGATION_PTBR,
				'product_stock_update_route_unavailable' => 'A rota de atualização de estoque de produtos não está disponível. ' . self::DELEGATION_PTBR,
				'product_stock_edit_route_unavailable' => 'A rota de edição de estoque de produtos não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'product_stock_index_route_unavailable' => 'Маршрут запасов товаров недоступен. ' . self::DELEGATION_RU,
				'product_stock_update_route_unavailable' => 'Маршрут обновления запасов товаров недоступен. ' . self::DELEGATION_RU,
				'product_stock_edit_route_unavailable' => 'Маршрут редактирования запасов товаров недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'product_stock_index_route_unavailable' => 'Ürün Stok rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_stock_update_route_unavailable' => 'Ürün Stok güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_stock_edit_route_unavailable' => 'Ürün Stok düzenleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'product_stock_index_route_unavailable' => '产品库存路由不可用。' . self::DELEGATION_ZH,
				'product_stock_update_route_unavailable' => '产品库存更新路由不可用。' . self::DELEGATION_ZH,
				'product_stock_edit_route_unavailable' => '产品库存编辑路由不可用。' . self::DELEGATION_ZH
			],
		],
		ViewsConstants::PRJ_TSK_STG => [
			'ar' => ['project_task_stages_index_route_unavailable' => 'مسار مراحل مهمة المشروع غير متاح. ' . self::DELEGATION_AR],
			'da' => ['project_task_stages_index_route_unavailable' => 'Projektopgavefaserute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['project_task_stages_index_route_unavailable' => 'Projektaufgabenphasen-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['project_task_stages_index_route_unavailable' => 'Project Task Stages route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['project_task_stages_index_route_unavailable' => 'La ruta de etapas de tareas del proyecto no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['project_task_stages_index_route_unavailable' => 'La route des étapes de tâches du projet n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['project_task_stages_index_route_unavailable' => 'נתיב שלבי משימות פרויקט אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['project_task_stages_index_route_unavailable' => 'La rotta delle fasi delle attività di progetto non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['project_task_stages_index_route_unavailable' => 'プロジェクトタスクステージルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['project_task_stages_index_route_unavailable' => 'Projecttaakfasenroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['project_task_stages_index_route_unavailable' => 'Trasa etapów zadania projektu jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['project_task_stages_index_route_unavailable' => 'A rota de estágios de tarefas do projeto não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['project_task_stages_index_route_unavailable' => 'A rota de estágios de tarefas do projeto não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['project_task_stages_index_route_unavailable' => 'Маршрут этапов задач проекта недоступен. ' . self::DELEGATION_RU],
			'tr' => ['project_task_stages_index_route_unavailable' => 'Proje Görev Aşamaları rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['project_task_stages_index_route_unavailable' => '项目任务阶段路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::PRM => [
			'ar' => ['promotion_index_route_unavailable' => 'مسار فهرس الترقيات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['promotion_index_route_unavailable' => 'Forfremmelsesindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['promotion_index_route_unavailable' => 'Beförderungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['promotion_index_route_unavailable' => 'Promotion index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['promotion_index_route_unavailable' => 'La ruta del índice de promociones no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['promotion_index_route_unavailable' => 'La route de l\'index des promotions est indisponible. ' . self::DELEGATION_FR],
			'he' => ['promotion_index_route_unavailable' => 'נתיב אינדקס קידום אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['promotion_index_route_unavailable' => 'La rotta dell\'indice delle promozioni non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['promotion_index_route_unavailable' => '昇進インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['promotion_index_route_unavailable' => 'Promotieindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['promotion_index_route_unavailable' => 'Trasa indeksu awansów jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['promotion_index_route_unavailable' => 'A rota do índice de promoções não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['promotion_index_route_unavailable' => 'A rota do índice de promoções não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['promotion_index_route_unavailable' => 'Маршрут индекса повышений недоступен. ' . self::DELEGATION_RU],
			'tr' => ['promotion_index_route_unavailable' => 'Terfi indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['promotion_index_route_unavailable' => '晋升索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::RL => [
			'ar' => ['role_index_route_unavailable' => 'مسار الأدوار غير متاح. ' . self::DELEGATION_AR],
			'da' => ['role_index_route_unavailable' => 'Rolle-rute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['role_index_route_unavailable' => 'Rollen-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['role_index_route_unavailable' => 'Role route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['role_index_route_unavailable' => 'La ruta de roles no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['role_index_route_unavailable' => 'La route des rôles n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['role_index_route_unavailable' => 'נתיב תפקידים אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['role_index_route_unavailable' => 'La rotta dei ruoli non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['role_index_route_unavailable' => 'ロールルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['role_index_route_unavailable' => 'Rolroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['role_index_route_unavailable' => 'Trasa ról jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['role_index_route_unavailable' => 'A rota de funções não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['role_index_route_unavailable' => 'A rota de funções não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['role_index_route_unavailable' => 'Маршрут ролей недоступен. ' . self::DELEGATION_RU],
			'tr' => ['role_index_route_unavailable' => 'Rol rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['role_index_route_unavailable' => '角色路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::RPT => [
			'ar' => [
				'download_daily_purchase_unavailable' => 'وظيفة تنزيل المشتريات اليومية غير متاحة. ' . self::DELEGATION_AR,
				'monthly_purchase_unavailable' => 'مسار المشتريات الشهرية غير متاح. ' . self::DELEGATION_AR,
				'filter_report_unavailable' => 'مسار تقرير التصفية غير متاح. ' . self::DELEGATION_AR,
				'daily_purchase_nav_unavailable' => 'وظيفة التنقل بين المشتريات اليومية غير متاحة. ' . self::DELEGATION_AR,
				'download_monthly_purchase_unavailable' => 'وظيفة تنزيل المشتريات الشهرية غير متاحة. ' . self::DELEGATION_AR,
				'account_statement_route_unavailable' => 'مسار كشف الحساب غير متاح. ' . self::DELEGATION_AR,
				'invoice_summary_route_unavailable' => 'مسار ملخص الفواتير غير متاح. ' . self::DELEGATION_AR,
				'sales_report_route_unavailable' => 'مسار تقرير المبيعات غير متاح. ' . self::DELEGATION_AR,
				'receivables_report_route_unavailable' => 'مسار المستحقات غير متاح. ' . self::DELEGATION_AR,
				'payables_report_route_unavailable' => 'مسار المدفوعات غير متاح. ' . self::DELEGATION_AR,
				'bill_summary_route_unavailable' => 'مسار ملخص الفواتير غير متاح. ' . self::DELEGATION_AR,
				'product_stock_report_route_unavailable' => 'مسار تقرير مخزون المنتج غير متاح. ' . self::DELEGATION_AR,
				'monthly_cashflow_route_unavailable' => 'مسار التدفق النقدي غير متاح. ' . self::DELEGATION_AR,
				'income_summary_route_unavailable' => 'مسار ملخص الدخل غير متاح. ' . self::DELEGATION_AR,
				'expense_summary_route_unavailable' => 'مسار ملخص المصروفات غير متاح. ' . self::DELEGATION_AR,
				'income_vs_expense_summary_route_unavailable' => 'مسار الدخل مقابل المصروفات غير متاح. ' . self::DELEGATION_AR,
				'tax_summary_unavailable' => 'مسار ملخص الضرائب غير متاح. ' . self::DELEGATION_AR,
				'payroll_route_unavailable' => 'مسار كشوف المرتبات غير متاح. ' . self::DELEGATION_AR,
				'leave_route_unavailable' => 'مسار الإجازات غير متاح. ' . self::DELEGATION_AR,
				'monthly_attendance_route_unavailable' => 'مسار الحضور الشهري غير متاح. ' . self::DELEGATION_AR,
				'lead_route_unavailable' => 'مسار تقرير العملاء المحتملين غير متاح. ' . self::DELEGATION_AR,
				'deal_route_unavailable' => 'مسار تقرير الصفقات غير متاح. ' . self::DELEGATION_AR,
				'warehouse_report_route_unavailable' => 'مسار تقرير المستودعات غير متاح. ' . self::DELEGATION_AR,
				'daily_purchase_report_route_unavailable' => 'مسار تقرير المشتريات اليومية/الشهرية غير متاح. ' . self::DELEGATION_AR,
				'daily_pos_report_route_unavailable' => 'مسار تقرير نقاط البيع اليومية/الشهرية غير متاح. ' . self::DELEGATION_AR,
				'pos_vs_purchase_report_route_unavailable' => 'مسار تقرير نقاط البيع مقابل المشتريات غير متاح. ' . self::DELEGATION_AR,
				'rpt_ledger_route_unavailable' => 'مسار ملخص الأستاذ غير متاح. ' . self::DELEGATION_AR,
				'rpt_balance_sheet_route_unavailable' => 'مسار الميزانية العمومية غير متاح. ' . self::DELEGATION_AR,
				'rpt_profit_loss_route_unavailable' => 'مسار الأرباح والخسائر غير متاح. ' . self::DELEGATION_AR,
				'trial_balance_route_unavailable' => 'مسار ميزان المراجعة غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'download_daily_purchase_unavailable' => 'Downloadfunktion til daglige køb er ikke tilgængelig. ' . self::DELEGATION_DA,
				'monthly_purchase_unavailable' => 'Månedlig købsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'filter_report_unavailable' => 'Filtreringsrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'daily_purchase_nav_unavailable' => 'Daglig købsnavigation er ikke tilgængelig. ' . self::DELEGATION_DA,
				'download_monthly_purchase_unavailable' => 'Downloadfunktion til månedlige køb er ikke tilgængelig. ' . self::DELEGATION_DA,
				'account_statement_route_unavailable' => 'Kontoudtogsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'invoice_summary_route_unavailable' => 'Fakturaoversigtsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'sales_report_route_unavailable' => 'Salgsrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'receivables_report_route_unavailable' => 'Tilgodehavender-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payables_report_route_unavailable' => 'Gælds-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'bill_summary_route_unavailable' => 'Regningsoversigtsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'product_stock_report_route_unavailable' => 'Produktlagerrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'monthly_cashflow_route_unavailable' => 'Pengestrømsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'income_summary_route_unavailable' => 'Indtægtsoversigtsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'expense_summary_route_unavailable' => 'Udgiftsoversigtsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'income_vs_expense_summary_route_unavailable' => 'Indtægt vs. udgift-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'tax_summary_unavailable' => 'Skatteoversigtsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'payroll_route_unavailable' => 'Lønrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'leave_route_unavailable' => 'Fraværsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'monthly_attendance_route_unavailable' => 'Månedlig fremmøderute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'lead_route_unavailable' => 'Kundeemnerapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'deal_route_unavailable' => 'Aftalerapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'warehouse_report_route_unavailable' => 'Lagerrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'daily_purchase_report_route_unavailable' => 'Daglig/månedlig indkøbsrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'daily_pos_report_route_unavailable' => 'Daglig/månedlig POS-rapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pos_vs_purchase_report_route_unavailable' => 'POS vs indkøbsrapportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'rpt_ledger_route_unavailable' => 'Hovedbogssummeringsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'rpt_balance_sheet_route_unavailable' => 'Balancerute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'rpt_profit_loss_route_unavailable' => 'Resultatopgørelsesrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'trial_balance_route_unavailable' => 'Prøvebalancerute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'download_daily_purchase_unavailable' => 'Download-Funktion für tägliche Einkäufe ist nicht verfügbar. ' . self::DELEGATION_DE,
				'monthly_purchase_unavailable' => 'Monatliche Einkaufsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'filter_report_unavailable' => 'Filterberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'daily_purchase_nav_unavailable' => 'Tägliche Einkaufsnavigation ist nicht verfügbar. ' . self::DELEGATION_DE,
				'download_monthly_purchase_unavailable' => 'Download-Funktion für monatliche Einkäufe ist nicht verfügbar. ' . self::DELEGATION_DE,
				'account_statement_route_unavailable' => 'Kontoauszugsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'invoice_summary_route_unavailable' => 'Rechnungsübersichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'sales_report_route_unavailable' => 'Verkaufsberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'receivables_report_route_unavailable' => 'Forderungsberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payables_report_route_unavailable' => 'Verbindlichkeitsberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'bill_summary_route_unavailable' => 'Rechnungsübersichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'product_stock_report_route_unavailable' => 'Produktbestandsberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'monthly_cashflow_route_unavailable' => 'Zahlungsflussroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'income_summary_route_unavailable' => 'Einkommensübersichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'expense_summary_route_unavailable' => 'Ausgabenübersichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'income_vs_expense_summary_route_unavailable' => 'Einnahmen-Ausgaben-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'tax_summary_unavailable' => 'Steuerübersichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'payroll_route_unavailable' => 'Gehaltsabrechnungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'leave_route_unavailable' => 'Abwesenheitsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'monthly_attendance_route_unavailable' => 'Monatliche Anwesenheitsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'lead_route_unavailable' => 'Lead-Berichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'deal_route_unavailable' => 'Deal-Berichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'warehouse_report_route_unavailable' => 'Lagerberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'daily_purchase_report_route_unavailable' => 'Tägliche/monatliche Einkaufsberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'daily_pos_report_route_unavailable' => 'Tägliche/monatliche POS-Berichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pos_vs_purchase_report_route_unavailable' => 'POS vs Einkaufsberichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'rpt_ledger_route_unavailable' => 'Hauptbuchzusammenfassungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'rpt_balance_sheet_route_unavailable' => 'Bilanzroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'rpt_profit_loss_route_unavailable' => 'Gewinn- und Verlustrechnungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'trial_balance_route_unavailable' => 'Saldenlistenroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'download_daily_purchase_unavailable' => 'Download function for daily purchases is unavailable. ' . self::DELEGATION_EN,
				'monthly_purchase_unavailable' => 'Monthly purchase route is unavailable. ' . self::DELEGATION_EN,
				'filter_report_unavailable' => 'Filter report route is unavailable. ' . self::DELEGATION_EN,
				'daily_purchase_nav_unavailable' => 'Daily purchase navigation is unavailable. ' . self::DELEGATION_EN,
				'download_monthly_purchase_unavailable' => 'Download function for monthly purchases is unavailable. ' . self::DELEGATION_EN,
				'account_statement_route_unavailable' => 'Account statement route is unavailable. ' . self::DELEGATION_EN,
				'invoice_summary_route_unavailable' => 'Invoice summary route is unavailable. ' . self::DELEGATION_EN,
				'sales_report_route_unavailable' => 'Sales report route is unavailable. ' . self::DELEGATION_EN,
				'receivables_report_route_unavailable' => 'Receivables route is unavailable. ' . self::DELEGATION_EN,
				'payables_report_route_unavailable' => 'Payables route is unavailable. ' . self::DELEGATION_EN,
				'bill_summary_route_unavailable' => 'Bill summary route is unavailable. ' . self::DELEGATION_EN,
				'product_stock_report_route_unavailable' => 'Product stock report route is unavailable. ' . self::DELEGATION_EN,
				'monthly_cashflow_route_unavailable' => 'Cash flow route is unavailable. ' . self::DELEGATION_EN,
				'income_summary_route_unavailable' => 'Income summary route is unavailable. ' . self::DELEGATION_EN,
				'expense_summary_route_unavailable' => 'Expense summary route is unavailable. ' . self::DELEGATION_EN,
				'income_vs_expense_summary_route_unavailable' => 'Income VS Expense route is unavailable. ' . self::DELEGATION_EN,
				'tax_summary_unavailable' => 'Tax summary route is unavailable. ' . self::DELEGATION_EN,
				'payroll_route_unavailable' => 'Payroll route is unavailable. ' . self::DELEGATION_EN,
				'leave_route_unavailable' => 'Leave route is unavailable. ' . self::DELEGATION_EN,
				'monthly_attendance_route_unavailable' => 'Monthly attendance route is unavailable. ' . self::DELEGATION_EN,
				'lead_route_unavailable' => 'Lead report route is unavailable. ' . self::DELEGATION_EN,
				'deal_route_unavailable' => 'Deal report route is unavailable. ' . self::DELEGATION_EN,
				'warehouse_report_route_unavailable' => 'Warehouse report route is unavailable. ' . self::DELEGATION_EN,
				'daily_purchase_report_route_unavailable' => 'Purchase daily/monthly report route is unavailable. ' . self::DELEGATION_EN,
				'daily_pos_report_route_unavailable' => 'POS daily/monthly report route is unavailable. ' . self::DELEGATION_EN,
				'pos_vs_purchase_report_route_unavailable' => 'POS VS Purchase report route is unavailable. ' . self::DELEGATION_EN,
				'rpt_ledger_route_unavailable' => 'Ledger Summary route is unavailable. ' . self::DELEGATION_EN,
				'rpt_balance_sheet_route_unavailable' => 'Balance Sheet route is unavailable. ' . self::DELEGATION_EN,
				'rpt_profit_loss_route_unavailable' => 'Profit & Loss route is unavailable. ' . self::DELEGATION_EN,
				'trial_balance_route_unavailable' => 'Trial Balance route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'download_daily_purchase_unavailable' => 'La función de descarga para compras diarias no está disponible.' . self::DELEGATION_ES,
				'monthly_purchase_unavailable' => 'La ruta de compra mensual no está disponible.' . self::DELEGATION_ES,
				'filter_report_unavailable' => 'La ruta del informe de filtro no está disponible.' . self::DELEGATION_ES,
				'daily_purchase_nav_unavailable' => 'La navegación de compras diarias no está disponible.' . self::DELEGATION_ES,
				'download_monthly_purchase_unavailable' => 'La función de descarga para compras mensuales no está disponible.' . self::DELEGATION_ES,
				'account_statement_route_unavailable' => 'La ruta del extracto de cuenta no está disponible. ' . self::DELEGATION_ES,
				'invoice_summary_route_unavailable' => 'La ruta del resumen de facturas no está disponible. ' . self::DELEGATION_ES,
				'sales_report_route_unavailable' => 'La ruta del informe de ventas no está disponible. ' . self::DELEGATION_ES,
				'receivables_report_route_unavailable' => 'La ruta de cuentas por cobrar no está disponible. ' . self::DELEGATION_ES,
				'payables_report_route_unavailable' => 'La ruta de cuentas por pagar no está disponible. ' . self::DELEGATION_ES,
				'bill_summary_route_unavailable' => 'La ruta del resumen de facturas no está disponible. ' . self::DELEGATION_ES,
				'product_stock_report_route_unavailable' => 'La ruta del informe de stock de productos no está disponible. ' . self::DELEGATION_ES,
				'monthly_cashflow_route_unavailable' => 'La ruta del flujo de efectivo no está disponible. ' . self::DELEGATION_ES,
				'income_summary_route_unavailable' => 'La ruta del resumen de ingresos no está disponible. ' . self::DELEGATION_ES,
				'expense_summary_route_unavailable' => 'La ruta del resumen de gastos no está disponible. ' . self::DELEGATION_ES,
				'income_vs_expense_summary_route_unavailable' => 'La ruta de ingresos vs gastos no está disponible. ' . self::DELEGATION_ES,
				'tax_summary_unavailable' => 'La ruta del resumen de impuestos no está disponible. ' . self::DELEGATION_ES,
				'payroll_route_unavailable' => 'La ruta de nómina no está disponible. ' . self::DELEGATION_ES,
				'leave_route_unavailable' => 'La ruta de permisos no está disponible. ' . self::DELEGATION_ES,
				'monthly_attendance_route_unavailable' => 'La ruta de asistencia mensual no está disponible. ' . self::DELEGATION_ES,
				'lead_route_unavailable' => 'La ruta del informe de clientes potenciales no está disponible. ' . self::DELEGATION_ES,
				'deal_route_unavailable' => 'La ruta del informe de tratos no está disponible. ' . self::DELEGATION_ES,
				'warehouse_report_route_unavailable' => 'La ruta del informe de almacén no está disponible. ' . self::DELEGATION_ES,
				'daily_purchase_report_route_unavailable' => 'La ruta del informe diario/mensual de compras no está disponible. ' . self::DELEGATION_ES,
				'daily_pos_report_route_unavailable' => 'La ruta del informe diario/mensual de POS no está disponible. ' . self::DELEGATION_ES,
				'pos_vs_purchase_report_route_unavailable' => 'La ruta del informe POS vs Compras no está disponible. ' . self::DELEGATION_ES,
				'rpt_ledger_route_unavailable' => 'La ruta del Resumen del libro mayor no está disponible. ' . self::DELEGATION_ES,
				'rpt_balance_sheet_route_unavailable' => 'La ruta del Balance de situación no está disponible. ' . self::DELEGATION_ES,
				'rpt_profit_loss_route_unavailable' => 'La ruta de Pérdidas y Ganancias no está disponible. ' . self::DELEGATION_ES,
				'trial_balance_route_unavailable' => 'La ruta del Balance de comprobación no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'download_daily_purchase_unavailable' => 'La fonction de téléchargement pour les achats quotidiens est indisponible.' . self::DELEGATION_FR,
				'monthly_purchase_unavailable' => 'La route d\'achat mensuelle est indisponible.' . self::DELEGATION_FR,
				'filter_report_unavailable' => 'La route du rapport de filtre est indisponible.' . self::DELEGATION_FR,
				'daily_purchase_nav_unavailable' => 'La navigation des achats quotidiens est indisponible.' . self::DELEGATION_FR,
				'download_monthly_purchase_unavailable' => 'La fonction de téléchargement pour les achats mensuels est indisponible.' . self::DELEGATION_FR,
				'account_statement_route_unavailable' => 'La route du relevé de compte n\'est pas disponible. ' . self::DELEGATION_FR,
				'invoice_summary_route_unavailable' => 'La route du récapitulatif de factures n\'est pas disponible. ' . self::DELEGATION_FR,
				'sales_report_route_unavailable' => 'La route du rapport de ventes n\'est pas disponible. ' . self::DELEGATION_FR,
				'receivables_report_route_unavailable' => 'La route des créances n\'est pas disponible. ' . self::DELEGATION_FR,
				'payables_report_route_unavailable' => 'La route des dettes n\'est pas disponible. ' . self::DELEGATION_FR,
				'bill_summary_route_unavailable' => 'La route du récapitulatif de factures n\'est pas disponible. ' . self::DELEGATION_FR,
				'product_stock_report_route_unavailable' => 'La route du rapport de stock produit n\'est pas disponible. ' . self::DELEGATION_FR,
				'monthly_cashflow_route_unavailable' => 'La route du flux de trésorerie n\'est pas disponible. ' . self::DELEGATION_FR,
				'income_summary_route_unavailable' => 'La route du récapitulatif des revenus n\'est pas disponible. ' . self::DELEGATION_FR,
				'expense_summary_route_unavailable' => 'La route du récapitulatif des dépenses n\'est pas disponible. ' . self::DELEGATION_FR,
				'income_vs_expense_summary_route_unavailable' => 'La route revenus vs dépenses n\'est pas disponible. ' . self::DELEGATION_FR,
				'tax_summary_unavailable' => 'La route du récapitulatif fiscal n\'est pas disponible. ' . self::DELEGATION_FR,
				'payroll_route_unavailable' => 'La route de paie est indisponible. ' . self::DELEGATION_FR,
				'leave_route_unavailable' => 'La route des congés est indisponible. ' . self::DELEGATION_FR,
				'monthly_attendance_route_unavailable' => 'La route de présence mensuelle est indisponible. ' . self::DELEGATION_FR,
				'lead_route_unavailable' => 'La route du rapport de prospects est indisponible. ' . self::DELEGATION_FR,
				'deal_route_unavailable' => 'La route du rapport d\'accords est indisponible. ' . self::DELEGATION_FR,
				'warehouse_report_route_unavailable' => 'La route du rapport d\'entrepôt est indisponible. ' . self::DELEGATION_FR,
				'daily_purchase_report_route_unavailable' => 'La route du rapport journalier/mensuel d\'achat est indisponible. ' . self::DELEGATION_FR,
				'daily_pos_report_route_unavailable' => 'La route du rapport journalier/mensuel de PDV est indisponible. ' . self::DELEGATION_FR,
				'pos_vs_purchase_report_route_unavailable' => 'La route du rapport PDV vs Achat est indisponible. ' . self::DELEGATION_FR,
				'rpt_ledger_route_unavailable' => 'La route du résumé du grand livre n\'est pas disponible. ' . self::DELEGATION_FR,
				'rpt_balance_sheet_route_unavailable' => 'La route du bilan n\'est pas disponible. ' . self::DELEGATION_FR,
				'rpt_profit_loss_route_unavailable' => 'La route des profits et pertes n\'est pas disponible. ' . self::DELEGATION_FR,
				'trial_balance_route_unavailable' => 'La route de la balance de vérification n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'download_daily_purchase_unavailable' => 'פונקציית הורדת רכישות יומיות אינה זמינה. ' . self::DELEGATION_HE,
				'monthly_purchase_unavailable' => 'מסלול רכישות חודשיות אינו זמין. ' . self::DELEGATION_HE,
				'filter_report_unavailable' => 'מסלול דוח מסנן אינו זמין. ' . self::DELEGATION_HE,
				'daily_purchase_nav_unavailable' => 'ניווט רכישות יומיות אינו זמין. ' . self::DELEGATION_HE,
				'download_monthly_purchase_unavailable' => 'פונקציית הורדת רכישות חודשיות אינה זמינה. ' . self::DELEGATION_HE,
				'account_statement_route_unavailable' => 'נתיב דוח חשבון אינו זמין. ' . self::DELEGATION_HE,
				'invoice_summary_route_unavailable' => 'נתיב סיכום חשבונית אינו זמין. ' . self::DELEGATION_HE,
				'sales_report_route_unavailable' => 'נתיב דוח מכירות אינו זמין. ' . self::DELEGATION_HE,
				'receivables_report_route_unavailable' => 'נתיב חשבונות לקבלה אינו זמין. ' . self::DELEGATION_HE,
				'payables_report_route_unavailable' => 'נתיב חשבונות לתשלום אינו זמין. ' . self::DELEGATION_HE,
				'bill_summary_route_unavailable' => 'נתיב סיכום חשבון אינו זמין. ' . self::DELEGATION_HE,
				'product_stock_report_route_unavailable' => 'נתיב דוח מלאי מוצרים אינו זמין. ' . self::DELEGATION_HE,
				'monthly_cashflow_route_unavailable' => 'נתיב תזרים מזומנים אינו זמין. ' . self::DELEGATION_HE,
				'income_summary_route_unavailable' => 'נתיב סיכום הכנסות אינו זמין. ' . self::DELEGATION_HE,
				'expense_summary_route_unavailable' => 'נתיב סיכום הוצאות אינו זמין. ' . self::DELEGATION_HE,
				'income_vs_expense_summary_route_unavailable' => 'נתיב הכנסות לעומת הוצאות אינו זמין. ' . self::DELEGATION_HE,
				'tax_summary_unavailable' => 'נתיב סיכום מס אינו זמין. ' . self::DELEGATION_HE,
				'payroll_route_unavailable' => 'נתיב משכורת אינו זמין. ' . self::DELEGATION_HE,
				'leave_route_unavailable' => 'נתיב חופשה אינו זמין. ' . self::DELEGATION_HE,
				'monthly_attendance_route_unavailable' => 'נתיב נוכחות חודשית אינו זמין. ' . self::DELEGATION_HE,
				'lead_route_unavailable' => 'נתיב דוח ליד אינו זמין. ' . self::DELEGATION_HE,
				'deal_route_unavailable' => 'נתיב דוח עסקה אינו זמין. ' . self::DELEGATION_HE,
				'warehouse_report_route_unavailable' => 'נתיב דוח מחסן אינו זמין. ' . self::DELEGATION_HE,
				'daily_purchase_report_route_unavailable' => 'נתיב דוח רכישה יומי/חודשי אינו זמין. ' . self::DELEGATION_HE,
				'daily_pos_report_route_unavailable' => 'נתיב דוח נקודת מכירה יומי/חודשי אינו זמין. ' . self::DELEGATION_HE,
				'pos_vs_purchase_report_route_unavailable' => 'נתיב דוח נקודת מכירה מול רכישה אינו זמין. ' . self::DELEGATION_HE,
				'rpt_ledger_route_unavailable' => 'נתיב סיכום ספר החשבונות אינו זמין. ' . self::DELEGATION_HE,
				'rpt_balance_sheet_route_unavailable' => 'נתיב מאזן הבוחן אינו זמין. ' . self::DELEGATION_HE,
				'rpt_profit_loss_route_unavailable' => 'נתיב רווח והפסד אינו זמין. ' . self::DELEGATION_HE,
				'trial_balance_route_unavailable' => 'נתיב מאזן הבוחן אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'download_daily_purchase_unavailable' => 'La funzione di download per gli acquisti giornalieri non è disponibile. ' . self::DELEGATION_IT,
				'monthly_purchase_unavailable' => 'La rotta di acquisto mensile non è disponibile. ' . self::DELEGATION_IT,
				'filter_report_unavailable' => 'La rotta del report di filtro non è disponibile. ' . self::DELEGATION_IT,
				'daily_purchase_nav_unavailable' => 'La navigazione degli acquisti giornalieri non è disponibile. ' . self::DELEGATION_IT,
				'download_monthly_purchase_unavailable' => 'La funzione di download per gli acquisti mensili non è disponibile. ' . self::DELEGATION_IT,
				'account_statement_route_unavailable' => 'La rotta dell\'estratto conto non è disponibile. ' . self::DELEGATION_IT,
				'invoice_summary_route_unavailable' => 'La rotta del riepilogo fatture non è disponibile. ' . self::DELEGATION_IT,
				'sales_report_route_unavailable' => 'La rotta del report vendite non è disponibile. ' . self::DELEGATION_IT,
				'receivables_report_route_unavailable' => 'La rotta dei crediti non è disponibile. ' . self::DELEGATION_IT,
				'payables_report_route_unavailable' => 'La rotta dei debiti non è disponibile. ' . self::DELEGATION_IT,
				'bill_summary_route_unavailable' => 'La rotta del riepilogo fatture non è disponibile. ' . self::DELEGATION_IT,
				'product_stock_report_route_unavailable' => 'La rotta del report giacenze non è disponibile. ' . self::DELEGATION_IT,
				'monthly_cashflow_route_unavailable' => 'La rotta del flusso di cassa non è disponibile. ' . self::DELEGATION_IT,
				'income_summary_route_unavailable' => 'La rotta del riepilogo entrate non è disponibile. ' . self::DELEGATION_IT,
				'expense_summary_route_unavailable' => 'La rotta del riepilogo spese non è disponibile. ' . self::DELEGATION_IT,
				'income_vs_expense_summary_route_unavailable' => 'La rotta entrate vs spese non è disponibile. ' . self::DELEGATION_IT,
				'tax_summary_unavailable' => 'La rotta del riepilogo fiscale non è disponibile. ' . self::DELEGATION_IT,
				'payroll_route_unavailable' => 'La rotta della busta paga non è disponibile. ' . self::DELEGATION_IT,
				'leave_route_unavailable' => 'La rotta dei permessi non è disponibile. ' . self::DELEGATION_IT,
				'monthly_attendance_route_unavailable' => 'La rotta delle presenze mensili non è disponibile. ' . self::DELEGATION_IT,
				'lead_route_unavailable' => 'La rotta del report dei lead non è disponibile. ' . self::DELEGATION_IT,
				'deal_route_unavailable' => 'La rotta del report degli affari non è disponibile. ' . self::DELEGATION_IT,
				'warehouse_report_route_unavailable' => 'La rotta del report del magazzino non è disponibile. ' . self::DELEGATION_IT,
				'daily_purchase_report_route_unavailable' => 'La rotta del report giornaliero/mensile degli acquisti non è disponibile. ' . self::DELEGATION_IT,
				'daily_pos_report_route_unavailable' => 'La rotta del report giornaliero/mensile POS non è disponibile. ' . self::DELEGATION_IT,
				'pos_vs_purchase_report_route_unavailable' => 'La rotta del report POS vs Acquisti non è disponibile. ' . self::DELEGATION_IT,
				'rpt_ledger_route_unavailable' => 'Il percorso del riepilogo del libro mastro non è disponibile. ' . self::DELEGATION_IT,
				'rpt_balance_sheet_route_unavailable' => 'Il percorso dello stato patrimoniale non è disponibile. ' . self::DELEGATION_IT,
				'rpt_profit_loss_route_unavailable' => 'Il percorso del conto economico non è disponibile. ' . self::DELEGATION_IT,
				'trial_balance_route_unavailable' => 'Il percorso della bilancia di verifica non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'download_daily_purchase_unavailable' => '日次購入のダウンロード機能は利用できません。' . self::DELEGATION_JA,
				'monthly_purchase_unavailable' => '月次購入ルートは利用できません。' . self::DELEGATION_JA,
				'filter_report_unavailable' => 'フィルターレポートルートは利用できません。' . self::DELEGATION_JA,
				'daily_purchase_nav_unavailable' => '日次購入ナビゲーションは利用できません。' . self::DELEGATION_JA,
				'download_monthly_purchase_unavailable' => '月次購入のダウンロード機能は利用できません。' . self::DELEGATION_JA,
				'account_statement_route_unavailable' => '口座明細ルートは利用できません。' . self::DELEGATION_JA,
				'invoice_summary_route_unavailable' => '請求書サマリールートは利用できません。' . self::DELEGATION_JA,
				'sales_report_route_unavailable' => '販売報告ルートは利用できません。' . self::DELEGATION_JA,
				'receivables_report_route_unavailable' => '売掛金ルートは利用できません。' . self::DELEGATION_JA,
				'payables_report_route_unavailable' => '買掛金ルートは利用できません。' . self::DELEGATION_JA,
				'bill_summary_route_unavailable' => '請求書サマリールートは利用できません。' . self::DELEGATION_JA,
				'product_stock_report_route_unavailable' => '商品在庫報告ルートは利用できません。' . self::DELEGATION_JA,
				'monthly_cashflow_route_unavailable' => '資金流動ルートは利用できません。' . self::DELEGATION_JA,
				'income_summary_route_unavailable' => '収入サマリールートは利用できません。' . self::DELEGATION_JA,
				'expense_summary_route_unavailable' => '経費サマリールートは利用できません。' . self::DELEGATION_JA,
				'income_vs_expense_summary_route_unavailable' => '収入対経費ルートは利用できません。' . self::DELEGATION_JA,
				'tax_summary_unavailable' => '税務サマリールートは利用できません。' . self::DELEGATION_JA,
				'payroll_route_unavailable' => '給与ルートは利用できません。' . self::DELEGATION_JA,
				'leave_route_unavailable' => '休暇ルートは利用できません。' . self::DELEGATION_JA,
				'monthly_attendance_route_unavailable' => '月次勤怠ルートは利用できません。' . self::DELEGATION_JA,
				'lead_route_unavailable' => 'リードレポートルートは利用できません。' . self::DELEGATION_JA,
				'deal_route_unavailable' => '取引レポートルートは利用できません。' . self::DELEGATION_JA,
				'warehouse_report_route_unavailable' => '倉庫レポートルートは利用できません。' . self::DELEGATION_JA,
				'daily_purchase_report_route_unavailable' => '日次/月次購買レポートルートは利用できません。' . self::DELEGATION_JA,
				'daily_pos_report_route_unavailable' => 'POS日次/月次レポートルートは利用できません。' . self::DELEGATION_JA,
				'pos_vs_purchase_report_route_unavailable' => 'POS対購買レポートルートは利用できません。' . self::DELEGATION_JA,
				'rpt_ledger_route_unavailable' => '元帳サマリールートは利用できません。' . self::DELEGATION_JA,
				'rpt_balance_sheet_route_unavailable' => '貸借対照表ルートは利用できません。' . self::DELEGATION_JA,
				'rpt_profit_loss_route_unavailable' => '損益計算書ルートは利用できません。' . self::DELEGATION_JA,
				'trial_balance_route_unavailable' => '試算表ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'download_daily_purchase_unavailable' => 'Downloadfunctie voor dagelijkse aankopen is niet beschikbaar. ' . self::DELEGATION_NL,
				'monthly_purchase_unavailable' => 'Maandelijkse aankooproute is niet beschikbaar. ' . self::DELEGATION_NL,
				'filter_report_unavailable' => 'Filterrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'daily_purchase_nav_unavailable' => 'Dagelijkse aankoopnavigatie is niet beschikbaar. ' . self::DELEGATION_NL,
				'download_monthly_purchase_unavailable' => 'Downloadfunctie voor maandelijkse aankopen is niet beschikbaar. ' . self::DELEGATION_NL,
				'account_statement_route_unavailable' => 'Rekeningoverzichtroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'invoice_summary_route_unavailable' => 'Factuuroverzichtroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'sales_report_route_unavailable' => 'Verkooprapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'receivables_report_route_unavailable' => 'Debiteurenrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payables_report_route_unavailable' => 'Crediteurenrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'bill_summary_route_unavailable' => 'Factuuroverzichtroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'product_stock_report_route_unavailable' => 'Productvoorraadrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'monthly_cashflow_route_unavailable' => 'Kasstroomroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'income_summary_route_unavailable' => 'Inkomensoverzichtroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'expense_summary_route_unavailable' => 'Uitgavenoverzichtroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'income_vs_expense_summary_route_unavailable' => 'Inkomen vs uitgaven route is niet beschikbaar. ' . self::DELEGATION_NL,
				'tax_summary_unavailable' => 'Belastingoverzichtroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'payroll_route_unavailable' => 'Loonstrookroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'leave_route_unavailable' => 'Verlofroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'monthly_attendance_route_unavailable' => 'Route maandelijkse aanwezigheid is niet beschikbaar. ' . self::DELEGATION_NL,
				'lead_route_unavailable' => 'Leadrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'deal_route_unavailable' => 'Dealrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'warehouse_report_route_unavailable' => 'Magazijnrapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'daily_purchase_report_route_unavailable' => 'Dagelijkse/maandelijkse inkooprapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'daily_pos_report_route_unavailable' => 'Dagelijkse/maandelijkse POS-rapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'pos_vs_purchase_report_route_unavailable' => 'POS vs Inkooprapportroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'rpt_ledger_route_unavailable' => 'Grootboeksamenvattingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'rpt_balance_sheet_route_unavailable' => 'Balansroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'rpt_profit_loss_route_unavailable' => 'Winst- en verliesrekeningroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'trial_balance_route_unavailable' => 'Proefbalansroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'download_daily_purchase_unavailable' => 'Funkcja pobierania codziennych zakupów jest niedostępna. ' . self::DELEGATION_PL,
				'monthly_purchase_unavailable' => 'Miesięczna trasa zakupów jest niedostępna. ' . self::DELEGATION_PL,
				'filter_report_unavailable' => 'Trasa raportu filtrowania jest niedostępna. ' . self::DELEGATION_PL,
				'daily_purchase_nav_unavailable' => 'Nawigacja codziennych zakupów jest niedostępna. ' . self::DELEGATION_PL,
				'download_monthly_purchase_unavailable' => 'Funkcja pobierania miesięcznych zakupów jest niedostępna. ' . self::DELEGATION_PL,
				'account_statement_route_unavailable' => 'Trasa wyciągu z konta jest niedostępna. ' . self::DELEGATION_PL,
				'invoice_summary_route_unavailable' => 'Trasa podsumowania faktur jest niedostępna. ' . self::DELEGATION_PL,
				'sales_report_route_unavailable' => 'Trasa raportu sprzedaży jest niedostępna. ' . self::DELEGATION_PL,
				'receivables_report_route_unavailable' => 'Trasa raportu należności jest niedostępna. ' . self::DELEGATION_PL,
				'payables_report_route_unavailable' => 'Trasa raportu zobowiązań jest niedostępna. ' . self::DELEGATION_PL,
				'bill_summary_route_unavailable' => 'Trasa podsumowania faktur jest niedostępna. ' . self::DELEGATION_PL,
				'product_stock_report_route_unavailable' => 'Trasa raportu stanu magazynowego jest niedostępna. ' . self::DELEGATION_PL,
				'monthly_cashflow_route_unavailable' => 'Trasa przepływów pieniężnych jest niedostępna. ' . self::DELEGATION_PL,
				'income_summary_route_unavailable' => 'Trasa podsumowania przychodów jest niedostępna. ' . self::DELEGATION_PL,
				'expense_summary_route_unavailable' => 'Trasa podsumowania wydatków jest niedostępna. ' . self::DELEGATION_PL,
				'income_vs_expense_summary_route_unavailable' => 'Trasa przychody vs wydatki jest niedostępna. ' . self::DELEGATION_PL,
				'tax_summary_unavailable' => 'Trasa podsumowania podatkowego jest niedostępna. ' . self::DELEGATION_PL,
				'payroll_route_unavailable' => 'Trasa listy płac jest niedostępna. ' . self::DELEGATION_PL,
				'leave_route_unavailable' => 'Trasa urlopów jest niedostępna. ' . self::DELEGATION_PL,
				'monthly_attendance_route_unavailable' => 'Trasa miesięcznej frekwencji jest niedostępna. ' . self::DELEGATION_PL,
				'lead_route_unavailable' => 'Trasa raportu potencjalnych klientów jest niedostępna. ' . self::DELEGATION_PL,
				'deal_route_unavailable' => 'Trasa raportu transakcji jest niedostępna. ' . self::DELEGATION_PL,
				'warehouse_report_route_unavailable' => 'Trasa raportu magazynowego jest niedostępna. ' . self::DELEGATION_PL,
				'daily_purchase_report_route_unavailable' => 'Trasa raportu dziennego/miesięcznego zakupów jest niedostępna. ' . self::DELEGATION_PL,
				'daily_pos_report_route_unavailable' => 'Trasa raportu dziennego/miesięcznego POS jest niedostępna. ' . self::DELEGATION_PL,
				'pos_vs_purchase_report_route_unavailable' => 'Trasa raportu POS vs Zakupy jest niedostępna. ' . self::DELEGATION_PL,
				'rpt_ledger_route_unavailable' => 'Trasa podsumowania księgi głównej jest niedostępna. ' . self::DELEGATION_PL,
				'rpt_balance_sheet_route_unavailable' => 'Trasa bilansu jest niedostępna. ' . self::DELEGATION_PL,
				'rpt_profit_loss_route_unavailable' => 'Trasa rachunku zysków i strat jest niedostępna. ' . self::DELEGATION_PL,
				'trial_balance_route_unavailable' => 'Trasa bilansu próbnego jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'download_daily_purchase_unavailable' => 'A função de download para compras diárias não está disponível. ' . self::DELEGATION_PT,
				'monthly_purchase_unavailable' => 'A rota de compra mensal não está disponível. ' . self::DELEGATION_PT,
				'filter_report_unavailable' => 'A rota do relatório de filtro não está disponível. ' . self::DELEGATION_PT,
				'daily_purchase_nav_unavailable' => 'A navegação de compras diárias não está disponível. ' . self::DELEGATION_PT,
				'download_monthly_purchase_unavailable' => 'A função de download para compras mensais não está disponível. ' . self::DELEGATION_PT,
				'account_statement_route_unavailable' => 'A rota do extrato da conta não está disponível. ' . self::DELEGATION_PT,
				'invoice_summary_route_unavailable' => 'A rota do resumo da fatura não está disponível. ' . self::DELEGATION_PT,
				'sales_report_route_unavailable' => 'A rota do relatório de vendas não está disponível. ' . self::DELEGATION_PT,
				'receivables_report_route_unavailable' => 'A rota de contas a receber não está disponível. ' . self::DELEGATION_PT,
				'payables_report_route_unavailable' => 'A rota de contas a pagar não está disponível. ' . self::DELEGATION_PT,
				'bill_summary_route_unavailable' => 'A rota do resumo da conta não está disponível. ' . self::DELEGATION_PT,
				'product_stock_report_route_unavailable' => 'A rota do relatório de estoque de produtos não está disponível. ' . self::DELEGATION_PT,
				'monthly_cashflow_route_unavailable' => 'A rota de fluxo de caixa não está disponível. ' . self::DELEGATION_PT,
				'income_summary_route_unavailable' => 'A rota do resumo de receitas não está disponível. ' . self::DELEGATION_PT,
				'expense_summary_route_unavailable' => 'A rota do resumo de despesas não está disponível. ' . self::DELEGATION_PT,
				'income_vs_expense_summary_route_unavailable' => 'A rota de Receitas vs Despesas não está disponível. ' . self::DELEGATION_PT,
				'tax_summary_unavailable' => 'A rota do resumo de impostos não está disponível. ' . self::DELEGATION_PT,
				'payroll_route_unavailable' => 'A rota da folha de pagamento não está disponível. ' . self::DELEGATION_PT,
				'leave_route_unavailable' => 'A rota de licenças não está disponível. ' . self::DELEGATION_PT,
				'monthly_attendance_route_unavailable' => 'A rota de assiduidade mensal não está disponível. ' . self::DELEGATION_PT,
				'lead_route_unavailable' => 'A rota do relatório de leads não está disponível. ' . self::DELEGATION_PT,
				'deal_route_unavailable' => 'A rota do relatório de negócios não está disponível. ' . self::DELEGATION_PT,
				'warehouse_report_route_unavailable' => 'A rota do relatório de armazém não está disponível. ' . self::DELEGATION_PT,
				'daily_purchase_report_route_unavailable' => 'A rota do relatório diário/mensal de compras não está disponível. ' . self::DELEGATION_PT,
				'daily_pos_report_route_unavailable' => 'A rota do relatório diário/mensal de POS não está disponível. ' . self::DELEGATION_PT,
				'pos_vs_purchase_report_route_unavailable' => 'A rota do relatório POS vs Compras não está disponível. ' . self::DELEGATION_PT,
				'rpt_ledger_route_unavailable' => 'A rota do Resumo do Razão não está disponível. ' . self::DELEGATION_PT,
				'rpt_balance_sheet_route_unavailable' => 'A rota do Balanço Patrimonial não está disponível. ' . self::DELEGATION_PT,
				'rpt_profit_loss_route_unavailable' => 'A rota de Lucros e Perdas não está disponível. ' . self::DELEGATION_PT,
				'trial_balance_route_unavailable' => 'A rota do Balancete não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'download_daily_purchase_unavailable' => 'A função de download para compras diárias não está disponível. ' . self::DELEGATION_PTBR,
				'monthly_purchase_unavailable' => 'A rota de compra mensal não está disponível. ' . self::DELEGATION_PTBR,
				'filter_report_unavailable' => 'A rota do relatório de filtro não está disponível. ' . self::DELEGATION_PTBR,
				'daily_purchase_nav_unavailable' => 'A navegação de compras diárias não está disponível. ' . self::DELEGATION_PTBR,
				'download_monthly_purchase_unavailable' => 'A função de download para compras mensais não está disponível. ' . self::DELEGATION_PTBR,
				'account_statement_route_unavailable' => 'A rota do extrato da conta não está disponível. ' . self::DELEGATION_PTBR,
				'invoice_summary_route_unavailable' => 'A rota do resumo da fatura não está disponível. ' . self::DELEGATION_PTBR,
				'sales_report_route_unavailable' => 'A rota do relatório de vendas não está disponível. ' . self::DELEGATION_PTBR,
				'receivables_report_route_unavailable' => 'A rota de contas a receber não está disponível. ' . self::DELEGATION_PTBR,
				'payables_report_route_unavailable' => 'A rota de contas a pagar não está disponível. ' . self::DELEGATION_PTBR,
				'bill_summary_route_unavailable' => 'A rota do resumo da conta não está disponível. ' . self::DELEGATION_PTBR,
				'product_stock_report_route_unavailable' => 'A rota do relatório de estoque de produtos não está disponível. ' . self::DELEGATION_PTBR,
				'monthly_cashflow_route_unavailable' => 'A rota de fluxo de caixa não está disponível. ' . self::DELEGATION_PTBR,
				'income_summary_route_unavailable' => 'A rota do resumo de receitas não está disponível. ' . self::DELEGATION_PTBR,
				'expense_summary_route_unavailable' => 'A rota do resumo de despesas não está disponível. ' . self::DELEGATION_PTBR,
				'income_vs_expense_summary_route_unavailable' => 'A rota de Receitas vs Despesas não está disponível. ' . self::DELEGATION_PTBR,
				'tax_summary_unavailable' => 'A rota do resumo de impostos não está disponível. ' . self::DELEGATION_PTBR,
				'payroll_route_unavailable' => 'A rota da folha de pagamento não está disponível. ' . self::DELEGATION_PTBR,
				'leave_route_unavailable' => 'A rota de licenças não está disponível. ' . self::DELEGATION_PTBR,
				'monthly_attendance_route_unavailable' => 'A rota de frequência mensal não está disponível. ' . self::DELEGATION_PTBR,
				'lead_route_unavailable' => 'A rota do relatório de leads não está disponível. ' . self::DELEGATION_PTBR,
				'deal_route_unavailable' => 'A rota do relatório de negócios não está disponível. ' . self::DELEGATION_PTBR,
				'warehouse_report_route_unavailable' => 'A rota do relatório de estoque não está disponível. ' . self::DELEGATION_PTBR,
				'daily_purchase_report_route_unavailable' => 'A rota do relatório diário/mensal de compras não está disponível. ' . self::DELEGATION_PTBR,
				'daily_pos_report_route_unavailable' => 'A rota do relatório diário/mensal de PDV não está disponível. ' . self::DELEGATION_PTBR,
				'pos_vs_purchase_report_route_unavailable' => 'A rota do relatório PDV vs Compras não está disponível. ' . self::DELEGATION_PTBR,
				'rpt_ledger_route_unavailable' => 'A rota do Resumo do Razão não está disponível. ' . self::DELEGATION_PTBR,
				'rpt_balance_sheet_route_unavailable' => 'A rota do Balanço Patrimonial não está disponível. ' . self::DELEGATION_PTBR,
				'rpt_profit_loss_route_unavailable' => 'A rota de Lucros e Perdas não está disponível. ' . self::DELEGATION_PTBR,
				'trial_balance_route_unavailable' => 'A rota do Balancete não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'download_daily_purchase_unavailable' => 'Функция загрузки ежедневных покупок недоступна. ' . self::DELEGATION_RU,
				'monthly_purchase_unavailable' => 'Маршрут ежемесячных покупок недоступен. ' . self::DELEGATION_RU,
				'filter_report_unavailable' => 'Маршрут отчета о фильтрации недоступен. ' . self::DELEGATION_RU,
				'daily_purchase_nav_unavailable' => 'Навигация по ежедневным покупкам недоступна. ' . self::DELEGATION_RU,
				'download_monthly_purchase_unavailable' => 'Функция загрузки ежемесячных покупок недоступна. ' . self::DELEGATION_RU,
				'account_statement_route_unavailable' => 'Маршрут выписки по счету недоступен. ' . self::DELEGATION_RU,
				'invoice_summary_route_unavailable' => 'Маршрут сводки по счетам недоступен. ' . self::DELEGATION_RU,
				'sales_report_route_unavailable' => 'Маршрут отчета о продажах недоступен. ' . self::DELEGATION_RU,
				'receivables_report_route_unavailable' => 'Маршрут дебиторской задолженности недоступен. ' . self::DELEGATION_RU,
				'payables_report_route_unavailable' => 'Маршрут кредиторской задолженности недоступен. ' . self::DELEGATION_RU,
				'bill_summary_route_unavailable' => 'Маршрут сводки по счетам к оплате недоступен. ' . self::DELEGATION_RU,
				'product_stock_report_route_unavailable' => 'Маршрут отчета о товарных запасах недоступен. ' . self::DELEGATION_RU,
				'monthly_cashflow_route_unavailable' => 'Маршрут движения денежных средств недоступен. ' . self::DELEGATION_RU,
				'income_summary_route_unavailable' => 'Маршрут сводки доходов недоступен. ' . self::DELEGATION_RU,
				'expense_summary_route_unavailable' => 'Маршрут сводки расходов недоступен. ' . self::DELEGATION_RU,
				'income_vs_expense_summary_route_unavailable' => 'Маршрут отчета "Доходы против расходов" недоступен. ' . self::DELEGATION_RU,
				'tax_summary_unavailable' => 'Маршрут налоговой сводки недоступен. ' . self::DELEGATION_RU,
				'payroll_route_unavailable' => 'Маршрут расчета заработной платы недоступен. ' . self::DELEGATION_RU,
				'leave_route_unavailable' => 'Маршрут отпусков недоступен. ' . self::DELEGATION_RU,
				'monthly_attendance_route_unavailable' => 'Маршрут ежемесячной посещаемости недоступен. ' . self::DELEGATION_RU,
				'lead_route_unavailable' => 'Маршрут отчета по лидам недоступен. ' . self::DELEGATION_RU,
				'deal_route_unavailable' => 'Маршрут отчета по сделкам недоступен. ' . self::DELEGATION_RU,
				'warehouse_report_route_unavailable' => 'Маршрут отчета по складу недоступен. ' . self::DELEGATION_RU,
				'daily_purchase_report_route_unavailable' => 'Маршрут ежедневного/ежемесячного отчета по закупкам недоступен. ' . self::DELEGATION_RU,
				'daily_pos_report_route_unavailable' => 'Маршрут ежедневного/ежемесячного отчета по POS недоступен. ' . self::DELEGATION_RU,
				'pos_vs_purchase_report_route_unavailable' => 'Маршрут отчета POS vs Закупки недоступен. ' . self::DELEGATION_RU,
				'rpt_ledger_route_unavailable' => 'Маршрут сводной книги недоступен. ' . self::DELEGATION_RU,
				'rpt_balance_sheet_route_unavailable' => 'Маршрут балансового отчета недоступен. ' . self::DELEGATION_RU,
				'rpt_profit_loss_route_unavailable' => 'Маршрут отчета о прибылях и убытках недоступен. ' . self::DELEGATION_RU,
				'trial_balance_route_unavailable' => 'Маршрут пробного баланса недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'download_daily_purchase_unavailable' => 'Günlük satın alımlar için indirme işlevi kullanılamıyor. ' . self::DELEGATION_TR,
				'monthly_purchase_unavailable' => 'Aylık satın alma rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'filter_report_unavailable' => 'Filtre raporu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'daily_purchase_nav_unavailable' => 'Günlük satın alma gezintisi kullanılamıyor. ' . self::DELEGATION_TR,
				'download_monthly_purchase_unavailable' => 'Aylık satın alımlar için indirme işlevi kullanılamıyor. ' . self::DELEGATION_TR,
				'account_statement_route_unavailable' => 'Hesap özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'invoice_summary_route_unavailable' => 'Fatura özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'sales_report_route_unavailable' => 'Satış raporu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'receivables_report_route_unavailable' => 'Alacaklar rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payables_report_route_unavailable' => 'Borçlar rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'bill_summary_route_unavailable' => 'Fatura özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'product_stock_report_route_unavailable' => 'Ürün stok raporu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'monthly_cashflow_route_unavailable' => 'Nakit akışı rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'income_summary_route_unavailable' => 'Gelir özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'expense_summary_route_unavailable' => 'Gider özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'income_vs_expense_summary_route_unavailable' => 'Gelir VS Gider rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'tax_summary_unavailable' => 'Vergi özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'payroll_route_unavailable' => 'Maaş bordrosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'leave_route_unavailable' => 'İzin rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'monthly_attendance_route_unavailable' => 'Aylık devam rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'lead_route_unavailable' => 'Müşteri adayı rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'deal_route_unavailable' => 'Anlaşma rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'warehouse_report_route_unavailable' => 'Depo rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'daily_purchase_report_route_unavailable' => 'Günlük/aylık satın alma rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'daily_pos_report_route_unavailable' => 'Günlük/aylık POS rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pos_vs_purchase_report_route_unavailable' => 'POS vs Satın alma rapor rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'rpt_ledger_route_unavailable' => 'Defter Özeti rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'rpt_balance_sheet_route_unavailable' => 'Bilanço rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'rpt_profit_loss_route_unavailable' => 'Kar ve Zarar rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'trial_balance_route_unavailable' => 'Deneme Bilançosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'download_daily_purchase_unavailable' => '每日采购的下载功能不可用。' . self::DELEGATION_ZH,
				'monthly_purchase_unavailable' => '月度采购路由不可用。' . self::DELEGATION_ZH,
				'filter_report_unavailable' => '筛选报告路由不可用。' . self::DELEGATION_ZH,
				'daily_purchase_nav_unavailable' => '每日采购导航不可用。' . self::DELEGATION_ZH,
				'download_monthly_purchase_unavailable' => '月度采购的下载功能不可用。' . self::DELEGATION_ZH,
				'account_statement_route_unavailable' => '账户对账单路由不可用。' . self::DELEGATION_ZH,
				'invoice_summary_route_unavailable' => '发票摘要路由不可用。' . self::DELEGATION_ZH,
				'sales_report_route_unavailable' => '销售报告路由不可用。' . self::DELEGATION_ZH,
				'receivables_report_route_unavailable' => '应收报告路由不可用。' . self::DELEGATION_ZH,
				'payables_report_route_unavailable' => '应付报告路由不可用。' . self::DELEGATION_ZH,
				'bill_summary_route_unavailable' => '账单摘要路由不可用。' . self::DELEGATION_ZH,
				'product_stock_report_route_unavailable' => '产品库存报告路由不可用。' . self::DELEGATION_ZH,
				'monthly_cashflow_route_unavailable' => '现金流路由不可用。' . self::DELEGATION_ZH,
				'income_summary_route_unavailable' => '收入摘要路由不可用。' . self::DELEGATION_ZH,
				'expense_summary_route_unavailable' => '支出摘要路由不可用。' . self::DELEGATION_ZH,
				'income_vs_expense_summary_route_unavailable' => '收入与支出对比路由不可用。' . self::DELEGATION_ZH,
				'tax_summary_unavailable' => '税务摘要路由不可用。' . self::DELEGATION_ZH,
				'payroll_route_unavailable' => '工资单路由不可用。' . self::DELEGATION_ZH,
				'leave_route_unavailable' => '休假路由不可用。' . self::DELEGATION_ZH,
				'monthly_attendance_route_unavailable' => '月度考勤路由不可用。' . self::DELEGATION_ZH,
				'lead_route_unavailable' => '潜在客户报告路由不可用。' . self::DELEGATION_ZH,
				'deal_route_unavailable' => '交易报告路由不可用。' . self::DELEGATION_ZH,
				'warehouse_report_route_unavailable' => '仓库报告路由不可用。' . self::DELEGATION_ZH,
				'daily_purchase_report_route_unavailable' => '采购每日/月度报告路由不可用。' . self::DELEGATION_ZH,
				'daily_pos_report_route_unavailable' => 'POS每日/月度报告路由不可用。' . self::DELEGATION_ZH,
				'pos_vs_purchase_report_route_unavailable' => 'POS VS 采购报告路由不可用。' . self::DELEGATION_ZH,
				'rpt_ledger_route_unavailable' => '分类账汇总路由不可用。' . self::DELEGATION_ZH,
				'rpt_balance_sheet_route_unavailable' => '资产负债表路由不可用。' . self::DELEGATION_ZH,
				'rpt_profit_loss_route_unavailable' => '损益表路由不可用。' . self::DELEGATION_ZH,
				'trial_balance_route_unavailable' => '试算表路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::RSG => [
			'ar' => ['resignation_index_route_unavailable' => 'مسار فهرس الاستقالات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['resignation_index_route_unavailable' => 'Fratrædelsesindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['resignation_index_route_unavailable' => 'Rücktrittsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['resignation_index_route_unavailable' => 'Resignation index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['resignation_index_route_unavailable' => 'La ruta del índice de renuncias no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['resignation_index_route_unavailable' => 'La route de l\'index de démission est indisponible. ' . self::DELEGATION_FR],
			'he' => ['resignation_index_route_unavailable' => 'נתיב אינדקס התפטרות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['resignation_index_route_unavailable' => 'La rotta dell\'indice delle dimissioni non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['resignation_index_route_unavailable' => '退職インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['resignation_index_route_unavailable' => 'Ontslagindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['resignation_index_route_unavailable' => 'Trasa indeksu rezygnacji jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['resignation_index_route_unavailable' => 'A rota do índice de demissões não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['resignation_index_route_unavailable' => 'A rota do índice de demissões não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['resignation_index_route_unavailable' => 'Маршрут индекса увольнений недоступен. ' . self::DELEGATION_RU],
			'tr' => ['resignation_index_route_unavailable' => 'İstifa indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['resignation_index_route_unavailable' => '辞职索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::RVN => [
			'ar' => ['revenue_index_route_unavailable' => 'مسار الإيرادات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['revenue_index_route_unavailable' => 'Indtægtsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['revenue_index_route_unavailable' => 'Einnahmen-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['revenue_index_route_unavailable' => 'Revenue route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['revenue_index_route_unavailable' => 'La ruta de ingresos no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['revenue_index_route_unavailable' => 'La route des revenus est indisponible. ' . self::DELEGATION_FR],
			'he' => ['revenue_index_route_unavailable' => 'נתיב הכנסות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['revenue_index_route_unavailable' => 'La rotta dei ricavi non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['revenue_index_route_unavailable' => '収益ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['revenue_index_route_unavailable' => 'Inkomstenroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['revenue_index_route_unavailable' => 'Trasa przychodów jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['revenue_index_route_unavailable' => 'A rota de receitas não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['revenue_index_route_unavailable' => 'A rota de receitas não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['revenue_index_route_unavailable' => 'Маршрут доходов недоступен. ' . self::DELEGATION_RU],
			'tr' => ['revenue_index_route_unavailable' => 'Gelir rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['revenue_index_route_unavailable' => '收入路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::S_SLR => [
			'ar' => [
				'set_salary_index_route_unavailable' => 'مسار تعيين الراتب غير متاح. ' . self::DELEGATION_AR,
				'salary_update_route_unavailable' => 'مسار تحديث الراتب غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'set_salary_index_route_unavailable' => 'Indstil løn rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'salary_update_route_unavailable' => 'Lønopdateringsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'set_salary_index_route_unavailable' => 'Gehaltseinstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'salary_update_route_unavailable' => 'Gehaltsaktualisierungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'set_salary_index_route_unavailable' => 'Set salary route is unavailable. ' . self::DELEGATION_EN,
				'salary_update_route_unavailable' => 'Salary update route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'set_salary_index_route_unavailable' => 'La ruta de establecimiento de salario no está disponible. ' . self::DELEGATION_ES,
				'salary_update_route_unavailable' => 'La ruta de actualización de salario no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'set_salary_index_route_unavailable' => 'La route de définition du salaire est indisponible. ' . self::DELEGATION_FR,
				'salary_update_route_unavailable' => 'La route de mise à jour du salaire est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'set_salary_index_route_unavailable' => 'נתיב הגדרת משכורת אינו זמין. ' . self::DELEGATION_HE,
				'salary_update_route_unavailable' => 'נתיב עדכון משכורת אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'set_salary_index_route_unavailable' => 'La rotta di impostazione dello stipendio non è disponibile. ' . self::DELEGATION_IT,
				'salary_update_route_unavailable' => 'La rotta di aggiornamento dello stipendio non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'set_salary_index_route_unavailable' => '給与設定ルートは利用できません。' . self::DELEGATION_JA,
				'salary_update_route_unavailable' => '給与更新ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'set_salary_index_route_unavailable' => 'Salarisinstellingsroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'salary_update_route_unavailable' => 'Salarisupdateroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'set_salary_index_route_unavailable' => 'Trasa ustawiania wynagrodzenia jest niedostępna. ' . self::DELEGATION_PL,
				'salary_update_route_unavailable' => 'Trasa aktualizacji wynagrodzenia jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'set_salary_index_route_unavailable' => 'A rota de definição de salário não está disponível. ' . self::DELEGATION_PT,
				'salary_update_route_unavailable' => 'A rota de atualização de salário não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'set_salary_index_route_unavailable' => 'A rota de definição de salário não está disponível. ' . self::DELEGATION_PTBR,
				'salary_update_route_unavailable' => 'A rota de atualização de salário não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'set_salary_index_route_unavailable' => 'Маршрут установки зарплаты недоступен. ' . self::DELEGATION_RU,
				'salary_update_route_unavailable' => 'Маршрут обновления зарплаты недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'set_salary_index_route_unavailable' => 'Maaş ayarlama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'salary_update_route_unavailable' => 'Maaş güncelleme rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'set_salary_index_route_unavailable' => '设置薪资路由不可用。' . self::DELEGATION_ZH,
				'salary_update_route_unavailable' => '薪资更新路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::SET => [
			'ar' => ['system_settings_route_unavailable' => 'مسار إعدادات النظام غير متاح. ' . self::DELEGATION_AR],
			'da' => ['system_settings_route_unavailable' => 'Systemindstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['system_settings_route_unavailable' => 'Systemeinstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['system_settings_route_unavailable' => 'System Settings route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['system_settings_route_unavailable' => 'La ruta de configuración del sistema no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['system_settings_route_unavailable' => 'La route des paramètres système n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['system_settings_route_unavailable' => 'נתיב הגדרות מערכת אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['system_settings_route_unavailable' => 'La rotta delle impostazioni di sistema non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['system_settings_route_unavailable' => 'システム設定ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['system_settings_route_unavailable' => 'Systeeminstellingenroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['system_settings_route_unavailable' => 'Trasa ustawień systemowych jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['system_settings_route_unavailable' => 'A rota de configurações do sistema não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['system_settings_route_unavailable' => 'A rota de configurações do sistema não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['system_settings_route_unavailable' => 'Маршрут системных настроек недоступен. ' . self::DELEGATION_RU],
			'tr' => ['system_settings_route_unavailable' => 'Sistem Ayarları rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['system_settings_route_unavailable' => '系统设置路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::SPT => [
			'ar' => [
				'support_system_index_route_unavailable' => 'مسار نظام الدعم غير متاح. ' . self::DELEGATION_AR,
				'spt_index_route_unavailable' => 'مسار الدعم غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'support_system_index_route_unavailable' => 'Supportsystemrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'spt_index_route_unavailable' => 'Supportrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'support_system_index_route_unavailable' => 'Supportsystem-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'spt_index_route_unavailable' => 'Support-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'support_system_index_route_unavailable' => 'Support System route is unavailable. ' . self::DELEGATION_EN,
				'spt_index_route_unavailable' => 'Support route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'support_system_index_route_unavailable' => 'La ruta del sistema de soporte no está disponible. ' . self::DELEGATION_ES,
				'spt_index_route_unavailable' => 'La ruta de soporte no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'support_system_index_route_unavailable' => 'La route du système de support n\'est pas disponible. ' . self::DELEGATION_FR,
				'spt_index_route_unavailable' => 'La route du support n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'support_system_index_route_unavailable' => 'נתיב מערכת תמיכה אינו זמין. ' . self::DELEGATION_HE,
				'spt_index_route_unavailable' => 'נתיב תמיכה אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'support_system_index_route_unavailable' => 'La rotta del sistema di supporto non è disponibile. ' . self::DELEGATION_IT,
				'spt_index_route_unavailable' => 'La rotta del supporto non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'support_system_index_route_unavailable' => 'サポートシステムルートは利用できません。' . self::DELEGATION_JA,
				'spt_index_route_unavailable' => 'サポートルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'support_system_index_route_unavailable' => 'Ondersteuningssysteemroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'spt_index_route_unavailable' => 'Ondersteuningsroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'support_system_index_route_unavailable' => 'Trasa systemu wsparcia jest niedostępna. ' . self::DELEGATION_PL,
				'spt_index_route_unavailable' => 'Trasa wsparcia jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'support_system_index_route_unavailable' => 'A rota do sistema de suporte não está disponível. ' . self::DELEGATION_PT,
				'spt_index_route_unavailable' => 'A rota de suporte não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'support_system_index_route_unavailable' => 'A rota do sistema de suporte não está disponível. ' . self::DELEGATION_PTBR,
				'spt_index_route_unavailable' => 'A rota de suporte não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'support_system_index_route_unavailable' => 'Маршрут системы поддержки недоступен. ' . self::DELEGATION_RU,
				'spt_index_route_unavailable' => 'Маршрут поддержки недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'support_system_index_route_unavailable' => 'Destek Sistemi rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'spt_index_route_unavailable' => 'Destek rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'support_system_index_route_unavailable' => '支持系统路由不可用。' . self::DELEGATION_ZH,
				'spt_index_route_unavailable' => '支持路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::STG => [
			'ar' => [
				'print_settings_route_unavailable' => 'مسار إعدادات الطباعة غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'print_settings_route_unavailable' => 'Udskriftsindstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'print_settings_route_unavailable' => 'Druckeinstellungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'print_settings_route_unavailable' => 'Print Settings route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'print_settings_route_unavailable' => 'La ruta de Configuración de impresión no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'print_settings_route_unavailable' => 'La route des paramètres d\'impression n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'print_settings_route_unavailable' => 'נתיב הגדרות ההדפסה אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'print_settings_route_unavailable' => 'Il percorso delle impostazioni di stampa non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'print_settings_route_unavailable' => '印刷設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'print_settings_route_unavailable' => 'Afdrukinstellingenroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'print_settings_route_unavailable' => 'Trasa ustawień drukowania jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'print_settings_route_unavailable' => 'A rota de Configurações de Impressão não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'print_settings_route_unavailable' => 'A rota de Configurações de Impressão não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'print_settings_route_unavailable' => 'Маршрут настроек печати недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'print_settings_route_unavailable' => 'Yazdırma Ayarları rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'print_settings_route_unavailable' => '打印设置路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::STR_DD => [
			'ar' => [
				'saturation_deduction_store_route_unavailable' => 'مسار تخزين استقطاع التشبع غير متاح. ' . self::DELEGATION_AR,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_AR,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_AR,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_AR
			],
			'da' => [
				'saturation_deduction_store_route_unavailable' => 'Mætningsfradrags lagrings rute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_DA,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_DA,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_DA
			],
			'de' => [
				'saturation_deduction_store_route_unavailable' => 'Sättigungsabzugs-Speicherroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_DE,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_DE,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_DE
			],
			'en' => [
				'saturation_deduction_store_route_unavailable' => 'Saturation deduction store route is unavailable. ' . self::DELEGATION_EN,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_EN,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_EN,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'saturation_deduction_store_route_unavailable' => 'La ruta de almacenamiento de deducción por saturación no está disponible. ' . self::DELEGATION_ES,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_ES,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_ES,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_ES
			],
			'fr' => [
				'saturation_deduction_store_route_unavailable' => 'La route de stockage des déductions de saturation est indisponible. ' . self::DELEGATION_FR,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_FR,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_FR,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_FR
			],
			'he' => [
				'saturation_deduction_store_route_unavailable' => 'נתיב אחסון ניכוי רוויה אינו זמין. ' . self::DELEGATION_HE,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_HE,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_HE,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_HE
			],
			'it' => [
				'saturation_deduction_store_route_unavailable' => 'La rotta di memorizzazione della deduzione di saturazione non è disponibile. ' . self::DELEGATION_IT,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_IT,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_IT,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_IT
			],
			'ja' => [
				'saturation_deduction_store_route_unavailable' => '飽和控除保存ルートは利用できません。' . self::DELEGATION_JA,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_JA,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_JA,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_JA
			],
			'nl' => [
				'saturation_deduction_store_route_unavailable' => 'Verzadigingsaftrek opslagroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_NL,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_NL,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_NL
			],
			'pl' => [
				'saturation_deduction_store_route_unavailable' => 'Trasa przechowywania odliczeń nasycenia jest niedostępna. ' . self::DELEGATION_PL,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_PL,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_PL,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_PL
			],
			'pt' => [
				'saturation_deduction_store_route_unavailable' => 'A rota de armazenamento de dedução de saturação não está disponível. ' . self::DELEGATION_PT,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_PT,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_PT,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'saturation_deduction_store_route_unavailable' => 'A rota de armazenamento de dedução de saturação não está disponível. ' . self::DELEGATION_PTBR,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_PTBR,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_PTBR,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'saturation_deduction_store_route_unavailable' => 'Маршрут хранения вычета насыщения недоступен. ' . self::DELEGATION_RU,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_RU,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_RU,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_RU
			],
			'tr' => [
				'saturation_deduction_store_route_unavailable' => 'Doygunluk kesintisi depolama rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_TR,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_TR,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_TR
			],
			'zh' => [
				'saturation_deduction_store_route_unavailable' => '饱和扣除存储路由不可用。' . self::DELEGATION_ZH,
				'saturation_deduction_create_route_unavailable' => 'Saturation deduction create route is unavailable. ' . self::DELEGATION_ZH,
				'saturation_deduction_edit_route_unavailable' => 'Saturation deduction edit route is unavailable. ' . self::DELEGATION_ZH,
				'saturation_deduction_destroy_route_unavailable' => 'Saturation deduction destroy route is unavailable. ' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::SYS => [
			'ar' => [
				'settings_index_route_unavailable' => 'مسار الإعدادات غير متاح. ' . self::DELEGATION_AR,
				'system_settings_route_unavailable' => 'مسار إعدادات النظام غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'settings_index_route_unavailable' => 'Indstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'system_settings_route_unavailable' => 'Systemindstillingsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'settings_index_route_unavailable' => 'Einstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'system_settings_route_unavailable' => 'Systemeinstellungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'settings_index_route_unavailable' => 'Settings route is unavailable. ' . self::DELEGATION_EN,
				'system_settings_route_unavailable' => 'System settings route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'settings_index_route_unavailable' => 'La ruta de configuración no está disponible. ' . self::DELEGATION_ES,
				'system_settings_route_unavailable' => 'La ruta de configuración del sistema no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'settings_index_route_unavailable' => 'La route des paramètres n\'est pas disponible. ' . self::DELEGATION_FR,
				'system_settings_route_unavailable' => 'La route des paramètres système n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'settings_index_route_unavailable' => 'נתיב הגדרות אינו זמין. ' . self::DELEGATION_HE,
				'system_settings_route_unavailable' => 'נתיב הגדרות המערכת אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'settings_index_route_unavailable' => 'La rotta delle impostazioni non è disponibile. ' . self::DELEGATION_IT,
				'system_settings_route_unavailable' => 'La rotta delle impostazioni di sistema non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'settings_index_route_unavailable' => '設定ルートは利用できません。' . self::DELEGATION_JA,
				'system_settings_route_unavailable' => 'システム設定ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'settings_index_route_unavailable' => 'Instellingenroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'system_settings_route_unavailable' => 'Systeeminstellingenroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'settings_index_route_unavailable' => 'Trasa ustawień jest niedostępna. ' . self::DELEGATION_PL,
				'system_settings_route_unavailable' => 'Trasa ustawień systemowych jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'settings_index_route_unavailable' => 'A rota de configurações não está disponível. ' . self::DELEGATION_PT,
				'system_settings_route_unavailable' => 'A rota de configurações do sistema não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'settings_index_route_unavailable' => 'A rota de configurações não está disponível. ' . self::DELEGATION_PTBR,
				'system_settings_route_unavailable' => 'A rota de configurações do sistema não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'settings_index_route_unavailable' => 'Маршрут настроек недоступен. ' . self::DELEGATION_RU,
				'system_settings_route_unavailable' => 'Маршрут системных настроек недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'settings_index_route_unavailable' => 'Ayarlar rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'system_settings_route_unavailable' => 'Sistem ayarları rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'settings_index_route_unavailable' => '设置路由不可用。' . self::DELEGATION_ZH,
				'system_settings_route_unavailable' => '系统设置路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::TNG => [
			'ar' => [
				'training_index_route_unavailable' => 'مسار قائمة التدريب غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'training_index_route_unavailable' => 'Træningslistrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'training_index_route_unavailable' => 'Schulungslistenroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'training_index_route_unavailable' => 'Training list route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'training_index_route_unavailable' => 'La ruta de la lista de formación no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'training_index_route_unavailable' => 'La route de la liste de formation est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'training_index_route_unavailable' => 'נתיב רשימת הדרכה אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'training_index_route_unavailable' => 'La rotta dell\'elenco di formazione non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'training_index_route_unavailable' => 'トレーニングリストルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'training_index_route_unavailable' => 'Trainingslijstroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'training_index_route_unavailable' => 'Trasa listy szkoleń jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'training_index_route_unavailable' => 'A rota da lista de formação não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'training_index_route_unavailable' => 'A rota da lista de treinamento não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'training_index_route_unavailable' => 'Маршрут списка обучения недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'training_index_route_unavailable' => 'Eğitim listesi rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'training_index_route_unavailable' => '培训列表路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::TNR => [
			'ar' => [
				'trainer_index_route_unavailable' => 'مسار فهرس المدرب غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'trainer_index_route_unavailable' => 'Trænerindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'trainer_index_route_unavailable' => 'Trainerindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'trainer_index_route_unavailable' => 'Trainer index route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'trainer_index_route_unavailable' => 'La ruta del índice de entrenadores no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'trainer_index_route_unavailable' => 'La route de l\'index des formateurs est indisponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'trainer_index_route_unavailable' => 'נתיב אינדקס מאמנים אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'trainer_index_route_unavailable' => 'La rotta dell\'indice dei formatori non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'trainer_index_route_unavailable' => 'トレーナーインデックスルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'trainer_index_route_unavailable' => 'Trainerindexroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'trainer_index_route_unavailable' => 'Trasa indeksu trenerów jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'trainer_index_route_unavailable' => 'A rota do índice de formadores não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'trainer_index_route_unavailable' => 'A rota do índice de treinadores não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'trainer_index_route_unavailable' => 'Маршрут индекса тренеров недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'trainer_index_route_unavailable' => 'Eğitmen indeks rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'trainer_index_route_unavailable' => '培训师索引路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::TRF => [
			'ar' => ['transfer_index_route_unavailable' => 'مسار فهرس النقل غير متاح. ' . self::DELEGATION_AR],
			'da' => ['transfer_index_route_unavailable' => 'Overførselsindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['transfer_index_route_unavailable' => 'Übertragungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['transfer_index_route_unavailable' => 'Transfer index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['transfer_index_route_unavailable' => 'La ruta del índice de transferencias no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['transfer_index_route_unavailable' => 'La route de l\'index de transfert est indisponible. ' . self::DELEGATION_FR],
			'he' => ['transfer_index_route_unavailable' => 'נתיב אינדקס העברות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['transfer_index_route_unavailable' => 'La rotta dell\'indice dei trasferimenti non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['transfer_index_route_unavailable' => '転送インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['transfer_index_route_unavailable' => 'Overdrachtsindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['transfer_index_route_unavailable' => 'Trasa indeksu transferów jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['transfer_index_route_unavailable' => 'A rota do índice de transferências não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['transfer_index_route_unavailable' => 'A rota do índice de transferências não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['transfer_index_route_unavailable' => 'Маршрут индекса переводов недоступен. ' . self::DELEGATION_RU],
			'tr' => ['transfer_index_route_unavailable' => 'Transfer indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['transfer_index_route_unavailable' => '调职索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::TMT => [
			'ar' => ['time_tracker_route_unavailable' => 'مسار المتعقب الزمني غير متاح. ' . self::DELEGATION_AR],
			'da' => ['time_tracker_route_unavailable' => 'Tidssporingsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['time_tracker_route_unavailable' => 'Zeiterfassungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['time_tracker_route_unavailable' => 'Tracker route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['time_tracker_route_unavailable' => 'La ruta del rastreador de tiempo no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['time_tracker_route_unavailable' => 'La route du traqueur de temps n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['time_tracker_route_unavailable' => 'נתיב מעקב זמן אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['time_tracker_route_unavailable' => 'La rotta del time tracker non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['time_tracker_route_unavailable' => 'タイムトラッカールートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['time_tracker_route_unavailable' => 'Tijdtrackerroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['time_tracker_route_unavailable' => 'Trasa śledzenia czasu jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['time_tracker_route_unavailable' => 'A rota do rastreador de tempo não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['time_tracker_route_unavailable' => 'A rota do rastreador de tempo não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['time_tracker_route_unavailable' => 'Маршрут отслеживания времени недоступен. ' . self::DELEGATION_RU],
			'tr' => ['time_tracker_route_unavailable' => 'Zaman Takipçisi rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['time_tracker_route_unavailable' => '时间跟踪器路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::TMN => [
			'ar' => ['termination_index_route_unavailable' => 'مسار فهرس إنهاء الخدمة غير متاح. ' . self::DELEGATION_AR],
			'da' => ['termination_index_route_unavailable' => 'Opsigelsesindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['termination_index_route_unavailable' => 'Kündigungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['termination_index_route_unavailable' => 'Termination index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['termination_index_route_unavailable' => 'La ruta del índice de terminación no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['termination_index_route_unavailable' => 'La route de l\'index de résiliation est indisponible. ' . self::DELEGATION_FR],
			'he' => ['termination_index_route_unavailable' => 'נתיב אינדקס סיום עבודה אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['termination_index_route_unavailable' => 'La rotta dell\'indice dei licenziamenti non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['termination_index_route_unavailable' => '解雇インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['termination_index_route_unavailable' => 'Beëindigingsindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['termination_index_route_unavailable' => 'Trasa indeksu wypowiedzeń jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['termination_index_route_unavailable' => 'A rota do índice de terminações não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['termination_index_route_unavailable' => 'A rota do índice de rescisões não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['termination_index_route_unavailable' => 'Маршрут индекса увольнений недоступен. ' . self::DELEGATION_RU],
			'tr' => ['termination_index_route_unavailable' => 'Fesih indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['termination_index_route_unavailable' => '终止索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::TMS => [
			'ar' => ['timesheet_list_route_unavailable' => 'مسار الجداول الزمنية غير متاح. ' . self::DELEGATION_AR],
			'da' => ['timesheet_list_route_unavailable' => 'Tidsplanliste-rute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['timesheet_list_route_unavailable' => 'Zeiterfassungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['timesheet_list_route_unavailable' => 'Timesheet route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['timesheet_list_route_unavailable' => 'La ruta de la hoja de horas no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['timesheet_list_route_unavailable' => 'La route de la feuille de temps n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['timesheet_list_route_unavailable' => 'נתיב גיליון שעות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['timesheet_list_route_unavailable' => 'La rotta del timesheet non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['timesheet_list_route_unavailable' => 'タイムシートルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['timesheet_list_route_unavailable' => 'Tijdschriftroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['timesheet_list_route_unavailable' => 'Trasa arkusza czasu pracy jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['timesheet_list_route_unavailable' => 'A rota da folha de horas não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['timesheet_list_route_unavailable' => 'A rota da folha de horas não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['timesheet_list_route_unavailable' => 'Маршрут табеля учета рабочего времени недоступен. ' . self::DELEGATION_RU],
			'tr' => ['timesheet_list_route_unavailable' => 'Zaman Çizelgesi rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['timesheet_list_route_unavailable' => '时间表路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::TRV => [
			'ar' => ['travel_index_route_unavailable' => 'مسار فهرس السفر غير متاح. ' . self::DELEGATION_AR],
			'da' => ['travel_index_route_unavailable' => 'Rejseindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['travel_index_route_unavailable' => 'Reiseindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['travel_index_route_unavailable' => 'Travel index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['travel_index_route_unavailable' => 'La ruta del índice de viajes no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['travel_index_route_unavailable' => 'La route de l\'index des voyages est indisponible. ' . self::DELEGATION_FR],
			'he' => ['travel_index_route_unavailable' => 'נתיב אינדקס נסיעות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['travel_index_route_unavailable' => 'La rotta dell\'indice dei viaggi non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['travel_index_route_unavailable' => '出張インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['travel_index_route_unavailable' => 'Reisindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['travel_index_route_unavailable' => 'Trasa indeksu podróży jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['travel_index_route_unavailable' => 'A rota do índice de viagens não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['travel_index_route_unavailable' => 'A rota do índice de viagens não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['travel_index_route_unavailable' => 'Маршрут индекса командировок недоступен. ' . self::DELEGATION_RU],
			'tr' => ['travel_index_route_unavailable' => 'Seyahat indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['travel_index_route_unavailable' => '差旅索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::TSK => [
			'ar' => [
				'taskboard_view_route_unavailable' => 'مسار المهام غير متاح. ' . self::DELEGATION_AR,
				'tsk_calendar_route_unavailable' => 'مسار تقويم المهام غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'taskboard_view_route_unavailable' => 'Opgavebordsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'tsk_calendar_route_unavailable' => 'Opgavekalenderrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'taskboard_view_route_unavailable' => 'Aufgabenboard-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'tsk_calendar_route_unavailable' => 'Aufgabenkalender-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'taskboard_view_route_unavailable' => 'Tasks route is unavailable. ' . self::DELEGATION_EN,
				'tsk_calendar_route_unavailable' => 'Task Calendar route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'taskboard_view_route_unavailable' => 'La ruta de tareas no está disponible. ' . self::DELEGATION_ES,
				'tsk_calendar_route_unavailable' => 'La ruta del calendario de tareas no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'taskboard_view_route_unavailable' => 'La route des tâches n\'est pas disponible. ' . self::DELEGATION_FR,
				'tsk_calendar_route_unavailable' => 'La route du calendrier des tâches n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'taskboard_view_route_unavailable' => 'נתיב משימות אינו זמין. ' . self::DELEGATION_HE,
				'tsk_calendar_route_unavailable' => 'נתיב לוח שנה למשימות אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'taskboard_view_route_unavailable' => 'La rotta delle attività non è disponibile. ' . self::DELEGATION_IT,
				'tsk_calendar_route_unavailable' => 'La rotta del calendario delle attività non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'taskboard_view_route_unavailable' => 'タスクルートは利用できません。' . self::DELEGATION_JA,
				'tsk_calendar_route_unavailable' => 'タスクカレンダールートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'taskboard_view_route_unavailable' => 'Takenbordroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'tsk_calendar_route_unavailable' => 'Taakkalenderroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'taskboard_view_route_unavailable' => 'Trasa tablicy zadań jest niedostępna. ' . self::DELEGATION_PL,
				'tsk_calendar_route_unavailable' => 'Trasa kalendarza zadań jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'taskboard_view_route_unavailable' => 'A rota de tarefas não está disponível. ' . self::DELEGATION_PT,
				'tsk_calendar_route_unavailable' => 'A rota do calendário de tarefas não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'taskboard_view_route_unavailable' => 'A rota de tarefas não está disponível. ' . self::DELEGATION_PTBR,
				'tsk_calendar_route_unavailable' => 'A rota do calendário de tarefas não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'taskboard_view_route_unavailable' => 'Маршрут задач недоступен. ' . self::DELEGATION_RU,
				'tsk_calendar_route_unavailable' => 'Маршрут календаря задач недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'taskboard_view_route_unavailable' => 'Görevler rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'tsk_calendar_route_unavailable' => 'Görev Takvimi rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'taskboard_view_route_unavailable' => '任务路由不可用。' . self::DELEGATION_ZH,
				'tsk_calendar_route_unavailable' => '任务日历路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::TSKB => [
			'ar' => [
				'taskboard_view_route_unavailable' => 'مسار عرض لوحة المهام غير متاح. ' . self::DELEGATION_AR,
				'taskboard_view_grid_route_unavailable' => 'مسار عرض شبكة لوحة المهام غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'taskboard_view_route_unavailable' => 'Opgaveboard visningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'taskboard_view_grid_route_unavailable' => 'Opgaveboard gittervisningsrute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'taskboard_view_route_unavailable' => 'Aufgabenboard-Anzeigeroute ist nicht verfügbar. ' . self::DELEGATION_DE,
				'taskboard_view_grid_route_unavailable' => 'Aufgabenboard-Rasteransichtsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'taskboard_view_route_unavailable' => 'Taskboard view route is unavailable. ' . self::DELEGATION_EN,
				'taskboard_view_grid_route_unavailable' => 'Taskboard grid view route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'taskboard_view_route_unavailable' => 'La ruta de visualización del tablero de tareas no está disponible. ' . self::DELEGATION_ES,
				'taskboard_view_grid_route_unavailable' => 'La ruta de vista de cuadrícula del tablero de tareas no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'taskboard_view_route_unavailable' => 'La route d\'affichage du tableau de tâches n\'est pas disponible. ' . self::DELEGATION_FR,
				'taskboard_view_grid_route_unavailable' => 'La route de la vue en grille du tableau de tâches n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'taskboard_view_route_unavailable' => 'נתיב תצוגת לוח המשימות אינו זמין. ' . self::DELEGATION_HE,
				'taskboard_view_grid_route_unavailable' => 'נתיב תצוגת הרשת של לוח המשימות אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'taskboard_view_route_unavailable' => 'La rotta di visualizzazione della bacheca attività non è disponibile. ' . self::DELEGATION_IT,
				'taskboard_view_grid_route_unavailable' => 'La rotta della vista a griglia della bacheca attività non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'taskboard_view_route_unavailable' => 'タスクボード表示ルートは利用できません。' . self::DELEGATION_JA,
				'taskboard_view_grid_route_unavailable' => 'タスクボードグリッド表示ルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'taskboard_view_route_unavailable' => 'Taakbord weergaveroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'taskboard_view_grid_route_unavailable' => 'Taakbord rasterweergaveroute is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'taskboard_view_route_unavailable' => 'Trasa widoku tablicy zadań jest niedostępna. ' . self::DELEGATION_PL,
				'taskboard_view_grid_route_unavailable' => 'Trasa widoku siatki tablicy zadań jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'taskboard_view_route_unavailable' => 'A rota de visualização do quadro de tarefas não está disponível. ' . self::DELEGATION_PT,
				'taskboard_view_grid_route_unavailable' => 'A rota de visualização em grade do quadro de tarefas não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'taskboard_view_route_unavailable' => 'A rota de visualização do quadro de tarefas não está disponível. ' . self::DELEGATION_PTBR,
				'taskboard_view_grid_route_unavailable' => 'A rota de visualização em grade do quadro de tarefas não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'taskboard_view_route_unavailable' => 'Маршрут просмотра доски задач недоступен. ' . self::DELEGATION_RU,
				'taskboard_view_grid_route_unavailable' => 'Маршрут просмотра сетки доски задач недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'taskboard_view_route_unavailable' => 'Görev panosu görüntüleme rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'taskboard_view_grid_route_unavailable' => 'Görev panosu ızgara görünümü rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'taskboard_view_route_unavailable' => '任务板查看路由不可用。' . self::DELEGATION_ZH,
				'taskboard_view_grid_route_unavailable' => '任务板网格视图路由不可用。' . self::DELEGATION_ZH
			]
		],
		ViewsConstants::TX => [
			'ar' => [
				'tx_index_route_unavailable' => 'مسار إعدادات المحاسبة غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'tx_index_route_unavailable' => 'Regnskabsopsætningsrute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'tx_index_route_unavailable' => 'Buchhaltungseinrichtungsroute ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'tx_index_route_unavailable' => 'Accounting Setup route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'tx_index_route_unavailable' => 'La ruta de Configuración contable no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'tx_index_route_unavailable' => 'La route de configuration comptable n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'tx_index_route_unavailable' => 'נתיב הגדרת החשבונאות אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'tx_index_route_unavailable' => 'Il percorso di configurazione contabile non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'tx_index_route_unavailable' => '会計設定ルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'tx_index_route_unavailable' => 'Boekhoudsetuproute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'tx_index_route_unavailable' => 'Trasa konfiguracji księgowej jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'tx_index_route_unavailable' => 'A rota de Configuração de Contabilidade não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'tx_index_route_unavailable' => 'A rota de Configuração de Contabilidade não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'tx_index_route_unavailable' => 'Маршрут настройки бухгалтерского учета недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'tx_index_route_unavailable' => 'Muhasebe Kurulumu rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'tx_index_route_unavailable' => '会计设置路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::USR => [
			'ar' => [
				'user_index_route_unavailable' => 'مسار المستخدمين غير متاح. ' . self::DELEGATION_AR,
				'profile_route_unavailable' => 'مسار الملف الشخصي غير متاح. ' . self::DELEGATION_AR,
				'logout_route_unavailable' => 'مسار تسجيل الخروج غير متاح. ' . self::DELEGATION_AR,
			],
			'da' => [
				'user_index_route_unavailable' => 'Brugerrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'profile_route_unavailable' => 'Profilrute er ikke tilgængelig. ' . self::DELEGATION_DA,
				'logout_route_unavailable' => 'Logud-rute er ikke tilgængelig. ' . self::DELEGATION_DA,
			],
			'de' => [
				'user_index_route_unavailable' => 'Benutzer-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'profile_route_unavailable' => 'Profil-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'logout_route_unavailable' => 'Abmelde-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
			],
			'en' => [
				'user_index_route_unavailable' => 'User route is unavailable. ' . self::DELEGATION_EN,
				'profile_route_unavailable' => 'Profile route is unavailable. ' . self::DELEGATION_EN,
				'logout_route_unavailable' => 'Logout route is unavailable. ' . self::DELEGATION_EN,
			],
			'es' => [
				'user_index_route_unavailable' => 'La ruta de usuarios no está disponible. ' . self::DELEGATION_ES,
				'profile_route_unavailable' => 'La ruta del perfil no está disponible. ' . self::DELEGATION_ES,
				'logout_route_unavailable' => 'La ruta de cierre de sesión no está disponible. ' . self::DELEGATION_ES,
			],
			'fr' => [
				'user_index_route_unavailable' => 'La route des utilisateurs n\'est pas disponible. ' . self::DELEGATION_FR,
				'profile_route_unavailable' => 'La route du profil n\'est pas disponible. ' . self::DELEGATION_FR,
				'logout_route_unavailable' => 'La route de déconnexion n\'est pas disponible. ' . self::DELEGATION_FR,
			],
			'he' => [
				'user_index_route_unavailable' => 'נתיב משתמשים אינו זמין. ' . self::DELEGATION_HE,
				'profile_route_unavailable' => 'נתיב פרופיל אינו זמין. ' . self::DELEGATION_HE,
				'logout_route_unavailable' => 'נתיב התנתקות אינו זמין. ' . self::DELEGATION_HE,
			],
			'it' => [
				'user_index_route_unavailable' => 'La rotta degli utenti non è disponibile. ' . self::DELEGATION_IT,
				'profile_route_unavailable' => 'La rotta del profilo non è disponibile. ' . self::DELEGATION_IT,
				'logout_route_unavailable' => 'La rotta di logout non è disponibile. ' . self::DELEGATION_IT,
			],
			'ja' => [
				'user_index_route_unavailable' => 'ユーザールートは利用できません。' . self::DELEGATION_JA,
				'profile_route_unavailable' => 'プロファイルルートは利用できません。' . self::DELEGATION_JA,
				'logout_route_unavailable' => 'ログアウトルートは利用できません。' . self::DELEGATION_JA,
			],
			'nl' => [
				'user_index_route_unavailable' => 'Gebruikersroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'profile_route_unavailable' => 'Profielroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'logout_route_unavailable' => 'Uitlogroute is niet beschikbaar. ' . self::DELEGATION_NL,
			],
			'pl' => [
				'user_index_route_unavailable' => 'Trasa użytkowników jest niedostępna. ' . self::DELEGATION_PL,
				'profile_route_unavailable' => 'Trasa profilu jest niedostępna. ' . self::DELEGATION_PL,
				'logout_route_unavailable' => 'Trasa wylogowania jest niedostępna. ' . self::DELEGATION_PL,
			],
			'pt' => [
				'user_index_route_unavailable' => 'A rota de usuários não está disponível. ' . self::DELEGATION_PT,
				'profile_route_unavailable' => 'A rota do perfil não está disponível. ' . self::DELEGATION_PT,
				'logout_route_unavailable' => 'A rota de logout não está disponível. ' . self::DELEGATION_PT,
			],
			'pt-br' => [
				'user_index_route_unavailable' => 'A rota de usuários não está disponível. ' . self::DELEGATION_PTBR,
				'profile_route_unavailable' => 'A rota do perfil não está disponível. ' . self::DELEGATION_PTBR,
				'logout_route_unavailable' => 'A rota de logout não está disponível. ' . self::DELEGATION_PTBR,
			],
			'ru' => [
				'user_index_route_unavailable' => 'Маршрут пользователей недоступен. ' . self::DELEGATION_RU,
				'profile_route_unavailable' => 'Маршрут профиля недоступен. ' . self::DELEGATION_RU,
				'logout_route_unavailable' => 'Маршрут выхода недоступен. ' . self::DELEGATION_RU,
			],
			'tr' => [
				'user_index_route_unavailable' => 'Kullanıcı rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'profile_route_unavailable' => 'Profil rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'logout_route_unavailable' => 'Çıkış rotası kullanılamıyor. ' . self::DELEGATION_TR,
			],
			'zh' => [
				'user_index_route_unavailable' => '用户路由不可用。' . self::DELEGATION_ZH,
				'profile_route_unavailable' => '个人资料路由不可用。' . self::DELEGATION_ZH,
				'logout_route_unavailable' => '退出路由不可用。' . self::DELEGATION_ZH,
			]
		],
		ViewsConstants::WRH => [
			'ar' => ['warehouse_index_route_unavailable' => 'مسار المستودعات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['warehouse_index_route_unavailable' => 'Lagerrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['warehouse_index_route_unavailable' => 'Lager-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['warehouse_index_route_unavailable' => 'Warehouse route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['warehouse_index_route_unavailable' => 'La ruta del almacén no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['warehouse_index_route_unavailable' => 'La route de l\'entrepôt n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['warehouse_index_route_unavailable' => 'נתיב מחסן אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['warehouse_index_route_unavailable' => 'La rotta del magazzino non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['warehouse_index_route_unavailable' => '倉庫ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['warehouse_index_route_unavailable' => 'Magazijnroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['warehouse_index_route_unavailable' => 'Trasa magazynu jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['warehouse_index_route_unavailable' => 'A rota do armazém não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['warehouse_index_route_unavailable' => 'A rota do armazém não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['warehouse_index_route_unavailable' => 'Маршрут склада недоступен. ' . self::DELEGATION_RU],
			'tr' => ['warehouse_index_route_unavailable' => 'Depo rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['warehouse_index_route_unavailable' => '仓库路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::WRH_TRF => [
			'ar' => ['wrh_trf_index_route_unavailable' => 'مسار نقل المستودع غير متاح. ' . self::DELEGATION_AR],
			'da' => ['wrh_trf_index_route_unavailable' => 'Lageroverførselsrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['wrh_trf_index_route_unavailable' => 'Lagerübertragungs-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['wrh_trf_index_route_unavailable' => 'Warehouse Transfer route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['wrh_trf_index_route_unavailable' => 'La ruta de transferencia de almacén no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['wrh_trf_index_route_unavailable' => 'La route de transfert d\'entrepôt n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['wrh_trf_index_route_unavailable' => 'נתיב העברת מחסן אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['wrh_trf_index_route_unavailable' => 'La rotta di trasferimento del magazzino non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['wrh_trf_index_route_unavailable' => '倉庫転送ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['wrh_trf_index_route_unavailable' => 'Magazijnoverdrachtsroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['wrh_trf_index_route_unavailable' => 'Trasa transferu magazynu jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['wrh_trf_index_route_unavailable' => 'A rota de transferência de armazém não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['wrh_trf_index_route_unavailable' => 'A rota de transferência de armazém não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['wrh_trf_index_route_unavailable' => 'Маршрут передачи склада недоступен. ' . self::DELEGATION_RU],
			'tr' => ['wrh_trf_index_route_unavailable' => 'Depo Transferi rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['wrh_trf_index_route_unavailable' => '仓库转移路由不可用。' . self::DELEGATION_ZH],
		],
		ViewsConstants::WRN => [
			'ar' => ['warning_index_route_unavailable' => 'مسار فهرس التحذيرات غير متاح. ' . self::DELEGATION_AR],
			'da' => ['warning_index_route_unavailable' => 'Advarselsindeksrute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['warning_index_route_unavailable' => 'Warnungsindex-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['warning_index_route_unavailable' => 'Warning index route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['warning_index_route_unavailable' => 'La ruta del índice de advertencias no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['warning_index_route_unavailable' => 'La route de l\'index des avertissements est indisponible. ' . self::DELEGATION_FR],
			'he' => ['warning_index_route_unavailable' => 'נתיב אינדקס אזהרות אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['warning_index_route_unavailable' => 'La rotta dell\'indice degli avvisi non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['warning_index_route_unavailable' => '警告インデックスルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['warning_index_route_unavailable' => 'Waarschuwingsindexroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['warning_index_route_unavailable' => 'Trasa indeksu ostrzeżeń jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['warning_index_route_unavailable' => 'A rota do índice de advertências não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['warning_index_route_unavailable' => 'A rota do índice de advertências não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['warning_index_route_unavailable' => 'Маршрут индекса предупреждений недоступен. ' . self::DELEGATION_RU],
			'tr' => ['warning_index_route_unavailable' => 'Uyarı indeks rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['warning_index_route_unavailable' => '警告索引路由不可用。' . self::DELEGATION_ZH]
		],
		ViewsConstants::ZMM => [
			'ar' => ['zoom_meeting_index_route_unavailable' => 'مسار اجتماعات زووم غير متاح. ' . self::DELEGATION_AR],
			'da' => ['zoom_meeting_index_route_unavailable' => 'Zoom-møderute er ikke tilgængelig. ' . self::DELEGATION_DA],
			'de' => ['zoom_meeting_index_route_unavailable' => 'Zoom-Meeting-Route ist nicht verfügbar. ' . self::DELEGATION_DE],
			'en' => ['zoom_meeting_index_route_unavailable' => 'Zoom Meeting route is unavailable. ' . self::DELEGATION_EN],
			'es' => ['zoom_meeting_index_route_unavailable' => 'La ruta de reunión de Zoom no está disponible. ' . self::DELEGATION_ES],
			'fr' => ['zoom_meeting_index_route_unavailable' => 'La route de réunion Zoom n\'est pas disponible. ' . self::DELEGATION_FR],
			'he' => ['zoom_meeting_index_route_unavailable' => 'נתיב פגישת זום אינו זמין. ' . self::DELEGATION_HE],
			'it' => ['zoom_meeting_index_route_unavailable' => 'La rotta della riunione Zoom non è disponibile. ' . self::DELEGATION_IT],
			'ja' => ['zoom_meeting_index_route_unavailable' => 'Zoom会議ルートは利用できません。' . self::DELEGATION_JA],
			'nl' => ['zoom_meeting_index_route_unavailable' => 'Zoom-vergaderroute is niet beschikbaar. ' . self::DELEGATION_NL],
			'pl' => ['zoom_meeting_index_route_unavailable' => 'Trasa spotkania Zoom jest niedostępna. ' . self::DELEGATION_PL],
			'pt' => ['zoom_meeting_index_route_unavailable' => 'A rota da reunião do Zoom não está disponível. ' . self::DELEGATION_PT],
			'pt-br' => ['zoom_meeting_index_route_unavailable' => 'A rota da reunião do Zoom não está disponível. ' . self::DELEGATION_PTBR],
			'ru' => ['zoom_meeting_index_route_unavailable' => 'Маршрут встреч Zoom недоступен. ' . self::DELEGATION_RU],
			'tr' => ['zoom_meeting_index_route_unavailable' => 'Zoom Toplantı rotası kullanılamıyor. ' . self::DELEGATION_TR],
			'zh' => ['zoom_meeting_index_route_unavailable' => 'Zoom会议路由不可用。' . self::DELEGATION_ZH],
		],
		'generics' => [
			'ar' => [
				'are_you_sure' => 'هل أنت متأكد؟',
				'irreversible_action' => 'لا يمكن التراجع عن هذا الإجراء. هل تريد المتابعة؟',
				'dashboard_unavailable' => 'مسار لوحة التحكم غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول النطاق الخاص بك.',
				'confirm_action_prompt' => 'هل تريد تأكيد هذا الإجراء؟ اضغط "نعم" للمتابعة أو "إلغاء" للرجوع',
				'hrm_dashboard_route_unavailable' => 'مسار لوحة تحكم إدارة الموارد البشرية غير متاح. ' . self::DELEGATION_AR,
				'crm_dashboard_route_unavailable' => 'مسار لوحة تحكم إدارة موارد العملاء غير متاح. ' . self::DELEGATION_AR,
				'project_dashboard_route_unavailable' => 'مسار لوحة تحكم المشاريع غير متاح. ' . self::DELEGATION_AR,
				'pos_dashboard_route_unavailable' => 'مسار لوحة تحكم نقاط البيع غير متاح. ' . self::DELEGATION_AR,
				'grammar_check_route_unavailable' => 'مسار التدقيق النحوي غير متاح. ' . self::DELEGATION_AR
			],
			'da' => [
				'are_you_sure' => 'Er du sikker?',
				'irreversible_action' => 'Denne handling kan ikke fortrydes. Vil du fortsætte?',
				'dashboard_unavailable' => 'Dashboard-ruten er ikke tilgængelig. ' . self::DELEGATION_DA,
				'confirm_action_prompt' => 'Vil du bekræfte denne handling? Tryk "Ja" for at fortsætte eller "Annuller" for at gå tilbage',
				'hrm_dashboard_route_unavailable' => 'Dashboard-ruten for Human Resources Management er ikke tilgængelig. ' . self::DELEGATION_DA,
				'crm_dashboard_route_unavailable' => 'Dashboard-ruten for Customer Resources Management er ikke tilgængelig. ' . self::DELEGATION_DA,
				'project_dashboard_route_unavailable' => 'Projekt-dashboard-ruten er ikke tilgængelig. ' . self::DELEGATION_DA,
				'pos_dashboard_route_unavailable' => 'Ruten for dashboardet for Points of Sales er ikke tilgængelig. ' . self::DELEGATION_DA,
				'grammar_check_route_unavailable' => 'Grammatiktjek-rute er ikke tilgængelig. ' . self::DELEGATION_DA
			],
			'de' => [
				'are_you_sure' => 'Sind Sie sicher?',
				'irreversible_action' => 'Diese Aktion kann nicht rückgängig gemacht werden. Möchten Sie fortfahren?',
				'dashboard_unavailable' => 'Dashboard-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'confirm_action_prompt' => 'Möchten Sie diese Aktion bestätigen? Drücken Sie "Ja" um fortzufahren oder "Abbrechen" um zurückzukehren',
				'hrm_dashboard_route_unavailable' => 'Die Dashboard-Route für Human Resources Management ist nicht verfügbar. ' . self::DELEGATION_DE,
				'crm_dashboard_route_unavailable' => 'Die Dashboard-Route für Customer Resources Management ist nicht verfügbar. ' . self::DELEGATION_DE,
				'project_dashboard_route_unavailable' => 'Die Projekt-Dashboard-Route ist nicht verfügbar. ' . self::DELEGATION_DE,
				'pos_dashboard_route_unavailable' => 'Die Route für das Dashboard der Points of Sales ist nicht verfügbar. ' . self::DELEGATION_DE,
				'grammar_check_route_unavailable' => 'Grammatikprüfungsroute ist nicht verfügbar. ' . self::DELEGATION_DE
			],
			'en' => [
				'are_you_sure' => 'Are You Sure?',
				'irreversible_action' => 'This action can not be undone. Do you want to continue?',
				'dashboard_unavailable' => 'Dashboard route is unavailable. ' . self::DELEGATION_EN,
				'confirm_action_prompt' => 'Do you want to confirm this action? Press "Yes" to continue or "Cancel" to go back',
				'hrm_dashboard_route_unavailable' => 'The dashboard for Human Resources Management route is unavailable. ' . self::DELEGATION_EN,
				'crm_dashboard_route_unavailable' => 'The dashboard route for Customer Resources Management is unavailable. ' . self::DELEGATION_EN,
				'project_dashboard_route_unavailable' => 'Project dashboard route is unavailable. ' . self::DELEGATION_EN,
				'pos_dashboard_route_unavailable' => 'The route for the dashboard of the Points of Sales is unavailable. ' . self::DELEGATION_EN,
				'grammar_check_route_unavailable' => 'Grammar check route is unavailable. ' . self::DELEGATION_EN
			],
			'es' => [
				'are_you_sure' => '¿Está seguro?',
				'irreversible_action' => 'Esta acción no se puede deshacer. ¿Desea continuar?',
				'dashboard_unavailable' => 'La ruta del panel de control no está disponible. ' . self::DELEGATION_ES,
				'confirm_action_prompt' => '¿Desea confirmar esta acción? Presione "Sí" para continuar o "Cancelar" para volver',
				'hrm_dashboard_route_unavailable' => 'La ruta del panel de control de Gestión de Recursos Humanos no está disponible. ' . self::DELEGATION_ES,
				'crm_dashboard_route_unavailable' => 'La ruta del panel de control de Gestión de Recursos de Clientes no está disponible. ' . self::DELEGATION_ES,
				'project_dashboard_route_unavailable' => 'La ruta del panel de control de proyectos no está disponible. ' . self::DELEGATION_ES,
				'pos_dashboard_route_unavailable' => 'La ruta del panel de control de Puntos de Venta no está disponible. ' . self::DELEGATION_ES,
				'grammar_check_route_unavailable' => 'La ruta de verificación gramatical no está disponible. ' . self::DELEGATION_ES
			],
			'fr' => [
				'are_you_sure' => 'Êtes-vous sûr ?',
				'irreversible_action' => 'Cette action ne peut pas être annulée. Voulez-vous continuer ?',
				'dashboard_unavailable' => 'La route du tableau de bord est indisponible. ' . self::DELEGATION_FR,
				'confirm_action_prompt' => 'Voulez-vous confirmer cette action ? Appuyez sur "Oui" pour continuer ou "Annuler" pour revenir en arrière',
				'hrm_dashboard_route_unavailable' => 'La route du tableau de bord pour la Gestion des Ressources Humaines est indisponible. ' . self::DELEGATION_FR,
				'crm_dashboard_route_unavailable' => 'La route du tableau de bord pour la Gestion des Ressources Clients est indisponible. ' . self::DELEGATION_FR,
				'project_dashboard_route_unavailable' => 'La route du tableau de bord de projet est indisponible. ' . self::DELEGATION_FR,
				'pos_dashboard_route_unavailable' => 'La route du tableau de bord des Points de Vente est indisponible. ' . self::DELEGATION_FR,
				'grammar_check_route_unavailable' => 'La route de vérification grammaticale n\'est pas disponible. ' . self::DELEGATION_FR
			],
			'he' => [
				'are_you_sure' => 'האם אתה בטוח?',
				'irreversible_action' => 'לא ניתן לבטל פעולה זו. האם ברצונך להמשיך?',
				'dashboard_unavailable' => 'מסלול לוח מחוונים אינו זמין. אנא פנה לתמיכה טכנית או למנהל הדומיין שלך.',
				'confirm_action_prompt' => 'האם אתה רוצה לאשר פעולה זו? לחץ "כן" כדי להמשיך או "ביטול" כדי לחזור',
				'hrm_dashboard_route_unavailable' => 'מסלול לוח המחוונים לניהול משאבי אנוש אינו זמין. ' . self::DELEGATION_HE,
				'crm_dashboard_route_unavailable' => 'מסלול לוח המחוונים לניהול משאבי לקוחות אינו זמין. ' . self::DELEGATION_HE,
				'project_dashboard_route_unavailable' => 'מסלול לוח המחוונים לפרויקטים אינו זמין. ' . self::DELEGATION_HE,
				'pos_dashboard_route_unavailable' => 'מסלול לוח המחוונים לנקודות מכירה אינו זמין. ' . self::DELEGATION_HE,
				'grammar_check_route_unavailable' => 'נתיב בדיקת הדקדוק אינו זמין. ' . self::DELEGATION_HE
			],
			'it' => [
				'are_you_sure' => 'Sei sicuro?',
				'irreversible_action' => 'Questa azione non può essere annullata. Vuoi continuare?',
				'dashboard_unavailable' => 'La rotta della dashboard non è disponibile. ' . self::DELEGATION_IT,
				'confirm_action_prompt' => 'Vuoi confermare questa azione? Premi "Sì" per continuare o "Annulla" per tornare indietro',
				'hrm_dashboard_route_unavailable' => 'La rotta della dashboard per la Gestione delle Risorse Umane non è disponibile. ' . self::DELEGATION_IT,
				'crm_dashboard_route_unavailable' => 'La rotta della dashboard per la Gestione delle Risorse Clienti non è disponibile. ' . self::DELEGATION_IT,
				'project_dashboard_route_unavailable' => 'La rotta della dashboard di progetto non è disponibile. ' . self::DELEGATION_IT,
				'pos_dashboard_route_unavailable' => 'La rotta della dashboard dei Punti Vendita non è disponibile. ' . self::DELEGATION_IT,
				'grammar_check_route_unavailable' => 'La rotta di controllo grammaticale non è disponibile. ' . self::DELEGATION_IT
			],
			'ja' => [
				'are_you_sure' => 'よろしいですか？',
				'irreversible_action' => 'この操作は元に戻せません。続行しますか？',
				'dashboard_unavailable' => 'ダッシュボードルートは利用できません。' . self::DELEGATION_JA,
				'confirm_action_prompt' => 'この操作を確認しますか？続行するには「はい」を、戻るには「キャンセル」を押してください',
				'hrm_dashboard_route_unavailable' => '人事管理ダッシュボードのルートは利用できません。' . self::DELEGATION_JA,
				'crm_dashboard_route_unavailable' => '顧客リソース管理ダッシュボードのルートは利用できません。' . self::DELEGATION_JA,
				'project_dashboard_route_unavailable' => 'プロジェクトダッシュボードのルートは利用できません。' . self::DELEGATION_JA,
				'pos_dashboard_route_unavailable' => '販売拠点ダッシュボードのルートは利用できません。' . self::DELEGATION_JA,
				'grammar_check_route_unavailable' => '文法チェックルートは利用できません。' . self::DELEGATION_JA
			],
			'nl' => [
				'are_you_sure' => 'Weet u het zeker?',
				'irreversible_action' => 'Deze actie kan niet ongedaan worden gemaakt. Wilt u doorgaan?',
				'dashboard_unavailable' => 'Dashboard-route is niet beschikbaar. ' . self::DELEGATION_NL,
				'confirm_action_prompt' => 'Wilt u deze actie bevestigen? Druk op "Ja" om door te gaan of "Annuleren" om terug te gaan',
				'hrm_dashboard_route_unavailable' => 'De dashboardroute voor Human Resources Management is niet beschikbaar. ' . self::DELEGATION_NL,
				'crm_dashboard_route_unavailable' => 'De dashboardroute voor Customer Resources Management is niet beschikbaar. ' . self::DELEGATION_NL,
				'project_dashboard_route_unavailable' => 'De projectdashboardroute is niet beschikbaar. ' . self::DELEGATION_NL,
				'pos_dashboard_route_unavailable' => 'De route voor het dashboard van de Points of Sales is niet beschikbaar. ' . self::DELEGATION_NL,
				'grammar_check_route_unavailable' => 'Grammaticacontrole-route is niet beschikbaar. ' . self::DELEGATION_NL
			],
			'pl' => [
				'are_you_sure' => 'Czy jesteś pewien?',
				'irreversible_action' => 'Tej czynności nie można cofnąć. Czy chcesz kontynuować?',
				'dashboard_unavailable' => 'Trasa pulpitu nawigacyjnego jest niedostępna. ' . self::DELEGATION_PL,
				'confirm_action_prompt' => 'Czy chcesz potwierdzić tę czynność? Naciśnij "Tak", aby kontynuować, lub "Anuluj", aby wrócić',
				'hrm_dashboard_route_unavailable' => 'Trasa pulpitu nawigacyjnego Zarządzania Zasobami Ludzkimi jest niedostępna. ' . self::DELEGATION_PL,
				'crm_dashboard_route_unavailable' => 'Trasa pulpitu nawigacyjnego Zarządzania Zasobami Klientów jest niedostępna. ' . self::DELEGATION_PL,
				'project_dashboard_route_unavailable' => 'Trasa pulpitu nawigacyjnego projektu jest niedostępna. ' . self::DELEGATION_PL,
				'pos_dashboard_route_unavailable' => 'Trasa pulpitu nawigacyjnego Punktów Sprzedaży jest niedostępna. ' . self::DELEGATION_PL,
				'grammar_check_route_unavailable' => 'Trasa sprawdzania gramatyki jest niedostępna. ' . self::DELEGATION_PL
			],
			'pt' => [
				'are_you_sure' => 'Tem a certeza?',
				'irreversible_action' => 'Esta ação não pode ser desfeita. Deseja continuar?',
				'dashboard_unavailable' => 'A rota do painel de controlo não está disponível. ' . self::DELEGATION_PT,
				'confirm_action_prompt' => 'Deseja confirmar esta ação? Pressione "Sim" para continuar ou "Cancelar" para voltar',
				'hrm_dashboard_route_unavailable' => 'A rota do painel de Gestão de Recursos Humanos não está disponível. ' . self::DELEGATION_PT,
				'crm_dashboard_route_unavailable' => 'A rota do painel de Gestão de Recursos de Clientes não está disponível. ' . self::DELEGATION_PT,
				'project_dashboard_route_unavailable' => 'A rota do painel de projeto não está disponível. ' . self::DELEGATION_PT,
				'pos_dashboard_route_unavailable' => 'A rota do painel dos Pontos de Venda não está disponível. ' . self::DELEGATION_PT,
				'grammar_check_route_unavailable' => 'A rota de verificação gramatical não está disponível. ' . self::DELEGATION_PT
			],
			'pt-br' => [
				'are_you_sure' => 'Você tem certeza?',
				'irreversible_action' => 'Esta ação não pode ser desfeita. Deseja continuar?',
				'dashboard_unavailable' => 'A rota do painel não está disponível. ' . self::DELEGATION_PTBR,
				'confirm_action_prompt' => 'Deseja confirmar esta ação? Pressione "Sim" para continuar ou "Cancelar" para voltar',
				'hrm_dashboard_route_unavailable' => 'A rota do painel de Gerenciamento de Recursos Humanos não está disponível. ' . self::DELEGATION_PTBR,
				'crm_dashboard_route_unavailable' => 'A rota do painel de Gerenciamento de Recursos do Cliente não está disponível. ' . self::DELEGATION_PTBR,
				'project_dashboard_route_unavailable' => 'A rota do painel de projetos não está disponível. ' . self::DELEGATION_PTBR,
				'pos_dashboard_route_unavailable' => 'A rota do painel dos Pontos de Venda não está disponível. ' . self::DELEGATION_PTBR,
				'grammar_check_route_unavailable' => 'A rota de verificação gramatical não está disponível. ' . self::DELEGATION_PTBR
			],
			'ru' => [
				'are_you_sure' => 'Вы уверены?',
				'irreversible_action' => 'Это действие нельзя отменить. Вы хотите продолжить?',
				'dashboard_unavailable' => 'Маршрут панели управления недоступен. ' . self::DELEGATION_RU,
				'confirm_action_prompt' => 'Вы хотите подтвердить это действие? Нажмите "Да" для продолжения или "Отмена" для возврата',
				'hrm_dashboard_route_unavailable' => 'Маршрут панели управления управления персоналом недоступен. ' . self::DELEGATION_RU,
				'crm_dashboard_route_unavailable' => 'Маршрут панели управления управления клиентскими ресурсами недоступен. ' . self::DELEGATION_RU,
				'project_dashboard_route_unavailable' => 'Маршрут панели управления проектами недоступен. ' . self::DELEGATION_RU,
				'pos_dashboard_route_unavailable' => 'Маршрут панели управления точек продаж недоступен. ' . self::DELEGATION_RU,
				'grammar_check_route_unavailable' => 'Маршрут проверки грамматики недоступен. ' . self::DELEGATION_RU
			],
			'tr' => [
				'are_you_sure' => 'Emin misiniz?',
				'irreversible_action' => 'Bu işlem geri alınamaz. Devam etmek istiyor musunuz?',
				'dashboard_unavailable' => 'Pano rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'confirm_action_prompt' => 'Bu işlemi onaylamak istiyor musunuz? Devam etmek için "Evet"e veya geri dönmek için "İptal"e basın',
				'hrm_dashboard_route_unavailable' => 'İnsan Kaynakları Yönetimi panosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'crm_dashboard_route_unavailable' => 'Müşteri Kaynakları Yönetimi panosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'project_dashboard_route_unavailable' => 'Proje panosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'pos_dashboard_route_unavailable' => 'Satış Noktaları panosu rotası kullanılamıyor. ' . self::DELEGATION_TR,
				'grammar_check_route_unavailable' => 'Dilbilgisi denetimi rotası kullanılamıyor. ' . self::DELEGATION_TR
			],
			'zh' => [
				'are_you_sure' => '您确定吗？',
				'irreversible_action' => '此操作无法撤消。您要继续吗？',
				'dashboard_unavailable' => '仪表板路由不可用。' . self::DELEGATION_ZH,
				'confirm_action_prompt' => '您要确认此操作吗？按“是”继续，或按“取消”返回',
				'hrm_dashboard_route_unavailable' => '人力资源管理仪表板路由不可用。' . self::DELEGATION_ZH,
				'crm_dashboard_route_unavailable' => '客户资源管理仪表板路由不可用。' . self::DELEGATION_ZH,
				'project_dashboard_route_unavailable' => '项目仪表板路由不可用。' . self::DELEGATION_ZH,
				'pos_dashboard_route_unavailable' => '销售点仪表板路由不可用。' . self::DELEGATION_ZH,
				'grammar_check_route_unavailable' => '语法检查路由不可用。' . self::DELEGATION_ZH
			]
		]
	];
}
