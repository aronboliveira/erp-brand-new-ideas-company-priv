/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/destroy.js
 * @generated from original JavaScript - manual review recommended
 * @module destroy
 */

((): void => {
  try {
    const forms = document.querySelectorAll('form[id^="delete-form-"]');
    if (!forms || forms.length === 0) return;

    forms.forEach(f => {
      try {
        if (f.getAttribute("data-listener-active") === "true") return;
        f.setAttribute("data-listener-active", "true");

        const resolved = f.getAttribute("data-resolved-action") ?? "#";
        if (
          f.hasAttribute("action") &&
          (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
          resolved !== "#"
        )
          f.setAttribute("action", resolved);

        f.addEventListener("submit", (e: Event) => {
          try {
            const action = f.getAttribute("action") ?? "#";
            if (action && action !== "#") return;
            e.preventDefault();

            const msg =
              f.getAttribute("data-guard-msg") ??
              "Delete tracker route is unavailable. Please contact technical support or your domain administrator.";
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
              document.body.appendChild(container);
            }
            const bsLink = document.querySelector('link[href*="bootstrap"]');
            if (bsLink && window.bootstrap.Toast) {
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
            console.error(`[destroy] Error:`, __err);
          }
        });
      } catch (__err) {
        console.error(`[destroy] Error:`, __err);
      }
    });
  } catch (__err) {
    console.error(`[destroy] Error:`, __err);
  }
})();

export {};
