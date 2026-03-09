(() => {
  const guard = typeof window !== "undefined" ? window.ERPGuard : null;
  const utils = typeof window !== "undefined" ? window.ERPUtils : null;
  const $ = window.jQuery;
  if (!guard || !utils || !$) return;

  const getMsg = key => utils.getTranslation(key) || "# ERROR";
  const showError = msg => guard.showToast(msg);
  const scheduleError = msg => {
    document.addEventListener("click", () => showError(msg), { once: true });
  };

  const $input = $("#attachment");
  const $img = $("#image");
  if (!$input.length) {
    scheduleError(getMsg("attachment_preview_unavailable"));
    return;
  }

  const onChange = function () {
    const file = this?.files?.[0];
    if (!file || !$img.length) {
      scheduleError(getMsg("attachment_preview_unavailable"));
      return;
    }
    try {
      const prev = this.getAttribute("data-prev-url") ?? "";
      const url = URL.createObjectURL(file);
      $img.attr("src", url);
      if (prev) {
        try {
          URL.revokeObjectURL(prev);
        } catch {}
      }
      this.setAttribute("data-prev-url", url);
    } catch {
      scheduleError(getMsg("attachment_preview_unavailable"));
    }
  };

  const listenerAttr = "data-attachment-listener";
  if ($input.attr(listenerAttr) !== "true") {
    $input.on("change", onChange);
    $input.attr(listenerAttr, "true");
    const el = $input.get(0);
    const obs = new MutationObserver(() => {
      if (!document.body.contains(el)) {
        $input.off("change", onChange);
        obs.disconnect();
      }
    });
    obs.observe(document.body, { childList: true, subtree: true });
  }
})();
