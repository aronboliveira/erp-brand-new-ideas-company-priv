/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/ledgers/lang/pdf.js
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
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      pdf_unavailable: "PDF export failed.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      pdf_unavailable: "La exportación a PDF falló.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      pdf_unavailable: "L’export PDF a échoué.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      pdf_unavailable: "Esportazione PDF non riuscita.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      pdf_unavailable: "PDF の書き出しに失敗しました。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      pdf_unavailable: "PDF-export is mislukt.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha na exportação para PDF.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha ao exportar o PDF.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      pdf_unavailable: "PDF 导出失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations![k] = { ...(window.translations![k] || {}), ...t[k] };
  });
})();
