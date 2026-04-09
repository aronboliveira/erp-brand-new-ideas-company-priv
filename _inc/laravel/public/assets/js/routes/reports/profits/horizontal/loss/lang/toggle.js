(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { toggle_unavailable: "لا يمكن تبديل عامل التصفية الآن." },
        da: { toggle_unavailable: "Kan ikke skifte filter nu." },
        de: {
            toggle_unavailable: "Filter kann derzeit nicht umgeschaltet werden.",
        },
        en: { toggle_unavailable: "Cannot toggle filter right now." },
        es: { toggle_unavailable: "No se puede alternar el filtro ahora." },
        fr: { toggle_unavailable: "Impossible d’alterner le filtre maintenant." },
        he: { toggle_unavailable: "לא ניתן להחליף את המסנן כעת." },
        it: {
            toggle_unavailable: "Impossibile attivare/disattivare il filtro ora.",
        },
        ja: { toggle_unavailable: "現在はフィルターを切り替えられません。" },
        nl: { toggle_unavailable: "Kan filter nu niet schakelen." },
        pl: { toggle_unavailable: "Nie można teraz przełączyć filtra." },
        pt: { toggle_unavailable: "Não é possível alternar o filtro agora." },
        "pt-br": { toggle_unavailable: "Não é possível alternar o filtro agora." },
        ru: { toggle_unavailable: "Невозможно переключить фильтр сейчас." },
        tr: { toggle_unavailable: "Filtre şu anda değiştirilemiyor." },
        zh: { toggle_unavailable: "当前无法切换筛选器。" },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=toggle.js.map