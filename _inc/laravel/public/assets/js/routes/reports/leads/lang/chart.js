(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      endpoint_unavailable: "المسار المطلوب غير متاح.",
      chart_unavailable: "تعذّر عرض المخطط الآن.",
      tabs_unavailable: "تعذّر تبديل علامات التبويب.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      endpoint_unavailable: "Den ønskede sti er ikke tilgængelig.",
      chart_unavailable: "Kan ikke vise diagrammet lige nu.",
      tabs_unavailable: "Kan ikke skifte faner.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      endpoint_unavailable: "Angeforderter Endpunkt ist nicht verfügbar.",
      chart_unavailable: "Diagramm kann derzeit nicht angezeigt werden.",
      tabs_unavailable: "Registerkarten können nicht gewechselt werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      endpoint_unavailable: "Requested endpoint is unavailable.",
      chart_unavailable: "Chart failed to render.",
      tabs_unavailable: "Could not switch tabs.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      endpoint_unavailable: "El endpoint solicitado no está disponible.",
      chart_unavailable: "No se pudo mostrar el gráfico.",
      tabs_unavailable: "No se pudieron cambiar las pestañas.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      endpoint_unavailable: "Le point de terminaison demandé est indisponible.",
      chart_unavailable: "Échec d’affichage du graphique.",
      tabs_unavailable: "Impossible de changer d’onglet.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      endpoint_unavailable: "נקודת הקצה המבוקשת אינה זמינה.",
      chart_unavailable: "לא ניתן להציג את התרשים כעת.",
      tabs_unavailable: "לא ניתן היה להחליף לשוניות.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      endpoint_unavailable: "L’endpoint richiesto non è disponibile.",
      chart_unavailable: "Impossibile visualizzare il grafico.",
      tabs_unavailable: "Impossibile cambiare schede.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      endpoint_unavailable: "要求されたエンドポイントは利用できません。",
      chart_unavailable: "グラフを表示できませんでした。",
      tabs_unavailable: "タブを切り替えできませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      endpoint_unavailable: "Aangevraagde endpoint is niet beschikbaar.",
      chart_unavailable: "Diagram kan nu niet worden weergegeven.",
      tabs_unavailable: "Kon niet van tabblad wisselen.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      endpoint_unavailable: "Żądany endpoint jest niedostępny.",
      chart_unavailable: "Nie udało się wyświetlić wykresu.",
      tabs_unavailable: "Nie można przełączyć kart.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      endpoint_unavailable: "Endpoint solicitado indisponível.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
      tabs_unavailable: "Não foi possível alternar abas.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      endpoint_unavailable: "Endpoint solicitado indisponível.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
      tabs_unavailable: "Não foi possível alternar abas.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      endpoint_unavailable: "Запрошенная точка недоступна.",
      chart_unavailable: "Не удалось отобразить график.",
      tabs_unavailable: "Не удалось переключить вкладки.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      endpoint_unavailable: "İstenen uç nokta kullanılamıyor.",
      chart_unavailable: "Grafik görüntülenemedi.",
      tabs_unavailable: "Sekmeler değiştirilemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      endpoint_unavailable: "请求的端点不可用。",
      chart_unavailable: "图表渲染失败。",
      tabs_unavailable: "无法切换标签页。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
