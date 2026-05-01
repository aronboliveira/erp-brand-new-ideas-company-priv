(() => {
    if (!window.translations) {
        window.translations = {};
    }
    const t = {
        ar: {
            application_unavailable: "لا يمكن جلب طلبات الوظيفة",
            application_order_unavailable: "فشل تحديث ترتيب الطلب",
            dragula_unavailable: "لا يمكن تهيئة قائمة السحب والإفلات",
        },
        da: {
            application_unavailable: "Kan ikke hente jobansøgning",
            application_order_unavailable: "Opdatering af rækkefølge mislykkedes",
            dragula_unavailable: "Kan ikke initialisere dragula",
        },
        de: {
            application_unavailable: "Kann Bewerbungen nicht abrufen",
            application_order_unavailable: "Reihenfolgeaktualisierung fehlgeschlagen",
            dragula_unavailable: "Kann Dragula nicht initialisieren",
        },
        en: {
            application_unavailable: "Cannot fetch job application",
            application_order_unavailable: "Failed to update application order",
            dragula_unavailable: "Cannot initialize dragula list",
        },
        es: {
            application_unavailable: "No se puede obtener solicitud de trabajo",
            application_order_unavailable: "Fallo al actualizar el orden de la solicitud",
            dragula_unavailable: "No se puede inicializar dragula",
        },
        fr: {
            application_unavailable: "Impossible de récupérer la candidature",
            application_order_unavailable: "Échec de la mise à jour de l’ordre",
            dragula_unavailable: "Impossible d’initialiser dragula",
        },
        he: {
            application_unavailable: "אין אפשרות להביא את בקשת המשרה",
            application_order_unavailable: "עדכון סדר הבקשה נכשל",
            dragula_unavailable: "לא ניתן לאתחל dragula",
        },
        it: {
            application_unavailable: "Impossibile recuperare candidatura",
            application_order_unavailable: "Aggiornamento ordine non riuscito",
            dragula_unavailable: "Impossibile inizializzare dragula",
        },
        ja: {
            application_unavailable: "求人応募を取得できません",
            application_order_unavailable: "申請順序の更新に失敗しました",
            dragula_unavailable: "dragulaを初期化できません",
        },
        nl: {
            application_unavailable: "Kan sollicitatie niet ophalen",
            application_order_unavailable: "Bijwerken volgorde mislukt",
            dragula_unavailable: "Kan dragula niet initialiseren",
        },
        pl: {
            application_unavailable: "Nie można pobrać zgłoszenia",
            application_order_unavailable: "Aktualizacja kolejności nieudana",
            dragula_unavailable: "Nie można zainicjalizować dragula",
        },
        pt: {
            application_unavailable: "Não foi possível buscar candidatura",
            application_order_unavailable: "Falha ao atualizar ordem",
            dragula_unavailable: "Não foi possível inicializar dragula",
        },
        "pt-br": {
            application_unavailable: "Não foi possível buscar candidatura",
            application_order_unavailable: "Falha ao atualizar ordem",
            dragula_unavailable: "Não foi possível inicializar dragula",
        },
        ru: {
            application_unavailable: "Не удалось получить заявку",
            application_order_unavailable: "Не удалось обновить порядок",
            dragula_unavailable: "Не удалось инициализировать dragula",
        },
        tr: {
            application_unavailable: "İş başvurusu alınamadı",
            application_order_unavailable: "Başvuru sırası güncellenemedi",
            dragula_unavailable: "dragula başlatılamıyor",
        },
        zh: {
            application_unavailable: "无法获取求职申请",
            application_order_unavailable: "更新申请顺序失败",
            dragula_unavailable: "无法初始化 dragula",
        },
    };
    Object.keys(t).forEach(k => (window.translations[k] = { ...(window.translations[k] || {}),
        ...t[k], }));
})();