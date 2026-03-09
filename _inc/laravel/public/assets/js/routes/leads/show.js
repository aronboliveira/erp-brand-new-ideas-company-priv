(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);

  const listenerAttr = "data-leads-show-listener";
  $(() => {
    const bindClick = el => {
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
      $el.on("click.leadsShowGuard", onClick);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("click.leadsShowGuard", onClick);
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
      const onPointerUp = e => {
        try {
          const url = el.getAttribute("data-url");
          const href = el.form
            ? el.form.getAttribute("action")
            : el.getAttribute("action");
          if ((!url || url === "#") && (!href || href === "#")) {
            e.preventDefault();
            showError(getMsg("leads_unavailable"));
          }
        } catch {
          e.preventDefault();
          showError(getMsg("leads_unavailable"));
        }
      };
      $el.on("pointerup.leadsDeleteGuard", onPointerUp);
      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          try {
            $el.off("pointerup.leadsDeleteGuard", onPointerUp);
          } catch {}
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    };
    try {
      document.querySelectorAll(".lead-route-guard").forEach(bindClick);
    } catch {
      $(".lead-route-guard").toArray().forEach(bindClick);
    }
    try {
      document.querySelectorAll(".lead-delete-guard").forEach(bindPointerUp);
    } catch {
      $(".lead-delete-guard").toArray().forEach(bindPointerUp);
    }
  });
})();
