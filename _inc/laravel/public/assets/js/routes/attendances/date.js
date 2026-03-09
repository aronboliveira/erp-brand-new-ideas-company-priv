(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;
  const selector = ".daterangepicker";
  const dataBound = "data-dp-listener";

  try {
    const pickers = document.querySelectorAll(selector);
    pickers.forEach(el => {
      if (el.getAttribute(dataBound) === "true") return;
      el.setAttribute(dataBound, "true");
      el.addEventListener("click", () => {
        try {
          if (
            typeof $ !== "function" ||
            typeof $.fn.daterangepicker !== "function"
          ) {
            scheduleError(getMsg("date_picker_unavailable"));
            return;
          }
          $(el).daterangepicker({
            format: "yyyy-mm-dd",
            locale: { format: "YYYY-MM-DD" },
          });
        } catch (_) {
          scheduleError(getMsg("date_picker_unavailable"));
        }
      });
    });
  } catch (_) {
    scheduleError(getMsg("date_picker_unavailable"));
  }
})();
