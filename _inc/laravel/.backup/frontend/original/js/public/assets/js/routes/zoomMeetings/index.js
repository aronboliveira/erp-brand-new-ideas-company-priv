// assets/js/routes/zoomMeetings/index.js
(() => {
  (function () {
    try {
      window.svLang = window.svLang || {};
      window.svLang.zoomMeetings = window.svLang.zoomMeetings || {};
      window.svLang.zoomMeetings.index = window.svLang.zoomMeetings.index || {};
      window.svLang.zoomMeetings.index.calendarGuardDefault =
        "Calendar route is unavailable. Please contact technical support or your domain administrator.";
      window.svLang.zoomMeetings.index.createGuardDefault =
        "Create zoom meeting route is unavailable. Please contact technical support or your domain administrator.";
    } catch {}
  })();
  function attachGuard(anchor, fallbackMsg) {
    if (!anchor || anchor.getAttribute("data-listener-active") === "true")
      return;
    anchor.setAttribute("data-listener-active", "true");
    const url = anchor.getAttribute("data-url") || "#";
    if (
      anchor.hasAttribute("href") &&
      (!anchor.getAttribute("href") || anchor.getAttribute("href") === "#") &&
      url !== "#"
    ) {
      anchor.setAttribute("href", url);
    }
    anchor.addEventListener("click", e => {
      try {
        const href = anchor.getAttribute("href") || "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg = anchor.getAttribute("data-guard-msg") || fallbackMsg;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        const hasBs =
          typeof window.bootstrap !== "undefined" && window.bootstrap?.Toast;
        if (hasBs) {
          const toast = document.createElement("div");
          toast.className = "toast";
          toast.setAttribute("role", "alert");
          toast.setAttribute("aria-live", "assertive");
          toast.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toast.appendChild(body);
          container.appendChild(toast);
          try {
            window.bootstrap.Toast.getOrCreateInstance(toast).show();
          } catch {
            alert(msg);
          }
        } else {
          alert(msg);
        }
        anchor.setAttribute("data-failed-route", "true");
      } catch {}
    });
  }

  try {
    const cal = document.getElementById("zoom-calendar-link");
    const crt = document.getElementById("zoom-create-link");
    const dCal =
      (window.svLang &&
        window.svLang.zoomMeetings &&
        window.svLang.zoomMeetings.index &&
        window.svLang.zoomMeetings.index.calendarGuardDefault) ||
      "";
    const dCrt =
      (window.svLang &&
        window.svLang.zoomMeetings &&
        window.svLang.zoomMeetings.index &&
        window.svLang.zoomMeetings.index.createGuardDefault) ||
      "";
    attachGuard(
      cal,
      dCal ||
        "Requested route is unavailable. Please contact technical support or your domain administrator."
    );
    attachGuard(
      crt,
      dCrt ||
        "Requested route is unavailable. Please contact technical support or your domain administrator."
    );
  } catch {}
})();
