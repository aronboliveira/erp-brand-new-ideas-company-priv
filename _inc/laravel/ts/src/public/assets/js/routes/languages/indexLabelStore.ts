/**
 * @fileoverview TypeScript version of public/assets/js/routes/languages/indexLabelStore.js
 * @generated from original JavaScript - manual review recommended
 * @module indexLabelStore
 */

((): void => {
  const listenerAttr = "data-lang-store-listener-active";
  const form = document.getElementById("langStoreForm");
  if (!form || form.getAttribute(listenerAttr) === "true") return;
  form.setAttribute(listenerAttr, "true");
  form.addEventListener("submit", event => {
    try {
      const action = form.getAttribute("action");
      const dataAction = form.getAttribute("data-action");
      if ((!action || action === "#") && (!dataAction || dataAction === "#")) {
        event.preventDefault();
        const msg = form.getAttribute("data-guard-msg") ?? "# ERROR";
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
      }
    } catch (error) {
    console.error(`[indexLabelStore] Error:`, error);
  }
  });
  const observer = new MutationObserver((): void => {
    if (!document.getElementById("langStoreForm")) observer.disconnect();
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();

export {};
