(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const previewIframe = document.getElementById(
      "invoice-template-preview-frame",
    );
    if (!previewIframe) {
      return;
    }
    if (previewIframe.getAttribute("data-listener-active") === "true") {
      return;
    }
    previewIframe.setAttribute("data-listener-active", "true");
    const src = previewIframe.getAttribute("src") ?? "#";
    const url = previewIframe.getAttribute("data-url") ?? src ?? "#";
    if (url !== "#" && src !== "#") {
      return;
    }
    const msg =
      previewIframe.getAttribute("data-guard-msg") ||
      getMsg("invoice_preview_unavailable");
    scheduleError(msg, "load");
    previewIframe.setAttribute("data-failed-route", "true");
  } catch (err) {}
})();
