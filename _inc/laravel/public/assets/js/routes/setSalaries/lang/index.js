(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            select_any_designation: "اختر أي مسمى وظيفي",
        },
        da: {
            select_any_designation: "Vælg en titel",
        },
        de: {
            select_any_designation: "Wählen Sie eine Bezeichnung",
        },
        en: {
            select_any_designation: "Select any Designation",
        },
        es: {
            select_any_designation: "Seleccione cualquier designación",
        },
        fr: {
            select_any_designation: "Sélectionnez une désignation",
        },
        he: {
            select_any_designation: "בחר כל תואר",
        },
        it: {
            select_any_designation: "Seleziona una qualifica",
        },
        ja: {
            select_any_designation: "任意の役職を選択",
        },
        nl: {
            select_any_designation: "Selecteer een functie",
        },
        pl: {
            select_any_designation: "Wybierz dowolne stanowisko",
        },
        pt: {
            select_any_designation: "Selecione qualquer designação",
        },
        "pt-br": {
            select_any_designation: "Selecione qualquer designação",
        },
        ru: {
            select_any_designation: "Выберите любое назначение",
        },
        tr: {
            select_any_designation: "Herhangi bir görev seçin",
        },
        zh: {
            select_any_designation: "选择任意职称",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();