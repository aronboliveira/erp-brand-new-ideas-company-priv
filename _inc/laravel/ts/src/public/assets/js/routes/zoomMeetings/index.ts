/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

// assets/js/routes/zoomMeetings/index.js
((): void => {
  type NestedLang = Record<string, Record<string, Record<string, string>>>;
  (function (): void {
    try {
      const svLang = ((window.svLang as unknown) ?? {}) as NestedLang;
      window.svLang = svLang as unknown as Record<string, string>;
      svLang.zoomMeetings =
        svLang.zoomMeetings || ({} as Record<string, Record<string, string>>);
      svLang.zoomMeetings.index =
        svLang.zoomMeetings.index || ({} as Record<string, string>);
      svLang.zoomMeetings.index.calendarGuardDefault =
        "Calendar route is unavailable. Please contact technical support or your domain administrator.";
      svLang.zoomMeetings.index.createGuardDefault =
        "Create zoom meeting route is unavailable. Please contact technical support or your domain administrator.";
    } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
  })();
  function attachGuard(anchor: HTMLElement | null, fallbackMsg: string): void{
    if (!anchor || anchor.getAttribute("data-listener-active") === "true")
      return;
    anchor.setAttribute("data-listener-active", "true");
    const url = anchor.getAttribute("data-url") ?? "#";
    if (
      anchor.hasAttribute("href") &&
      (!anchor.getAttribute("href") || anchor.getAttribute("href") === "#") &&
      url !== "#"
    ) {
      anchor.setAttribute("href", url);
    }
    anchor.addEventListener("click", (e: Event) => {
      try {
        const href = anchor.getAttribute("href") ?? "#";
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
        const hasBs = window.bootstrap.Toast;
        if (hasBs) {
          const toast = document.createElement("div");
          toast.className = "toast";
          for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toast.setAttribute(k, v);
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
      } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
    });
  }

  try {
    const cal = document.getElementById("zoom-calendar-link");
    const crt = document.getElementById("zoom-create-link");
    const svLang = window.svLang as unknown as NestedLang | undefined;
    const dCal = svLang?.zoomMeetings?.index?.calendarGuardDefault || "";
    const dCrt = svLang?.zoomMeetings?.index?.createGuardDefault || "";
    attachGuard(
      cal,
      dCal ??
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    );
    attachGuard(
      crt,
      dCrt ??
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    );
  } catch (__err) {
    console.error(`[index] Error:`, __err);
  }
})();
