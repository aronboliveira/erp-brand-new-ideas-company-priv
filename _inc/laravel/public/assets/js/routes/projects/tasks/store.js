/**
 * @file Project Tasks Store Guard
 * @description Guards project task creation form using ERPGuard singleton
 * @requires ERPGuard
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;

  if (!guard) {
    
    return;
  }

  const FORM_MSG_KEY = "project_task_store_unavailable";
  const AI_MSG_KEY = "ai_generate_unavailable";
  const FORM_FALLBACK = "Create project task route is unavailable. Please contact technical support or your domain administrator.";
  const AI_FALLBACK = "Generate project task content route is unavailable. Please contact technical support or your domain administrator.";

  try {
    // Guard form submission
    const form = document.getElementById("store_task");
    if (form && form.getAttribute("data-submit-listener") !== "true") {
      form.setAttribute("data-submit-listener", "true");

      form.addEventListener("submit", e => {
        try {
          const action = form.getAttribute("action") || "#";
          if (!guard.isInvalidUrl(action)) return;

          e.preventDefault();
          const msg = utils?.getTranslation?.(FORM_MSG_KEY) ||
            form.getAttribute("data-guard-msg") ||
            FORM_FALLBACK;
          guard.showToast(msg, "error");
          form.setAttribute("data-failed-route", "true");
        } catch (_) {}
      }, { passive: false });
    }

    // Guard AI generate link
    const ai = document.getElementById("project-task-ai-generate-link");
    if (ai && ai.getAttribute("data-ai-listener") !== "true") {
      ai.setAttribute("data-ai-listener", "true");

      ai.addEventListener("click", e => {
        try {
          const href = ai.getAttribute("href") || "#";
          const url = ai.getAttribute("data-url") || href;

          if (!guard.isInvalidUrl(href) || !guard.isInvalidUrl(url)) return;

          e.preventDefault();
          const msg = utils?.getTranslation?.(AI_MSG_KEY) ||
            ai.getAttribute("data-guard-msg") ||
            AI_FALLBACK;
          guard.showToast(msg, "error");
          ai.setAttribute("data-failed-route", "true");
        } catch (_) {}
      }, { passive: false });
    }
  } catch (_) {}
})();
