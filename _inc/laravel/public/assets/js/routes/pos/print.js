(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);

  const onClick = () => {
    try {
      $(".row, .toast, #print").addClass("d-none");
      window.print();
      $(".row, .toast, #print").removeClass("d-none");
    } catch {
      showError(getMsg("print_unavailable"));
    }
  };

  const listenerAttr = "data-print-listener";
  $(() => {
    const btn = $("#print");
    if (!btn.length) return;
    const el = btn.get(0);
    if (el.getAttribute(listenerAttr) === "true") return;
    el.setAttribute(listenerAttr, "true");
    btn.on("click", onClick);
    const obs = new MutationObserver(() => {
      if (!document.body.contains(el)) {
        try {
          btn.off("click", onClick);
        } catch {}
        obs.disconnect();
      }
    });
    obs.observe(document.body, { childList: true, subtree: true });
  });
})();
