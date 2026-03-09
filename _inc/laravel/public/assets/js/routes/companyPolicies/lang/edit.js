/** @requires ERPUtils (translations) */
(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      image_preview_failed: "فشل عرض المعاينة.",
    },
    da: {
      image_preview_failed: "Kunne ikke vise forhåndsvisning.",
    },
    de: {
      image_preview_failed: "Vorschau konnte nicht angezeigt werden.",
    },
    en: {
      image_preview_failed: "Failed to preview image.",
    },
    es: {
      image_preview_failed: "Error al mostrar la vista previa.",
    },
    fr: {
      image_preview_failed: "Échec de l’affichage de l’aperçu.",
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
