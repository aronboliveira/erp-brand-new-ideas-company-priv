/**
 * @file Payslips PDF Save Guard
 * @description Guards PDF save functionality using ERPGuard singleton
 * @requires ERPGuard
 * @requires html2pdf
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;

  if (!guard) {
    
    return;
  }

  const Q = s => document.querySelector(s);
  const QA = s => Array.from(document.querySelectorAll(s));
  const CLICK_SEL = '[data-action="save-pdf"]';
  const ONCE = "data-guard-once";
  const MSG_KEY = "savepdf_unavailable";
  const FALLBACK = "Save as PDF is unavailable. Please contact technical support or your domain administrator.";

  /**
   * Perform PDF save operation
   * @param {HTMLElement} btn - Button element
   */
  const doSavePdf = btn => {
    try {
      const area = Q("#printableArea");
      if (!area || typeof html2pdf === "undefined" || !html2pdf?.().set) {
        throw new Error("missing");
      }

      const filename = (Q(".invoice .invoice-title h4")?.textContent || "document").trim();
      const opt = {
        margin: 0.3,
        filename,
        image: { type: "jpeg", quality: 1 },
        html2canvas: { scale: 4, dpi: 72, letterRendering: true },
        jsPDF: { unit: "in", format: "A4" },
      };

      html2pdf()
        .set(opt)
        .from(area)
        .save()
        .catch(() => {
          const msg = utils?.getTranslation?.(MSG_KEY) || btn?.getAttribute("data-guard-msg") || FALLBACK;
          guard.showToast(msg, "error");
        });
    } catch (_) {
      const msg = utils?.getTranslation?.(MSG_KEY) || btn?.getAttribute("data-guard-msg") || FALLBACK;
      guard.showToast(msg, "error");
    }
  };

  /**
   * Bind PDF save to button
   * @param {HTMLElement} btn - Button element
   */
  const bind = btn => {
    if (!btn || btn.getAttribute(ONCE) === "true") return;
    btn.setAttribute(ONCE, "true");

    btn.addEventListener("click", ev => {
      ev.preventDefault();
      doSavePdf(btn);
    }, { passive: true });
  };

  // Initial binding
  document.addEventListener("DOMContentLoaded", () => {
    QA(CLICK_SEL).forEach(bind);
  });

  // Observe for dynamically added buttons
  const mo = new MutationObserver(muts => {
    muts.forEach(m => {
      m.addedNodes?.forEach(n => {
        if (n.matches?.(CLICK_SEL)) bind(n);
        n.querySelectorAll?.(CLICK_SEL).forEach(bind);
      });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  // Global function for legacy support
  window.saveAsPDF = () => doSavePdf(Q(CLICK_SEL) || document.body);
})();
