/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/projectReport.js
 * @generated from original JavaScript - manual review recommended
 * @module projectReport
 */

/* global bootstrap */
((): void => {
  console.info("Loaded: projectReport.js");
  const listenerAttr = "data-project-report-index-listener-active";
  const el = document.getElementById("project-report-index-link");
  if (!el || el.getAttribute(listenerAttr) === "true") return;
  el.setAttribute(listenerAttr, "true");
  el.addEventListener("click", event => {
    console.info("Clicked: project-report-index-link");
    try {
      const url = el.getAttribute("data-url");
      const href = (el as HTMLAnchorElement).href
        .replace(window.location.origin, "")
        .replace(window.location.pathname, "");
      if ((!url || url === "#") && (!href || href === "#")) {
        event.preventDefault();
        const msg = el.getAttribute("data-guard-msg") ?? "# ERROR";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (bootstrapLink && window.bootstrap) {
          const toastEl = document.createElement("div");
          toastEl.className = "toast";
          toastEl.setAttribute("role", "alert");
          toastEl.setAttribute("aria-live", "assertive");
          toastEl.setAttribute("aria-atomic", "true");
          const body = document.createElement("div");
          body.className = "toast-body";
          body.textContent = msg;
          toastEl.appendChild(body);
          container.appendChild(toastEl);
          bootstrap.Toast.getOrCreateInstance(toastEl).show();
        } else {
          alert(msg);
        }
        el.setAttribute("data-failed-route", "true");
      }
    } catch (error) {}
  });
  const observer = new MutationObserver((): void => {
    console.info("MutationObserver triggered for project-report-index-link");
    if (!document.getElementById("project-report-index-link"))
      observer.disconnect();
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();

export {};
