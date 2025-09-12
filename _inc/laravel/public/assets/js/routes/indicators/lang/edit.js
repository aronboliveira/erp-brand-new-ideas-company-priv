(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      designation_fetch_unavailable: "تعذر جلب بيانات التعيين.",
      designation_fetch_failed: "فشل تحميل بيانات التعيين.",
      designation_default: "اختر أي تعيين",
    },
    da: {
      designation_fetch_unavailable: "Kunne ikke hente tildelinger.",
      designation_fetch_failed: "Kunne ikke hente tildelingsdata.",
      designation_default: "Vælg en tildeling",
    },
    de: {
      designation_fetch_unavailable:
        "Zuweisungen konnten nicht abgerufen werden.",
      designation_fetch_failed: "Zuweisungsdaten konnten nicht geladen werden.",
      designation_default: "Wählen Sie eine Zuordnung",
    },
    en: {
      designation_fetch_unavailable: "Failed to fetch designations.",
      designation_fetch_failed: "Failed to fetch designation data.",
      designation_default: "Select any Designation",
    },
    es: {
      designation_fetch_unavailable: "No se pudieron obtener las asignaciones.",
      designation_fetch_failed: "Error al obtener datos de asignación.",
      designation_default: "Selecciona cualquier designación",
    },
    fr: {
      designation_fetch_unavailable:
        "Impossible de récupérer les affectations.",
      designation_fetch_failed:
        "Impossible de récupérer les données d’affectation.",
      designation_default: "Sélectionnez une désignation",
    },
    he: {
      designation_fetch_unavailable: "נכשלו טעינת התפקידים.",
      designation_fetch_failed: "נכשלו טעינת נתוני התפקיד.",
      designation_default: "בחר תפקיד כלשהו",
    },
    it: {
      designation_fetch_unavailable: "Impossibile recuperare le assegnazioni.",
      designation_fetch_failed:
        "Impossibile recuperare i dati di assegnazione.",
      designation_default: "Seleziona una qualunque designazione",
    },
    ja: {
      designation_fetch_unavailable: "役割の取得に失敗しました。",
      designation_fetch_failed: "役割データの取得に失敗しました。",
      designation_default: "任意の役割を選択してください",
    },
    nl: {
      designation_fetch_unavailable: "Kon toewijzingen niet ophalen.",
      designation_fetch_failed: "Kon toewijzingsgegevens niet ophalen.",
      designation_default: "Selecteer een willekeurige aanstelling",
    },
    pl: {
      designation_fetch_unavailable: "Nie udało się pobrać przypisań.",
      designation_fetch_failed: "Nie udało się pobrać danych przypisania.",
      designation_default: "Wybierz dowolne stanowisko",
    },
    pt: {
      designation_fetch_unavailable: "Falha ao obter atribuições.",
      designation_fetch_failed: "Falha ao obter dados de designação.",
      designation_default: "Selecione qualquer designação",
    },
    "pt-br": {
      designation_fetch_unavailable: "Falha ao obter atribuições.",
      designation_fetch_failed: "Falha ao obter dados de designação.",
      designation_default: "Selecione qualquer designação",
    },
    ru: {
      designation_fetch_unavailable: "Не удалось получить назначения.",
      designation_fetch_failed: "Не удалось получить данные о назначении.",
      designation_default: "Выберите любое назначение",
    },
    tr: {
      designation_fetch_unavailable: "Atamalar alınamadı.",
      designation_fetch_failed: "Atama verileri alınamadı.",
      designation_default: "Herhangi bir atama seçin",
    },
    zh: {
      designation_fetch_unavailable: "无法获取职称。",
      designation_fetch_failed: "无法获取职称数据。",
      designation_default: "请选择任何职称",
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
