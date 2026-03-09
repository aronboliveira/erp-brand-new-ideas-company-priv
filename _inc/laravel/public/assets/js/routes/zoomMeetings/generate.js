/**
 * Zoom Meeting Generate Route Guards
 * Handles AI zoom meeting generation link validation
 * @module routes/zoomMeetings/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#zoom-ai-generate-link", {
    fallbackMsg:
      "AI generation route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
