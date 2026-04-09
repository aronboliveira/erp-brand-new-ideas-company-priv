(function () {
    if (!window.translations)
        window.translations = {};
    const t = {
        ar: { ld_unavailable: "الإجراء غير متاح." },
        da: { ld_unavailable: "Handling ikke tilgængelig." },
        de: { ld_unavailable: "Aktion nicht verfügbar." },
        en: { ld_unavailable: "Action unavailable." },
        es: { ld_unavailable: "Acción no disponible." },
        fr: { ld_unavailable: "Action indisponible." },
        he: { ld_unavailable: "הפעולה אינה זמינה." },
        it: { ld_unavailable: "Azione non disponibile." },
        ja: { ld_unavailable: "操作を利用できません。" },
        nl: { ld_unavailable: "Actie niet beschikbaar." },
        pl: { ld_unavailable: "Działanie niedostępne." },
        pt: { ld_unavailable: "Ação indisponível." },
        "pt-br": { ld_unavailable: "Ação indisponível." },
        ru: { ld_unavailable: "Действие недоступно." },
        tr: { ld_unavailable: "İşlem kullanılamıyor." },
        zh: { ld_unavailable: "操作不可用。" },
    };
    Object.keys(t).forEach(k => {
        window.translations[k] = Object.assign({}, window.translations[k] || {}, t[k]);
    });
})();
//# sourceMappingURL=create.js.map