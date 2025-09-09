(() => {
  if (!window.translations) {
    window.translations = {};
  }
  const t = {
    ar: {
      pipeline_change_failed: "فشل تغيير مسار العملية.",
    },
    da: {
      pipeline_change_failed: "Kunne ikke ændre pipeline.",
    },
    de: {
      pipeline_change_failed: "Pipeline konnte nicht gewechselt werden.",
    },
    en: {
      pipeline_change_failed: "Failed to change pipeline.",
    },
    es: {
      pipeline_change_failed: "Error al cambiar el pipeline.",
    },
    fr: {
      pipeline_change_failed: "Échec du changement de pipeline.",
    },
    he: {
      pipeline_change_failed: "שינוי הצנרת נכשל.",
    },
    it: {
      pipeline_change_failed: "Impossibile cambiare pipeline.",
    },
    ja: {
      pipeline_change_failed: "パイプラインの変更に失敗しました。",
    },
    nl: {
      pipeline_change_failed: "Kon pipeline niet wijzigen.",
    },
    pl: {
      pipeline_change_failed: "Nie udało się zmienić pipeline.",
    },
    pt: {
      pipeline_change_failed: "Falha ao alterar pipeline.",
    },
    "pt-br": {
      pipeline_change_failed: "Falha ao alterar pipeline.",
    },
    ru: {
      pipeline_change_failed: "Не удалось сменить воронку.",
    },
    tr: {
      pipeline_change_failed: "Pipeline değiştirilemedi.",
    },
    zh: {
      pipeline_change_failed: "更改管道失败。",
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
