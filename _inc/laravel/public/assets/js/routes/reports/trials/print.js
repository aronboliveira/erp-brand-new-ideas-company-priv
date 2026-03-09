(function () {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("click", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";
  const dataPrintBound = "data-print-init-bound";

  const goBack = () => {
    try {
      window.close();
    } catch (_) {}
    try {
      if (window.history && typeof window.history.back === "function") {
        window.history.back();
      }
    } catch (_) {}
  };
  const init = () => {
    const root = document.documentElement;
    if (root.getAttribute(dataPrintBound) === "true") {
      return;
    }
    root.setAttribute(dataPrintBound, "true");
    let printed = false;
    const onAfterPrint = () => {
      printed = true;
      goBack();
    };
    if ("onafterprint" in window) {
      window.onafterprint = onAfterPrint;
    } else {
      window.addEventListener("afterprint", onAfterPrint);
    }
    try {
      if (typeof window.print === "function") {
        window.print();
      } else {
        scheduleError(getMsg("print_unavailable"));
      }
    } catch (_) {
      scheduleError(getMsg("print_unavailable"));
    }
    setTimeout(function () {
      if (!printed) {
        scheduleError(getMsg("print_unavailable"));
      }
    }, 2000);
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
