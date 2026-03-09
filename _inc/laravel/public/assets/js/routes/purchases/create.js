// assets/js/routes/purchases/create.js
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.create-purchase", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create purchase route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
