(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "تعذّر تحميل مكتبة مطلوبة.",
      route_unavailable: "رابط غير صالح.",
      pipeline_unavailable: "تعذّر تحديث الأنبوب.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      route_unavailable: "Ugyldig URL.",
      pipeline_unavailable: "Kunne ikke opdatere pipeline.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      route_unavailable: "Ungültige URL.",
      pipeline_unavailable: "Pipeline konnte nicht aktualisiert werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      route_unavailable: "Invalid URL.",
      pipeline_unavailable: "Could not update the pipeline.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      route_unavailable: "URL no válida.",
      pipeline_unavailable: "No se pudo actualizar el pipeline.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      route_unavailable: "URL non valide.",
      pipeline_unavailable: "Impossible de mettre à jour le pipeline.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      route_unavailable: "כתובת לא חוקית.",
      pipeline_unavailable: "לא ניתן לעדכן את הצינור.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      route_unavailable: "URL non valido.",
      pipeline_unavailable: "Impossibile aggiornare la pipeline.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      route_unavailable: "無効なURLです。",
      pipeline_unavailable: "パイプラインを更新できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      route_unavailable: "Ongeldige URL.",
      pipeline_unavailable: "Kan de pijplijn niet bijwerken.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      route_unavailable: "Nieprawidłowy adres URL.",
      pipeline_unavailable: "Nie można zaktualizować potoku.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválido.",
      pipeline_unavailable: "Não foi possível atualizar o pipeline.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      route_unavailable: "URL inválida.",
      pipeline_unavailable: "Não foi possível atualizar o pipeline.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      route_unavailable: "Недействительный URL.",
      pipeline_unavailable: "Не удалось обновить конвейер.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      route_unavailable: "Geçersiz URL.",
      pipeline_unavailable: "Boru hattı güncellenemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      route_unavailable: "无效的链接。",
      pipeline_unavailable: "无法更新管道。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
