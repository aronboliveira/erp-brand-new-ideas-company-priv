/**
 * @file POS Barcode Setting Route Guard
 * @description Guards the barcode setting form and select elements
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
  const LS = "data-listener-active";
  const NS = ".barcodeSetting";
  const FORM_ID = "pos-barcode-setting-form";
  const MSG_KEY = "action_unavailable";
  const FALLBACK_MSG = "This action is unavailable. Please contact technical support.";

  const formHandlers = new WeakMap();

  /**
   * Binds guard to a form
   * @param {HTMLFormElement} f - Form element
   */
  const bindForm = f => {
    if (!f || f.getAttribute(L) === "true") return;
    f.setAttribute(L, "true");

    const handler = e => {
      try {
        const url = f.getAttribute("data-url");
        const action = f.getAttribute("action");

        if (guard.isInvalidUrl(url) && guard.isInvalidUrl(action)) {
          e.preventDefault();
          const msg = utils?.getTranslation?.(MSG_KEY) ||
            f.getAttribute("data-guard-msg") ||
            FALLBACK_MSG;
          guard.showToast(msg, "error");
        }
      } catch (_) {
        e.preventDefault();
        guard.showToast(FALLBACK_MSG, "error");
      }
    };

    $(f).on("submit.formGuard", handler);
    formHandlers.set(f, handler);
  };

  /**
   * Unbinds guard from a form
   * @param {HTMLFormElement} f - Form element
   */
  const unbindForm = f => {
    if (!f) return;
    try {
      $(f).off("submit.formGuard");
      f.removeAttribute(L);
      formHandlers.delete(f);
    } catch (_) {}
  };

  /**
   * Binds change handler to select element
   * @param {HTMLSelectElement} s - Select element
   */
  const bindSelect = s => {
    if (!s || s.getAttribute(LS) === "true") return;
    s.setAttribute(LS, "true");

    if (!s.value && s.options?.length) s.selectedIndex = 0;

    $(s).on("change" + NS, () => {
      try {
        const v = $(s).val();
        if (v != null) {
          s.setAttribute("data-has-selection", String(v !== ""));
        }
      } catch (_) {}
    });
  };

  /**
   * Unbinds change handler from select element
   * @param {HTMLSelectElement} s - Select element
   */
  const unbindSelect = s => {
    if (!s) return;
    try {
      $(s).off("change" + NS);
      s.removeAttribute(LS);
    } catch (_) {}
  };

  const ready = () => {
    try {
      // Bind forms with guard attributes
      $("form[data-guard-msg], form[data-url]").each(function () {
        bindForm(this);
      });

      // Bind barcode setting form selects
      const form = document.getElementById(FORM_ID);
      if (form) {
        bindForm(form);
        form.querySelectorAll('select[data-toggle="select"]').forEach(bindSelect);
      }
    } catch (_) {}
  };

  if (document.readyState === "loading") {
    $(ready);
  } else {
    ready();
  }

  // Observe DOM changes for cleanup
  const mo = new MutationObserver(muts => {
    muts.forEach(m => {
      m.removedNodes?.forEach(n => {
        if (n.nodeType !== 1) return;
        if (n.tagName === "FORM") unbindForm(n);
        if (n.tagName === "SELECT") unbindSelect(n);
        n.querySelectorAll?.("form").forEach(unbindForm);
        n.querySelectorAll?.("select").forEach(unbindSelect);
      });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();
