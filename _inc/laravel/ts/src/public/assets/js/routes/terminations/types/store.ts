/**
 * @fileoverview TypeScript version of public/assets/js/routes/terminations/types/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

((): void => {
  try {
    const f = document.getElementById("termination-type-create-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    f.addEventListener("submit", (e: Event) => {
      try {
        const action = (f.getAttribute("action") ?? "").trim(),
          url = (f.getAttribute("data-url") ?? "").trim() || action;
        if (action !== "#" || url !== "#") return;

        e.preventDefault();

        const msg = f.getAttribute("data-guard-msg") ?? "Create termination type route is unavailable. Please contact technical support or your domain administrator.";
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className = "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }

        const hasBootstrapCss = !!document.querySelector('link[href*="bootstrap"]'),
          hasBootstrapJs = typeof window.bootstrap !== "undefined";
        if (hasBootstrapCss && hasBootstrapJs) {
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
          } catch (_err) {
            alert(msg);
          }
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

export {};
