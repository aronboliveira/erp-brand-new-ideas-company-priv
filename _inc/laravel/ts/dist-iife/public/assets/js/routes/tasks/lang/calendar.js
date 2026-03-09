(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            calendar_unavailable: "تعذّر تحميل التقويم.",
            fetch_unavailable: "تعذّر جلب البيانات.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            calendar_unavailable: "Kalender kunne ikke indlæses.",
            fetch_unavailable: "Kunne ikke hente data.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            calendar_unavailable: "Kalender konnte nicht geladen werden.",
            fetch_unavailable: "Daten konnten nicht abgerufen werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            calendar_unavailable: "Calendar could not be loaded.",
            fetch_unavailable: "Failed to fetch data.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            calendar_unavailable: "No se pudo cargar el calendario.",
            fetch_unavailable: "No se pudieron obtener los datos.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            calendar_unavailable: "Impossible de charger le calendrier.",
            fetch_unavailable: "Échec de la récupération des données.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            calendar_unavailable: "לא ניתן היה לטעון את הלוח.",
            fetch_unavailable: "כשל בשליפת נתונים.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            calendar_unavailable: "Impossibile caricare il calendario.",
            fetch_unavailable: "Impossibile recuperare i dati.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            calendar_unavailable: "カレンダーを読み込めませんでした。",
            fetch_unavailable: "データの取得に失敗しました。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            calendar_unavailable: "Kalender kon niet worden geladen.",
            fetch_unavailable: "Gegevens ophalen is mislukt.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            calendar_unavailable: "Nie udało się załadować kalendarza.",
            fetch_unavailable: "Nie udało się pobrać danych.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            calendar_unavailable: "Não foi possível carregar o calendário.",
            fetch_unavailable: "Falha ao buscar os dados.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            calendar_unavailable: "Não foi possível carregar o calendário.",
            fetch_unavailable: "Falha ao buscar os dados.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            calendar_unavailable: "Не удалось загрузить календарь.",
            fetch_unavailable: "Не удалось получить данные.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            calendar_unavailable: "Takvim yüklenemedi.",
            fetch_unavailable: "Veriler alınamadı.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            calendar_unavailable: "无法加载日历。",
            fetch_unavailable: "获取数据失败。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();