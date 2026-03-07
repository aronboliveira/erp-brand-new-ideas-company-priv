/**
 * @fileoverview TypeScript version of public/assets/js/routes/projects/reports/reset.js
 * @generated from original JavaScript - manual review recommended
 * @module reset
 */

/* global bootstrap */
((): void => {
  try {
    const form = document.getElementById("project_report_submit");
    const formAlias = "data-listening-projectreportsubmit";
    if (form && !form.hasAttribute(formAlias)) {
      form.setAttribute(formAlias, "true");
      form.addEventListener("submit", event => {
        try {
          const url = form.getAttribute("data-url");
          if (url !== "#" || (form as HTMLFormElement).action !== "#") return;
          event.preventDefault();
          const msg =
            form.getAttribute("data-guard-msg") ??
            "Project report index route is unavailable. Please contact technical support or your domain administrator.";
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
                const c = document.createElement("div");
                c.id = "toast-container";
                c.className =
                  "toast-container position-fixed bottom-0 end-0 p-3";
                document.body.appendChild(c);
                return c;
              })();
            const toastEl = document.createElement("div");
            toastEl.className =
              "toast align-items-center text-bg-danger border-0";
            toastEl.setAttribute("role", "alert");
            toastEl.setAttribute("aria-live", "assertive");
            toastEl.setAttribute("aria-atomic", "true");
            toastEl.innerHTML =
              '<div class="d-flex"><div class="toast-body">' +
              msg +
              '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
            container.appendChild(toastEl);
            new bootstrap.Toast(toastEl, { delay: 5000 }).show();
          } else {
            alert(msg);
          }
        } catch {}
      });
    }
  } catch {}
  try {
    const selector = ".reset-project-report-link";
    const alias = "data-listening-resetprojectreportclick";
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
                "Project report index route is unavailable. Please contact technical support or your domain administrator.";
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
                    const c = document.createElement("div");
                    c.id = "toast-container";
                    c.className =
                      "toast-container position-fixed bottom-0 end-0 p-3";
                    document.body.appendChild(c);
                    return c;
                  })();
                const toastEl = document.createElement("div");
                toastEl.className =
                  "toast align-items-center text-bg-danger border-0";
                toastEl.setAttribute("role", "alert");
                toastEl.setAttribute("aria-live", "assertive");
                toastEl.setAttribute("aria-atomic", "true");
                toastEl.innerHTML =
                  '<div class="d-flex"><div class="toast-body">' +
                  msg +
                  '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
                container.appendChild(toastEl);
                new bootstrap.Toast(toastEl, { delay: 5000 }).show();
              } else {
                alert(msg);
              }
            } catch {}
          });
        }
      } catch {}
    });
  } catch {}
})();

export {};
