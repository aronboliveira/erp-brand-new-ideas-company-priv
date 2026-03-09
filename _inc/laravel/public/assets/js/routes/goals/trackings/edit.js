/**
 * @file Goal Tracking Edit Route Guard
 * @description Guards goal tracking edit form and manages progress range using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard("#goal-tracking-edit-form", {
      fallbackMsg:
        "Update route is unavailable. Please contact technical support or your domain administrator.",
    });

    const range = document.getElementById("goal-progress-range");
    const out = document.getElementById("goal-progress-output");
    if (range && out) {
      const sync = () => {
        try {
          out.textContent = String(range.value || "0");
        } catch {}
      };
      range.addEventListener("input", sync);
      range.addEventListener("change", sync);
      sync();
    }

    guard.initTooltips();
  } catch {}
})();
