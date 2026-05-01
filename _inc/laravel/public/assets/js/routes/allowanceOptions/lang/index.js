(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            guard_unavailable: "هذا الإجراء غير متاح.",
        },
        da: {
            guard_unavailable: "Denne handling er ikke tilgængelig.",
        },
        de: {
            guard_unavailable: "Diese Aktion ist nicht verfügbar.",
        },
        en: {
            guard_unavailable: "This action is unavailable.",
        },
        es: {
            guard_unavailable: "Esta acción no está disponible.",
        },
        fr: {
            guard_unavailable: "Cette action n’est pas disponible.",
        },
        he: {
            guard_unavailable: "הפעולה הזו אינה זמינה.",
        },
        it: {
            guard_unavailable: "Questa azione non è disponibile.",
        },
        ja: {
            guard_unavailable: "この操作は利用できません。",
        },
        nl: {
            guard_unavailable: "Deze actie is niet beschikbaar.",
        },
        pl: {
            guard_unavailable: "Ta akcja jest niedostępna.",
        },
        pt: {
            guard_unavailable: "Esta ação não está disponível.",
        },
        "pt-br": {
            guard_unavailable: "Esta ação não está disponível.",
        },
        ru: {
            guard_unavailable: "Это действие недоступно.",
        },
        tr: {
            guard_unavailable: "Bu işlem kullanılamıyor.",
        },
        zh: {
            guard_unavailable: "此操作不可用。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();