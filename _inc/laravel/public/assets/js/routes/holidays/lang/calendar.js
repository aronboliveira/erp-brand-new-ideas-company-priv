/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      calendar_type_element_unavailable: "عنصر نوع التقويم غير متوفر.",
      calendar_element_unavailable: "عنصر التقويم غير متوفر.",
      holiday_fetch_unavailable: "فشل جلب بيانات العطل.",
      calendar_initialization_failed: "فشل تهيئة التقويم.",
    },
    da: {
      calendar_type_element_unavailable:
        "Elementet til kalender type ikke fundet.",
      calendar_element_unavailable: "Kalenderelement ikke fundet.",
      holiday_fetch_unavailable: "Kunne ikke hente helligdagsdata.",
      calendar_initialization_failed: "Kalenderinitialisering mislykkedes.",
    },
    de: {
      calendar_type_element_unavailable: "Kalendertyp-Element nicht gefunden.",
      calendar_element_unavailable: "Kalender-Element nicht gefunden.",
      holiday_fetch_unavailable: "Abruf der Feiertagsdaten fehlgeschlagen.",
      calendar_initialization_failed:
        "Initialisierung des Kalenders fehlgeschlagen.",
    },
    en: {
      calendar_type_element_unavailable: "Calendar type element unavailable.",
      calendar_element_unavailable: "Calendar element unavailable.",
      holiday_fetch_unavailable: "Failed to fetch holiday data.",
      calendar_initialization_failed: "Failed to initialize calendar.",
    },
    es: {
      calendar_type_element_unavailable:
        "Elemento de tipo de calendario no disponible.",
      calendar_element_unavailable: "Elemento de calendario no disponible.",
      holiday_fetch_unavailable: "Error al obtener los datos de días festivos.",
      calendar_initialization_failed: "Error al inicializar el calendario.",
    },
    fr: {
      calendar_type_element_unavailable:
        "Élément de type de calendrier indisponible.",
      calendar_element_unavailable: "Élément de calendrier indisponible.",
      holiday_fetch_unavailable:
        "Échec de la récupération des données des jours fériés.",
      calendar_initialization_failed:
        "Échec de l’initialisation du calendrier.",
    },
    he: {
      calendar_type_element_unavailable: "אלמנט סוג לוח שנה לא זמין.",
      calendar_element_unavailable: "אלמנט לוח שנה לא זמין.",
      holiday_fetch_unavailable: "לא ניתנו נתוני חגים.",
      calendar_initialization_failed: "התחוללה שגיאה באתחול לוח השנה.",
    },
    it: {
      calendar_type_element_unavailable:
        "Elemento tipo calendario non disponibile.",
      calendar_element_unavailable: "Elemento calendario non disponibile.",
      holiday_fetch_unavailable:
        "Impossibile recuperare i dati delle festività.",
      calendar_initialization_failed:
        "Impossibile inizializzare il calendario.",
    },
    ja: {
      calendar_type_element_unavailable:
        "カレンダータイプ要素が利用できません。",
      calendar_element_unavailable: "カレンダー要素が利用できません。",
      holiday_fetch_unavailable: "祝日データの取得に失敗しました。",
      calendar_initialization_failed: "カレンダーの初期化に失敗しました。",
    },
    nl: {
      calendar_type_element_unavailable:
        "Kalendertype-element niet beschikbaar.",
      calendar_element_unavailable: "Kalenderelement niet beschikbaar.",
      holiday_fetch_unavailable: "Kon feestdaggegevens niet ophalen.",
      calendar_initialization_failed: "Initialiseren van de kalender mislukt.",
    },
    pl: {
      calendar_type_element_unavailable: "Element typu kalendarza niedostępny.",
      calendar_element_unavailable: "Element kalendarza niedostępny.",
      holiday_fetch_unavailable: "Nie udało się pobrać danych o świętach.",
      calendar_initialization_failed: "Nie udało się zainicjować kalendarza.",
    },
    pt: {
      calendar_type_element_unavailable:
        "Elemento de tipo de calendário indisponível.",
      calendar_element_unavailable: "Elemento de calendário indisponível.",
      holiday_fetch_unavailable: "Falha ao obter dados de feriados.",
      calendar_initialization_failed: "Falha ao inicializar o calendário.",
    },
    "pt-br": {
      calendar_type_element_unavailable:
        "Elemento de tipo de calendário indisponível.",
      calendar_element_unavailable: "Elemento de calendário indisponível.",
      holiday_fetch_unavailable: "Falha ao obter dados de feriados.",
      calendar_initialization_failed: "Falha ao inicializar o calendário.",
    },
    ru: {
      calendar_type_element_unavailable: "Элемент типа календаря недоступен.",
      calendar_element_unavailable: "Элемент календаря недоступен.",
      holiday_fetch_unavailable: "Не удалось получить данные о праздниках.",
      calendar_initialization_failed: "Не удалось инициализировать календарь.",
    },
    tr: {
      calendar_type_element_unavailable: "Takvim türü öğesi bulunamadı.",
      calendar_element_unavailable: "Takvim öğesi bulunamadı.",
      holiday_fetch_unavailable: "Tatil verileri alınamadı.",
      calendar_initialization_failed: "Takvim başlatılamadı.",
    },
    zh: {
      calendar_type_element_unavailable: "日历类型元素不可用。",
      calendar_element_unavailable: "日历元素不可用。",
      holiday_fetch_unavailable: "获取节假日数据失败。",
      calendar_initialization_failed: "初始化日历失败。",
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
