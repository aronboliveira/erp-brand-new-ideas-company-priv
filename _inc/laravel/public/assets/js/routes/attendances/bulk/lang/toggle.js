(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            present_all_toggle_failed: "فشل تبديل جميع خانات الاختيار.",
            present_toggle_failed: "فشل تبديل خانة الحضور.",
        },
        da: {
            present_all_toggle_failed: "Kunne ikke slå alle afkrydsningsfelter til/fra.",
            present_toggle_failed: "Kunne ikke skifte tilstedeværelsesfelt.",
        },
        de: {
            present_all_toggle_failed: "Konnte nicht alle Kontrollkästchen umschalten.",
            present_toggle_failed: "Konnte das Anwesenheitskontrollkästchen nicht umschalten.",
        },
        en: {
            present_all_toggle_failed: "Failed to toggle all checkboxes.",
            present_toggle_failed: "Failed to toggle attendance checkbox.",
        },
        es: {
            present_all_toggle_failed: "Error al alternar todas las casillas.",
            present_toggle_failed: "Error al alternar la casilla de asistencia.",
        },
        fr: {
            present_all_toggle_failed: "Échec du basculement de toutes les cases.",
            present_toggle_failed: "Échec du basculement de la case de présence.",
        },
        he: {
            present_all_toggle_failed: "לא ניתן להחליף את כל תיבות הסימון.",
            present_toggle_failed: "לא ניתן להחליף את תיבת הסימון של נוכחות.",
        },
        it: {
            present_all_toggle_failed: "Impossibile attivare/disattivare tutte le caselle.",
            present_toggle_failed: "Impossibile attivare/disattivare la casella di presenza.",
        },
        ja: {
            present_all_toggle_failed: "すべてのチェックボックスの切り替えに失敗しました。",
            present_toggle_failed: "出席チェックボックスの切り替えに失敗しました。",
        },
        nl: {
            present_all_toggle_failed: "Kan niet alle selectievakjes wisselen.",
            present_toggle_failed: "Kan selectievakje voor aanwezigheid niet wisselen.",
        },
        pl: {
            present_all_toggle_failed: "Nie udało się przełączyć wszystkich pól wyboru.",
            present_toggle_failed: "Nie udało się przełączyć pola wyboru obecności.",
        },
        pt: {
            present_all_toggle_failed: "Falha ao alternar todas as caixas de seleção.",
            present_toggle_failed: "Falha ao alternar a caixa de presença.",
        },
        "pt-br": {
            present_all_toggle_failed: "Falha ao alternar todas as caixas de seleção.",
            present_toggle_failed: "Falha ao alternar a caixa de presença.",
        },
        ru: {
            present_all_toggle_failed: "Не удалось переключить все флажки.",
            present_toggle_failed: "Не удалось переключить флажок присутствия.",
        },
        tr: {
            present_all_toggle_failed: "Tüm onay kutuları değiştirilemedi.",
            present_toggle_failed: "Yoklama onay kutusu değiştirilemedi.",
        },
        zh: {
            present_all_toggle_failed: "无法切换所有复选框。",
            present_toggle_failed: "无法切换出席复选框。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=toggle.js.map