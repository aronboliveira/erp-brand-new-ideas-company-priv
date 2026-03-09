(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const fileInput = document.getElementById("document");
  const imgEl = document.getElementById("image");
  if (!fileInput || !imgEl) return;

  let lastUrl = "";

  const previewHandler = () => {
    try {
      const file = fileInput.files?.[0];
      if (!file) return;
      if (lastUrl) URL.revokeObjectURL(lastUrl);
      lastUrl = URL.createObjectURL(file);
      imgEl.src = lastUrl;
    } catch {
      const msg = getMsg("image_preview_failed");
      scheduleError(msg, "change");
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
      }),
    );
  }).observe(document.body, { childList: true, subtree: true });
})();
