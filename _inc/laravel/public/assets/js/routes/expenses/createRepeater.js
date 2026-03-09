(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils) return;

  let errorMessage = "";

  const getLocalizedMessage = (msgKey, el) => {
    return utils.getTranslation(msgKey) || "# ERROR";
  };

  const showError = message => {
    guard.showToast(message);
  };

  const onPointerUp = () => {
    if (errorMessage) {
      showError(errorMessage);
      errorMessage = "";
    }
  };

  document.addEventListener("pointerup", onPointerUp);

  document.querySelectorAll("[data-repeater-delete]").forEach(el => {
    if (el.getAttribute("data-guard-listener-active") === "true") return;
    el.setAttribute("data-guard-listener-active", "true");
    el.addEventListener("click", () => {
      try {
        $(".price").change();
        $(".discount").change();
      } catch {
        errorMessage = getLocalizedMessage("repeater_delete_failed", el);
      }
    });
  });

  new MutationObserver((muts, obs) => {
    muts.forEach(m =>
      m.removedNodes.forEach(n => {
        if (n === document.documentElement) {
          document.removeEventListener("pointerup", onPointerUp);
          obs.disconnect();
        }
      }),
    );
  }).observe(document.body, { childList: true, subtree: true });
})();
