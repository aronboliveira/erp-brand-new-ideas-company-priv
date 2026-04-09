(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            first_plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
        },
        da: {
            first_plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            pdf_unavailable: "Kunne ikke generere PDF lige nu.",
        },
        de: {
            first_plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
        },
        en: {
            first_plugin_unavailable: "A required library failed to load.",
            pdf_unavailable: "PDF export failed.",
        },
        es: {
            first_plugin_unavailable: "No se cargó una biblioteca requerida.",
            pdf_unavailable: "La exportación a PDF falló.",
        },
        fr: {
            first_plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            pdf_unavailable: "L’export PDF a échoué.",
        },
        he: {
            first_plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            pdf_unavailable: "ייצוא ה-PDF נכשל.",
        },
        it: {
            first_plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            pdf_unavailable: "Esportazione PDF non riuscita.",
        },
        ja: {
            first_plugin_unavailable: "必要なライブラリが読み込まれていません。",
            pdf_unavailable: "PDF の書き出しに失敗しました。",
        },
        nl: {
            first_plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            pdf_unavailable: "PDF-export is mislukt.",
        },
        pl: {
            first_plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            pdf_unavailable: "Eksport do PDF nie powiódł się.",
        },
        pt: {
            first_plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            pdf_unavailable: "Falha na exportação para PDF.",
        },
        "pt-br": {
            first_plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            pdf_unavailable: "Falha ao exportar o PDF.",
        },
        ru: {
            first_plugin_unavailable: "Не загружена необходимая библиотека.",
            pdf_unavailable: "Не удалось выполнить экспорт PDF.",
        },
        tr: {
            first_plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
        },
        zh: {
            first_plugin_unavailable: "未能加载所需的库。",
            pdf_unavailable: "PDF 导出失败。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=pdf.js.map