/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      emp_by_star_route_unavailable: "مسار empByStar غير متوفر.",
      emp_by_star_unavailable: "فشل جلب بيانات النجوم للموظف.",
      getemployee_route_unavailable: "مسار getemployee غير متوفر.",
      employee_fetch_unavailable: "فشل جلب قائمة الموظفين.",
    },
    da: {
      emp_by_star_route_unavailable: "EmpByStar-ruten er ikke tilgængelig.",
      emp_by_star_unavailable:
        "Kunne ikke hente stjernedata for medarbejderen.",
      getemployee_route_unavailable: "GetEmployee-ruten er ikke tilgængelig.",
      employee_fetch_unavailable: "Kunne ikke hente medarbejderlisten.",
    },
    de: {
      emp_by_star_route_unavailable: 'Route "empByStar" ist nicht verfügbar.',
      emp_by_star_unavailable:
        "Fehler beim Laden der Stern-Daten für den Mitarbeiter.",
      getemployee_route_unavailable: 'Route "getemployee" ist nicht verfügbar.',
      employee_fetch_unavailable: "Fehler beim Abrufen der Mitarbeiterliste.",
    },
    en: {
      emp_by_star_route_unavailable: "The empByStar route is unavailable.",
      emp_by_star_unavailable: "Failed to load star data for the employee.",
      getemployee_route_unavailable: "The getemployee route is unavailable.",
      employee_fetch_unavailable: "Failed to fetch employee list.",
    },
    es: {
      emp_by_star_route_unavailable: "La ruta empByStar no está disponible.",
      emp_by_star_unavailable:
        "Error al cargar los datos de estrellas para el empleado.",
      getemployee_route_unavailable: "La ruta getemployee no está disponible.",
      employee_fetch_unavailable: "Error al obtener la lista de empleados.",
    },
    fr: {
      emp_by_star_route_unavailable: "La route empByStar n’est pas disponible.",
      emp_by_star_unavailable:
        "Échec du chargement des données d’étoiles pour l’employé.",
      getemployee_route_unavailable:
        "La route getemployee n’est pas disponible.",
      employee_fetch_unavailable:
        "Échec de la récupération de la liste des employés.",
    },
    he: {
      emp_by_star_route_unavailable: "נתיב empByStar אינו זמין.",
      emp_by_star_unavailable: "לא ניתן לטעון נתוני כוכבים עבור העובד.",
      getemployee_route_unavailable: "נתיב getemployee אינו זמין.",
      employee_fetch_unavailable: "לא ניתן להביא את רשימת העובדים.",
    },
    it: {
      emp_by_star_route_unavailable: "Il percorso empByStar non è disponibile.",
      emp_by_star_unavailable:
        "Impossibile caricare i dati delle stelle per il dipendente.",
      getemployee_route_unavailable:
        "Il percorso getemployee non è disponibile.",
      employee_fetch_unavailable:
        "Impossibile recuperare l’elenco dei dipendenti.",
    },
    ja: {
      emp_by_star_route_unavailable: "empByStar ルートが利用できません。",
      emp_by_star_unavailable:
        "従業員のスター データの読み込みに失敗しました。",
      getemployee_route_unavailable: "getemployee ルートが利用できません。",
      employee_fetch_unavailable: "従業員リストの取得に失敗しました。",
    },
    nl: {
      emp_by_star_route_unavailable: "De empByStar-route is niet beschikbaar.",
      emp_by_star_unavailable:
        "Kan stergegevens voor de medewerker niet laden.",
      getemployee_route_unavailable:
        "De getemployee-route is niet beschikbaar.",
      employee_fetch_unavailable: "Kan werknemerslijst niet ophalen.",
    },
    pl: {
      emp_by_star_route_unavailable: "Trasa empByStar jest niedostępna.",
      emp_by_star_unavailable:
        "Nie udało się załadować danych gwiazdek pracownika.",
      getemployee_route_unavailable: "Trasa getemployee jest niedostępna.",
      employee_fetch_unavailable: "Nie udało się pobrać listy pracowników.",
    },
    pt: {
      emp_by_star_route_unavailable: "A rota empByStar não está disponível.",
      emp_by_star_unavailable:
        "Falha ao carregar dados de estrelas do funcionário.",
      getemployee_route_unavailable: "A rota getemployee não está disponível.",
      employee_fetch_unavailable: "Falha ao buscar a lista de funcionários.",
    },
    "pt-br": {
      emp_by_star_route_unavailable: "A rota empByStar não está disponível.",
      emp_by_star_unavailable:
        "Falha ao carregar dados de estrelas do funcionário.",
      getemployee_route_unavailable: "A rota getemployee não está disponível.",
      employee_fetch_unavailable: "Falha ao buscar a lista de funcionários.",
    },
    ru: {
      emp_by_star_route_unavailable: "Маршрут empByStar недоступен.",
      emp_by_star_unavailable:
        "Не удалось загрузить данные звезд для сотрудника.",
      getemployee_route_unavailable: "Маршрут getemployee недоступен.",
      employee_fetch_unavailable: "Не удалось получить список сотрудников.",
    },
    tr: {
      emp_by_star_route_unavailable: "empByStar rotası kullanılamıyor.",
      emp_by_star_unavailable: "Çalışan için yıldız verileri yüklenemedi.",
      getemployee_route_unavailable: "getemployee rotası kullanılamıyor.",
      employee_fetch_unavailable: "Çalışan listesi alınamadı.",
    },
    zh: {
      emp_by_star_route_unavailable: "empByStar 路由不可用。",
      emp_by_star_unavailable: "无法加载该员工的星级数据。",
      getemployee_route_unavailable: "getemployee 路由不可用。",
      employee_fetch_unavailable: "无法获取员工列表。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      }),
  );
})();
