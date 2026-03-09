(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  try {
    const links = document.querySelectorAll(".joining-letter-language-link");
    if (!links || links.length === 0) return;
    links.forEach(l => {
      if (l.getAttribute("data-listener-active") === "true") return;
      l.setAttribute("data-listener-active", "true");
      l.addEventListener("click", e => {
        try {
          const url = l.getAttribute("data-url") || "#";
          if (url !== "#") return;
          e.preventDefault();
          const msg =
            l.getAttribute("data-guard-msg") ||
            getMsg("joining_letter_language_unavailable");
          scheduleError(msg, "click");
          l.setAttribute("data-failed-route", "true");
        } catch (err) {}
      });
    });
  } catch (err) {}
})();
