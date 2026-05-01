(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
        },
        da: {
            pdf_unavailable: "Kunne ikke generere PDF lige nu.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
        },
        de: {
            pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
        },
        en: {
            pdf_unavailable: "PDF export failed.",
            plugin_unavailable: "A required library failed to load.",
        },
        es: {
            pdf_unavailable: "La exportación a PDF falló.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
        },
        fr: {
            pdf_unavailable: "L’export PDF a échoué.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
        },
        he: {
            pdf_unavailable: "ייצוא ה-PDF נכשל.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
        },
        it: {
            pdf_unavailable: "Esportazione PDF non riuscita.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
        },
        ja: {
            pdf_unavailable: "PDF の書き出しに失敗しました。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
        },
        nl: {
            pdf_unavailable: "PDF-export is mislukt.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
        },
        pl: {
            pdf_unavailable: "Eksport do PDF nie powiódł się.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
        },
        pt: {
            pdf_unavailable: "Falha na exportação para PDF.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        "pt-br": {
            pdf_unavailable: "Falha ao exportar o PDF.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
        },
        ru: {
            pdf_unavailable: "Не удалось выполнить экспорт PDF.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
        },
        tr: {
            pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
        },
        zh: {
            pdf_unavailable: "PDF 导出失败。",
            plugin_unavailable: "未能加载所需的库。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();