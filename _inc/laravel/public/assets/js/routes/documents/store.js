/**
 * @file Document Store Route Guard
 * @description Guards the document creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#document-store-form", {
    msgKey: "store_document_unavailable",
    fallbackMsg:
      "Store document route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
