(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  if (!guard || !utils) return;

  const dataBindGuard = "data-dismiss-bound";

  const showError = message => {
    guard.showToast(message);
  };

  const scheduleClickError = msg => {
    document.addEventListener("click", () => showError(msg), { once: true });
  };

  const localize = (el, key) => {
    const msg = utils.getTranslation(key) || "# ERROR";
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ||
        document.documentElement.lang ||
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const hideAlert = closeEl => {
    try {
      const target = qs("#error_alert");
      if (!target) {
        scheduleClickError(localize(closeEl, "dismiss_unavailable"));
        return;
      }
      target.style.display = "none";
    } catch (_) {
      scheduleClickError(localize(closeEl, "dismiss_unavailable"));
    }
  };
  const bind = () => {
    const closeEl = qs("#close_alert");
    if (!closeEl) return;
    if (closeEl.getAttribute(dataBindGuard) === "true") return;
    closeEl.setAttribute(dataBindGuard, "true");
    const onClick = function (e) {
      e.preventDefault();
      hideAlert(this);
    };
    closeEl.addEventListener("click", onClick, { passive: false });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(closeEl)) {
        closeEl.removeEventListener("click", onClick);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  else bind();
})();
