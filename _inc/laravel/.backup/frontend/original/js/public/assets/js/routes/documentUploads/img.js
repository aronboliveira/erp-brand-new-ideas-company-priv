(() => {
  const fileInput = document.getElementById("document");
  const imgEl = document.getElementById("image");
  if (!fileInput || !imgEl) return;

  const langShort = () => {
    const l = (
      sessionStorage.getItem("erp-np-lang") ||
      document.documentElement.lang ||
      "en"
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
    window.show_toastr ? window.show_toastr("error", m, "error") : alert(m);

  let lastUrl = "";

  const previewHandler = () => {
    try {
      const file = fileInput.files?.[0];
      if (!file) return;
      if (lastUrl) URL.revokeObjectURL(lastUrl);
      lastUrl = URL.createObjectURL(file);
      imgEl.src = lastUrl;
    } catch {
      toast(t("image_preview_failed"));
    }
  };

  fileInput.addEventListener("change", previewHandler);
  new MutationObserver((ms, obs) => {
    ms.forEach(m =>
      m.removedNodes.forEach(n => {
        if (n === fileInput) {
          fileInput.removeEventListener("change", previewHandler);
          if (lastUrl) URL.revokeObjectURL(lastUrl);
          obs.disconnect();
        }
      })
    );
  }).observe(document.body, { childList: true, subtree: true });
})();
