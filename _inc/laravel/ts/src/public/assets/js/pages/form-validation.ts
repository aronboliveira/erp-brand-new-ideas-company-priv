/**
 * @fileoverview TypeScript version of public/assets/js/pages/form-validation.js
 * @generated from original JavaScript - manual review recommended
 * @module form-validation
 */

"use strict";

declare let Bouncer: new (selector: string, options?: unknown) => unknown;

const _bouncer = new Bouncer("[data-validate]", {
  disableSubmit: true,
  customValidations: {
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    valueMismatch: function (
      field: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement,
    ) {
      const selector = field.getAttribute("data-bouncer-match");
      if (!selector || !field.form) return false;
      const otherField = field.form.querySelector<HTMLInputElement>(selector);
      if (!otherField) return false;
      return otherField.value !== field.value;
    },
  },
  messages: {
    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
    valueMismatch: function (
      field: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement,
    ) {
      const customMessage = field.getAttribute("data-bouncer-mismatch-message");
      return customMessage
        ? customMessage
        : "Please make sure the fields match.";
    },
  },
});

document.addEventListener(
  "bouncerFormInvalid",
  function (event: Event) {
    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
    const detail = (event as CustomEvent).detail;
    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-argument
    if (detail?.errors?.[0]) window.scrollTo(0, detail.errors[0].offsetTop);
  },
  false,
);

document.addEventListener(
  "bouncerFormValid",
  function (): void {
    alert("Form submitted successfully!");
    window.location.reload();
  },
  false,
);

export {};
