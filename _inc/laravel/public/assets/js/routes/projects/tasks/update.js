(() => {
  if (typeof window !== "undefined" && window.ERPGuard) {
    const guard = window.ERPGuard;
    guard.bindSubmitGuard("#edit_task", {
      fallbackMsg:
        "Update project task route is unavailable. Please contact technical support or your domain administrator.",
    });
  }
})();
