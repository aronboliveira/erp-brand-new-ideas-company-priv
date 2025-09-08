(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      route_unavailable: "رابط غير صالح.",
      contract_desc_save_unavailable: "تعذّر حفظ وصف العقد.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      route_unavailable: "Ugyldig URL.",
      contract_desc_save_unavailable: "Kunne ikke gemme kontraktbeskrivelse.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      route_unavailable: "Ungültige URL.",
      contract_desc_save_unavailable:
        "Vertragsbeschreibung konnte nicht gespeichert werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      route_unavailable: "Invalid URL.",
      contract_desc_save_unavailable:
        "Could not save the contract description.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      route_unavailable: "URL no válida.",
      contract_desc_save_unavailable:
        "No se pudo guardar la descripción del contrato.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      route_unavailable: "URL non valide.",
      contract_desc_save_unavailable:
        "Impossible d’enregistrer la description du contrat.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      route_unavailable: "כתובת לא חוקית.",
      contract_desc_save_unavailable: "לא ניתן לשמור את תיאור החוזה.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      route_unavailable: "URL non valido.",
      contract_desc_save_unavailable:
        "Impossibile salvare la descrizione del contratto.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      route_unavailable: "無効なURLです。",
      contract_desc_save_unavailable: "契約の説明を保存できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      route_unavailable: "Ongeldige URL.",
      contract_desc_save_unavailable: "Kon contractbeschrijving niet opslaan.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      route_unavailable: "Nieprawidłowy adres URL.",
      contract_desc_save_unavailable: "Nie udało się zapisać opisu umowy.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválido.",
      contract_desc_save_unavailable:
        "Não foi possível salvar a descrição do contrato.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválida.",
      contract_desc_save_unavailable:
        "Não foi possível salvar a descrição do contrato.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      route_unavailable: "Недействительный URL.",
      contract_desc_save_unavailable: "Не удалось сохранить описание договора.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      route_unavailable: "Geçersiz URL.",
      contract_desc_save_unavailable: "Sözleşme açıklaması kaydedilemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      route_unavailable: "无效的链接。",
      contract_desc_save_unavailable: "无法保存合同描述。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
