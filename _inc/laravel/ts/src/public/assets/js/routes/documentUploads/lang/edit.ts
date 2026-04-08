/**
 * @fileoverview TypeScript version of public/assets/js/routes/documentUploads/lang/edit.js
 * @generated from original JavaScript — automated migration
 * @module edit
 */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
export {};
declare global {
  interface Window {
    translations?: Record<string, Record<string, string>>;
  }
}

((): void => {
  const imgPrevPatch = {
    ar: { image_preview_failed: "تعذر معاينة الصورة." },
    da: { image_preview_failed: "Kunne ikke forhåndsvise billedet." },
    de: { image_preview_failed: "Bildvorschau fehlgeschlagen." },
    en: { image_preview_failed: "Image preview failed." },
    es: { image_preview_failed: "No se pudo previsualizar la imagen." },
    fr: { image_preview_failed: "Échec de l’aperçu de l’image." },
    he: { image_preview_failed: "תצוגה מקדימה של התמונה נכשלה." },
    it: { image_preview_failed: "Anteprima immagine non riuscita." },
    ja: { image_preview_failed: "画像プレビューに失敗しました。" },
    nl: { image_preview_failed: "Afbeelding kon niet worden weergegeven." },
    pl: { image_preview_failed: "Nie udało się wyświetlić podglądu obrazu." },
    pt: { image_preview_failed: "Falha na pré‑visualização da imagem." },
    "pt-br": { image_preview_failed: "Falha na pré‑visualização da imagem." },
    ru: { image_preview_failed: "Не удалось просмотреть изображение." },
    tr: { image_preview_failed: "Resim ön izlemesi başarısız." },
    zh: { image_preview_failed: "图像预览失败。" },
  };
  window.translations = Object.keys(window.translations || {}).length
    ? Object.keys(imgPrevPatch).reduce((a: Record<string, Record<string, string>>, l) => {
        a[l] = { ...(a[l] || {}), ...(imgPrevPatch as Record<string, Record<string, string>>)[l] };
        return a;
      }, window.translations!)
    : imgPrevPatch;
})();
