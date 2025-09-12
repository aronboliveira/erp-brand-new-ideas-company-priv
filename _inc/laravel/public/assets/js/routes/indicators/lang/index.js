(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      toggle_init_failed: "فشل تهيئة التبديل.",
      star_toggle_failed: "فشل تبديل النجمة.",
      designation_failed: "فشل جلب الوظيفة.",
      designation_fetch_failed: "فشل جلب بيانات الوظيفة.",
      invoice_fetch_unavailable: "بيانات الفاتورة غير متوفرة.",
    },
    da: {
      toggle_init_failed: "Kunne ikke initialisere toggle.",
      star_toggle_failed: "Kunne ikke skifte stjerne.",
      designation_failed: "Kunne ikke hente betegnelse.",
      designation_fetch_failed: "Kunne ikke hente betegnelsesdata.",
      invoice_fetch_unavailable: "Fakturadata ikke tilgængelige.",
    },
    de: {
      toggle_init_failed: "Initialisierung des Schalters fehlgeschlagen.",
      star_toggle_failed: "Sterneinstellung fehlgeschlagen.",
      designation_failed: "Abrufen der Bezeichnung fehlgeschlagen.",
      designation_fetch_failed: "Abrufen der Bezeichnungsdaten fehlgeschlagen.",
      invoice_fetch_unavailable: "Rechnungsdaten nicht verfügbar.",
    },
    en: {
      toggle_init_failed: "Failed to initialize toggle.",
      star_toggle_failed: "Failed to toggle star.",
      designation_failed: "Failed to fetch designation.",
      designation_fetch_failed: "Failed to fetch designation data.",
      invoice_fetch_unavailable: "Invoice data unavailable.",
    },
    es: {
      toggle_init_failed: "No se pudo inicializar el interruptor.",
      star_toggle_failed: "No se pudo alternar la estrella.",
      designation_failed: "No se pudo obtener la designación.",
      designation_fetch_failed:
        "No se pudieron obtener los datos de la designación.",
      invoice_fetch_unavailable: "Datos de factura no disponibles.",
    },
    fr: {
      toggle_init_failed: "Échec de l’initialisation du commutateur.",
      star_toggle_failed: "Échec du changement d’état de l’étoile.",
      designation_failed: "Échec de la récupération de la désignation.",
      designation_fetch_failed:
        "Échec de la récupération des données de désignation.",
      invoice_fetch_unavailable: "Données de facture indisponibles.",
    },
    he: {
      toggle_init_failed: "נכשל בהפעלת הפקד.",
      star_toggle_failed: "נכשל שינוי הכוכב.",
      designation_failed: "נכשל בקבלת התפקיד.",
      designation_fetch_failed: "נכשל בקבלת נתוני התפקיד.",
      invoice_fetch_unavailable: "נתוני החשבונית אינם זמינים.",
    },
    it: {
      toggle_init_failed: "Impossibile inizializzare l’interruttore.",
      star_toggle_failed: "Impossibile attivare la stella.",
      designation_failed: "Impossibile ottenere la designazione.",
      designation_fetch_failed:
        "Impossibile ottenere i dati della designazione.",
      invoice_fetch_unavailable: "Dati della fattura non disponibili.",
    },
    ja: {
      toggle_init_failed: "トグルの初期化に失敗しました。",
      star_toggle_failed: "星の切り替えに失敗しました。",
      designation_failed: "役職の取得に失敗しました。",
      designation_fetch_failed: "役職データの取得に失敗しました。",
      invoice_fetch_unavailable: "請求書データが利用できません。",
    },
    nl: {
      toggle_init_failed: "Kan schakelaar niet initialiseren.",
      star_toggle_failed: "Kan ster niet omzetten.",
      designation_failed: "Kan functie niet ophalen.",
      designation_fetch_failed: "Kan functiedata niet ophalen.",
      invoice_fetch_unavailable: "Factuurgegevens niet beschikbaar.",
    },
    pl: {
      toggle_init_failed: "Nie można zainicjować przełącznika.",
      star_toggle_failed: "Nie udało się przełączyć gwiazdki.",
      designation_failed: "Nie udało się pobrać nazwy stanowiska.",
      designation_fetch_failed: "Nie udało się pobrać danych stanowiska.",
      invoice_fetch_unavailable: "Dane faktury niedostępne.",
    },
    pt: {
      toggle_init_failed: "Falha ao inicializar o alternador.",
      star_toggle_failed: "Falha ao alternar estrela.",
      designation_failed: "Falha ao obter designação.",
      designation_fetch_failed: "Falha ao obter dados de designação.",
      invoice_fetch_unavailable: "Dados da fatura indisponíveis.",
    },
    "pt-br": {
      toggle_init_failed: "Falha ao inicializar o alternador.",
      star_toggle_failed: "Falha ao alternar estrela.",
      designation_failed: "Falha ao obter designação.",
      designation_fetch_failed: "Falha ao obter dados de designação.",
      invoice_fetch_unavailable: "Dados da fatura indisponíveis.",
    },
    ru: {
      toggle_init_failed: "Не удалось инициализировать переключатель.",
      star_toggle_failed: "Не удалось переключить звезду.",
      designation_failed: "Не удалось получить должность.",
      designation_fetch_failed: "Не удалось получить данные должности.",
      invoice_fetch_unavailable: "Данные счета недоступны.",
    },
    tr: {
      toggle_init_failed: "Anahtar başlatılamadı.",
      star_toggle_failed: "Yıldız anahtarı başarısız.",
      designation_failed: "Unvan alınamadı.",
      designation_fetch_failed: "Unvan verileri alınamadı.",
      invoice_fetch_unavailable: "Fatura verileri kullanılamıyor.",
    },
    zh: {
      toggle_init_failed: "初始化切换失败。",
      star_toggle_failed: "切换星标失败。",
      designation_failed: "获取职称失败。",
      designation_fetch_failed: "获取职称数据失败。",
      invoice_fetch_unavailable: "发票数据不可用。",
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
