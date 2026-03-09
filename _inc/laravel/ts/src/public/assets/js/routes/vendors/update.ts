/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  try {
    const f = document.getElementById("vendor-update-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");
    const resolved = f.getAttribute("data-resolved-action") ?? "#",
      current = f.getAttribute("action");
    if ((current === "#" || !current) && resolved !== "#")
      f.setAttribute("action", resolved);
    f.addEventListener("submit", (e: Event) => {
      const action = f.getAttribute("action") ?? "#";
      if (action && action !== "#") return;
      e.preventDefault();
      const msg =
        f.getAttribute("data-guard-msg") ??
        "Update vendor route is unavailable. Please contact technical support or your domain administrator.";
      let c = document.getElementById("toast-container");
      if (!c) {
        c = document.createElement("div");
        c.id = "toast-container";
        document.body.appendChild(c);
      }
      const hasBS =
        document.querySelector('link[href*="bootstrap"]') &&
        window.bootstrap.Toast;
      if (hasBS) {
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
  } catch (__err) {
    console.error(`[update] Error:`, __err);
  }
})();

export {};
