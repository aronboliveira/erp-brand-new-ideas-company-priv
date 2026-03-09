/**
 * @fileoverview TypeScript version of public/assets/js/routes/products/services/import.js
 * @generated from original JavaScript - manual review recommended
 * @module import
 */

((): void => {
  try {
    const fm = document.getElementById("prd-sv-import-form");
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
              "Product CSV import route is unavailable. Please contact technical support or your domain administrator.",
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
        } catch (__err) {
          console.error(`[import] Error:`, __err);
        }
      });
    }
  } catch (__err) {
    console.error(`[import] Error:`, __err);
  }

  try {
    const inp = document.getElementById("file");
    if (!inp) return;
    if (inp.getAttribute("data-filename-guarded") === "true") return;
    inp.setAttribute("data-filename-guarded", "true");
    if (!inp.getAttribute("data-listener-bound-change")) {
      inp.setAttribute("data-listener-bound-change", "1");
      inp.addEventListener("change", (): void => {
        try {
          const sel = inp.getAttribute("data-filename") ?? "",
            out = sel ? document.querySelector("." + sel) : null;
          if (!out) return;
          const file = (inp as HTMLInputElement).files?.[0]
            ? (inp as HTMLInputElement).files![0]
            : null;
          out.textContent = file ? file.name : "";
        } catch (__err) {
          console.error(`[import] Error:`, __err);
        }
      });
    }
  } catch (__err) {
    console.error(`[import] Error:`, __err);
  }
})();

export {};
