(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      task_view_unavailable: "تعذّر تحميل المهام.",
      ajax_unavailable: "تعذّر الاتصال بالخادم.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      task_view_unavailable: "Kunne ikke indlæse opgaver.",
      ajax_unavailable: "Serveren kunne ikke kontaktes.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      task_view_unavailable: "Aufgaben konnten nicht geladen werden.",
      ajax_unavailable: "Serveranfrage fehlgeschlagen.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      task_view_unavailable: "Could not load tasks.",
      ajax_unavailable: "Server request failed.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      task_view_unavailable: "No se pudieron cargar las tareas.",
      ajax_unavailable: "Falló la solicitud al servidor.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      task_view_unavailable: "Impossible de charger les tâches.",
      ajax_unavailable: "Échec de la requête serveur.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      task_view_unavailable: "לא ניתן היה לטעון משימות.",
      ajax_unavailable: "בקשת השרת נכשלה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      task_view_unavailable: "Impossibile caricare le attività.",
      ajax_unavailable: "Richiesta al server non riuscita.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      task_view_unavailable: "タスクを読み込めませんでした。",
      ajax_unavailable: "サーバー要求に失敗しました。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      task_view_unavailable: "Taken konden niet worden geladen.",
      ajax_unavailable: "Serververzoek is mislukt.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      task_view_unavailable: "Nie udało się wczytać zadań.",
      ajax_unavailable: "Żądanie do serwera nie powiodło się.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      task_view_unavailable: "Não foi possível carregar as tarefas.",
      ajax_unavailable: "Falha na solicitação ao servidor.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      task_view_unavailable: "Não foi possível carregar as tarefas.",
      ajax_unavailable: "Falha na requisição ao servidor.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      task_view_unavailable: "Не удалось загрузить задачи.",
      ajax_unavailable: "Сбой запроса к серверу.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      task_view_unavailable: "Görevler yüklenemedi.",
      ajax_unavailable: "Sunucu isteği başarısız oldu.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      task_view_unavailable: "无法加载任务。",
      ajax_unavailable: "服务器请求失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
