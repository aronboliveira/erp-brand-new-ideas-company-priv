(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const homeTab = document.getElementById("pills-home-tab");
    const listenerAttr = "data-daily-purchase-nav-listener-added";
    if (homeTab && homeTab.getAttribute(listenerAttr) !== "true") {
      homeTab.setAttribute(listenerAttr, "true");
      homeTab.addEventListener("click", e => {
        e.preventDefault();
        const url = homeTab.getAttribute("data-url");
        const href = homeTab.getAttribute("href");
        if ((!url || url === "#") && (!href || href === "#")) {
          const msg = getMsg("daily_purchase_nav_unavailable");
          scheduleError(msg, "click");
          return;
        }
        window.location.href = url || href;
      });
    }
  } catch (error) {}
})();
