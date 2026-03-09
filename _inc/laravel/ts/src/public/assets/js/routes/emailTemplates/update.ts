/**
 * @fileoverview TypeScript version of public/assets/js/routes/emailTemplates/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  try {
    const fm = document.querySelector(
      'form[id^="email-template-update-form-"]',
    );
    if (!fm) return;
    if (fm.getAttribute("data-submit-guarded") === "true") return;
    fm.setAttribute("data-submit-guarded", "true");

    if (!fm.getAttribute("data-listener-bound-submit")) {
      fm.setAttribute("data-listener-bound-submit", "1");
      fm.addEventListener("submit", (e: Event) => {
        try {
          const action = (fm.getAttribute("action") ?? "#").trim(),
            url = (fm.getAttribute("data-url") ?? "#").trim();
          if (url !== "#" && action !== "#") return;
          e.preventDefault();

          const msg =
              fm.getAttribute("data-guard-msg") ??
              "Update email template route is unavailable. Please contact technical support or your domain administrator.",
            hasBootstrap = !!(
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
            const toast = document.createElement("div");
            toast.className = "toast";
            for (const [k, v] of Object.entries({
              role: "alert",
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

          fm.setAttribute("data-failed-route", "true");
        } catch (err) {
          console.error(`[update] Error:`, err);
        }
      });
    }
  } catch (err) {
    console.error(`[update] Error:`, err);
  }
})();

export {};
