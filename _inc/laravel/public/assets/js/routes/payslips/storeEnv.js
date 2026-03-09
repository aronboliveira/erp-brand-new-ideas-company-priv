(() => {
  const { guard, utils } = window.ERPBootstrap.require("ERPGuard", "ERPUtils");
  if (!guard) return;
  const showError = message => {
    guard.showToast(message);
  };

  const scheduleClickError = message => {
    document.addEventListener("click", () => showError(message), {
      once: true,
    });
  };

  const getMsg = (el, key) => {
    return utils.getTranslation(key) || "# ERROR";
  };

  const qs = (s, r = document) => r.querySelector(s);

  const safeDisplay = (el, show) => {
    if (!el) return false;
    el.style.display = show ? "block" : "none";
    return true;
  };

  const checkEnvironment = val => {
    try {
      const el = qs("#environment_text_input");
      const ok = safeDisplay(el, val === "other");
      if (!ok)
        scheduleClickError(
          guard.getMsg("env_toggle_unavailable"),
        );
    } catch (_) {
      scheduleClickError(guard.getMsg("env_toggle_unavailable"));
    }
  };

  const showDatabaseSettings = () => {
    try {
      const el = qs("#tab2");
      if (!el) {
        scheduleClickError(guard.getMsg("tab_db_unavailable"));
        return;
      }
      el.checked = true;
    } catch (_) {
      scheduleClickError(guard.getMsg("tab_db_unavailable"));
    }
  };

  const showApplicationSettings = () => {
    try {
      const el = qs("#tab3");
      if (!el) {
        scheduleClickError(guard.getMsg("tab_app_unavailable"));
        return;
      }
      el.checked = true;
    } catch (_) {
      scheduleClickError(guard.getMsg("tab_app_unavailable"));
    }
  };

  window.checkEnvironment = checkEnvironment;
  window.showDatabaseSettings = showDatabaseSettings;
  window.showApplicationSettings = showApplicationSettings;
})();
