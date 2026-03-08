/**
 * @fileoverview TypeScript version of public/assets/js/routes/deals/discussionStore.js
 * @generated from original JavaScript - manual review recommended
 * @module discussionStore
 */

((): void => {
  const form = document.getElementById("discussion-store-form");
  if (!form || form.getAttribute("data-listener-active") === "true") return;
  form.setAttribute("data-listener-active", "true");
  form.addEventListener("submit", (e: Event) => {
    try {
      const action =
        form.getAttribute("action") ?? form.getAttribute("data-url") ?? "#";
      if (action !== "#") return;
      e.preventDefault();
      const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
      const bs =
        document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
      }
      if (bs) {
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
      form.setAttribute("data-failed-route", "true");
    } catch (__err) {
    console.error(`[discussionStore] Error:`, __err);
  }
  });
})();

export {};
