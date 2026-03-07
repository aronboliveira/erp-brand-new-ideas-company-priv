/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/chatify/card.js
 * @generated from original JavaScript - manual review recommended
 * @module card
 */

/* global bootstrap */
((): void => {
  try {
    const anchors = document.querySelectorAll("a[data-guard-msg][data-url]");
    anchors.forEach(a => {
      if (a.getAttribute("data-listener-active") === "true") return;
      a.setAttribute("data-listener-active", "true");
      const url = a.getAttribute("data-url") ?? "#";
      if (
        (a.getAttribute("href") === "#" || !a.getAttribute("href")) &&
        url !== "#"
      )
        a.setAttribute("href", url);
      a.addEventListener("click", (e: Event) => {
        const href = a.getAttribute("href") ?? "#";
        if (href && href !== "#") return;
        e.preventDefault();
        const msg = a.getAttribute("data-guard-msg") ?? "Route is unavailable.";
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const hasBS =
          document.querySelector('link[href*="bootstrap"]') &&
          window.bootstrap?.Toast;
        if (hasBS) {
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
        a.setAttribute("data-failed-route", "true");
      });
    });
  } catch {}
})();

export {};
