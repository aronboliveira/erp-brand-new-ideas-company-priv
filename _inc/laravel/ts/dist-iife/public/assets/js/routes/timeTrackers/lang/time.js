(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            time_unavailable: "عنصر اختيار الوقت غير متاح.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
        },
        da: {
            time_unavailable: "Tidsvælgeren er ikke tilgængelig.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
        },
        de: {
            time_unavailable: "Zeitauswahl ist nicht verfügbar.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
        },
        en: {
            time_unavailable: "Time picker is unavailable.",
            plugin_unavailable: "A required library failed to load.",
        },
        es: {
            time_unavailable: "El selector de hora no está disponible.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
        },
        fr: {
            time_unavailable: "Le sélecteur d’heure est indisponible.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
        },
        he: {
            time_unavailable: "בורר הזמן אינו זמין.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
        },
        it: {
            time_unavailable: "Selettore orario non disponibile.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
        },
        ja: {
            time_unavailable: "時刻ピッカーは利用できません。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
        },
        nl: {
            time_unavailable: "Tijdkiezer is niet beschikbaar.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
        },
        pl: {
            time_unavailable: "Selektor czasu jest niedostępny.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
        },
        pt: {
            time_unavailable: "Seletor de horário indisponível.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        "pt-br": {
            time_unavailable: "Seletor de horário indisponível.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        ru: {
            time_unavailable: "Выбор времени недоступен.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
        },
        tr: {
            time_unavailable: "Zaman seçici kullanılamıyor.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
        },
        zh: {
            time_unavailable: "时间选择器不可用。",
            plugin_unavailable: "未能加载所需的库。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();