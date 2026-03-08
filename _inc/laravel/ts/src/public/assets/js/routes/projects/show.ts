/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */

((): void => {
  try {
    const links = document.querySelectorAll("a.project-show-link");
    if (links.length === 0) {
      return;
    }
    links.forEach((el: Element): void => {
      try {
        if (el.getAttribute("data-listener-active") === "true") {
          return;
        }
        el.setAttribute("data-listener-active", "true");
        el.addEventListener("click", (e: Event) => {
          try {
            const href = el.getAttribute("href") ?? "#";
            const url = el.getAttribute("data-url") ?? "#";
            if (url !== "#" && href !== "#") {
              return;
            }
            e.preventDefault();
            const msg =
              el.getAttribute("data-guard-msg") ??
              "Show project route is unavailable. Please contact technical support or your domain administrator.";
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
              bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
              alert(msg);
            }
            el.setAttribute("data-failed-route", "true");
          } catch (err) {
    console.error(`[show] Error:`, err);
  }
        });
      } catch (err) {
    console.error(`[show] Error:`, err);
  }
    });
  } catch (err) {
    console.error(`[show] Error:`, err);
  }
})();

export {};
