(() => {
  if (!window.translations) {
    window.translations = {
      ar: { barcode_unavailable: "تعذّر إنشاء الباركود." },
      da: { barcode_unavailable: "Kunne ikke generere stregkode." },
      de: { barcode_unavailable: "Barcode konnte nicht erzeugt werden." },
      en: { barcode_unavailable: "Failed to generate barcode." },
      es: { barcode_unavailable: "No se pudo generar el código de barras." },
      fr: { barcode_unavailable: "Échec de la génération du code-barres." },
      he: { barcode_unavailable: "יצירת הברקוד נכשלה." },
      it: { barcode_unavailable: "Impossibile generare il codice a barre." },
      ja: { barcode_unavailable: "バーコードの生成に失敗しました。" },
      nl: { barcode_unavailable: "Barcodes genereren is mislukt." },
      pl: { barcode_unavailable: "Nie udało się wygenerować kodu kreskowego." },
      pt: { barcode_unavailable: "Falha ao gerar código de barras." },
      "pt-br": { barcode_unavailable: "Falha ao gerar código de barras." },
      ru: { barcode_unavailable: "Не удалось создать штрих-код." },
      tr: { barcode_unavailable: "Barkod oluşturulamadı." },
      zh: { barcode_unavailable: "生成条形码失败。" },
    };
  } else {
    const addKeys = {
      ar: { barcode_unavailable: "تعذّر إنشاء الباركود." },
      da: { barcode_unavailable: "Kunne ikke generere stregkode." },
      de: { barcode_unavailable: "Barcode konnte nicht erzeugt werden." },
      en: { barcode_unavailable: "Failed to generate barcode." },
      es: { barcode_unavailable: "No se pudo generar el código de barras." },
      fr: { barcode_unavailable: "Échec de la génération du code-barres." },
      he: { barcode_unavailable: "יצירת הברקוד נכשלה." },
      it: { barcode_unavailable: "Impossibile generare il codice a barre." },
      ja: { barcode_unavailable: "バーコードの生成に失敗しました。" },
      nl: { barcode_unavailable: "Barcodes genereren is mislukt." },
      pl: { barcode_unavailable: "Nie udało się wygenerować kodu kreskowego." },
      pt: { barcode_unavailable: "Falha ao gerar código de barras." },
      "pt-br": { barcode_unavailable: "Falha ao gerar código de barras." },
      ru: { barcode_unavailable: "Не удалось создать штрих-код." },
      tr: { barcode_unavailable: "Barkod oluşturulamadı." },
      zh: { barcode_unavailable: "生成条形码失败。" },
    };
    Object.keys(addKeys).forEach(
      k =>
        (window.translations[k] = Object.assign(
          {},
          window.translations[k] || {},
          addKeys[k]
        ))
    );
  }
})();
