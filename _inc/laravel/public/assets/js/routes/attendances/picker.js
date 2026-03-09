(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const $ = window.jQuery;
  const selector = ".daterangepicker";
  const dataBound = "data-datepicker";

  const handleClick = el => {
    try {
      if (
        typeof $ !== "function" ||
        typeof $.fn.daterangepicker !== "function"
      ) {
        scheduleError(getMsg("datepicker_unavailable"));
        return;
      }
      $(el).daterangepicker({
        format: "yyyy-mm-dd",
        locale: { format: "YYYY-MM-DD" },
      });
    } catch (_) {
      scheduleError(getMsg("datepicker_unavailable"));
    }
  };

  try {
    const pickers = document.querySelectorAll(selector);
    if (!pickers.length) return;

    pickers.forEach(el => {
      if (el.getAttribute(dataBound) === "true") return;
      el.setAttribute(dataBound, "true");
      el.addEventListener("click", () => handleClick(el));
    });
  } catch (_) {
    scheduleError(getMsg("datepicker_unavailable"));
  }
})();
