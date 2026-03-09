/**
 * @fileoverview TypeScript version of public/assets/js/routes/events/generate.js
 * @generated from original JavaScript - manual review recommended
 * @module generate
 */

((): void => {
  const toast = (m: unknown): void => {
    try {
      if (window.bootstrap.Toast) {
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          c.className = "position-fixed bottom-0 end-0 p-3";
          document.body.appendChild(c);
        }
        const t = document.createElement("div");
        t.className = "toast align-items-center text-bg-danger border-0";
        for (const [k, v] of Object.entries({
          role: "alert",
          "aria-live": "assertive",
          "aria-atomic": "true",
        }))
          t.setAttribute(k, v);
        t.innerHTML =
          '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        const body = t.querySelector(".toast-body");
        if (body) body.textContent = String(m);
        c.appendChild(t);
        bootstrap.Toast.getOrCreateInstance(t, { delay: 4000 }).show();
        return;
      }
    } catch (_) {
      console.error(`[generate] Error:`, _);
    }
    alert(m);
  };
  const ai = document.getElementById("event-generate-ai-link");
  if (ai && ai.getAttribute("data-listener-active") !== "true") {
    ai.setAttribute("data-listener-active", "true");
    ai.addEventListener(
      "click",
      (e: Event) => {
        const u = ai.getAttribute("data-url");
        if (!u || u === "#") {
          e.preventDefault();
          toast(ai.getAttribute("data-guard-msg") ?? "#");
        }
      },
      { passive: false },
    );
  }
})();

export {};
