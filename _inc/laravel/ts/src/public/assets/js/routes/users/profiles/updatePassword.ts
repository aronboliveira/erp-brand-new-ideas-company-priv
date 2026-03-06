/**
 * @fileoverview TypeScript version of public/assets/js/routes/users/profiles/updatePassword.js
 * @generated from original JavaScript - manual review recommended
 * @module updatePassword
 */
/* eslint-disable @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("profile-password-update-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    const current = f.getAttribute("action");
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if ((current === "#" || !current) && resolved !== "#")
      f.setAttribute("action", resolved);

    f.addEventListener("submit", e => {
      const action = f.getAttribute("action") ?? "#";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (action && action !== "#") return;
      e.preventDefault();

      const msg =
        f.getAttribute("data-guard-msg") ?? "Update password route is unavailable. Please contact technical support or your domain administrator.";
      let container = document.getElementById("toast-container");
      if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        container.style.zIndex = "1080";
        document.body.appendChild(container);
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
        container.appendChild(t);
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
