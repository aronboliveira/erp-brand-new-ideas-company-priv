(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;
  if (!$) {
    scheduleError(getMsg("plugin_unavailable"));
    return;
  }

  const dataInit = "data-date-sync-init";
  const dataListener = "data-date-sync-listener";

  const syncDates = () => {
    try {
      const startVal = $(".startDate").val() ?? "";
      const endVal = $(".endDate").val() ?? "";
      const hasTargets = $(".start_date").length + $(".end_date").length > 0;
      if (!hasTargets) {
        scheduleError(getMsg("date_sync_failed"));
        return;
      }
      $(".start_date").val(startVal);
      $(".end_date").val(endVal);
    } catch (_) {
      scheduleError(getMsg("date_sync_failed"));
    }
  };

  const init = () => {
    const root = document.documentElement;
    if (root.getAttribute(dataInit) === "true") return;
    root.setAttribute(dataInit, "true");

    syncDates();

    document.querySelectorAll(".startDate, .endDate").forEach(el => {
      if (el.getAttribute(dataListener) === "true") return;
      el.setAttribute(dataListener, "true");
      $(el).on("change", syncDates);
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
