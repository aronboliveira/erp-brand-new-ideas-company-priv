(() => {
  const { scheduleError, utils } = window.ERPGuard || {};
  const getMsg = utils?.getMsg;
  if (!scheduleError || !getMsg) return;

  const selector = ".create-meeting-link";
  const dataBound = "data-listening-createmeetingclick";

  document.querySelectorAll(selector).forEach(el => {
    if (el.getAttribute(dataBound) === "true") return;
    el.setAttribute(dataBound, "true");
    el.addEventListener("click", event => {
      const url = el.getAttribute("data-url");
      const href = (el.href || "")
        .replace(window.location.origin, "")
        .replace(window.location.pathname, "");
      if ((!url || url === "#") && (!href || href === "#")) {
        event.preventDefault();
        scheduleError(getMsg("create_meeting_route_unavailable"));
      }
    });
  });
})();
