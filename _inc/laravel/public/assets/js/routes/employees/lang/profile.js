(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { employee_fetch_unavailable: "فشل جلب المسميات الوظيفية." },
        da: { employee_fetch_unavailable: "Kunne ikke hente betegnelse." },
        de: {
            employee_fetch_unavailable: "Abrufen der Bezeichnungen fehlgeschlagen.",
        },
        en: { employee_fetch_unavailable: "Failed to fetch designations." },
        es: { employee_fetch_unavailable: "Error al obtener designaciones." },
        fr: {
            employee_fetch_unavailable: "Échec de la récupération des intitulés.",
        },
        he: {
            employee_fetch_unavailable: "לא ניתן לאחזר תפקידים.",
        },
        it: { employee_fetch_unavailable: "Impossibile recuperare le mansioni." },
        ja: { employee_fetch_unavailable: "役職を取得できませんでした。" },
        nl: { employee_fetch_unavailable: "Ophalen van functies mislukt." },
        pl: { employee_fetch_unavailable: "Nie udało się pobrać stanowisk." },
        pt: { employee_fetch_unavailable: "Falha ao obter designações." },
        "pt-br": { employee_fetch_unavailable: "Falha ao buscar cargos." },
        ru: { employee_fetch_unavailable: "Не удалось получить должности." },
        tr: { employee_fetch_unavailable: "Unvanlar alınamadı." },
        zh: { employee_fetch_unavailable: "获取职位失败。" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=profile.js.map