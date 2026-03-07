/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/import.js
 * @generated from original JavaScript - manual review recommended
 * @module import
 */


((): void => {
  try {
    const f = document.getElementById("vendor-import-form");
    if (!f || f.getAttribute("data-listener-active") === "true") return;
    f.setAttribute("data-listener-active", "true");

    const resolved = f.getAttribute("data-resolved-action") ?? "#";
    if (
      (f.getAttribute("action") === "#" || !f.getAttribute("action")) &&
      resolved !== "#"
    ) {
      f.setAttribute("action", resolved);
    }

    f.addEventListener("submit", (e: Event) => {
      const action = f.getAttribute("action") ?? "#";
      if (action && action !== "#") return;
      e.preventDefault();

      const msg =
        f.getAttribute("data-guard-msg") ??
        "Import vendor route is unavailable. Please contact technical support or your domain administrator.";
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

    const fileInput = document.getElementById(
      "file",
    ) as HTMLInputElement | null;
    if (fileInput) {
      fileInput.addEventListener("change", (): void => {
        const target = document.querySelector(
          "." + (fileInput.getAttribute("data-filename") ?? "upload_file"),
        );
        if (target) target.textContent = fileInput.files?.[0]?.name ?? "";
      });
    }
  } catch {}
})();

export {};
