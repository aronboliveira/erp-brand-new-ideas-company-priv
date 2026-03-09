/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/incomes/summaries/lang/chart.js
 * @generated from original JavaScript — automated migration
 * @module chart
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

(function (): void {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
      chart_unavailable: "تعذّر عرض المخطط الآن.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
      chart_unavailable: "Kan ikke vise diagrammet lige nu.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
      chart_unavailable: "Diagramm kann derzeit nicht angezeigt werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      pdf_unavailable: "PDF export failed.",
      chart_unavailable: "Chart failed to render.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      pdf_unavailable: "La exportación a PDF falló.",
      chart_unavailable: "No se pudo mostrar el gráfico.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      pdf_unavailable: "L’export PDF a échoué.",
      chart_unavailable: "Échec d’affichage du graphique.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
      chart_unavailable: "לא ניתן להציג את התרשים כעת.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      pdf_unavailable: "Esportazione PDF non riuscita.",
      chart_unavailable: "Impossibile visualizzare il grafico.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      pdf_unavailable: "PDF の書き出しに失敗しました。",
      chart_unavailable: "グラフを表示できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      pdf_unavailable: "PDF-export is mislukt.",
      chart_unavailable: "Diagram kan nu niet worden weergegeven.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
      chart_unavailable: "Nie udało się wyświetlić wykresu.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha na exportação para PDF.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha ao exportar o PDF.",
      chart_unavailable: "Falha ao renderizar o gráfico.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
      chart_unavailable: "Не удалось отобразить график.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
      chart_unavailable: "Grafik görüntülenemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      pdf_unavailable: "PDF 导出失败。",
      chart_unavailable: "图表渲染失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
