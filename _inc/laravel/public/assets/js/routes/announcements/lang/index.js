(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            announcement_department_unavailable: "قائمة أقسام الإعلان غير متوفرة.",
            announcement_employee_unavailable: "قائمة موظفي الإعلان غير متوفرة.",
        },
        da: {
            announcement_department_unavailable: "Annonceringsafdelinger er ikke tilgængelige.",
            announcement_employee_unavailable: "Annonceringsmedarbejdere er ikke tilgængelige.",
        },
        de: {
            announcement_department_unavailable: "Ankündigungsabteilungen sind nicht verfügbar.",
            announcement_employee_unavailable: "Ankündigungsmitarbeiter sind nicht verfügbar.",
        },
        en: {
            announcement_department_unavailable: "Announcement departments are unavailable.",
            announcement_employee_unavailable: "Announcement employee list is unavailable.",
        },
        es: {
            announcement_department_unavailable: "Los departamentos del anuncio no están disponibles.",
            announcement_employee_unavailable: "Los empleados del anuncio no están disponibles.",
        },
        fr: {
            announcement_department_unavailable: "Les départements de l’annonce ne sont pas disponibles.",
            announcement_employee_unavailable: "La liste des employés de l’annonce n’est pas disponible.",
        },
        he: {
            announcement_department_unavailable: "מחלקות ההודעה אינן זמינות.",
            announcement_employee_unavailable: "רשימת העובדים של ההודעה אינה זמינה.",
        },
        it: {
            announcement_department_unavailable: "I reparti dell’annuncio non sono disponibili.",
            announcement_employee_unavailable: "La lista dei dipendenti dell’annuncio non è disponibile.",
        },
        ja: {
            announcement_department_unavailable: "アナウンスの部署が利用できません。",
            announcement_employee_unavailable: "アナウンスの従業員リストが利用できません。",
        },
        nl: {
            announcement_department_unavailable: "Aankondigingsafdelingen zijn niet beschikbaar.",
            announcement_employee_unavailable: "Aankondigingswerknemerslijst is niet beschikbaar.",
        },
        pl: {
            announcement_department_unavailable: "Działy ogłoszenia są niedostępne.",
            announcement_employee_unavailable: "Lista pracowników ogłoszenia jest niedostępna.",
        },
        pt: {
            announcement_department_unavailable: "Departamentos do anúncio não estão disponíveis.",
            announcement_employee_unavailable: "Lista de funcionários do anúncio não está disponível.",
        },
        "pt-br": {
            announcement_department_unavailable: "Departamentos do anúncio não estão disponíveis.",
            announcement_employee_unavailable: "Lista de funcionários do anúncio não está disponível.",
        },
        ru: {
            announcement_department_unavailable: "Отделы объявления недоступны.",
            announcement_employee_unavailable: "Список сотрудников объявления недоступен.",
        },
        tr: {
            announcement_department_unavailable: "Duyuru bölümleri kullanılamıyor.",
            announcement_employee_unavailable: "Duyuru çalışan listesi kullanılamıyor.",
        },
        zh: {
            announcement_department_unavailable: "公告部门不可用。",
            announcement_employee_unavailable: "公告员工列表不可用。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();