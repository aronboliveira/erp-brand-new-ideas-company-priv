(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            calendar_unavailable: "تعذّر تحميل التقويم الآن.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            endpoint_unavailable: "المسار المطلوب غير متاح.",
            modal_unavailable: "تعذّر فتح النافذة المنبثقة.",
        },
        da: {
            calendar_unavailable: "Kan ikke indlæse kalenderen lige nu.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            endpoint_unavailable: "Den ønskede sti er ikke tilgængelig.",
            modal_unavailable: "Kunne ikke åbne modal.",
        },
        de: {
            calendar_unavailable: "Kalender konnte derzeit nicht geladen werden.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            endpoint_unavailable: "Angeforderter Endpunkt ist nicht verfügbar.",
            modal_unavailable: "Modal konnte nicht geöffnet werden.",
        },
        en: {
            calendar_unavailable: "Calendar failed to load.",
            plugin_unavailable: "A required library failed to load.",
            endpoint_unavailable: "Requested endpoint is unavailable.",
            modal_unavailable: "Could not open the modal.",
        },
        es: {
            calendar_unavailable: "No se pudo cargar el calendario.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            endpoint_unavailable: "El endpoint solicitado no está disponible.",
            modal_unavailable: "No se pudo abrir la ventana modal.",
        },
        fr: {
            calendar_unavailable: "Échec du chargement du calendrier.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            endpoint_unavailable: "Le point de terminaison demandé est indisponible.",
            modal_unavailable: "Impossible d’ouvrir la fenêtre modale.",
        },
        he: {
            calendar_unavailable: "לא ניתן היה לטעון את היומן.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            endpoint_unavailable: "נקודת הקצה המבוקשת אינה זמינה.",
            modal_unavailable: "לא ניתן היה לפתוח את החלון הקופץ.",
        },
        it: {
            calendar_unavailable: "Impossibile caricare il calendario.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            endpoint_unavailable: "L’endpoint richiesto non è disponibile.",
            modal_unavailable: "Impossibile aprire la modale.",
        },
        ja: {
            calendar_unavailable: "カレンダーを読み込めませんでした。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            endpoint_unavailable: "要求されたエンドポイントは利用できません。",
            modal_unavailable: "モーダルを開けませんでした。",
        },
        nl: {
            calendar_unavailable: "Kalender kon niet worden geladen.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            endpoint_unavailable: "Aangevraagde endpoint is niet beschikbaar.",
            modal_unavailable: "Kon de modal niet openen.",
        },
        pl: {
            calendar_unavailable: "Nie udało się wczytać kalendarza.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            endpoint_unavailable: "Żądany endpoint jest niedostępny.",
            modal_unavailable: "Nie można otworzyć okna modalnego.",
        },
        pt: {
            calendar_unavailable: "Falha ao carregar o calendário.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            endpoint_unavailable: "Endpoint solicitado indisponível.",
            modal_unavailable: "Não foi possível abrir o modal.",
        },
        "pt-br": {
            calendar_unavailable: "Falha ao carregar o calendário.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            endpoint_unavailable: "Endpoint solicitado indisponível.",
            modal_unavailable: "Não foi possível abrir o modal.",
        },
        ru: {
            calendar_unavailable: "Не удалось загрузить календарь.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
            endpoint_unavailable: "Запрошенная точка недоступна.",
            modal_unavailable: "Не удалось открыть модальное окно.",
        },
        tr: {
            calendar_unavailable: "Takvim yüklenemedi.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            endpoint_unavailable: "İstenen uç nokta kullanılamıyor.",
            modal_unavailable: "Modal açılamadı.",
        },
        zh: {
            calendar_unavailable: "日历加载失败。",
            plugin_unavailable: "未能加载所需的库。",
            endpoint_unavailable: "请求的端点不可用。",
            modal_unavailable: "无法打开模态框。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();