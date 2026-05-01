(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { tags_unavailable: "لا يمكن تهيئة الوسوم" },
        da: { tags_unavailable: "Kan ikke initialisere tags" },
        de: { tags_unavailable: "Tags konnten nicht initialisiert werden" },
        en: { tags_unavailable: "Cannot initialize tags" },
        es: { tags_unavailable: "No se pueden inicializar etiquetas" },
        fr: { tags_unavailable: "Impossible d’initialiser les tags" },
        he: { tags_unavailable: "לא ניתן לאתחל תגים" },
        it: { tags_unavailable: "Impossibile inizializzare i tag" },
        ja: { tags_unavailable: "タグを初期化できません" },
        nl: { tags_unavailable: "Kan tags niet initialiseren" },
        pl: { tags_unavailable: "Nie można zainicjalizować tagów" },
        pt: { tags_unavailable: "Não foi possível inicializar as tags" },
        "pt-br": { tags_unavailable: "Não foi possível inicializar as tags" },
        ru: { tags_unavailable: "Не удалось инициализировать теги" },
        tr: { tags_unavailable: "Etiketler başlatılamıyor" },
        zh: { tags_unavailable: "无法初始化标签" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();