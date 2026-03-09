(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);

  const listenerAttr = "data-leads-list-listener";
  $(() => {
    const bind = el => {
      if (!el || el.getAttribute(listenerAttr) === "true") return;
      el.setAttribute(listenerAttr, "true");
      const $el = $(el);
      const onClick = e => {
        try {
          const url = el.getAttribute("data-url");
          const href = el.getAttribute("href");
          if ((!url || url === "#") && (!href || href === "#")) {
            e.preventDefault();
            showError(getMsg("leads_unavailable"));
          }
        } catch {
          e.preventDefault();
          showError(getMsg("leads_unavailable"));
        }
      };
      $el.on("click.leadsGuard", onClick);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("click.leadsGuard", onClick);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    try {
      document.querySelectorAll(".lead-route-guard").forEach(bind);
    } catch {
      $(".lead-route-guard").toArray().forEach(bind);
    }
  });
})();
