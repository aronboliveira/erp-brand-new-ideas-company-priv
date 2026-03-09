/**
 * @fileoverview Click guard for goal AI generation using ERPGuard singleton
 * @module assets/js/routes/goals/trackings/generateEdit
 */
(() => {
  try {
    window.ERPGuard?.bindClickGuard?.("#goal-ai-generate-btn", {
      msg: btoa(
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
