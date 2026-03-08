/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/reports/show.js
 * @generated from original JavaScript - manual review recommended
 * @module show
 */

((): void => {
  const selector = ".show-project-report-link";
  const alias = "data-listening-showprojectreportclick";
  document.querySelectorAll(selector).forEach((el: Element): void => {
    try {
      if (!el.hasAttribute(alias)) {
        el.setAttribute(alias, "true");
        el.addEventListener("click", event => {
          try {
            const url = el.getAttribute("data-url");
            if (url !== "#" || (el as HTMLAnchorElement).href !== "#") return;
            event.preventDefault();
            const msg =
              el.getAttribute("data-guard-msg") ??
              "View project report route is unavailable. Please contact technical support or your domain administrator.";
            const hasBS = Array.from(document.scripts).some(
              s =>
                s.src.includes("bootstrap.min.js") &&
                window.bootstrap &&
                typeof window.bootstrap.Toast === "function",
            );
            if (hasBS) {
              const container =
                document.getElementById("toast-container") ??
                ((): HTMLDivElement => {
                  const d = document.createElement("div");
                  d.id = "toast-container";
                  d.className =
                    "toast-container position-fixed bottom-0 end-0 p-3";
                  document.body.appendChild(d);
                  return d;
                })();
              const toastEl = document.createElement("div");
              toastEl.className =
                "toast align-items-center text-bg-danger border-0";
              for (const [k, v] of Object.entries({
  "role": "alert",
  "aria-live": "assertive",
  "aria-atomic": "true",
}))
  toastEl.setAttribute(k, v);
              toastEl.innerHTML =
                '<div class="d-flex"><div class="toast-body">' +
                msg +
                '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
              container.appendChild(toastEl);
              new bootstrap.Toast(toastEl, { delay: 5000 }).show();
            } else {
              alert(msg);
            }
          } catch (__err) {
    console.error(`[show] Error:`, __err);
  }
        });
      }
    } catch (__err) {
    console.error(`[show] Error:`, __err);
  }
  });
})();

export {};
