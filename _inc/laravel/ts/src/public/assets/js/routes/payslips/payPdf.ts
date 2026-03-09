/**
 * @fileoverview TypeScript version of public/assets/js/routes/payslips/payPdf.js
 * @generated from original JavaScript - manual review recommended
 * @module payPdf
 */

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(() => {
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const Q = (s: string) => document.querySelector(s);
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const QA = (s: string) => Array.from(document.querySelectorAll(s)),
    CLICK_SEL = '[data-action="save-pdf"]',
    ATTR_GUARD = "data-guard-msg",
    ATTR_LOCALIZED = "data-sv-localized",
    ONCE = "data-guard-once",
    DEFAULT_ERR = "Save as PDF is unavailable. Please contact technical support or your domain administrator.";
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const hasBs = () => !!(document.querySelector('link[rel~="stylesheet"][href*="bootstrap"]') && window.bootstrap);

  const getLang = (): string => {
    const fromStorage = (window.sessionStorage.getItem("erp-np-lang") ?? "").trim(),
      fromDoc = document.documentElement.lang.trim(),
      lang = (fromStorage || fromDoc).toLowerCase().replace(/_/g, "-");
    return lang === "pt-br" ? lang : lang.slice(0, 2);
  };
  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type

  // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
  const localizeGuard = (el: Element | null) => {
    if (!el) return DEFAULT_ERR;
    if (el.getAttribute(ATTR_LOCALIZED) === "true") return el.getAttribute(ATTR_GUARD) || DEFAULT_ERR;
    const lang = getLang(),
      key = "savepdf_unavailable";
    const msg = window.translations?.[lang]?.[key] || el.getAttribute(ATTR_GUARD) || window.translations?.en?.[key] || DEFAULT_ERR;
    el.setAttribute(ATTR_GUARD, msg);
    el.setAttribute(ATTR_LOCALIZED, "true");
    return msg;
  };

  const toast = (message: string): void => {
    const text = message || DEFAULT_ERR;
    if (hasBs()) {
      const wrapId = "toast-wrap-guard";
      if (!Q("#" + wrapId)) {
        const wrap = document.createElement("div");
        wrap.id = wrapId;
        wrap.className = "position-fixed top-0 end-0 p-3";
        wrap.style.zIndex = "1080";
        document.body.appendChild(wrap);
      }
      const id = "toast-savepdf-error",
        node = document.createElement("div");
      node.id = id;
      node.className = "toast align-items-center text-bg-danger border-0";
      for (const [k, v] of Object.entries({
        role: "alert",
        "aria-live": "assertive",
        "aria-atomic": "true",
      }))
        node.setAttribute(k, v);
      node.innerHTML = '<div class="d-flex"><div class="toast-body">' + text + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      Q("#" + wrapId)?.appendChild(node);
      new window.bootstrap.Toast(node, { autohide: true, delay: 4000 }).show();
    } else {
      alert(text);
    }
  };

  interface Html2PdfChain {
    set(o: Record<string, unknown>): Html2PdfChain;
    from(el: Element): Html2PdfChain;
    save(): Html2PdfChain;
    catch(fn: (err?: unknown) => void): void;
  }

  const doSavePdf = (btn: Element | null): void => {
    try {
      const area = Q("#printableArea"),
        h2p = window.html2pdf as (() => Html2PdfChain) | undefined;
      if (!area || !h2p?.().set) throw new Error("missing");
      const filename = (Q(".invoice .invoice-title h4")?.textContent ?? "document").trim(),
        opt = {
          margin: 0.3,
          filename,
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 4, dpi: 72, letterRendering: true },
          jsPDF: { unit: "in", format: "A4" },
        };
      h2p()
        .set(opt)
        .from(area)
        .save()
        .catch((): void => {
          toast(localizeGuard(btn));
        });
    } catch (e) {
      toast(localizeGuard(btn));
      try {
        if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") console.error("html2pdf unavailable or printable area missing");
      } catch (_) {
        console.error(`[payPdf] Error:`, _);
      }
    }
  };

  const bind = (btn: Element): void => {
    if (!btn || btn.getAttribute(ONCE) === "true") return;
    btn.setAttribute(ONCE, "true");
    localizeGuard(btn);
    btn.addEventListener(
      "click",
      (ev: Event) => {
        ev.preventDefault();
        doSavePdf(btn);
      },
      { passive: true },
    );
  };

  document.addEventListener("DOMContentLoaded", (): void => {
    QA(CLICK_SEL).forEach(bind);
  });

  const mo = new MutationObserver(m => {
    m.forEach(r => {
      r.addedNodes &&
        r.addedNodes.forEach(n => {
          if (n.nodeType !== Node.ELEMENT_NODE) return;
          const el = n as Element;
          el.matches(CLICK_SEL) ? bind(el) : el.querySelectorAll(CLICK_SEL).forEach(child => bind(child));
        });
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });

  window.saveAsPDF = (): void => {
    // eslint-disable-next-line @typescript-eslint/prefer-nullish-coalescing
    doSavePdf(Q(CLICK_SEL) || document.body);
  };
})();

export {};
