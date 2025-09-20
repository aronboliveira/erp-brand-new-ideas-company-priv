(() => {
  if (!window.translations) {
    window.translations = {
      ar: { savepdf_unavailable: "تعذّر إنشاء ملف PDF." },
      da: { savepdf_unavailable: "Kunne ikke oprette PDF." },
      de: { savepdf_unavailable: "PDF konnte nicht erstellt werden." },
      en: { savepdf_unavailable: "Failed to create PDF." },
      es: { savepdf_unavailable: "No se pudo crear el PDF." },
      fr: { savepdf_unavailable: "Échec de la création du PDF." },
      he: { savepdf_unavailable: "יצירת ה-PDF נכשלה." },
      it: { savepdf_unavailable: "Impossibile creare il PDF." },
      ja: { savepdf_unavailable: "PDF を作成できませんでした。" },
      nl: { savepdf_unavailable: "PDF maken is mislukt." },
      pl: { savepdf_unavailable: "Nie udało się utworzyć pliku PDF." },
      pt: { savepdf_unavailable: "Falha ao criar o PDF." },
      "pt-br": { savepdf_unavailable: "Falha ao criar o PDF." },
      ru: { savepdf_unavailable: "Не удалось создать PDF." },
      tr: { savepdf_unavailable: "PDF oluşturulamadı." },
      zh: { savepdf_unavailable: "创建 PDF 失败。" },
    };
  } else {
    const patch = {
      en: { savepdf_unavailable: "Failed to create PDF." },
    };
    Object.keys(patch).forEach(
      k =>
        (window.translations[k] = Object.assign(
          {},
          window.translations[k] || {},
          patch[k]
        ))
    );
  }
})();
