(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            pdf_unavailable: "تعذّر إنشاء ملف PDF.",
            close_unavailable: "تعذّر إغلاق النافذة.",
        },
        da: {
            pdf_unavailable: "Kunne ikke oprette PDF.",
            close_unavailable: "Kunne ikke lukke vinduet.",
        },
        de: {
            pdf_unavailable: "PDF konnte nicht erstellt werden.",
            close_unavailable: "Fenster konnte nicht geschlossen werden.",
        },
        en: {
            pdf_unavailable: "Could not generate the PDF.",
            close_unavailable: "Could not close the window.",
        },
        es: {
            pdf_unavailable: "No se pudo generar el PDF.",
            close_unavailable: "No se pudo cerrar la ventana.",
        },
        fr: {
            pdf_unavailable: "Impossible de générer le PDF.",
            close_unavailable: "Impossible de fermer la fenêtre.",
        },
        he: {
            pdf_unavailable: "לא ניתן ליצור PDF.",
            close_unavailable: "לא ניתן לסגור את החלון.",
        },
        it: {
            pdf_unavailable: "Impossibile generare il PDF.",
            close_unavailable: "Impossibile chiudere la finestra.",
        },
        ja: {
            pdf_unavailable: "PDF を生成できませんでした。",
            close_unavailable: "ウィンドウを閉じられませんでした。",
        },
        nl: {
            pdf_unavailable: "PDF kon niet worden gegenereerd.",
            close_unavailable: "Kon het venster niet sluiten.",
        },
        pl: {
            pdf_unavailable: "Nie udało się wygenerować PDF.",
            close_unavailable: "Nie udało się zamknąć okna.",
        },
        pt: {
            pdf_unavailable: "Não foi possível gerar o PDF.",
            close_unavailable: "Não foi possível fechar a janela.",
        },
        "pt-br": {
            pdf_unavailable: "Não foi possível gerar o PDF.",
            close_unavailable: "Não foi possível fechar a janela.",
        },
        ru: {
            pdf_unavailable: "Не удалось создать PDF.",
            close_unavailable: "Не удалось закрыть окно.",
        },
        tr: {
            pdf_unavailable: "PDF oluşturulamadı.",
            close_unavailable: "Pencere kapatılamadı.",
        },
        zh: {
            pdf_unavailable: "无法生成 PDF。",
            close_unavailable: "无法关闭窗口。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();