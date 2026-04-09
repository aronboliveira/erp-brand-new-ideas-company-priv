(function () {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            calendar_unavailable: "تعذّر تحميل التقويم.",
            department_unavailable: "تعذّر تحميل الأقسام.",
            employee_unavailable: "تعذّر تحميل الموظفين.",
            element_unavailable: "العنصر المطلوب غير موجود.",
            request_failed: "فشل الطلب.",
        },
        da: {
            calendar_unavailable: "Kunne ikke indlæse kalenderen.",
            department_unavailable: "Kunne ikke indlæse afdelinger.",
            employee_unavailable: "Kunne ikke indlæse medarbejdere.",
            element_unavailable: "Påkrævet element mangler.",
            request_failed: "Forespørgslen mislykkedes.",
        },
        de: {
            calendar_unavailable: "Kalender konnte nicht geladen werden.",
            department_unavailable: "Abteilungen konnten nicht geladen werden.",
            employee_unavailable: "Mitarbeiter konnten nicht geladen werden.",
            element_unavailable: "Erforderliches Element fehlt.",
            request_failed: "Anfrage fehlgeschlagen.",
        },
        en: {
            calendar_unavailable: "Could not load the calendar.",
            department_unavailable: "Could not load departments.",
            employee_unavailable: "Could not load employees.",
            element_unavailable: "Required element is missing.",
            request_failed: "The request failed.",
        },
        es: {
            calendar_unavailable: "No se pudo cargar el calendario.",
            department_unavailable: "No se pudieron cargar los departamentos.",
            employee_unavailable: "No se pudieron cargar los empleados.",
            element_unavailable: "Falta el elemento requerido.",
            request_failed: "La solicitud falló.",
        },
        fr: {
            calendar_unavailable: "Impossible de charger le calendrier.",
            department_unavailable: "Impossible de charger les départements.",
            employee_unavailable: "Impossible de charger les employés.",
            element_unavailable: "Élément requis manquant.",
            request_failed: "La requête a échoué.",
        },
        he: {
            calendar_unavailable: "לא ניתן לטעון את היומן.",
            department_unavailable: "לא ניתן לטעון מחלקות.",
            employee_unavailable: "לא ניתן לטעון עובדים.",
            element_unavailable: "האלמנט הנדרש חסר.",
            request_failed: "הבקשה נכשלה.",
        },
        it: {
            calendar_unavailable: "Impossibile caricare il calendario.",
            department_unavailable: "Impossibile caricare i reparti.",
            employee_unavailable: "Impossibile caricare i dipendenti.",
            element_unavailable: "Elemento richiesto mancante.",
            request_failed: "Richiesta non riuscita.",
        },
        ja: {
            calendar_unavailable: "カレンダーを読み込めませんでした。",
            department_unavailable: "部署を読み込めませんでした。",
            employee_unavailable: "従業員を読み込めませんでした。",
            element_unavailable: "必要な要素が見つかりません。",
            request_failed: "リクエストに失敗しました。",
        },
        nl: {
            calendar_unavailable: "Kan de kalender niet laden.",
            department_unavailable: "Afdelingen konden niet worden geladen.",
            employee_unavailable: "Werknemers konden niet worden geladen.",
            element_unavailable: "Vereist element ontbreekt.",
            request_failed: "Aanvraag mislukt.",
        },
        pl: {
            calendar_unavailable: "Nie można załadować kalendarza.",
            department_unavailable: "Nie można załadować działów.",
            employee_unavailable: "Nie można załadować pracowników.",
            element_unavailable: "Brakuje wymaganego elementu.",
            request_failed: "Żądanie nie powiodło się.",
        },
        pt: {
            calendar_unavailable: "Não foi possível carregar o calendário.",
            department_unavailable: "Não foi possível carregar os departamentos.",
            employee_unavailable: "Não foi possível carregar os colaboradores.",
            element_unavailable: "Elemento necessário ausente.",
            request_failed: "A solicitação falhou.",
        },
        "pt-br": {
            calendar_unavailable: "Não foi possível carregar o calendário.",
            department_unavailable: "Não foi possível carregar os departamentos.",
            employee_unavailable: "Não foi possível carregar os colaboradores.",
            element_unavailable: "Elemento obrigatório ausente.",
            request_failed: "A solicitação falhou.",
        },
        ru: {
            calendar_unavailable: "Не удалось загрузить календарь.",
            department_unavailable: "Не удалось загрузить отделы.",
            employee_unavailable: "Не удалось загрузить сотрудников.",
            element_unavailable: "Отсутствует необходимый элемент.",
            request_failed: "Запрос не выполнен.",
        },
        tr: {
            calendar_unavailable: "Takvim yüklenemedi.",
            department_unavailable: "Birimler yüklenemedi.",
            employee_unavailable: "Çalışanlar yüklenemedi.",
            element_unavailable: "Gerekli öğe eksik.",
            request_failed: "İstek başarısız oldu.",
        },
        zh: {
            calendar_unavailable: "无法加载日历。",
            department_unavailable: "无法加载部门。",
            employee_unavailable: "无法加载员工。",
            element_unavailable: "缺少所需元素。",
            request_failed: "请求失败。",
        },
    };
    Object.keys(t).forEach(function (k) {
        window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
    });
})();
//# sourceMappingURL=calendar.js.map