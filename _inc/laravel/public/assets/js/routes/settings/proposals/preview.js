(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    const ifr = document.getElementById("proposal-template-preview-frame");
    if (!ifr) {
      return;
    }
    if (ifr.getAttribute("data-listener-active") === "true") {
      return;
    }
    ifr.setAttribute("data-listener-active", "true");
    const src = ifr.getAttribute("src") ?? "#";
    const url = ifr.getAttribute("data-url") ?? src ?? "#";
    if (url !== "#" && src !== "#") {
      return;
    }
    const msg =
      ifr.getAttribute("data-guard-msg") ||
      getMsg("proposal_preview_unavailable");
    scheduleError(msg, "load");
    ifr.setAttribute("data-failed-route", "true");
  } catch (err) {}
})();
