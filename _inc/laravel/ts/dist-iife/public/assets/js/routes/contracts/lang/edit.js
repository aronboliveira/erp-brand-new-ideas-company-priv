(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            choices_unavailable: "تعذّر تهيئة عناصر الاختيار",
            project_list_unavailable: "تعذّر تحميل قائمة المشاريع",
        },
        da: {
            choices_unavailable: "Kunne ikke initialisere multi-select",
            project_list_unavailable: "Kunne ikke indlæse projektliste",
        },
        de: {
            choices_unavailable: "Mehrfachauswahl konnte nicht initialisiert werden",
            project_list_unavailable: "Projektliste konnte nicht geladen werden",
        },
        en: {
            choices_unavailable: "Cannot initialize multi-select",
            project_list_unavailable: "Cannot load project list",
        },
        es: {
            choices_unavailable: "No se puede inicializar el selector múltiple",
            project_list_unavailable: "No se puede cargar la lista de proyectos",
        },
        fr: {
            choices_unavailable: "Impossible d’initialiser la sélection multiple",
            project_list_unavailable: "Impossible de charger la liste des projets",
        },
        he: {
            choices_unavailable: "לא ניתן לאתחל בחירה מרובה",
            project_list_unavailable: "לא ניתן לטעון את רשימת הפרויקטים",
        },
        it: {
            choices_unavailable: "Impossibile inizializzare la multiselezione",
            project_list_unavailable: "Impossibile caricare l’elenco progetti",
        },
        ja: {
            choices_unavailable: "マルチセレクトを初期化できません",
            project_list_unavailable: "プロジェクト一覧を読み込めません",
        },
        nl: {
            choices_unavailable: "Multiselect kan niet worden geïnitialiseerd",
            project_list_unavailable: "Projectlijst kan niet worden geladen",
        },
        pl: {
            choices_unavailable: "Nie można zainicjować pola wielokrotnego wyboru",
            project_list_unavailable: "Nie można wczytać listy projektów",
        },
        pt: {
            choices_unavailable: "Não foi possível iniciar o multisseleção",
            project_list_unavailable: "Não foi possível carregar a lista de projetos",
        },
        "pt-br": {
            choices_unavailable: "Não foi possível iniciar o multisseleção",
            project_list_unavailable: "Não foi possível carregar a lista de projetos",
        },
        ru: {
            choices_unavailable: "Не удалось инициализировать мультивыбор",
            project_list_unavailable: "Не удалось загрузить список проектов",
        },
        tr: {
            choices_unavailable: "Çoklu seçim başlatılamıyor",
            project_list_unavailable: "Proje listesi yüklenemiyor",
        },
        zh: {
            choices_unavailable: "无法初始化多选控件",
            project_list_unavailable: "无法加载项目列表",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();