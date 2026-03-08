/**
 * @fileoverview TypeScript version of public/assets/js/routes/partials/admin/menu/chart.js
 * @generated from original JavaScript - manual review recommended
 * @module chart
 */

((): void => {
  const bindGuard = (id: string): void=> {
    const listenerAttr = `data-${id}-listener-active`;
    const el = document.getElementById(id);
    if (!el || el.getAttribute(listenerAttr) === "true") return;
    el.setAttribute(listenerAttr, "true");
    el.addEventListener("click", event => {
      try {
        const url = el.getAttribute("data-url");
        const href = (el as HTMLAnchorElement).href
          .replace(window.location.origin, "")
          .replace(window.location.pathname, "");
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
          if (bootstrapLink && window.bootstrap) {
            const toastEl = document.createElement("div");
            toastEl.className = "toast";
            for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
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
      } catch (error) {
    console.error(`[chart] Error:`, error);
  }
    });
    const observer = new MutationObserver((): void => {
      if (!document.getElementById(id)) observer.disconnect();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  };
  [
    "chart-of-accounts-link",
    "journal-account-link",
    "ledger-summary-link",
    "balance-sheet-link",
    "profit-loss-link",
    "trial-balance-link",
  ].forEach(bindGuard);
})();

export {};
