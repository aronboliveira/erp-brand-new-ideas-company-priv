(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            pusher_unavailable: "خدمة Pusher غير متاحة.",
            pusher_auth_unavailable: "تعذّر التحقق من الهوية لـ Pusher.",
            pusher_connect_failed: "فشل الاتصال بـ Pusher.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
        },
        da: {
            pusher_unavailable: "Pusher er ikke tilgængelig.",
            pusher_auth_unavailable: "Pusher-godkendelse mislykkedes.",
            pusher_connect_failed: "Forbindelse til Pusher mislykkedes.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
        },
        de: {
            pusher_unavailable: "Pusher ist nicht verfügbar.",
            pusher_auth_unavailable: "Pusher-Authentifizierung fehlgeschlagen.",
            pusher_connect_failed: "Verbindung zu Pusher fehlgeschlagen.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
        },
        en: {
            pusher_unavailable: "Pusher is unavailable.",
            pusher_auth_unavailable: "Pusher authentication failed.",
            pusher_connect_failed: "Failed to connect to Pusher.",
            plugin_unavailable: "A required library failed to load.",
        },
        es: {
            pusher_unavailable: "Pusher no está disponible.",
            pusher_auth_unavailable: "La autenticación de Pusher falló.",
            pusher_connect_failed: "No se pudo conectar a Pusher.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
        },
        fr: {
            pusher_unavailable: "Pusher est indisponible.",
            pusher_auth_unavailable: "Échec de l’authentification Pusher.",
            pusher_connect_failed: "Échec de connexion à Pusher.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
        },
        he: {
            pusher_unavailable: "Pusher אינו זמין.",
            pusher_auth_unavailable: "אימות Pusher נכשל.",
            pusher_connect_failed: "החיבור ל־Pusher נכשל.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
        },
        it: {
            pusher_unavailable: "Pusher non è disponibile.",
            pusher_auth_unavailable: "Autenticazione Pusher non riuscita.",
            pusher_connect_failed: "Connessione a Pusher non riuscita.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
        },
        ja: {
            pusher_unavailable: "Pusher を利用できません。",
            pusher_auth_unavailable: "Pusher の認証に失敗しました。",
            pusher_connect_failed: "Pusher への接続に失敗しました。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
        },
        nl: {
            pusher_unavailable: "Pusher is niet beschikbaar.",
            pusher_auth_unavailable: "Pusher-authenticatie mislukt.",
            pusher_connect_failed: "Verbinding met Pusher mislukt.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
        },
        pl: {
            pusher_unavailable: "Pusher jest niedostępny.",
            pusher_auth_unavailable: "Uwierzytelnienie Pusher nie powiodło się.",
            pusher_connect_failed: "Nie udało się połączyć z Pusher.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
        },
        pt: {
            pusher_unavailable: "Pusher indisponível.",
            pusher_auth_unavailable: "Falha na autenticação do Pusher.",
            pusher_connect_failed: "Falha ao conectar ao Pusher.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        "pt-br": {
            pusher_unavailable: "Pusher indisponível.",
            pusher_auth_unavailable: "Falha na autenticação do Pusher.",
            pusher_connect_failed: "Falha na conexão com o Pusher.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        ru: {
            pusher_unavailable: "Pusher недоступен.",
            pusher_auth_unavailable: "Сбой аутентификации Pusher.",
            pusher_connect_failed: "Не удалось подключиться к Pusher.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
        },
        tr: {
            pusher_unavailable: "Pusher kullanılamıyor.",
            pusher_auth_unavailable: "Pusher kimlik doğrulaması başarısız.",
            pusher_connect_failed: "Pusher'a bağlanılamadı.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
        },
        zh: {
            pusher_unavailable: "Pusher 不可用。",
            pusher_auth_unavailable: "Pusher 身份验证失败。",
            pusher_connect_failed: "连接 Pusher 失败。",
            plugin_unavailable: "未能加载所需的库。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=pusher.js.map