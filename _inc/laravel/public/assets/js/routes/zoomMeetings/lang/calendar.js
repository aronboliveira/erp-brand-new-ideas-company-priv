(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            calendar_unavailable: "تعذّر تحميل بيانات التقويم.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
        },
        da: {
            calendar_unavailable: "Kunne ikke indlæse kalenderdata.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
        },
        de: {
            calendar_unavailable: "Kalenderdaten konnten nicht geladen werden.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
        },
        en: {
            calendar_unavailable: "Could not load calendar data.",
            plugin_unavailable: "A required library failed to load.",
        },
        es: {
            calendar_unavailable: "No se pudieron cargar los datos del calendario.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
        },
        fr: {
            calendar_unavailable: "Impossible de charger les données du calendrier.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
        },
        he: {
            calendar_unavailable: "לא ניתן היה לטעון נתוני לוח שנה.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
        },
        it: {
            calendar_unavailable: "Impossibile caricare i dati del calendario.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
        },
        ja: {
            calendar_unavailable: "カレンダーのデータを読み込めませんでした。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
        },
        nl: {
            calendar_unavailable: "Kon kalendergegevens niet laden.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
        },
        pl: {
            calendar_unavailable: "Nie można wczytać danych kalendarza.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
        },
        pt: {
            calendar_unavailable: "Não foi possível carregar os dados do calendário.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        "pt-br": {
            calendar_unavailable: "Não foi possível carregar os dados do calendário.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        ru: {
            calendar_unavailable: "Не удалось загрузить данные календаря.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
        },
        tr: {
            calendar_unavailable: "Takvim verileri yüklenemedi.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
        },
        zh: {
            calendar_unavailable: "无法加载日历数据。",
            plugin_unavailable: "未能加载所需的库。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();