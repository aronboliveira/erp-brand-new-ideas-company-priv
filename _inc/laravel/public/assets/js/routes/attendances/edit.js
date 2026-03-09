(() => {
  const { scheduleError } = window.ERPGuard ?? {};
  const { getMsg } = window.ERPUtils ?? {};

  if (typeof scheduleError !== "function" || typeof getMsg !== "function") {
    
    return;
  }

  try {
    document.querySelectorAll('[id^="edit-attendance-link-"]').forEach(el => {
      try {
        const alias = "data-listening-editattendanceclick";
        if (!el.hasAttribute(alias)) {
          el.setAttribute(alias, "true");
          el.addEventListener("click", event => {
            try {
              const url = el.getAttribute("data-url");
              const href = el.getAttribute("href") ?? "";
              if ((url && url !== "#") || (href && href !== "#")) return;
              event.preventDefault();
              const msg =
                el.getAttribute("data-guard-msg") ||
                getMsg("edit_attendance_unavailable");
              scheduleError(msg, "click");
            } catch {}
          });
        }
      } catch {}
    });
  } catch {}
})();
