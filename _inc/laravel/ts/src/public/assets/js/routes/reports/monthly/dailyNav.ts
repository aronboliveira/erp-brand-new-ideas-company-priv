/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/monthly/dailyNav.js
 * @generated from original JavaScript - manual review recommended
 * @module dailyNav
 */

((): void => {
  try {
    const homeTab = document.getElementById("pills-home-tab"),
      listenerAttr = "data-daily-purchase-nav-listener-added";
    if (homeTab && homeTab.getAttribute(listenerAttr) !== "true") {
      homeTab.setAttribute(listenerAttr, "true");
      if (!homeTab.getAttribute("data-listener-bound-click")) {
        homeTab.setAttribute("data-listener-bound-click", "1");
        homeTab.addEventListener("click", (e: Event) => {
          e.preventDefault();
          const url = homeTab.getAttribute("data-url"),
            href = homeTab.getAttribute("href");
          if ((!url || url === "#") && (!href || href === "#")) return;
          const msg = "{{ $dailyPurchaseNavMsg }}",
            toastEl = document.querySelector<HTMLElement>(".toast");
          if (
            toastEl &&
            window.bootstrap &&
            typeof bootstrap.Toast === "function"
          ) {
            const toast = new bootstrap.Toast(toastEl),
              body = toastEl.querySelector(".toast-body");
            if (body) body.textContent = msg;
            toast.show();
          } else {
            alert(msg);
          }
          window.location.href = url ?? "#";
        });
      }
    }
  } catch (error) {
    console.error(`[dailyNav] Error:`, error);
  }
})();

export {};
