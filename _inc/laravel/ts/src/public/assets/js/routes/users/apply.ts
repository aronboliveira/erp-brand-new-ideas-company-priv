/**
 * @fileoverview TypeScript version of public/assets/js/routes/users/apply.js
 * @generated from original JavaScript - manual review recommended
 * @module apply
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("user_userlog");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    if (
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    const apply = document.getElementById("userlog-apply-btn");
    if (apply?.getAttribute("data-listener-active") !== "true") {
      apply.setAttribute("data-listener-active", "true");
      apply.addEventListener("click", e => {
        e.preventDefault();
        const action = f.getAttribute("action") ?? "#";
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
        if (action && action !== "#") {
          f.submit();
          return;
        }
        const msg =
          f.getAttribute("data-guard-msg") ?? "User logs route is unavailable. Please contact technical support or your domain administrator.";
        let c = document.getElementById("toast-container");
        if (!c) {
          c = document.createElement("div");
          c.id = "toast-container";
          document.body.appendChild(c);
        }
        const hasBS =
          document.querySelector('link[href*="bootstrap"]') &&
          // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
          window.bootstrap &&
          window.bootstrap.Toast;
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
        f.setAttribute("data-failed-route", "true");
      });
    }
  } catch {}
})();

export {};
