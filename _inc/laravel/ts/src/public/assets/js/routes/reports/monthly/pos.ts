/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/monthly/pos.js
 * @generated from original JavaScript - manual review recommended
 * @module pos
 */

((): void => {
  try {
    document
      .querySelectorAll(".monthly-pos-link")
      .forEach((el: Element): void => {
        try {
          const alias = "data-listening-monthlyposclick";
          if (!el.hasAttribute(alias)) {
            el.setAttribute(alias, "true");
            el.addEventListener("click", event => {
              try {
                const url = el.getAttribute("data-url");
                const href = el.getAttribute("href") ?? "";
                if ((url && url !== "#") ?? (href && href !== "#")) return;
                event.preventDefault();
                const msg =
                  el.getAttribute("data-guard-msg") ??
                  "Monthly route is unavailable. Please contact technical support or your domain administrator.";
                const hasBS = Array.from(document.scripts).some(
                  s =>
                    s.src.includes("bootstrap.min.js") &&
                    window.bootstrap &&
                    typeof bootstrap.Toast === "function",
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
    console.error(`[pos] Error:`, __err);
  }
            });
          }
        } catch (__err) {
    console.error(`[pos] Error:`, __err);
  }
      });
  } catch (__err) {
    console.error(`[pos] Error:`, __err);
  }
})();

export {};
