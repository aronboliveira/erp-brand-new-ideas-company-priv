(function () {
  if (!window.translations) window.translations = {};
  const t = {
    ar: {
      chart_unavailable: "تعذّر تحميل المخطط.",
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
    },
    da: {
      chart_unavailable: "Kunne ikke indlæse diagrammet.",
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
    },
    de: {
      chart_unavailable: "Diagram konnte nicht geladen werden.",
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
    },
    en: {
      chart_unavailable: "Could not load the chart.",
      plugin_unavailable: "A required library failed to load.",
    },
    es: {
      chart_unavailable: "No se pudo cargar el gráfico.",
      plugin_unavailable: "No se cargó una biblioteca requerida.",
    },
    fr: {
      chart_unavailable: "Impossible de charger le graphique.",
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
    },
    he: {
      chart_unavailable: "לא ניתן היה לטעון את התרשים.",
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
    },
    it: {
      chart_unavailable: "Impossibile caricare il grafico.",
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
    },
    ja: {
      chart_unavailable: "チャートを読み込めませんでした。",
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
    },
    nl: {
      chart_unavailable: "Kon de grafiek niet laden.",
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
    },
    pl: {
      chart_unavailable: "Nie można wczytać wykresu.",
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
    },
    pt: {
      chart_unavailable: "Não foi possível carregar o gráfico.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    "pt-br": {
      chart_unavailable: "Não foi possível carregar o gráfico.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    ru: {
      chart_unavailable: "Не удалось загрузить график.",
      plugin_unavailable: "Не загружена необходимая библиотека.",
    },
    tr: {
      chart_unavailable: "Grafik yüklenemedi.",
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
    },
    zh: {
      chart_unavailable: "无法加载图表。",
      plugin_unavailable: "未能加载所需的库。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
