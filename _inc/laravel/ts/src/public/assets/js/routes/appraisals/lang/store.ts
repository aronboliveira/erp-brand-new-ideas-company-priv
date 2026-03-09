/**
 * @fileoverview TypeScript version of public/assets/js/routes/appraisals/lang/store.js
 * @generated from original JavaScript — automated migration
 * @module store
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      emp_by_star_unavailable: "فشل جلب بيانات النجوم للموظف.",
      employee_fetch_unavailable: "فشل جلب قائمة الموظفين.",
    },
    da: {
      emp_by_star_unavailable:
        "Kunne ikke hente stjernedata for medarbejderen.",
      employee_fetch_unavailable: "Kunne ikke hente medarbejderlisten.",
    },
    de: {
      emp_by_star_unavailable:
        "Fehler beim Laden der Stern-Daten für den Mitarbeiter.",
      employee_fetch_unavailable: "Fehler beim Abrufen der Mitarbeiterliste.",
    },
    en: {
      emp_by_star_unavailable: "Failed to load star data for the employee.",
      employee_fetch_unavailable: "Failed to fetch employee list.",
    },
    es: {
      emp_by_star_unavailable:
        "Error al cargar los datos de estrellas para el empleado.",
      employee_fetch_unavailable: "Error al obtener la lista de empleados.",
    },
    fr: {
      emp_by_star_unavailable:
        "Échec du chargement des données d’étoiles pour l’employé.",
      employee_fetch_unavailable:
        "Échec de la récupération de la liste des employés.",
    },
    he: {
      emp_by_star_unavailable: "לא ניתן לטעון נתוני כוכבים עבור העובד.",
      employee_fetch_unavailable: "לא ניתן להביא את רשימת העובדים.",
    },
    it: {
      emp_by_star_unavailable:
        "Impossibile caricare i dati delle stelle per il dipendente.",
      employee_fetch_unavailable:
        "Impossibile recuperare l’elenco dei dipendenti.",
    },
    ja: {
      emp_by_star_unavailable:
        "従業員のスター データの読み込みに失敗しました。",
      employee_fetch_unavailable: "従業員リストの取得に失敗しました。",
    },
    nl: {
      emp_by_star_unavailable:
        "Kan stergegevens voor de medewerker niet laden.",
      employee_fetch_unavailable: "Kan werknemerslijst niet ophalen.",
    },
    pl: {
      emp_by_star_unavailable:
        "Nie udało się załadować danych gwiazdek pracownika.",
      employee_fetch_unavailable: "Nie udało się pobrać listy pracowników.",
    },
    pt: {
      emp_by_star_unavailable:
        "Falha ao carregar dados de estrelas do funcionário.",
      employee_fetch_unavailable: "Falha ao buscar a lista de funcionários.",
    },
    "pt-br": {
      emp_by_star_unavailable:
        "Falha ao carregar dados de estrelas do funcionário.",
      employee_fetch_unavailable: "Falha ao buscar a lista de funcionários.",
    },
    ru: {
      emp_by_star_unavailable:
        "Не удалось загрузить данные звезд для сотрудника.",
      employee_fetch_unavailable: "Не удалось получить список сотрудников.",
    },
    tr: {
      emp_by_star_unavailable: "Çalışan için yıldız verileri yüklenemedi.",
      employee_fetch_unavailable: "Çalışan listesi alınamadı.",
    },
    zh: {
      emp_by_star_unavailable: "无法加载该员工的星级数据。",
      employee_fetch_unavailable: "无法获取员工列表。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
