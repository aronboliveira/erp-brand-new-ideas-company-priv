/**
 * Journal Entries Cancel Route Guards
 * Handles cancel link buttons with data-href navigation
 * @module routes/journalEntries/cancel
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    "button.cancel-link[data-href][data-guard-msg],input.cancel-link[data-href][data-guard-msg]",
    {
      fallbackMsg:
        "Journal entries index route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
