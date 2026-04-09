(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
            print_unavailable: "تعذّر بدء الطباعة.",
            toggle_unavailable: "لا يمكن تبديل عامل التصفية الآن.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            pdf_unavailable: "Kunne ikke generere PDF lige nu.",
            print_unavailable: "Kunne ikke starte udskrivning.",
            toggle_unavailable: "Kan ikke skifte filter nu.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
            print_unavailable: "Drucken konnte nicht gestartet werden.",
            toggle_unavailable: "Filter kann derzeit nicht umgeschaltet werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            pdf_unavailable: "PDF export failed.",
            print_unavailable: "Print could not start.",
            toggle_unavailable: "Cannot toggle filter right now.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            pdf_unavailable: "La exportación a PDF falló.",
            print_unavailable: "No se pudo iniciar la impresión.",
            toggle_unavailable: "No se puede alternar el filtro ahora.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            pdf_unavailable: "L’export PDF a échoué.",
            print_unavailable: "Impossible de lancer l’impression.",
            toggle_unavailable: "Impossible d’alterner le filtre maintenant.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            pdf_unavailable: "ייצוא ה-PDF נכשל.",
            print_unavailable: "לא ניתן להתחיל הדפסה.",
            toggle_unavailable: "לא ניתן להחליף את המסנן כעת.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            pdf_unavailable: "Esportazione PDF non riuscita.",
            print_unavailable: "Impossibile avviare la stampa.",
            toggle_unavailable: "Impossibile attivare/disattivare il filtro ora.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            pdf_unavailable: "PDF の書き出しに失敗しました。",
            print_unavailable: "印刷を開始できませんでした。",
            toggle_unavailable: "現在はフィルターを切り替えられません。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            pdf_unavailable: "PDF-export is mislukt.",
            print_unavailable: "Afdrukken kon niet worden gestart.",
            toggle_unavailable: "Kan filter nu niet schakelen.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            pdf_unavailable: "Eksport do PDF nie powiódł się.",
            print_unavailable: "Nie można uruchomić drukowania.",
            toggle_unavailable: "Nie można teraz przełączyć filtra.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            pdf_unavailable: "Falha na exportação para PDF.",
            print_unavailable: "Não foi possível iniciar a impressão.",
            toggle_unavailable: "Não é possível alternar o filtro agora.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            pdf_unavailable: "Falha ao exportar o PDF.",
            print_unavailable: "Não foi possível iniciar a impressão.",
            toggle_unavailable: "Não é possível alternar o filtro agora.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            pdf_unavailable: "Не удалось выполнить экспорт PDF.",
            print_unavailable: "Не удалось запустить печать.",
            toggle_unavailable: "Невозможно переключить фильтр сейчас.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
            print_unavailable: "Yazdırma başlatılamadı.",
            toggle_unavailable: "Filtre şu anda değiştirilemiyor.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            pdf_unavailable: "PDF 导出失败。",
            print_unavailable: "无法开始打印。",
            toggle_unavailable: "当前无法切换筛选器。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=pdf.js.map