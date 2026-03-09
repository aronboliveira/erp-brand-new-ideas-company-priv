/**
 * @file Login Submit Route Guard
 * @description Guards the login form submission
 * @requires ERPGuard
 * @requires ERPUtils
 * @requires jQuery
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;

  if (!$) {
    // jQuery is essential for this script — defer without blocking the form
    return;
  }

  const bindSubmit = () => {
    const form = document.querySelector("#form_data");
    if (!form) return;

    const listenerAttr = "data-login-submit-listener";
    if (form.getAttribute(listenerAttr) === "true") return;
    form.setAttribute(listenerAttr, "true");

    const handler = () => {
      try {
        const btn = document.querySelector("#login_button");
        if (btn) {
          btn.setAttribute("disabled", "true");
          return true;
        } else {
          const msg =
            utils?.getTranslation?.("login_submit_unavailable") ||
            "Login submission failed.";
          guard?.scheduleError?.(msg);
          return true;
        }
      } catch (_) {
        const msg =
          utils?.getTranslation?.("login_submit_unavailable") ||
          "Login submission failed.";
        guard?.scheduleError?.(msg);
        return true;
      }
    };

    $(form).on("submit.loginGuard", handler);

    const obs = new MutationObserver(() => {
      if (!document.body.contains(form)) {
        $(form).off("submit.loginGuard");
        obs.disconnect();
      }
    });
    obs.observe(document.body, { childList: true, subtree: true });
  };

  if (document.readyState === "loading") {
    $(() => bindSubmit());
  } else {
    bindSubmit();
  }
})();
