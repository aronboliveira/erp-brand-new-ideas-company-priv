/**
 * @fileoverview TypeScript version of public/assets/js/routes/coupons/create.js
 * @generated from original JavaScript - manual review recommended
 * @module create
 */

((): void => {
  const btn = document.getElementById("coupon-create-btn");
  if (!btn || btn.getAttribute("data-listener-active") === "true") return;
  btn.setAttribute("data-listener-active", "true");
  btn.addEventListener("click", event => {
    try {
      const url = btn.getAttribute("data-url");
      if (!url || url === "#") {
        event.preventDefault();
        const msg = btn.getAttribute("data-guard-msg") ?? "# ERROR";
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
        btn.setAttribute("data-failed-route", "true");
        return;
      }
      // otherwise proceed with AJAX popup
    } catch (e) {
    console.error(`[create] Error:`, e);
  }
  });
})();

export {};
