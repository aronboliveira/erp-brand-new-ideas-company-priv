/**
 * @fileoverview TypeScript version of public/assets/js/routes/bills/index.js
 * @generated from original JavaScript - manual review recommended
 * @module index
 */

((): void => {
  const link = document.getElementById("bill-index-link");
  if (!link || link.getAttribute("data-listener-active") === "true") return;
  link.setAttribute("data-listener-active", "true");
  if (!link.getAttribute("data-listener-bound-click")) {
    link.setAttribute("data-listener-bound-click", "1");
    link.addEventListener("click", event => {
      try {
        const href = link.getAttribute("href"),
          url = link.getAttribute("data-url");
        if ((href && href !== "#") ?? (url && url !== "#")) return;
        event.preventDefault();
        const msg = link.getAttribute("data-guard-msg") ?? "# ERROR",
          bootstrapLink = document.querySelector('link[href*="bootstrap"]');
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
            role: "alert",
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
        link.setAttribute("data-failed-route", "true");
      } catch (e) {
        console.error(`[index] Error:`, e);
      }
    });
  }
})();

export {};
