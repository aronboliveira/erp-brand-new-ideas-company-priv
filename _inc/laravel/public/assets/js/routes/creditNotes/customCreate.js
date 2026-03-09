/**
 * @fileoverview Form submission guard for custom credit note creation using ERPGuard singleton
 * @module assets/js/routes/creditNotes/customCreate
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#invoice_custom_credit_note_form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
