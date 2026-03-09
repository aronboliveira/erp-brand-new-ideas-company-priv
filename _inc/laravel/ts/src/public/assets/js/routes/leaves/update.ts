/**
 * @fileoverview TypeScript version of public/assets/js/routes/leaves/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  try {
    const f = document.getElementById("edit_leave");
    if (!f) return;
    if (
      f.hasAttribute("data-submit-listener") &&
      f.getAttribute("data-submit-listener") === "true"
    )
      return;
    f.setAttribute("data-submit-listener", "true");
    const toast = (msg: string): void => {
      try {
        const linkEl = document.querySelector('link[href*="bootstrap"]'),
          hasBootstrap = !!linkEl && window.bootstrap.Toast;
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          container.className = "position-fixed top-0 end-0 p-3";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
          const t = document.createElement("div");
          t.className = "toast";
          for (const [k, v] of Object.entries({
            role: "alert",
            "aria-live": "assertive",
            "aria-atomic": "true",
          }))
            t.setAttribute(k, v);
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent =
            msg ??
            "Requested route is unavailable. Please contact technical support or your domain administrator.";
          t.appendChild(body);
          container.appendChild(t);
          const inst = window.bootstrap.Toast.getOrCreateInstance(t);
          t.addEventListener("hidden.bs.toast", function (): void {
            try {
              t.remove();
            } catch (e) {
              console.error(`[update] Error:`, e);
            }
          });
          inst.show();
        } else {
          alert(
            msg ??
              "Requested route is unavailable. Please contact technical support or your domain administrator.",
          );
        }
      } catch (e) {
        console.error(`[update] Error:`, e);
      }
    };
    f.addEventListener(
      "submit",
      function (e: Event) {
        try {
          const action = f.getAttribute("action") ?? "#";
          if (action !== "#") return;
          e.preventDefault();
          const msg =
            f.getAttribute("data-guard-msg") ??
            "Update leave route is unavailable. Please contact technical support or your domain administrator.";
          toast(msg);
          f.setAttribute("data-failed-route", "true");
        } catch (err) {
          console.error(`[update] Error:`, err);
        }
      },
      { passive: false },
    );
  } catch (error) {
    console.error(`[update] Error:`, error);
  }
})();

export {};
