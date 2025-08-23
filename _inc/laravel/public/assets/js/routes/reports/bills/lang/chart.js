(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      chart_unavailable: "تعذّر عرض المخطط الآن.",
      datatable_unavailable: "تعذّر تحميل الجدول الآن.",
      pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
    },
    da: {
      chart_unavailable: "Kan ikke vise diagrammet lige nu.",
      datatable_unavailable: "Kan ikke indlæse tabellen lige nu.",
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
    },
    de: {
      chart_unavailable: "Diagram kann derzeit nicht angezeigt werden.",
      datatable_unavailable: "Tabelle kann derzeit nicht geladen werden.",
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
    },
    en: {
      chart_unavailable: "Chart failed to render.",
      datatable_unavailable: "Table failed to load.",
      pdf_unavailable: "PDF export failed.",
      plugin_unavailable: "A required library failed to load.",
    },
    es: {
      chart_unavailable: "No se pudo mostrar el gráfico.",
      datatable_unavailable: "No se pudo cargar la tabla.",
      pdf_unavailable: "La exportación a PDF falló.",
      plugin_unavailable: "No se cargó una biblioteca requerida.",
    },
    fr: {
      chart_unavailable: "Échec d’affichage du graphique.",
      datatable_unavailable: "Échec du chargement du tableau.",
      pdf_unavailable: "L’export PDF a échoué.",
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
    },
    he: {
      chart_unavailable: "לא ניתן להציג את התרשים כעת.",
      datatable_unavailable: "לא ניתן לטעון את הטבלה כעת.",
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
    },
    it: {
      chart_unavailable: "Impossibile visualizzare il grafico.",
      datatable_unavailable: "Impossibile caricare la tabella.",
      pdf_unavailable: "Esportazione PDF non riuscita.",
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
    },
    ja: {
      chart_unavailable: "グラフを表示できませんでした。",
      datatable_unavailable: "テーブルを読み込めませんでした。",
      pdf_unavailable: "PDF の書き出しに失敗しました。",
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
    },
    nl: {
      chart_unavailable: "Diagram kan nu niet worden weergegeven.",
      datatable_unavailable: "Tabel kan nu niet worden geladen.",
      pdf_unavailable: "PDF-export is mislukt.",
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
    },
    pl: {
      chart_unavailable: "Nie udało się wyświetlić wykresu.",
      datatable_unavailable: "Nie udało się wczytać tabeli.",
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
    },
    pt: {
      chart_unavailable: "Falha ao renderizar o gráfico.",
      datatable_unavailable: "Falha ao carregar a tabela.",
      pdf_unavailable: "Falha na exportação para PDF.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    "pt-br": {
      chart_unavailable: "Falha ao renderizar o gráfico.",
      datatable_unavailable: "Falha ao carregar a tabela.",
      pdf_unavailable: "Falha ao exportar o PDF.",
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
    },
    ru: {
      chart_unavailable: "Не удалось отобразить график.",
      datatable_unavailable: "Не удалось загрузить таблицу.",
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
      plugin_unavailable: "Не загружена необходимая библиотека.",
    },
    tr: {
      chart_unavailable: "Grafik görüntülenemedi.",
      datatable_unavailable: "Tablo yüklenemedi.",
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
    },
    zh: {
      chart_unavailable: "图表渲染失败。",
      datatable_unavailable: "表格加载失败。",
      pdf_unavailable: "PDF 导出失败。",
      plugin_unavailable: "未能加载所需的库。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
