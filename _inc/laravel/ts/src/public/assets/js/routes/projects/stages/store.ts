/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/stages/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

((): void => {
  try {
    const f = document.getElementById("create-project-stage-form");
    if (!f) return;
    const flag = "data-submit-listener";
    if (f.hasAttribute(flag) && f.getAttribute(flag) === "true") return;
    f.setAttribute(flag, "true");
    f.addEventListener(
      "submit",
      function (e: Event) {
        try {
          const action = f.getAttribute("action") ?? "#";
          if (action !== "#") return;
          e.preventDefault();
          const msg =
              f.getAttribute("data-guard-msg") ??
              "Create project stage route is unavailable. Please contact technical support or your domain administrator.",
            hasBootstrap =
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap.Toast;
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
            const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
            toast.addEventListener("hidden.bs.toast", function (): void {
              try {
                toast.remove();
              } catch (_) {
                console.error(`[store] Error:`, _);
              }
            });
            inst.show();
          } else {
            alert(msg);
          }
          f.setAttribute("data-failed-route", "true");
        } catch (_) {
          console.error(`[store] Error:`, _);
        }
      },
      { passive: false },
    );
  } catch (_) {
    console.error(`[store] Error:`, _);
  }
})();

export {};
