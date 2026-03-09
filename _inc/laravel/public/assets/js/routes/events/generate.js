/**
 * Event Generate Route Guards
 * Handles AI event generation link validation
 * @module routes/events/generate
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#event-generate-ai-link", {
    fallbackMsg: "#",
  });
})();
