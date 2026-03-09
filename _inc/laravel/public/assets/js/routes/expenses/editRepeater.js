(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  function getMsg(key, el) {
    return utils.getTranslation(key) || "# ERROR";
  }

  function showError(message) {
    guard.showToast(message);
  }

  const listenerAttr = "data-rep-del-listener";
  document.querySelectorAll("[data-repeater-delete]").forEach(el => {
    if (el.getAttribute(listenerAttr) === "true") return;
    el.setAttribute(listenerAttr, "true");
    el.addEventListener("click", () => {
      try {
        $(".price").change();
        $(".discount").change();
      } catch {
        showError(getMsg("repeater_delete_failed", el));
      }
    });
    const obs = new MutationObserver((mutations, o) => {
      if (!document.body.contains(el)) {
        el.removeEventListener("click", null);
        o.disconnect();
      }
    });
    obs.observe(document.body, { childList: true, subtree: true });
  });
})();
