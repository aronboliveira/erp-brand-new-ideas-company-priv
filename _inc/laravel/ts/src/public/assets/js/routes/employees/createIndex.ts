/**
 * @fileoverview TypeScript version of public/assets/js/routes/employees/createIndex.js
 * @generated from original JavaScript - manual review recommended
 * @module createIndex
 */

((): void => {
  try {
    const a = document.getElementById("bc-employee-index-link");
    if (!a) {
      return;
    }
    if (a.getAttribute("data-listener-active") === "true") {
      return;
    }
    a.setAttribute("data-listener-active", "true");
    a.addEventListener("click", (e: Event) => {
      try {
        const href = (a.getAttribute("href") ?? "#").trim();
        const url = (a.getAttribute("data-url") ?? "#").trim();
        if (url !== "#" && href !== "#") {
          return;
        }
        e.preventDefault();
        const msg =
          a.getAttribute("data-guard-msg") ??
          "Employee index route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap = !!(
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap
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
        a.setAttribute("data-failed-route", "true");
      } catch (err) {
    console.error(`[createIndex] Error:`, err);
  }
    });
  } catch (err) {
    console.error(`[createIndex] Error:`, err);
  }
})();

export {};
