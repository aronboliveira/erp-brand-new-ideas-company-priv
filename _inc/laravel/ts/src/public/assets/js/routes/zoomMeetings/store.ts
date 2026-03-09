/**
 * @fileoverview TypeScript version of public/assets/js/routes/zoomMeetings/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

import type { SvLang } from "../../../../../declarations/routes/ajax-responses.interfaces";

((): void => {
  (function (): void {
    try {
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      const svLang = (window.svLang || {}) as SvLang;
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      svLang.zoomMeetings = svLang.zoomMeetings || {};
      // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
      svLang.zoomMeetings.store = svLang.zoomMeetings.store || {};
      svLang.zoomMeetings.store.routeGuardDefault =
        "Store zoom meeting route is unavailable. Please contact technical support or your domain administrator.";
      (window as unknown as { svLang: SvLang }).svLang = svLang;
    } catch (__err) {
      console.error(`[store] Error:`, __err);
    }
  })();
  try {
    const f = document.getElementById("store_zoom_meeting");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    if (
      (f as HTMLFormElement).hasAttribute("action") &&
      (!(f as HTMLFormElement).getAttribute("action") ||
        (f as HTMLFormElement).getAttribute("action") === "#") &&
      resolved !== "#"
    )
      (f as HTMLFormElement).setAttribute("action", resolved);

    f.addEventListener("submit", (e: Event) => {
      try {
        const action = f.getAttribute("action") ?? "#";
        if (action && action !== "#") return;
        e.preventDefault();
        const fallback =
            (window as unknown as { svLang?: SvLang }).svLang?.zoomMeetings
              ?.store?.routeGuardDefault || "",
          msg =
            f.getAttribute("data-guard-msg") ??
            (fallback ||
              "Requested route is unavailable. Please contact technical support or your domain administrator.");
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
            role: "alert",
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
        f.setAttribute("data-failed-route", "true");
      } catch (__err) {
        console.error(`[store] Error:`, __err);
      }
    });
  } catch (__err) {
    console.error(`[store] Error:`, __err);
  }
})();

export {};
