(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);
  const scheduleError = msg => {
    document.addEventListener("pointerup", () => showError(msg), {
      once: true,
    });
  };

  const listenerAttr = "data-pos-show-listener";
  $(() => {
    const bind = el => {
      if (!el || el.getAttribute(listenerAttr) === "true") return;
      el.setAttribute(listenerAttr, "true");
      const $el = $(el);
      const handler = e => {
        try {
          const url = el.getAttribute("data-url");
          const href =
            el.getAttribute("href") ||
            (el.form ? el.form.getAttribute("action") : null);
          if ((!url || url === "#") && (!href || href === "#")) {
            e.preventDefault();
            showError(getMsg("pos_unavailable"));
          }
        } catch {
          e.preventDefault();
          showError(getMsg("pos_unavailable"));
        }
      };
      $el.on("click.posGuard", handler);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("click.posGuard", handler);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    try {
      document.querySelectorAll(".payment-done-btn").forEach(bind);
    } catch {
      $(".payment-done-btn").toArray().forEach(bind);
    }
  });
})();
