(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindChangeGuard(".item", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Select item route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
