(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      char_of_account_subtype_unavailable: "فشل جلب الأنواع الفرعية للحساب.",
      date_callback_failed: "فشل نسخ قيم التاريخ.",
    },
    da: {
      char_of_account_subtype_unavailable: "Kunne ikke hente underkategorier.",
      date_callback_failed: "Kunne ikke kopiere datoværdier.",
    },
    de: {
      char_of_account_subtype_unavailable:
        "Unterkategorien konnten nicht geladen werden.",
      date_callback_failed: "Konnte Datumswerte nicht kopieren.",
    },
    en: {
      char_of_account_subtype_unavailable: "Failed to fetch account sub-types.",
      date_callback_failed: "Failed to copy date values.",
    },
    es: {
      char_of_account_subtype_unavailable:
        "Error al obtener subtipos de cuenta.",
      date_callback_failed: "Error al copiar los valores de fecha.",
    },
    fr: {
      char_of_account_subtype_unavailable:
        "Échec de la récupération des sous-types de compte.",
      date_callback_failed: "Échec de la copie des valeurs de date.",
    },
    he: {
      char_of_account_subtype_unavailable: "נכשל קבלת תת־סוגי חשבון.",
      date_callback_failed: "העתקת ערכי התאריך נכשלה.",
    },
    it: {
      char_of_account_subtype_unavailable:
        "Recupero dei sottotipi di conto non riuscito.",
      date_callback_failed: "Impossibile copiare i valori della data.",
    },
    ja: {
      char_of_account_subtype_unavailable:
        "勘定科目のサブタイプの取得に失敗しました。",
      date_callback_failed: "日付値のコピーに失敗しました。",
    },
    nl: {
      char_of_account_subtype_unavailable:
        "Kon subtypes van rekening niet ophalen.",
      date_callback_failed: "Kon datumwaarden niet kopiëren.",
    },
    pl: {
      char_of_account_subtype_unavailable:
        "Nie udało się pobrać podtypów konta.",
      date_callback_failed: "Nie udało się skopiować wartości daty.",
    },
    pt: {
      char_of_account_subtype_unavailable: "Falha ao obter subtipos de conta.",
      date_callback_failed: "Falha ao copiar os valores de data.",
    },
    "pt-br": {
      char_of_account_subtype_unavailable: "Falha ao obter subtipos de conta.",
      date_callback_failed: "Falha ao copiar os valores de data.",
    },
    ru: {
      char_of_account_subtype_unavailable: "Не удалось получить подтипы счета.",
      date_callback_failed: "Не удалось скопировать значения даты.",
    },
    tr: {
      char_of_account_subtype_unavailable: "Hesap alt türleri alınamadı.",
      date_callback_failed: "Tarih değerleri kopyalanamadı.",
    },
    zh: {
      char_of_account_subtype_unavailable: "获取账户子类型失败。",
      date_callback_failed: "复制日期值失败。",
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
