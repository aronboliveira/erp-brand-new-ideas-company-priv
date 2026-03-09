/* assets/js/routes/supports/create.js */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.support-create", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create support route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
