(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        en: { date_picker_init_failed: "Failed to initialize date picker." },
        ar: { date_picker_init_failed: "فشل تهيئة منتقي التاريخ." },
        da: { date_picker_init_failed: "Kunne ikke starte datovælgeren." },
        de: {
            date_picker_init_failed: "Initialisierung des Datumsauswahl fehlgeschlagen.",
        },
        es: {
            date_picker_init_failed: "Error al inicializar el selector de fecha.",
        },
        fr: {
            date_picker_init_failed: "Échec de l’initialisation du sélecteur de date.",
        },
        he: { date_picker_init_failed: "אתחול בוחר התאריך נכשל." },
        it: {
            date_picker_init_failed: "Impossibile inizializzare il selettore data.",
        },
        ja: { date_picker_init_failed: "日付ピッカーの初期化に失敗しました。" },
        nl: { date_picker_init_failed: "Initialisatie van de datakeuze mislukt." },
        pl: {
            date_picker_init_failed: "Nie udało się zainicjalizować selektora daty.",
        },
        pt: { date_picker_init_failed: "Falha ao inicializar o seletor de data." },
        "pt-br": { date_picker_init_failed: "Falha ao iniciar o seletor de data." },
        ru: { date_picker_init_failed: "Не удалось инициализировать выбор даты." },
        tr: { date_picker_init_failed: "Tarih seçici başlatılamadı." },
        zh: { date_picker_init_failed: "初始化日期选择器失败。" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();