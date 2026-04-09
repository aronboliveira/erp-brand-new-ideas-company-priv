(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            calendar_init_failed: "فشل تهيئة التقويم.",
            event_modal_unavailable: "فشل تحميل تفاصيل الحدث.",
            chart_tasks_unavailable: "فشل عرض مخطط المهام.",
            chart_projects_unavailable: "فشل عرض مخطط حالة المشروع.",
        },
        da: {
            calendar_init_failed: "Kunne ikke initialisere kalender.",
            event_modal_unavailable: "Kunne ikke indlæse begivenhedsdetaljer.",
            chart_tasks_unavailable: "Kunne ikke vise opgavegraf.",
            chart_projects_unavailable: "Kunne ikke vise projektstatusgraf.",
        },
        de: {
            calendar_init_failed: "Kalender konnte nicht initialisiert werden.",
            event_modal_unavailable: "Ereignisdetails konnten nicht geladen werden.",
            chart_tasks_unavailable: "Task-Diagramm konnte nicht dargestellt werden.",
            chart_projects_unavailable: "Projektstatus-Diagramm konnte nicht dargestellt werden.",
        },
        en: {
            calendar_init_failed: "Failed to initialize calendar.",
            event_modal_unavailable: "Failed to load event details.",
            chart_tasks_unavailable: "Failed to render tasks chart.",
            chart_projects_unavailable: "Failed to render project status chart.",
        },
        es: {
            calendar_init_failed: "Error al inicializar el calendario.",
            event_modal_unavailable: "Error al cargar los detalles del evento.",
            chart_tasks_unavailable: "Error al mostrar el gráfico de tareas.",
            chart_projects_unavailable: "Error al mostrar el gráfico de estado del proyecto.",
        },
        fr: {
            calendar_init_failed: "Échec de l’initialisation du calendrier.",
            event_modal_unavailable: "Échec du chargement des détails de l’événement.",
            chart_tasks_unavailable: "Échec de l’affichage du graphique des tâches.",
            chart_projects_unavailable: "Échec de l’affichage du graphique de statut du projet.",
        },
        he: {
            calendar_init_failed: "האתחול של היומן נכשל.",
            event_modal_unavailable: "הטענת פרטי האירוע נכשלה.",
            chart_tasks_unavailable: "הצגת תרשים המשימות נכשלה.",
            chart_projects_unavailable: "הצגת תרשים מצב הפרויקט נכשלה.",
        },
        it: {
            calendar_init_failed: "Impossibile inizializzare il calendario.",
            event_modal_unavailable: "Impossibile caricare i dettagli dell’evento.",
            chart_tasks_unavailable: "Impossibile visualizzare il grafico delle attività.",
            chart_projects_unavailable: "Impossibile visualizzare il grafico di stato del progetto.",
        },
        ja: {
            calendar_init_failed: "カレンダーの初期化に失敗しました。",
            event_modal_unavailable: "イベントの詳細の読み込みに失敗しました。",
            chart_tasks_unavailable: "タスクチャートの表示に失敗しました。",
            chart_projects_unavailable: "プロジェクトステータスチャートの表示に失敗しました。",
        },
        nl: {
            calendar_init_failed: "Initialisatie van kalender mislukt.",
            event_modal_unavailable: "Kon gebeurtenisdetails niet laden.",
            chart_tasks_unavailable: "Kon takengrafiek niet weergeven.",
            chart_projects_unavailable: "Kon projectstatusgrafiek niet weergeven.",
        },
        pl: {
            calendar_init_failed: "Nie udało się zainicjalizować kalendarza.",
            event_modal_unavailable: "Nie można załadować szczegółów wydarzenia.",
            chart_tasks_unavailable: "Nie udało się wyświetlić wykresu zadań.",
            chart_projects_unavailable: "Nie udało się wyświetlić wykresu statusu projektu.",
        },
        pt: {
            calendar_init_failed: "Falha ao inicializar o calendário.",
            event_modal_unavailable: "Falha ao carregar detalhes do evento.",
            chart_tasks_unavailable: "Falha ao exibir gráfico de tarefas.",
            chart_projects_unavailable: "Falha ao exibir gráfico de status do projeto.",
        },
        "pt-br": {
            calendar_init_failed: "Falha ao inicializar o calendário.",
            event_modal_unavailable: "Falha ao carregar detalhes do evento.",
            chart_tasks_unavailable: "Falha ao exibir gráfico de tarefas.",
            chart_projects_unavailable: "Falha ao exibir gráfico de status do projeto.",
        },
        ru: {
            calendar_init_failed: "Не удалось инициализировать календарь.",
            event_modal_unavailable: "Не удалось загрузить детали события.",
            chart_tasks_unavailable: "Не удалось отобразить график задач.",
            chart_projects_unavailable: "Не удалось отобразить график статуса проекта.",
        },
        tr: {
            calendar_init_failed: "Takvim başlatılamadı.",
            event_modal_unavailable: "Etkinlik ayrıntıları yüklenemedi.",
            chart_tasks_unavailable: "Görev grafiği oluşturulamadı.",
            chart_projects_unavailable: "Proje durum grafiği oluşturulamadı.",
        },
        zh: {
            calendar_init_failed: "初始化日历失败。",
            event_modal_unavailable: "加载事件详情失败。",
            chart_tasks_unavailable: "呈现任务图表失败。",
            chart_projects_unavailable: "呈现项目状态图表失败。",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();
//# sourceMappingURL=chart.js.map