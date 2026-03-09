(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const ATTACH_SELECTOR = "#attachment";
  const IMAGE_SELECTOR = "#image";
  const attachEl = document.querySelector(ATTACH_SELECTOR);
  const imageEl = document.querySelector(IMAGE_SELECTOR);
  if (!attachEl || !imageEl) return;

  const handler = e => {
    try {
      const file = e.target.files?.[0];
      if (!file) return;
      imageEl.src = URL.createObjectURL(file);
    } catch {
      const msg = getMsg("image_preview_failed");
      scheduleError(msg, "change");
    }
  };

  attachEl.addEventListener("change", handler, false);

  const mo = new MutationObserver((_, obs) => {
    if (!document.body.contains(attachEl)) {
      attachEl.removeEventListener("change", handler);
      obs.disconnect();
    }
  });
  mo.observe(document.body, { childList: true, subtree: true });
})();
