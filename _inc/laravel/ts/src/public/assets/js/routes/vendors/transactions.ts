/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/transactions.js
 * @generated from original JavaScript - manual review recommended
 * @module transactions
 */

((): void => {
  try {
    const f = document.getElementById("vendor-transaction-filter-form");
    if (f && f.getAttribute("data-listener-active") !== "true") {
      f.setAttribute("data-listener-active", "true");
      const resolved = f.getAttribute("data-resolved-action") ?? "#";
      if (
        (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
        resolved !== "#"
      )
        f.setAttribute("action", resolved);
      f.addEventListener("submit", (e: Event) => {
        const action = f.getAttribute("action") ?? "#";
        if (action && action !== "#") return;
        e.preventDefault();
        const msg =
          f.getAttribute("data-guard-msg") ??
          "Vendor transaction route is unavailable. Please contact technical support or your domain administrator.";
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const ok =
          document.querySelector('link[href*="bootstrap"]') &&
          window.bootstrap.Toast;
        if (ok) {
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
          try {
            window.bootstrap.Toast.getOrCreateInstance(t).show();
          } catch {
            alert(msg);
          }
        } else {
          alert(msg);
        }
        f.setAttribute("data-failed-route", "true");
      });
    }

    const reset = document.getElementById("vendor-transaction-reset-link");
    if (reset && reset.getAttribute("data-listener-active") !== "true") {
      reset.setAttribute("data-listener-active", "true");
      const url = reset.getAttribute("data-url") ?? "#";
      if (
        (reset.getAttribute("href") === "#" || !reset.getAttribute("href")) &&
        url !== "#"
      )
        reset.setAttribute("href", url);
      if (!reset.getAttribute("data-listener-bound-click")) {
        reset.setAttribute("data-listener-bound-click", "1");
        reset.addEventListener("click", (e: Event) => {
          const href = reset.getAttribute("href") ?? "#";
          if (href && href !== "#") return;
          e.preventDefault();
          const msg =
            reset.getAttribute("data-guard-msg") ??
            "Vendor transaction route is unavailable. Please contact technical support or your domain administrator.";
          let c = document.getElementById("toast-container");
          if (!c) {
            c = document.createElement("div");
            c.id = "toast-container";
            document.body.appendChild(c);
          }
          const ok =
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap.Toast;
          if (ok) {
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
            try {
              window.bootstrap.Toast.getOrCreateInstance(t).show();
            } catch {
              alert(msg);
            }
          } else {
            alert(msg);
          }
          reset.setAttribute("data-failed-route", "true");
        });
      }
    }
  } catch (__err) {
    console.error(`[transactions] Error:`, __err);
  }
})();

export {};
