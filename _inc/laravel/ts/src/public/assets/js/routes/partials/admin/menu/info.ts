/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/info.js
 * @generated from original JavaScript - manual review recommended
 * @module info
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const bindGuard = id => {
    const listenerAttr = `data-${id}-listener-active`;
    const el = document.getElementById(id);
    if (!el || el.getAttribute(listenerAttr) === "true") return;
    el.setAttribute(listenerAttr, "true");
    el.addEventListener("click", event => {
      try {
        const url = el.getAttribute("data-url");
        const href = el.href
          .replace(window.location.origin, "")
          .replace(window.location.pathname, "");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if ((!url || url === "#") && (!href || href === "#")) {
          event.preventDefault();
          const msg = el.getAttribute("data-guard-msg") ?? "# ERROR";
          const bootstrapLink = document.querySelector(
            'link[href*="bootstrap"]'
          );
          const containerId = "toast-container";
          let container = document.getElementById(containerId);
          if (!container) {
            container = document.createElement("div");
            container.id = containerId;
            document.body.appendChild(container);
          }
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
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
      if (!document.getElementById(id)) {
        observer.disconnect();
      }
    });
    observer.observe(document.body, { childList: true, subtree: true });
  };
  [
    "award-index-link",
    "transfer-index-link",
    "resignation-index-link",
    "trip-index-link",
    "promotion-index-link",
    "complaint-index-link",
    "warning-index-link",
    "termination-index-link",
    "announcement-index-link",
    "holidays-index-link",
  ].forEach(bindGuard);
})();

export {};
