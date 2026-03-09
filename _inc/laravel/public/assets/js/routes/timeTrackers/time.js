(() => {
  const { guard, utils } = window.ERPBootstrap.require("ERPGuard", "ERPUtils");
  if (!guard) return;
  const $ = window.jQuery;
  const dataInitGuard = "data-timeentry-initialized";

  const showError = message => {
    guard.showToast(message);
  };

  const getMsg = (el, msgKey) => {
    return utils.getTranslation(msgKey) || "# ERROR";
  };

  const scheduleClickError = message => {
    document.addEventListener("click", () => showError(message), {
      once: true,
    });
  };

  const initTimeInputs = () => {
    if (!$ || !$.fn) {
      scheduleClickError(guard.getMsg("plugin_unavailable"));
      return;
    }
    if (!$.fn.timeEntry) {
      scheduleClickError(guard.getMsg("time_unavailable"));
      return;
    }
    const $targets = $('[data-type="times"]');
    if (!$targets.length) return;

    $targets.each(function () {
      const el = this;
      if ($(el).data("timeEntry")) return;
      try {
        $(el).timeEntry({ show24Hours: true });
      } catch (_) {
        scheduleClickError(guard.getMsg("time_unavailable"));
      }
    });
  };

  const body = document.body;
  if (body && body.getAttribute(dataInitGuard) !== "true") {
    body.setAttribute(dataInitGuard, "true");
    initTimeInputs();
    const mo = new MutationObserver(() => initTimeInputs());
    mo.observe(document.body, { childList: true, subtree: true });
  }
})();
