/**
 * @fileoverview TypeScript version of public/assets/js/routes/terminations/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

// public/assets/js/routes/terminations/store.js
((): void => {
  try {
    const f = document.getElementById("store_termination");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");
    f.addEventListener("submit", (e: Event) => {
      try {
        const action = f.getAttribute("action") ?? "#";
        const url = f.getAttribute("data-url") ?? "#";
        if (action !== "#" || url !== "#") return;
        e.preventDefault();
        const msgAttr = f.hasAttribute("data-guard-msg")
          ? f.getAttribute("data-guard-msg")
          : "";
        const msg =
          (msgAttr ?? "").trim().length > 0
            ? msgAttr
            : "Store termination route is unavailable. Please contact technical support or your domain administrator.";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap = !!(
          bootstrapLink && typeof window.bootstrap.Toast !== "undefined"
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
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
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        f.setAttribute("data-failed-route", "true");
      } catch (err) {
    console.error(`[store] Error:`, err);
  }
    });
  } catch (error) {
    console.error(`[store] Error:`, error);
  }
})();
