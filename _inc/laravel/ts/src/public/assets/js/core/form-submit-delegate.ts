/**
 * form-submit-delegate.ts — CSP-safe delegated submit trigger.
 * Replaces inline onclick="…submit()" handlers.
 * Any element with `data-submit-form` attribute will submit the
 * form whose `id` matches the attribute value.
 *
 * Mirror of public/assets/js/core/form-submit-delegate.js
 * @module core/form-submit-delegate
 */

(() => {
  "use strict";

  document.addEventListener("click", (e: MouseEvent) => {
    const target = e.target as HTMLElement | null;
    if (!target) return;

    const trigger = target.closest<HTMLElement>("[data-submit-form]");
    if (!trigger) return;

    e.preventDefault();
    const formId = trigger.getAttribute("data-submit-form");
    if (!formId) return;

    const form = document.getElementById(formId) as HTMLFormElement | null;
    if (form && typeof form.submit === "function") {
      form.submit();
    }
  });
})();

export {};
