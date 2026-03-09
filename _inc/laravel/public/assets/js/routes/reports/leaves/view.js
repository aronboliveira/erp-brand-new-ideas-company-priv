(() => {
  if (typeof window !== "undefined" && window.ERPGuard) {
    const guard = window.ERPGuard;
    guard.bindClickGuard("a.view-employee-leave", {
      fallbackMsg:
        "Leave detail route is unavailable. Please contact technical support or your domain administrator.",
      validateUrl: true,
      updateHref: true,
    });
  }
})();
