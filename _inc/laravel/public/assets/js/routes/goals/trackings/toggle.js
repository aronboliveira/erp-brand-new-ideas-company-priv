(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getLocalizedMessage = (el, key) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const showError = msg => {
    guard.showToast(msg);
  };

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toggleswitch").forEach(el => {
      try {
        if (typeof $(el).bootstrapToggle !== "function") {
          throw new Error("bootstrapToggle missing");
        }
        $(el).bootstrapToggle();
      } catch {
        const msg = getLocalizedMessage(el, "toggle_init_failed");
        el.addEventListener("click", () => showError(msg), { once: true });
      }
    });

    const starSelector = "fieldset[id^='demo'] .stars";
    const handleStarClick = e => {
      const tgt = e.target;
      if (!tgt.matches(starSelector)) return;
      try {
        if (tgt.value) guard.showToast(`Rating: ${tgt.value}`, "info");
        tgt.checked = true;
      } catch {
        const msg = getLocalizedMessage(tgt, "star_click_failed");
        tgt.addEventListener("pointerup", () => showError(msg), { once: true });
      }
    };

    if (!document.body.hasAttribute("data-star-listener")) {
      document.body.addEventListener("click", handleStarClick);
      document.body.setAttribute("data-star-listener", "true");
      const mo = new MutationObserver(() => {
        if (!document.querySelector(starSelector)) {
          mo.disconnect();
          document.body.removeEventListener("click", handleStarClick);
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
    }
  });
})();
