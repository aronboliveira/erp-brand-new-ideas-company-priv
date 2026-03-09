/**
 * @file Leads Update Route Guard
 * @description Guards the leads update form and AI generate button
 * @requires ERPGuard
 * @requires ERPUtils
 * @requires jQuery
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;

  if (!guard || !$) {
    
    return;
  }

  const L = "data-guard-listener";
  const FORM_ID = "lead-update-form";
  const AI_ID = "lead-ai-generate";
  const FORM_MSG_KEY = "lead_update_route_unavailable";
  const AI_MSG_KEY = "ai_generate_unavailable";
  const FORM_FALLBACK = "Update lead route is unavailable. Please contact technical support or your domain administrator.";
  const AI_FALLBACK = "AI generate feature is unavailable. Please contact technical support.";

  /**
   * Binds the form submit guard
   */
  const bindFormGuard = () => {
    try {
      const form = document.getElementById(FORM_ID);
      if (!form || form.getAttribute(L) === "true") return;
      form.setAttribute(L, "true");

      guard.bindSubmitGuard(`#${FORM_ID}`, {
        fallbackMsg: FORM_FALLBACK,
        handler(event, formElement) {
          event.preventDefault();
          const url = formElement.getAttribute("data-url") || "";
          const action = formElement.getAttribute("action") || "";

          if (!guard.isInvalidUrl(url) || !guard.isInvalidUrl(action)) {
            try {
              formElement.submit();
            } catch (_) {}
            return;
          }

          const msg = utils?.getTranslation?.(FORM_MSG_KEY) ||
            formElement.getAttribute("data-guard-msg") ||
            FORM_FALLBACK;
          guard.showToast(msg, "error");
          formElement.setAttribute("data-failed-route", "true");
        }
      });
    } catch (_) {}
  };

  /**
   * Binds the AI generate button guard
   */
  const bindAiGuard = () => {
    try {
      const el = document.getElementById(AI_ID);
      if (!el || el.getAttribute(L) === "true") return;
      el.setAttribute(L, "true");

      $(el).off("click.aiGuard").on("click.aiGuard", e => {
        try {
          const url = el.getAttribute("data-url");
          const href = el.href;

          if (guard.isInvalidUrl(url) && guard.isInvalidUrl(href)) {
            e.preventDefault();
            const msg = utils?.getTranslation?.(AI_MSG_KEY) ||
              el.getAttribute("data-guard-msg") ||
              AI_FALLBACK;
            guard.showToast(msg, "error");
          }
        } catch (_) {
          e.preventDefault();
          guard.showToast(AI_FALLBACK, "error");
        }
      });

      const obs = new MutationObserver(() => {
        if (!document.body.contains(el)) {
          $(el).off("click.aiGuard");
          obs.disconnect();
        }
      });
      obs.observe(document.body, { childList: true, subtree: true });
    } catch (_) {}
  };

  const ready = () => {
    bindFormGuard();
    bindAiGuard();
  };

  if (document.readyState === "loading") {
    $(ready);
  } else {
    ready();
  }
})();
