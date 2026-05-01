(function() {
"use strict";
if (!window.translations) {
    window.translations = {
        ar: { print_unavailable: "تعذّر تنفيذ الطباعة." },
        da: { print_unavailable: "Udskrivning kunne ikke gennemføres." },
        de: { print_unavailable: "Drucken konnte nicht ausgeführt werden." },
        en: { print_unavailable: "Unable to execute print." },
        es: { print_unavailable: "No se pudo ejecutar la impresión." },
        fr: { print_unavailable: "Impossible d’exécuter l’impression." },
        he: { print_unavailable: "לא ניתן לבצע הדפסה." },
        it: { print_unavailable: "Impossibile eseguire la stampa." },
        ja: { print_unavailable: "印刷を実行できませんでした。" },
        nl: { print_unavailable: "Afdrukken kan niet worden uitgevoerd." },
        pl: { print_unavailable: "Nie można wykonać drukowania." },
        pt: { print_unavailable: "Não foi possível executar a impressão." },
        "pt-br": { print_unavailable: "Não foi possível executar a impressão." },
        ru: { print_unavailable: "Не удалось выполнить печать." },
        tr: { print_unavailable: "Yazdırma gerçekleştirilemedi." },
        zh: { print_unavailable: "无法执行打印。" },
    };
}
else {
    const add = {
        ar: { print_unavailable: "تعذّر تنفيذ الطباعة." },
        da: { print_unavailable: "Udskrivning kunne ikke gennemføres." },
        de: { print_unavailable: "Drucken konnte nicht ausgeführt werden." },
        en: { print_unavailable: "Unable to execute print." },
        es: { print_unavailable: "No se pudo ejecutar la impresión." },
        fr: { print_unavailable: "Impossible d’exécuter l’impression." },
        he: { print_unavailable: "לא ניתן לבצע הדפסה." },
        it: { print_unavailable: "Impossibile eseguire la stampa." },
        ja: { print_unavailable: "印刷を実行できませんでした。" },
        nl: { print_unavailable: "Afdrukken kan niet worden uitgevoerd." },
        pl: { print_unavailable: "Nie można wykonać drukowania." },
        pt: { print_unavailable: "Não foi possível executar a impressão." },
        "pt-br": { print_unavailable: "Não foi possível executar a impressão." },
        ru: { print_unavailable: "Не удалось выполнить печать." },
        tr: { print_unavailable: "Yazdırma gerçekleştirilemedi." },
        zh: { print_unavailable: "无法执行打印。" },
    };
    Object.keys(add).forEach(k => (window.translations[k] = Object.assign({}, window.translations[k] || {}, add[k])));
}
})();