/**
 * @fileoverview TypeScript version of public/assets/js/routes/documentUploads/img.js
 * @generated from original JavaScript - manual review recommended
 * @module img
 */

((): void => {
  const fileInput = document.getElementById(
      "document",
    ) as HTMLInputElement | null,
    imgEl = document.getElementById("image") as HTMLImageElement | null;
  if (!fileInput || !imgEl) return;
  const langShort = (): string => {
    const l = (
      sessionStorage.getItem("erp-np-lang") ??
      document.documentElement.lang ??
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    return l === "pt-br" ? l : l.slice(0, 2);
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const t = (k: string) =>
    window.translations?.[langShort()]?.[k] ??
    window.translations?.en?.[k] ??
    "# ERROR";
  const toast = (m: string): void => {
    window.show_toastr ? window.show_toastr("error", m, "error") : alert(m);
  };

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

  if (!fileInput.getAttribute("data-listener-bound-change")) {
    fileInput.setAttribute("data-listener-bound-change", "1");
    fileInput.addEventListener("change", previewHandler);
  }
  new MutationObserver((ms, obs) => {
    ms.forEach(m => {
      m.removedNodes.forEach(n => {
        if (n === fileInput) {
          fileInput.removeEventListener("change", previewHandler);
          if (lastUrl !== "") URL.revokeObjectURL(lastUrl);
          obs.disconnect();
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });
})();

export {};
