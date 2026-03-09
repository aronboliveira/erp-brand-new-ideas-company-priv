(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#update-project-stage-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update project stage route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
