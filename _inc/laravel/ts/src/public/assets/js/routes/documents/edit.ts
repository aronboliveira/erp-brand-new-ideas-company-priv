/**
 * @fileoverview TypeScript version of public/assets/js/routes/documents/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

/* global bootstrap */
((): void => {
  try {
    const links = Array.from(
      document.querySelectorAll(
        'a[id^="document-edit-btn-"][data-url][data-guard-msg]'
      )
    );
    if (links.length === 0) {
      return;
    }
    links.forEach(l => {
      try {
        if (l.getAttribute("data-click-guarded") === "true") {
          return;
        }
        l.setAttribute("data-click-guarded", "true");
        l.addEventListener("click", (e: Event) => {
          try {
            const url = (l.getAttribute("data-url") ?? "#").trim();
            if (url !== "#") {
              return;
            }
            e.preventDefault();
            const msg =
              l.getAttribute("data-guard-msg") ??
              "Edit document route is unavailable. Please contact technical support or your domain administrator.";
            const hasBootstrap = !!(
              document.querySelector('link[href*="bootstrap"]') &&
              window.bootstrap
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
              toast.setAttribute("role", "alert");
              toast.setAttribute("aria-live", "assertive");
              toast.setAttribute("aria-atomic", "true");
              const body = document.createElement("div");
              body.className = "toast-body";
              body.textContent = msg;
              toast.appendChild(body);
              container.appendChild(toast);
              bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
              alert(msg);
            }
            l.setAttribute("data-failed-route", "true");
          } catch (err) {}
        });
      } catch (err) {}
    });
  } catch (err) {}
})();

export {};
