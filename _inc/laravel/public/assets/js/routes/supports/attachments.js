/**
 * @file Supports Attachments Guard
 * @description Guards attachment preview functionality using ERPGuard singleton
 * @requires ERPGuard
 * @requires ERPUtils
 * @requires jQuery
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;

  if (!guard || !utils || !$) {
    
    return;
  }

  const MSG_KEY = "attachment_preview_unavailable";
  const FALLBACK = "Attachment preview is unavailable.";

  try {
    const $input = $("#attachment");
    const $img = $("#image");

    if (!$input.length) {
      guard.scheduleError(utils.getTranslation(MSG_KEY) || FALLBACK);
      return;
    }

    if ($input.attr("data-np-bound") === "true") return;
    $input.attr("data-np-bound", "true");

    const onChange = function () {
      const file = this?.files?.[0];
      if (!file || !$img.length) {
        guard.showToast(utils.getTranslation(MSG_KEY) || FALLBACK, "error");
        return;
      }

      try {
        const prev = this.getAttribute("data-prev-url") ?? "";
        const url = URL.createObjectURL(file);
        $img.attr("src", url);
        if (prev) {
          try { URL.revokeObjectURL(prev); } catch {}
        }
        this.setAttribute("data-prev-url", url);
      } catch {
        guard.showToast(utils.getTranslation(MSG_KEY) || FALLBACK, "error");
      }
    };

    $input.on("change", onChange);

    const el = $input.get(0);
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(el)) {
        $input.off("change", onChange);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  } catch (e) {
    guard.showToast(FALLBACK, "error");
  }
})();
