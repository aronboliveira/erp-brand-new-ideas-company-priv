/**
 * @fileoverview TypeScript version of public/assets/js/routes/documents/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  try {
    const forms = Array.from(
      document.querySelectorAll(
        'form[id^="document-update-form-"][data-url][data-guard-msg]',
      ),
    );
    if (forms.length === 0) return;
    forms.forEach(fm => {
      try {
        if (fm.getAttribute("data-submit-guarded") === "true") return;
        fm.setAttribute("data-submit-guarded", "true");
        fm.addEventListener("submit", (e: Event) => {
          try {
            const action = (fm.getAttribute("action") ?? "#").trim(),
              url = (fm.getAttribute("data-url") ?? "#").trim();
            if (url !== "#" && action !== "#") return;
            e.preventDefault();
            const msg =
              fm.getAttribute("data-guard-msg") ??
              "Update document route is unavailable. Please contact technical support or your domain administrator.";
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
            fm.setAttribute("data-failed-route", "true");
          } catch (err) {
            console.error(`[update] Error:`, err);
          }
        });
      } catch (err) {
        console.error(`[update] Error:`, err);
      }
    });
  } catch (err) {
    console.error(`[update] Error:`, err);
  }
})();

export {};
