(function () {
  const $ = window.jQuery;
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const scheduleError = msg => guard?.scheduleError?.("click", msg);
  const getMsg = key => utils?.getMsg?.(key) ?? "# ERROR";
  const dataBound = "data-pipeline-bound";
  const selector = '.change-pipeline select[name="default_pipeline_id"]';

  const verifyRoute = form => {
    const url = form?.getAttribute?.("data-url") ?? "";
    const href = form?.action ?? "";
    if ((!url || url === "#") && (!href || href === "#")) return false;
    return true;
  };
  $(() => {
    if (!($ && $.fn)) {
      scheduleError(getMsg("plugin_unavailable"));
      return;
    }
    const body = document.body;
    if (body.getAttribute(dataBound) === "true") return;
    body.setAttribute(dataBound, "true");
    const handler = function () {
      try {
        const formEl = document.querySelector("#change-pipeline");
        if (!formEl) {
          scheduleError(getMsg("pipeline_unavailable"));
          return;
        }
        if (!verifyRoute(formEl)) {
          scheduleError(getMsg("route_unavailable"));
          return;
        }
        $(formEl).trigger("submit");
      } catch (_) {
        scheduleError(getMsg("pipeline_unavailable"));
      }
    };
    $(document).on("change", selector, handler);
    const mo = new MutationObserver(function () {
      if (!document.querySelector(selector)) {
        try {
          $(document).off("change", selector, handler);
        } catch (_) {}
        body.removeAttribute(dataBound);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  });
})();
