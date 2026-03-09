/**
 * @file Announcement Store Route Guard
 * @description Guards the announcement creation form and AI generate link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#announcement-ai-generate-link", {
    msgKey: "generate_announcement_ai_unavailable",
    fallbackMsg:
      "AI announcement generation route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindSubmitGuard("#announcement-store-form", {
    msgKey: "store_announcement_unavailable",
    fallbackMsg:
      "Create announcement route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
