(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: { calendar_data_unavailable: "لا يمكن تحميل بيانات التقويم" },
        da: { calendar_data_unavailable: "Kan ikke indlæse kalenderdata" },
        de: {
            calendar_data_unavailable: "Kalenderdaten konnten nicht geladen werden",
        },
        en: { calendar_data_unavailable: "Cannot load calendar data" },
        es: {
            calendar_data_unavailable: "No se pueden cargar los datos del calendario",
        },
        fr: {
            calendar_data_unavailable: "Impossible de charger les données du calendrier",
        },
        he: { calendar_data_unavailable: "לא ניתן לטעון נתוני לוח השנה" },
        it: {
            calendar_data_unavailable: "Impossibile caricare i dati del calendario",
        },
        ja: { calendar_data_unavailable: "カレンダーデータを読み込めません" },
        nl: { calendar_data_unavailable: "Kan kalendergegevens niet laden" },
        pl: { calendar_data_unavailable: "Nie można załadować danych kalendarza" },
        pt: {
            calendar_data_unavailable: "Não é possível carregar dados do calendário",
        },
        "pt-br": {
            calendar_data_unavailable: "Não é possível carregar dados do calendário",
        },
        ru: { calendar_data_unavailable: "Не удалось загрузить данные календаря" },
        tr: { calendar_data_unavailable: "Takvim verileri yüklenemiyor" },
        zh: { calendar_data_unavailable: "无法加载日历数据" },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=index.js.map