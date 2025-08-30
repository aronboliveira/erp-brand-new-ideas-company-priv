(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      calendar_data_unavailable: "فشل تحميل بيانات التقويم.",
      department_fetch_unavailable: "فشل جلب الأقسام.",
      employee_fetch_unavailable: "فشل جلب الموظفين.",
    },
    da: {
      calendar_data_unavailable: "Kunne ikke hente kalenderdata.",
      department_fetch_unavailable: "Kunne ikke hente afdelinger.",
      employee_fetch_unavailable: "Kunne ikke hente medarbejdere.",
    },
    de: {
      calendar_data_unavailable: "Abrufen der Kalenderdaten fehlgeschlagen.",
      department_fetch_unavailable: "Abrufen der Abteilungen fehlgeschlagen.",
      employee_fetch_unavailable: "Abrufen der Mitarbeiter fehlgeschlagen.",
    },
    en: {
      calendar_data_unavailable: "Failed to load calendar data.",
      department_fetch_unavailable: "Failed to fetch departments.",
      employee_fetch_unavailable: "Failed to fetch employees.",
    },
    es: {
      calendar_data_unavailable: "Error al cargar datos del calendario.",
      department_fetch_unavailable: "Error al obtener departamentos.",
      employee_fetch_unavailable: "Error al obtener empleados.",
    },
    fr: {
      calendar_data_unavailable: "Échec du chargement du calendrier.",
      department_fetch_unavailable:
        "Échec de la récupération des départements.",
      employee_fetch_unavailable: "Échec de la récupération des employés.",
    },
    it: {
      calendar_data_unavailable: "Impossibile caricare il calendario.",
      department_fetch_unavailable: "Impossibile recuperare i dipartimenti.",
      employee_fetch_unavailable: "Impossibile recuperare i dipendenti.",
    },
    ja: {
      calendar_data_unavailable: "カレンダーデータの読み込みに失敗しました。",
      department_fetch_unavailable: "部署を取得できませんでした。",
      employee_fetch_unavailable: "従業員を取得できませんでした。",
    },
    nl: {
      calendar_data_unavailable: "Kan kalendergegevens niet laden.",
      department_fetch_unavailable: "Kan afdelingen niet ophalen.",
      employee_fetch_unavailable: "Kan medewerkers niet ophalen.",
    },
    pl: {
      calendar_data_unavailable: "Nie udało się załadować kalendarza.",
      department_fetch_unavailable: "Nie udało się pobrać działów.",
      employee_fetch_unavailable: "Nie udało się pobrać pracowników.",
    },
    pt: {
      calendar_data_unavailable: "Falha ao carregar calendário.",
      department_fetch_unavailable: "Falha ao obter departamentos.",
      employee_fetch_unavailable: "Falha ao obter funcionários.",
    },
    "pt-br": {
      calendar_data_unavailable: "Falha ao carregar dados do calendário.",
      department_fetch_unavailable: "Falha ao buscar departamentos.",
      employee_fetch_unavailable: "Falha ao buscar funcionários.",
    },
    ru: {
      calendar_data_unavailable: "Не удалось загрузить календарь.",
      department_fetch_unavailable: "Не удалось получить отделы.",
      employee_fetch_unavailable: "Не удалось получить сотрудников.",
    },
    tr: {
      calendar_data_unavailable: "Takvim verileri yüklenemedi.",
      department_fetch_unavailable: "Birimler alınamadı.",
      employee_fetch_unavailable: "Çalışanlar alınamadı.",
    },
    zh: {
      calendar_data_unavailable: "无法加载日历数据。",
      department_fetch_unavailable: "获取部门失败。",
      employee_fetch_unavailable: "获取员工失败。",
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
