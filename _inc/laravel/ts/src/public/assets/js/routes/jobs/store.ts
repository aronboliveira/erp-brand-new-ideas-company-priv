/**
 * @fileoverview TypeScript version of public/assets/js/routes/jobs/store.js
 * @generated from original JavaScript - manual review recommended
 * @module store
 */

/* global bootstrap, $, jQuery */
((): void => {
  const show = (msg: string) => {
    try {
      const hasBs = !!window.bootstrap.Toast;
      if (hasBs) {
        const c =
          document.getElementById("toast-container") ??
          ((): HTMLDivElement => {
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

  const safeUrl = (el: Element | null) =>
    (
      (el?.getAttribute("action") ||
        el?.getAttribute("data-url") ||
        el?.getAttribute("href")) ??
      ""
    ).trim();

  const guard = (el: Element | null) =>
    el?.getAttribute("data-guard-msg") ??
    "Route is unavailable. Please contact technical support or your domain administrator.";

  const form = document.getElementById("job-create-form");
  if (form) {
    form.addEventListener(
      "submit",
      (e: Event) => {
        const url = safeUrl(form);
        if (!url || url === "#") {
          e.preventDefault();
          show(guard(form));
        }
      },
      { passive: false },
    );
  }

  const guardLinks = Array.from(
    document.querySelectorAll('a[data-ajax-popup-over="true"]'),
  );
  guardLinks.forEach(a => {
    a.addEventListener("click", (e: Event) => {
      const url = safeUrl(a);
      if (!url || url === "#") {
        e.preventDefault();
        show(guard(a));
      }
    });
  });

  if (window.jQuery) {
    const $ = window.jQuery;
    $(".summernote-simple").each(function (): void {
      if (!$(this).data("summernote")) $(this).summernote({ height: 200 });
    });
    $(".summernote-simple-2").each(function (): void {
      if (!$(this).data("summernote")) $(this).summernote({ height: 300 });
    });
    $('input[data-toggle="tags"]').each(function (): void {
      if (typeof $(this).tagsinput === "function")
        ($(this) as JQuery).tagsinput(
          "items" as unknown as Record<string, unknown>,
        );
    });
  }
})();

export {};
