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

  try {
    if (!$.fn.daterangepicker) throw new Error("daterangepicker unavailable");
    const els = document.querySelectorAll(".datepicker");
    if (!els.length) return;
    const opts = {
      singleDatePicker: true,
      locale: window.date_picker_locale ?? { format: "YYYY-MM-DD" },
    };
    els.forEach(el => $(el).daterangepicker(opts));
  } catch {
    scheduleError(getMsg("date_picker_init_failed"));
  }
})();
