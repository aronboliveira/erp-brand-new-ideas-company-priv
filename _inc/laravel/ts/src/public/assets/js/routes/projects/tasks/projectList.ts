/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/projectList.js
 * @generated from original JavaScript - manual review recommended
 * @module projectList
 */

/* global bootstrap */
((): void => {
  try {
    const anchors = document.querySelectorAll('a[id^="task-index-link-"]');
    if (anchors.length === 0) return;
    for (let i = 0; i < anchors.length; i++) {
      try {
        const el = anchors[i];
        const flag = "data-listener-active";
        if (el.hasAttribute(flag) && el.getAttribute(flag) === "true") continue;
        el.setAttribute(flag, "true");
        el.addEventListener(
          "click",
          function (e: Event) {
            try {
              const href = el.getAttribute("href") ?? "#";
              const url = el.getAttribute("data-url") ?? "#";
              if (href !== "#" || url !== "#") return;
              e.preventDefault();
              const msg =
                el.getAttribute("data-guard-msg") ?? "View project tasks route is unavailable. Please contact technical support or your domain administrator.";
              const hasBootstrap =
                document.querySelector('link[href*="bootstrap"]') &&
                window.bootstrap?.Toast;
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
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "assertive");
                toast.setAttribute("aria-atomic", "true");
                const body = document.createElement("div");
                body.className = "toast-body";
                body.textContent = msg;
                toast.appendChild(body);
                container.appendChild(toast);
                const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                toast.addEventListener("hidden.bs.toast", function (): void {
                  try {
                    toast.remove();
                  } catch (_) {}
                });
                inst.show();
              } else {
                alert(msg);
              }
              el.setAttribute("data-failed-route", "true");
            } catch (_) {}
          },
          { passive: false }
        );
      } catch (_) {}
    }
  } catch (_) {}
})();

export {};
