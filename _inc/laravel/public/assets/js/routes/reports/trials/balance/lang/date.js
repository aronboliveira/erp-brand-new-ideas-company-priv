(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            date_sync_failed: "تعذّر مزامنة التواريخ.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
        },
        da: {
            date_sync_failed: "Kunne ikke synkronisere datoerne.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
        },
        de: {
            date_sync_failed: "Datumswerte konnten nicht synchronisiert werden.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
        },
        en: {
            date_sync_failed: "Failed to sync dates.",
            plugin_unavailable: "A required library failed to load.",
        },
        es: {
            date_sync_failed: "No se pudieron sincronizar las fechas.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
        },
        fr: {
            date_sync_failed: "Échec de la synchronisation des dates.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
        },
        he: {
            date_sync_failed: "סנכרון התאריכים נכשל.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
        },
        it: {
            date_sync_failed: "Sincronizzazione delle date non riuscita.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
        },
        ja: {
            date_sync_failed: "日付の同期に失敗しました。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
        },
        nl: {
            date_sync_failed: "Datumsynchronisatie is mislukt.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
        },
        pl: {
            date_sync_failed: "Nie udało się zsynchronizować dat.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
        },
        pt: {
            date_sync_failed: "Falha ao sincronizar as datas.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        "pt-br": {
            date_sync_failed: "Falha ao sincronizar as datas.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        ru: {
            date_sync_failed: "Не удалось синхронизировать даты.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
        },
        tr: {
            date_sync_failed: "Tarihler eşitlenemedi.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
        },
        zh: {
            date_sync_failed: "日期同步失败。",
            plugin_unavailable: "未能加载所需的库。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=date.js.map