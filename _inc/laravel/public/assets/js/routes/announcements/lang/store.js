(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      announcement_department_fetch_failed: "فشل جلب أقسام الإعلان.",
      announcement_employee_fetch_failed: "فشل جلب قائمة الموظفين للإعلان.",
    },
    da: {
      announcement_department_fetch_failed:
        "Kunne ikke hente afdelinger til annoncen.",
      announcement_employee_fetch_failed:
        "Kunne ikke hente medarbejderlisten til annoncen.",
    },
    de: {
      announcement_department_fetch_failed:
        "Fehler beim Laden der Abteilungen für die Ankündigung.",
      announcement_employee_fetch_failed:
        "Fehler beim Laden der Mitarbeiterliste für die Ankündigung.",
    },
    en: {
      announcement_department_fetch_failed:
        "Failed to fetch announcement departments.",
      announcement_employee_fetch_failed:
        "Failed to fetch announcement employee list.",
    },
    es: {
      announcement_department_fetch_failed:
        "Error al obtener los departamentos del anuncio.",
      announcement_employee_fetch_failed:
        "Error al obtener la lista de empleados del anuncio.",
    },
    fr: {
      announcement_department_fetch_failed:
        "Échec de la récupération des départements de l’annonce.",
      announcement_employee_fetch_failed:
        "Échec de la récupération de la liste des employés de l’annonce.",
    },
    he: {
      announcement_department_fetch_failed:
        "לא ניתן להביא את המחלקות של ההודעה.",
      announcement_employee_fetch_failed:
        "לא ניתן להביא את רשימת העובדים של ההודעה.",
    },
    it: {
      announcement_department_fetch_failed:
        "Impossibile recuperare i reparti per l’avviso.",
      announcement_employee_fetch_failed:
        "Impossibile recuperare l’elenco dei dipendenti per l’avviso.",
    },
    ja: {
      announcement_department_fetch_failed:
        "お知らせの部署データの取得に失敗しました。",
      announcement_employee_fetch_failed:
        "お知らせの従業員リストの取得に失敗しました。",
    },
    nl: {
      announcement_department_fetch_failed:
        "Kan aankondigingsafdelingen niet ophalen.",
      announcement_employee_fetch_failed:
        "Kan aankondigingsmedewerkerslijst niet ophalen.",
    },
    pl: {
      announcement_department_fetch_failed:
        "Nie udało się pobrać działów ogłoszenia.",
      announcement_employee_fetch_failed:
        "Nie udało się pobrać listy pracowników ogłoszenia.",
    },
    pt: {
      announcement_department_fetch_failed:
        "Falha ao buscar departamentos do anúncio.",
      announcement_employee_fetch_failed:
        "Falha ao buscar lista de funcionários do anúncio.",
    },
    "pt-br": {
      announcement_department_fetch_failed:
        "Falha ao buscar departamentos do anúncio.",
      announcement_employee_fetch_failed:
        "Falha ao buscar lista de funcionários do anúncio.",
    },
    ru: {
      announcement_department_fetch_failed:
        "Не удалось получить отделы объявления.",
      announcement_employee_fetch_failed:
        "Не удалось получить список сотрудников объявления.",
    },
    tr: {
      announcement_department_fetch_failed: "Duyuru bölümleri alınamadı.",
      announcement_employee_fetch_failed: "Duyuru çalışan listesi alınamadı.",
    },
    zh: {
      announcement_department_fetch_failed: "获取公告部门失败。",
      announcement_employee_fetch_failed: "获取公告员工列表失败。",
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
