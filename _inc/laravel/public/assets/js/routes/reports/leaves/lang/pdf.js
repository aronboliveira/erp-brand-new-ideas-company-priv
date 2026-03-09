/** @requires ERPUtils (translations) */
(function () {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      plugin_unavailable: "فشل تحميل مكتبة مطلوبة.",
      pdf_unavailable: "تعذّر إنشاء ملف PDF الآن.",
      datatable_unavailable: "تعذّر تحميل الجدول الآن.",
    },
    da: {
      plugin_unavailable: "Et påkrævet bibliotek blev ikke indlæst.",
      pdf_unavailable: "Kunne ikke generere PDF lige nu.",
      datatable_unavailable: "Kan ikke indlæse tabellen lige nu.",
    },
    de: {
      plugin_unavailable: "Erforderliche Bibliothek wurde nicht geladen.",
      pdf_unavailable: "PDF konnte derzeit nicht erstellt werden.",
      datatable_unavailable: "Tabelle kann derzeit nicht geladen werden.",
    },
    en: {
      plugin_unavailable: "A required library failed to load.",
      pdf_unavailable: "PDF export failed.",
      datatable_unavailable: "Table failed to load.",
    },
    es: {
      plugin_unavailable: "No se cargó una biblioteca requerida.",
      pdf_unavailable: "La exportación a PDF falló.",
      datatable_unavailable: "No se pudo cargar la tabla.",
    },
    fr: {
      plugin_unavailable: "Une bibliothèque requise n’a pas été chargée.",
      pdf_unavailable: "L’export PDF a échoué.",
      datatable_unavailable: "Échec du chargement du tableau.",
    },
    he: {
      plugin_unavailable: "ספרייה נדרשת לא נטענה.",
      pdf_unavailable: "ייצוא ה-PDF נכשל.",
      datatable_unavailable: "לא ניתן לטעון את הטבלה כעת.",
    },
    it: {
      plugin_unavailable: "Una libreria richiesta non è stata caricata.",
      pdf_unavailable: "Esportazione PDF non riuscita.",
      datatable_unavailable: "Impossibile caricare la tabella.",
    },
    ja: {
      plugin_unavailable: "必要なライブラリが読み込まれていません。",
      pdf_unavailable: "PDF の書き出しに失敗しました。",
      datatable_unavailable: "テーブルを読み込めませんでした。",
    },
    nl: {
      plugin_unavailable: "Een vereiste bibliotheek is niet geladen.",
      pdf_unavailable: "PDF-export is mislukt.",
      datatable_unavailable: "Tabel kan nu niet worden geladen.",
    },
    pl: {
      plugin_unavailable: "Wymagana biblioteka nie została wczytana.",
      pdf_unavailable: "Eksport do PDF nie powiódł się.",
      datatable_unavailable: "Nie udało się wczytać tabeli.",
    },
    pt: {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha na exportação para PDF.",
      datatable_unavailable: "Falha ao carregar a tabela.",
    },
    "pt-br": {
      plugin_unavailable: "Uma biblioteca necessária não foi carregada.",
      pdf_unavailable: "Falha ao exportar o PDF.",
      datatable_unavailable: "Falha ao carregar a tabela.",
    },
    ru: {
      plugin_unavailable: "Не загружена необходимая библиотека.",
      pdf_unavailable: "Не удалось выполнить экспорт PDF.",
      datatable_unavailable: "Не удалось загрузить таблицу.",
    },
    tr: {
      plugin_unavailable: "Gerekli bir kitaplık yüklenmedi.",
      pdf_unavailable: "PDF dışa aktarma başarısız oldu.",
      datatable_unavailable: "Tablo yüklenemedi.",
    },
    zh: {
      plugin_unavailable: "未能加载所需的库。",
      pdf_unavailable: "PDF 导出失败。",
      datatable_unavailable: "表格加载失败。",
    },
  };
  Object.keys(t).forEach(function (k) {
    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
  });
})();
