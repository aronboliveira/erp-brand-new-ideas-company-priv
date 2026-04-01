/**
 * @file erp-guard.js
 * @description Central singleton for route protection, error handling, toast notifications,
 *              and form validation guards across the ERP system.
 * @version 2.0.0
 * @license MIT
 * @author ERP Prestech Team
 * @see https://getbootstrap.com/docs/5.3/components/toasts/
 * @see https://getbootstrap.com/docs/5.3/components/modal/
 *
 * @requires Bootstrap 5.x (optional, falls back to native alerts)
 *
 * @example
 * // Basic usage
 * const guard = window.ERPGuard;
 * guard.showToast('Operation completed', 'success');
 * guard.bindSubmitGuard(formElement, validateFn);
 */
(function (global) {
  "use strict";

  /**
   * @constant {string} TOAST_CONTAINER_ID - ID for the toast container element
   * @private
   */
  const TOAST_CONTAINER_ID = "erp-toast-container";

  /**
   * @constant {string} MODAL_CONTAINER_ID - ID for the modal container element
   * @private
   */
  const MODAL_CONTAINER_ID = "erp-modal-container";

  /**
   * @constant {Object<string, string>} TOAST_CLASSES - Bootstrap classes for toast variants
   * @private
   */
  const TOAST_CLASSES = Object.freeze({
    success: "bg-success text-white",
    error: "bg-danger text-white",
    warning: "bg-warning text-dark",
    info: "bg-info text-dark",
    primary: "bg-primary text-white",
    secondary: "bg-secondary text-white",
    dark: "bg-dark text-white",
    light: "bg-light text-dark",
  });

  /**
   * @constant {Object<string, string>} DEFAULT_MESSAGES - Default localized messages
   * @private
   */
  const DEFAULT_MESSAGES = Object.freeze({
    en: {
      error: "An error occurred",
      success: "Operation completed successfully",
      warning: "Warning",
      info: "Information",
      confirm: "Are you sure?",
      yes: "Yes",
      no: "No",
      cancel: "Cancel",
      ok: "OK",
      loading: "Loading...",
      invalidUrl: "Invalid URL detected",
      invalidForm: "Please fill in all required fields",
      networkError: "Network error. Please try again.",
      serverError: "Server error. Please contact support.",
      validationError: "Validation failed",
      unauthorized: "Unauthorized access",
      forbidden: "Access forbidden",
      notFound: "Resource not found",
      timeout: "Request timed out",
      notice: "Notice",
      login_submit_unavailable:
        "Login submit route is unavailable. Please contact technical support or your domain administrator.",
      route_unavailable:
        "This route is unavailable. Please contact technical support.",
    },
    pt: {
      error: "Ocorreu um erro",
      success: "Operação concluída com sucesso",
      warning: "Aviso",
      info: "Informação",
      confirm: "Tem certeza?",
      yes: "Sim",
      no: "Não",
      cancel: "Cancelar",
      ok: "OK",
      loading: "Carregando...",
      invalidUrl: "URL inválida detectada",
      invalidForm: "Por favor, preencha todos os campos obrigatórios",
      networkError: "Erro de rede. Por favor, tente novamente.",
      serverError: "Erro no servidor. Por favor, contate o suporte.",
      validationError: "Falha na validação",
      unauthorized: "Acesso não autorizado",
      forbidden: "Acesso proibido",
      notFound: "Recurso não encontrado",
      timeout: "Tempo de requisição esgotado",
      notice: "Aviso",
      login_submit_unavailable:
        "A rota de envio de login está indisponível. Contacte o suporte técnico ou o administrador do seu domínio.",
      route_unavailable:
        "Esta rota está indisponível. Contacte o suporte técnico.",
    },
    es: {
      error: "Ocurrió un error",
      success: "Operación completada con éxito",
      warning: "Advertencia",
      info: "Información",
      confirm: "¿Está seguro?",
      yes: "Sí",
      no: "No",
      cancel: "Cancelar",
      ok: "OK",
      loading: "Cargando...",
      invalidUrl: "URL inválida detectada",
      invalidForm: "Por favor, complete todos los campos requeridos",
      networkError: "Error de red. Por favor, inténtelo de nuevo.",
      serverError: "Error del servidor. Por favor, contacte al soporte.",
      validationError: "Falló la validación",
      unauthorized: "Acceso no autorizado",
      forbidden: "Acceso prohibido",
      notFound: "Recurso no encontrado",
      timeout: "Tiempo de solicitud agotado",
      notice: "Aviso",
      login_submit_unavailable:
        "La ruta de envío de inicio de sesión no está disponible. Comuníquese con el soporte técnico.",
      route_unavailable:
        "Esta ruta no está disponible. Comuníquese con el soporte técnico.",
    },
    fr: {
      error: "Une erreur est survenue",
      success: "Opération terminée avec succès",
      warning: "Avertissement",
      info: "Information",
      confirm: "Êtes-vous sûr ?",
      yes: "Oui",
      no: "Non",
      cancel: "Annuler",
      ok: "OK",
      loading: "Chargement...",
      invalidUrl: "URL invalide détectée",
      invalidForm: "Veuillez remplir tous les champs obligatoires",
      networkError: "Erreur réseau. Veuillez réessayer.",
      serverError: "Erreur serveur. Veuillez contacter le support.",
      validationError: "Échec de la validation",
      unauthorized: "Accès non autorisé",
      forbidden: "Accès interdit",
      notFound: "Ressource introuvable",
      timeout: "Délai de la requête dépassé",
      notice: "Avis",
      login_submit_unavailable:
        "La route de soumission de connexion est indisponible. Contactez le support technique.",
      route_unavailable:
        "Cette route est indisponible. Contactez le support technique.",
    },
    de: {
      error: "Ein Fehler ist aufgetreten",
      success: "Vorgang erfolgreich abgeschlossen",
      warning: "Warnung",
      info: "Information",
      confirm: "Sind Sie sicher?",
      yes: "Ja",
      no: "Nein",
      cancel: "Abbrechen",
      ok: "OK",
      loading: "Laden...",
      invalidUrl: "Ungültige URL erkannt",
      invalidForm: "Bitte füllen Sie alle erforderlichen Felder aus",
      networkError: "Netzwerkfehler. Bitte versuchen Sie es erneut.",
      serverError: "Serverfehler. Bitte wenden Sie sich an den Support.",
      validationError: "Validierung fehlgeschlagen",
      unauthorized: "Unbefugter Zugriff",
      forbidden: "Zugriff verboten",
      notFound: "Ressource nicht gefunden",
      timeout: "Zeitüberschreitung der Anfrage",
      notice: "Hinweis",
      login_submit_unavailable:
        "Die Anmelde-Route ist nicht verfügbar. Wenden Sie sich an den technischen Support.",
      route_unavailable:
        "Diese Route ist nicht verfügbar. Wenden Sie sich an den technischen Support.",
    },
    it: {
      error: "Si è verificato un errore",
      success: "Operazione completata con successo",
      warning: "Avviso",
      info: "Informazione",
      confirm: "Sei sicuro?",
      yes: "Sì",
      no: "No",
      cancel: "Annulla",
      ok: "OK",
      loading: "Caricamento...",
      invalidUrl: "URL non valido rilevato",
      invalidForm: "Compilare tutti i campi obbligatori",
      networkError: "Errore di rete. Riprovare.",
      serverError: "Errore del server. Contattare il supporto.",
      validationError: "Validazione non riuscita",
      unauthorized: "Accesso non autorizzato",
      forbidden: "Accesso vietato",
      notFound: "Risorsa non trovata",
      timeout: "Timeout della richiesta",
      notice: "Avviso",
      login_submit_unavailable:
        "Il percorso di invio del login non è disponibile. Contattare il supporto tecnico.",
      route_unavailable:
        "Questo percorso non è disponibile. Contattare il supporto tecnico.",
    },
    ja: {
      error: "エラーが発生しました",
      success: "操作が正常に完了しました",
      warning: "警告",
      info: "情報",
      confirm: "よろしいですか？",
      yes: "はい",
      no: "いいえ",
      cancel: "キャンセル",
      ok: "OK",
      loading: "読み込み中...",
      invalidUrl: "無効なURLが検出されました",
      invalidForm: "必須フィールドをすべて入力してください",
      networkError: "ネットワークエラー。もう一度お試しください。",
      serverError: "サーバーエラー。サポートにお問い合わせください。",
      validationError: "検証に失敗しました",
      unauthorized: "不正なアクセス",
      forbidden: "アクセスが禁止されています",
      notFound: "リソースが見つかりません",
      timeout: "リクエストがタイムアウトしました",
      notice: "お知らせ",
      login_submit_unavailable:
        "ログイン送信ルートが利用できません。テクニカルサポートにお問い合わせください。",
      route_unavailable:
        "このルートは利用できません。テクニカルサポートにお問い合わせください。",
    },
    ru: {
      error: "Произошла ошибка",
      success: "Операция выполнена успешно",
      warning: "Предупреждение",
      info: "Информация",
      confirm: "Вы уверены?",
      yes: "Да",
      no: "Нет",
      cancel: "Отмена",
      ok: "ОК",
      loading: "Загрузка...",
      invalidUrl: "Обнаружен недействительный URL",
      invalidForm: "Заполните все обязательные поля",
      networkError: "Ошибка сети. Попробуйте ещё раз.",
      serverError: "Ошибка сервера. Обратитесь в службу поддержки.",
      validationError: "Ошибка валидации",
      unauthorized: "Несанкционированный доступ",
      forbidden: "Доступ запрещён",
      notFound: "Ресурс не найден",
      timeout: "Время запроса истекло",
      notice: "Уведомление",
      login_submit_unavailable:
        "Маршрут отправки входа недоступен. Обратитесь в техническую поддержку.",
      route_unavailable:
        "Этот маршрут недоступен. Обратитесь в техническую поддержку.",
    },
    zh: {
      error: "发生错误",
      success: "操作成功完成",
      warning: "警告",
      info: "信息",
      confirm: "您确定吗？",
      yes: "是",
      no: "否",
      cancel: "取消",
      ok: "确定",
      loading: "加载中...",
      invalidUrl: "检测到无效的URL",
      invalidForm: "请填写所有必填字段",
      networkError: "网络错误，请重试。",
      serverError: "服务器错误，请联系支持。",
      validationError: "验证失败",
      unauthorized: "未授权访问",
      forbidden: "禁止访问",
      notFound: "未找到资源",
      timeout: "请求超时",
      notice: "通知",
      login_submit_unavailable: "登录提交路由不可用。请联系技术支持。",
      route_unavailable: "此路由不可用。请联系技术支持。",
    },
    ar: {
      error: "حدث خطأ",
      success: "تمت العملية بنجاح",
      warning: "تحذير",
      info: "معلومات",
      confirm: "هل أنت متأكد؟",
      yes: "نعم",
      no: "لا",
      cancel: "إلغاء",
      ok: "موافق",
      loading: "جاري التحميل...",
      invalidUrl: "تم اكتشاف رابط غير صالح",
      invalidForm: "يرجى ملء جميع الحقول المطلوبة",
      networkError: "خطأ في الشبكة. يرجى المحاولة مرة أخرى.",
      serverError: "خطأ في الخادم. يرجى الاتصال بالدعم.",
      validationError: "فشل التحقق",
      unauthorized: "وصول غير مصرح به",
      forbidden: "الوصول محظور",
      notFound: "لم يتم العثور على المورد",
      timeout: "انتهت مهلة الطلب",
      notice: "إشعار",
      login_submit_unavailable:
        "مسار تسجيل الدخول غير متاح. يرجى الاتصال بالدعم الفني.",
      route_unavailable: "هذا المسار غير متاح. يرجى الاتصال بالدعم الفني.",
    },
    tr: {
      error: "Bir hata oluştu",
      success: "İşlem başarıyla tamamlandı",
      warning: "Uyarı",
      info: "Bilgi",
      confirm: "Emin misiniz?",
      yes: "Evet",
      no: "Hayır",
      cancel: "İptal",
      ok: "Tamam",
      loading: "Yükleniyor...",
      invalidUrl: "Geçersiz URL algılandı",
      invalidForm: "Lütfen tüm gerekli alanları doldurun",
      networkError: "Ağ hatası. Lütfen tekrar deneyin.",
      serverError: "Sunucu hatası. Lütfen destek ile iletişime geçin.",
      validationError: "Doğrulama başarısız",
      unauthorized: "Yetkisiz erişim",
      forbidden: "Erişim yasak",
      notFound: "Kaynak bulunamadı",
      timeout: "İstek zaman aşımına uğradı",
      notice: "Bildirim",
      login_submit_unavailable:
        "Giriş gönderim yolu kullanılamıyor. Teknik destek ile iletişime geçin.",
      route_unavailable:
        "Bu yol kullanılamıyor. Teknik destek ile iletişime geçin.",
    },
    nl: {
      error: "Er is een fout opgetreden",
      success: "Bewerking succesvol voltooid",
      warning: "Waarschuwing",
      info: "Informatie",
      confirm: "Weet u het zeker?",
      yes: "Ja",
      no: "Nee",
      cancel: "Annuleren",
      ok: "OK",
      loading: "Laden...",
      invalidUrl: "Ongeldige URL gedetecteerd",
      invalidForm: "Vul alle verplichte velden in",
      networkError: "Netwerkfout. Probeer het opnieuw.",
      serverError: "Serverfout. Neem contact op met ondersteuning.",
      validationError: "Validatie mislukt",
      unauthorized: "Ongeautoriseerde toegang",
      forbidden: "Toegang verboden",
      notFound: "Bron niet gevonden",
      timeout: "Verzoek is verlopen",
      notice: "Kennisgeving",
      login_submit_unavailable:
        "De inlogroute is niet beschikbaar. Neem contact op met de technische ondersteuning.",
      route_unavailable:
        "Deze route is niet beschikbaar. Neem contact op met de technische ondersteuning.",
    },
    pl: {
      error: "Wystąpił błąd",
      success: "Operacja zakończona pomyślnie",
      warning: "Ostrzeżenie",
      info: "Informacja",
      confirm: "Czy na pewno?",
      yes: "Tak",
      no: "Nie",
      cancel: "Anuluj",
      ok: "OK",
      loading: "Ładowanie...",
      invalidUrl: "Wykryto nieprawidłowy URL",
      invalidForm: "Proszę wypełnić wszystkie wymagane pola",
      networkError: "Błąd sieci. Spróbuj ponownie.",
      serverError: "Błąd serwera. Skontaktuj się z pomocą techniczną.",
      validationError: "Walidacja nie powiodła się",
      unauthorized: "Nieautoryzowany dostęp",
      forbidden: "Dostęp zabroniony",
      notFound: "Nie znaleziono zasobu",
      timeout: "Przekroczono limit czasu żądania",
      notice: "Powiadomienie",
      login_submit_unavailable:
        "Trasa logowania jest niedostępna. Skontaktuj się z pomocą techniczną.",
      route_unavailable:
        "Ta trasa jest niedostępna. Skontaktuj się z pomocą techniczną.",
    },
    da: {
      error: "Der opstod en fejl",
      success: "Handlingen blev gennemført",
      warning: "Advarsel",
      info: "Information",
      confirm: "Er du sikker?",
      yes: "Ja",
      no: "Nej",
      cancel: "Annuller",
      ok: "OK",
      loading: "Indlæser...",
      invalidUrl: "Ugyldig URL fundet",
      invalidForm: "Udfyld venligst alle obligatoriske felter",
      networkError: "Netværksfejl. Prøv venligst igen.",
      serverError: "Serverfejl. Kontakt venligst support.",
      validationError: "Validering mislykkedes",
      unauthorized: "Uautoriseret adgang",
      forbidden: "Adgang forbudt",
      notFound: "Ressource ikke fundet",
      timeout: "Anmodningen fik timeout",
      notice: "Besked",
      login_submit_unavailable:
        "Login-ruten er ikke tilgængelig. Kontakt teknisk support.",
      route_unavailable:
        "Denne rute er ikke tilgængelig. Kontakt teknisk support.",
    },
    he: {
      error: "אירעה שגיאה",
      success: "הפעולה הושלמה בהצלחה",
      warning: "אזהרה",
      info: "מידע",
      confirm: "האם אתה בטוח?",
      yes: "כן",
      no: "לא",
      cancel: "ביטול",
      ok: "אישור",
      loading: "טוען...",
      invalidUrl: "זוהה כתובת URL לא חוקית",
      invalidForm: "אנא מלא את כל השדות הנדרשים",
      networkError: "שגיאת רשת. נסה שוב.",
      serverError: "שגיאת שרת. פנה לתמיכה.",
      validationError: "האימות נכשל",
      unauthorized: "גישה לא מורשית",
      forbidden: "הגישה אסורה",
      notFound: "המשאב לא נמצא",
      timeout: "הבקשה חרגה מהזמן המוקצב",
      notice: "הודעה",
      login_submit_unavailable: "נתיב ההתחברות אינו זמין. פנה לתמיכה הטכנית.",
      route_unavailable: "נתיב זה אינו זמין. פנה לתמיכה הטכנית.",
    },
  });

  /**
   * @constant {number} DEFAULT_TOAST_DURATION - Default toast display duration in milliseconds
   * @private
   */
  const DEFAULT_TOAST_DURATION = 5000;

  /**
   * @constant {number} DEFAULT_MODAL_ZINDEX - Default z-index for modals
   * @private
   */
  const _DEFAULT_MODAL_ZINDEX = 1055;

  /**
   * @constant {WeakMap<Element, Function>} boundElements - Track elements with bound guards
   * @private
   */
  const boundElements = new WeakMap();

  /**
   * @constant {Set<string>} scheduledErrors - Track scheduled error messages to prevent duplicates
   * @private
   */
  const scheduledErrors = new Set();

  /**
   * @class ERPGuard
   * @classdesc Singleton class for centralized route protection, error handling,
   *            toast/modal management, and form validation across the ERP system.
   * @hideconstructor
   */
  class ERPGuard {
    /**
     * @private
     * @static
     * @type {ERPGuard|null}
     */
    static #instance = null;

    /**
     * @private
     * @type {string}
     */
    #locale = "en";

    /**
     * @private
     * @type {HTMLElement|null}
     */
    #toastContainer = null;

    /**
     * @private
     * @type {HTMLElement|null}
     */
    #modalContainer = null;

    /**
     * @private
     * @type {MutationObserver|null}
     */
    #observer = null;

    /**
     * @private
     * @type {boolean}
     */
    #initialized = false;

    /**
     * @constructor
     * @private
     * @throws {Error} If attempting to instantiate directly
     */
    constructor() {
      if (ERPGuard.#instance) {
        throw new Error(
          "ERPGuard is a singleton. Use ERPGuard.getInstance() or window.ERPGuard",
        );
      }
      this.#init();
    }

    /**
     * Gets the singleton instance of ERPGuard
     * @static
     * @returns {ERPGuard} The singleton instance
     */
    static getInstance() {
      if (!ERPGuard.#instance) {
        ERPGuard.#instance = new ERPGuard();
      }
      return ERPGuard.#instance;
    }

    /**
     * Initializes the guard system
     * @private
     * @returns {void}
     */
    #init() {
      if (this.#initialized) return;

      this.#locale = this.#detectLocale();
      this.#ensureContainers();
      this.#setupMutationObserver();
      this.#autoGuardElements();
      this.#initialized = true;

      console.info("[ERPGuard] Initialized with locale:", this.#locale);
    }

    /**
     * Detects the current locale from various sources
     * @private
     * @returns {string} The detected locale code
     */
    #detectLocale() {
      try {
        // Check localStorage
        const stored = localStorage.getItem("locale");
        if (stored && DEFAULT_MESSAGES[stored]) return stored;

        // Check document lang attribute
        const docLang = document.documentElement.lang?.slice(0, 2);
        if (docLang && DEFAULT_MESSAGES[docLang]) return docLang;

        // Check navigator language
        const navLang = navigator.language?.slice(0, 2);
        if (navLang && DEFAULT_MESSAGES[navLang]) return navLang;

        // Check meta tag
        const meta = document.querySelector('meta[name="locale"]');
        const metaLang = meta?.content?.slice(0, 2);
        if (metaLang && DEFAULT_MESSAGES[metaLang]) return metaLang;
      } catch (e) {
        console.warn("[ERPGuard] Error detecting locale:", e);
      }
      return "en";
    }

    /**
     * Gets the current locale
     * @returns {string} The current locale code
     */
    getLocale() {
      return this.#locale;
    }

    /**
     * Sets the locale
     * @param {string} locale - The locale code to set
     * @returns {ERPGuard} This instance for chaining
     */
    setLocale(locale) {
      if (DEFAULT_MESSAGES[locale]) {
        this.#locale = locale;
        try {
          localStorage.setItem("locale", locale);
        } catch (_e) {
          console.warn("[ERPGuard] Could not save locale to localStorage");
        }
      }
      return this;
    }

    /**
     * Gets a localized message
     * @param {string} key - The message key
     * @param {string} [fallback] - Fallback message if key not found
     * @returns {string} The localized message
     */
    getMsg(key, fallback = "") {
      const messages = DEFAULT_MESSAGES[this.#locale] || DEFAULT_MESSAGES.en;
      return messages[key] || fallback || key;
    }

    /**
     * Checks if Bootstrap is available
     * @returns {boolean} True if Bootstrap is available
     */
    hasBootstrap() {
      return !!(
        typeof bootstrap !== "undefined" &&
        bootstrap.Toast &&
        bootstrap.Modal
      );
    }

    /**
     * Checks if Bootstrap Toast is available
     * @returns {boolean} True if Bootstrap Toast is available
     */
    hasToast() {
      return !!(typeof bootstrap !== "undefined" && bootstrap.Toast);
    }

    /**
     * Checks if Bootstrap Modal is available
     * @returns {boolean} True if Bootstrap Modal is available
     */
    hasModal() {
      return !!(typeof bootstrap !== "undefined" && bootstrap.Modal);
    }

    /**
     * Ensures toast and modal containers exist in the DOM
     * @private
     * @returns {void}
     */
    #ensureContainers() {
      // Toast container
      if (!this.#toastContainer) {
        this.#toastContainer = document.getElementById(TOAST_CONTAINER_ID);
        if (!this.#toastContainer) {
          this.#toastContainer = document.createElement("div");
          this.#toastContainer.id = TOAST_CONTAINER_ID;
          this.#toastContainer.className =
            "toast-container position-fixed top-0 end-0 p-3";
          this.#toastContainer.style.cssText = "z-index: 1100;";
          this.#toastContainer.setAttribute("aria-live", "polite");
          this.#toastContainer.setAttribute("aria-atomic", "true");
          document.body.appendChild(this.#toastContainer);
        }
      }

      // Modal container
      if (!this.#modalContainer) {
        this.#modalContainer = document.getElementById(MODAL_CONTAINER_ID);
        if (!this.#modalContainer) {
          this.#modalContainer = document.createElement("div");
          this.#modalContainer.id = MODAL_CONTAINER_ID;
          document.body.appendChild(this.#modalContainer);
        }
      }
    }

    /**
     * Shows a toast notification
     * @param {string} message - The message to display
     * @param {('success'|'error'|'warning'|'info'|'primary'|'secondary'|'dark'|'light')} [type='info'] - Toast type
     * @param {Object} [options={}] - Additional options
     * @param {number} [options.duration=5000] - Duration in milliseconds
     * @param {boolean} [options.autohide=true] - Whether to auto-hide
     * @param {string} [options.title] - Optional title
     * @param {boolean} [options.closable=true] - Whether to show close button
     * @returns {ERPGuard} This instance for chaining
     */
    showToast(message, type = "info", options = {}) {
      const {
        duration = DEFAULT_TOAST_DURATION,
        autohide = true,
        title = "",
        closable = true,
      } = options;

      if (!message) return this;

      // Fallback to alert if Bootstrap not available
      if (!this.hasToast()) {
        const prefix =
          type === "error"
            ? "❌ "
            : type === "success"
              ? "✅ "
              : type === "warning"
                ? "⚠️ "
                : "ℹ️ ";
        alert(prefix + (title ? `${title}: ` : "") + message);
        return this;
      }

      this.#ensureContainers();

      const toastId = `toast-${Date.now()}-${Math.random()
        .toString(36)
        .slice(2, 9)}`;
      const classes = TOAST_CLASSES[type] || TOAST_CLASSES.info;

      const toastHtml = `
        <div id="${toastId}" class="toast ${classes}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="${autohide}" data-bs-delay="${duration}">
          ${
            title
              ? `
          <div class="toast-header ${classes}">
            <strong class="me-auto">${this.#escapeHtml(title)}</strong>
            ${
              closable
                ? '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>'
                : ""
            }
          </div>
          `
              : ""
          }
          <div class="toast-body d-flex align-items-center justify-content-between">
            <span>${this.#escapeHtml(message)}</span>
            ${
              !title && closable
                ? '<button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>'
                : ""
            }
          </div>
        </div>
      `;

      this.#toastContainer.insertAdjacentHTML("beforeend", toastHtml);

      const toastEl = document.getElementById(toastId);
      if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();

        toastEl.addEventListener(
          "hidden.bs.toast",
          () => {
            toastEl.remove();
          },
          { once: true },
        );
      }

      return this;
    }

    /**
     * Shows a success toast
     * @param {string} message - The message to display
     * @param {Object} [options={}] - Additional options
     * @returns {ERPGuard} This instance for chaining
     */
    success(message, options = {}) {
      return this.showToast(message, "success", options);
    }

    /**
     * Shows an error toast
     * @param {string} message - The message to display
     * @param {Object} [options={}] - Additional options
     * @returns {ERPGuard} This instance for chaining
     */
    error(message, options = {}) {
      return this.showToast(message || this.getMsg("error"), "error", options);
    }

    /**
     * Shows a warning toast
     * @param {string} message - The message to display
     * @param {Object} [options={}] - Additional options
     * @returns {ERPGuard} This instance for chaining
     */
    warning(message, options = {}) {
      return this.showToast(
        message || this.getMsg("warning"),
        "warning",
        options,
      );
    }

    /**
     * Shows an info toast
     * @param {string} message - The message to display
     * @param {Object} [options={}] - Additional options
     * @returns {ERPGuard} This instance for chaining
     */
    info(message, options = {}) {
      return this.showToast(message || this.getMsg("info"), "info", options);
    }

    /**
     * Shows a modal dialog
     * @param {Object} options - Modal options
     * @param {string} options.title - Modal title
     * @param {string} options.body - Modal body content
     * @param {('sm'|'md'|'lg'|'xl')} [options.size='md'] - Modal size
     * @param {boolean} [options.closable=true] - Whether modal can be closed
     * @param {Array<{text: string, class: string, onClick: Function}>} [options.buttons=[]] - Footer buttons
     * @param {Function} [options.onShow] - Callback when modal is shown
     * @param {Function} [options.onHide] - Callback when modal is hidden
     * @returns {Object|null} Modal instance or null if Bootstrap unavailable
     */
    showModal(options = {}) {
      const {
        title = "",
        body = "",
        size = "md",
        closable = true,
        buttons = [],
        onShow = null,
        onHide = null,
        centered = true,
        scrollable = false,
      } = options;

      // Fallback if Bootstrap not available
      if (!this.hasModal()) {
        const confirmed = buttons.length
          ? confirm(`${title}\n\n${body}`)
          : (alert(`${title}\n\n${body}`), true);
        if (buttons.length && confirmed && buttons[0]?.onClick) {
          buttons[0].onClick();
        }
        return null;
      }

      this.#ensureContainers();

      const modalId = `modal-${Date.now()}-${Math.random()
        .toString(36)
        .slice(2, 9)}`;
      const sizeClass = size !== "md" ? `modal-${size}` : "";
      const centeredClass = centered ? "modal-dialog-centered" : "";
      const scrollableClass = scrollable ? "modal-dialog-scrollable" : "";

      const buttonsHtml = buttons
        .map(
          (btn, idx) => `
        <button type="button" class="btn ${btn.class || "btn-secondary"}" data-btn-idx="${idx}">
          ${this.#escapeHtml(btn.text)}
        </button>
      `,
        )
        .join("");

      const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}-label" aria-hidden="true" ${
          closable ? "" : 'data-bs-backdrop="static" data-bs-keyboard="false"'
        }>
          <div class="modal-dialog ${sizeClass} ${centeredClass} ${scrollableClass}">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="${modalId}-label">${this.#escapeHtml(
                  title,
                )}</h5>
                ${
                  closable
                    ? '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
                    : ""
                }
              </div>
              <div class="modal-body">${body}</div>
              ${
                buttons.length
                  ? `<div class="modal-footer">${buttonsHtml}</div>`
                  : ""
              }
            </div>
          </div>
        </div>
      `;

      this.#modalContainer.insertAdjacentHTML("beforeend", modalHtml);

      const modalEl = document.getElementById(modalId);
      const modal = new bootstrap.Modal(modalEl);

      // Bind button click handlers
      buttons.forEach((btn, idx) => {
        const btnEl = modalEl.querySelector(`[data-btn-idx="${idx}"]`);
        if (btnEl && btn.onClick) {
          btnEl.addEventListener(
            "click",
            () => {
              btn.onClick(modal);
            },
            { once: true },
          );
        }
      });

      // Event handlers
      if (onShow) {
        modalEl.addEventListener("shown.bs.modal", onShow, { once: true });
      }
      if (onHide) {
        modalEl.addEventListener("hidden.bs.modal", onHide, { once: true });
      }

      // Cleanup on hide
      modalEl.addEventListener(
        "hidden.bs.modal",
        () => {
          modalEl.remove();
        },
        { once: true },
      );

      modal.show();
      return modal;
    }

    /**
     * Shows a confirmation modal
     * @param {string} message - Confirmation message
     * @param {Function} onConfirm - Callback when confirmed
     * @param {Function} [onCancel] - Callback when cancelled
     * @param {Object} [options={}] - Additional modal options
     * @returns {Object|null} Modal instance or null
     */
    confirm(message, onConfirm, onCancel = null, options = {}) {
      return this.showModal({
        title: options.title || this.getMsg("confirm"),
        body: `<p>${this.#escapeHtml(message)}</p>`,
        closable: options.closable !== false,
        ...options,
        buttons: [
          {
            text: options.confirmText || this.getMsg("yes"),
            class: options.confirmClass || "btn-primary",
            onClick: modal => {
              modal.hide();
              if (onConfirm) onConfirm();
            },
          },
          {
            text: options.cancelText || this.getMsg("no"),
            class: options.cancelClass || "btn-secondary",
            onClick: modal => {
              modal.hide();
              if (onCancel) onCancel();
            },
          },
        ],
      });
    }

    /**
     * Schedules an error to be shown (debounced)
     * @param {string} message - Error message
     * @param {number} [delay=100] - Delay in milliseconds
     * @returns {ERPGuard} This instance for chaining
     */
    scheduleError(message, delay = 100) {
      if (scheduledErrors.has(message)) return this;

      scheduledErrors.add(message);
      setTimeout(() => {
        this.error(message);
        scheduledErrors.delete(message);
      }, delay);

      return this;
    }

    /**
     * Schedules an interactive error with optional action
     * @param {string} message - Error message
     * @param {Object} [options={}] - Options
     * @param {Function} [options.onRetry] - Retry callback
     * @param {Function} [options.onDismiss] - Dismiss callback
     * @param {number} [options.delay=100] - Delay in milliseconds
     * @returns {ERPGuard} This instance for chaining
     */
    scheduleInteractiveError(message, options = {}) {
      const { onRetry, onDismiss, delay = 100 } = options;
      const key = `interactive:${message}`;

      if (scheduledErrors.has(key)) return this;

      scheduledErrors.add(key);
      setTimeout(() => {
        if (onRetry) {
          this.confirm(message, onRetry, onDismiss, {
            title: this.getMsg("error"),
            confirmText: "Retry",
            cancelText: this.getMsg("cancel"),
          });
        } else {
          this.error(message);
          if (onDismiss) onDismiss();
        }
        scheduledErrors.delete(key);
      }, delay);

      return this;
    }

    /**
     * Validates if a URL is safe and well-formed
     * @param {string} url - URL to validate
     * @param {Object} [options={}] - Validation options
     * @param {boolean} [options.allowRelative=true] - Allow relative URLs
     * @param {boolean} [options.requireHttps=false] - Require HTTPS
     * @param {string[]} [options.allowedHosts=[]] - List of allowed hosts
     * @returns {boolean} True if URL is valid
     */
    isInvalidUrl(url, options = {}) {
      const {
        allowRelative = true,
        requireHttps = false,
        allowedHosts = [],
      } = options;

      if (!url || typeof url !== "string") return true;

      // Check for javascript: or data: schemes
      const dangerous = /^(javascript|data|vbscript):/i;
      if (dangerous.test(url.trim())) return true;

      // Relative URL check
      if (
        allowRelative &&
        (url.startsWith("/") || url.startsWith(".") || !url.includes(":"))
      ) {
        return false;
      }

      try {
        const parsed = new URL(url, window.location.origin);

        // HTTPS requirement
        if (requireHttps && parsed.protocol !== "https:") return true;

        // Allowed hosts check
        if (allowedHosts.length > 0 && !allowedHosts.includes(parsed.host))
          return true;

        return false;
      } catch (_e) {
        return true;
      }
    }

    /* ── Network / AJAX helpers ──────────────────────────────────── */

    /**
     * Get CSRF token from meta tag
     * @returns {string} CSRF token value
     */
    getCsrfToken() {
      return (
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || ""
      );
    }

    /**
     * Resolve the effective URL from an element's attributes
     * Checks data-url, href (for non-forms), and action (for forms).
     * @param {HTMLElement|null} el - Element to inspect
     * @param {string} [explicit] - An explicitly provided URL override
     * @returns {string|null} The resolved URL, or null if unavailable
     */
    resolveUrl(el, explicit) {
      if (explicit && explicit !== "#") return explicit;

      const url = el?.getAttribute?.("data-url") || "";
      const href =
        el?.tagName === "FORM"
          ? el.getAttribute("action") || ""
          : el?.getAttribute?.("href") || "";

      if ((!url || url === "#") && (!href || href === "#")) {
        return null;
      }

      return url && url !== "#" ? url : href;
    }

    /**
     * Perform a guarded fetch with default JSON/AJAX headers.
     * Returns a normalized result `{ ok, data?, status?, error? }`.
     * @param {string} url - Request URL
     * @param {RequestInit} [opts={}] - Fetch options
     * @returns {Promise<{ok:boolean, data?:*, status?:number, error?:string}>}
     */
    async safeFetch(url, opts = {}) {
      if (this.isInvalidUrl(url)) return { ok: false, error: "Invalid URL" };

      try {
        const resp = await fetch(url, {
          ...opts,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            ...(opts.headers || {}),
          },
        });

        if (!resp.ok)
          return { ok: false, status: resp.status, error: resp.statusText };

        const data = await resp.json().catch(() => ({}));
        return { ok: true, data };
      } catch (e) {
        return { ok: false, error: e.message };
      }
    }

    /**
     * Guarded POST request. Uses the Fetch API with CSRF token.
     * @param {string} url - Request URL
     * @param {Object} data - Payload (will be JSON-stringified)
     * @param {Object} [opts={}] - Extra options
     * @param {string} [opts.errorMsg] - Custom error message on invalid URL
     * @param {Object} [opts.headers] - Additional headers
     * @returns {Promise<{ok:boolean, data?:*, status?:number, error?:string}>}
     */
    async ajaxPost(url, data, opts = {}) {
      if (this.isInvalidUrl(url)) {
        const msg = opts.errorMsg || this.getMsg("invalidUrl");
        this.error(msg);
        return { ok: false, error: "Invalid URL" };
      }

      return this.safeFetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": this.getCsrfToken(),
          ...(opts.headers || {}),
        },
        body: JSON.stringify(data),
      });
    }

    /**
     * Guarded DELETE request. Uses the Fetch API with CSRF token.
     * @param {string} url - Request URL
     * @param {Object} [opts={}] - Extra options
     * @param {Object} [opts.headers] - Additional headers
     * @returns {Promise<{ok:boolean, data?:*, status?:number, error?:string}>}
     */
    async ajaxDelete(url, opts = {}) {
      if (this.isInvalidUrl(url)) {
        const msg = opts.errorMsg || this.getMsg("invalidUrl");
        this.error(msg);
        return { ok: false, error: "Invalid URL" };
      }

      return this.safeFetch(url, {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": this.getCsrfToken(),
          ...(opts.headers || {}),
        },
      });
    }

    /**
     * Perform a jQuery $.ajax call with guard checks.
     * Falls back to safeFetch when jQuery is unavailable.
     * @param {Object} config - jQuery-style ajax config
     * @param {string} config.url - Request URL
     * @param {string} [config.type='GET'] - HTTP method
     * @param {*} [config.data] - Request payload
     * @param {Function} [config.success] - Success callback
     * @param {Function} [config.error] - Error callback
     * @param {HTMLElement} [config.guardEl] - Element for guard messages
     * @param {string} [config.guardMsgKey] - Translation key on failure
     * @returns {void}
     */
    guardedAjax(config = {}) {
      const {
        url,
        type = "GET",
        data,
        success,
        error: onError,
        guardEl,
        guardMsgKey = "ajax_unavailable",
        ...rest
      } = config;

      const resolved = this.resolveUrl(guardEl, url);
      if (!resolved) {
        this.scheduleInteractiveError(
          this.getMsg(guardMsgKey, "This action is currently unavailable."),
        );
        return;
      }

      const $ = window.jQuery;
      if ($ && $.ajax) {
        $.ajax({
          url: resolved,
          type,
          data: data || {},
          cache: false,
          ...rest,
          success: d => {
            if (typeof success === "function") success(d);
          },
          error: xhr => {
            if (typeof onError === "function") {
              onError(xhr);
            } else {
              this.scheduleInteractiveError(
                this.getMsg(guardMsgKey, "Request failed."),
              );
            }
          },
        });
      } else {
        // Fallback: use Fetch API
        const fetchOpts = {
          method: type,
          headers: { "X-CSRF-TOKEN": this.getCsrfToken() },
        };

        if (data && type !== "GET") {
          if (data instanceof FormData) {
            fetchOpts.body = data;
          } else if (typeof data === "object") {
            fetchOpts.headers["Content-Type"] = "application/json";
            fetchOpts.body = JSON.stringify(data);
          }
        }

        this.safeFetch(resolved, fetchOpts).then(result => {
          if (result.ok && typeof success === "function") success(result.data);
          if (!result.ok && typeof onError === "function")
            onError({ status: result.status, statusText: result.error });
        });
      }
    }

    /**
     * Sets up MutationObserver to auto-guard dynamically added elements
     * @private
     * @returns {void}
     */
    #setupMutationObserver() {
      if (this.#observer) return;

      this.#observer = new MutationObserver(mutations => {
        for (const mutation of mutations) {
          if (mutation.type === "childList") {
            for (const node of mutation.addedNodes) {
              if (node.nodeType === Node.ELEMENT_NODE) {
                this.#autoGuardElement(node);
              }
            }
          }
        }
      });

      this.#observer.observe(document.body, {
        childList: true,
        subtree: true,
      });
    }

    /**
     * Auto-guards elements marked with data attributes
     * @private
     * @returns {void}
     */
    #autoGuardElements() {
      document.querySelectorAll("[data-erp-guard]").forEach(el => {
        this.#autoGuardElement(el);
      });
    }

    /**
     * Auto-guards a single element
     * @private
     * @param {Element} element - Element to guard
     * @returns {void}
     */
    #autoGuardElement(element) {
      if (!element || boundElements.has(element)) return;

      const guardType = element.dataset?.erpGuard;
      if (!guardType) {
        // Check children
        element.querySelectorAll?.("[data-erp-guard]").forEach(el => {
          this.#autoGuardElement(el);
        });
        return;
      }

      switch (guardType) {
        case "submit":
          this.bindSubmitGuard(element);
          break;
        case "click":
          this.bindClickGuard(element);
          break;
        case "change":
          this.bindChangeGuard(element);
          break;
        default:
          console.warn(`[ERPGuard] Unknown guard type: ${guardType}`);
      }
    }

    /**
     * Binds a submit guard to a form
     * @param {HTMLFormElement} form - Form element to guard
     * @param {Function} [validate] - Custom validation function
     * @param {Object} [options={}] - Options
     * @param {boolean} [options.preventDefault=false] - Prevent default submit
     * @param {Function} [options.onError] - Error callback
     * @param {Function} [options.onSuccess] - Success callback
     * @returns {ERPGuard} This instance for chaining
     */
    bindSubmitGuard(form, validate = null, options = {}) {
      if (!form || !(form instanceof HTMLFormElement)) {
        console.warn("[ERPGuard] bindSubmitGuard requires a form element");
        return this;
      }

      if (boundElements.has(form)) return this;

      const { preventDefault = false, onError, onSuccess } = options;

      const handler = e => {
        try {
          // Run custom validation
          if (validate && !validate(form)) {
            e.preventDefault();
            e.stopPropagation();
            this.error(this.getMsg("invalidForm"));
            if (onError) onError(new Error("Validation failed"));
            return;
          }

          // Check HTML5 validity
          if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            form.classList.add("was-validated");
            this.error(this.getMsg("invalidForm"));
            if (onError) onError(new Error("HTML5 validation failed"));
            return;
          }

          if (preventDefault) {
            e.preventDefault();
          }

          if (onSuccess) onSuccess(form);
        } catch (err) {
          e.preventDefault();
          console.error("[ERPGuard] Submit guard error:", err);
          this.error(this.getMsg("error"));
          if (onError) onError(err);
        }
      };

      form.addEventListener("submit", handler);
      boundElements.set(form, handler);

      return this;
    }

    /**
     * Binds a click guard to an element
     * @param {HTMLElement} element - Element to guard
     * @param {Function} [validate] - Custom validation function
     * @param {Object} [options={}] - Options
     * @returns {ERPGuard} This instance for chaining
     */
    bindClickGuard(element, validate = null, options = {}) {
      // Resolve string selectors to DOM elements
      if (typeof element === "string") {
        const resolved = document.querySelector(element);
        if (!resolved) {
          // Element not in DOM — silently return (common on pages where guarded elements don't exist)
          return this;
        }
        element = resolved;
      }

      if (!element || !(element instanceof HTMLElement)) {
        console.warn("[ERPGuard] bindClickGuard requires an HTML element");
        return this;
      }

      if (boundElements.has(element)) return this;

      const { onError, onSuccess, confirmMessage } = options;

      const handler = e => {
        try {
          // Confirmation if needed
          if (confirmMessage) {
            e.preventDefault();
            this.confirm(
              confirmMessage,
              () => {
                if (validate && !validate(element)) {
                  this.error(this.getMsg("validationError"));
                  if (onError) onError(new Error("Validation failed"));
                  return;
                }
                if (onSuccess) onSuccess(element);
                // Re-trigger if it's a link
                if (element.tagName === "A" && element.href) {
                  window.location.href = element.href;
                }
              },
              () => {
                if (onError) onError(new Error("Cancelled"));
              },
            );
            return;
          }

          // Run validation
          if (validate && !validate(element)) {
            e.preventDefault();
            e.stopPropagation();
            this.error(this.getMsg("validationError"));
            if (onError) onError(new Error("Validation failed"));
            return;
          }

          if (onSuccess) onSuccess(element);
        } catch (err) {
          e.preventDefault();
          console.error("[ERPGuard] Click guard error:", err);
          this.error(this.getMsg("error"));
          if (onError) onError(err);
        }
      };

      element.addEventListener("click", handler);
      boundElements.set(element, handler);

      return this;
    }

    /**
     * Binds a change guard to an input element
     * @param {HTMLElement} element - Input element to guard
     * @param {Function} [validate] - Custom validation function
     * @param {Object} [options={}] - Options
     * @returns {ERPGuard} This instance for chaining
     */
    bindChangeGuard(element, validate = null, options = {}) {
      if (!element || !(element instanceof HTMLElement)) {
        console.warn("[ERPGuard] bindChangeGuard requires an HTML element");
        return this;
      }

      if (boundElements.has(element)) return this;

      const { onError, onSuccess, debounce = 0 } = options;

      let timeout = null;

      const handler = _e => {
        if (timeout) clearTimeout(timeout);

        const doValidation = () => {
          try {
            if (validate && !validate(element)) {
              element.classList.add("is-invalid");
              element.classList.remove("is-valid");
              if (onError) onError(new Error("Validation failed"));
              return;
            }

            element.classList.remove("is-invalid");
            element.classList.add("is-valid");
            if (onSuccess) onSuccess(element);
          } catch (err) {
            console.error("[ERPGuard] Change guard error:", err);
            element.classList.add("is-invalid");
            if (onError) onError(err);
          }
        };

        if (debounce > 0) {
          timeout = setTimeout(doValidation, debounce);
        } else {
          doValidation();
        }
      };

      element.addEventListener("change", handler);
      element.addEventListener("input", handler);
      boundElements.set(element, handler);

      return this;
    }

    /**
     * Unbinds guards from an element
     * @param {HTMLElement} element - Element to unbind
     * @returns {ERPGuard} This instance for chaining
     */
    unbind(element) {
      const handler = boundElements.get(element);
      if (handler) {
        element.removeEventListener("submit", handler);
        element.removeEventListener("click", handler);
        element.removeEventListener("change", handler);
        element.removeEventListener("input", handler);
        boundElements.delete(element);
      }
      return this;
    }

    /**
     * Encodes a message for safe URL transmission
     * @param {string} message - Message to encode
     * @returns {string} Encoded message
     */
    encodeMsg(message) {
      try {
        return btoa(encodeURIComponent(message));
      } catch (_e) {
        return encodeURIComponent(message);
      }
    }

    /**
     * Decodes a URL-encoded message
     * @param {string} encoded - Encoded message
     * @returns {string} Decoded message
     */
    decodeMsg(encoded) {
      try {
        return decodeURIComponent(atob(encoded));
      } catch (_e) {
        try {
          return decodeURIComponent(encoded);
        } catch (_e2) {
          return encoded;
        }
      }
    }

    /**
     * Escapes HTML special characters
     * @private
     * @param {string} str - String to escape
     * @returns {string} Escaped string
     */
    #escapeHtml(str) {
      if (!str) return "";
      const div = document.createElement("div");
      div.textContent = str;
      return div.innerHTML;
    }

    /**
     * Handles AJAX/fetch errors with appropriate toast messages
     * @param {Error|Response|Object} error - The error object
     * @param {Object} [options={}] - Options
     * @param {Function} [options.onRetry] - Retry callback
     * @returns {ERPGuard} This instance for chaining
     */
    handleAjaxError(error, options = {}) {
      let message = this.getMsg("error");

      if (error instanceof Response) {
        switch (error.status) {
          case 401:
            message = this.getMsg("unauthorized");
            break;
          case 403:
            message = this.getMsg("forbidden");
            break;
          case 404:
            message = this.getMsg("notFound");
            break;
          case 408:
            message = this.getMsg("timeout");
            break;
          case 500:
          case 502:
          case 503:
            message = this.getMsg("serverError");
            break;
          default:
            message = error.statusText || message;
        }
      } else if (error instanceof Error) {
        if (
          error.name === "NetworkError" ||
          error.message.includes("network")
        ) {
          message = this.getMsg("networkError");
        } else if (error.name === "AbortError") {
          message = this.getMsg("timeout");
        } else {
          message = error.message || message;
        }
      } else if (typeof error === "object" && error.message) {
        message = error.message;
      }

      if (options.onRetry) {
        this.scheduleInteractiveError(message, options);
      } else {
        this.scheduleError(message);
      }

      return this;
    }

    /**
     * Destroys the guard instance and cleans up resources
     * @returns {void}
     */
    destroy() {
      if (this.#observer) {
        this.#observer.disconnect();
        this.#observer = null;
      }

      if (this.#toastContainer) {
        this.#toastContainer.remove();
        this.#toastContainer = null;
      }

      if (this.#modalContainer) {
        this.#modalContainer.remove();
        this.#modalContainer = null;
      }

      scheduledErrors.clear();
      this.#initialized = false;

      console.info("[ERPGuard] Destroyed");
    }
  }

  // Create and expose singleton instance
  const instance = ERPGuard.getInstance();

  // Expose to global scope
  if (typeof module !== "undefined" && module.exports) {
    module.exports = instance;
  } else {
    global.ERPGuard = instance;
  }
})(typeof window !== "undefined" ? window : globalThis);
