(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            pdf_unavailable: "تعذّر إنشاء ملف PDF.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
        },
        da: {
            pdf_unavailable: "Kunne ikke generere PDF.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
        },
        de: {
            pdf_unavailable: "PDF konnte nicht erstellt werden.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
        },
        en: {
            pdf_unavailable: "Could not generate PDF.",
            plugin_unavailable: "A required library failed to load.",
        },
        es: {
            pdf_unavailable: "No se pudo generar el PDF.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
        },
        fr: {
            pdf_unavailable: "Impossible de générer le PDF.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
        },
        he: {
            pdf_unavailable: "לא ניתן היה ליצור PDF.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
        },
        it: {
            pdf_unavailable: "Impossibile generare il PDF.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
        },
        ja: {
            pdf_unavailable: "PDF を生成できませんでした。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
        },
        nl: {
            pdf_unavailable: "PDF kon niet worden gegenereerd.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
        },
        pl: {
            pdf_unavailable: "Nie udało się wygenerować PDF.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
        },
        pt: {
            pdf_unavailable: "Não foi possível gerar o PDF.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        "pt-br": {
            pdf_unavailable: "Não foi possível gerar o PDF.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        ru: {
            pdf_unavailable: "Не удалось создать PDF.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
        },
        tr: {
            pdf_unavailable: "PDF oluşturulamadı.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
        },
        zh: {
            pdf_unavailable: "无法生成 PDF。",
            plugin_unavailable: "未能加载所需的库。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();