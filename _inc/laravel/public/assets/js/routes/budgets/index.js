/**
 * Budgets Index Route Guards
 * Handles budget edit, view, and destroy buttons with dynamic IDs
 * @module routes/budgets/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#{{ $editBtnId }}", {
    fallbackMsg:
      "Edit budget route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#{{ $viewBtnId }}", {
    fallbackMsg:
      "View budget route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard("#{{ $destroyBtnId }}", {
    fallbackMsg:
      "Delete budget route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
