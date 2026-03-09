/**
 * @fileoverview TypeScript version of public/assets/js/routes/dashboards/pos/lang/chart.js
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

((): void => {
  if (!window.translations) {
    window.translations = {};
  }
  const t: Record<string, Record<string, string>> = {
    ar: {
      traffic_chart_unavailable: "فشل عرض مخطط المرور.",
    },
    da: {
      traffic_chart_unavailable: "Kunne ikke vise trafikdiagram.",
    },
    de: {
      traffic_chart_unavailable: "Fehler beim Anzeigen des Verkehrscharts.",
    },
    en: {
      traffic_chart_unavailable: "Failed to render traffic chart.",
    },
    es: {
      traffic_chart_unavailable: "Error al mostrar el gráfico de tráfico.",
    },
    fr: {
      traffic_chart_unavailable: "Échec de l’affichage du graphique de trafic.",
    },
    he: {
      traffic_chart_unavailable: "הצגת תרשים התנועה נכשלה.",
    },
    it: {
      traffic_chart_unavailable:
        "Visualizzazione del grafico del traffico non riuscita.",
    },
    ja: {
      traffic_chart_unavailable: "トラフィックチャートの表示に失敗しました。",
    },
    nl: {
      traffic_chart_unavailable: "Kan verkeersgrafiek niet weergeven.",
    },
    pl: {
      traffic_chart_unavailable: "Nie udało się wyświetlić wykresu ruchu.",
    },
    pt: {
      traffic_chart_unavailable: "Falha ao exibir o gráfico de tráfego.",
    },
    "pt-br": {
      traffic_chart_unavailable: "Falha ao exibir o gráfico de tráfego.",
    },
    ru: {
      traffic_chart_unavailable: "Не удалось отобразить график трафика.",
    },
    tr: {
      traffic_chart_unavailable: "Trafik grafiği oluşturulamadı.",
    },
    zh: {
      traffic_chart_unavailable: "呈现流量图失败。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations![k] = { ...(window.translations![k] || {}),
        ...t[k],
      })
  );
})();
