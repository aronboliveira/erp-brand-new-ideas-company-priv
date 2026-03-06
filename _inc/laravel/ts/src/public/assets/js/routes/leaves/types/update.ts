/**
 * @fileoverview TypeScript version of public/assets/js/routes/leaves/types/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access */

((): void => {
  "use strict";

  const form = document.getElementById("leaveType-edit-form");
  if (!form) return;

  const guardMsg =
    form.getAttribute("data-guard-msg") ?? "Update route is unavailable.";
  const submitBtn = form.querySelector('input[type="submit"]');
  const titleEl = form.querySelector("#title");
  const daysEl = form.querySelector("#days");

  const actionIsBlocked = (): void => {
    const act = form.getAttribute("action") ?? "#";
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    return !act || act === "#";
  };

  const showErr = msg => {
    try {
      alert(msg);
    } catch {
      /* no-op */
    }
  };

  const validate = (): void => {
    if (titleEl && !titleEl.value.trim()) {
      titleEl.focus();
      return false;
    }
    if (daysEl) {
      const v = Number(daysEl.value);
      if (Number.isNaN(v) || v < 0) {
        daysEl.focus();
        return false;
      }
    }
    return true;
  };

  // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
  if (actionIsBlocked() && submitBtn) {
    submitBtn.addEventListener("click", e => {
      e.preventDefault();
      showErr(guardMsg);
    });
  }

  form.addEventListener("submit", e => {
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (actionIsBlocked()) {
      e.preventDefault();
      showErr(guardMsg);
      return;
    }
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
    if (!validate()) {
      e.preventDefault();
    }
  });
})();

export {};
