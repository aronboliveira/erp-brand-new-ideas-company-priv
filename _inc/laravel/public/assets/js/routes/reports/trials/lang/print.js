(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { print_unavailable: "تعذّر فتح نافذة الطباعة." },
        da: { print_unavailable: "Udskriftsvinduet kunne ikke åbnes." },
        de: { print_unavailable: "Der Druckdialog konnte nicht geöffnet werden." },
        en: { print_unavailable: "Could not open the print dialog." },
        es: { print_unavailable: "No se pudo abrir el cuadro de impresión." },
        fr: { print_unavailable: "Impossible d’ouvrir la boîte d’impression." },
        he: { print_unavailable: "לא ניתן היה לפתוח את תיבת ההדפסה." },
        it: { print_unavailable: "Impossibile aprire la finestra di stampa." },
        ja: { print_unavailable: "印刷ダイアログを開けませんでした。" },
        nl: { print_unavailable: "Het afdrukvenster kon niet worden geopend." },
        pl: { print_unavailable: "Nie można otworzyć okna drukowania." },
        pt: { print_unavailable: "Não foi possível abrir a janela de impressão." },
        "pt-br": {
            print_unavailable: "Não foi possível abrir a janela de impressão.",
        },
        ru: { print_unavailable: "Не удалось открыть окно печати." },
        tr: { print_unavailable: "Yazdırma penceresi açılamadı." },
        zh: { print_unavailable: "无法打开打印对话框。" },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();