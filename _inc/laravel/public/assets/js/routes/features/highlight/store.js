(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  const form = document.getElementById("highlight-feature-store-form");
  if (form) {
    form.addEventListener(
      "submit",
      e => {
        const url =
          form.getAttribute("action") || form.getAttribute("data-url") || "#";
        if (!url || url === "#") {
          e.preventDefault();
          const msg =
            form.getAttribute("data-guard-msg") ||
            getMsg("store_highlight_unavailable");
          scheduleError(msg, "submit");
        }
      },
      { passive: false },
    );
  }
  const input = document.getElementById("highlight_feature_image");
  const img = document.getElementById("image1");
  if (input && img) {
    input.addEventListener("change", () => {
      const f = input.files && input.files[0];
      if (!f) return;
      img.src = URL.createObjectURL(f);
    });
  }
})();
