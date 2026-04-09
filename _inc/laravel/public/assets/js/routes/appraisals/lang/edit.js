(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            emp_by_star_unavailable: "فشل جلب بيانات النجوم للموظف.",
            emp_by_star1_unavailable: "فشل جلب بيانات النجوم للتقييم.",
            employee_fetch_unavailable: "فشل جلب قائمة الموظفين.",
        },
        da: {
            emp_by_star_unavailable: "Kunne ikke hente stjernedata for medarbejderen.",
            emp_by_star1_unavailable: "Kunne ikke hente stjernedata for evalueringen.",
            employee_fetch_unavailable: "Kunne ikke hente medarbejderlisten.",
        },
        de: {
            emp_by_star_unavailable: "Fehler beim Laden der Stern-Daten für den Mitarbeiter.",
            emp_by_star1_unavailable: "Fehler beim Laden der Stern-Daten für die Bewertung.",
            employee_fetch_unavailable: "Fehler beim Abrufen der Mitarbeiterliste.",
        },
        en: {
            emp_by_star_unavailable: "Failed to load star data for the employee.",
            emp_by_star1_unavailable: "Failed to load star data for the appraisal.",
            employee_fetch_unavailable: "Failed to fetch employee list.",
        },
        es: {
            emp_by_star_unavailable: "Error al cargar los datos de estrellas para el empleado.",
            emp_by_star1_unavailable: "Error al cargar los datos de estrellas para la evaluación.",
            employee_fetch_unavailable: "Error al obtener la lista de empleados.",
        },
        fr: {
            emp_by_star_unavailable: "Échec du chargement des données d’étoiles pour l’employé.",
            emp_by_star1_unavailable: "Échec du chargement des données d’étoiles pour l’évaluation.",
            employee_fetch_unavailable: "Échec de la récupération de la liste des employés.",
        },
        he: {
            emp_by_star_unavailable: "לא ניתן לטעון נתוני כוכבים עבור העובד.",
            emp_by_star1_unavailable: "לא ניתן לטעון נתוני כוכבים עבור ההערכה.",
            employee_fetch_unavailable: "לא ניתן להביא את רשימת העובדים.",
        },
        it: {
            emp_by_star_unavailable: "Impossibile caricare i dati delle stelle per il dipendente.",
            emp_by_star1_unavailable: "Impossibile caricare i dati delle stelle per la valutazione.",
            employee_fetch_unavailable: "Impossibile recuperare l’elenco dei dipendenti.",
        },
        ja: {
            emp_by_star_unavailable: "従業員のスター データの読み込みに失敗しました。",
            emp_by_star1_unavailable: "評価のスター データの読み込みに失敗しました。",
            employee_fetch_unavailable: "従業員リストの取得に失敗しました。",
        },
        nl: {
            emp_by_star_unavailable: "Kan stergegevens voor de medewerker niet laden.",
            emp_by_star1_unavailable: "Kan stergegevens voor de beoordeling niet laden.",
            employee_fetch_unavailable: "Kan werknemerslijst niet ophalen.",
        },
        pl: {
            emp_by_star_unavailable: "Nie udało się załadować danych gwiazdek pracownika.",
            emp_by_star1_unavailable: "Nie udało się załadować danych gwiazdek dla oceny.",
            employee_fetch_unavailable: "Nie udało się pobrać listy pracowników.",
        },
        pt: {
            emp_by_star_unavailable: "Falha ao carregar dados de estrelas do funcionário.",
            emp_by_star1_unavailable: "Falha ao carregar dados de estrelas da avaliação.",
            employee_fetch_unavailable: "Falha ao buscar a lista de funcionários.",
        },
        "pt-br": {
            emp_by_star_unavailable: "Falha ao carregar dados de estrelas do funcionário.",
            emp_by_star1_unavailable: "Falha ao carregar dados de estrelas da avaliação.",
            employee_fetch_unavailable: "Falha ao buscar a lista de funcionários.",
        },
        ru: {
            emp_by_star_unavailable: "Не удалось загрузить данные звезд для сотрудника.",
            emp_by_star1_unavailable: "Не удалось загрузить данные звезд для оценки.",
            employee_fetch_unavailable: "Не удалось получить список сотрудников.",
        },
        tr: {
            emp_by_star_unavailable: "Çalışan için yıldız verileri yüklenemedi.",
            emp_by_star1_unavailable: "Değerlendirme için yıldız verileri yüklenemedi.",
            employee_fetch_unavailable: "Çalışan listesi alınamadı.",
        },
        zh: {
            emp_by_star_unavailable: "无法加载该员工的星级数据。",
            emp_by_star1_unavailable: "无法加载该评估的星级数据。",
            employee_fetch_unavailable: "无法获取员工列表。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=edit.js.map