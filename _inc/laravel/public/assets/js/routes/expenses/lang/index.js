(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      url_copy_success: "تم نسخ الرابط إلى الحافظة.",
      url_copy_failed: "فشل نسخ الرابط.",
    },
    da: {
      url_copy_success: "URL kopieret til udklipsholder.",
      url_copy_failed: "Kunne ikke kopiere URL.",
    },
    de: {
      url_copy_success: "URL in die Zwischenablage kopiert.",
      url_copy_failed: "Konnte URL nicht kopieren.",
    },
    en: {
      url_copy_success: "URL copied to clipboard.",
      url_copy_failed: "Failed to copy URL.",
    },
    es: {
      url_copy_success: "URL copiada al portapapeles.",
      url_copy_failed: "Error al copiar la URL.",
    },
    fr: {
      url_copy_success: "URL copiée dans le presse-papiers.",
      url_copy_failed: "Échec de la copie de l’URL.",
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
