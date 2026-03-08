/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/tasks/boardList.js
 * @generated from original JavaScript - manual review recommended
 * @module boardList
 */

((): void => {
  try {
    const l = document.getElementById("taskboard-list-view-link");
    if (!l || l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener("click", (e: Event) => {
      try {
        const href = l.getAttribute("href") ?? "#";
        const url = l.getAttribute("data-url") ?? "#";
        if (href !== "#" || url !== "#") return;
        e.preventDefault();
        const msg =
          l.getAttribute("data-guard-msg") ?? "List taskboard route is unavailable. Please contact technical support or your domain administrator.";
        const hasBootstrap =
          document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
        l.setAttribute("data-failed-route", "true");
      } catch (err) {
    console.error(`[boardList] Error:`, err);
  }
    });
  } catch (error) {
    console.error(`[boardList] Error:`, error);
  }
})();

export {};
