(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      link_copy_success: "تم نسخ الرابط إلى الحافظة.",
      link_copy_failed: "فشل نسخ الرابط.",
    },
    da: {
      link_copy_success: "Link kopieret til udklipsholder.",
      link_copy_failed: "Kunne ikke kopiere link.",
    },
    de: {
      link_copy_success: "Link in die Zwischenablage kopiert.",
      link_copy_failed: "Kopieren des Links fehlgeschlagen.",
    },
    en: {
      link_copy_success: "Link copied to clipboard.",
      link_copy_failed: "Failed to copy link.",
    },
    es: {
      link_copy_success: "Enlace copiado al portapapeles.",
      link_copy_failed: "Error al copiar el enlace.",
    },
    fr: {
      link_copy_success: "Lien copié dans le presse-papiers.",
      link_copy_failed: "Échec de la copie du lien.",
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
