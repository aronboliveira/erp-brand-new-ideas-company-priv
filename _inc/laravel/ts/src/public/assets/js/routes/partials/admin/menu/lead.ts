/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/lead.js
 * @generated from original JavaScript - manual review recommended
 * @module lead
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

/* global bootstrap */
((): void => {
  const listenerAttr = "data-ld-index-listener-active";
  const el = document.getElementById("ld-index-link");
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
    if (!document.getElementById("ld-index-link")) observer.disconnect();
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();

export {};
