/**
 * @file form-submit-delegate.js
 * @description Replaces inline onclick="…submit()" handlers with a CSP-safe
 *   delegated event listener.  Any anchor (or button) with a
 *   `data-submit-form` attribute will, on click, submit the form whose
 *   `id` matches the attribute value.
 */
(() => {
  "use strict";
  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-submit-form]");
    if (!trigger) return;
    e.preventDefault();
    const formId = trigger.getAttribute("data-submit-form");
    if (!formId) return;
    const form = document.getElementById(formId);
    if (form && typeof form.submit === "function") {
      form.submit();
    }
  });
})();
