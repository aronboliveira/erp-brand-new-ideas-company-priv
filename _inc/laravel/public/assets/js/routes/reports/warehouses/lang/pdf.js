/** @requires ERPUtils (translations) */
(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      chart_unavailable: "تعذّر عرض المخطط الآن.",
    },
    da: {
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      chart_unavailable: "Kan ikke vise diagrammet lige nu.",
    },
    de: {
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      chart_unavailable: "Diagramm kann derzeit nicht angezeigt werden.",
    },
    en: {
      pdf_unavailable: "PDF export failed.",
      plugin_unavailable: "A required library failed to load.",
      chart_unavailable: "Chart failed to render.",
    },
    es: {
      pdf_unavailable: "La exportación a PDF falló.",
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      chart_unavailable: "No se pudo mostrar el gráfico.",
    },
    fr: {
      pdf_unavailable: "L’export PDF a échoué.",
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      chart_unavailable: "Échec d’affichage du graphique.",
    },
    he: {
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      chart_unavailable: "לא ניתן להציג את התרשים כעת.",
    },
    it: {
      pdf_unavailable: "Esportazione PDF non riuscita.",
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      chart_unavailable: "Impossibile visualizzare il grafico.",
    },
    ja: {
      pdf_unavailable: "PDF の書き出しに失敗しました。",
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      chart_unavailable: "グラフを表示できませんでした。",
    },
    nl: {
      pdf_unavailable: "PDF-export is mislukt.",
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      chart_unavailable: "Diagram kan nu niet worden weergegeven.",
    },
    pl: {
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      chart_unavailable: "Nie udało się wyświetlić wykresu.",
    },
    pt: {
      pdf_unavailable: "Falha na exportação para PDF.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
    },
    "pt-br": {
      pdf_unavailable: "Falha ao exportar o PDF.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
    },
    ru: {
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
      plugin_unavailable: "Не загружена необходимая библиотека.",
      chart_unavailable: "Не удалось отобразить график.",
    },
    tr: {
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      chart_unavailable: "Grafik görüntülenemedi.",
    },
    zh: {
      pdf_unavailable: "PDF 导出失败。",
      plugin_unavailable: "未能加载所需的库。",
      chart_unavailable: "图表渲染失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
