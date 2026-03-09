/**
 * @fileoverview Click guard for expense index breadcrumb link using ERPGuard singleton
 * @module assets/js/routes/expenses/editIndex
 */
(() => {
  try {
    window.ERPGuard?.bindClickGuard?.("#bc-expense-index-link", {
      msg: btoa(
        "Expense index route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
