/**
 * @fileoverview TypeScript version of public/assets/js/routes/journalEntries/cancel.js
 * @generated from original JavaScript - manual review recommended
 * @module cancel
 */

((): void => {
  try {
    const btns = Array.from(
      document.querySelectorAll(
        "button.cancel-link[data-href][data-guard-msg],input.cancel-link[data-href][data-guard-msg]",
      ),
    );
    if (btns.length === 0) return;
    btns.forEach(btn => {
      if (btn.getAttribute("data-click-guarded") === "true") return;
      btn.setAttribute("data-click-guarded", "true");
      btn.addEventListener("click", (e: Event) => {
        try {
          const href = (btn.getAttribute("data-href") ?? "#").trim();
          if (href && href !== "#") {
            window.location.assign(href);
            return;
          }
          e.preventDefault();
          const msg =
            btn.getAttribute("data-guard-msg") ??
            "Journal entries index route is unavailable. Please contact technical support or your domain administrator.";
          const hasBootstrap = !!(
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap
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
            const t = document.createElement("div");
            t.className = "toast";
            for (const [k, v] of Object.entries({
              role: "alert",
              "aria-live": "assertive",
              "aria-atomic": "true",
            }))
              t.setAttribute(k, v);
            const b = document.createElement("div");
            b.className = "toast-body";
            b.textContent = msg;
            t.appendChild(b);
            container.appendChild(t);
            bootstrap.Toast.getOrCreateInstance(t).show();
          } else {
            alert(msg);
          }
          btn.setAttribute("data-failed-route", "true");
        } catch (__err) {
          console.error(`[cancel] Error:`, __err);
        }
      });
    });
  } catch (__err) {
    console.error(`[cancel] Error:`, __err);
  }
})();

export {};
