(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);

  const listenerAttr = "data-ld-listener";
  $(() => {
    const bindClick = el => {
      if (!el || el.getAttribute(listenerAttr) === "true") return;
      el.setAttribute(listenerAttr, "true");
      const $el = $(el);
      const handler = e => {
        try {
          const url = el.getAttribute("data-url");
          const href = el.getAttribute("href");
          if ((!url || url === "#") && (!href || href === "#")) {
            e.preventDefault();
            showError(getMsg("ld_unavailable"));
          }
        } catch {
          e.preventDefault();
          showError(getMsg("ld_unavailable"));
        }
      };
      $el.on("click.ldGuard", handler);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("click.ldGuard", handler);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    const bindPointerUp = el => {
      if (!el || el.getAttribute(listenerAttr) === "true") return;
      el.setAttribute(listenerAttr, "true");
      const $el = $(el);
      const handler = e => {
        try {
          const url = el.getAttribute("data-url");
          const href = el.form
            ? el.form.getAttribute("action")
            : el.getAttribute("action");
          if ((!url || url === "#") && (!href || href === "#")) {
            e.preventDefault();
            showError(getMsg("ld_unavailable"));
          }
        } catch {
          e.preventDefault();
          showError(getMsg("ld_unavailable"));
        }
      };
      $el.on("pointerup.ldSubmitGuard", handler);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("pointerup.ldSubmitGuard", handler);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    try {
      document.querySelectorAll(".ld-route-guard").forEach(bindClick);
    } catch {
      $(".ld-route-guard").toArray().forEach(bindClick);
    }
    const submitEl = document.getElementById("lead-submit");
    if (submitEl) bindPointerUp(submitEl);
  });
})();
