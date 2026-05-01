(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            print_unavailable: "تعذّر فتح نافذة الطباعة.",
        },
        da: {
            pdf_unavailable: "Kunne ikke generere PDF lige nu.",
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            print_unavailable: "Udskriftsvinduet kunne ikke åbnes.",
        },
        de: {
            pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            print_unavailable: "Der Druckdialog konnte nicht geöffnet werden.",
        },
        en: {
            pdf_unavailable: "PDF export failed.",
            plugin_unavailable: "A required library failed to load.",
            print_unavailable: "Could not open the print dialog.",
        },
        es: {
            pdf_unavailable: "La exportación a PDF falló.",
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            print_unavailable: "No se pudo abrir el cuadro de impresión.",
        },
        fr: {
            pdf_unavailable: "L’export PDF a échoué.",
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            print_unavailable: "Impossible d’ouvrir la boîte d’impression.",
        },
        he: {
            pdf_unavailable: "ייצוא ה-PDF נכשל.",
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            print_unavailable: "לא ניתן היה לפתוח את תיבת ההדפסה.",
        },
        it: {
            pdf_unavailable: "Esportazione PDF non riuscita.",
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            print_unavailable: "Impossibile aprire la finestra di stampa.",
        },
        ja: {
            pdf_unavailable: "PDF の書き出しに失敗しました。",
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            print_unavailable: "印刷ダイアログを開けませんでした。",
        },
        nl: {
            pdf_unavailable: "PDF-export is mislukt.",
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            print_unavailable: "Het afdrukvenster kon niet worden geopend.",
        },
        pl: {
            pdf_unavailable: "Eksport do PDF nie powiódł się.",
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            print_unavailable: "Nie można otworzyć okna drukowania.",
        },
        pt: {
            pdf_unavailable: "Falha na exportação para PDF.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            print_unavailable: "Não foi possível abrir a janela de impressão.",
        },
        "pt-br": {
            pdf_unavailable: "Falha ao exportar o PDF.",
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            print_unavailable: "Não foi possível abrir a janela de impressão.",
        },
        ru: {
            pdf_unavailable: "Не удалось выполнить экспорт PDF.",
            plugin_unavailable: "Не загружена необходимая библиотека.",
            print_unavailable: "Не удалось открыть окно печати.",
        },
        tr: {
            pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            print_unavailable: "Yazdırma penceresi açılamadı.",
        },
        zh: {
            pdf_unavailable: "PDF 导出失败。",
            plugin_unavailable: "未能加载所需的库。",
            print_unavailable: "无法打开打印对话框。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();