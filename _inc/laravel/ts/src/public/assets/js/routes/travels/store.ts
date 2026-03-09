/**
 * @fileoverview TypeScript version of public/assets/js/routes/travels/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

(function (): void {
  try {
    const form = document.getElementById("create_travel");
    if (!form) return;
    if (form.getAttribute("data-listener-active") === "true") return;
    form.setAttribute("data-listener-active", "true");

    if (!form.getAttribute("data-listener-bound-submit")) {
      form.setAttribute("data-listener-bound-submit", "1");
      form.addEventListener("submit", function (e: Event) {
        try {
          const action = form.getAttribute("action") ?? "#";
          if (!action || action === "#") {
            e.preventDefault();
            const msg =
              form.getAttribute("data-guard-msg") ??
              "Store travel route is unavailable. Please contact technical support or your domain administrator.";
            let container = document.getElementById("toast-container");
            if (!container) {
              container = document.createElement("div");
              container.id = "toast-container";
              container.className =
                "toast-container position-fixed top-0 end-0 p-3";
              container.style.zIndex = "1080";
              document.body.appendChild(container);
            }
            const bs =
              typeof window.bootstrap !== "undefined" ? window.bootstrap : null;
            if (bs?.Toast) {
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
              bs.Toast.getOrCreateInstance(t).show();
            } else {
              alert(msg);
            }
            return;
          }
          const sd = form.querySelector(
              'input[name="start_date"]',
            ) as HTMLInputElement | null,
            ed = form.querySelector(
              'input[name="end_date"]',
            ) as HTMLInputElement | null;
          if (sd && ed && sd.value && ed.value) {
            const s = new Date(sd.value),
              en = new Date(ed.value);
            if (en < s) {
              e.preventDefault();
              const m = "End Date cannot be earlier than Start Date.";
              let c = document.getElementById("toast-container");
              if (!c) {
                c = document.createElement("div");
                c.id = "toast-container";
                document.body.appendChild(c);
              }
              const bs2 =
                typeof window.bootstrap !== "undefined"
                  ? window.bootstrap
                  : null;
              if (bs2?.Toast) {
                const t2 = document.createElement("div");
                t2.className = "toast";
                for (const [k, v] of Object.entries({
                  role: "alert",
                  "aria-live": "assertive",
                  "aria-atomic": "true",
                }))
                  t2.setAttribute(k, v);
                const b2 = document.createElement("div");
                b2.className = "toast-body";
                b2.textContent = m;
                t2.appendChild(b2);
                c.appendChild(t2);
                bs2.Toast.getOrCreateInstance(t2).show();
              } else {
                alert(m);
              }
              return;
            }
          }
        } catch (__err) {
          console.error(`[store] Error:`, __err);
        }
      });
    }
  } catch (__err) {
    console.error(`[store] Error:`, __err);
  }
})();

export {};
