/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/payables/index/lang/pdf.js
 * @generated from original JavaScript — automated migration
 * @module pdf
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
      toggle_unavailable: "لا يمكن تبديل عامل التصفية الآن.",
      date_sync_unavailable: "تعذّر مزامنة التواريخ.",
      report_unavailable: "تعذّر تحديد التقرير.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
      toggle_unavailable: "Kan ikke skifte filter nu.",
      date_sync_unavailable: "Kunne ikke synkronisere datoer.",
      report_unavailable: "Kunne ikke angive rapport.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
      toggle_unavailable: "Filter kann derzeit nicht umgeschaltet werden.",
      date_sync_unavailable:
        "Datumsangaben konnten nicht synchronisiert werden.",
      report_unavailable: "Bericht konnte nicht gesetzt werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      pdf_unavailable: "PDF export failed.",
      toggle_unavailable: "Cannot toggle filter right now.",
      date_sync_unavailable: "Failed to sync dates.",
      report_unavailable: "Failed to set report.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      pdf_unavailable: "La exportación a PDF falló.",
      toggle_unavailable: "No se puede alternar el filtro ahora.",
      date_sync_unavailable: "No se pudieron sincronizar las fechas.",
      report_unavailable: "No se pudo establecer el informe.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      pdf_unavailable: "L’export PDF a échoué.",
      toggle_unavailable: "Impossible d’alterner le filtre maintenant.",
      date_sync_unavailable: "Échec de la synchronisation des dates.",
      report_unavailable: "Impossible de définir le rapport.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
      toggle_unavailable: "לא ניתן להחליף את המסנן כעת.",
      date_sync_unavailable: "סנכרון התאריכים נכשל.",
      report_unavailable: "לא ניתן היה להגדיר את הדוח.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      pdf_unavailable: "Esportazione PDF non riuscita.",
      toggle_unavailable: "Impossibile attivare/disattivare il filtro ora.",
      date_sync_unavailable: "Sincronizzazione delle date non riuscita.",
      report_unavailable: "Impossibile impostare il report.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      pdf_unavailable: "PDF の書き出しに失敗しました。",
      toggle_unavailable: "現在はフィルターを切り替えられません。",
      date_sync_unavailable: "日付の同期に失敗しました。",
      report_unavailable: "レポートを設定できませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      pdf_unavailable: "PDF-export is mislukt.",
      toggle_unavailable: "Kan filter nu niet schakelen.",
      date_sync_unavailable: "Datums synchroniseren is mislukt.",
      report_unavailable: "Rapport instellen mislukt.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
      toggle_unavailable: "Nie można teraz przełączyć filtra.",
      date_sync_unavailable: "Nie udało się zsynchronizować dat.",
      report_unavailable: "Nie udało się ustawić raportu.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha na exportação para PDF.",
      toggle_unavailable: "Não é possível alternar o filtro agora.",
      date_sync_unavailable: "Falha ao sincronizar as datas.",
      report_unavailable: "Falha ao definir o relatório.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha ao exportar o PDF.",
      toggle_unavailable: "Não é possível alternar o filtro agora.",
      date_sync_unavailable: "Falha ao sincronizar as datas.",
      report_unavailable: "Falha ao definir o relatório.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
      toggle_unavailable: "Невозможно переключить фильтр сейчас.",
      date_sync_unavailable: "Не удалось синхронизировать даты.",
      report_unavailable: "Не удалось установить отчёт.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
      toggle_unavailable: "Filtre şu anda değiştirilemiyor.",
      date_sync_unavailable: "Tarihler senkronize edilemedi.",
      report_unavailable: "Rapor ayarlanamadı.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      pdf_unavailable: "PDF 导出失败。",
      toggle_unavailable: "当前无法切换筛选器。",
      date_sync_unavailable: "日期同步失败。",
      report_unavailable: "无法设置报表。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
