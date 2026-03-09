/**
 * @fileoverview TypeScript version of public/assets/js/routes/pos/setting.js
 * @generated from original JavaScript - manual review recommended
 * @module setting
 */

((): void => {
  try {
    const l = document.getElementById("pos-setting-btn");
    if (!l) return;
    if (l.getAttribute("data-listener-active") === "true") return;
    l.setAttribute("data-listener-active", "true");
    l.addEventListener("click", (e: Event) => {
      try {
        const href = (l.getAttribute("href") ?? "#").trim(),
          url = (l.getAttribute("data-url") ?? "#").trim();
        if (url !== "#" && href !== "#") return;
        e.preventDefault();
        const msg =
            l.getAttribute("data-guard-msg") ??
            "POS barcode setting route is unavailable. Please contact technical support or your domain administrator.",
          hasBs = !!(
            document.querySelector('link[href*="bootstrap"]') &&
            window.bootstrap
          );
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        if (hasBs) {
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
          bootstrap.Toast.getOrCreateInstance(t).show();
        } else {
          alert(msg);
        }
        l.setAttribute("data-failed-route", "true");
      } catch (err) {
        console.error(`[setting] Error:`, err);
      }
    });
  } catch (err) {
    console.error(`[setting] Error:`, err);
  }
})();

export {};
