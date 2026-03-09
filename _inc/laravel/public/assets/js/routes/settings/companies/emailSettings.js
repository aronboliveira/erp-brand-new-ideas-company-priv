(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    void 0;
    return;
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".email-template-toggle").forEach(function (el) {
      if (el.dataset.guardBound === "1") return;
      el.dataset.guardBound = "1";
      el.addEventListener("change", function (e) {
        const url = el.getAttribute("data-url") || "#";
        if (url !== "#") return;
        e.preventDefault();
        el.checked = !el.checked;
        const msg =
          el.getAttribute("data-guard-msg") ||
          getMsg("email_template_toggle_unavailable");
        scheduleError(msg, "change");
      });
    });
  });
})();
