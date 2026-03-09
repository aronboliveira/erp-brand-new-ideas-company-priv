/** @requires ERPUtils (translations) */
(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      copy_billing_unavailable: "تعذّر نسخ بيانات الفوترة إلى الشحن.",
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
    },
    da: {
      copy_billing_unavailable:
        "Kunne ikke kopiere faktureringsdata til levering.",
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
    },
    de: {
      copy_billing_unavailable:
        "Rechnungsdaten konnten nicht in Versand übernommen werden.",
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
    },
    en: {
      copy_billing_unavailable: "Could not copy billing data to shipping.",
      plugin_unavailable: "A required library failed to load.",
    },
    es: {
      copy_billing_unavailable:
        "No se pudieron copiar los datos de facturación al envío.",
      plugin_unavailable: "No se cargó una biblioteca requerida.",
    },
    fr: {
      copy_billing_unavailable:
        "Impossible de copier la facturation vers la livraison.",
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
    },
    he: {
      copy_billing_unavailable: "לא ניתן היה להעתיק נתוני חיוב למשלוח.",
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
    },
    it: {
      copy_billing_unavailable:
        "Impossibile copiare i dati di fatturazione nella spedizione.",
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
    },
    ja: {
      copy_billing_unavailable: "請求情報を配送先にコピーできませんでした。",
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
    },
    nl: {
      copy_billing_unavailable:
        "Kon factuurgegevens niet naar verzending kopiëren.",
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
    },
    pl: {
      copy_billing_unavailable:
        "Nie można skopiować danych rozliczeniowych do wysyłki.",
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
    },
    pt: {
      copy_billing_unavailable:
        "Não foi possível copiar o faturamento para o envio.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    "pt-br": {
      copy_billing_unavailable:
        "Não foi possível copiar o faturamento para o envio.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    ru: {
      copy_billing_unavailable:
        "Не удалось скопировать данные оплаты в доставку.",
      plugin_unavailable: "Не загружена необходимая библиотека.",
    },
    tr: {
      copy_billing_unavailable: "Fatura bilgileri gönderime kopyalanamadı.",
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
    },
    zh: {
      copy_billing_unavailable: "无法将账单信息复制到配送信息。",
      plugin_unavailable: "未能加载所需的库。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
