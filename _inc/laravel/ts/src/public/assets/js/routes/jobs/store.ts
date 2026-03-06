/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
((): void => {
  const show = msg => {
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      const hasBs = !!window.bootstrap.Toast;
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
      if (hasBs) {
        const c =
          document.getElementById("toast-container") ??
          ((): void => {
            const t = document.createElement("div");
            t.id = "toast-container";
            document.body.appendChild(t);
            return t;
          })();
        const el = document.createElement("div");
        el.className = "toast";
        el.setAttribute("role", "alert");
        el.setAttribute("aria-live", "assertive");
        el.setAttribute("aria-atomic", "true");
        const body = document.createElement("div");
        body.className = "toast-body";
        body.textContent = msg;
        el.appendChild(body);
        c.appendChild(el);
        window.bootstrap.Toast.getOrCreateInstance(el).show();
      } else {
        alert(msg);
      }
    } catch {
      alert(msg);
    }
  };

  const safeUrl = el =>
    (
      el?.getAttribute("action") ||
      el?.getAttribute("data-url") ||
      el?.getAttribute("href") ?? ""
    ).trim();

  const guard = el =>
    el?.getAttribute("data-guard-msg") ?? "Route is unavailable. Please contact technical support or your domain administrator.";

  const form = document.getElementById("job-create-form");
  if (form) {
    form.addEventListener(
      "submit",
      e => {
        const url = safeUrl(form);
        if (!url || url === "#") {
          e.preventDefault();
          show(guard(form));
        }
      },
      { passive: false }
    );
  }

  const guardLinks = Array.from(
    document.querySelectorAll('a[data-ajax-popup-over="true"]')
  );
  guardLinks.forEach(a => {
    a.addEventListener("click", e => {
      const url = safeUrl(a);
      if (!url || url === "#") {
        e.preventDefault();
        show(guard(a));
      }
    });
  });

  // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
  if (window.jQuery) {
    const $ = window.jQuery;
    $(".summernote-simple").each(function (): void {
      if (!$(this).data("summernote")) $(this).summernote({ height: 200 });
    });
    $(".summernote-simple-2").each(function (): void {
      if (!$(this).data("summernote")) $(this).summernote({ height: 300 });
    });
    $('input[data-toggle="tags"]').each(function (): void {
      if (typeof $(this).tagsinput === "function") $(this).tagsinput("items");
    });
  }
})();

export {};
