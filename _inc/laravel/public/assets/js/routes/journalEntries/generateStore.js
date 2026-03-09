/**
 * @file Journal entry AI generate store route guard
 * @description Prevents navigation if AI generation route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard(
      "a.ai-btn[data-ajax-popup-over][data-url][data-guard-msg]",
      {
        msg: btoa(
          "Generate content route is unavailable. Please contact technical support or your domain administrator.",
        ),
      },
    );
  } catch {}
})();
