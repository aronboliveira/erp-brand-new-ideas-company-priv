(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    return;
  }

  document.addEventListener("DOMContentLoaded", () => {
    try {
      if (
        typeof jQuery === "undefined" ||
        typeof jQuery.fn.daterangepicker !== "function"
      ) {
        scheduleError(getMsg("datepicker_plugin_unavailable"), "click");
        return;
      }
      const els = document.querySelectorAll(".datepicker");
      if (!els.length) return;
      els.forEach(el => {
        const locale = window.date_picker_locale || {};
        jQuery(el).daterangepicker({
          singleDatePicker: true,
          locale: { ...locale, format: "YYYY-MM-DD" },
        });
      });
    } catch (_) {
      scheduleError(getMsg("datepicker_init_failed"), "click");
    }
  });
})();
