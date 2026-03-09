/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/stages/edit.js
 * @generated from original JavaScript - manual review recommended
 * @module edit
 */

((): void => {
  try {
    const fm = document.getElementById("jobStage-edit-form");
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");
    if (!fm.getAttribute("data-listener-bound-submit")) {
      fm.setAttribute("data-listener-bound-submit", "1");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const url = (
            fm.getAttribute("data-url") ??
            fm.getAttribute("action") ??
            "#"
          ).trim();
          if (!url || url === "#") {
            e.preventDefault();
            const msg =
                fm.getAttribute("data-guard-msg") ?? "Route unavailable.",
              hasBs =
                !!document.querySelector('link[href*="bootstrap"]') &&
                !!window.bootstrap.Toast;
            if (hasBs) {
              let c = document.getElementById("toast-container");
              if (!c) {
                c = document.createElement("div");
                c.id = "toast-container";
                document.body.appendChild(c);
              }
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
              c.appendChild(t);
              window.bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
              alert(msg);
            }
          }
        } catch (__err) {
          console.error(`[edit] Error:`, __err);
        }
      });
    }
  } catch (__err) {
    console.error(`[edit] Error:`, __err);
  }
})();

export {};
