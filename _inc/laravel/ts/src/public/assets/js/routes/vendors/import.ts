/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/import.js
 * @generated from original JavaScript - manual review recommended
 * @module import
 */
/* eslint-disable @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars */

/* global bootstrap */
((): void => {
  try {
    const f = document.getElementById("vendor-import-form");
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

    f.addEventListener("submit", e => {
      const action = f.getAttribute("action") ?? "#";
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (action && action !== "#") return;
      e.preventDefault();

      const msg =
        f.getAttribute("data-guard-msg") ?? "Import vendor route is unavailable. Please contact technical support or your domain administrator.";
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

    const fileInput = document.getElementById("file");
    if (fileInput) {
      fileInput.addEventListener("change", (): void => {
        const target = document.querySelector(
          "." + (fileInput.getAttribute("data-filename") ?? "upload_file")
        );
        if (target) target.textContent = fileInput.files?.[0]?.name ?? "";
      });
    }
  } catch {}
})();

export {};
