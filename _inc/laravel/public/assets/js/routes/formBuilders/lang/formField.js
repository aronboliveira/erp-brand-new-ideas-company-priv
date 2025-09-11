(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      lead_toggle_failed: "فشل تبديل حالة العميل المحتمل.",
    },
    da: {
      lead_toggle_failed: "Kunne ikke ændre lead-tilstand.",
    },
    de: {
      lead_toggle_failed: "Lead-Zustand konnte nicht geändert werden.",
    },
    en: {
      lead_toggle_failed: "Failed to toggle lead status.",
    },
    es: {
      lead_toggle_failed: "Error al cambiar el estado del lead.",
    },
    fr: {
      lead_toggle_failed: "Échec du basculement de l’état du lead.",
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
