/**
 * @fileoverview TypeScript version of public/assets/js/routes/transactions/pdf.js
 * @generated from original JavaScript - manual review recommended
 * @module pdf
 */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-pdf-error";
  const qs = (s: string, r: Document | Element = document) =>
    r.querySelector(s);
  const hasBootstrap = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap?.Toast;
  const ensureToastContainer = (): HTMLDivElement => {
    const existing = qs("#np-toast-container") as HTMLDivElement | null;
    if (existing) {
      return existing;
    }
    const c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string) => {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t as Element);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t as Element, {
          autohide: true,
          delay: 4000,
        }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const schedulePointerupError = (message: string) => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const filenameFrom = (): string => {
    try {
      if (!$) return "download";
      return String($("#filename").val() ?? "").trim() || "download";
    } catch (_) {
      return "download";
    }
  };
  const ensureHtml2Pdf = (): boolean => {
    if (typeof window.html2pdf === "function") {
      return true;
    }
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("html2pdf unavailable");
    } catch (_) {}
    schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
    return false;
  };
  const doSave = (el: HTMLElement | null) => {
    if (!ensureHtml2Pdf()) {
      return;
    }
    const area = document.getElementById("printableArea");
    if (!area) {
      schedulePointerupError(getMsg(document.body, "pdf_unavailable"));
      return;
    }
    const opt = {
      margin: 0.3,
      filename: filenameFrom(),
      image: { type: "jpeg", quality: 1 },
      html2canvas: { scale: 4, dpi: 72, letterRendering: true },
      jsPDF: { unit: "in", format: "A4" },
    };
    try {
      (window.html2pdf as () => any)().set(opt).from(area).save();
    } catch (_) {
      schedulePointerupError(getMsg(el ?? document.body, "pdf_unavailable"));
    }
  };
  if (!window.saveAsPDF) {
    window.saveAsPDF = function (): void {
      doSave(this || document.body);
    };
  }
})();

export {};
