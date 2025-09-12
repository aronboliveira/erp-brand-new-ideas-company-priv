(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      datepicker_plugin_unavailable: "مكون التاريخ غير متاح.",
      datepicker_init_failed: "فشل تهيئة محدد التاريخ.",
    },
    da: {
      datepicker_plugin_unavailable: "Datepicker-plugin ikke tilgængelig.",
      datepicker_init_failed: "Kunne ikke initialisere datovælger.",
    },
    de: {
      datepicker_plugin_unavailable: "Datepicker-Plugin nicht verfügbar.",
      datepicker_init_failed:
        "Initialisierung des Datumsauswahlfelds fehlgeschlagen.",
    },
    en: {
      datepicker_plugin_unavailable: "Datepicker plugin unavailable.",
      datepicker_init_failed: "Failed to initialize datepicker.",
    },
    es: {
      datepicker_plugin_unavailable:
        "Complemento de selector de fecha no disponible.",
      datepicker_init_failed: "Error al inicializar el selector de fecha.",
    },
    fr: {
      datepicker_plugin_unavailable:
        "Plugin de sélecteur de date indisponible.",
      datepicker_init_failed: "Échec de l’initialisation du sélecteur de date.",
    },
    he: {
      datepicker_plugin_unavailable: "תוסף בוחר התאריך אינו זמין.",
      datepicker_init_failed: "ההפעלה של בוחר התאריך נכשלה.",
    },
    it: {
      datepicker_plugin_unavailable:
        "Plugin del selettore data non disponibile.",
      datepicker_init_failed: "Impossibile inizializzare il selettore data.",
    },
    ja: {
      datepicker_plugin_unavailable:
        "日付ピッカー プラグインが利用できません。",
      datepicker_init_failed: "日付ピッカーの初期化に失敗しました。",
    },
    nl: {
      datepicker_plugin_unavailable: "Datepicker-plug-in niet beschikbaar.",
      datepicker_init_failed: "Kon datepicker niet initialiseren.",
    },
    pl: {
      datepicker_plugin_unavailable: "Wtyczka wyboru daty niedostępna.",
      datepicker_init_failed: "Nie udało się zainicjalizować selektora daty.",
    },
    pt: {
      datepicker_plugin_unavailable: "Plugin de seletor de data indisponível.",
      datepicker_init_failed: "Falha ao inicializar o seletor de data.",
    },
    "pt-br": {
      datepicker_plugin_unavailable: "Plugin de datepicker indisponível.",
      datepicker_init_failed: "Falha ao inicializar o datepicker.",
    },
    ru: {
      datepicker_plugin_unavailable: "Плагин выбора даты недоступен.",
      datepicker_init_failed: "Не удалось инициализировать выбор даты.",
    },
    tr: {
      datepicker_plugin_unavailable: "Tarih seçici eklentisi kullanılamıyor.",
      datepicker_init_failed: "Tarih seçici başlatılamadı.",
    },
    zh: {
      datepicker_plugin_unavailable: "日期选择插件不可用。",
      datepicker_init_failed: "初始化日期选择器失败。",
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
