/**
 * @fileoverview TypeScript version of public/assets/js/routes/leaves/types/update.js
 * @generated from original JavaScript - manual review recommended
 * @module update
 */

((): void => {
  "use strict";

  const form = document.getElementById("leaveType-edit-form");
  if (!form) return;

  const guardMsg =
    form.getAttribute("data-guard-msg") ?? "Update route is unavailable.";
  const submitBtn = form.querySelector('input[type="submit"]');
  const titleEl = form.querySelector<HTMLInputElement>("#title");
  const daysEl = form.querySelector<HTMLInputElement>("#days");

  const actionIsBlocked = (): boolean => {
    const act = form.getAttribute("action") ?? "#";
    return !act || act === "#";
  };

  const showErr = (msg: string): void=> {
    try {
      alert(msg);
    } catch {
      /* no-op */
    }
  };

  const validate = (): boolean => {
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

  if (actionIsBlocked() && submitBtn) {
    submitBtn.addEventListener("click", (e: Event) => {
      e.preventDefault();
      showErr(guardMsg);
    });
  }

  form.addEventListener("submit", (e: Event) => {
    if (actionIsBlocked()) {
      e.preventDefault();
      showErr(guardMsg);
      return;
    }
    if (!validate()) {
      e.preventDefault();
    }
  });
})();

export {};
