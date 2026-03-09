/**
 * @file Leads Sources Route Guard
 * @description Guards the leads sources form with MutationObserver support
 * @requires ERPGuard
 * @requires ERPUtils
 * @requires jQuery
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;
  const $ = window.jQuery;

  if (!guard || !utils || !$) {
    void 0;
    return;
  }

  const DPL = "data-pointer-listener";
  const FORM_ID = "leads-sources-form";
  const MSG_KEY = "leads_sources_update_route_unavailable";
  const FALLBACK_MSG =
    "Leads sources route is unavailable. Please contact technical support.";

  const handlersPointer = new WeakMap();

  const bindFormPointerGuard = form => {
    if (!form || form.getAttribute(DPL) === "true") return;
    form.setAttribute(DPL, "true");
    const $btns = $(form).find('button[type="submit"], input[type="submit"]');
    if (!$btns.length) return;

    const h = e => {
      try {
        const url = form.getAttribute("data-url");
        const action = form.getAttribute("action");
        if (guard.isInvalidUrl(url) && guard.isInvalidUrl(action)) {
          e.preventDefault();
          e.stopPropagation();
          const msg =
            utils.getTranslation(MSG_KEY) ||
            form.getAttribute("data-guard-msg") ||
            FALLBACK_MSG;
          guard.showToast(msg, "error");
        }
      } catch (_) {}
    };

    handlersPointer.set(form, h);
    $btns.each(function () {
      $(this).on("pointerup", h);
    });
  };

  const unbindFormPointerGuard = form => {
    const h = handlersPointer.get(form);
    if (h) {
      $(form)
        .find('button[type="submit"], input[type="submit"]')
        .each(function () {
          $(this).off("pointerup", h);
        });
      handlersPointer.delete(form);
    }
    form?.removeAttribute?.(DPL);
  };

  const scan = root => {
    const form = (root || document).getElementById(FORM_ID);
    if (form) bindFormPointerGuard(form);
  };

  const ready = () => {
    try {
      scan(document);
    } catch (_) {}
  };

  if (document.readyState === "loading") {
    $(ready);
  } else {
    ready();
  }

  const mo = new MutationObserver(muts => {
    muts.forEach(m => {
      m.addedNodes?.forEach(n => {
        if (n.nodeType === 1) scan(n);
      });
      m.removedNodes?.forEach(n => {
        if (n.nodeType === 1) {
          if (n.id === FORM_ID) unbindFormPointerGuard(n);
          n.querySelectorAll?.("#" + FORM_ID).forEach(unbindFormPointerGuard);
        }
      });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();
