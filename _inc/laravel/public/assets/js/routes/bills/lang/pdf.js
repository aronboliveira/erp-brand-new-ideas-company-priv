(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      pdf_generation_failed: "فشل إنشاء ملف PDF.",
      window_close_failed: "فشل إغلاق النافذة.",
    },
    da: {
      pdf_generation_failed: "Oprettelse af PDF mislykkedes.",
      window_close_failed: "Lukning af vindue mislykkedes.",
    },
    de: {
      pdf_generation_failed: "PDF-Generierung fehlgeschlagen.",
      window_close_failed: "Fenster konnte nicht geschlossen werden.",
    },
    en: {
      pdf_generation_failed: "Failed to generate PDF.",
      window_close_failed: "Failed to close window.",
    },
    es: {
      pdf_generation_failed: "Error al generar el PDF.",
      window_close_failed: "Error al cerrar la ventana.",
    },
    fr: {
      pdf_generation_failed: "Échec de la génération du PDF.",
      window_close_failed: "Échec de la fermeture de la fenêtre.",
    },
    he: {
      pdf_generation_failed: "יצירת PDF נכשלה.",
      window_close_failed: "סגירת החלון נכשלה.",
    },
    it: {
      pdf_generation_failed: "Creazione del PDF non riuscita.",
      window_close_failed: "Chiusura della finestra non riuscita.",
    },
    ja: {
      pdf_generation_failed: "PDF の生成に失敗しました。",
      window_close_failed: "ウィンドウを閉じることができませんでした。",
    },
    nl: {
      pdf_generation_failed: "Genereren van PDF is mislukt.",
      window_close_failed: "Venster sluiten is mislukt.",
    },
    pl: {
      pdf_generation_failed: "Nie udało się wygenerować PDF.",
      window_close_failed: "Nie udało się zamknąć okna.",
    },
    pt: {
      pdf_generation_failed: "Falha ao gerar PDF.",
      window_close_failed: "Falha ao fechar a janela.",
    },
    "pt-br": {
      pdf_generation_failed: "Falha ao gerar PDF.",
      window_close_failed: "Falha ao fechar a janela.",
    },
    ru: {
      pdf_generation_failed: "Не удалось сформировать PDF.",
      window_close_failed: "Не удалось закрыть окно.",
    },
    tr: {
      pdf_generation_failed: "PDF oluşturma başarısız oldu.",
      window_close_failed: "Pencere kapatılamadı.",
    },
    zh: {
      pdf_generation_failed: "生成 PDF 失败。",
      window_close_failed: "关闭窗口失败。",
    },
  };
  Object.keys(t).forEach(
    k =>
      (window.translations[k] = {
        ...(window.translations[k] || {}),
        ...t[k],
      })
  );
})();
