(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard(".email-template-lang-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Manage email template language route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
