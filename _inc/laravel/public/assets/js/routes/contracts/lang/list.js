(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            choices_unavailable: "تعذّر تهيئة عناصر الاختيار",
            project_list_unavailable: "تعذّر تحميل المشاريع",
        },
        da: {
            choices_unavailable: "Kunne ikke initialisere valgfelter",
            project_list_unavailable: "Kunne ikke indlæse projekter",
        },
        de: {
            choices_unavailable: "Auswahlfelder konnten nicht initialisiert werden",
            project_list_unavailable: "Projekte konnten nicht geladen werden",
        },
        en: {
            choices_unavailable: "Cannot initialize multi-select",
            project_list_unavailable: "Cannot load projects",
        },
        es: {
            choices_unavailable: "No se puede inicializar el selector múltiple",
            project_list_unavailable: "No se pueden cargar los proyectos",
        },
        fr: {
            choices_unavailable: "Impossible d’initialiser la sélection multiple",
            project_list_unavailable: "Impossible de charger les projets",
        },
        he: {
            choices_unavailable: "לא ניתן לאתחל בחירה מרובה",
            project_list_unavailable: "לא ניתן לטעון פרויקטים",
        },
        it: {
            choices_unavailable: "Impossibile inizializzare la multiselezione",
            project_list_unavailable: "Impossibile caricare i progetti",
        },
        ja: {
            choices_unavailable: "マルチセレクトを初期化できません",
            project_list_unavailable: "プロジェクトを読み込めません",
        },
        nl: {
            choices_unavailable: "Kan multiselect niet initialiseren",
            project_list_unavailable: "Kan projecten niet laden",
        },
        pl: {
            choices_unavailable: "Nie można zainicjować pola wielokrotnego wyboru",
            project_list_unavailable: "Nie można wczytać projektów",
        },
        pt: {
            choices_unavailable: "Não foi possível iniciar o multisseleção",
            project_list_unavailable: "Não foi possível carregar os projetos",
        },
        "pt-br": {
            choices_unavailable: "Não foi possível iniciar o multisseleção",
            project_list_unavailable: "Não foi possível carregar os projetos",
        },
        ru: {
            choices_unavailable: "Не удалось инициализировать мультивыбор",
            project_list_unavailable: "Не удалось загрузить проекты",
        },
        tr: {
            choices_unavailable: "Çoklu seçim başlatılamadı",
            project_list_unavailable: "Projeler yüklenemedi",
        },
        zh: {
            choices_unavailable: "无法初始化多选控件",
            project_list_unavailable: "无法加载项目",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();