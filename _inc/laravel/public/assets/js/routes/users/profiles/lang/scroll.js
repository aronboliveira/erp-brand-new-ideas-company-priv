/** @requires ERPUtils (translations) */
(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      scrollspy_unavailable: "تعذّر تهيئة ScrollSpy.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      scrollspy_unavailable: "Kunne ikke initialisere ScrollSpy.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      scrollspy_unavailable: "ScrollSpy konnte nicht initialisiert werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      scrollspy_unavailable: "Could not initialize ScrollSpy.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      scrollspy_unavailable: "No se pudo inicializar ScrollSpy.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      scrollspy_unavailable: "Impossible d’initialiser ScrollSpy.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      scrollspy_unavailable: "לא ניתן היה לאתחל את ScrollSpy.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      scrollspy_unavailable: "Impossibile inizializzare ScrollSpy.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      scrollspy_unavailable: "ScrollSpy を初期化できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      scrollspy_unavailable: "ScrollSpy kon niet worden geïnitialiseerd.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      scrollspy_unavailable: "Nie można zainicjować ScrollSpy.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      scrollspy_unavailable: "Não foi possível inicializar o ScrollSpy.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      scrollspy_unavailable: "Não foi possível inicializar o ScrollSpy.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      scrollspy_unavailable: "Не удалось инициализировать ScrollSpy.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      scrollspy_unavailable: "ScrollSpy başlatılamadı.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      scrollspy_unavailable: "无法初始化 ScrollSpy。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
