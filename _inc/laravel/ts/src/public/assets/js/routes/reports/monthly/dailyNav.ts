/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/monthly/dailyNav.js
 * @generated from original JavaScript - manual review recommended
 * @module dailyNav
 */
/* eslint-disable @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  try {
    const homeTab = document.getElementById("pills-home-tab");
    const listenerAttr = "data-daily-purchase-nav-listener-added";
    if (homeTab?.getAttribute(listenerAttr) !== "true") {
      homeTab.setAttribute(listenerAttr, "true");
      homeTab.addEventListener("click", e => {
        e.preventDefault();
        const url = homeTab.getAttribute("data-url");
        const href = homeTab.getAttribute("href");
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if ((!url || url === "#") && (!href || href === "#")) return;
        const msg = "{{ $dailyPurchaseNavMsg }}";
        const toastEl = document.querySelector<HTMLElement>(".toast");
        if (
          toastEl &&
          // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
          window.bootstrap &&
          typeof bootstrap.Toast === "function"
        ) {
          const toast = new bootstrap.Toast(toastEl);
          const body = toastEl.querySelector(".toast-body");
          if (body) {
            body.textContent = msg;
          }
          toast.show();
        } else {
          alert(msg);
        }
        window.location.href = url;
      });
    }
  } catch (error) {}
})();

export {};
