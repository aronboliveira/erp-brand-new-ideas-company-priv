(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      endpoint_unavailable: "المسار المطلوب غير متاح.",
      chart_unavailable: "تعذّر عرض المخطط الآن.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      endpoint_unavailable: "Den ønskede sti er ikke tilgængelig.",
      chart_unavailable: "Kan ikke vise diagrammet lige nu.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      endpoint_unavailable: "Angeforderter Endpunkt ist nicht verfügbar.",
      chart_unavailable: "Diagramm kann derzeit nicht angezeigt werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      endpoint_unavailable: "Requested endpoint is unavailable.",
      chart_unavailable: "Chart failed to render.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      endpoint_unavailable: "El endpoint solicitado no está disponible.",
      chart_unavailable: "No se pudo mostrar el gráfico.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      endpoint_unavailable: "Le point de terminaison demandé est indisponible.",
      chart_unavailable: "Échec d’affichage du graphique.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      endpoint_unavailable: "נקודת הקצה המבוקשת אינה זמינה.",
      chart_unavailable: "לא ניתן להציג את התרשים כעת.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      endpoint_unavailable: "L’endpoint richiesto non è disponibile.",
      chart_unavailable: "Impossibile visualizzare il grafico.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      endpoint_unavailable: "要求されたエンドポイントは利用できません。",
      chart_unavailable: "グラフを表示できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      endpoint_unavailable: "Aangevraagde endpoint is niet beschikbaar.",
      chart_unavailable: "Diagram kan nu niet worden weergegeven.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      endpoint_unavailable: "Żądany endpoint jest niedostępny.",
      chart_unavailable: "Nie udało się wyświetlić wykresu.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      endpoint_unavailable: "Endpoint solicitado indisponível.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      endpoint_unavailable: "Endpoint solicitado indisponível.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      endpoint_unavailable: "Запрошенная точка недоступна.",
      chart_unavailable: "Не удалось отобразить график.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      endpoint_unavailable: "İstenen uç nokta kullanılamıyor.",
      chart_unavailable: "Grafik görüntülenemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      endpoint_unavailable: "请求的端点不可用。",
      chart_unavailable: "图表渲染失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
