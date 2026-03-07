/**
 * @fileoverview TypeScript version of public/assets/js/routes/warehouses/transfers/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */


((): void => {
  try {
    const f = document.getElementById("warehouse-transfer-store-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    const current = f.getAttribute("action");
    if ((current === "#" || !current) && resolved !== "#")
      f.setAttribute("action", resolved);

    f.addEventListener("submit", (e: Event) => {
      const action = f.getAttribute("action") ?? "#";
      if (action && action !== "#") return;
      e.preventDefault();

      const msg =
        f.getAttribute("data-guard-msg") ?? "Store warehouse transfer route is unavailable. Please contact technical support or your domain administrator.";
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
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
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
  } catch {}
})();

export {};
