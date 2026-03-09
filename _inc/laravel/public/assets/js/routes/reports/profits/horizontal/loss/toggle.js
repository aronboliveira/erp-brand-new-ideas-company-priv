(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;
  if (!$ || !$.fn) {
    scheduleError(getMsg("toggle_unavailable"));
    return;
  }

  const dataFilterGuard = "data-filter-guard";

  const btn = document.getElementById("filter");
  const panel = document.getElementById("show_filter");
  if (!btn || btn.getAttribute(dataFilterGuard) === "true") return;

  btn.setAttribute(dataFilterGuard, "true");

  const handler = function () {
    try {
      if (panel) {
        $("#show_filter").toggle();
      } else {
        scheduleError(getMsg("toggle_unavailable"));
      }
    } catch (_) {
      scheduleError(getMsg("toggle_unavailable"));
    }
  };

  $(btn).on("click", handler);
})();
