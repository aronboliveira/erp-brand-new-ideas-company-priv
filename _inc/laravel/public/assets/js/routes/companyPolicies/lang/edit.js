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
    he: {
      image_preview_failed: "תצוגה מקדימה של התמונה נכשלה.",
    },
    it: {
      image_preview_failed: "Anteprima immagine non riuscita.",
    },
    ja: {
      image_preview_failed: "画像プレビューに失敗しました。",
    },
    nl: {
      image_preview_failed: "Afbeelding kon niet worden weergegeven.",
    },
    pl: {
      image_preview_failed: "Nie udało się wyświetlić podglądu obrazu.",
    },
    pt: {
      image_preview_failed: "Falha na pré‑visualização da imagem.",
    },
    "pt-br": {
      image_preview_failed: "Falha na pré‑visualização da imagem.",
    },
    ru: {
      image_preview_failed: "Не удалось просмотреть изображение.",
    },
    tr: {
      image_preview_failed: "Resim ön izlemesi başarısız.",
    },
    zh: {
      image_preview_failed: "图像预览失败。",
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
