/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      taskboard_unavailable: "تعذّر تحميل لوحة المهام",
      task_filter_unavailable: "تعذّر تطبيق عوامل التصفية",
      task_sort_unavailable: "تعذّر تغيير الفرز",
      task_search_unavailable: "تعذّر تنفيذ البحث",
    },
    da: {
      taskboard_unavailable: "Kunne ikke indlæse opgavetavle",
      task_filter_unavailable: "Kunne ikke anvende filtre",
      task_sort_unavailable: "Kunne ikke ændre sortering",
      task_search_unavailable: "Kunne ikke udføre søgning",
    },
    de: {
      taskboard_unavailable: "Aufgabentafel konnte nicht geladen werden",
      task_filter_unavailable: "Filter konnten nicht angewendet werden",
      task_sort_unavailable: "Sortierung konnte nicht geändert werden",
      task_search_unavailable: "Suche konnte nicht ausgeführt werden",
    },
    en: {
      taskboard_unavailable: "Cannot load task board",
      task_filter_unavailable: "Cannot apply filters",
      task_sort_unavailable: "Cannot change sorting",
      task_search_unavailable: "Cannot perform search",
    },
    es: {
      taskboard_unavailable: "No se puede cargar el tablero de tareas",
      task_filter_unavailable: "No se pueden aplicar filtros",
      task_sort_unavailable: "No se puede cambiar el orden",
      task_search_unavailable: "No se puede realizar la búsqueda",
    },
    fr: {
      taskboard_unavailable: "Impossible de charger le tableau des tâches",
      task_filter_unavailable: "Impossible d’appliquer les filtres",
      task_sort_unavailable: "Impossible de modifier le tri",
      task_search_unavailable: "Impossible d’effectuer la recherche",
    },
    he: {
      taskboard_unavailable: "לא ניתן לטעון לוח משימות",
      task_filter_unavailable: "לא ניתן להחיל מסננים",
      task_sort_unavailable: "לא ניתן לשנות מיון",
      task_search_unavailable: "לא ניתן לבצע חיפוש",
    },
    it: {
      taskboard_unavailable: "Impossibile caricare la bacheca attività",
      task_filter_unavailable: "Impossibile applicare i filtri",
      task_sort_unavailable: "Impossibile cambiare l’ordinamento",
      task_search_unavailable: "Impossibile eseguire la ricerca",
    },
    ja: {
      taskboard_unavailable: "タスクボードを読み込めません",
      task_filter_unavailable: "フィルターを適用できません",
      task_sort_unavailable: "並び替えを変更できません",
      task_search_unavailable: "検索を実行できません",
    },
    nl: {
      taskboard_unavailable: "Kan taakbord niet laden",
      task_filter_unavailable: "Kan filters niet toepassen",
      task_sort_unavailable: "Kan sortering niet wijzigen",
      task_search_unavailable: "Kan zoeken niet uitvoeren",
    },
    pl: {
      taskboard_unavailable: "Nie można załadować tablicy zadań",
      task_filter_unavailable: "Nie można zastosować filtrów",
      task_sort_unavailable: "Nie można zmienić sortowania",
      task_search_unavailable: "Nie można wykonać wyszukiwania",
    },
    pt: {
      taskboard_unavailable: "Não foi possível carregar o quadro de tarefas",
      task_filter_unavailable: "Não foi possível aplicar filtros",
      task_sort_unavailable: "Não foi possível alterar a ordenação",
      task_search_unavailable: "Não foi possível realizar a pesquisa",
    },
    "pt-br": {
      taskboard_unavailable: "Não foi possível carregar o quadro de tarefas",
      task_filter_unavailable: "Não foi possível aplicar filtros",
      task_sort_unavailable: "Não foi possível alterar a ordenação",
      task_search_unavailable: "Não foi possível realizar a pesquisa",
    },
    ru: {
      taskboard_unavailable: "Не удалось загрузить доску задач",
      task_filter_unavailable: "Не удалось применить фильтры",
      task_sort_unavailable: "Не удалось изменить сортировку",
      task_search_unavailable: "Не удалось выполнить поиск",
    },
    tr: {
      taskboard_unavailable: "Görev panosu yüklenemiyor",
      task_filter_unavailable: "Filtreler uygulanamadı",
      task_sort_unavailable: "Sıralama değiştirilemedi",
      task_search_unavailable: "Arama gerçekleştirilemedi",
    },
    zh: {
      taskboard_unavailable: "无法加载任务看板",
      task_filter_unavailable: "无法应用筛选",
      task_sort_unavailable: "无法更改排序",
      task_search_unavailable: "无法执行搜索",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
