(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
            pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
            toggle_unavailable: "لا يمكن تبديل عامل التصفية الآن.",
            print_unavailable: "تعذّر بدء الطباعة.",
        },
        da: {
            plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
            pdf_unavailable: "Kunne ikke generere PDF lige nu.",
            toggle_unavailable: "Kan ikke skifte filter nu.",
            print_unavailable: "Kunne ikke starte udskrivning.",
        },
        de: {
            plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
            pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
            toggle_unavailable: "Filter kann derzeit nicht umgeschaltet werden.",
            print_unavailable: "Drucken konnte nicht gestartet werden.",
        },
        en: {
            plugin_unavailable: "A required library failed to load.",
            pdf_unavailable: "PDF export failed.",
            toggle_unavailable: "Cannot toggle filter right now.",
            print_unavailable: "Print could not start.",
        },
        es: {
            plugin_unavailable: "No se cargó una biblioteca requerida.",
            pdf_unavailable: "La exportación a PDF falló.",
            toggle_unavailable: "No se puede alternar el filtro ahora.",
            print_unavailable: "No se pudo iniciar la impresión.",
        },
        fr: {
            plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
            pdf_unavailable: "L’export PDF a échoué.",
            toggle_unavailable: "Impossible d’alterner le filtre maintenant.",
            print_unavailable: "Impossible de lancer l’impression.",
        },
        he: {
            plugin_unavailable: "ספרייה נדרשת לא נטענה.",
            pdf_unavailable: "ייצוא ה-PDF נכשל.",
            toggle_unavailable: "לא ניתן להחליף את המסנן כעת.",
            print_unavailable: "לא ניתן להתחיל הדפסה.",
        },
        it: {
            plugin_unavailable: "Una libreria richiesta non è stata caricata.",
            pdf_unavailable: "Esportazione PDF non riuscita.",
            toggle_unavailable: "Impossibile attivare/disattivare il filtro ora.",
            print_unavailable: "Impossibile avviare la stampa.",
        },
        ja: {
            plugin_unavailable: "必要なライブラリが読み込まれていません。",
            pdf_unavailable: "PDF の書き出しに失敗しました。",
            toggle_unavailable: "現在はフィルターを切り替えられません。",
            print_unavailable: "印刷を開始できませんでした。",
        },
        nl: {
            plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
            pdf_unavailable: "PDF-export is mislukt.",
            toggle_unavailable: "Kan filter nu niet schakelen.",
            print_unavailable: "Afdrukken kon niet worden gestart.",
        },
        pl: {
            plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
            pdf_unavailable: "Eksport do PDF nie powiódł się.",
            toggle_unavailable: "Nie można teraz przełączyć filtra.",
            print_unavailable: "Nie można uruchomić drukowania.",
        },
        pt: {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            pdf_unavailable: "Falha na exportação para PDF.",
            toggle_unavailable: "Não é possível alternar o filtro agora.",
            print_unavailable: "Não foi possível iniciar a impressão.",
        },
        "pt-br": {
            plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
            pdf_unavailable: "Falha ao exportar o PDF.",
            toggle_unavailable: "Não é possível alternar o filtro agora.",
            print_unavailable: "Não foi possível iniciar a impressão.",
        },
        ru: {
            plugin_unavailable: "Не загружена необходимая библиотека.",
            pdf_unavailable: "Не удалось выполнить экспорт PDF.",
            toggle_unavailable: "Невозможно переключить фильтр сейчас.",
            print_unavailable: "Не удалось запустить печать.",
        },
        tr: {
            plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
            pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
            toggle_unavailable: "Filtre şu anda değiştirilemiyor.",
            print_unavailable: "Yazdırma başlatılamadı.",
        },
        zh: {
            plugin_unavailable: "未能加载所需的库。",
            pdf_unavailable: "PDF 导出失败。",
            toggle_unavailable: "当前无法切换筛选器。",
            print_unavailable: "无法开始打印。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();