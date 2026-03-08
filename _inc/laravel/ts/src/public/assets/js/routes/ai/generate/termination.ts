/**
 * @fileoverview TypeScript version of public/assets/js/routes/ai/generate/termination.js
 * @generated from original JavaScript - manual review recommended
 * @module termination
 */

((): void => {
  try {
    const l = document.getElementById("termination-generate-link");
    if (!l || l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener("click", (e: Event) => {
      try {
        const href = l.getAttribute("href") ?? "#";
        const url = l.getAttribute("data-url") ?? "#";
        if (href !== "#" || url !== "#") return;
        e.preventDefault();
        const msgAttr = l.hasAttribute("data-guard-msg")
          ? l.getAttribute("data-guard-msg")
          : "";
        const msg =
          (msgAttr ?? "").trim().length > 0
            ? msgAttr
            : "Generate termination route is unavailable. Please contact technical support or your domain administrator.";
        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
        const hasBootstrap = !!(
          bootstrapLink && typeof window.bootstrap.Toast !== "undefined"
        );
        let container = document.getElementById("toast-container");
        if (!container) {
          container = document.createElement("div");
          container.id = "toast-container";
          container.className =
            "toast-container position-fixed top-0 end-0 p-3";
          container.style.zIndex = "1080";
          document.body.appendChild(container);
        }
        if (hasBootstrap) {
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
          window.bootstrap.Toast.getOrCreateInstance(toast).show();
        } else {
          alert(msg);
        }
        l.setAttribute("data-failed-route", "true");
      } catch (err) {
    console.error(`[termination] Error:`, err);
  }
    });
  } catch (error) {
    console.error(`[termination] Error:`, error);
  }
})();

export {};
