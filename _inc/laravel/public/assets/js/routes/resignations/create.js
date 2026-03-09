/**
 * @file Resignation Create Route Guard
 * @description Guards resignation creation links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("a.resignation-create", {
    msgKey: "create_resignation_unavailable",
    fallbackMsg:
      "Create resignation route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
