/**
 * @fileoverview TypeScript version of public/assets/js/routes/documentUploads/img.js
 * @generated from original JavaScript - manual review recommended
 * @module img
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return */

((): void => {
  const fileInput = document.getElementById("document");
  const imgEl = document.getElementById("image");
  if (!fileInput || !imgEl) return;

  const langShort = (): void => {
    const l = (
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ?? "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    return l === "pt-br" ? l : l.slice(0, 2);
  };
  const t = k =>
    window.translations?.[langShort()]?.[k] ??
    window.translations?.en?.[k] ??
    "# ERROR";
  const toast = m =>
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    { window.show_toastr ? window.show_toastr("error", m, "error") : alert(m); };

  let lastUrl = "";

  const previewHandler = (): void => {
    try {
      const file = fileInput.files?.[0];
      if (!file) return;
      if (lastUrl !== "") URL.revokeObjectURL(lastUrl);
      lastUrl = URL.createObjectURL(file);
      imgEl.src = lastUrl;
    } catch {
      toast(t("image_preview_failed"));
    }
  };

  fileInput.addEventListener("change", previewHandler);
  new MutationObserver((ms, obs) => {
    ms.forEach(m =>
      { m.removedNodes.forEach(n => {
        if (n === fileInput) {
          fileInput.removeEventListener("change", previewHandler);
          if (lastUrl !== "") URL.revokeObjectURL(lastUrl);
          obs.disconnect();
        }
      }); }
    );
  }).observe(document.body, { childList: true, subtree: true });
})();

export {};
